<?php

if( !defined( "in_ochki" ) ) die( "You can't access this file directly" );

class ExternalQueries
{
	function run()
	{
		global $query, $lang, $main, $admin, $mysql;
		
		if( !$admin->auth || !defined( "in_ochki_admin" ) )
			return $this->runsite();
			
		if( $query->gp_post( "getwindow" ) ) {
			
			$wt = $query->gp_post( "getwindow" );
			
			if( $query->gp( "langs" ) ) {
				
				return $lang->getExternal( $wt, "langs".( $query->gp( "global" ) ? "/global" : "" ) );
				
			} else if( $query->gp( "metas" ) ) {

				$add .= "metas";
				$metas = $main->modules->gmi( "metas" );
				return $metas->getExternal( $wt, $add );
				
			} else if( $query->gp( "modules" ) ) {
				
				$add = "modules/";
				
				if( $query->gp( "list" ) ) {
					$add .= "list";
				} else if( $query->gp( "settings" ) ) {
					$add .= "settings";
				} else if( $query->gp( "news" ) ) {
					$add .= "news";
					$news = $main->modules->gmi( "news" );
					return $news->getExternal( $wt, $add );
				} else if( $query->gp( "lenses" ) ) {
					$shops = $main->modules->gmi( "lenses" );
					return $shops->getExternal( $wt, "lenses" );
				} else if( $query->gp( "actions" ) ) {
					$banners = $main->modules->gmi( "actions" );
					return $banners->getExternal( $wt, "actions".( $query->gp( "global" ) ? "/global" : "" ) );
				} else if( $query->gp( "delivery" ) ) {
					$delivery = $main->modules->gmi( "delivery" );
					return $delivery->getExternal( $wt, $add."delivery" );
				} else if( $query->gp( "catalog_admin" ) ) {
					$catalog_admin = $main->modules->gmi( "catalog_admin" );
					return $catalog_admin->getExternal( $wt, "catalog_admin".( $query->gp( "global" ) ? "/global" : "" ) );
				}
				
				return $main->modules->getExternal( $wt, $add );
				
			} else if( $query->gp( "users" ) ) {
				
				$add = "users/";
				if( $query->gp( "actypes" ) )
					$add .= "actypes";
				else if( $query->gp( "list" ) )
					$add .= "list";

				return $main->users->getExternal( $wt, $add );
				
			} else if( $query->gp( "listings" ) ) {
				
				return $main->listings->getExternal( $wt, "listings".( $query->gp( "global" ) ? "/global" : "" ) );
				
			} else if( $query->gp( "properties" ) ) {
				
				return $main->properties->getExternal( $wt, "properties".( $query->gp( "global" ) ? "/global" : "" ) );
				
			}
			
		} else if( $query->gp_post( "simplecheck" ) ) {
			
			$module = $query->gp_post( "module" );
			if( $module == 'news' ) {
				$news = $main->modules->gmi( "news" );
				return $news->getExternal( $query->gp_post( "simplecheck" ), $query->gp_post( "link" ) );
			}
			
		} else if( $query->gp_post( "deletesimplefile" ) ) {
			
			$module = $query->gp_post( "module" );
			$deletesimplefile = urldecode( $query->gp_post( "deletesimplefile" ) );
			
			@unlink( ROOT_PATH."files/upload/any/".$module."/".$deletesimplefile );
			
			return "1";
			
		} else if( $query->gp( "getblock" ) ) { // Получить блок с текстом или чем то другим
			
			$type = $query->gp( "type" );
			$toplace = $query->gp( "toplace" );
			
			switch( $type ) {
				
				case 12: // Любой файл
					$fname = $query->gp_post( "fname" );
    				if( !isset( $_FILES[$fname] ) || !file_exists( $_FILES[$fname]['tmp_name'] ) )
						return -2;
						
					$max_size = $query->gp_post( "max_size" );
    				
					$extensions = $query->gp_post( "extensions" );
					$extensions = $extensions && @strpos( $extensions, "|" ) > 0 ? @explode( "|", $extensions ) : null;
					
					$_FILES[$fname]['name'] = @addslashes( @htmlspecialchars( $_FILES[$fname]['name'] ) );
					$t = @explode( ".", $_FILES[$fname]['name'] );
					$ext = @strtolower( $t[@count( $t ) - 1] );
					
					if( strstr( $ext, 'php' ) || strstr( $ext, 'htm' ) )
						return -3;
						
					if( $extensions ) {
						if( @array_search( $ext, $extensions ) === false )
							return -4;
					}
							
					$tmp = $_FILES[$fname]['tmp_name'];
					
					if( $max_size && filesize( $tmp ) > $max_size )
						return -1;
					
					$newname = md5( $tmp.time().$_FILES[$fname]['name'] );
					$newfile = $newname.".".$ext;
					$path = ROOT_PATH."tmp/".$newfile;
					
   					@move_uploaded_file( $tmp, $path );
   					return @file_exists( $path ) ? $newfile.": ".@round( @filesize( $path ) / 1000, 2 )."Kb" : -5;
    				break;
    				
    			case 13: // Баннер
					$fname = $query->gp_post( "fname" );
    				if( !isset( $_FILES[$fname] ) || !file_exists( $_FILES[$fname]['tmp_name'] ) )
						return 0;
						
					$extensions = $query->gp_post( "extensions" ); 
					$extensions = $extensions && @strpos( $extensions, "|" ) > 0 ? @explode( "|", $extensions ) : null;
					
					$_FILES[$fname]['name'] = @addslashes( @htmlspecialchars( $_FILES[$fname]['name'] ) );
					$t = @explode( ".", $_FILES[$fname]['name'] );
					$ext = @strtolower( $t[@count( $t ) - 1] );
					
					if( strstr( $ext, 'php' ) || strstr( $ext, 'htm' ) )
						return 0;
						
					if( $extensions ) {
						if( @array_search( $ext, $extensions ) === false )
							return 0;
					} else 
						return 0;
							
					$totemp = $query->gp_post( "totemp" );
					$topath = $query->gp_post( "topath" );
					$tmp = $_FILES[$fname]['tmp_name'];
					$newname = md5( $tmp.time().$_FILES[$fname]['name'] );
					$newfile = $newname.".".$ext;
					$path = $totemp ? ROOT_PATH."tmp/".$newfile : ROOT_PATH.$topath.$newfile;
					
					$max_width = $query->gp_post( "max_width" );
    				
    				$s = @getimagesize( $tmp );
    				if( !$s || !$s[0] || !$s[1] )
    					return 0;
    				if( $max_width && $s[0] != $max_width )
    					return 0;
    				   					
    				@move_uploaded_file( $tmp, $path );
    				return file_exists( $path ) ? $newfile : 0;
				
				case 14: // Графический Файл
					$fname = $query->gp_post( "fname" );
    				if( !isset( $_FILES[$fname] ) || !file_exists( $_FILES[$fname]['tmp_name'] ) )
						return 0;
						
					$extensions = $query->gp_post( "extensions" ); 
					$extensions = $extensions && @strpos( $extensions, "|" ) > 0 ? @explode( "|", $extensions ) : null;
					
					$_FILES[$fname]['name'] = @addslashes( @htmlspecialchars( $_FILES[$fname]['name'] ) );
					$t = @explode( ".", $_FILES[$fname]['name'] );
					$ext = @strtolower( $t[@count( $t ) - 1] );
					
					if( strstr( $ext, 'php' ) || strstr( $ext, 'htm' ) )
						return 0;
						
					if( $extensions ) {
						if( @array_search( $ext, $extensions ) === false )
							return 0;
					} else 
						return 0;
							
					$totemp = $query->gp_post( "totemp" );
					$tmp = $_FILES[$fname]['tmp_name'];
					$newname = md5( $tmp.time().$_FILES[$fname]['name'] );
					$newfile = $newname.".".$ext;
					$path = $totemp ? ROOT_PATH."tmp/".$newfile : ROOT_PATH."files/upload/".$newfile;
					
    				$preview_width = @intval( $query->gp( "preview_width" ) );
    				$preview_height = @intval( $query->gp( "preview_height" ) );
    				$cut = $query->gp_post( "cut" );
    				
					$newfile_tmbl = "";
					$path_tmbl = "";
					if( $preview_width ) {
						$newfile_tmbl = $newname."_tmbl.".$ext;
						$path_tmbl = $totemp ? ROOT_PATH."tmp/".$newfile_tmbl : ROOT_PATH."files/upload/".$newfile_tmbl;
					}
					
    				$memoryNeed = ceil( intval( ( @filesize( $tmp ) ) * 16 ) / 1000000 );
        			if( $memoryNeed > 16 ) 
        				ini_set( "memory_limit", ( $memoryNeed * 16 )."M" );
    				
    				$s = @getimagesize( $tmp );
    				
    				$max_width = $query->gp_post( "max_width" );
					$max_width = $max_width ? $max_width : $s[0];
    				$max_height = $query->gp_post( "max_height" );
    				$max_height = $max_height ? $max_height : $s[0];

    				if( $s && $s[0] && $cut == 'true' ) { // Это картинка
    					
    					$ratio = ( $max_width ? $max_width : $s[0] ) / ( $max_height ? $max_height : $s[0] );
    					$src_ratio = $s[0] / $s[1];
    					
    					$same = false;
						if( $s[0] <= $max_width && $s[1] <= $max_height )
							$same = true;
							
						$w_big = $max_width;
						$h_big = $max_height;
						$w_sbig = $s[0];
						$h_sbig = $s[1];
						
						if( $same ) {
							$w_big = $s[0];
							$h_big = $s[1];
						} else if( $src_ratio > $ratio ) {
							$h_big = floor( $s[1] * $w_big / $s[0] );
						} else if( !$cut ) {
							$w_big = floor( $s[0] * $h_big / $s[1] );
						} else if( $cut ) {
							if( $src_ratio > $ratio ) {
								$w_sbig = floor( $h_sbig / ( $h_big / $w_big ) );
							} else {
								$h_sbig = floor( $w_sbig / ( $w_big / $h_big ) );
							}
						}
						
						$src_img = null;
						switch( $s[2] ) {
							case 2:
								$src_img = @imagecreatefromjpeg( $tmp );
								break;
							case 1:
								$src_img = @imagecreatefromgif( $tmp );
								break;
							case 3:
								$src_img = @imagecreatefrompng( $tmp );
								break;
							case 6:
							case 15:
								$src_img = @imagecreatefromwbmp( $tmp );
								break;
						}
						if( !$src_img )
							return 0;
						
						$dest_img_big = @imagecreatetruecolor( $w_big, $h_big );
						@imagefill( $dest_img_big, 0, 0, 0xFFFFFF );
						
						if( !@imagecopyresampled( $dest_img_big, $src_img, 0, 0, 0, 0, $w_big, $h_big, $w_sbig, $h_sbig ) ) {
							@imagedestroy( $dest_img_big );
							@imagedestroy( $src_img );
							return 0;
						}
						
						$dest_img_small = null;
						if( $preview_width ) {
							
							$w_small = $preview_width;
							$h_small = $preview_height;
							$w_ssmall = $s[0];
							$h_ssmall = $s[1];
							
							$same = false;
							if( $s[0] <= $preview_width && $s[1] <= $preview_height )
								$same = true;
							
							if( $same ) {
								$w_small = $s[0];
								$h_small = $s[1];
							} else if( $src_ratio > $ratio ) {
								$h_small = floor( $s[1] * $w_small / $s[0] );
							} else if( !$cut ) {
								$w_small = floor( $s[0] * $h_small / $s[1] );
							} else if( $cut ) {
								if( $src_ratio > $ratio ) {
									$w_ssmall = floor( $h_ssmall / ( $h_big / $w_big ) );
								} else {
									$h_ssmall = floor( $w_ssmall / ( $w_big / $h_big ) );
								}
							}
							
							$dest_img_small = @imagecreatetruecolor( $w_small, $h_small );
							@imagefill( $dest_img_small, 0, 0, 0xFFFFFF );
						
							if( !@imagecopyresampled( $dest_img_small, $src_img, 0, 0, 0, 0, $w_small, $h_small, $w_ssmall, $h_ssmall ) ) {
								@imagedestroy( $dest_img_big );
								@imagedestroy( $dest_img_small );
								@imagedestroy( $src_img );
								return 0;
							}
							
						}
						
						switch( $s[2] ) {
							case 2:
								@imagejpeg( $dest_img_big, $path );
								if( $dest_img_small )
									@imagejpeg( $dest_img_small, $path_tmbl );
								break;
							case 1:
								@imagegif( $dest_img_big, $path );
								if( $dest_img_small )
									@imagegif( $dest_img_small, $path_tmbl );
								break;
							case 3:
								@imagepng( $dest_img_big, $path );
								if( $dest_img_small )
									@imagepng( $dest_img_small, $path_tmbl );
								break;
							case 6:
							case 15:
								@imagewbmp( $dest_img_big, $path );
								if( $dest_img_small )
									@imagewbmp( $dest_img_small, $path_tmbl );
								break;
						}

						@imagedestroy( $src_img );
						@imagedestroy( $dest_img_big );
						if( $dest_img_small )
							@imagedestroy( $dest_img_small );
						
						return $newfile;
    					
    				} else { // Любой другой файл - просто копируем и возвращаем новое и старое название файла
    					
    					@move_uploaded_file( $tmp, $path );
    					return file_exists( $path ) ? $newfile : 0;
    					
    				}
    				break;
    				
    			case 15: // Картинка для товара
					$fname = $query->gp_post( "fname" );
    				if( !isset( $_FILES[$fname] ) || !file_exists( $_FILES[$fname]['tmp_name'] ) )
						return 0;
						
					$extensions = $query->gp_post( "extensions" ); 
					$extensions = $extensions && @strpos( $extensions, "|" ) > 0 ? @explode( "|", $extensions ) : null;
					
					$_FILES[$fname]['name'] = @addslashes( @htmlspecialchars( $_FILES[$fname]['name'] ) );
					$t = @explode( ".", $_FILES[$fname]['name'] );
					$ext = @strtolower( $t[@count( $t ) - 1] );
					
					if( strstr( $ext, 'php' ) || strstr( $ext, 'htm' ) )
						return 0;
						
					if( $extensions ) {
						if( @array_search( $ext, $extensions ) === false )
							return 0;
					} else 
						return 0;
							
					$tmp = $_FILES[$fname]['tmp_name'];
					$newname = md5( $tmp.time().$_FILES[$fname]['name'] );
					$newfile = $newname.".".$ext;
					$path = ROOT_PATH."tmp/".$newfile;
					
    				$preview_width = 290;
    				$preview_height = 230;
    				
					$newfile_tmbl = $newname."_tmbl.".$ext;
					$path_tmbl = ROOT_PATH."tmp/".$newfile_tmbl;
					
    				$memoryNeed = ceil( intval( ( @filesize( $tmp ) ) * 8 ) / 1000000 );
        			if( $memoryNeed > 8 ) 
        				ini_set( "memory_limit", ( $memoryNeed * 8 )."M" );
    				
    				$s = @getimagesize( $tmp );
    				
    				$max_width = 1280;
    				$max_height = 984;

    				if( $s && $s[0] ) { // Это картинка
    					
    					$ratio = $max_width / $max_height;
    					$src_ratio = $s[0] / $s[1];
    					
    					$same = false;
						if( $s[0] <= $max_width && $s[1] <= $max_height )
							$same = true;
							
						$w_big = $max_width;
						$h_big = $max_height;
						
						if( $same ) {
							$w_big = $s[0];
							$h_big = $s[1];
						} else if( $src_ratio > $ratio ) {
							$h_big = floor( $s[1] * $w_big / $s[0] );
						} else {
							$w_big = floor( $s[0] * $h_big / $s[1] );
						}
						
						$src_img = null;
						switch( $s[2] ) {
							case 2:
								$src_img = @imagecreatefromjpeg( $tmp );
								break;
							case 1:
								$src_img = @imagecreatefromgif( $tmp );
								break;
							case 3:
								$src_img = @imagecreatefrompng( $tmp );
								break;
							case 6:
							case 15:
								$src_img = @imagecreatefromwbmp( $tmp );
								break;
						}
						if( !$src_img )
							return 0;
						
						$dest_img_big = @imagecreatetruecolor( $w_big, $h_big );
						if( strtolower( $ext ) == 'png' ) {
							@imagealphablending( $dest_img_big, false );
							@imagesavealpha( $dest_img_big, true );
						} else 
							@imagefill( $dest_img_big, 0, 0, 0xFFFFFF );
						
						if( !@imagecopyresampled( $dest_img_big, $src_img, 0, 0, 0, 0, $w_big, $h_big, $s[0], $s[1] ) ) {
							@imagedestroy( $dest_img_big );
							@imagedestroy( $src_img );
							return 0;
						}
						
						$dest_img_small = null;
						if( $preview_width ) {
							
							$w_small = $preview_width;
							$h_small = $preview_height;
							
							$same = false;
							if( $s[0] <= $preview_width && $s[1] <= $preview_height )
								$same = true;
							
							if( $same ) {
								$w_small = $s[0];
								$h_small = $s[1];
							} else if( $src_ratio > $ratio ) {
								$h_small = floor( $s[1] * $w_small / $s[0] );
							} else {
								$w_small = floor( $s[0] * $h_small / $s[1] );
							}
							
							$dest_img_small = @imagecreatetruecolor( $w_small, $h_small );
							if( strtolower( $ext ) == 'png' ) {
								@imagealphablending( $dest_img_small, false );
								@imagesavealpha( $dest_img_small, true );
							} else 
								@imagefill( $dest_img_small, 0, 0, 0xFFFFFF );
						
							if( !@imagecopyresampled( $dest_img_small, $src_img, 0, 0, 0, 0, $w_small, $h_small, $s[0], $s[1] ) ) {
								@imagedestroy( $dest_img_big );
								@imagedestroy( $dest_img_small );
								@imagedestroy( $src_img );
								return 0;
							}
							
						}
						
						switch( $s[2] ) {
							case 2:
								@imagejpeg( $dest_img_big, $path );
								if( $dest_img_small )
									@imagejpeg( $dest_img_small, $path_tmbl );
								break;
							case 1:
								@imagegif( $dest_img_big, $path );
								if( $dest_img_small )
									@imagegif( $dest_img_small, $path_tmbl );
								break;
							case 3:
								@imagepng( $dest_img_big, $path );
								if( $dest_img_small )
									@imagepng( $dest_img_small, $path_tmbl );
								break;
							case 6:
							case 15:
								@imagewbmp( $dest_img_big, $path );
								if( $dest_img_small )
									@imagewbmp( $dest_img_small, $path_tmbl );
								break;
						}

						@imagedestroy( $src_img );
						@imagedestroy( $dest_img_big );
						if( $dest_img_small )
							@imagedestroy( $dest_img_small );
						
						return $newfile."^".$newfile_tmbl."^".$w_big."^".$h_big;
    					
    				} else {
    					
    					return 0;
    					
    				}
    				break;
    				
    			case 18: // Графический Файл
					$fname = $query->gp_post( "fname" );
    				if( !isset( $_FILES[$fname] ) || !file_exists( $_FILES[$fname]['tmp_name'] ) )
						return 0;
						
					$extensions = $query->gp_post( "extensions" ); 
					$extensions = $extensions && @strpos( $extensions, "|" ) > 0 ? @explode( "|", $extensions ) : null;
					
					$_FILES[$fname]['name'] = @addslashes( @htmlspecialchars( $_FILES[$fname]['name'] ) );
					$t = @explode( ".", $_FILES[$fname]['name'] );
					$ext = @strtolower( $t[@count( $t ) - 1] );
					
					if( strstr( $ext, 'php' ) || strstr( $ext, 'htm' ) )
						return 0;
						
					if( $extensions ) {
						if( @array_search( $ext, $extensions ) === false )
							return 0;
					} else 
						return 0;
							
					$totemp = $query->gp_post( "totemp" );
					$tmp = $_FILES[$fname]['tmp_name'];
					$newname = md5( $tmp.time().$_FILES[$fname]['name'] );
					$newfile = $newname.".".$ext;
					$path = $totemp ? ROOT_PATH."tmp/".$newfile : ROOT_PATH."files/upload/".$newfile;
					
    				$preview_width = 170;
    				$preview_height = 120;
    				$cut = $query->gp_post( "cut" );
    				
					$newfile_tmbl = "";
					$path_tmbl = "";
					if( $preview_width ) {
						$newfile_tmbl = $newname."_tmbl.".$ext;
						$path_tmbl = $totemp ? ROOT_PATH."tmp/".$newfile_tmbl : ROOT_PATH."files/upload/".$newfile_tmbl;
					}
					
    				$memoryNeed = ceil( intval( ( @filesize( $tmp ) ) * 16 ) / 1000000 );
        			if( $memoryNeed > 16 ) 
        				ini_set( "memory_limit", ( $memoryNeed * 16 )."M" );
    				
    				$s = @getimagesize( $tmp );
    				
    				$max_width = 600;
					$max_width = $max_width ? $max_width : $s[0];
    				$max_height = 500;
    				$max_height = $max_height ? $max_height : $s[0];

    				if( $s && $s[0] && $cut == 'true' ) { // Это картинка
    					
    					$ratio = ( $max_width ? $max_width : $s[0] ) / ( $max_height ? $max_height : $s[0] );
    					$src_ratio = $s[0] / $s[1];
    					
    					$same = false;
						if( $s[0] <= $max_width && $s[1] <= $max_height )
							$same = true;
							
						$w_big = $max_width;
						$h_big = $max_height;
						$w_sbig = $s[0];
						$h_sbig = $s[1];
						
						if( $same ) {
							$w_big = $s[0];
							$h_big = $s[1];
						} else if( $src_ratio > $ratio ) {
							$h_big = floor( $s[1] * $w_big / $s[0] );
						} else {
							$w_big = floor( $s[0] * $h_big / $s[1] );
						}
						
						$src_img = null;
						switch( $s[2] ) {
							case 2:
								$src_img = @imagecreatefromjpeg( $tmp );
								break;
							case 1:
								$src_img = @imagecreatefromgif( $tmp );
								break;
							case 3:
								$src_img = @imagecreatefrompng( $tmp );
								break;
							case 6:
							case 15:
								$src_img = @imagecreatefromwbmp( $tmp );
								break;
						}
						if( !$src_img )
							return 0;
						
						$dest_img_big = @imagecreatetruecolor( $w_big, $h_big );
						@imagefill( $dest_img_big, 0, 0, 0xFFFFFF );
						
						if( !@imagecopyresampled( $dest_img_big, $src_img, 0, 0, 0, 0, $w_big, $h_big, $w_sbig, $h_sbig ) ) {
							@imagedestroy( $dest_img_big );
							@imagedestroy( $src_img );
							return 0;
						}
						
						$dest_img_small = null;
						if( $preview_width ) {
							
							$w_small = $preview_width;
							$h_small = $preview_height;
							$w_ssmall = $s[0];
							$h_ssmall = $s[1];
							
							$same = false;
							if( $s[0] <= $preview_width && $s[1] <= $preview_height )
								$same = true;
							
							if( $same ) {
								$w_small = $s[0];
								$h_small = $s[1];
							} else if( $src_ratio > $ratio ) {
								$h_small = floor( $s[1] * $w_small / $s[0] );
							} else {
								$w_small = floor( $s[0] * $h_small / $s[1] );
							}
							
							$dest_img_small = @imagecreatetruecolor( $w_small, $h_small );
							@imagefill( $dest_img_small, 0, 0, 0xFFFFFF );
						
							if( !@imagecopyresampled( $dest_img_small, $src_img, 0, 0, 0, 0, $w_small, $h_small, $w_ssmall, $h_ssmall ) ) {
								@imagedestroy( $dest_img_big );
								@imagedestroy( $dest_img_small );
								@imagedestroy( $src_img );
								return 0;
							}
							
						}
						
						switch( $s[2] ) {
							case 2:
								@imagejpeg( $dest_img_big, $path );
								if( $dest_img_small )
									@imagejpeg( $dest_img_small, $path_tmbl );
								break;
							case 1:
								@imagegif( $dest_img_big, $path );
								if( $dest_img_small )
									@imagegif( $dest_img_small, $path_tmbl );
								break;
							case 3:
								@imagepng( $dest_img_big, $path );
								if( $dest_img_small )
									@imagepng( $dest_img_small, $path_tmbl );
								break;
							case 6:
							case 15:
								@imagewbmp( $dest_img_big, $path );
								if( $dest_img_small )
									@imagewbmp( $dest_img_small, $path_tmbl );
								break;
						}

						@imagedestroy( $src_img );
						@imagedestroy( $dest_img_big );
						if( $dest_img_small )
							@imagedestroy( $dest_img_small );
						
						return $newfile;
    					
    				} else { // Любой другой файл - просто копируем и возвращаем новое и старое название файла
    					
    					@move_uploaded_file( $tmp, $path );
    					return file_exists( $path ) ? $newfile : 0;
    					
    				}
    				break;
    				
    			case 71: // Любой файл для заявки
					$fname = $query->gp( "fname" );
					$app = $query->gp( "app" );
    				if( !isset( $_FILES[$fname] ) || !file_exists( $_FILES[$fname]['tmp_name'] ) || !$app )
						return -1;
						
					$_FILES[$fname]['name'] = @addslashes( @htmlspecialchars( $_FILES[$fname]['name'] ) );
					$t = @explode( ".", $_FILES[$fname]['name'] );
					$ext = @strtolower( $t[@count( $t ) - 1] );
					
					if( strstr( $ext, 'php' ) || strstr( $ext, 'htm' ) )
						return -2;
					
					$tmp = $_FILES[$fname]['tmp_name'];
					
					$newfile = $utils->StrToTranslite( str_replace( "'", "", str_replace( '"', '', $_FILES[$fname]['name'] ) ) );
					$path = ROOT_PATH."files/upload/apps/".$app."/".$newfile;
					
   					@move_uploaded_file( $tmp, $path );
   					return file_exists( $path ) ? $newfile."~~~".round( filesize( $path ) / 1000, 2 ) : 0;
    				break;	
    				
    				
				case 100: // По модулям
					$module = $main->modules->gmi( $query->gp( "module" ) );
					if( $module ) {
						return $module->parseExternalRequest();
					}
					break;
			}
		}
		
		return "Undefined admin query states";
	}
	
	//
	// Далее обработчик внешних запросов в рамках сайта	
	//
	
	function runsite()
	{
		global $query, $lang, $main, $utils, $mysql;

		$type = $query->gp( "type" );
		
		if( $query->gp( "getblock" ) ) { // Получить блок с текстом
			
			$toplace = $query->gp( "toplace" );
			
			switch( $type ) {
				
				case 4: // Капча
					$d = explode( "_", $toplace );
					$toplace_e = $d[0]."_check";
					
					$cap = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."capcha` WHERE 1 ORDER BY RAND()" );
		
					return $toplace."##<div style=\"background: url(".$mysql->settings['local_folder']."captcha".$cap['file']."/".md5( time().$cap['nums'] ).".jpg) no-repeat top left; width: 120px; height: 60px;\" ></div>##".$toplace_e."##".$cap['file'];
					
				case 12: // Любой файл
					$fname = $query->gp_post( "fname" );
    				if( !isset( $_FILES[$fname] ) || !file_exists( $_FILES[$fname]['tmp_name'] ) )
						return 0;
						
					$max_size = $query->gp_post( "max_size" );
    				
					$extensions = $query->gp_post( "extensions" );
					$extensions = $extensions && @strpos( $extensions, "|" ) > 0 ? @explode( "|", $extensions ) : null;
					
					$_FILES[$fname]['name'] = @addslashes( @htmlspecialchars( $_FILES[$fname]['name'] ) );
					$t = @explode( ".", $_FILES[$fname]['name'] );
					$ext = @strtolower( $t[@count( $t ) - 1] );
					
					if( strstr( $ext, 'php' ) || strstr( $ext, 'htm' ) )
						return 0;
						
					if( $extensions ) {
						if( @array_search( $ext, $extensions ) === false )
							return 0;
					} else 
						return 0;
							
					$totemp = $query->gp_post( "totemp" );
					$tmp = $_FILES[$fname]['tmp_name'];
					
					if( filesize( $tmp ) > $max_size )
						return '-1';
					
					$newname = md5( $tmp.time().$_FILES[$fname]['name'] );
					$newfile = $newname.".".$ext;
					$path = $totemp ? ROOT_PATH."tmp/".$newfile : ROOT_PATH."files/upload/".$newfile;
					
   					@move_uploaded_file( $tmp, $path );
   					return file_exists( $path ) ? $newfile.":".round( filesize( $tmp ) / 1000, 2 )."Kb" : 0;
    				break;	
    				
    			case 13: // Баннер
					$fname = $query->gp_post( "fname" );
    				if( !isset( $_FILES[$fname] ) || !file_exists( $_FILES[$fname]['tmp_name'] ) )
						return 0;
						
					$extensions = $query->gp_post( "extensions" ); 
					$extensions = $extensions && @strpos( $extensions, "|" ) > 0 ? @explode( "|", $extensions ) : null;
					
					$_FILES[$fname]['name'] = @addslashes( @htmlspecialchars( $_FILES[$fname]['name'] ) );
					$t = @explode( ".", $_FILES[$fname]['name'] );
					$ext = @strtolower( $t[@count( $t ) - 1] );
					
					if( strstr( $ext, 'php' ) || strstr( $ext, 'htm' ) )
						return 0;
						
					if( $extensions ) {
						if( @array_search( $ext, $extensions ) === false )
							return 0;
					} else 
						return 0;
							
					$totemp = $query->gp_post( "totemp" );
					$tmp = $_FILES[$fname]['tmp_name'];
					$newname = md5( $tmp.time().$_FILES[$fname]['name'] );
					$newfile = $newname.".".$ext;
					$path = $totemp ? ROOT_PATH."tmp/".$newfile : ROOT_PATH."files/upload/b_imag/".$newfile;
					
					$max_width = $query->gp_post( "max_width" );
    				//$max_height = $query->gp_post( "max_height" );
					// || $s[1] != $max_height
    				
    				$s = @getimagesize( $tmp );
    				if( !$s || !$s[0] || !$s[1] || $s[0] != $max_width )
    					return 0;
    				   					
    				@move_uploaded_file( $tmp, $path );
    				return file_exists( $path ) ? $newfile : 0;
				
				case 14: // Графический Файл
					$fname = $query->gp_post( "fname" );
    				if( !isset( $_FILES[$fname] ) || !file_exists( $_FILES[$fname]['tmp_name'] ) )
						return 0;
						
					$extensions = $query->gp_post( "extensions" ); 
					$extensions = $extensions && @strpos( $extensions, "|" ) > 0 ? @explode( "|", $extensions ) : null;
					
					$_FILES[$fname]['name'] = @addslashes( @htmlspecialchars( $_FILES[$fname]['name'] ) );
					$t = @explode( ".", $_FILES[$fname]['name'] );
					$ext = @strtolower( $t[@count( $t ) - 1] );
					
					if( strstr( $ext, 'php' ) || strstr( $ext, 'htm' ) )
						return 0;
						
					if( $extensions ) {
						if( @array_search( $ext, $extensions ) === false )
							return 0;
					} else 
						return 0;
							
					$totemp = $query->gp_post( "totemp" );
					$tmp = $_FILES[$fname]['tmp_name'];
					$newname = md5( $tmp.time().$_FILES[$fname]['name'] );
					$newfile = $newname.".".$ext;
					$path = $totemp ? ROOT_PATH."tmp/".$newfile : ROOT_PATH."files/upload/".$newfile;
					
					$max_width = $query->gp_post( "max_width" );
    				$max_height = $query->gp_post( "max_height" );
    				$preview_width = @intval( $query->gp( "preview_width" ) );
    				$preview_height = @intval( $query->gp( "preview_height" ) );
    				$cut = $query->gp_post( "cut" );
					
					$newfile_tmbl = "";
					$path_tmbl = "";
					if( $preview_width ) {
						$newfile_tmbl = $newname."_tmbl.".$ext;
						$path_tmbl = $totemp ? ROOT_PATH."tmp/".$newfile_tmbl : ROOT_PATH."files/upload/".$newfile_tmbl;
					}
    				
    				$memoryNeed = ceil( intval( ( @filesize( $tmp ) ) * 16 ) / 1000000 );
        			if( $memoryNeed > 8 )
        				ini_set( "memory_limit", ( $memoryNeed * 2 )."M" );
    				
    				$s = @getimagesize( $tmp );
    				if( $s && $s[0] && $max_width ) { // Это картинка
    					
    					$ratio = $max_width / $max_height;
    					$src_ratio = $s[0] / $s[1];
    					
    					$same = false;
						if( $s[0] <= $max_width && $s[1] <= $max_height )
							$same = true;
							
						$w_big = $max_width;
						$h_big = $max_height;
						$w_sbig = $s[0];
						$h_sbig = $s[1];
						
						if( $same ) {
							$w_big = $s[0];
							$h_big = $s[1];
						} else if( $src_ratio > $ratio ) {
							$h_big = floor( $s[1] * $w_big / $s[0] );
						} else if( !$cut ) {
							$w_big = floor( $s[0] * $h_big / $s[1] );
						} else if( $cut ) {
							if( $src_ratio > $ratio ) {
								$w_sbig = floor( $h_sbig / ( $h_big / $w_big ) );
							} else {
								$h_sbig = floor( $w_sbig / ( $w_big / $h_big ) );
							}
						}
						
						$src_img = null;
						switch( $s[2] ) {
							case 2:
								$src_img = @imagecreatefromjpeg( $tmp );
								break;
							case 1:
								$src_img = @imagecreatefromgif( $tmp );
								break;
							case 3:
								$src_img = @imagecreatefrompng( $tmp );
								break;
							case 6:
							case 15:
								$src_img = @imagecreatefromwbmp( $tmp );
								break;
						}
						if( !$src_img )
							return 0;
						
						$dest_img_big = @imagecreatetruecolor( $w_big, $h_big );
						@imagefill( $dest_img_big, 0, 0, 0xFFFFFF );
						
						if( !@imagecopyresampled( $dest_img_big, $src_img, 0, 0, 0, 0, $w_big, $h_big, $w_sbig, $h_sbig ) ) {
							@imagedestroy( $dest_img_big );
							@imagedestroy( $src_img );
							return 0;
						}
						
						$dest_img_small = null;
						if( $preview_width ) {
							
							$w_small = $preview_width;
							$h_small = $preview_height;
							$w_ssmall = $s[0];
							$h_ssmall = $s[1];
							
							$same = false;
							if( $s[0] <= $preview_width && $s[1] <= $preview_height )
								$same = true;
							
							if( $same ) {
								$w_small = $s[0];
								$h_small = $s[1];
							} else if( $src_ratio > $ratio ) {
								$h_small = floor( $s[1] * $w_small / $s[0] );
							} else if( !$cut ) {
								$w_small = floor( $s[0] * $h_small / $s[1] );
							} else if( $cut ) {
								if( $src_ratio > $ratio ) {
									$w_ssmall = floor( $h_ssmall / ( $h_big / $w_big ) );
								} else {
									$h_ssmall = floor( $w_ssmall / ( $w_big / $h_big ) );
								}
							}
							
							$dest_img_small = @imagecreatetruecolor( $w_small, $h_small );
							@imagefill( $dest_img_small, 0, 0, 0xFFFFFF );
						
							if( !@imagecopyresampled( $dest_img_small, $src_img, 0, 0, 0, 0, $w_small, $h_small, $w_ssmall, $h_ssmall ) ) {
								@imagedestroy( $dest_img_big );
								@imagedestroy( $dest_img_small );
								@imagedestroy( $src_img );
								return 0;
							}
							
						}
						
						switch( $s[2] ) {
							case 2:
								@imagejpeg( $dest_img_big, $path );
								if( $dest_img_small )
									@imagejpeg( $dest_img_small, $path_tmbl );
								break;
							case 1:
								@imagegif( $dest_img_big, $path );
								if( $dest_img_small )
									@imagegif( $dest_img_small, $path_tmbl );
								break;
							case 3:
								@imagepng( $dest_img_big, $path );
								if( $dest_img_small )
									@imagepng( $dest_img_small, $path_tmbl );
								break;
							case 6:
							case 15:
								@imagewbmp( $dest_img_big, $path );
								if( $dest_img_small )
									@imagewbmp( $dest_img_small, $path_tmbl );
								break;
						}

						@imagedestroy( $src_img );
						@imagedestroy( $dest_img_big );
						if( $dest_img_small )
							@imagedestroy( $dest_img_small );
						
						return $path_tmbl ? $path_tmbl : $newname;
    					
    				} else { // Любой другой файл - просто копируем и возвращаем новое и старое название файла
    					
    					@move_uploaded_file( $tmp, $path );
    					return file_exists( $path ) ? $newfile : 0;
    					
    				}
    				break;
    				
    			case 15: // Картинка для товара
					$fname = $query->gp_post( "fname" );
    				if( !isset( $_FILES[$fname] ) || !file_exists( $_FILES[$fname]['tmp_name'] ) )
						return 0;
						
					$extensions = $query->gp_post( "extensions" ); 
					$extensions = $extensions && @strpos( $extensions, "|" ) > 0 ? @explode( "|", $extensions ) : null;
					
					$_FILES[$fname]['name'] = @addslashes( @htmlspecialchars( $_FILES[$fname]['name'] ) );
					$t = @explode( ".", $_FILES[$fname]['name'] );
					$ext = @strtolower( $t[@count( $t ) - 1] );
					
					if( strstr( $ext, 'php' ) || strstr( $ext, 'htm' ) )
						return 0;
						
					if( $extensions ) {
						if( @array_search( $ext, $extensions ) === false )
							return 0;
					} else 
						return 0;
							
					$tmp = $_FILES[$fname]['tmp_name'];
					$newname = md5( $tmp.time().$_FILES[$fname]['name'] );
					$newfile = $newname.".".$ext;
					$path = ROOT_PATH."tmp/".$newfile;
					
    				$preview_width = 290;
    				$preview_height = 230;
    				
					$newfile_tmbl = $newname."_tmbl.".$ext;
					$path_tmbl = ROOT_PATH."tmp/".$newfile_tmbl;
					
    				$memoryNeed = ceil( intval( ( @filesize( $tmp ) ) * 8 ) / 1000000 );
        			if( $memoryNeed > 8 ) 
        				ini_set( "memory_limit", ( $memoryNeed * 8 )."M" );
    				
    				$s = @getimagesize( $tmp );
    				
    				$max_width = 1280;
    				$max_height = 984;

    				if( $s && $s[0] ) { // Это картинка
    					
    					$ratio = $max_width / $max_height;
    					$src_ratio = $s[0] / $s[1];
    					
    					$same = false;
						if( $s[0] <= $max_width && $s[1] <= $max_height )
							$same = true;
							
						$w_big = $max_width;
						$h_big = $max_height;
						
						if( $same ) {
							$w_big = $s[0];
							$h_big = $s[1];
						} else if( $src_ratio > $ratio ) {
							$h_big = floor( $s[1] * $w_big / $s[0] );
						} else {
							$w_big = floor( $s[0] * $h_big / $s[1] );
						}
						
						$src_img = null;
						switch( $s[2] ) {
							case 2:
								$src_img = @imagecreatefromjpeg( $tmp );
								break;
							case 1:
								$src_img = @imagecreatefromgif( $tmp );
								break;
							case 3:
								$src_img = @imagecreatefrompng( $tmp );
								break;
							case 6:
							case 15:
								$src_img = @imagecreatefromwbmp( $tmp );
								break;
						}
						if( !$src_img )
							return 0;
						
						$dest_img_big = @imagecreatetruecolor( $w_big, $h_big );
						if( strtolower( $ext ) == 'png' ) {
							@imagealphablending( $dest_img_big, false );
							@imagesavealpha( $dest_img_big, true );
						} else 
							@imagefill( $dest_img_big, 0, 0, 0xFFFFFF );
						
						if( !@imagecopyresampled( $dest_img_big, $src_img, 0, 0, 0, 0, $w_big, $h_big, $s[0], $s[1] ) ) {
							@imagedestroy( $dest_img_big );
							@imagedestroy( $src_img );
							return 0;
						}
						
						$dest_img_small = null;
						if( $preview_width ) {
							
							$w_small = $preview_width;
							$h_small = $preview_height;
							
							$same = false;
							if( $s[0] <= $preview_width && $s[1] <= $preview_height )
								$same = true;
							
							if( $same ) {
								$w_small = $s[0];
								$h_small = $s[1];
							} else if( $src_ratio > $ratio ) {
								$h_small = floor( $s[1] * $w_small / $s[0] );
							} else {
								$w_small = floor( $s[0] * $h_small / $s[1] );
							}
							
							$dest_img_small = @imagecreatetruecolor( $w_small, $h_small );
							if( strtolower( $ext ) == 'png' ) {
								@imagealphablending( $dest_img_small, false );
								@imagesavealpha( $dest_img_small, true );
							} else 
								@imagefill( $dest_img_small, 0, 0, 0xFFFFFF );
						
							if( !@imagecopyresampled( $dest_img_small, $src_img, 0, 0, 0, 0, $w_small, $h_small, $s[0], $s[1] ) ) {
								@imagedestroy( $dest_img_big );
								@imagedestroy( $dest_img_small );
								@imagedestroy( $src_img );
								return 0;
							}
							
						}
						
						switch( $s[2] ) {
							case 2:
								@imagejpeg( $dest_img_big, $path );
								if( $dest_img_small )
									@imagejpeg( $dest_img_small, $path_tmbl );
								break;
							case 1:
								@imagegif( $dest_img_big, $path );
								if( $dest_img_small )
									@imagegif( $dest_img_small, $path_tmbl );
								break;
							case 3:
								@imagepng( $dest_img_big, $path );
								if( $dest_img_small )
									@imagepng( $dest_img_small, $path_tmbl );
								break;
							case 6:
							case 15:
								@imagewbmp( $dest_img_big, $path );
								if( $dest_img_small )
									@imagewbmp( $dest_img_small, $path_tmbl );
								break;
						}

						@imagedestroy( $src_img );
						@imagedestroy( $dest_img_big );
						if( $dest_img_small )
							@imagedestroy( $dest_img_small );
						
						return $newfile."^".$newfile_tmbl."^".$w_big."^".$h_big;
    					
    				} else {
    					
    					return 0;
    					
    				}
    				break;
    				
    			case 18: // Графический Файл
					$fname = $query->gp_post( "fname" );
    				if( !isset( $_FILES[$fname] ) || !file_exists( $_FILES[$fname]['tmp_name'] ) )
						return 0;
						
					$extensions = $query->gp_post( "extensions" ); 
					$extensions = $extensions && @strpos( $extensions, "|" ) > 0 ? @explode( "|", $extensions ) : null;
					
					$_FILES[$fname]['name'] = @addslashes( @htmlspecialchars( $_FILES[$fname]['name'] ) );
					$t = @explode( ".", $_FILES[$fname]['name'] );
					$ext = @strtolower( $t[@count( $t ) - 1] );
					
					if( strstr( $ext, 'php' ) || strstr( $ext, 'htm' ) )
						return 0;
						
					if( $extensions ) {
						if( @array_search( $ext, $extensions ) === false )
							return 0;
					} else 
						return 0;
							
					$totemp = $query->gp_post( "totemp" );
					$tmp = $_FILES[$fname]['tmp_name'];
					$newname = md5( $tmp.time().$_FILES[$fname]['name'] );
					$newfile = $newname.".".$ext;
					$path = $totemp ? ROOT_PATH."tmp/".$newfile : ROOT_PATH."files/upload/".$newfile;
					
    				$preview_width = 170;
    				$preview_height = 120;
    				$cut = $query->gp_post( "cut" );
    				
					$newfile_tmbl = "";
					$path_tmbl = "";
					if( $preview_width ) {
						$newfile_tmbl = $newname."_tmbl.".$ext;
						$path_tmbl = $totemp ? ROOT_PATH."tmp/".$newfile_tmbl : ROOT_PATH."files/upload/".$newfile_tmbl;
					}
					
    				$memoryNeed = ceil( intval( ( @filesize( $tmp ) ) * 16 ) / 1000000 );
        			if( $memoryNeed > 16 ) 
        				ini_set( "memory_limit", ( $memoryNeed * 16 )."M" );
    				
    				$s = @getimagesize( $tmp );
    				
    				$max_width = 600;
					$max_width = $max_width ? $max_width : $s[0];
    				$max_height = 500;
    				$max_height = $max_height ? $max_height : $s[0];

    				if( $s && $s[0] && $cut == 'true' ) { // Это картинка
    					
    					$ratio = ( $max_width ? $max_width : $s[0] ) / ( $max_height ? $max_height : $s[0] );
    					$src_ratio = $s[0] / $s[1];
    					
    					$same = false;
						if( $s[0] <= $max_width && $s[1] <= $max_height )
							$same = true;
							
						$w_big = $max_width;
						$h_big = $max_height;
						$w_sbig = $s[0];
						$h_sbig = $s[1];
						
						if( $same ) {
							$w_big = $s[0];
							$h_big = $s[1];
						} else if( $src_ratio > $ratio ) {
							$h_big = floor( $s[1] * $w_big / $s[0] );
						} else {
							$w_big = floor( $s[0] * $h_big / $s[1] );
						}
						
						$src_img = null;
						switch( $s[2] ) {
							case 2:
								$src_img = @imagecreatefromjpeg( $tmp );
								break;
							case 1:
								$src_img = @imagecreatefromgif( $tmp );
								break;
							case 3:
								$src_img = @imagecreatefrompng( $tmp );
								break;
							case 6:
							case 15:
								$src_img = @imagecreatefromwbmp( $tmp );
								break;
						}
						if( !$src_img )
							return 0;
						
						$dest_img_big = @imagecreatetruecolor( $w_big, $h_big );
						@imagefill( $dest_img_big, 0, 0, 0xFFFFFF );
						
						if( !@imagecopyresampled( $dest_img_big, $src_img, 0, 0, 0, 0, $w_big, $h_big, $w_sbig, $h_sbig ) ) {
							@imagedestroy( $dest_img_big );
							@imagedestroy( $src_img );
							return 0;
						}
						
						$dest_img_small = null;
						if( $preview_width ) {
							
							$w_small = $preview_width;
							$h_small = $preview_height;
							$w_ssmall = $s[0];
							$h_ssmall = $s[1];
							
							$same = false;
							if( $s[0] <= $preview_width && $s[1] <= $preview_height )
								$same = true;
							
							if( $same ) {
								$w_small = $s[0];
								$h_small = $s[1];
							} else if( $src_ratio > $ratio ) {
								$h_small = floor( $s[1] * $w_small / $s[0] );
							} else {
								$w_small = floor( $s[0] * $h_small / $s[1] );
							}
							
							$dest_img_small = @imagecreatetruecolor( $w_small, $h_small );
							@imagefill( $dest_img_small, 0, 0, 0xFFFFFF );
						
							if( !@imagecopyresampled( $dest_img_small, $src_img, 0, 0, 0, 0, $w_small, $h_small, $w_ssmall, $h_ssmall ) ) {
								@imagedestroy( $dest_img_big );
								@imagedestroy( $dest_img_small );
								@imagedestroy( $src_img );
								return 0;
							}
							
						}
						
						switch( $s[2] ) {
							case 2:
								@imagejpeg( $dest_img_big, $path );
								if( $dest_img_small )
									@imagejpeg( $dest_img_small, $path_tmbl );
								break;
							case 1:
								@imagegif( $dest_img_big, $path );
								if( $dest_img_small )
									@imagegif( $dest_img_small, $path_tmbl );
								break;
							case 3:
								@imagepng( $dest_img_big, $path );
								if( $dest_img_small )
									@imagepng( $dest_img_small, $path_tmbl );
								break;
							case 6:
							case 15:
								@imagewbmp( $dest_img_big, $path );
								if( $dest_img_small )
									@imagewbmp( $dest_img_small, $path_tmbl );
								break;
						}

						@imagedestroy( $src_img );
						@imagedestroy( $dest_img_big );
						if( $dest_img_small )
							@imagedestroy( $dest_img_small );
						
						return $newfile;
    					
    				} else { // Любой другой файл - просто копируем и возвращаем новое и старое название файла
    					
    					@move_uploaded_file( $tmp, $path );
    					return file_exists( $path ) ? $newfile : 0;
    					
    				}
    				break;
    				
    			case 40: // Загрузка из множественной загрузки
    			
    				$input = @fopen("php://input", "r");
			        $temp = @tmpfile();
       				$realSize = @stream_copy_to_stream($input, $temp);
       				@fclose($input);
        	
       				if ($realSize != (int)$_SERVER["CONTENT_LENGTH"]){            
           				return -3;
       				}
        				
       				$extensions = @urldecode( $query->gp( "extensions" ) );
					$extensions = $extensions && @strpos( $extensions, "|" ) > 0 ? @explode( "|", $extensions ) : null;
						
					$fname = $query->gp( "fname" );
					$fname = @addslashes( @htmlspecialchars( $query->gp( $fname ) ) );
						
					$t = @explode( ".", $fname );
					$ext = @strtolower( $t[@count( $t ) - 1] );
					if( $extensions )
						if( @array_search( $ext, $extensions ) === false )
							return -2;
							
					$newfile = md5( $tmp.time().$fname );
					$path = ROOT_PATH."tmp/".$newfile.".".$ext;
        
        			$target = fopen($path, "w");        
   	    			@fseek($temp, 0, SEEK_SET);
       				@stream_copy_to_stream($temp, $target);
       				@fclose($target);
       				
       				$newfile_tmbl = "";
       				$preview_width = 150;
    				$preview_height = 150;
					
					$newfile_tmbl = $newfile."_tmbl.".$ext;
					$path_tmbl = ROOT_PATH."tmp/".$newfile_tmbl;
    				
    				$s = @getimagesize( $path );
    				if( $s && $s[0] && $preview_width ) { // Это картинка
    					
    					$ratio = 1;
    					$src_ratio = $s[0] / $s[1];
    					
						$src_img = null;
						switch( $s[2] ) {
							case 2:
								$src_img = @imagecreatefromjpeg( $path );
								break;
							case 1:
								$src_img = @imagecreatefromgif( $path );
								break;
							case 3:
								$src_img = @imagecreatefrompng( $path );
								break;
							case 6:
							case 15:
								$src_img = @imagecreatefromwbmp( $path );
								break;
						}
						if( !$src_img )
							return -1;
						
						$w_small = $preview_width;
						$h_small = $preview_height;
						$w_ssmall = $s[0];
						$h_ssmall = $s[1];
							
						$same = false;
						if( $s[0] <= $preview_width && $s[1] <= $preview_height )
							$same = true;
							
						if( $same ) {
							$w_small = $s[0];
							$h_small = $s[1];
						} else if( $src_ratio > $ratio ) {
							$h_small = floor( $s[1] * $w_small / $s[0] );
						} else {
							if( $src_ratio > $ratio ) {
								$w_ssmall = floor( $h_ssmall / ( $h_big / $w_big ) );
							} else {
								$h_ssmall = floor( $w_ssmall / ( $w_big / $h_big ) );
							}
						}
							
						$dest_img_small = @imagecreatetruecolor( $w_small, $h_small );
						@imagefill( $dest_img_small, 0, 0, 0xFFFFFF );
						
						if( !@imagecopyresampled( $dest_img_small, $src_img, 0, 0, 0, 0, $w_small, $h_small, $w_ssmall, $h_ssmall ) ) {
							@imagedestroy( $dest_img_small );
							@imagedestroy( $src_img );
							return -1;
						}
							
						switch( $s[2] ) {
							case 2:
								@imagejpeg( $dest_img_small, $path_tmbl );
								break;
							case 1:
								@imagegif( $dest_img_small, $path_tmbl );
								break;
							case 3:
								@imagepng( $dest_img_small, $path_tmbl );
								break;
							case 6:
							case 15:
								@imagewbmp( $dest_img_small, $path_tmbl );
								break;
						}

						@imagedestroy( $src_img );
						@imagedestroy( $dest_img_small );
    				}
        				
       				return @file_exists( $path ) && @file_exists( $path_tmbl ) ? $newfile.".".$ext."~".$newfile_tmbl : 0;
    				
    			case 70: // Любой файл для проекта
    			
        			$fname = $query->gp( "fname" );
    				if( !isset( $_FILES[$fname] ) || !file_exists( $_FILES[$fname]['tmp_name'] ) )
					return 0;
						
				$_FILES[$fname]['name'] = @addslashes( @htmlspecialchars( $_FILES[$fname]['name'] ) );
				$t = @explode( ".", $_FILES[$fname]['name'] );
				$ext = @strtolower( $t[@count( $t ) - 1] );
					
				if( strstr( $ext, 'php' ) || strstr( $ext, 'htm' ) )
					return 0;
					
				$tmp = $_FILES[$fname]['tmp_name'];
					
				$newfile = md5( time().mt_rand(111111,999999).$_FILES[$fname]['name']);
				$path = ROOT_PATH."tmp/".$newfile.".".$ext;
                                @move_uploaded_file( $tmp, $path );
                                
                                $ret = array( 
                                    'or' => @str_replace( "'", "", @str_replace( '"', '', $_FILES[$fname]['name'] ) ),
                                    'now' => $newfile.".".$ext,
                                    'size' => @round( @filesize( $path ) / 1000, 2 )."Mb"
                                );
					
   				
   				return @file_exists( $path ) ? json_encode($ret) : 0;
    				
			case 100: // По модулям
				$module = $main->modules->gmi( $query->gp( "module" ) );
				if( $module ) {
					return $module->parseExternalRequest();
				}
				break;
			}
			
		} else if( $query->gp( "checkdata" ) ) { // Проверить данные
			
			switch( $type ) {
				case 1: // Проверка легальности почтового адреса
				
					$email = $query->gp( "v" );
					
					return $email && function_exists( "filter_var" ) && @filter_var( $email, FILTER_VALIDATE_EMAIL ) ? 1 : $utils->checkEmail( $email );
					
				case 2: // Проверка существования пользователя с указанным именем
				
					$email = $query->gp( "v" );
					
					return $email && !$mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."users` WHERE `ulogin`='".$email."'" ) ? 1 : 0;
					
				case 3: // Проверка существования пользователя с указанным ID
				case 6: // Получение блока с проверкой
				
					$id = $query->gp( "v" );
					
					$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."users` WHERE `id`='".$id."' AND ( `level`=7 OR `level`=4 )" );
					
					if( $type == 3 ) 
						return $id && $r ? $id : 0;
					else if( $r ) {
						
						return "
							<div id='sub_".$r['id']."'>Пользователь № ".$r['id'].". <b>".$r['name']."</b> (".$r['ulogin'].").</b>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"#\" onclick=\"
								if( !confirm( 'Вы уверены?' ) ) return false;
								$( '#sub_".$r['id']."' ).remove();
								for( var a = 0; a < sc.length; a++ ) {
									if( sc[a] == ".$r['id']." ) {
										sc[a] = 0;
										break;
									}
								}
								return false;
							\">Удалить</a></div>
						";
						
					}
					
				case 5: // Проверка существования пользователя с указанным именем 
				case 7: // Получение блока с проверкой
				
					$email = $query->gp( "v" );
					
					$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."users` WHERE `ulogin`='".$email."' AND ( `level`=7 OR `level`=4 )" );
					
					if( $type == 5 ) 
						return $email && $r ? $r['id'] : 0;
					else if( $r ) {
						
						return "
							<div id='sub_".$r['id']."'>Пользователь № ".$r['id'].". <b>".$r['name']."</b> (".$r['ulogin'].").</b>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"#\" onclick=\"
								if( !confirm( 'Вы уверены?' ) ) return false;
								$( '#sub_".$r['id']."' ).remove();
								for( var a = 0; a < sc.length; a++ ) {
									if( sc[a] == ".$r['id']." ) {
										sc[a] = 0;
										break;
									}
								}
								return false;
							\">Удалить</a></div>
						";
						
					}
				
				case 4: // Капча
				
					$v = $query->gp( "v" );
					$l = $query->gp( "l" );
				
					return is_numeric( intval( $v ) ) && $mysql->mq( "SELECT `file` FROM `".$mysql->t_prefix."capcha` WHERE `nums`=".intval( $v )." AND `file`=".$l ) ? 1 : 0;
			}
			
		}
		
		return "Undefined site query states";
	}
}

?>