<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

class Utils
{
	var $javascript_files_path = "jsf/";
	var $css_files_path = "";
	
	var $months = array( 1 => 2405, 2 => 2406, 3 => 2407, 4 => 2408, 5 => 2409, 6 => 2410, 7 => 2411, 8 => 2412, 9 => 2413, 10 => 2414, 11 => 2415, 12 => 2416 );
	var $months_simple = array( 1 => 89, 2 => 93, 3 => 94, 4 => 95, 5 => 96, 6 => 97, 7 => 98, 8 => 99, 9 => 100, 10 => 101, 11 => 102, 12 => 103 );
	var $weeks = array( 0 => 109, 1 => 110, 2 => 111, 3 => 112, 4 => 113, 5 => 114, 6 => 115 );
	
	function cleanTabs( $t )
	{
		return $t;
	}
	
	function getCorrectWidthOfString( $str, $maxlen, $end = "..." )
	{
		if( @function_exists( "mb_strlen" ) )
			return $this->mb_getCorrectWidthOfString( $str, $maxlen, $end );
		
		$len = strlen( $str );
		if( $len <= $maxlen )
			return $str;
			
		$add = 0;
		while( $str[$maxlen + $add] != ' ' && $str[$maxlen + $add] != '_' && $maxlen + $add < $len ) $add++;
		
		return substr( $str, 0, $maxlen + $add ).$end;
	}
	
	function mb_getCorrectWidthOfString( $str, $maxlen, $end = "..." )
	{
		$len = @mb_strlen( $str, "UTF-8" );
		if( $len <= $maxlen )
			return $str;
			
		return @mb_substr( $str, 0, $maxlen, "UTF-8" ).$end;
	}
	
	function getStrlen( $str )
	{
		if( @function_exists( "mb_strlen" ) )
			return @mb_strlen( $str, "UTF-8" );
		else 
			return @strlen( $str );
	}
	
	function rmdir_notempty( $dir )
	{ 
   		if( is_dir( $dir ) ) { 
     		$objects = @scandir( $dir );
     		foreach( $objects as $object ) { 
       			if( $object != "." && $object != ".." ) { 
         			if( @filetype( $dir."/".$object ) == "dir" ) 
         				rmdir_notempty( $dir."/".$object ); 
         			else 
         				@unlink( $dir."/".$object ); 
       			} 
     		} 
     		@reset( $objects );
     		@rmdir( $dir ); 
   		} 
	}
	
	function getCapcha( $cap, $mysql )
	{
		$img = @imagecreatetruecolor( 120, 60 );
		@imagefill( $img, 0, 0, 0xc2dafe );
		
		$colors = array();
		$c = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."colors` WHERE `id`<>1 AND `id`<>6 AND `id`<>7 AND `id`<>9" );
		while( $r = @mysql_fetch_assoc( $c ) )
			$colors[$r['id']] = $r;			
		$color = $colors[array_rand( $colors )];
		
		$cap = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."capcha` WHERE `file`=".$cap );
		if( !$cap )
			exit;
		
		if( $color ) {
			$r = $color['r'];
			$g = $color['g'];
			$b = $color['b'];
		} else {
			$r = 0;
			$g = 0;
			$b = 0;
		}
		$color = @imagecolorallocatealpha( $img, $r, $g, $b, 60 );
		if( function_exists( "imageantialias" ) )
			@imageantialias( $img, true );
		$size = 20;
		$ar = imagettfbbox( $size, 0, ".".$mysql->settings['local_folder']."arialbi.ttf", $cap['nums'] );
		$x = ( 120 - $ar[2] ) / 2;
		
		for( $a = 0; $a < 120; $a++ )
			@imageline( $img, mt_rand( 0, 119 ), mt_rand( 0, 59 ), mt_rand( 0, 119 ), mt_rand( 0, 59 ), @imagecolorallocatealpha( $img, mt_rand( 0, 255 ), mt_rand( 0, 255 ), mt_rand( 0, 255 ), 70 ) );
		
		imagettftext( $img, $size, mt_rand( -10, 10 ), $x, 45, $color, ".".$mysql->settings['local_folder']."arialbi.ttf", $cap['nums'] );
		
		$filename = md5( time().$cap['nums'] ).".jpg";
		
		@ob_end_clean(); 
		@ini_set( 'zlib.output_compression', 'Off' );
		
		header( 'Pragma: ' );
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
		header( 'Last-Modified: '.gmdate( 'D, d M Y H:i:s' ).' GMT' ); 
		header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
		header( 'Cache-Control: post-check=0, pre-check=0, max-age=0' );
		header( 'Content-Transfer-Encoding: none' );
		
		header( "Content-Type: image/jpg; name=\"".$filename."\"" );
	    header( "Content-Disposition: inline; filename=\"".$filename."\"" );
		
		@imagejpeg( $img );
	}
	
	function checkStringForExistOfSymbols( $str, $smb )
	{
		$sl = $str.length;
		$scl = $smb.length;
		for( $a = 0; $a < $sl; $a++ ) {
			$c = $str[a];
			$here = false;
			for( $i = 0; $i < $scl; $i++ ) {
				if( $c == $smb[i] ) {
					$here = true;
					break;
				}
			}
			if( !$here ) {
				return false;
			}
		}
	
		return true;	
	}
	
	function checkForLegalDomainname( $host )
	{
		global $mysql;
		
		if( @function_exists( "getmxrr" ) )
			return @getmxrr( $host, $mxhostsarr );
			
		return !@fsockopen( $host, 80, $errno, $errstr, 4 ) ? false : true;
	}
	
	function checkEmail( $email )
	{
		if( !preg_match( "/^(?:[a-z0-9]+(?:[-_]?[a-z0-9]+)?@[a-z0-9]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", trim( $email ) ) )
			return 0;
		
		return 1;
	}
	
	function httpGet( $header, $host )
	{
		global $mysql;
		
 		$return = "";
 		if( !$OpenedSocket = @fsockopen( $host, 80, $errno, $errstr, 9 ) )
  			return "";
 		else {
  			@fwrite( $OpenedSocket, $header );
	  		while( !@feof( $OpenedSocket ) )
   				$return .= @fgets( $OpenedSocket, 4096 );
  			@fclose( $OpenedSocket );
 		}
 		
 		return $return;
	}
	
	function searchArrayForValue( $ar, $val )
	{
		foreach( $ar as $k => $v )
			if( $v == $val )
				return $k;
				
		return false;
	}
	
	function generatePassword()
	{
            $res = '';
            $len = 8;
        	$useChars = '23456789ABCDEFGHKMNPQRSTUVWXYZabcdefghkmnpqrstuvwxyz';
        	$useChars .= $useChars;
            for( $i = 0; $i < $len; $i++ )
                $res .= $useChars[mt_rand( 0, strlen( $useChars ) - 1 )];
            return $res;
	}
	
	function getTimeBetween( $start, $end, $dayPhrase, $hourPhrase, $minPhrase )
	{
		$b = $end - $start;
		$inday = 3600 * 24;
		$days = floor( $b / $inday );
		$b = $b - ( $days * $inday );
		$hours = floor( $b / 3600 );
		$b = $b - ( $hours * 3600 );
		$min = floor( $b / 60 );
		
		return ( $days ? $days." ".$dayPhrase." " : "" ).( $hours ? $hours." ".$hourPhrase." " : "" ).( $min ? $min." ".$minPhrase." " : "" );
	}
	
	var $ftypes = array(
		'doc' => "application/msword",
		'docx' => "application/msword",
		'rtf' => "application/msword",
		'pdf' => "application/pdf"
	);
	
	function getfile( $fname, $filename )
	{
		if( !file_exists( $fname ) )
			return 0;
			
		$tt = explode( ".", $filename );
		$ext = $tt[count( $tt ) - 1];

		$ct = isset( $this->ftypes[strtolower( $ext )] ) ? $this->ftypes[strtolower( $ext )] : 'application/force-download';
		$filesize = @filesize( $fname );
		
		$fp = @fopen( $fname, 'rb' );
		$buffer = @fread( $fp, $filesize );
		@fclose( $fp );
		
		@ob_end_clean(); 
		@ini_set( 'zlib.output_compression', 'Off' );
		
		header( 'Pragma: ' );
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
		header( 'Last-Modified: '.gmdate( 'D, d M Y H:i:s' ).' GMT' ); 
		header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
		header( 'Cache-Control: post-check=0, pre-check=0, max-age=0' );
		header( 'Content-Transfer-Encoding: none' );
		
		header( "Content-Type: $ct; name=\"".$filename."\"" );
	    header( "Content-Disposition: inline; filename=\"".$filename."\"" );
	    header( "Content-length: ".$filesize );

		echo $buffer;
    	
       	return 1;
	}
	
	function StrToTranslite( $st )
	{
		return iconv( "UTF-8", "WINDOWS-1251", $st );
		
    	$st = strtr( $st, "абвгдеёзийклмнопрстуфхъыэ_", "abvgdeeziyklmnoprstufh`iei" );
    	$st = strtr( $st, "АБВГДЕЁЗИЙКЛМНОПРСТУФХЪЫЭ_", "ABVGDEEZIYKLMNOPRSTUFH`IEI");

    	$st = strtr( $st, 

                    array(

                        "ж"=>"zh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh", 

                        "щ"=>"shch","ь"=>"", "ю"=>"yu", "я"=>"ya",

                        "Ж"=>"ZH", "Ц"=>"TS", "Ч"=>"CH", "Ш"=>"SH", 

                        "Щ"=>"SHCH","Ь"=>"", "Ю"=>"YU", "Я"=>"YA",

                        "ї"=>"i", "Ї"=>"Yi", "є"=>"ie", "Є"=>"Ye"

                        )
        );

    	return $st;
	}
	
	// ИЛИ
	
	function translitIt( $str ) 
	{
		$str = str_replace( "&quot;", "_", $str );
		$str = str_replace( " ", "_", $str );
		$str = str_replace( '"', "_", $str );
		$str = str_replace( "'", "", $str );
		$str = str_replace( ",", "", $str );
		//$str = str_replace( ".", "_", $str );
		$str = str_replace( "#", "_", $str );
		$str = str_replace( ":", "_", $str );
		$str = str_replace( ";", "_", $str );
		$str = str_replace( "!", "_", $str );
		$str = str_replace( "?", "_", $str );
		$str = str_replace( "@", "_", $str );
		$str = str_replace( '"', "", $str );
		$str = str_replace( '(', "", $str );
		$str = str_replace( ')', "", $str );
		$str = str_replace( '«', "", $str );
		$str = str_replace( '»', "", $str );
		$str = str_replace( '/', "", $str );
		$str = str_replace( '___', "_", $str );
		$str = str_replace( '__', "_", $str );
		
    	return strtr( $str, array(
        "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
        "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
        "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
        "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
        "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
        "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
        "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
        "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
        "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
        "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
        "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
        "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
        "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya","ё"=>"yo","Ё"=>"YO"
    	) );
	}
	
	function getRightCountString( $value, $str_0, $str_1, $str_2 )
	{
		
		// "новых сообщений", "новое сообщение", "новых сообщения"
		// "пользователей", "пользователь", "пользователя"
		
		if( ( $value >= 5 ) && ( $value <= 20 ) )
			$str = $str_0;
  		else {
			$num = $value - ( floor( $value / 10 ) * 10 );
	 
			if( $num == 1 ) 
				$str = $str_1;
	 		else if( $num == 0 )
	 			$str = $str_0;
	  		else if( $num >= 2 && $num <= 4 ) 
	  			$str = $str_2;
	    	else if( $num >= 5 && $num <= 9 ) 
	    		$str = $str_0;
		}
		
  		return $value." ".$str;	
	}
	
	function getDigitsFromString( $s )
	{
		$ss = '';
		$len = strlen( $s );
		for( $a = 0; $a < $len; $a++ ) {
			if( is_numeric( $s[$a] ) )
				$ss .= $s[$a];
		}
		
		return $ss;
	}
	
	function getFormattedDate( $date )
	{
		global $lang;

		$t = explode( "/", date( "d/m/Y" ) );
		$today = mktime( "00", "00", "00", $t[1], $t[0], $t[2] );
		$yesterday = $today - ( 3600 * 24 );
		$thisyear = $t[2];
		
		$t = "";
		if( $date >= $today ) {
			$t = $lang->gp( 90, true )."&nbsp;".trim( date( "H:i", $date ) );
		} else if( $date >= $yesterday ) {
			$t = $lang->gp( 91, true )."&nbsp;".trim( date( "H:i", $date ) );
		} else {
			$month = $lang->gp( $this->months[date( "n", $date )], true );
			$t = date( "j", $date )." ".$month." ".$lang->gp( 92, true )." ".date( "H:i", $date );
		}
		
		return $t;
	}
	
	function getFormattedDateWithYear( $date )
	{
		global $lang;

		$t = explode( "/", date( "d/m/Y" ) );
		$today = mktime( "00", "00", "00", $t[1], $t[0], $t[2] );
		$yesterday = $today - ( 3600 * 24 );
		$thisyear = $t[2];
		
		$t = "";
		if( $date >= $today ) {
			$t = $lang->gp( 90, true )."&nbsp;".trim( date( "H:i", $date ) );
		} else if( $date >= $yesterday ) {
			$t = $lang->gp( 91, true )."&nbsp;".trim( date( "H:i", $date ) );
		} else {
			$month = $lang->gp( $this->months[date( "n", $date )], true );
			$t = date( "j", $date )." ".$month." ".date( "Y", $date ).( $lang->currentLanguage == 1 ? " ".$lang->gp( 28, true ) : "" );
		}
		
		return $t;
	}
	
	function getCorrectDuration( $duration )
	{
		global $lang;
			
		$hour = floor( $duration / 3600 );
		$min = $duration - ( $hour * 3600 );
		$min = floor( $min / 60 );
		$sec = $duration - ( $hour * 3600 ) - ( $min * 60 );
		$sec = floor( $sec );
		
		//return ( $hour ? $this->getRightCountString( $hour, $lang->gp( 2607 ), $lang->gp( 2608 ), $lang->gp( 2609 ) )." " : "" ).( $min ? $this->getRightCountString( $min, $lang->gp( 2610 ), $lang->gp( 2611 ), $lang->gp( 2612 ) ) : "" ).$this->getRightCountString( $sec, $lang->gp( 2613 ), $lang->gp( 2614 ), $lang->gp( 2615 ) );
		
		return ( $hour < 10 ? "0".$hour : $hour ).":".( $min < 10 ? "0".$min : $min ).":".( $sec < 10 ? "0".$sec : $sec );
	}
	
	function getFullDate( $date, $time = false, $noday = false )
	{
		global $lang;
		
		return ( !$noday ? date( "j", $date )." " : "" ).$lang->gp( $this->months[date( "n", $date )], true )." ".date( "Y", $date ).( $lang->currentLanguage == 1 ? " ".$lang->gp( 28, true ) : "" ).( $time ? " ".$lang->gp( 92, true )." ".date( "H:i", $date ) : "" );
	}
	
	function getMonthYearDate( $month, $year = false )
	{
		global $lang;
		
		return $lang->gp( $this->months_simple[$month], true ).( $year ? date( " Y" ).( $lang->currentLanguage == 1 ? " ".$lang->gp( 28, true ) : "" ) : "" );
	}
	
	function getMonthsYearDate( $month, $year = false )
	{
		global $lang;
		
		return $lang->gp( $this->months[$month], true ).( $year ? date( " Y" ).( $lang->currentLanguage == 1 ? " ".$lang->gp( 28, true ) : "" ) : "" );
	}
	
	function getDayOfWeek( $day )
	{
		global $lang;
		
		return $lang->gp( $this->weeks[$day], true );
	}
	
	function marktext( $str, $tomark, $ifnull = "&nbsp;", $maxlength = 0, &$found = 0 )
	{
		if( !$str )
			return $ifnull;
			
		if( $maxlength && mb_strlen( $str, "UTF-8" ) > $maxlength )
			$str = mb_substr( $str, 0, strpos( $str, " ", $maxlength, "UTF-8" ) )."...";
			
		if( !$tomark )
			return $str;
			
		return str_ireplace( $tomark, "<span style=\"background-color: yellow; border-bottom: 1px dashed #444;\">".$tomark."</span>", $str, $found );
	}
	
	function digitsToRazryadi( $digit )
	{
		return preg_replace('/(?<=\d)(?=(\d{3})+(?!\d))/', ' ', intval( $digit ) );
	}
}

?>