<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulecatalog_admin extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	var $gl_dbase_string = "`shop`.";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
        
        function getOpravasForSelect( $selected = array() )
        {
            global $mysql, $query, $utils, $admin, $lang, $main;
            
            $t = "";
            $a = $mysql->mqm( "SELECT * FROM `shop`.`shop_tovar` WHERE 1" );			
            while( $r = @mysql_fetch_assoc( $a ) ) {
                if( !$r['name'] )
                    continue;
                $t .= "<option value='".$r['id']."'".( $this->searchArrayForValue( $selected, $r['id'] ) ? " selected" : "" ).">".$r['name']."</option>";
            }
            return $t;            
        }
	
	//
	// Далее администраторская область
	//
        
        function getImportScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;
                
		$catalog = $main->modules->gmi( "catalog" );
                
                $selectedElement = 1;
                
                if( $query->gp( "addfile" ) && isset( $_FILES['newfile'] ) ) {
                    $data = iconv( "WINDOWS-1251", "UTF-8", file_get_contents( $_FILES['newfile']['tmp_name'] ) );
                    if( !$data )
                        return "<h1>Ошибка импорта файла</h1>Невозможно прочитать файл...";
                    
                    $brands = $main->listings->getListingElementsArrayAll( 7, '', true );
                    $vids = $main->listings->getListingElementsArrayAll( 4, '', true );
                    $meterial = $main->listings->getListingElementsArrayAll( 5, '', true );
                    $colors = $main->listings->getListingElementsArrayAll( 3, '', true );
                    $forma = $main->listings->getListingElementsArrayAll( 2, '', true );
                    $sizes = $main->listings->getListingElementsArrayAll( 27, '', true );
                    $types = $main->listings->getListingElementsArrayAll( 1, '', true );
                    
                    $addArray = array();
                    
                    $tt = explode( "\n", $data );
                    $t = "<h1>Импортирование файла «".$_FILES['newfile']['name']."»...</h1>";
                    $c = 0;
                    foreach( $tt as $v ) {
                        $c++;
                        $v = trim( $v );
                        $cc = explode( ";", $v );
                        
                        $article = trim( $cc[0] );
                        $article_root = trim( $cc[1] );
                        $name = trim( $cc[2] );
                        $brand_field = trim( $cc[3] );
                        $type_field = trim( $cc[4] );
                        $meterial_field = trim( $cc[5] );
                        $color_field = trim( $cc[6] );
                        $forma_field = trim( $cc[7] );
                        $size_field = trim( $cc[8] );
                        $vid_field = trim( $cc[9] );
                        $digits_field = trim( $cc[10] );
                        
                        $add = array();
                        
                        if( !$article )
                            continue;
                        $r = $mysql->mq( "SELECT `id` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `article`='".$article."'" );
                        if( $r ) {
                            $t .= $c.". Товар с артикулем ".$article." (".$name.") уже есть в базе данных под номером ".$r['id'].".<br/>";
                            continue;
                        }
                        
                        if( isset( $addArray[$article] ) ) {
                            $t .= $c.". Товар с артикулем ".$article." (".$name.") уже есть в списке импорта.<br/>";
                            continue;
                        }
                        
                        $add['name'] = $name;
                        $add['article'] = $article;
                        $add['article_root'] = $article_root;
                        
                        $error = false;
                        
                        $brand_found = false;
                        foreach( $brands as $k => $v ) { if( $v['root'] ) continue;
                            if( mb_strtolower( $lang->gp( $v['value'], true ), "UTF-8" ) == mb_strtolower( $brand_field, "UTF-8" ) ) {
                                $brand_found = true;
                                $add['brand'] = $k;
                                break;
                            }
                        }
                        if( !$brand_found ) {
                            $t .= $c.". У товара с артикулем ".$article." (".$name.") не найден бренд. Указан ".$brand_field."<br/>";
                            $error = true;
                        }
                        
                        $types_found = false;
                        foreach( $types as $k => $v ) { if( $v['root'] ) continue;
                            $vv = explode( "/", $type_field );
                            $local_found = false;
                            foreach( $vv as $ext ) {
                                if( mb_strtolower( $lang->gp( $v['value'], true ), "UTF-8" ) == mb_strtolower( trim( $ext ), "UTF-8" ) ) {
                                    $local_found = true;
                                    if( !isset( $add['types'] ) )
                                        $add['types'] = array();
                                    array_push( $add['types'], $k );
                                    break;
                                }
                            }   
                            if( $local_found ) {
                                $types_found = true;
                                //break;
                            }
                        }
                        if( !$types_found ) {
                            $t .= $c.". У товара с артикулем ".$article." (".$name.") не найден тип оправы. Указан ".$type_field."<br/>";
                            $error = true;
                        }
                        
                        $meterial_found = false;
                        foreach( $meterial as $k => $v ) { if( $v['root'] ) continue;
                            if( mb_strtolower( $lang->gp( $v['value'], true ), "UTF-8" ) == mb_strtolower( $meterial_field, "UTF-8" ) ) {
                                $meterial_found = true;
                                $add['material'] = $k;
                                break;
                            }
                        }
                        if( !$meterial_found ) {
                            $t .= $c.". У товара с артикулем ".$article." (".$name.") не найден материал. Указан ".$meterial_field."<br/>";
                            $error = true;
                        }
                        
                        $colors_found = false;
                        foreach( $colors as $k => $v ) { if( $v['root'] ) continue;
                            if( mb_strtolower( $lang->gp( $v['value'], true ), "UTF-8" ) == mb_strtolower( $color_field, "UTF-8" ) ) {
                                $colors_found = true;
                                $add['color'] = $k;
                                break;
                            }
                        }
                        if( !$colors_found ) {
                            $t .= $c.". У товара с артикулем ".$article." (".$name.") не найден цвет. Указан ".$color_field."<br/>";
                            $error = true;
                        }
                        
                        $forma_found = false;
                        foreach( $forma as $k => $v ) { if( $v['root'] ) continue;
                            if( mb_strtolower( $lang->gp( $v['value'], true ), "UTF-8" ) == mb_strtolower( $forma_field, "UTF-8" ) ) {
                                $forma_found = true;
                                $add['forma'] = $k;
                                break;
                            }
                        }
                        if( !$forma_found ) {
                            $t .= $c.". У товара с артикулем ".$article." (".$name.") не найдена форма. Указан ".$forma_field."<br/>";
                            $error = true;
                        }
                        
                        $sizes_found = false;
                        foreach( $sizes as $k => $v ) { if( $v['root'] ) continue;
                            if( mb_strtolower( $lang->gp( $v['value'], true ), "UTF-8" ) == mb_strtolower( $size_field, "UTF-8" ) ) {
                                $sizes_found = true;
                                $add['size'] = $k;
                                break;
                            }
                        }
                        if( !$sizes_found ) {
                            $t .= $c.". У товара с артикулем ".$article." (".$name.") не найден размер. Указан ".$size_field."<br/>";
                            $error = true;
                        }
                        
                        
                        $vid_found = false;
                        foreach( $vids as $k => $v ) { if( $v['root'] ) continue;
                            if( mb_strtolower( $lang->gp( $v['value'], true ), "UTF-8" ) == mb_strtolower( $vid_field, "UTF-8" ) ) {
                                $vid_found = true;
                                $add['vid'] = $k;
                                break;
                            }
                        }
                        if( !$vid_found ) {
                            $t .= $c.". У товара с артикулем ".$article." (".$name.") не найден вид. Указан ".$vid_field."<br/>";
                            $error = true;
                        }
                        
                        $vv = explode( "/", $digits_field );
                        if( !$digits_field || count( $vv ) != 3 ) {
                            $t .= $c.". У товара с артикулем ".$article." (".$name.") не найден точный размер или он указан неверно. Указан ".$digits_field."<br/>";
                            $error = true;
                        } else
                            $add['digits'] = $vv;
                        
                        if( $error )
                            continue;
                        
                        $add['files'] = $this->findImportFiles( $article );
                        
                        $addArray[$article] = $add;
                    }
                    
                    $brands_text = "";
                    foreach( $brands as $v ) { if( $v['root'] ) continue;
                        $brands_text .= ( $brands_text ? ", " : "" ).mb_strtolower( $lang->gp( $v['value'], true ), "UTF-8" );
                    }
                    
                    $vids_text = "";
                    foreach( $vids as $v ) { if( $v['root'] ) continue;
                        $vids_text .= ( $vids_text ? ", " : "" ).mb_strtolower( $lang->gp( $v['value'], true ), "UTF-8" );
                    }
                    
                    $meterial_text = "";
                    foreach( $meterial as $v ) { if( $v['root'] ) continue;
                        $meterial_text .= ( $meterial_text ? ", " : "" ).mb_strtolower( $lang->gp( $v['value'], true ), "UTF-8" );
                    }
                    
                    $colors_text = "";
                    foreach( $colors as $v ) { if( $v['root'] ) continue;
                        $colors_text .= ( $colors_text ? ", " : "" ).mb_strtolower( $lang->gp( $v['value'], true ), "UTF-8" );
                    }
                    
                    $forma_text = "";
                    foreach( $forma as $v ) { if( $v['root'] ) continue;
                        $forma_text .= ( $forma_text ? ", " : "" ).mb_strtolower( $lang->gp( $v['value'], true ), "UTF-8" );
                    }
                    
                    $sizes_text = "";
                    foreach( $sizes as $v ) { if( $v['root'] ) continue;
                        $sizes_text .= ( $sizes_text ? ", " : "" ).mb_strtolower( $lang->gp( $v['value'], true ), "UTF-8" );
                    }
                    
                    $types_text = "";
                    foreach( $types as $v ) { if( $v['root'] ) continue;
                        $types_text .= ( $types_text ? ", " : "" ).mb_strtolower( $lang->gp( $v['value'], true ), "UTF-8" );
                    }
                    
                    $ret = "Импортируется:<br/>";
                    foreach( $addArray as $ar => $add ) {
                        $ret .= "Артикль <b>".$ar."</b>. Имя <b>".$add['name']."</b>. Все указано нормально. Файлы:";
                        foreach( $add['files'] as $num => $is ) {
                            $ret .= " ".$num;
                        }
                        if( !count( $add['files'] ) )
                            $ret .= " нет";
                        $ret .= "<br/>";
                    }
                    
                    return "<div style='margin-top: 20px; border-bottom: 2px solid #000;'><h3>Имейте ввиду, что в базе данных содержатся следующие характеристики добавляемых товаров (регистр символов НЕ имеет значения):</h3></div>"
                            . "<div style='margin-top: 5px;'><b>Бренды</b>: ".$brands_text."</div>"
                            . "<div style='margin-top: 5px;'><b>Типы оправы</b>: ".$types_text."</div>"
                            . "<div style='margin-top: 5px;'><b>Материалы</b>: ".$meterial_text."</div>"
                            . "<div style='margin-top: 5px;'><b>Цвета</b>: ".$colors_text."</div>"
                            . "<div style='margin-top: 5px;'><b>Формы</b>: ".$forma_text."</div>"
                            . "<div style='margin-top: 5px;'><b>Размеры</b>: ".$sizes_text."</div>"
                            . "<div style='margin-top: 5px;'><b>Виды</b>: ".$vids_text."</div>".$t.$ret;
                }
                
                $t = "
                    <h1>Импорт товаров</h1>
			
			<form action=\"".LOCAL_FOLDER."admin/".$path."/import/addfile\" method=POST enctype='multipart/form-data' style='margin: 0px; padding: 0px; margin-top: 5px; margin-bottom: 5px;' id='filter_form'>
				Выборите локальный файл CSV:<br/>
                                <input type=file name='newfile' id='newfile' style='margin-top: 10px;' /><br/>
                                <input type=button value='Добавить новый файл' style='float: left; height: 25px; width: 200px; margin-top: 10px;' onclick=\"
                                    if( $( '#newfile' ).val() ) {
                                        $( '#filter_form' ).submit();
                                    }
                                \" />
			</form>
			";
                
                return $t;
        }
        
        function findImportFiles( $article )
        {
            global $mysql, $query, $utils, $admin, $lang, $main;
            
            $article = strtolower( $article );
            
            $found = array();
            $dh = @opendir( "import/" );
            while( $dh && ( $file = @readdir( $dh ) ) !== false ) {
                if( $file !== '.' && $file !== '..' ) {
                    $vv = explode( ".", strtolower( $file ) );
                    $ext = $vv[count( $vv ) - 1];
                    if( $ext == 'jpg' || $ext == 'png' || $ext == 'gif' || $ext == 'jpeg' ) {
                        for( $a = 1; $a <= 4; $a++ ) {
                            if( $file == $article."_".$a.".".$ext ) {
                                $found[$a] = true;
                            }
                        }
                    }
                }
            }
            
            return $found;
        }
	
	function getAdminScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;
                
                if( $query->gp( "import" ) ) {
                    return $this->getImportScreen( $selectedElement, $path );
                }
		
		$catalog = $main->modules->gmi( "catalog" );
		
		if( $query->gp( "createtovar" ) && $query->gp( "process" ) ) {
			
			$name = isset( $_POST['name'] ) && $_POST['name'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", str_replace( '"', "&quot;", $_POST['name'] ) )  ) : '';
			$article = isset( $_POST['article'] ) && $_POST['article'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['article'] )  ) : '';
			//$guid = isset( $_POST['guid'] ) && $_POST['guid'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['guid'] )  ) : '';
			$vendor = $query->gp( "vendor" );
			//$razd = $query->gp( "razd" );
			$price = $query->gp( "price" );
			//$currency_type = $query->gp( "currency_type" );
			$order = $query->gp( "order" );
			$popular = $query->gp( "popular" );
			$popular = is_numeric( $popular ) ? $popular : 0;
			$strings_set = isset( $_POST['strings_set'] ) && $_POST['strings_set'] ? $_POST['strings_set'] : '';
			$images_set = isset( $_POST['images_set'] ) && $_POST['images_set'] ? $_POST['images_set'] : '';
			
			//$razd_data = $main->listings->getListingElementById( 22, $razd, true );
			//$root = $razd_data['root'] ? $razd_data['root'] : $razd;
			//$subroot = $razd_data['root'] ? $razd : 0;
			
			$cd = time();
			
			$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` VALUES(
				0,
				'".$name."',
				'".$article."',
				'',
				0,
				0,
				0,
				'".$order."',
				1,
				'".$vendor."',
				'".$price."',
				0,
				'".$popular."',
				".$cd."
			);" );
			
			$r = $mysql->mq( "SELECT `id` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `vendor`=".$vendor." AND `date`=".$cd." ORDER BY `id` DESC" );
			$id = $r['id'];
			
			$properties = $main->properties->getCurrentList();
		
			foreach( $properties as $prop_id => $prop_data ) {
				if( $prop_id == 11136 ) {
					$ssadasdasdasdasd_set = explode( ";;;", $strings_set );
					foreach( $ssadasdasdasdasd_set as $v ) {
						if( !$v )
							continue;
						$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
							0,
							36,
							".$id.",
							'".$v."'
						);" );
					}
					continue;
				} else if( $prop_id == 16 ) { // Галерея фотографий
					$ssadasdasdasdasd_set = array();
					$tt = explode( ";;;", $images_set );
					foreach( $tt as $v ) {
						$ttt = explode( "src=\"", $v );
						$ttt = explode( "\">", $ttt[1] );
						$adsfasdf = explode( "/", $ttt[0] );
						$file = $adsfasdf[count( $adsfasdf ) - 1];
						$new = strpos( $ttt[0], "tmp" ) > 0 ? true : false;
						$ssadasdasdasdasd_set[$file] = array( 'file' => $file, 'new' => $new );
					}
					
					foreach( $ssadasdasdasdasd_set as $v ) {
						if( !$v || !isset( $v['file'] ) || !$v['file'] || !$v['new'] )
							continue;
						$affd = explode( ".", $v['file'] );
						$fname = str_replace( "_tmbl", "", $affd[0] );
						$ext = $affd[1];
						@copy( ROOT_PATH."tmp/".$fname.".".$ext, ROOT_PATH."files/upload/goods/".$fname.".".$ext );
						@unlink( ROOT_PATH."tmp/".$fname.".".$ext );
						@copy( ROOT_PATH."tmp/".$v['file'], ROOT_PATH."files/upload/goods/thumbs/".$fname.".".$ext );
						@unlink( ROOT_PATH."tmp/".$v['file'] );
						$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
							0,
							16,
							".$id.",
							'".$fname.".".$ext."'
						);" );
					}	
					
					continue;
					
				}
				
				if( !isset( $_POST['param_'.$prop_id] ) )
					continue;
				if( isset( $_POST['param_'.$prop_id] ) && $_POST['param_'.$prop_id] ) {
					if( is_array( $_POST['param_'.$prop_id] ) ) {
						$prop_value = $_POST['param_'.$prop_id];
					} else {
						$prop_value = str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['param_'.$prop_id] )  );
						$prop_value = $prop_value == 'on' ? 1 : $prop_value;
					}
				} else 
					$prop_value = "";
				if( $prop_value === '' || !count( $prop_value ) )
					continue;
					
				if( $prop_id == 20 ) {
					$ll = $main->listings->getListingElementsArray( 27, 0, false, '',  true );
					foreach( $ll as $k => $v ) {
						$tttt = explode( "~", $v['additional_info'] );
						$xxx = explode( "-", $tttt[0] );
						if( intval( $prop_value ) >= $xxx[0] && $prop_value <= $xxx[1] ) {
							$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
								0,
								19,
								".$id.",
								'".$v['id']."'
							);" );
							break;
						}
					}
				}
					
				if( is_array( $prop_value ) ) {
					
					foreach( $prop_value as $v ) {
						
						$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
							0,
							".$prop_id.",
							".$id.",
							'".$v."'
						);" );
						
					}
					
				} else {
					if( $prop_id == 14 || $prop_id == 15 ) { // Основное изображение или Изображение для наложения на фото
						$affd = explode( ".", $prop_value );
						$fname = str_replace( "_tmbl", "", $affd[0] );
						$ext = $affd[1];
						@copy( ROOT_PATH."tmp/".$fname.".".$ext, ROOT_PATH."files/upload/goods/".$fname.".".$ext );
						@unlink( ROOT_PATH."tmp/".$fname.".".$ext );
						@copy( ROOT_PATH."tmp/".$prop_value, ROOT_PATH."files/upload/goods/thumbs/".$fname.".".$ext );
						@unlink( ROOT_PATH."tmp/".$prop_value );
                                                $mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
    						0,
    						".$prop_id.",
						".$id.",
						'".$fname.".".$ext."'
                                                );" );
					} else {
                                            $mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
						0,
						".$prop_id.",
						".$id.",
						'".$prop_value."'
                                            );" );
                                        }
					
				}
				
			}
			
			$query->setProperty( "createtovar", 0 );
			$query->setProperty( "process", 0 );
			
		} else if( $query->gp( "edit" ) && $query->gp( "process" ) && $query->gp( "goodsid" ) ) {
			
			//echo print_r( $_POST, true );
			
			$id = $query->gp( "goodsid" );
			
			$name = isset( $_POST['name'] ) && $_POST['name'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", str_replace( '"', "&quot;", $_POST['name'] ) )  ) : '';
			$article = isset( $_POST['article'] ) && $_POST['article'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['article'] )  ) : '';
			//$guid = isset( $_POST['guid'] ) && $_POST['guid'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['guid'] )  ) : '';
			$vendor = $query->gp( "vendor" );
			//$razd = $query->gp( "razd" );
			$price = $query->gp( "price" );
			//$currency_type = $query->gp( "currency_type" );
			$order = $query->gp( "order" );
			$popular = $query->gp( "popular" );
			$popular = is_numeric( $popular ) ? $popular : 0;
			//$colors_set = isset( $_POST['colors_set'] ) && $_POST['colors_set'] ? $_POST['colors_set'] : '';
			$strings_set = isset( $_POST['strings_set'] ) && $_POST['strings_set'] ? $_POST['strings_set'] : '';
			$images_set = isset( $_POST['images_set'] ) && $_POST['images_set'] ? $_POST['images_set'] : '';
			
			$properties = $main->properties->getCurrentList();
			$props = $main->properties->getPropertiesOfGood( $id );
			
			foreach( $properties as $prop_id => $prop_data ) {
				
				if( $prop_data['type'] == 4 && !isset( $_POST['param_'.$prop_id] ) ) {
					$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=".$prop_id );
					continue;
				}
				
				if( $prop_id == 36111 ) {
					$ssadasdasdasdasd_set = explode( ";;;", $strings_set );
					$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=36" );
					foreach( $ssadasdasdasdasd_set as $v ) {
						if( !$v )
							continue;
						$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
							0,
							36,
							".$id.",
							'".$v."'
						);" );
					}
					continue;
				} else if( $prop_id == 16 ) {
					$ssadasdasdasdasd_set = array();
					$tt = explode( ";;;", $images_set );
					foreach( $tt as $v ) {
						$ttt = explode( "src=\"", $v );
						$ttt = explode( "\">", $ttt[1] );
						$adsfasdf = explode( "/", $ttt[0] );
						$file = $adsfasdf[count( $adsfasdf ) - 1];
						$new = strpos( $ttt[0], "tmp" ) > 0 ? true : false;
						$ssadasdasdasdasd_set[$file] = array( 'file' => $file, 'new' => $new );
					}
					
					foreach( $ssadasdasdasdasd_set as $v ) {
						if( !$v || !isset( $v['file'] ) || !$v['file'] || !$v['new'] )
							continue;
						$affd = explode( ".", $v['file'] );
						$fname = str_replace( "_tmbl", "", $affd[0] );
						$ext = $affd[1];
						@copy( ROOT_PATH."tmp/".$fname.".".$ext, ROOT_PATH."files/upload/goods/".$fname.".".$ext );
						@unlink( ROOT_PATH."tmp/".$fname.".".$ext );
						@copy( ROOT_PATH."tmp/".$v['file'], ROOT_PATH."files/upload/goods/thumbs/".$fname.".".$ext );
						@unlink( ROOT_PATH."tmp/".$v['file'] );
						$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
							0,
							16,
							".$id.",
							'".$fname.".".$ext."'
						);" );
					}
					
					foreach( $props as $p ) {
						if( $p['prop_id'] != 16 || isset( $ssadasdasdasdasd_set[$p['value']] ) )
							continue;
						@unlink( ROOT_PATH."files/upload/goods/".$p['value'] );
						@unlink( ROOT_PATH."files/upload/goods/thumbs/".$p['value'] );
						$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=16 AND `id`=".$p['id'] );
					}					
					
					continue;
					
				} else if( $prop_id == 14 || $prop_id == 15 ) { // Основное изображение или Изображение для наложения на фото
					
					if( !isset( $_POST['param_'.$prop_id] ) || !$_POST['param_'.$prop_id] )
						continue;
						
					foreach( $props as $p ) {
						if( $p['prop_id'] != $prop_id )
							continue;
						@unlink( ROOT_PATH."files/upload/goods/".$p['value'] );
						@unlink( ROOT_PATH."files/upload/goods/thumbs/".$p['value'] );
						$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=".$prop_id." AND `id`=".$p['id'] );
					}
						
					if( $_POST['param_'.$prop_id] == '-' ) { // Просто удаляем
						continue;
					}
					
					$affd = explode( ".", $_POST['param_'.$prop_id] );
					$fname = str_replace( "_tmbl", "", $affd[0] );
					$ext = $affd[1];
					@copy( ROOT_PATH."tmp/".$fname.".".$ext, ROOT_PATH."files/upload/goods/".$fname.".".$ext );
					@unlink( ROOT_PATH."tmp/".$fname.".".$ext );
					@copy( ROOT_PATH."tmp/".$_POST['param_'.$prop_id], ROOT_PATH."files/upload/goods/thumbs/".$fname.".".$ext );
					@unlink( ROOT_PATH."tmp/".$_POST['param_'.$prop_id] );
					$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
						0,
						".$prop_id.",
						".$id.",
						'".$fname.".".$ext."'
					);" );
					continue;
					
				}
				
				if( !isset( $_POST['param_'.$prop_id] ) )
					continue;
					
				if( isset( $_POST['param_'.$prop_id] ) && $_POST['param_'.$prop_id] ) {
					if( is_array( $_POST['param_'.$prop_id] ) ) {
						$prop_value = $_POST['param_'.$prop_id];
					} else {
						$prop_value = str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['param_'.$prop_id] )  );
						$prop_value = $prop_value == 'on' ? 1 : $prop_value;
					}
				} else 
					$prop_value = "";
				
				if( $prop_id == 20 ) {
					$ll = $main->listings->getListingElementsArray( 27, 0, false, '',  true );
					foreach( $ll as $k => $v ) {
						$tttt = explode( "~", $v['additional_info'] );
						$xxx = explode( "-", $tttt[0] );
						if( intval( $prop_value ) >= $xxx[0] && $prop_value <= $xxx[1] ) {
							$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=19" );
							$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
								0,
								19,
								".$id.",
								'".$v['id']."'
							);" );
							break;
						}
					}
				}
					
				if( $prop_value && is_array( $prop_value ) ) {
					
					foreach( $props as $p )
						if( $p['prop_id'] == $prop_id )
							$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=".$prop_id );
							
					foreach( $prop_value as $v ) {
						
						$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
							0,
							".$prop_id.",
							".$id.",
							'".$v."'
						);" );
						
					}
					
				} else {
				
					$saved = false;
					foreach( $props as $p ) {
						if( $p['prop_id'] == $prop_id ) {
							if( !$prop_value ) {
								$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=".$prop_id );
							} else {
								$mysql->mu( "UPDATE ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` SET `value`='".$prop_value."' WHERE `tovar_id`=".$id." AND `prop_id`=".$prop_id );
							}
							$saved = true;
							break;
						}
					}
					if( !$prop_value || $saved )
						continue;
									
					$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
						0,
						".$prop_id.",
						".$id.",
						'".$prop_value."'
					);" );
				}
			}
			
			//$razd_data = $main->listings->getListingElementById( 22, $razd, true );
			//$root = $razd_data['root'] ? $razd_data['root'] : $razd;
			//$subroot = $razd_data['root'] ? $razd : 0;
			
			$mysql->mu( "UPDATE ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` SET

				`name`='".$name."',
				`article`='".$article."',
				
				`order`='".$order."',
				
				
				`order`='".$order."',
				`vendor`='".$vendor."',
				`price`='".$price."',
				
				`popular`='".$popular."'
				
			WHERE `id`=".$id );
			
			$query->setProperty( "goodsid", 0 );
			$query->setProperty( "edit", 0 );
			$query->setProperty( "process", 0 );
			
		} else if( $query->gp( "turntovar" ) ) {
			
			$id = $query->gp( "turntovar" );
			$ep = $mysql->mq( "SELECT `view` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `id`=".$id );			
			if( $ep )
				$mysql->mu( "UPDATE ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` SET `view`=".( $ep['view'] ? "0" : "1" )." WHERE `id`=".$id );
				
		} else if( $query->gp( "delete" ) ) {
			
			$id = $query->gp( "delete" );
			$ep = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `id`=".$id );			
			
			if( $ep ) {
				
				$props = $main->properties->getPropertiesOfGood( $id );
				foreach( $props as $p ) {
					if( $p['prop_id'] >= 14 && $p['prop_id'] <= 16 && $p['value'] ) {
						@unlink( ROOT_PATH."files/upload/goods/thumbs/".$p['value'] );
						@unlink( ROOT_PATH."files/upload/goods/".$p['value'] );
					}
				}
				
				$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `id`=".$id );
				$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_prices` WHERE `tovar_id`=".$id );
				$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id );
				
				// Удаляем все товары с ID из корзины
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."basket` WHERE `good`=".$id );
			}
			
		} else if( $query->gp( "copyitem" ) && $query->gp( "process" ) && $query->gp( "goodsid" ) ) {
			
			$id = $query->gp( "goodsid" );
			$data = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `id`=".$id );
			
			$name = isset( $_POST['name'] ) && $_POST['name'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", str_replace( '"', "&quot;", $_POST['name'] ) )  ) : '';
			$article = isset( $_POST['article'] ) && $_POST['article'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['article'] )  ) : '';
			//$guid = isset( $_POST['guid'] ) && $_POST['guid'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['guid'] )  ) : '';
			$price = $query->gp( "price" );
			
			$cd = time();
			
			$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` VALUES(
				0,
				'".$name."',
				'".$article."',
				'".$data['guid']."',
				'".$data['root']."',
				'".$data['r']."',
				'".$data['sub_r']."',
				'".$data['order']."',
				1,
				'".$data['vendor']."',
				'".$price."',
				'".$data['currency_type']."',
				'0',
				".$cd."
			);" );
			
			$r = $mysql->mq( "SELECT `id` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `vendor`=".$data['vendor']." AND `r`=".$data['r']." AND `sub_r`=".$data['sub_r']." AND `article`='".$article."' ORDER BY `id` DESC" );
			$new_id = $r['id'];
			
			$props = $main->properties->getPropertiesOfGood( $id );
			foreach( $props as $p ) {
				if( $p['prop_id'] >= 14 && $p['prop_id'] <= 16 ) {
					continue;
				} else {
					$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
						0,
						".$p['prop_id'].",
						".$new_id.",
						'".$p['value']."'
					);" );
				}
			}
			
			$query->setProperty( "goodsid", 0 );
			$query->setProperty( "copyitem", 0 );
			$query->setProperty( "process", 0 );
			
		} else if( $query->gp( "cloneitem" ) && $query->gp( "process" ) && $query->gp( "goodsid" ) ) {
			
			$id = $query->gp( "goodsid" );
			$data = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `id`=".$id );
			
			$article = isset( $_POST['article'] ) && $_POST['article'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['article'] )  ) : '';
			//$guid = isset( $_POST['guid'] ) && $_POST['guid'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['guid'] )  ) : '';
			$price = $query->gp( "price" );
			$color = $query->gp( "color" );
			
			$cd = time();
			
			$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` VALUES(
				0,
				'',
				'".$article."',
				'',
				'".$id."',
				0,
				0,
				'".$data['order']."',
				1,
				'".$data['vendor']."',
				'".$price."',
				'".$data['currency_type']."',
				0,
				".$data['date']."
			);" );
			
			$r = $mysql->mq( "SELECT `id` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `root`=".$id." AND `article`='".$article."' ORDER BY `id` DESC" );
			$new_id = $r['id'];
			
			$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
				0,
				1,
				".$new_id.",
				'".$color."'
			);" );
			
			$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(
				0,
				1,
				".$new_id.",
				'1'
			);" );
			
			$query->setProperty( "goodsid", 0 );
			$query->setProperty( "cloneitem", 0 );
			$query->setProperty( "process", 0 );
		}
		
		$selectedElement = 1;
		
		$page = $query->gp( "page" );
		$page = $page > 1 ? $page : 1;
		
		//$razdel = $query->gp( "razdel" );
		//$razdel = $razdel && is_numeric( $razdel ) ? $razdel : 0;		
		$brand = $query->gp( "brand" );
		$brand = $brand && is_numeric( $brand ) ? $brand : 0;
		$add = $query->gp( "add" );
		$add = $add && is_numeric( $add ) ? $add : 0;
		$searchstring = trim( urldecode( isset( $_POST['searchstring'] ) && $_POST['searchstring'] ? $_POST['searchstring'] : ( $query->gp( "searchstring" ) ? $query->gp( "searchstring" ) : '' ) ) );
		$searchstring = $searchstring ? str_replace( '"', "&quot;", str_replace( "'", "", $searchstring ) ) : '';
		
		$getLink = ( $razdel ? "/razdel".$razdel : "" ).( $brand ? "/brand".$brand : "" ).( $searchstring ? "/searchstring!".urlencode( $searchstring ) : "" ).( $add ? "/add".$add : "" );
		
		if( $query->gp( "createtovar" ) ) {
			
			$t = $this->getExternalNewtovar( $path.$getLink.( $page > 1 ? "/page".$page : "" ) );
			if( $t )
				return $t;
			
		} else if( $query->gp( "edit" ) ) {
			
			$t = $this->getExternalEdittovar( $path.$getLink.( $page > 1 ? "/page".$page : "" ), $query->gp( "edit" ) );
			if( $t )
				return $t;
				
		} else if( $query->gp( "copyitem" ) ) {
			
			$t = $this->getExternalCopytovar( $path.$getLink.( $page > 1 ? "/page".$page : "" ), $query->gp( "copyitem" ) );
			if( $t )
				return $t;
				
		} else if( $query->gp( "cloneitem" ) ) {
			
			$t = $this->getExternalClonetovar( $path.$getLink.( $page > 1 ? "/page".$page : "" ), $query->gp( "cloneitem" ) );
			if( $t )
				return $t;
				
		}
		
		$where = '`root`=0';
		
		if( $razdel )
			$where .= ( $where ? " AND " : "" )."(`r`=".$razdel." OR `sub_r`=".$razdel.")";
			
		if( $brand )
			$where .= ( $where ? " AND " : "" )."(`vendor`=".$brand.")";
			
		if( $searchstring )
			$where .= ( $where ? " AND " : "" )."(`name` LIKE '%".$searchstring."%' OR `article` LIKE '%".$searchstring."%' OR `guid` LIKE '%".$searchstring."%' OR `price` LIKE '%".$searchstring."%' OR `id` LIKE '%".$searchstring."%')";
			
		if( $add == 2 )
			$where .= ( $where ? " AND " : "" )."(`view`=1)";
		else if( $add == 3 )
			$where .= ( $where ? " AND " : "" )."(`article`='')";
			
		if( !$where )
			$where = "1";
			
		$items_count = $mysql->getTableRecordsCount( $this->gl_dbase_string."`".$mysql->t_prefix."tovar`", $where );
		
		$maxonpage = 50;
		$mysqlLimits = "";			
		if( $items_count > $maxonpage && $add != 1 && $add != 4 ) {
			
			$pagesCount = ceil( $items_count / $maxonpage );
			
			if( $page > $pagesCount )
				$page = $pagesCount;
			
			$startFrom = ( $page - 1 ) * $maxonpage;
			$mysqlLimits = " LIMIT ".$startFrom.",".$maxonpage;

			$pages = "<div class='pages_block'>Страницы: ";
				
			$p = array();
			for( $a = 1; $a <= $pagesCount; $a++ ) {
				$p[$a] = ( $a > 1 ? "&nbsp;" : "" ).( $page == $a ? 
					"<span>".$a."</span>" : 
					"<a href=\"".LOCAL_FOLDER."admin/".$path.$getLink."/page".$a."\">".$a."</a>" );
			}
			
			$tp = "";
			if( count( $p ) >= 10 ) {
				$tp = $p[$page];
				$a = $page - 1;
				while( isset( $p[$a] ) && $a > $page - 5 )
					$tp = $p[$a--].$tp;
				if( ++$a > 1 ) {
					if( $a > 2 )
						$tp = "&nbsp;...&nbsp;".$tp;
					$tp = $p[1].$tp;
				}
				$a = $page + 1;
				while( isset( $p[$a] ) && $a < $page + 5 )
					$tp .= $p[$a++];
				if( --$a < $pagesCount ) {
					if( $a < $pagesCount - 1 )
						$tp .= "&nbsp;...&nbsp;";
					$tp .= $p[$pagesCount];
				}
			} else {
				foreach( $p as $v )
					$tp .= $v;
			}
				
			$pages .= $tp."&nbsp;&nbsp;".( $page > 1 ? "[<a href=\"".LOCAL_FOLDER."admin/".$path.$getLink."/page".( $page - 1 )."\"><<</a>]" : "" ).( $page < $pagesCount ? ( $page > 1 ? " | " : "" )."[<a href=\"".LOCAL_FOLDER."admin/".$path.$getLink."/page".( $page + 1 )."\">>></a>]" : "" )."</div>";
		}
		
		$t = "
			<style>
				img.logotype { max-width: 70px; }
				div.div_fields { margin-top: 5px; }
				img.tovarLogo { max-width: 120px; float: left; }
				img.color { width: 30px; height: 30px; margin-right: 1px; margin-bottom: 1px; }
				
				div.pages_block { margin-top: 10px; margin-bottom: 10px; text-align: center; font-size: 14px; font-weight: 600; }
				
				div.color { display: inline-block; position: relative; width: 20px; height: 20px; margin-right: 2px; margin-left: 2px; margin-bottom: 4px; border: 1px solid #fff; cursor: pointer; -webkit-transition: all 0.2s ease-in-out; -moz-transition: all 0.2s ease-in-out; -ms-transition: all 0.2s ease-in-out; -o-transition: all 0.2s ease-in-out; transition: all 0.2s ease-in-out; -webkit-transform: translateZ(0); }
				div.color:hover { border: 1px solid #444; }
			</style>
		
			<h1>Товары</h1>
			
			<form action=\"".LOCAL_FOLDER."admin/".$path."\" method=POST style='margin: 0px; padding: 0px; margin-top: 5px; margin-bottom: 5px;' id='filter_form'>
				<table cellspacing=0 cellpadding=0 border=0>
					<tr>
						<td>
							Фильтр по бренду:<br>
							<select name='brand' id='brand'>
								".$main->listings->getListingForSelecting( 7, $brand, 0, "<option value=0".( !$brand ? " selected" : "" ).">Не использовать</option>", "", false, '', true )."
							</select>
						</td>
						<td style='padding-left: 5px;'>
							Дополнительный фильтр:<br>
							<select name='add' id='add'>
								<option value=0".( !$add ? " selected" : "" ).">Не использовать</option>
								<option value=1".( $add == 1 ? " selected" : "" ).">Только без фото</option>
								<option value=2".( $add == 2 ? " selected" : "" ).">Только включенные</option>
								<option value=3".( $add == 3 ? " selected" : "" ).">Только БЕЗ артикля</option>
							</select>
						</td>
						<td style='padding-left: 5px;'>
							Поиск по строке (по имени, артикулу, ID и цене):<br>
							<input type=text name='searchstring' id='searchstring' value='".$searchstring."' onkeypress=\"var code = processKeyPress( event ); if( code == 13 ) { $( '#filter_form' ).submit(); }\" style='width: 250px;' /> <input type=button value='Очистить' onclick=\"$( '#searchstring' ).val( '' ); $( '#filter_form' ).submit();\" />
						</td>
					</tr>
					<tr>
						<td style='padding-top: 5px;'>
							<input type=submit value='Применить фильтры' />							
						</td>
					</tr>
				</table>
			</form>
			
			<input type=button value='Создать новый товар' style='float: left; height: 25px; width: 200px; margin-bottom: 10px;' onclick=\"document.location='".LOCAL_FOLDER."admin/".$path.$getLink."/createtovar';\" />
			
			".$pages."
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%;'>
				<tr class='list_table_header'>
					<td width=40>
						ID
					</td>
					<td width=45% style='text-align: left;'>
						Название, изображние, артикул
					</td>
					<td width=15%>
						Цвета
					</td>
					<td width=5%>
						Бренд
					</td>
					<td width=10%>
						Цена
					</td>
					<td width=7%>
						Дата добавления
					</td>
					<td width=3%>
						В наличии?
					</td>
					<td width=3%>
						Популярность
					</td>
					<td width=5%>
						Порядок вывода
					</td>
					<td width=110>
						Опции
					</td>
				</tr>
		";
		
		$vendorsList = $main->listings->getListingElementsArraySpec( 7, "`order` DESC, `id` ASC", "", 0, true );
		//$moneyTypes = $main->listings->getListingElementsArraySpec( 21, "`order` DESC, `id` ASC", "", 0, true );
		//$razdels = $main->listings->getListingElementsArrayAll( 22, "", true );
		$ColorsList = $main->listings->getListingElementsArraySpec( 3, "`order` DESC, `id` ASC", "", 0, true );
		
		$counter = 0;
		$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE ".$where." ORDER BY `order` ASC, `id` ASC".$mysqlLimits );			
		while( $r = @mysql_fetch_assoc( $a ) ) {
			
			$props = $main->properties->getPropertiesOfGood( $r['id'] );
			$tovarImage = $this->getElementByData( $props, "prop_id", 14 );
			if( $add == 1 && $tovarImage )
				continue;
			$tovarImage = $tovarImage ? "<img src=\"".$mysql->settings['local_folder']."files/upload/goods/thumbs/".$tovarImage['value']."\" class='tovarLogo' />" : "<img src=\"".$mysql->settings['local_folder']."images/no_image_admin.png\" class='tovarLogo' />";
			
			$avalaible = $this->getElementByData( $props, "prop_id", 10 );
			
			$colors = "";
			$i = 0;
			$p = $this->getElementByData( $props, "prop_id", 1 );
			if( $p && $p['value'] ) {
				$price = $utils->digitsToRazryadi( $r['price'] )." руб.";
				$titleName = is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'];
				$colors .= "<div class='color' style='background-color: #".$ColorsList[$p['value']]['additional_info'].";' title='".$titleName.", стоимость ".$price."' onclick=\"urlmove('".LOCAL_FOLDER."admin/".$path.$getLink.( $page > 1 ? "/page".$page : "" ).( $add ? "/add".$add : "" )."/edit".$r['id']."');\"></div>";
			}

			$aa = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `root`=".$r['id']." ORDER BY `order` ASC, `id` ASC" );			
			while( $rr = @mysql_fetch_assoc( $aa ) ) {
				$cprops = $main->properties->getPropertiesOfGood( $rr['id'] );
				$p = $this->getElementByData( $cprops, "prop_id", 1 );
				$c_avalaible = $this->getElementByData( $cprops, "prop_id", 10 );
				if( $p && $p['value'] ) {
					$price = $utils->digitsToRazryadi( $rr['price'] )." руб.";
					$titleName = is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'];
					$colors .= "<div class='color' id='color_".$rr['id']."' style='background-color: #".$ColorsList[$p['value']]['additional_info'].";' title='".$titleName.", стоимость ".$price.( !$c_avalaible || !$c_avalaible['value'] ? ", нет в наличии" : "" )."' onclick=\"switchMenuColor( $( this ) );\">
						".( !$c_avalaible || !$c_avalaible['value'] ? "<span class='out'>x</span>" : "" )."
						<div class='colorOptions'>
							<a href='#' title='Установить новую цену' onclick=\"
								var newprice = prompt( 'Новая цена для «".$r['name'].". ".$titleName."»', '' );
								if( newprice == undefined )
									return false;
								processSimpleAsyncReqForModuleAdmin( 'catalog_admin', '111', '&id=".$rr['id']."&newprice=' + newprice, 'updateTitleFor( $( \'#color_".$rr['id']."\' ), \'".$titleName.", стоимость \' + data + \' руб.\' );' );
								return false;
							\">Установить новую цену</a>
							<a href='".LOCAL_FOLDER."admin/".$path.$getLink.( $page > 1 ? "/page".$page : "" ).( $add ? "/add".$add : "" )."/edit".$rr['id']."'>Редактировать</a>
							<a href='#' title='Удалить' onclick=\"
								if( !confirm( 'Вы уверены?' ) ) { return false; }
								processSimpleAsyncReqForModuleAdmin( 'catalog_admin', '9', '&id=".$rr['id']."', '$( \'#color_".$rr['id']."\' ).remove();' );
							\">Удалить</a>
						</div>
					</div>";
				}
			}
			
			$colors .= "<div style='clear: both;'></div>
			
			<script>
				function switchMenuColor( elem )
				{
					$( elem ).find( '.colorOptions' ).toggle();
				}
				
				function updateTitleFor( elem, value )
				{
					$( elem ).attr( 'title', value );
				}
			</script>
			
			<style>
				span.out { position: absolute; color: #fff; font-size: 18px; top: -3px; left: 6px; }
				div.colorOptions { display: none; position: absolute; top: 22px; left: 22px; border: 1px solid #222; background-color: #ddd; padding: 10px; white-space: nowrap; text-align: left; }
				div.colorOptions a { display: block; color: #000; font-size: 12px; text-decoration: none; }
				div.colorOptions a:hover { text-decoration: underline; }
			</style>
			
			";
			
			$t .= "
				<tr class='list_table_element'".( !$r['view'] ? " style='background-color: #fcd9d9;'" : "" ).">
					<td valign=middle>
						".$r['id']."
					</td>
					<td style='text-align: left;' valign=middle>
						<a href=\"".LOCAL_FOLDER."admin/".$path.$getLink.( $page > 1 ? "/page".$page : "" ).( $add ? "/add".$add : "" )."/edit".$r['id']."\">".$tovarImage."</a>
						<div style='padding-left: 125px;'>
							<a href=\"".LOCAL_FOLDER."admin/".$path.$getLink.( $page > 1 ? "/page".$page : "" ).( $add ? "/add".$add : "" )."/edit".$r['id']."\"><strong>".$r['name']."</strong></a>
							<div style='float: right;'>
								<a href=\"#\" onclick=\"
									var newprice = prompt( '".$r['name']."', '' );
									if( newprice == undefined )
										return false;
									processSimpleAsyncReqForModuleAdmin( 'catalog_admin', '1', '&id=".$r['id']."&newprice=' + newprice, '$( \'#label_".$r['id']."\' ).html( data );' );
									return false;
								\">Установить цену</a>
								<br>
								<a href=\"#\" onclick=\"
									var newarticle = prompt( '".$r['name']."', '".$r['article']."' );
									if( newarticle == undefined )
										return false;
									processSimpleAsyncReqForModuleAdmin( 'catalog_admin', '11', '&id=".$r['id']."&newarticle=' + newarticle, '$( \'#article_".$r['id']."\' ).html( data );' );
									return false;
								\">Установить артикул</a>
								".( !$r['root'] ? "<br>
								<a href=\"".LOCAL_FOLDER."admin/".$path.$getLink.( $page > 1 ? "/page".$page : "" ).( $add ? "/add".$add : "" )."/copyitem".$r['id']."\">Копировать</a>
								<br>
								<a href=\"".LOCAL_FOLDER."admin/".$path.$getLink.( $page > 1 ? "/page".$page : "" ).( $add ? "/add".$add : "" )."/cloneitem".$r['id']."\">Добавить другой цвет</a>" : "" )."
							</div>
							
							<div class='div_fields'>						
								Артикул: <span id='article_".$r['id']."'>".( $r['article'] ? $r['article'] : "не указан" )."</span>
							</div>
						</div>
					</td>
					<td valign=middle align=center>
						".$colors."
					</td>
					<td valign=middle>
						".( isset( $vendorsList[$r['vendor']] ) ? ( $vendorsList[$r['vendor']]['image'] ? "<img src=\"/files/upload/listings/".$vendorsList[$r['vendor']]['image']."\" class='logotype' />" : $lang->gp( $vendorsList[$r['vendor']]['value'], true ) ) : "Не выбран" )."
					</td>
					<td valign=middle nowrap>
						<label id='label_".$r['id']."'>".$utils->digitsToRazryadi( $r['price'] )."</label> руб.
					</td>
					<td valign=middle>
						".date( "d/m/Y", $r['date'] )."
					</td>
					<td>
						".( $avalaible && $avalaible['value'] ? "Да" : 'Нет' )."
					</td>
					<td valign=middle>
						".$r['popular']."
					</td>
					<td valign=middle>
						".$r['order']."
					</td>
					<td valign=middle nowrap>						
						<a href=\"".LOCAL_FOLDER."admin/".$path.$getLink.( $page > 1 ? "/page".$page : "" ).( $add ? "/add".$add : "" )."/turntovar".$r['id']."\">".( $r['view'] ? "Выключить" : "Включить" )."</a><br>
						<a href=\"".LOCAL_FOLDER."admin/".$path.$getLink.( $page > 1 ? "/page".$page : "" ).( $add ? "/add".$add : "" )."/edit".$r['id']."\">Редактировать</a><br>
						<a href=\"".LOCAL_FOLDER."admin/".$path.$getLink.( $page > 1 ? "/page".$page : "" ).( $add ? "/add".$add : "" )."/delete".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены? Восстановлению не подлежит...' ) ) { return false; } return true;\">Удалить</a>
					</td>
				</tr>
			";
			$counter++;
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=11>
						Всего товаров (на странице/в списке): ".$counter."/".$items_count."
					</td>
				</tr>
		</table>
		".$pages;
		
		return $t;
	}
	
	function getExternal( $wt, $link )
	{
		global $query, $mysql;
		
		return "Unknown image query";
	}
	
	function getExternalNewtovar( $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;
		
		$catalog = $main->modules->gmi( "catalog" );
		
		$name = isset( $_POST['name'] ) && $_POST['name'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", str_replace( '"', "&quot;", $_POST['name'] ) )  ) : '';
		$article = isset( $_POST['article'] ) && $_POST['article'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", str_replace( '"', "&quot;", $_POST['article'] ) )  ) : '';
		//$guid = isset( $_POST['guid'] ) && $_POST['guid'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", str_replace( '"', "&quot;", $_POST['guid'] ) )  ) : '';
		$vendor = $query->gp( "vendor" );
		//$razd = $query->gp( "razd" );
		$price = $query->gp( "price" );
		$price = $price ? $price : 0;
		//$currency_type = $query->gp( "currency_type" );
		//$currency_type = $currency_type ? $currency_type : 206;
		
		$inner = "
				<p>
					Название товара: <br>
					<input type=text name=\"name\" id=\"name\" value=\"".$name."\" class='text_input' />
				</p>
				<p>
					Артикул товара: <br>
					<input type=text name=\"article\" id=\"article\" value=\"".$article."\" class='text_input' />
				</p>
				<p>
					Бренд:<br>
					<select name='vendor' id='vendor' class='select_input'>
						".$main->listings->getListingForSelecting( 7, $vendor, 0, "<option value=0".( !$vendor ? " selected" : "" ).">Не выбран</option>", "", false, '', true )."
					</select>
				</p>
				<p>
					Стоимость товара: <br>
					<input type=text name=\"price\" id=\"price\" value=\"".$price."\" class='text_input' style='width: 100px;' />
				</p>
				<p>
					Порядок вывода в списках: <small style='color: #ff0000;'>(чем меньше число, тем выше(раньше) выводится)</small> <br>
					<input type=text name=\"order\" id=\"order\" value=\"500\" class='text_input' />
				</p>
				<p>
					Популярность: <small style='color: #ff0000;'>(формируется автоматически, исходя из кол-ва реальных заказов, но можно установить вручную)</small> <br>
					<input type=text name=\"popular\" id=\"popular\" value=\"0\" class='text_input' />
				</p>
				
				<h1 style='border-top: 1px dashed #ff0000; padding-top: 5px;'>Основные параметры</h1>
		";
		
		$data = array();
		$data['price'] = $price;
		
		$properties = $main->properties->getCurrentList();
		
		foreach( $properties as $prop_id => $prop_data ) {
			if( $prop_id == 19 )
				continue;
			switch( $prop_data['type'] ) {
				case 1:
					$inner .= "
					<p>
						".$lang->gp( $prop_data['name'], true ).": ".( $prop_data['comment'] ? "(".$prop_data['comment'].")" : "" )."<br>
						<input type=text name=\"param_".$prop_id."\" id=\"param_".$prop_id."\" value=\"\" class='text_input' />
					</p>
					";
					break;
				case 2:
					$inner .= "
					<p>
						".$lang->gp( $prop_data['name'], true ).": ".( $prop_data['comment'] ? "(".$prop_data['comment'].")" : "" )."<br>
						<select name=\"param_".$prop_id."\" id=\"param_".$prop_id."\" class='select_input'>
							".$main->listings->getListingForSelecting( $prop_data['source'], 0, 0, "<option value=0>Не выбрано</option>", "", false, '', true )."
						</select>
					</p>
					";
					break;
				case 3:
					$inner .= "
						<p>
							".$lang->gp( $prop_data['name'], true ).": ".( $prop_data['comment'] ? "(".$prop_data['comment'].")" : "" )."<br>
							<select multiple name=\"param_".$prop_id."[]\" id=\"param_".$prop_id."\" class='select_input' style='height: 100px;'>
								".$main->listings->getListingForSelecting( $prop_data['source'], 0, 0, "", "", false, '', true )."
							</select>
						</p>
					";
					break;
				case 4:
					$inner .= "
					<p>
						<input type=checkbox name=\"param_".$prop_id."\" id=\"param_".$prop_id."\" style='position: relative; top: 2px;' /> — ".$lang->gp( $prop_data['name'], true )."
					</p>
					";
					break;
				case 5:
					$inner .= "
					<div style='border: 1px solid #0000ff; padding: 5px;'>
						".$lang->gp( $prop_data['name'], true ).": ".( $prop_data['comment'] ? "(".$prop_data['comment'].")" : "" )."
						<div>
							Новая строчка: <input type=text id=\"new_string\" value=\"\" class='text_input' style='width: 200px;' /> <input type=button value='Добавить' onclick=\"
								var str = $( '#new_string' ).val();
								if( str == '' )
									return;
								$( '#no_strings' ).hide();
								$( '#strings' ).append( addNewStringParam( str ) );
								$( '#new_string' ).val( '' );
							\" />
						</div>
						<hr>
						<div id='no_strings'>
							Нет добавленных строк
						</div>
						<div id='strings'></div>
					</div>
					";
					break;
				case 6:
					$inner .= "
					<p>
						".$lang->gp( $prop_data['name'], true ).": ".( $prop_data['comment'] ? "(".$prop_data['comment'].")" : "" )."<br>
						<textarea name=\"param_".$prop_id."\" id=\"param_".$prop_id."\" class='textarea_input' rows=20></textarea>
					</p>
					";
					break;
			}
		}
		
		foreach( $properties as $prop_id => $prop_data ) {
			if( $prop_id < 14 || $prop_id > 15 )
				continue;
			$inner .= "
				<h1 style='border-top: 1px dashed #ff0000; padding-top: 5px;'>".$lang->gp( $prop_data['name'], true )." ".( $prop_data['comment'] ? "(".$prop_data['comment'].")" : "" )."</h1>
				<p>
					<label class=\"uploadbutton\" id=\"newfile_".$prop_id."_upload\">
						<span id=\"newfile_".$prop_id."_upload_innerspan\">
							Выберите графический файл
						</span>
					</label>
					&nbsp;
					<label class='red'>(форматы: gif, jpg, png)</label>
					<div class='error' id='newfile_".$prop_id."_error'></div>
					<input type=hidden name=\"param_".$prop_id."\" id=\"param_".$prop_id."\" value=\"\" />
					<table cellspacing=0 cellpadding=0 border=0><tr><td id='newfile_".$prop_id."_images' class='object_images'></td></tr></table>
					
					<script>
					$( '#newfile_".$prop_id."_upload' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'newfile',
       						action: '".$mysql->settings['local_folder']."admin/getblock',
       						data: {
    							external: '1',
    							type: '15',
    							extensions: 'gif|jpg|jpeg|png',
    							totemp: '1',
    							fname: 'newfile'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	$( '#newfile_".$prop_id."_error' ).html( 'Неверное расширение файла' ).show();
                					return false;
					            }
       		
					            $( '#newfile_".$prop_id."_error' ).hide();
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								$( '#newfile_".$prop_id."_error' ).html( 'Ошибка обработки файла' ).show();
       							} else {
       								$( '#newfile_".$prop_id."_images' ).html( prepareFileStringForSingle( data, 'param_".$prop_id."' ) );
       								$( '#param_".$prop_id."' ).val( prepareSingleFileString( data ) );
       								$( '#newfile_".$prop_id."_error' ).hide();
       							}
			       			}
						} );
					} );
					</script>
				</p>
			";
		}
		
		foreach( $properties as $prop_id => $prop_data ) {
			if( $prop_id != 16 )
				continue;
			$inner .= "
				<h1 style='border-top: 1px dashed #ff0000; padding-top: 5px;'>Работа с галереей изображений</h1>
				<p>
					<label class=\"uploadbutton\" id=\"newfile_upload\">
						<span id=\"newfile_upload_innerspan\">
							Выберите графический файл
						</span>
					</label>
					&nbsp;
					<label class='red'>(форматы: gif, jpg, png)</label>
					<div class='error' id='newfile_error'></div>
					<table cellspacing=0 cellpadding=0 border=0><tr><td id='newfile_images' class='object_images'></td></tr></table>
					
					<script>
					$( '#newfile_upload' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'newfile',
       						action: '".$mysql->settings['local_folder']."admin/getblock',
       						data: {
    							external: '1',
    							type: '15',
    							extensions: 'gif|jpg|jpeg|png',
    							totemp: '1',
    							fname: 'newfile'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	$( '#newfile_error' ).html( 'Неверное расширение файла' ).show();
                					return false;
					            }
       		
					            $( '#newfile_error' ).hide();
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								$( '#newfile_error' ).html( 'Ошибка обработки файла' ).show();
       							} else {
       								$( '#newfile_images' ).append( prepareFileString( data ) );
       								$( '#newfile_error' ).hide();
       							}
			       			}
						} );
					} );
					</script>
				</p>
			";
		}
				
		$inner .= "
		
		<style>
			td.object_images { padding-top: 10px; padding-bottom: 10px; }
			td.object_images div { float: left; margin-right: 5px; margin-bottom: 5px; text-align: center; }
			td.object_images div img { margin-bottom: 5px; max-height: 100px; }
		</style>
		
		<script>
			function prepareFileString( data )
			{
				var ar = data.toString().split( '^' );
				
				return '<div><img src=\"".$mysql->settings['local_folder']."tmp/' + ar[1] + '\" /><br>Размер: ' + ar[2] + 'x' + ar[3] + '<br><a href=\"#\" onclick=\"if( !confirm( \'Вы уверены?\' ) ) return false; $( this ).parent().remove(); return false;\">удалить</a></div>';
			}
			function prepareFileStringForSingle( data, p_id )
			{
				var ar = data.toString().split( '^' );
				
				return '<div><img src=\"".$mysql->settings['local_folder']."tmp/' + ar[1] + '\" /><br>Размер: ' + ar[2] + 'x' + ar[3] + '<br><a href=\"#\" onclick=\"if( !confirm( \'Вы уверены?\' ) ) return false; $( this ).parent().remove(); $( \'#\' + p_id ).val(''); return false;\">удалить</a></div>';
			}
			function prepareSingleFileString( data )
			{
				var ar = data.toString().split( '^' );
				
				return ar[1];
			}
		</script>
		
		<script type=\"text/javascript\" src=\"/jsf/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>
		<script type=\"text/javascript\">
		tinyMCE.init({
		
		mode : \"textareas\",
		language : \"ru\",
		theme : \"advanced\",
		plugins : \"pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave,smimage,smexplorer\",

		
		theme_advanced_buttons1 : \"save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect\",
		theme_advanced_buttons2 : \"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor\",
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
        
        plugin_smimage_directory : '".LOCAL_FOLDER."files/upload/goods/additional',
        plugin_smexplorer_directory : '".LOCAL_FOLDER."files/upload/goods/additional_files',
		file_browser_callback : 'SMPlugins',       

		
		template_replace_values : {
			username : \"Some User\",
			staffid : \"991234\"
		}
		});

		function addNewStringParam( str )
			{
				return '<div><span>' + str + '</span>&nbsp;<img src=\'/images/edit.gif\' style=\'position: relative; top: 2px; cursor: pointer;\' onclick=\"var new_str = prompt( \'Введите строчку\', $( this ).parent().children( \'span\' ).html() ); if( new_str != undefined ) $( this ).parent().children( \'span\' ).html( new_str );\" />&nbsp;<img src=\'/images/drop.gif\' style=\'position: relative; top: 2px; cursor: pointer;\' onclick=\"if( !confirm( \'Вы уверены?\' ) ) return; $( this ).parent().remove(); var v = 0; $( \'#strings div\' ).each( function() { v++ } ); if( !v ) $( \'#no_strings\' ).show();\" /></div>';
			}
		</script>
		";
			
		return "
				<h1 align=left><b>Создание нового товара</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$path."\" method=POST style='text-align: left;' id='createtovar_form'>
					".$inner."
										
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=submit value=\"Добавить товар\" class='button_input' title='Добавить товар' onclick=\"
							var stringsset = '';
							$( '#strings div' ).each( function() {
								stringsset += ( ( stringsset.length > 0 ? ';;;' : '' ) + $( this ).children( 'span' ).html() );
							} );
							$( '#strings_set' ).val( stringsset );
							
							var imagesset = '';
							$( '#newfile_images div' ).each( function() {
								imagesset += ( ( imagesset.length > 0 ? ';;;' : '' ) + $( this ).html() );
							} );
							$( '#images_set' ).val( imagesset );
							
							$( '#process' ).attr( 'value', 1 );
						\" />
							&nbsp;&nbsp;&nbsp;&nbsp;
						<input type=submit value=\"Отменить\" class='button_input' onclick=\"$( '#createtovar' ).attr( 'value', 0 );\" />
					</div>
					
				<input type=hidden name=\"strings_set\" id=\"strings_set\" value=\"\" />
				<input type=hidden name=\"images_set\" id=\"images_set\" value=\"\" />
					
				<input type=hidden name=\"createtovar\" id=\"createtovar\" value=\"1\" />
				<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
					
				</form>
		";
	}
	
	function parseExternalRequest()
	{
		global $query, $main, $utils, $lang, $mysql;
		
		$type = $query->gp( "localtype" );
		
		switch( $type ) {
			case 1:
				$id = $query->gp( "id" );
				$newprice = $query->gp( "newprice" );
				
				$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=48" );
				while( $r = @mysql_fetch_assoc( $a ) ) {
					$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_prices` VALUES(
						0,
						".$id.",
						48,
						'".$r['value']."',
						'".$newprice."',
						206,
						".time()."
					);" );
				}
				
				$r = $mysql->mq( "SELECT `price` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `id`=".$id );			
				$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_prices` VALUES(
						0,
						".$id.",
						0,
						'',
						'".$r['price']."',
						206,
						".time()."
					);" );
				
				$mysql->mu( "UPDATE ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` SET

					`price`='".$newprice."'
				
				WHERE `id`=".$id );
			
				return $utils->digitsToRazryadi( $newprice );
				
			case 111:
				$id = $query->gp( "id" );
				$newprice = $query->gp( "newprice" );
				
				$r = $mysql->mq( "SELECT `price` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `id`=".$id );			
				$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_prices` VALUES(
						0,
						".$id.",
						0,
						'',
						'".$r['price']."',
						206,
						".time()."
					);" );
				
				$mysql->mu( "UPDATE ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` SET

					`price`='".$newprice."'
				
				WHERE `id`=".$id );
			
				return $utils->digitsToRazryadi( $newprice );
				
			case 2:
				$id = $query->gp( "id" );
				$newconf = $query->gp( "newconf" );
				
				if( !$newconf ) {
					$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=56" );
					return "1";
				}
				
				$r = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=56" );
				
				if( $r ) {
					$mysql->mu( "UPDATE ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` SET
	
						`value`='".$newconf."'
					
					WHERE `id`=".$r['id'] );
				} else {
					$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(0,56,".$id.",'".$newconf."');" );
				}
			
				return "1";
				
			case 3:
				$id = $query->gp( "id" );
				
				$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(0,55,".$id.",'1');" );
				$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(0,2,".$id.",'1');" );
				
				return "1";
				
			case 33:
				$id = $query->gp( "id" );
				
				$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(0,55,".$id.",'1');" );
				
				return "1";
				
			case 4:
				$id = $query->gp( "id" );
				
				$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=55" );
				$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=2" );
				
				$r = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_prices` WHERE `tovar_id`=".$id." ORDER BY `id` DESC" );
				if( $r ) {
					$mysql->mu( "UPDATE ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` SET `price`='".$r['price']."' WHERE `id`=".$id );
					$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_prices` WHERE `id`=".$r['id'] );
				}
				
				return "1";
				
			case 5:
				$id = $query->gp( "id" );
				
				$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(0,4,".$id.",'1');" );
				
				return "1";
				
			case 6:
				$id = $query->gp( "id" );
				
				$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=4" );
				
				return "1";
				
			case 7:
				$id = $query->gp( "id" );
				
				$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` VALUES(0,2,".$id.",'1');" );
				
				return "1";
				
			case 8:
				$id = $query->gp( "id" );
				
				$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=55" );
				$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." AND `prop_id`=2" );
				
				return "1";
				
			case 9:
				$id = $query->gp( "id" );
				
				$props = $main->properties->getPropertiesOfGood( $id );
				foreach( $props as $p ) {
					if( $p['prop_id'] >= 14 && $p['prop_id'] <= 16 && $p['value'] ) {
						@unlink( ROOT_PATH."files/upload/goods/thumbs/".$p['value'] );
						@unlink( ROOT_PATH."files/upload/goods/".$p['value'] );
					}
				}
				
				$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `id`=".$id );
				$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_prices` WHERE `tovar_id`=".$id );
				$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id );
				
				// Удаляем все товары с ID из корзины
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."basket` WHERE `good`=".$id );
				
				return "1";
				
			case 11:
				$id = $query->gp( "id" );
				$newarticle = $query->gp( "newarticle" );
				
				$mysql->mu( "UPDATE ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` SET

					`article`='".$newarticle."'
				
				WHERE `id`=".$id );
			
				return $newarticle;
				
		}
	}
	
	function getExternalEdittovar( $path, $id )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;
		
		$catalog = $main->modules->gmi( "catalog" );
		
		$data = $catalog->getItem( $id );
		if( !$data )
			return "";
			
		$root = $data['root'] ? $catalog->getItem( $data['root'] ) : 0;
			
		$inner = "
				".( !$root ? "<p>
					Название товара: <br>
					<input type=text name=\"name\" id=\"name\" value=\"".str_replace( '"', "&quot;", $data['name'] )."\" class='text_input' />
				</p>" : "
				<p>
					<h2>".str_replace( '"', "&quot;", $root['name'] )."</h2>
				</p>
				" )."
				<p>
					Артикул товара: <br>
					<input type=text name=\"article\" id=\"article\" value=\"".$data['article']."\" class='text_input' />
				</p>
				<p>
					Бренд:<br>
					<select name='vendor' id='vendor' class='select_input'>
						".$main->listings->getListingForSelecting( 7, $data['vendor'], 0, "<option value=0>Не выбран</option>", "", false, '', true )."
					</select>
				</p>
				<p>
					Стоимость товара: <small style='color: #ff0000;'>(если вы измените эту цифру и сохраните изменения, то старая цена сохранится в архив и новая цена станет активной)</small> <br>
					<input type=text name=\"price\" id=\"price\" value=\"".intval( $data['price'] )."\" class='text_input' style='width: 100px;' />
				</p>
				<p>
					Популярность: <small style='color: #ff0000;'>(формируется автоматически, исходя из кол-ва реальных заказов, но можно изменить вручную)</small> <br>
					<input type=text name=\"popular\" id=\"popular\" value=\"".$data['popular']."\" class='text_input' />
				</p>
				
				<h1 style='border-top: 1px dashed #ff0000; padding-top: 5px;'>Основные параметры</h1>
		";
		
		$properties = $main->properties->getCurrentList();
		$props = $main->properties->getPropertiesOfGood( $id );
		
		foreach( $properties as $prop_id => $prop_data ) {
			if( $prop_id == 19 )
				continue;
			if( $root && $prop_id != 1 && $prop_id != 10 && $prop_id != 11 )
				continue;
			
			$property = "";
			$pr_simple = null;
			foreach( $props as $p ) {
				if( $p['prop_id'] == $prop_id ) {
					$property .= ( $property ? "," : "" ).$p['value'];
				}
			}
			if( !strpos( $property, "," ) || !$property ) {
				if( !strpos( $property, ";" ) )
					$property = $this->getElementByData( $props, "prop_id", $prop_id );
			}
			
			switch( $prop_data['type'] ) {
				case 1:
					$inner .= "
					<p>
						".$lang->gp( $prop_data['name'], true ).": ".( $prop_data['comment'] ? "(".$prop_data['comment'].")" : "" )."<br>
						<input type=text name=\"param_".$prop_id."\" id=\"param_".$prop_id."\" value=\"".( is_array( $property ) ? $property['value'] : $property )."\" class='text_input' />
					</p>
					";
					break;
				case 2:
					$inner .= "
					<p>
						".$lang->gp( $prop_data['name'], true ).": ".( $prop_data['comment'] ? "(".$prop_data['comment'].")" : "" )."<br>
						<select name=\"param_".$prop_id."\" id=\"param_".$prop_id."\" class='select_input'>
							".$main->listings->getListingForSelecting( $prop_data['source'], ( is_array( $property ) ? $property['value'] : $property ), 0, "<option value=0>Не выбрано</option>", "", false, '', true )."
						</select>
					</p>
					";
					break;
				case 3:
					$inner .= "
						<p>
							".$lang->gp( $prop_data['name'], true ).": ".( $prop_data['comment'] ? "(".$prop_data['comment'].")" : "" )."<br>
							<select multiple name=\"param_".$prop_id."[]\" id=\"param_".$prop_id."\" class='select_input' style='height: 100px;'>
								".$main->listings->getListingForSelecting( $prop_data['source'], ( is_array( $property ) ? $property['value'] : $property ), 0, "", "", false, '', true )."
							</select>
						</p>
					";
					break;
				case 4:
					$inner .= "
					<p>
						<input type=checkbox name=\"param_".$prop_id."\" id=\"param_".$prop_id."\"".( ( is_array( $property ) ? $property['value'] : $property ) ? " checked" : "" )." style='position: relative; top: 2px;' /> — ".$lang->gp( $prop_data['name'], true )."
					</p>
					";
					break;
				case 5:
					$inner .= "
					<div style='border: 1px solid #0000ff; padding: 5px;'>
						".$lang->gp( $prop_data['name'], true ).": ".( $prop_data['comment'] ? "(".$prop_data['comment'].")" : "" )."
						<div>
							Новая строчка: <input type=text id=\"new_string\" value=\"\" class='text_input' style='width: 200px;' /> <input type=button value='Добавить' onclick=\"
								var str = $( '#new_string' ).val();
								if( str == '' )
									return;
								$( '#no_strings' ).hide();
								$( '#strings' ).append( addNewStringParam( str ) );
								$( '#new_string' ).val( '' );
							\" />
						</div>
						<hr>
						<div id='no_strings'".( $property ? " class='invisible'" : "" ).">
							Нет добавленных строк
						</div>
						<div id='strings'>
					";
					foreach( $props as $p ) {
						if( $p['prop_id'] == $prop_id ) {
							$inner .= "
							<div>
								<span>".$p['value']."</span>&nbsp;
								<img src=\"/images/edit.gif\" style='position: relative; top: 2px; cursor: pointer;' onclick=\"
									var new_str = prompt( 'Введите строчку', $( this ).parent().children( 'span' ).html() );
									if( new_str != undefined )
										$( this ).parent().children( 'span' ).html( new_str );
								\" />&nbsp;
								<img src=\"/images/drop.gif\" style='position: relative; top: 2px; cursor: pointer;' onclick=\"
									if( !confirm( 'Вы уверены?' ) )
										return;
									$( this ).parent().remove();
									var v = 0;
									$( '#strings div' ).each( function() { v++ } );
									if( !v )
										$( '#no_strings' ).show();
								\" />
							</div>";
						}
					}
					$inner .= "
						</div>
					</div>
					";
					break;
				case 6:
					$inner .= "
					<p>
						".$lang->gp( $prop_data['name'], true ).": ".( $prop_data['comment'] ? "(".$prop_data['comment'].")" : "" )."<br>
						<textarea name=\"param_".$prop_id."\" id=\"param_".$prop_id."\" class='textarea_input' rows=20>".( is_array( $property ) ? $property['value'] : $property )."</textarea>
					</p>
					";
					break;
			}
		}
		
		foreach( $properties as $prop_id => $prop_data ) {
			if( $prop_id < 14 || $prop_id > 15 )
				continue;
			$p_image = "";
			foreach( $props as $p ) {
				if( $p['prop_id'] == $prop_id ) {
					$s = getimagesize( ROOT_PATH."files/upload/goods/".$p['value'] );
					$p_image .= "
					<div><img src=\"http://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."files/upload/goods/thumbs/".$p['value']."\" /><br>
					Размер: ".$s[0]."x".$s[1]."<br>
					<a href=\"#\" onclick=\"
						if( !confirm( 'Вы уверены?' ) ) return false;
						$( this ).parent().remove();
						$( '#param_".$prop_id."' ).val('-');
						 return false;
					\">удалить</a>
					</div>";
				}
			}
			$inner .= "
				<h1 style='border-top: 1px dashed #ff0000; padding-top: 5px;'>".$lang->gp( $prop_data['name'], true )." ".( $prop_data['comment'] ? "(".$prop_data['comment'].")" : "" )."</h1>
				<p>
					<label class=\"uploadbutton\" id=\"newfile_".$prop_id."_upload\">
						<span id=\"newfile_".$prop_id."_upload_innerspan\">
							Выберите графический файл
						</span>
					</label>
					&nbsp;
					<label class='red'>(форматы: gif, jpg, png)</label>
					<div class='error' id='newfile_".$prop_id."_error'></div>
					<input type=hidden name=\"param_".$prop_id."\" id=\"param_".$prop_id."\" value=\"\" />
					<table cellspacing=0 cellpadding=0 border=0><tr><td id='newfile_".$prop_id."_images' class='object_images'>".$p_image."</td></tr></table>
					
					<script>
					$( '#newfile_".$prop_id."_upload' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'newfile',
       						action: '".$mysql->settings['local_folder']."admin/getblock',
       						data: {
    							external: '1',
    							type: '15',
    							extensions: 'gif|jpg|jpeg|png',
    							totemp: '1',
    							fname: 'newfile'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	$( '#newfile_".$prop_id."_error' ).html( 'Неверное расширение файла' ).show();
                					return false;
					            }
       		
					            $( '#newfile_".$prop_id."_error' ).hide();
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								$( '#newfile_".$prop_id."_error' ).html( 'Ошибка обработки файла' ).show();
       							} else {
       								$( '#newfile_".$prop_id."_images' ).html( prepareFileStringForSingle( data, 'param_".$prop_id."' ) );
       								$( '#param_".$prop_id."' ).val( prepareSingleFileString( data ) );
       								$( '#newfile_".$prop_id."_error' ).hide();
       							}
			       			}
						} );
					} );
					</script>
				</p>
			";
		}
		
		$images = "";
		foreach( $props as $p ) {
			if( $p['prop_id'] == 16 ) {
				$s = getimagesize( ROOT_PATH."files/upload/goods/".$p['value'] );
				$images .= "
				<div><img src=\"http://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."files/upload/goods/thumbs/".$p['value']."\" /><br>
				Размер: ".$s[0]."x".$s[1]."<br>
				<a href=\"#\" onclick=\"
					if( !confirm( 'Вы уверены?' ) ) return false;
					$( this ).parent().remove();
					 return false;
				\">удалить</a>
				</div>";
			}
		}
		
		foreach( $properties as $prop_id => $prop_data ) {
			if( $prop_id != 16 )
				continue;
			$inner .= "
				<h1 style='border-top: 1px dashed #ff0000; padding-top: 5px;'>Работа с галереей изображений</h1>
				<p>
					<label class=\"uploadbutton\" id=\"newfile_upload\">
						<span id=\"newfile_upload_innerspan\">
							Выберите графический файл
						</span>
					</label>
					&nbsp;
					<label class='red'>(форматы: gif, jpg, png)</label>
					<div class='error' id='newfile_error'></div>
					<table cellspacing=0 cellpadding=0 border=0><tr><td id='newfile_images' class='object_images'>".$images."</td></tr></table>
					
					<script>
					$( '#newfile_upload' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'newfile',
       						action: '".$mysql->settings['local_folder']."admin/getblock',
       						data: {
    							external: '1',
    							type: '15',
    							extensions: 'gif|jpg|jpeg|png',
    							totemp: '1',
    							fname: 'newfile'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	$( '#newfile_error' ).html( 'Неверное расширение файла' ).show();
                					return false;
					            }
       		
					            $( '#newfile_error' ).hide();
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								$( '#newfile_error' ).html( 'Ошибка обработки файла' ).show();
       							} else {
       								$( '#newfile_images' ).append( prepareFileString( data ) );
       								$( '#newfile_error' ).hide();
       							}
			       			}
						} );
					} );
					</script>
				</p>
			";
		}
				
		$inner .= "
		
		<style>
			td.object_images { padding-top: 10px; padding-bottom: 10px; }
			td.object_images div { float: left; margin-right: 5px; margin-bottom: 5px; text-align: center; }
			td.object_images div img { margin-bottom: 5px; max-height: 100px; }
		</style>
		
		<script>
			function prepareFileString( data )
			{
				var ar = data.toString().split( '^' );
				
				return '<div><img src=\"".$mysql->settings['local_folder']."tmp/' + ar[1] + '\" /><br>Размер: ' + ar[2] + 'x' + ar[3] + '<br><a href=\"#\" onclick=\"if( !confirm( \'Вы уверены?\' ) ) return false; $( this ).parent().remove(); return false;\">удалить</a></div>';
			}
			function prepareFileStringForSingle( data, p_id )
			{
				var ar = data.toString().split( '^' );
				
				return '<div><img src=\"".$mysql->settings['local_folder']."tmp/' + ar[1] + '\" /><br>Размер: ' + ar[2] + 'x' + ar[3] + '<br><a href=\"#\" onclick=\"if( !confirm( \'Вы уверены?\' ) ) return false; $( this ).parent().remove(); $( \'#\' + p_id ).val(\'-\'); return false;\">удалить</a></div>';
			}
			function prepareSingleFileString( data )
			{
				var ar = data.toString().split( '^' );
				
				return ar[1];
			}
		</script>
		
		<script type=\"text/javascript\" src=\"/jsf/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>
		<script type=\"text/javascript\">
		tinyMCE.init({
		
		mode : \"textareas\",
		language : \"ru\",
		theme : \"advanced\",
		plugins : \"pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave,smimage,smexplorer\",

		
		theme_advanced_buttons1 : \"save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect\",
		theme_advanced_buttons2 : \"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor\",
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
        
        plugin_smimage_directory : '".LOCAL_FOLDER."files/upload/goods/additional',
        plugin_smexplorer_directory : '".LOCAL_FOLDER."files/upload/goods/additional_files',
		file_browser_callback : 'SMPlugins',       

		
		template_replace_values : {
			username : \"Some User\",
			staffid : \"991234\"
		}
		});

		function addNewStringParam( str )
			{
				return '<div><span>' + str + '</span>&nbsp;<img src=\'/images/edit.gif\' style=\'position: relative; top: 2px; cursor: pointer;\' onclick=\"var new_str = prompt( \'Введите строчку\', $( this ).parent().children( \'span\' ).html() ); if( new_str != undefined ) $( this ).parent().children( \'span\' ).html( new_str );\" />&nbsp;<img src=\'/images/drop.gif\' style=\'position: relative; top: 2px; cursor: pointer;\' onclick=\"if( !confirm( \'Вы уверены?\' ) ) return; $( this ).parent().remove(); var v = 0; $( \'#strings div\' ).each( function() { v++ } ); if( !v ) $( \'#no_strings\' ).show();\" /></div>';
			}
		</script>
		";
			
		return "
				<h1 align=left><b>Редактирование товара № ".$id." - «".( $root ? $root['name'] : $data['name'] )."»</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$path."\" method=POST style='text-align: left;'>
					".$inner."
										
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=submit value=\"Сохранить\" class='button_input' title='Сохранить изменения' onclick=\"
							var stringsset = '';
							$( '#strings div' ).each( function() {
								stringsset += ( ( stringsset.length > 0 ? ';;;' : '' ) + $( this ).children( 'span' ).html() );
							} );
							$( '#strings_set' ).val( stringsset );
							
							var imagesset = '';
							$( '#newfile_images div' ).each( function() {
								imagesset += ( ( imagesset.length > 0 ? ';;;' : '' ) + $( this ).html() );
							} );
							$( '#images_set' ).val( imagesset );
							
							$( '#process' ).attr( 'value', 1 );
							
						\" />
							&nbsp;&nbsp;&nbsp;&nbsp;
						<input type=submit value=\"Отменить\" class='button_input' onclick=\"$( '#edit' ).attr( 'value', 0 );\" />
					</div>
					
				<input type=hidden name=\"strings_set\" id=\"strings_set\" value=\"\" />
				<input type=hidden name=\"images_set\" id=\"images_set\" value=\"\" />
					
				<input type=hidden name=\"goodsid\" id=\"goodsid\" value=\"".$id."\" />
				<input type=hidden name=\"edit\" id=\"edit\" value=\"1\" />
				<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
					
				</form>
		";
	}
	
	function getExternalCopytovar( $path, $id )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;
		
		$catalog = $main->modules->gmi( "catalog" );
		
		$data = $catalog->getItem( $id );
		if( !$data )
			return "";
			
		$inner = "
				<p>
					Название товара: <br>
					<input type=text name=\"name\" id=\"name\" value=\"".str_replace( '"', "&quot;", $data['name'] )."\" class='text_input' />
				</p>
				<p>
					Артикул товара: <br>
					<input type=text name=\"article\" id=\"article\" value=\"\" class='text_input' />
				</p>
				<p>
					Стоимость товара:<br>
					<input type=text name=\"price\" id=\"price\" value=\"".intval( $data['price'] )."\" class='text_input' style='width: 100px;' />
				</p>
		";
			
		return "
				<h1 align=left><b>Копирование товара № ".$id." - «".$data['name']."»</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$path."\" method=POST style='text-align: left;'>
					".$inner."
										
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=submit value=\"Скопировать\" class='button_input' onclick=\"
							$( '#process' ).attr( 'value', 1 );
						\" />
							&nbsp;&nbsp;&nbsp;&nbsp;
						<input type=submit value=\"Отменить\" class='button_input' onclick=\"$( '#copyitem' ).attr( 'value', 0 );\" />
					</div>
					
				<input type=hidden name=\"goodsid\" id=\"goodsid\" value=\"".$id."\" />
				<input type=hidden name=\"copyitem\" id=\"copyitem\" value=\"1\" />
				<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
					
				</form>
		";
	}
	
	function getExternalClonetovar( $path, $id )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;
		
		$catalog = $main->modules->gmi( "catalog" );
		
		$data = $catalog->getItem( $id );
		if( !$data )
			return "";
			
		$props = $main->properties->getPropertiesOfGood( $id );
		$selectedColor = $this->getElementByData( $props, "prop_id", 1 );
			
		$ex_colors = "`id`<>'".$selectedColor['value']."'";
		$aa = $mysql->mqm( "SELECT `id` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `root`=".$id );			
		while( $rr = @mysql_fetch_assoc( $aa ) ) {
			$cprops = $main->properties->getPropertiesOfGood( $rr['id'] );
			$p = $this->getElementByData( $cprops, "prop_id", 1 );
			if( $p && $p['value'] ) {
				$ex_colors .= " AND `id`<>'".$p['value']."'";
			}
		}
			
		$inner = "
				<p>
					Артикул товара: <br>
					<input type=text name=\"article\" id=\"article\" value=\"\" class='text_input' />
				</p>
				<p>
					Стоимость товара:<br>
					<input type=text name=\"price\" id=\"price\" value=\"".intval( $data['price'] )."\" class='text_input' style='width: 100px;' />
				</p>
				<p>
					Новый цвет товара: <small style='color: #ff0000;'>(нужно выбрать из списка доступных и ранее не добавленных цветов)</span><br>
					<select name=\"color\" id=\"color\" class='select_input'>
						".$main->listings->getListingForSelecting( 3, 0, 0, "<option value=0>Не выбрано</option>", "", false, $ex_colors, true )."
					</select>
				</p>
		";
			
		return "
				<h1 align=left><b>Добавление цвета для товара № ".$id." - «".$data['name']."»</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$path."\" method=POST style='text-align: left;'>
					".$inner."
										
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=submit value=\"Добавить\" class='button_input' onclick=\"
							if( $( '#color' ).val() == '0' ) {
								alert( 'Необходимо выбрать новый цвет товара' );
								return false;
							}
							$( '#process' ).attr( 'value', 1 );
						\" />
							&nbsp;&nbsp;&nbsp;&nbsp;
						<input type=submit value=\"Отменить\" class='button_input' onclick=\"$( '#cloneitem' ).attr( 'value', 0 );\" />
					</div>
					
				<input type=hidden name=\"goodsid\" id=\"goodsid\" value=\"".$id."\" />
				<input type=hidden name=\"cloneitem\" id=\"cloneitem\" value=\"1\" />
				<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
					
				</form>
		";
	}
}

?>