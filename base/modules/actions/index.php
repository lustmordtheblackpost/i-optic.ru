<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class moduleactions extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	var $global = false;
	var $gl_dbase_string = "`shop`.";
	
	function init( $dbinfo )
	{
		global $query;

		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
		$this->global = $query->gp( "global" ) ? true : false;
	}
	
	function getIndexBanners( $catalog = 0 )
	{
		global $mysql, $query, $utils, $lang, $main;

		$cd = time();
		$rr = "";
		$c = 0;
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."actions` WHERE `view`=1 AND `start_date`<=".$cd." AND `end_date`>".$cd." ORDER BY RAND()" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$mm = json_decode( $r['razd'] );
			if( !$this->searchArrayForValue( $mm, $catalog ? $catalog : 3 ) )
				continue;
			$c++;
			$rr .= "
			<div class='theone' data-url='/files/upload/actions/".$r['image']."' data-murl='/files/upload/actions/".$r['m_image']."' style=\"background-repeat: no-repeat; background-size: cover; background-position: 50% 50%;\" id='rolling_image_".$c."'></div>
			";
		}
                
                if( !$c )
                    return "";
		
		$t = "
		<div class='action".( $catalog ? " action_rest" : "" )."'>
			<div class='all_lines' style='height: 100%;'>
				".$rr."
			</div>
		</div>
		
		<script>
			var current_picture = 1, max_pictures = ".$c.";
			
			function changePictures()
			{
				if( max_pictures == 1 )
					return;
				$( '#rolling_image_' + current_picture ).fadeOut( 'slow' );
				current_picture = current_picture == max_pictures ? 1 : current_picture + 1;
				$( '#rolling_image_' + current_picture ).fadeIn( 'slow' );
				setTimeout( changePictures, ".( $this->getParam( "change_timer" ) * 1000 )." );
			}
			
			setTimeout( changePictures, ".( $this->getParam( "change_timer" ) * 1000 )." );
                            
                        $(window).load(function()
			{
                            if( isMobile ) {
                                $( '.action' ).height( $( '.action' ).width() / 1.77 );
                                $( '.action .theone' ).each(function(){
                                    var url = $( this ).attr( 'data-murl' );
                                    if( !url )
                                        url = $( this ).attr( 'data-url' );
                                    $( this ).css( 'background-image', 'url(' + url + ')' );
                                });
                            } else {
                                $( '.action .theone' ).each(function(){
                                    $( this ).css( 'background-image', 'url(' + $( this ).attr( 'data-url' ) + ')' );
                                });
                            }
                        });
                        $(window).resize(function()
			{
                            if( isMobile ) {
                                $( '.action' ).height( $( '.action' ).width() / 1.77 );
                                $( '.action .theone' ).each(function(){
                                    var url = $( this ).attr( 'data-murl' );
                                    if( !url )
                                        url = $( this ).attr( 'data-url' );
                                    $( this ).css( 'background-image', 'url(' + url + ')' );
                                });
                            } else {
                                $( '.action .theone' ).each(function(){
                                    $( this ).css( 'background-image', 'url(' + $( this ).attr( 'data-url' ) + ')' );
                                });
                            }
                        });
		</script>
		";
		
		return $t;
	}
        
        function getBannersBlock()
	{
		global $mysql, $query, $utils, $lang, $main;

		$cd = time();
		$rr = "";
		$c = 0;
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."banners` WHERE `view`=1 AND `start_date`<=".$cd." AND `end_date`>".$cd." ORDER BY RAND()" );
                $even = true;
                /*<div class='data_block'><div class='heighter'></div><div class='texts'>
                                <h3>".$r['title']."</h3>
                                <p>".$main->templates->psl( $r['subtitle'], true )."</p>
                                <a href='".$r['link']."'>Подробнее</a>&nbsp;&nbsp;<img src='/images/bluearrowright.png' alt='bluearrowright' />
                            </div></div><div class='clear'></div>*/
		while( $r = @mysql_fetch_assoc( $a ) ) {
                        $even = $even ? false : true;
			$rr .= "<div class='theone".( $r['link'] ? " pointer" : "" )."'".( $r['link'] ? " onclick=\"urlmove('".$r['link']."');\"" : "" )."><div class='image_block' style=\"background: url(/files/upload/actions/".$r['image'].") no-repeat; background-position: 50% 50%;\"></div></div>";
		}
		
		$t = "
		<div class='rblocks'>
			<div class='all_lines' style='height: 100%;'>".$rr."<div class='clear'></div></div>
		</div>
                <script>
                        $(window).load(function()
			{
                            var col = Math.floor( $( '.rblocks' ).css( 'z-index' ) );
                            var c = 1;
                            $( '.rblocks .theone' ).each(function(){
                                if( c >= 1 && c < col ) {
                                    $( this ).css( 'margin-right', '20px' );
                                } else {
                                    $( this ).css( 'margin-right', '0px' );
                                }
                                c++;
                                if( c > col )
                                    c = 1;
                                $( this ).animate( { opacity: 1 }, 200 );
                            });
                        });
                        $(window).resize(function()
			{
                            var col = Math.floor( $( '.rblocks' ).css( 'z-index' ) );
                            var c = 1;
                            $( '.rblocks .theone' ).each(function(){
                                if( c >= 1 && c < col ) {
                                    $( this ).css( 'margin-right', '20px' );
                                } else {
                                    $( this ).css( 'margin-right', '0px' );
                                }
                                c++;
                                if( c > col )
                                    c = 1;
                            });
                        });
                </script>
		";
		
		return $t;
	}
	
	function getSignBlock()
	{
		global $mysql, $query, $utils, $lang, $main;
		
		$t = "
		<!--<div class='all_lines'><div class='SignBlock'>
			
				<div class='inputBlock'><div class='icon' id='sign_icon' onclick=\"if( $( '#email_text' ).val() == '' || $( '#email_text' ).val() == '".$lang->gp( 37 )."' ) return false; processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '1', '&mail=' + $( '#email_text' ).val(), 'alert( data ); $( \'#email_text\' ).val( \'".$lang->gp( 37 )."\' );' );\"><img src='/images/arr.png' /></div>
					<input type=text name='email_text' id='email_text' value='".$lang->gp( 37 )."' 
					onfocus=\"if( this.value == '".$lang->gp( 37 )."' ) this.value = '';\"
					onblur=\"if( this.value == '' ) this.value = '".$lang->gp( 37 )."';\"
					onkeypress=\"
						var code = processKeyPress( event );
						if( code == 13 ) $( '#sign_icon' ).click();
					\" />
				</div>
				<h3>Получите скидку <span>5%</span> на первый заказ</h3>
				<h4>Введите свой e-mail и вы будете получать всю информацию о новинках и акциях</h4>
			</div>
		</div> -->
		";
		
		return $t;
	}
	
	function getSmallSignBlock()
	{
		global $mysql, $query, $utils, $lang, $main;
		
		$t = "<!--
		<div class='SmallSignBlock'>
			<div class='inputBlock'><div class='icon' id='small_sign_icon' onclick=\"if( $( '#small_email_text' ).val() == '' || $( '#small_email_text' ).val() == '".$lang->gp( 37 )."' ) return false; processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '1', '&mail=' + $( '#small_email_text' ).val(), 'alert( data ); $( \'#small_email_text\' ).val( \'".$lang->gp( 37 )."\' );' );\"><img src='/images/arr.png' /></div>
				<input type=text name='small_email_text' id='small_email_text' value='".$lang->gp( 37 )."' 
				onfocus=\"if( this.value == '".$lang->gp( 37 )."' ) this.value = '';\"
				onblur=\"if( this.value == '' ) this.value = '".$lang->gp( 37 )."';\"
				onkeypress=\"
					var code = processKeyPress( event );
					if( code == 13 ) $( '#small_sign_icon' ).click();
				\" />
			</div>
			<h3>Получите скидку <span>5%</span> на первый заказ</h3>
			<h4>Введите свой e-mail и вы будете получать всю<br/>информацию о новинках и акциях</h4>
		</div> -->
		";
		
		return $t;
	}
	
	function getDiscountsForType( $type )
	{
		global $main, $mysql;
		
		$cd = time();
		$ar = array();
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."actions` WHERE `view`=1 AND `type`=".$type." AND `start_date`<=".$cd." AND `end_date`>".$cd );
		while( $r = @mysql_fetch_assoc( $a ) )
			array_push( $ar, $r );
		$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."actions` WHERE `view`=1 AND `type`=".$type." AND `start_date`<=".$cd." AND `end_date`>".$cd );
		while( $r = @mysql_fetch_assoc( $a ) )
			array_push( $ar, $r );
		
		return $ar;
	}
	
	
	function parseExternalRequest()
	{
		global $query, $main, $utils, $lang, $mysql;
		
		$type = $query->gp( "localtype" );
		
		switch( $type ) {
			case 1:
				$mail = $query->gp( 'mail' );
				if( !$mail )
					return "Укажите E-Mail адрес";
				$true = $mail && function_exists( "filter_var" ) && @filter_var( $mail, FILTER_VALIDATE_EMAIL ) ? 1 : $utils->checkEmail( $mail );
				if( !$true )
					return "E-Mail адрес неверен";
				if( !$mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."sign` WHERE `mail`='".$mail."'" ) ) {
                                    
                                        $promo = '';
                                        while( true ) {
                                            $promo = $utils->generatePassword();
                                            if( !$mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."sign` WHERE `promo`='".$promo."'" ) )
                                                    break;
                                        }
                                    
					$mysql->mu( "INSERT INTO `".$mysql->t_prefix."sign` VALUES(0,'".$mail."','".$promo."');" );
                                        
                                        $mail_agent = $main->modules->gmi( "mail_agent" );
                                        
                                        $template = $main->templates->psl( $lang->gp( 159 ), true );
                        		$template = str_replace( "[promo]", $promo, $template );
					
		                        $mail_agent->sendMessage( $mail, $this->getParam( "from_to_send_sign" ), $main->templates->psl( $lang->gp( 160 ) ), $template );
                                        
					return "Вы подписаны на новости о новинках! Получите письмо с подтверждением";
				} else {
					return "Указанный E-Mail адрес уже в списке рассылок";
				}
		}
	}
	//
	// Далее администраторская область
	//
	
	function getAdminScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;
		
		if( $query->gp( "createaction" ) && $query->gp( "process" ) ) {
			
			$title = isset( $_POST['title'] ) && $_POST['title'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['title'] )  ) : '';
			$razd = $_POST['razd'];
			$image = $query->gp( "image" );
                        $m_image = $query->gp( "m_image" );
			$link = strtolower( $query->gp( "link" ) );
			$comment = isset( $_POST['comment'] ) && $_POST['comment'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['comment'] )  ) : '';
			
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
			
			$mysql->mu( "INSERT INTO ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."actions` VALUES(
				0,
				'".$title."',
				'".json_encode( $razd )."',
				'".$image."',
                                '".$m_image."',
				'".$link."',
				1,
				'".$start_date."',
				'".$end_date."',
				'".$comment."',
				''
			);" );
			
			$r = $mysql->mq( "SELECT `id` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."actions` WHERE `start_date`=".$start_date." ORDER BY `id` DESC" );
			
			if( $r ) {
			
				if( $image ) {
					@copy( ROOT_PATH."tmp/".$image, ROOT_PATH."files/upload/actions/".$image );
					@unlink( ROOT_PATH."tmp/".$image );
				}
                                
                                if( $m_image ) {
					@copy( ROOT_PATH."tmp/".$m_image, ROOT_PATH."files/upload/actions/".$m_image );
					@unlink( ROOT_PATH."tmp/".$m_image );
				}
			
			}
			
			return "<script>document.location = '".$mysql->settings['local_folder']."admin/".$path."';</script>";
			
		} else if( $query->gp( "edit" ) && $query->gp( "process" ) ) {
			
			$id = $query->gp( "edit" );
			$ep = $mysql->mq( "SELECT `image` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."actions` WHERE `id`=".$id );
			
			$title = isset( $_POST['title'] ) && $_POST['title'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['title'] )  ) : '';
			$razd = $_POST['razd'];
			$image = $query->gp( "image" );
                        $m_image = $query->gp( "m_image" );
			$link = strtolower( $query->gp( "link" ) );
			$comment = isset( $_POST['comment'] ) && $_POST['comment'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['comment'] )  ) : '';
			
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
			
			if( $image ) {
				
				if( $ep['image'] )
					@unlink( ROOT_PATH."files/upload/actions/".$ep['image'] );
				
				@copy( ROOT_PATH."tmp/".$image, ROOT_PATH."files/upload/actions/".$image );
				@unlink( ROOT_PATH."tmp/".$image );
			
			}
                        
                        if( $m_image ) {
				
				if( $ep['m_image'] )
					@unlink( ROOT_PATH."files/upload/actions/".$ep['m_image'] );
				
				@copy( ROOT_PATH."tmp/".$m_image, ROOT_PATH."files/upload/actions/".$m_image );
				@unlink( ROOT_PATH."tmp/".$m_image );
			
			}
			
			$mysql->mu( "UPDATE ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."actions` SET

				`title`='".$title."',
				`razd`='".json_encode( $razd )."',
				".( $image ? "`image`='".$image."'," : "" )."
				".( $m_image ? "`m_image`='".$m_image."'," : "" )."
				`link`='".$link."',
				`start_date`='".$start_date."',
				`end_date`='".$end_date."',
				`comment`='".$comment."'
				
			WHERE `id`=".$id );
			
			$query->setProperty( "edit", 0 );
			
		} else if( $query->gp( "turnaction" ) ) {
			
			$id = $query->gp( "turnaction" );
			$ep = $mysql->mq( "SELECT `view` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."actions` WHERE `id`=".$id );			
			if( $ep )
				$mysql->mu( "UPDATE ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."actions` SET `view`=".( $ep['view'] ? "0" : "1" )." WHERE `id`=".$id );
				
		} else if( $query->gp( "delete" ) ) {
			
			$id = $query->gp( "delete" );
			$ep = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."actions` WHERE `id`=".$id );			
			if( $ep ) {
				$mysql->mu( "DELETE FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."actions` WHERE `id`=".$id );
				if( $ep['image'] )
					@unlink( ROOT_PATH."files/upload/actions/".$ep['image'] );
                                if( $ep['m_image'] )
					@unlink( ROOT_PATH."files/upload/actions/".$ep['m_image'] );
			}
			
		}
		
		if( $query->gp( "createaction" ) ) {
			
			return $this->getExternalNewAction( $path );
			
		} else if( $query->gp( "edit" ) ) {
			
			return $this->getExternalEditAction( $path, $query->gp( "edit" ) );
			
		}
		
		$selectedElement = 1;
		$t = "
			<h1>Список акций</h1>
			
			<a href=\"".$mysql->settings['local_folder']."admin/".$path."/createaction\">Создать новую акцию</a><br><br>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%;'>
				<tr class='list_table_header'>
					<td width=50>
						ID
					</td>
					<td width=20% style='text-align: left;'>
						Название акции и ссылка
					</td>
					<td width=20%>
						Дата начала/окончания действия
					</td>
					<td width=10%>
						Модули отображения
					</td>
					<td width=20%>
						Мини превью / мобильное превью
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
		$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."actions` WHERE ".$where." ORDER BY `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			
			$mm = json_decode( $r['razd'] );
			$rr = "";
			foreach( $mm as $v ) {
				$rr .= ( $rr ? ", " : "" ).$modules[$v]['instance']->getName();
			}
			
			$t .= "
				<tr class='list_table_element'".( !$r['view'] ? " style='background-color: #fcd9d9;'" : "" ).">
					<td valign=middle>
						".$r['id']."
					</td>
					<td style='text-align: left;' valign=middle>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/edit".$r['id']."\"><strong>".$r['title']."</strong></a><br>
						---<br>
						".( $r['link'] ? "Ссылка: <a href=\"".$r['link']."\" target=_BLANK>".$r['link']."</a>" : "Ссылка не используется" )."
					</td>
					<td nowrap>
						с ".$utils->getFullDate( $r['start_date'], true )."<br>по ".$utils->getFullDate( $r['end_date'], true )."
					</td>
					<td>
						".$rr."
					</td>
					<td>
						".( $r['image'] ? "<img src=\"".$mysql->settings['local_folder']."files/upload/actions/".$r['image']."\" style='max-width: 140px;' />" : "-" )."<br/><br/>".( $r['m_image'] ? "<img src=\"".$mysql->settings['local_folder']."files/upload/actions/".$r['m_image']."\" style='max-width: 140px;' />" : "-" )."
					</td>
					<td>
						".( $r['view'] ? "Да" : "Нет" )."
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
					<td colspan=7>
						Всего акций: ".$counter."
					</td>
				</tr>
		</table>
		";
		
		return $t;
	}
        
        function getAdminBannersScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;
		
		if( $query->gp( "createbanner" ) && $query->gp( "process" ) ) {
			
			$title = isset( $_POST['title'] ) && $_POST['title'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['title'] )  ) : '';
			$subtitle = isset( $_POST['subtitle'] ) && $_POST['subtitle'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['subtitle'] )  ) : '';
			$image = $query->gp( "image" );
			$link = strtolower( $query->gp( "link" ) );
			$comment = isset( $_POST['comment'] ) && $_POST['comment'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['comment'] )  ) : '';
			
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
			
			$mysql->mu( "INSERT INTO ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."banners` VALUES(
				0,
				'".$title."',
				'".$subtitle."',
				'".$image."',
				'".$link."',
				1,
				'".$start_date."',
				'".$end_date."',
				'".$comment."'
			);" );
			
			$r = $mysql->mq( "SELECT `id` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."banners` WHERE `start_date`=".$start_date." ORDER BY `id` DESC" );
			
			if( $r ) {
			
				if( $image ) {
					@copy( ROOT_PATH."tmp/".$image, ROOT_PATH."files/upload/actions/".$image );
					@unlink( ROOT_PATH."tmp/".$image );
				}
			
			}
			
			return "<script>document.location = '".$mysql->settings['local_folder']."admin/".$path."';</script>";
			
		} else if( $query->gp( "edit" ) && $query->gp( "process" ) ) {
			
			$id = $query->gp( "edit" );
			$ep = $mysql->mq( "SELECT `image` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."banners` WHERE `id`=".$id );
			
			$title = isset( $_POST['title'] ) && $_POST['title'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['title'] )  ) : '';
			$subtitle = isset( $_POST['subtitle'] ) && $_POST['subtitle'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['subtitle'] )  ) : '';
			$image = $query->gp( "image" );
			$link = strtolower( $query->gp( "link" ) );
			$comment = isset( $_POST['comment'] ) && $_POST['comment'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['comment'] )  ) : '';
			
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
			
			if( $image ) {
				
				if( $ep['image'] )
					@unlink( ROOT_PATH."files/upload/actions/".$ep['image'] );
				
				@copy( ROOT_PATH."tmp/".$image, ROOT_PATH."files/upload/actions/".$image );
				@unlink( ROOT_PATH."tmp/".$image );
			
			}
			
			$mysql->mu( "UPDATE ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."banners` SET

				`title`='".$title."',
				`subtitle`='".$subtitle."',
				".( $image ? "`image`='".$image."'," : "" )."
				`link`='".$link."',
				`start_date`='".$start_date."',
				`end_date`='".$end_date."',
				`comment`='".$comment."'
				
			WHERE `id`=".$id );
			
			$query->setProperty( "edit", 0 );
			
		} else if( $query->gp( "turnbanner" ) ) {
			
			$id = $query->gp( "turnbanner" );
			$ep = $mysql->mq( "SELECT `view` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."banners` WHERE `id`=".$id );			
			if( $ep )
				$mysql->mu( "UPDATE ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."banners` SET `view`=".( $ep['view'] ? "0" : "1" )." WHERE `id`=".$id );
				
		} else if( $query->gp( "delete" ) ) {
			
			$id = $query->gp( "delete" );
			$ep = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."banners` WHERE `id`=".$id );			
			if( $ep ) {
				$mysql->mu( "DELETE FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."banners` WHERE `id`=".$id );
				if( $ep['image'] )
					@unlink( ROOT_PATH."files/upload/actions/".$ep['image'] );
			}
			
		}
		
		if( $query->gp( "createbanner" ) ) {
			
			return $this->getExternalNewBanner( $path );
			
		} else if( $query->gp( "edit" ) ) {
			
			return $this->getExternalEditBanner( $path, $query->gp( "edit" ) );
			
		}
		
		$selectedElement = 1;
		$t = "
			<h1>Список рекламных блоков</h1>
			
			<a href=\"".$mysql->settings['local_folder']."admin/".$path."/createbanner\">Создать новый рекламный блок</a><br><br>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%;'>
				<tr class='list_table_header'>
					<td width=50>
						ID
					</td>
					<td width=20% style='text-align: left;'>
						Название блока и ссылка
					</td>
                                        <td width=10%>
						Подзаголовок
					</td>
					<td width=20%>
						Дата начала/окончания действия
					</td>
					<td width=20%>
						Мини превью
					</td>
					<td width=70>
						Включен?
					</td>
					<td width=110>
						Опции
					</td>
				</tr>
		";
		
		$where = '';
			
		if( !$where )
			$where = "1";
			
		$counter = 0;
		$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."banners` WHERE ".$where." ORDER BY `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			
			$t .= "
				<tr class='list_table_element'".( !$r['view'] ? " style='background-color: #fcd9d9;'" : "" ).">
					<td valign=middle>
						".$r['id']."
					</td>
					<td style='text-align: left;' valign=middle>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/edit".$r['id']."\"><strong>".$r['title']."</strong></a><br>
						---<br>
						".( $r['link'] ? "Ссылка: <a href=\"".$r['link']."\" target=_BLANK>".$r['link']."</a>" : "Ссылка не используется" )."
					</td>
                                        <td>
						".( $r['subtitle'] ? $r['subtitle'] : '-' )."
					</td>
					<td nowrap>
						с ".$utils->getFullDate( $r['start_date'], true )."<br>по ".$utils->getFullDate( $r['end_date'], true )."
					</td>					
					<td>
						".( $r['image'] ? "<img src=\"".$mysql->settings['local_folder']."files/upload/actions/".$r['image']."\" style='max-width: 140px;' />" : "-" )."
					</td>
					<td>
						".( $r['view'] ? "Да" : "Нет" )."
					</td>
					<td nowrap>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/turnbanner".$r['id']."\">".( $r['view'] ? "Выключить" : "Включить" )."</a><br>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/edit".$r['id']."\">Редактировать</a><br>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/delete".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a>
					</td>
				</tr>
			";
			$counter++;
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=7>
						Всего блококв: ".$counter."
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
		
		foreach( $main->modules->modules as $k => $v ) {
			$rr .= "<option value='".$k."'>".$v['instance']->getName()."</option>";
		}
		
		$inner = "
				<p>
					Название акции: <label class='red'>*</label><br>
					<input type=text name=\"title\" id=\"title\" value=\"\" class='text_input' />
				</p>
				<p>
					Модули приложения: <br>
					<select name=\"razd[]\" id=\"razd\" class='select_input' multiple style='height: 350px;'>
						".$rr."
					</select>
				</p>
				<p>
					Ссылка для перехода: (введите, если хотите, чтобы при клике на баннер акции осуществлялся переход по какой либо ссылке)<br>
					<input type=text name=\"link\" id=\"link\" value=\"\" class='text_input' />
				</p>				
				<p>
					Дата начала акции: (формат: дд/мм/гггг чч:мм , пример: 07/10/2013 15:31) - оставьте поле пустым, чтобы использовать текущую дату и время<br>
					<input type=text name=\"start_date\" id=\"start_date\" value=\"".date( "d/m/Y H:i" )."\" class='text_input' />
				</p>
				<p>
					Дата окончания акции: (формат: дд/мм/гггг чч:мм , пример: 07/10/2013 15:31) - оставьте поле пустым, чтобы использовать текущую дату и время<br>
					<input type=text name=\"end_date\" id=\"end_date\" value=\"".date( "d/m/Y H:i" )."\" class='text_input' />
				</p>
				<p>
					Комментарий:<br>
					<textarea name=\"comment\" id=\"comment\" rows=25 class='textarea_input'></textarea>
				</p>
				
				<p>
					Загрузите изображение для акции. При загрузке учитывайте следующее:
					<ol>
						<li>Высота и ширина баннера может быть любая;</li>
						<li>Автоподгонка размеров НЕ используется.</li>
					</ol>
				</p>
				
				<label class=\"uploadbutton\" id=\"action_upload\">
					<span id=\"action_upload_innerspan\">
						Выберите файл
					</span>
				</label>
				
				<input type=hidden name=\"image\" id=\"image\" value=\"\" />
				<div class='error' id='action_error'></div>
				<div id='action_image' style='margin-top: 7px; margin-bottom: 7px;'></div>
				
				<script>
					$( '#action_upload' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'action',
       						action: '".$mysql->settings['local_folder']."admin/getblock',
       						data: {
    							external: '1',
    							type: '13',
    							extensions: 'gif|jpg|jpeg|png',
    							totemp: '1',
    							fname: 'action'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	$( '#action_error' ).html( 'Неверное расширение файла' ).show();
                					return false;
					            }
       		
					            $( '#action_error' ).hide();
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								$( '#action_error' ).html( 'Ошибка обработки файла' ).show();
       							} else {
       								$( '#action_image' ).html( '<img src=\"".$mysql->settings['local_folder']."tmp/' + data + '\" />' );
       								$( '#action_error' ).hide();
       								
			       					$( '#image' ).attr( 'value', data );
			       					
       							}
			       			}
						} );
					} );
				</script>
                                <hr>
                                <p>
					Загрузите изображение для акции (мобильная версия). При загрузке учитывайте следующее:
					<ol>
						<li>Высота и ширина баннера может быть любая;</li>
						<li>Автоподгонка размеров НЕ используется.</li>
					</ol>
				</p>
				
				<label class=\"uploadbutton\" id=\"m_action_upload\">
					<span id=\"m_action_upload_innerspan\">
						Выберите файл
					</span>
				</label>
				
				<input type=hidden name=\"m_image\" id=\"m_image\" value=\"\" />
				<div class='error' id='m_action_error'></div>
				<div id='m_action_image' style='margin-top: 7px; margin-bottom: 7px;'></div>
				
				<script>
					$( '#m_action_upload' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'action',
       						action: '".$mysql->settings['local_folder']."admin/getblock',
       						data: {
    							external: '1',
    							type: '13',
    							extensions: 'gif|jpg|jpeg|png',
    							totemp: '1',
    							fname: 'action'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	$( '#m_action_error' ).html( 'Неверное расширение файла' ).show();
                					return false;
					            }
       		
					            $( '#m_action_error' ).hide();
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								$( '#m_action_error' ).html( 'Ошибка обработки файла' ).show();
       							} else {
       								$( '#m_action_image' ).html( '<img src=\"".$mysql->settings['local_folder']."tmp/' + data + '\" />' );
       								$( '#m_action_error' ).hide();
       								
			       					$( '#m_image' ).attr( 'value', data );
			       					
       							}
			       			}
						} );
					} );
				</script>
				
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
				<h1 align=left>Создание новой акции</h1>
				
				<form action=\"".$mysql->settings['local_folder']."admin/".$link."\" method=POST id='creatings' style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=hidden name=\"createaction\" id=\"createaction\" value=\"0\" />						
						<input type=button value=\"Создать\" class='button_input' onclick=\"
							if( $( '#title' ).attr( 'value' ) == '' ) { 
								alert( 'Укажите название акции' ); 
								return false; 
							}
							$( '#process' ).attr( 'value', 1 );
							$( '#createaction' ).attr( 'value', 1 );
							$( '#creatings' ).submit();
						\" />&nbsp;&nbsp;
						<input type=button value=\"Отменить\" class='button_input' onclick=\"$( '#creatings' ).submit();\" />
					</div>
				</form>
		";
	}
        
        function getExternalNewBanner( $link )
	{
		global $query, $mysql, $lang, $main, $utils;
		
		$inner = "
				<p>
					Заголовок блока: <label class='red'>*</label><br>
					<input type=text name=\"title\" id=\"title\" value=\"\" class='text_input' />
				</p>
				<p>
					Подзаголовок:<br>
					<textarea name=\"subtitle\" id=\"subtitle\" rows=7 class='textarea_input'></textarea>
				</p>
				<p>
					Ссылка для перехода:<br>
					<input type=text name=\"link\" id=\"link\" value=\"\" class='text_input' />
				</p>				
				<p>
					Дата начала показа блока: (формат: дд/мм/гггг чч:мм , пример: 07/12/2017 15:31) - оставьте поле пустым, чтобы использовать текущую дату и время<br>
					<input type=text name=\"start_date\" id=\"start_date\" value=\"".date( "d/m/Y H:i" )."\" class='text_input' />
				</p>
				<p>
					Дата окончания показа блока: (формат: дд/мм/гггг чч:мм , пример: 17/12/2017 17:31) - оставьте поле пустым, чтобы использовать текущую дату и время<br>
					<input type=text name=\"end_date\" id=\"end_date\" value=\"".date( "d/m/Y H:i" )."\" class='text_input' />
				</p>
				<p>
					Комментарий (для личного использования, не выводится на сайте):<br>
					<textarea name=\"comment\" id=\"comment\" rows=15 class='textarea_input'></textarea>
				</p>
				
				<p>
					Загрузите изображение для блока. При загрузке учитывайте следующее:
					<ol>
						<li>Высота и ширина картинки может быть любая;</li>
						<li>Автоподгонка размеров НЕ используется.</li>
					</ol>
				</p>
				
				<label class=\"uploadbutton\" id=\"action_upload\">
					<span id=\"action_upload_innerspan\">
						Выберите файл
					</span>
				</label>
				
				<input type=hidden name=\"image\" id=\"image\" value=\"\" />
				<div class='error' id='action_error'></div>
				<div id='action_image' style='margin-top: 7px; margin-bottom: 7px;'></div>
				
				<script>
					$( '#action_upload' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'action',
       						action: '".$mysql->settings['local_folder']."admin/getblock',
       						data: {
    							external: '1',
    							type: '13',
    							extensions: 'gif|jpg|jpeg|png',
    							totemp: '1',
    							fname: 'action'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	$( '#action_error' ).html( 'Неверное расширение файла' ).show();
                					return false;
					            }
       		
					            $( '#action_error' ).hide();
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								$( '#action_error' ).html( 'Ошибка обработки файла' ).show();
       							} else {
       								$( '#action_image' ).html( '<img src=\"".$mysql->settings['local_folder']."tmp/' + data + '\" />' );
       								$( '#action_error' ).hide();
       								
			       					$( '#image' ).attr( 'value', data );
			       					
       							}
			       			}
						} );
					} );
				</script>
                                
                                
			";
			
		return "
				<h1 align=left>Создание нового рекламного блока</h1>
				
				<form action=\"".$mysql->settings['local_folder']."admin/".$link."\" method=POST id='creatings' style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=hidden name=\"createbanner\" id=\"createbanner\" value=\"0\" />						
						<input type=button value=\"Создать\" class='button_input' onclick=\"
							if( $( '#title' ).val() == '' ) { 
								alert( 'Укажите название блока' ); 
								return false; 
							}
							$( '#process' ).val( 1 );
							$( '#createbanner' ).val( 1 );
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
		
		$data = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."actions` WHERE `id`=".$actionid );
		if( !$data ) {
			return "Unknown action id";
		}
		
		$mm = json_decode( $data['razd'] );
		foreach( $main->modules->modules as $k => $v ) {
			$rr .= "<option value='".$k."'".( $this->searchArrayForValue( $mm, $k ) ? " selected" : "" ).">".$v['instance']->getName()."</option>";
		}
		
		$inner = "
				<p>
					Название акции: <label class='red'>*</label><br>
					<input type=text name=\"title\" id=\"title\" value=\"".$data['title']."\" class='text_input' />
				</p>
				<p>
					Модули приложения: <br>
					<select name=\"razd[]\" id=\"razd\" class='select_input' multiple style='height: 350px;'>
						".$rr."
					</select>
				</p>
				<p>
					Ссылка для перехода: (введите, если хотите, чтобы при клике на баннер акции осуществлялся переход по какой либо ссылке, иначе при клике будет осуществляться переход в описании акции на сайте)<br>
					<input type=text name=\"link\" id=\"link\" value=\"".$data['link']."\" class='text_input' />
				</p>				
				<p>
					Дата начала акции: (формат: дд/мм/гггг чч:мм , пример: 07/10/2013 15:31) - оставьте поле пустым, чтобы использовать текущую дату и время<br>
					<input type=text name=\"start_date\" id=\"start_date\" value=\"".date( "d/m/Y H:i", $data['start_date'] )."\" class='text_input' />
				</p>
				<p>
					Дата окончания акции: (формат: дд/мм/гггг чч:мм , пример: 07/10/2013 15:31) - оставьте поле пустым, чтобы использовать текущую дату и время<br>
					<input type=text name=\"end_date\" id=\"end_date\" value=\"".date( "d/m/Y H:i", $data['end_date'] )."\" class='text_input' />
				</p>
				<p>
					Комментарий:<br>
					<textarea name=\"comment\" id=\"comment\" rows=25 class='textarea_input'>".$data['comment']."</textarea>
				</p>
				
				<p>
					Загрузите изображение для акции. При загрузке учитывайте следующее:
					<ol>
						<li>Высота и ширина баннера может быть любая. Тип файла любой;</li>
						<li>Автоподгонка размеров НЕ используется.</li>
					</ol>
				</p>
				
				<label class=\"uploadbutton\" id=\"action_upload\">
					<span id=\"action_upload_innerspan\">
						Выберите файл
					</span>
				</label>
				
				<input type=hidden name=\"image\" id=\"image\" value=\"\" />
				<div class='error' id='action_error'></div>
				<div id='action_image' style='margin-top: 7px; margin-bottom: 7px;'>
					".( $data['image'] ? "<img src=\"".$mysql->settings['local_folder']."files/upload/actions/".$data['image']."\" />" : "" )."
				</div>
				
				<script>
					$( '#action_upload' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'action',
       						action: '".$mysql->settings['local_folder']."admin/getblock',
       						data: {
    							external: '1',
    							type: '13',
    							extensions: 'gif|jpg|jpeg|png',
    							totemp: '1',
    							fname: 'action'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	$( '#action_error' ).html( 'Неверное расширение файла' ).show();
                					return false;
					            }
       		
					            $( '#action_error' ).hide();
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								$( '#action_error' ).html( 'Ошибка обработки файла' ).show();
       							} else {
       								$( '#action_image' ).html( '<img src=\"".$mysql->settings['local_folder']."tmp/' + data + '\" />' );
       								$( '#action_error' ).hide();
       								
			       					$( '#image' ).attr( 'value', data );
			       					
       							}
			       			}
						} );
					} );
				</script>
                                
                                <hr>
                                <p>
					Загрузите изображение для акции (мобильная версия). При загрузке учитывайте следующее:
					<ol>
						<li>Высота и ширина баннера может быть любая. Тип файла любой;</li>
						<li>Автоподгонка размеров НЕ используется.</li>
					</ol>
				</p>
				
				<label class=\"uploadbutton\" id=\"m_action_upload\">
					<span id=\"m_action_upload_innerspan\">
						Выберите файл
					</span>
				</label>
				
				<input type=hidden name=\"m_image\" id=\"m_image\" value=\"\" />
				<div class='error' id='m_action_error'></div>
				<div id='m_action_image' style='margin-top: 7px; margin-bottom: 7px;'>
					".( $data['m_image'] ? "<img src=\"".$mysql->settings['local_folder']."files/upload/actions/".$data['m_image']."\" />" : "" )."
				</div>
				
				<script>
					$( '#m_action_upload' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'action',
       						action: '".$mysql->settings['local_folder']."admin/getblock',
       						data: {
    							external: '1',
    							type: '13',
    							extensions: 'gif|jpg|jpeg|png',
    							totemp: '1',
    							fname: 'action'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	$( '#m_action_error' ).html( 'Неверное расширение файла' ).show();
                					return false;
					            }
       		
					            $( '#m_action_error' ).hide();
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								$( '#m_action_error' ).html( 'Ошибка обработки файла' ).show();
       							} else {
       								$( '#m_action_image' ).html( '<img src=\"".$mysql->settings['local_folder']."tmp/' + data + '\" />' );
       								$( '#m_action_error' ).hide();
       								
			       					$( '#m_image' ).attr( 'value', data );
			       					
       							}
			       			}
						} );
					} );
				</script>
				
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
				<h1 align=left>Редактирование акции «".$data['title']."»</h1>
				
				<form action=\"".$mysql->settings['local_folder']."admin/".$link."\" method=POST id='creatings' style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=hidden name=\"edit\" id=\"edit\" value=\"0\" />
						<input type=button value=\"Сохранить\" class='button_input' onclick=\"
							if( $( '#title' ).attr( 'value' ) == '' ) { 
								alert( 'Укажите название акции' ); 
								return false; 
							}
							$( '#process' ).attr( 'value', 1 );
							$( '#edit' ).attr( 'value', ".$actionid." );
							$( '#creatings' ).submit();
						\" />&nbsp;&nbsp;
						<input type=button value=\"Отменить\" class='button_input' onclick=\"$( '#creatings' ).submit();\" />
					</div>
				</form>
		";
	}
        
        function getExternalEditBanner( $link, $actionid )
	{
		global $query, $mysql, $lang, $main, $utils;
		
		$data = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."banners` WHERE `id`=".$actionid );
		if( !$data ) {
			return "Unknown banner id";
		}
		
		$inner = "
				<p>
					Название блока: <label class='red'>*</label><br>
					<input type=text name=\"title\" id=\"title\" value=\"".$data['title']."\" class='text_input' />
				</p>
				<p>
					Подзаголовок:<br>
					<textarea name=\"subtitle\" id=\"subtitle\" rows=7 class='textarea_input'>".$data['subtitle']."</textarea>
				</p>
				<p>
					Ссылка для перехода:<br>
					<input type=text name=\"link\" id=\"link\" value=\"".$data['link']."\" class='text_input' />
				</p>				
				<p>
					Дата начала показа блока: (формат: дд/мм/гггг чч:мм , пример: 07/12/2017 15:31) - оставьте поле пустым, чтобы использовать текущую дату и время<br>
					<input type=text name=\"start_date\" id=\"start_date\" value=\"".date( "d/m/Y H:i", $data['start_date'] )."\" class='text_input' />
				</p>
				<p>
					Дата окончания показа блока: (формат: дд/мм/гггг чч:мм , пример: 17/12/2017 17:31) - оставьте поле пустым, чтобы использовать текущую дату и время<br>
					<input type=text name=\"end_date\" id=\"end_date\" value=\"".date( "d/m/Y H:i", $data['end_date'] )."\" class='text_input' />
				</p>
				<p>
					Комментарий (для личного использования, не выводится на сайте):<br>
					<textarea name=\"comment\" id=\"comment\" rows=15 class='textarea_input'>".$data['comment']."</textarea>
				</p>
				
				<p>
					Загрузите изображение для блока. При загрузке учитывайте следующее:
					<ol>
						<li>Высота и ширина блока может быть любая. Тип файла любой;</li>
						<li>Автоподгонка размеров НЕ используется.</li>
					</ol>
				</p>
				
				<label class=\"uploadbutton\" id=\"action_upload\">
					<span id=\"action_upload_innerspan\">
						Выберите файл
					</span>
				</label>
				
				<input type=hidden name=\"image\" id=\"image\" value=\"\" />
				<div class='error' id='action_error'></div>
				<div id='action_image' style='margin-top: 7px; margin-bottom: 7px;'>
					".( $data['image'] ? "<img src=\"".$mysql->settings['local_folder']."files/upload/actions/".$data['image']."\" />" : "" )."
				</div>
				
				<script>
					$( '#action_upload' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'action',
       						action: '".$mysql->settings['local_folder']."admin/getblock',
       						data: {
    							external: '1',
    							type: '13',
    							extensions: 'gif|jpg|jpeg|png',
    							totemp: '1',
    							fname: 'action'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	$( '#action_error' ).html( 'Неверное расширение файла' ).show();
                					return false;
					            }
       		
					            $( '#action_error' ).hide();
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								$( '#action_error' ).html( 'Ошибка обработки файла' ).show();
       							} else {
       								$( '#action_image' ).html( '<img src=\"".$mysql->settings['local_folder']."tmp/' + data + '\" />' );
       								$( '#action_error' ).hide();
       								
			       					$( '#image' ).attr( 'value', data );
			       					
       							}
			       			}
						} );
					} );
				</script>
			";
			
		return "
				<h1 align=left>Редактирование блока «".$data['title']."»</h1>
				
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