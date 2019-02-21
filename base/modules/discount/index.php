<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulediscount extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
        var $gl_dbase_string = "`shop`.";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
        
        function getDiscountForGood( $good, $dateFromOrder = 0 )
        {
            global $mysql;
            
            $cd = $dateFromOrder ? $dateFromOrder : time();
            $a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."discount` WHERE `start`<=".$cd." AND `end`>=".$cd );
            while( $r = @mysql_fetch_assoc( $a ) ) {
                $brands = json_decode( $r['brands'] );
                $tovar = json_decode( $r['tovar'] );
                if( $this->searchArrayForValue( $tovar, $good['id'] ) || $this->searchArrayForValue( $brands, $good['vendor'] ) )
                    return $r;
            }
            
            return null;
        }
        
        //
	// Далее администраторская область
	//
	
	function getAdminScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;
		
		if( $query->gp( "createaction" ) && $query->gp( "process" ) ) {
			
			$title = isset( $_POST['title'] ) && $_POST['title'] ? str_replace( "&nbsp;", " ", str_replace( "'", "\\'", $_POST['title'] )  ) : '';
                        $percent = $_POST['percent'];
			$brands = $_POST['brands'];
                        $tovar = $_POST['tovar'];
			$comment = isset( $_POST['comment'] ) && $_POST['comment'] ? str_replace( "&nbsp;", " ", str_replace( "'", "\\'", $_POST['comment'] )  ) : '';
			
			$start_date = $query->gp( "start_date" );
			if( $start_date ) {
				$ex = explode( " ", $start_date );
				if( count( $ex ) == 2 ) {
					$da = explode( "/", trim( $ex[0] ) );
					$ta = explode( ":", trim( $ex[1] ) );
					if( count( $ta ) == 2 && count( $da ) == 3 )
						$start_date = mktime( $ta[0], $ta[1], "00", $da[1], $da[0], $da[2] );
				}
			}			
			$start_date = is_numeric( $start_date ) && $start_date ? $start_date : time();
			
			$end_date = $query->gp( "end_date" );
			if( $end_date ) {
				$ex = explode( " ", $end_date );
				if( count( $ex ) == 2 ) {
					$da = explode( "/", trim( $ex[0] ) );
					$ta = explode( ":", trim( $ex[1] ) );
					if( count( $ta ) == 2 && count( $da ) == 3 )
						$end_date = mktime( $ta[0], $ta[1], "00", $da[1], $da[0], $da[2] );
				}
			}
			$end_date = is_numeric( $end_date ) && $end_date ? $end_date : time();
			
			$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."discount` VALUES(
				0,
                                '".$percent."',
				'".$title."',
				'".json_encode( $brands )."',
				'".json_encode( $tovar )."',
				'".$comment."',
				'".$start_date."',
				'".$end_date."'
			);" );
			
			return "<script>document.location = '".$mysql->settings['local_folder']."admin/".$path."';</script>";
			
		} else if( $query->gp( "edit" ) && $query->gp( "process" ) ) {
			
			$id = $query->gp( "edit" );
			
			$title = isset( $_POST['title'] ) && $_POST['title'] ? str_replace( "&nbsp;", " ", str_replace( "'", "\\'", $_POST['title'] )  ) : '';
                        $percent = $_POST['percent'];
			$brands = $_POST['brands'];
                        $tovar = $_POST['tovar'];
			$comment = isset( $_POST['comment'] ) && $_POST['comment'] ? str_replace( "&nbsp;", " ", str_replace( "'", "\\'", $_POST['comment'] )  ) : '';
			
			$start_date = $query->gp( "start_date" );
			if( $start_date ) {
				$ex = explode( " ", $start_date );
				if( count( $ex ) == 2 ) {
					$da = explode( "/", trim( $ex[0] ) );
					$ta = explode( ":", trim( $ex[1] ) );
					if( count( $ta ) == 2 && count( $da ) == 3 )
						$start_date = mktime( $ta[0], $ta[1], "00", $da[1], $da[0], $da[2] );
				}
			}			
			$start_date = is_numeric( $start_date ) && $start_date ? $start_date : time();
			
			$end_date = $query->gp( "end_date" );
			if( $end_date ) {
				$ex = explode( " ", $end_date );
				if( count( $ex ) == 2 ) {
					$da = explode( "/", trim( $ex[0] ) );
					$ta = explode( ":", trim( $ex[1] ) );
					if( count( $ta ) == 2 && count( $da ) == 3 )
						$end_date = mktime( $ta[0], $ta[1], "00", $da[1], $da[0], $da[2] );
				}
			}
			$end_date = is_numeric( $end_date ) && $end_date ? $end_date : time();
			
			$mysql->mu( "UPDATE ".$this->gl_dbase_string."`".$mysql->t_prefix."discount` SET

				`title`='".$title."',
				`brands`='".json_encode( $brands )."',
                                `tovar`='".json_encode( $tovar )."',
                                    `percent`='".$percent."',
				`start`='".$start_date."',
				`end`='".$end_date."',
				`text`='".$comment."'
				
			WHERE `id`=".$id );
			
			$query->setProperty( "edit", 0 );
			
		} else if( $query->gp( "turnaction" ) ) {
			
			$id = $query->gp( "turnaction" );
			$ep = $mysql->mq( "SELECT `view` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."discount` WHERE `id`=".$id );			
			if( $ep )
				$mysql->mu( "UPDATE ".$this->gl_dbase_string."`".$mysql->t_prefix."discount` SET `view`=".( $ep['view'] ? "0" : "1" )." WHERE `id`=".$id );
				
		} else if( $query->gp( "delete" ) ) {
			
			$id = $query->gp( "delete" );
			$ep = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."discount` WHERE `id`=".$id );			
			if( $ep ) {
				$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."discount` WHERE `id`=".$id );
			}
			
		}
		
		if( $query->gp( "createaction" ) ) {
			
			return $this->getExternalNewAction( $path );
			
		} else if( $query->gp( "edit" ) ) {
			
			return $this->getExternalEditAction( $path, $query->gp( "edit" ) );
			
		}
		
		$selectedElement = 1;
		$t = "
			<h1>Список скидок</h1>
			
			<a href=\"".$mysql->settings['local_folder']."admin/".$path."/createaction\">Создать новую скидку</a><br><br>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%;'>
				<tr class='list_table_header'>
					<td width=50>
						ID
					</td>
					<td width=20% style='text-align: left;'>
						Название и размер скидки
					</td>
					<td width=20%>
						Дата начала/окончания действия
					</td>
					<td width=70>
						Включена?
					</td>
					<td width=110>
						Опции
					</td>
				</tr>
		";
		
		$where = '';
			
		if( !$where )
			$where = "1";
			
		$modules = $main->modules->modules;
			
		$counter = 0;
		$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."discount` WHERE 1 ORDER BY `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
                    
                        $isin = time() >= $r['start'] && time() <= $r['end'] ? true : false;
			
			$t .= "
				<tr class='list_table_element'".( !$isin ? " style='background-color: #fcd9d9;'" : "" ).">
					<td valign=middle>
						".$r['id']."
					</td>
					<td style='text-align: left;' valign=middle>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/edit".$r['id']."\"><strong>".$r['title']."</strong></a><br>
						---<br>
						".$r['percent']."%
					</td>
					<td nowrap>
						с ".$utils->getFullDate( $r['start'], true )."<br>по ".$utils->getFullDate( $r['end'], true )."
					</td>
					<td>
						".( $isin ? "Да" : "Нет" )."
					</td>
					<td nowrap>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/turnaction".$r['id']."\">".( $r['view'] ? "Выключить" : "Включить" )."</a><br>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/edit".$r['id']."\">Редактировать</a><br>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/delete".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a>
					</td>
				</tr>
			";
			$counter++;
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=5>
						Всего скидок: ".$counter."
					</td>
				</tr>
		</table>
		";
		
		return $t;
	}
	
	function getExternal( $wt, $link )
	{
		global $query, $mysql;
		
		return "Unknown actions query";
	}
	
	function getExternalNewAction( $link )
	{
		global $query, $mysql, $lang, $main, $utils;
		
		$inner = "
				<p>
					Название скидки: <label class='red'>*</label><br>
					<input type=text name=\"title\" id=\"title\" value=\"\" class='text_input' />
				</p>
                                <p>
					Процент скидки: <label class='red'>*</label><br>
					<input type=text name=\"percent\" id=\"percent\" value=\"\" class='text_input' />
				</p>
                                <p>
					Бренды, участвующие в скидке: <br>
					<select name=\"brands[]\" id=\"brands\" class='select_input' multiple style='height: 350px;'>
						".$main->listings->getListingForSelecting( 7, '', 0, "", "", true, '', true )."
					</select>
				</p>
				<p>
					И (ИЛИ) конкретные оправы, участвующие в скидке: <br>
					<select name=\"tovar[]\" id=\"tovar\" class='select_input' multiple style='height: 350px;'>
						".$main->modules->gmi( "catalog_admin" )->getOpravasForSelect()."
					</select>
				</p>				
				<p>
					Дата начала акции: (формат: дд/мм/гггг чч:мм , пример: 07/10/2013 15:31) - оставьте поле пустым, чтобы использовать текущую дату и время<br>
					<input type=text name=\"start_date\" id=\"start_date\" value=\"".date( "d/m/Y H:i" )."\" class='text_input' />
				</p>
				<p>
					Дата окончания акции: (формат: дд/мм/гггг чч:мм , пример: 07/10/2013 15:31) - оставьте поле пустым, чтобы использовать текущую дату и время<br>
					<input type=text name=\"end_date\" id=\"end_date\" value=\"".date( "d/m/Y H:i", time() + ( 3600 * 24 * 365 ) )."\" class='text_input' />
				</p>
				<p>
					Комментарий:<br>
					<textarea name=\"comment\" id=\"comment\" rows=25 class='textarea_input'></textarea>
				</p>
				
				<script type=\"text/javascript\" src=\"".$mysql->settings['local_folder'].$utils->javascript_files_path."tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>
				<script type=\"text/javascript\">
	tinyMCE.init({
		
		mode : \"exact\",
		elements : \"comment\",
		language : \"ru\",
		theme : \"advanced\",
		plugins : \"pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave\",

		
		theme_advanced_buttons1 : \"bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect,|,forecolor,backcolor\",
		theme_advanced_buttons2 : \"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,code,|,insertdate,inserttime,preview\",
		theme_advanced_buttons3 : \"tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen\",
		theme_advanced_buttons4 : \"insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft\",
		theme_advanced_toolbar_location : \"top\",
		theme_advanced_toolbar_align : \"left\",
		theme_advanced_statusbar_location : \"bottom\",
		theme_advanced_resizing : true,

		
		content_css : \"css/content.css\",

		
		template_external_list_url : \"lists/template_list.js\",
		external_link_list_url : \"lists/link_list.js\",
		external_image_list_url : \"lists/image_list.js\",
		media_external_list_url : \"lists/media_list.js\",

		
		style_formats : [
			{title : 'Bold text', inline : 'b'},
			{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
			{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
			{title : 'Example 1', inline : 'span', classes : 'example1'},
			{title : 'Example 2', inline : 'span', classes : 'example2'},
			{title : 'Table styles'},
			{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
		],
		
		convert_urls : false,
        relative_urls : false,
        remove_script_host : false,  
        
		template_replace_values : {
			username : \"Some User\",
			staffid : \"991234\"
		}
	});
</script>
			";
			
		return "
				<h1 align=left>Создание новой акции со скидками</h1>
				
				<form action=\"".$mysql->settings['local_folder']."admin/".$link."\" method=POST id='creatings' style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=hidden name=\"createaction\" id=\"createaction\" value=\"0\" />						
						<input type=button value=\"Создать\" class='button_input' onclick=\"
							if( $( '#title' ).val() == '' ) { 
								alert( 'Укажите название акции' ); 
								return false; 
							}
							$( '#process' ).val( 1 );
							$( '#createaction' ).val( 1 );
							$( '#creatings' ).submit();
						\" />&nbsp;&nbsp;
						<input type=button value=\"Отменить\" class='button_input' onclick=\"$( '#creatings' ).submit();\" />
					</div>
				</form>
		";
	}
        
	function getExternalEditAction( $link, $actionid )
	{
		global $query, $mysql, $lang, $main, $utils;
		
		$data = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."discount` WHERE `id`=".$actionid );
		if( !$data ) {
			return "Unknown action id";
		}
		
		$inner = "
				<p>
					Название скидки: <label class='red'>*</label><br>
					<input type=text name=\"title\" id=\"title\" value=\"".$data['title']."\" class='text_input' />
				</p>
                                <p>
					Процент скидки: <label class='red'>*</label><br>
					<input type=text name=\"percent\" id=\"percent\" value=\"".$data['percent']."\" class='text_input' />
				</p>
				<p>
					Бренды, участвующие в скидке: <br>
					<select name=\"brands[]\" id=\"brands\" class='select_input' multiple style='height: 350px;'>
						".$main->listings->getListingForSelecting( 7, str_replace( '[', '', str_replace( ']', '', str_replace( '"', '', $data['brands'] ) ) ), 0, "", "", true, '', true )."
					</select>
				</p>
				<p>
					И (ИЛИ) конкретные оправы, участвующие в скидке: <br>
					<select name=\"tovar[]\" id=\"tovar\" class='select_input' multiple style='height: 350px;'>
						".$main->modules->gmi( "catalog_admin" )->getOpravasForSelect( json_decode( $data['tovar'] ) )."
					</select>
				</p>			
				<p>
					Дата начала акции: (формат: дд/мм/гггг чч:мм , пример: 07/10/2013 15:31) - оставьте поле пустым, чтобы использовать текущую дату и время<br>
					<input type=text name=\"start_date\" id=\"start_date\" value=\"".date( "d/m/Y H:i", $data['start'] )."\" class='text_input' />
				</p>
				<p>
					Дата окончания акции: (формат: дд/мм/гггг чч:мм , пример: 07/10/2013 15:31) - оставьте поле пустым, чтобы использовать текущую дату и время<br>
					<input type=text name=\"end_date\" id=\"end_date\" value=\"".date( "d/m/Y H:i", $data['end'] )."\" class='text_input' />
				</p>
				<p>
					Комментарий:<br>
					<textarea name=\"comment\" id=\"comment\" rows=25 class='textarea_input'>".$data['text']."</textarea>
				</p>
				<script type=\"text/javascript\" src=\"".$mysql->settings['local_folder'].$utils->javascript_files_path."tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>
				<script type=\"text/javascript\">
	tinyMCE.init({
		
		mode : \"exact\",
		elements : \"comment\",
		language : \"ru\",
		theme : \"advanced\",
		plugins : \"pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave\",

		
		theme_advanced_buttons1 : \"bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect,|,forecolor,backcolor\",
		theme_advanced_buttons2 : \"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,code,|,insertdate,inserttime,preview\",
		theme_advanced_buttons3 : \"tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen\",
		theme_advanced_buttons4 : \"insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft,smimage,smexplorer\",
		theme_advanced_toolbar_location : \"top\",
		theme_advanced_toolbar_align : \"left\",
		theme_advanced_statusbar_location : \"bottom\",
		theme_advanced_resizing : true,

		
		content_css : \"css/content.css\",

		
		template_external_list_url : \"lists/template_list.js\",
		external_link_list_url : \"lists/link_list.js\",
		external_image_list_url : \"lists/image_list.js\",
		media_external_list_url : \"lists/media_list.js\",

		
		style_formats : [
			{title : 'Bold text', inline : 'b'},
			{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
			{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
			{title : 'Example 1', inline : 'span', classes : 'example1'},
			{title : 'Example 2', inline : 'span', classes : 'example2'},
			{title : 'Table styles'},
			{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
		],
		
		convert_urls : false,
        relative_urls : false,
        remove_script_host : false,  
        
		template_replace_values : {
			username : \"Some User\",
			staffid : \"991234\"
		}
	});
</script>
			";
			
		return "
				<h1 align=left>Редактирование акции со скидками «".$data['title']."»</h1>
				
				<form action=\"".$mysql->settings['local_folder']."admin/".$link."\" method=POST id='creatings' style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=hidden name=\"edit\" id=\"edit\" value=\"0\" />
						<input type=button value=\"Сохранить\" class='button_input' onclick=\"
							if( $( '#title' ).val() == '' ) { 
								alert( 'Укажите название акции' ); 
								return false; 
							}
							$( '#process' ).val( 1 );
							$( '#edit' ).val( ".$actionid." );
							$( '#creatings' ).submit();
						\" />&nbsp;&nbsp;
						<input type=button value=\"Отменить\" class='button_input' onclick=\"$( '#creatings' ).submit();\" />
					</div>
				</form>
		";
	}
}

?>