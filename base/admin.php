<?php

if( !defined( "in_ochki" ) ) die( "You can't access this file directly" );

class Admin
{
	var $auth = false;
	
	var $ip = '';
	var $currentAgent = '';
		
	var $userLogin = '';
	var $userLevel = '';
	var $userArray = null;
	var $sid = null;
	
	var $settings = array();
	
	var $isOpera = false;
	var $isIE = false;
	var $isGecko = false;
	
	function init()
	{
		global $mysql;
		
		$this->ip = @htmlspecialchars( @addslashes( @getenv( REMOTE_ADDR ) ? @getenv( REMOTE_ADDR ) : @getenv( HTTP_X_FORWARDED_FOR ) ) );
		if( strlen( $this->ip ) > 15 )
			return false;
			
		$this->currentAgent = @htmlspecialchars( @addslashes( @getenv( HTTP_USER_AGENT ) ) );
		
		$ua = @getenv( HTTP_USER_AGENT );
		$this->isOpera = @eregi( "opera", $ua );
		$this->isIE = @eregi( "msie", $ua );
		$this->isGecko = @eregi( "gecko", $ua );

		$a = $mysql->mqm_spec( $mysql->t_prefix."settings" );
		while( $r = @mysql_fetch_assoc( $a ) )
			$this->settings[$r['name']] = $r;
		
		return true;
	}
	
	function start()
	{
		global $query, $mysql, $main;
		
		if( $query->gp( "php_version_checking" ) ) {
			print_r( $_POST );
			exit;
		}
		
		// Очистка каталога TMP (стираем файлы, которые загружены не позднее часа назад
		$d = time() - 3600;
		$dh = @opendir( ROOT_PATH."tmp/" );
		while( $dh && ( $file = @readdir( $dh ) ) !== false ) {
			if( $file !== '.' && $file !== '..' && $file != 'index.html' ) { 
        		if( @is_file( ROOT_PATH."tmp/".$file ) && filectime( ROOT_PATH."tmp/".$file ) <= $d )
        			@unlink( ROOT_PATH."tmp/".$file );
			}
		}
                
                $toDel = "";
                $a = $mysql->mqm( "SELECT `id`,`data` FROM `".$mysql->t_prefix."blobs` WHERE `date`<=".( time() - ( 24 * 30 * 3 ) ) );
                while( $r = @mysql_fetch_assoc( $a ) ) {
                    @unlink( ROOT_PATH."files/upload/usersData/".$r['data'] );
                    $toDel .= ( $toDel ? " OR " : "" )."`id`=".$r['id'];
                }
                if( $toDel )
                    $mysql->mu( "DELETE FROM `".$mysql->t_prefix."blobs` WHERE ".$toDel );
		
		// Очистка пользователей, которые не прошли активацию аккаунта через 1 день
		// $mysql->mu( "DELETE FROM `".$mysql->t_prefix."users` WHERE `auth`<>'' AND `rdate`<=".( time() - ( 3600 * $mysql->settings['activation_time_limit'] ) ) );
		
		if( !$this->checkAuth() ) {
			$this->loginPage();
		} else {
			define( "in_ochki_admin", 1 );
			$this->auth = true;
                        
                        if( $query->gp( "admin" ) && $query->gp( "users" )&& $query->gp( "list" ) && $query->gp( "export" ) ) {
                            $main->users->exportUsers();
                            exit;
                        }
			
			if( $query->gp_post( "external" ) ) {
				@include_once( ROOT_PATH."base/external.php" );
				$ext = new ExternalQueries();
				echo $ext->run();
				exit;
			} else 
				$this->run();
		}
	}
	
	function checkAuth()
	{
		global $query, $main;
		
		$sid = $query->gp_post( "usid", 32 );
		$c_sid = isset( $_COOKIE['ochki_usid'] ) && strlen( $_COOKIE['ochki_usid'] ) === 32 ? $query->fm( $_COOKIE['ochki_usid'] ) : null;
		
		$l = $query->gp_post( "ulogin" );
		if( strlen( $l ) > 50 )
			$l = null;
		$p = $query->gp_post( "upass" );
		if( strlen( $p ) > 100 )
			$p = null;
		
		if( $sid || $c_sid ) {
			
			$usid = $sid ? $sid : $c_sid;
			$ses = $main->users->getLastUserSessionBySID( $usid, $this->ip, $this->currentAgent, true );
			
			if( $ses ) {
				
				$u = $main->users->getUserById( $ses['user'], true );
				
				if( $u && time() - $ses['last'] < $this->settings['user_session_length']['value'] && !$u['block'] && $u['level'] <= 2 ) {
					
					$main->users->updateSessionTime( $ses );
					$this->userArray = $u;
					$this->userLevel = $u['level'];
					$this->sid = $ses['sid'];
					@setcookie( "ochki_usid", $ses['sid'], time() + $this->settings['user_session_length']['value'], LOCAL_FOLDER );
					
					return true;
					
				} else
					$main->users->closeSession( $usid );
				
			}
			
			$ses = null;
			if( $c_sid ) {
				@setcookie( "ochki_usid", '', time() + 31536000, LOCAL_FOLDER );
				$c_sid = "";
			}
			if( $sid )
				$sid = "";
			
		}
		
		if( $l && $p ) {
			
			$u = $main->users->getUserByLoginAndPassword( $l, $p, true );
			
			if( $u && !$u['block'] && $u['level'] <= 2 ) {
				
				$ses = $main->users->createSession( $u, $this->ip, $this->currentAgent );
				
				if( $ses ) {
					$this->userArray = $u;
					$this->userLevel = $u['level'];
					$this->sid = $ses['sid'];
					@setcookie( "ochki_usid", $ses['sid'], time() + $this->settings['user_session_length']['value'], LOCAL_FOLDER );
					@setcookie( "ochki_ulogin", $l, time() + 31536000, LOCAL_FOLDER );
					@setcookie( "ochki_upass", strrev( base64_encode( $p ) ), time() + 31536000, LOCAL_FOLDER );
					
					return true;
				}
				
			}
			
		}
		
		if( $l ) {
			$this->userLogin = $l;
			@setcookie( "ochki_ulogin", $l, time() + 31536000, LOCAL_FOLDER );
			@setcookie( "ochki_upass", '', time() + 31536000, LOCAL_FOLDER );
		}
		
		@setcookie( "ochki_usid", '', time() + 31536000, LOCAL_FOLDER );
		
		return false;
	}
	
	function loginPage()
	{
		global $lang;
		
		$l = ( !$this->userLogin ? ( isset( $_COOKIE['ochki_ulogin'] ) ? $_COOKIE['ochki_ulogin'] : '' ) : $this->userLogin );
		$p = ( isset( $_COOKIE['ochki_upass'] ) ? base64_decode( strrev( $_COOKIE['ochki_upass'] ) ) : '' );
		
		$t = "
			
			<!DOCTYPE html  \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
			<html xmlns=\"http://www.w3.org/1999/xhtml\">
			
			<head>
			<meta http-equiv=\"Content-Type\" content=\"text/html;charset=UTF-8\"/>
			<meta name=\"robots\" content=\"no-index, no-follow\" />
			<meta http-equiv=\"Content-Language\" content=\"".$lang->currentLanguageCL."\" />
			<title>I-OPTIC.RU - Система Администрирования</title>

			<link href=\"".LOCAL_FOLDER."css/ochki_admin.css\" rel=\"stylesheet\" type=\"text/css\"/>
			<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700,800&amp;subset=cyrillic' rel='stylesheet'>
			
			</head>
			
			<body>
			<div class='layer' style='left: 0px; top: 10px; width: 100%; text-align: center;'>
				<img src='".LOCAL_FOLDER."css/ochki_admin_logo.png' /><br />
			</div>
			
			<div class='layer full'><div>
				<form method=POST action='".LOCAL_FOLDER."admin' style='height: 100%;'>
				<table class='login_table' align=center>
					<tr>
						<td valign=middle align=center ".( $this->isGecko ? " style='font-size: 1.8em;'" : "" ).">
							<p>Авторизация</p>
							<input type=text name=\"ulogin\" value=\"".$l."\" class='inputfield' ".( $this->isGecko ? " style='width: 80%;'" : "" )." />
							<input type=password name=\"upass\" value=\"".$p."\" class='inputfield' ".( $this->isGecko ? " style='width: 80%;'" : "" )." />
							<input type=submit value='Войти' class='loginbutton' ".( $this->isGecko ? " style='width: 80%;'" : "" )." />
						</td>
					</tr>
				</table>
				</form>
			</div></div>
			
			<div class='copyright'>
				<b>I-OPTIC.RU - Система Администрирования, версия 1.1<br>Москва, 2012-".date( "Y" )."</b>
			</div>
			
			</body>
			</html>
		";
		
		echo str_replace( "\t", "", str_replace( "\n", "", str_replace( "\r", "", $t ) ) );
	}
	
	function run()
	{
		global $main, $query, $lang;
		
		if( $query->gp( "logout" ) ) {
			@setcookie( "ochki_usid", '', time() + 31536000, LOCAL_FOLDER );
			$main->users->closeSession( $this->sid );
			$this->auth = false;
			$this->userArray = null;
			header( "Location: ".LOCAL_FOLDER."admin" );
			exit;
		}
		
		header( 'Content-type: text/html; charset=utf-8' );
		header( 'X-XSS-Protection: 0' );
		
		$t = "
			
			<!DOCTYPE html  \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
			<html xmlns=\"http://www.w3.org/1999/xhtml\">
			
			<head>
			<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\"/>
			<meta name=\"robots\" content=\"no-index, no-follow\" />
			<meta http-equiv=\"content-language\" content=\"".$lang->currentLanguageCL."\" />
			<title>I-OPTIC.RU - Система Администрирования</title>
			
			<link href=\"".LOCAL_FOLDER."css/ochki_admin.css\" rel=\"stylesheet\" type=\"text/css\"/>
			<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700,800&amp;subset=cyrillic' rel='stylesheet'>
			<link type=\"text/css\" href=\"".LOCAL_FOLDER."jsf/jqui/themes/base/ui.all.css\" rel=\"stylesheet\" />
			
			<script type=\"text/javascript\" src=\"".LOCAL_FOLDER."jsf/jquery.1.4.1.js\"></script>
			<script type=\"text/javascript\" src=\"".LOCAL_FOLDER."jsf/jquery.ou.1.1.2.packed.js\"></script>

			<script type=\"text/javascript\" src=\"".LOCAL_FOLDER."jsf/jqui/ui/ui.core.js\"></script>
			<script type=\"text/javascript\" src=\"".LOCAL_FOLDER."jsf/jqui/ui/ui.draggable.js\"></script>
			<script type=\"text/javascript\" src=\"".LOCAL_FOLDER."jsf/jqui/ui/ui.resizable.js\"></script>
			<script type=\"text/javascript\" src=\"".LOCAL_FOLDER."jsf/jqui/ui/ui.dialog.js\"></script>
			<script type=\"text/javascript\" src=\"".LOCAL_FOLDER."jsf/jqui/ui/ui.tabs.js\"></script>
			<script type=\"text/javascript\" src=\"".LOCAL_FOLDER."jsf/jqui/ui/ui.accordion.js\"></script>

			<script src=\"".LOCAL_FOLDER."jsf/ochki.js\" type=\"text/javascript\"></script>
			<script src=\"".LOCAL_FOLDER."jsf/ochki_admin.js\" type=\"text/javascript\"></script>
			
			<script>
				$.ajax( { cache: false } );
			</script>
			
			</head>
			
			<body>
			
			<table cellspacing=0 cellpadding=0 border=0 width=100%>
				<tr>
					<td align=left style='background: url(".LOCAL_FOLDER."css/ochki_admin_topbg.png) repeat-x bottom left; padding-left: 20px;' height=95 valign=top>
						".$main->modules->gmi( "order" )->getNewOrderAdminBlock()."
						<a href='".LOCAL_FOLDER."admin'><img src='".LOCAL_FOLDER."css/ochki_admin_logo.png' style='margin-top: 24px;' /></a>
						<div style='position: relative; top: 4px;'></div>
					</td>
				</tr>
				
				<tr>
					<td align=left style='background: url(".LOCAL_FOLDER."css/ochki_admin_middlebg.png) repeat-x top left; padding: 10px;' valign=top>
						".$this->getInnerTable()."
					</td>
				</tr>
				
				<tr>
					<td align=center height=40 style='border-top: 2px solid #999; color: #999;' valign=middle>
						<b>I-OPTIC.RU - Система Администрирования, версия 1.1<br>Москва, 2012-".date( "Y" )."</b>
					</td>
				</tr>
				
			</table>
			<div style='position: fixed; z-index: 1000; top: 0px; bottom: 0px; left: 0px; right: 0px; background-color: rgba(0,0,0,0.5); display: none;' id='sendlayer'><div id='window' style='position: absolute; top: 50%; left: 50%; margin-top: -300px; margin-left: -400px; width: 800px; height: 600px; background-color: #fff; border: 2px solid #000; box-shadow: 5px 5px 15px rgba(0,0,0,0.8); text-align: left;'><img src='/images/remove.png' style='cursor: pointer; position: absolute; top: 10px; right: 10px; z-index: 500;' onclick=\"$( '#sendlayer' ).fadeOut( 200 );\" /><div id='success' style='position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px; background-color: #fff; z-index: 100; display: none;'><div style='font-size: 20px; color: #1b1b1b; font-weight: 700; margin-top: 285px; text-align: center; text-transform: uppercase;'>Сообщение отправлено</div></div>
                            <div style='margin-left: 20px; margin-top: 20px; font-size: 17px;'>
                                Введите адрес получателя:<br/>
                                <input type=text id='s_email' value='' style='height: 30px; border: 1px solid #1b1b1b; color: #1b1b1b; font-size: 17px; padding-left: 10px; padding-right: 10px; line-height: 30px; width: 50%;' />
                            </div>
                            <div style='margin-left: 20px; margin-top: 10px; font-size: 17px;'>
                                Введите тему сообщения:<br/>
                                <input type=text id='s_title' value='' style='height: 30px; border: 1px solid #1b1b1b; color: #1b1b1b; font-size: 17px; padding-left: 10px; padding-right: 10px; line-height: 30px; width: 50%;' />
                            </div>
                            <div style='margin-left: 20px; margin-top: 10px; font-size: 17px;'>
                                Отредактируйте текст сообщения:<br/>
                                <textarea id='s_message' data-first=1 style='height: 280px; border: 1px solid #1b1b1b; color: #1b1b1b; font-size: 14px; padding: 10px; width: 95%;'>".$lang->gp( 164 )."</textarea>
                            </div>
                            <div style='margin-left: 20px; margin-top: 10px;'>
                                <input type=button value='Отправить сообщение' onclick=\"sendOrderMessage();\" style='height: 30px; font-size: 18px;' />
                            </div>
                        </div></div>
			</body>
			</html>
		";
		
		echo $t;
	}
	
	function getInnerTable()
	{
		global $mysql;
		
		$t = "
		
			<table cellspacing=0 cellpadding=0 border=0 width=100%>
				
				<tr>
					<td width=250 valign=top style='padding-right: 10px;'>
						
						<div id=\"accordion\">
							".( $this->userLevel == 1 ? "<h3 style='background: url(".LOCAL_FOLDER."css/ochki_admin_accordbg.png) repeat-x top left;'><a href=\"#\">Система</a></h3>
							<div style='padding: 0px; margin-bottom: 10px;'>
								<a href='".LOCAL_FOLDER."admin/listings/global' class='admin_link'>Списки</a>
								<a href='".LOCAL_FOLDER."admin/properties/global' class='admin_link'>Параметры товаров</a>
								<a href='#' class='admin_link' onclick='return false;' style='cursor: default;'>---</a>
								<a href='".LOCAL_FOLDER."admin/settings' class='admin_link'>Системные установки</a>
								<a href='".LOCAL_FOLDER."admin/metas' class='admin_link'>Редактор META тегов</a>
								<a href=\"".LOCAL_FOLDER."admin/logout\" class='admin_link'>ВЫХОД</a>
							</div>" : "" )."
	
							<h3 style='background: url(".LOCAL_FOLDER."css/ochki_admin_accordbg.png) repeat-x top left;'><a href=\"#\">Модули и данные</a></h3>
							<div style='padding: 0px; margin-bottom: 10px;'>
								".( $this->userLevel == 1 ? "<a href='".LOCAL_FOLDER."admin/modules/list' class='admin_link'>Список модулей</a>" : "" )."
								".( $this->userLevel == 1 ? "<a href='".LOCAL_FOLDER."admin/modules/settings' class='admin_link'>Параметры модулей</a>" : "" )."
								".( $this->userLevel == 1 ? "<a href='#' class='admin_link' onclick='return false;' style='cursor: default;'>---</a>" : "" )."
								
								<a href='#' class='admin_link' onclick='return false;' style='cursor: default;'>---</a>
								<a href='".LOCAL_FOLDER."admin/modules/context' class='admin_link'>Содержание разделов</a>
								<a href='".LOCAL_FOLDER."admin/modules/actions' class='admin_link'>Акции</a>
                                                                    <a href='".LOCAL_FOLDER."admin/modules/discount' class='admin_link'>Скидки</a>
                                                                    <a href='".LOCAL_FOLDER."admin/modules/banners' class='admin_link'>Рекламные блоки</a>
								<a href='".LOCAL_FOLDER."admin/modules/issues' class='admin_link'>Статьи</a>
								<a href='".LOCAL_FOLDER."admin/modules/help' class='admin_link'>Помощь</a>
								<a href='".LOCAL_FOLDER."admin/modules/delivery' class='admin_link'>Доставка</a>
								
								<a href='#' class='admin_link' onclick='return false;' style='cursor: default;'>---</a>
								<a href='".LOCAL_FOLDER."admin/modules/catalog_admin' class='admin_link'>Основной каталог товаров</a>
                                                                <a href='".LOCAL_FOLDER."admin/modules/catalog_admin/import' class='admin_link'>Импорт из файла</a>
								<a href='".LOCAL_FOLDER."admin/modules/lenses' class='admin_link'>Линзы</a>

								<a href='#' class='admin_link' onclick='return false;' style='cursor: default;'>---</a>
								<a href='".LOCAL_FOLDER."admin/modules/order' class='admin_link'>Заказы</a>
                                                                    <a href='".LOCAL_FOLDER."admin/modules/fitting' class='admin_link'>Заказы по примерке</a>
								<a href='".LOCAL_FOLDER."admin/modules/order/stat' class='admin_link'>Статистика по заказам</a>
								<a href='".LOCAL_FOLDER."admin/modules/order/timestat' class='admin_link'>Статистика по времени</a>
								<a href='#' class='admin_link' onclick='return false;' style='cursor: default;'>---</a>
								<a href='".LOCAL_FOLDER."admin/modules/write_message' class='admin_link'>Написать письмо клиенту</a>
							</div>
	
							<h3 style='background: url(".LOCAL_FOLDER."css/ochki_admin_accordbg.png) repeat-x top left;'><a href=\"#\">Пользователи</a></h3>
							<div style='padding: 0px; margin-bottom: 10px;'>
								".( $this->userLevel == 1 ? "<a href='".LOCAL_FOLDER."admin/users/actypes' class='admin_link'>Типы аккаунтов</a>" : "" )."
								<a href='".LOCAL_FOLDER."admin/users/list' class='admin_link'>Список пользователей</a>
								<a href='".LOCAL_FOLDER."admin/users/actions' class='admin_link invisible'>Действия пользователей</a>
							</div>
					
							".( $this->userLevel == 1 ? "<h3 style='background: url(".LOCAL_FOLDER."css/ochki_admin_accordbg.png) repeat-x top left;'><a href=\"#\">Языки и фразы</a></h3>
							<div style='padding: 0px;'>
								<a href='".LOCAL_FOLDER."admin/langs/global' class='admin_link'>Общие фразы</a>
								<a href='".LOCAL_FOLDER."admin/langs' class='admin_link'>Дополнительные фразы</a>
								<a href='".LOCAL_FOLDER."admin/styles' class='admin_link'>РУЧНЫЕ СТИЛИ</a>
								<a href='".LOCAL_FOLDER."admin/qphrases' class='admin_link'>Фразы для вопросиков</a>
								<a href='".LOCAL_FOLDER."admin/modules/mailtemplates' class='admin_link'>Шаблоны писем</a>
							</div>" : "" )."
						</div>
					</td>
					<td valign=top>
						".$this->runInner()."
					</td>
				</tr>
			
			</table>
		
		";
		
		return $t;
	}
	
	function runInner()
	{
		global $query, $lang, $main, $mysql;
		
		$selectedElement = 1;
		
		if( $query->gp( "modules" ) ) {
			
			if( $query->gp( "list" ) && $this->userLevel == 1 ) {
				$t = $main->modules->getAdminListScreen( $selectedElement, "modules/list" );
			} else if( $query->gp( "settings" ) && $this->userLevel == 1 ) {
				$t = $main->modules->getAdminSettingsScreen( $selectedElement, "modules/settings" );
			
			} else if( $query->gp( "catalog_admin" ) ) {
				$catalog_admin = $main->modules->getModuleInstanceByLocal( "catalog_admin" );
				$t = $catalog_admin->getAdminScreen( $selectedElement, "modules/catalog_admin" );
				
			} else if( $query->gp( "context" ) ) {
				
				$t = $this->getContextScreen( $selectedElement, "modules/context" );
				
			} else if( $query->gp( "write_message" ) ) {
				
				$t = $this->getwrite_messageScreen( $selectedElement, "modules/write_message" );
				
			} else if( $query->gp( "actions" ) ) {
				
				$actions = $main->modules->getModuleInstanceByLocal( "actions" );
				$t = $actions->getAdminScreen( $selectedElement, "modules/actions".( $query->gp( "global" ) ? "/global" : "" ) );
                                
                        } else if( $query->gp( "discount" ) ) {
				
				$actions = $main->modules->getModuleInstanceByLocal( "discount" );
				$t = $actions->getAdminScreen( $selectedElement, "modules/discount" );
                                
                        } else if( $query->gp( "banners" ) ) {
				
				$actions = $main->modules->getModuleInstanceByLocal( "actions" );
				$t = $actions->getAdminBannersScreen( $selectedElement, "modules/banners".( $query->gp( "global" ) ? "/global" : "" ) );
				
			} else if( $query->gp( "issues" ) ) {
				
				$usefultips = $main->modules->getModuleInstanceByLocal( "issues" );
				$t = $usefultips->getAdminScreen( $selectedElement, "modules/issues" );
				
			} else if( $query->gp( "help" ) ) {
				
				$usefultips = $main->modules->getModuleInstanceByLocal( "help" );
				$t = $usefultips->getAdminScreen( $selectedElement, "modules/help" );
				
			} else if( $query->gp( "news" ) ) {
				
				$news = $main->modules->getModuleInstanceByLocal( "news" );
				$t = $news->getAdminScreen( $selectedElement, "modules/news" );
				
			} else if( $query->gp( "lenses" ) ) {
				
				$lenses = $main->modules->getModuleInstanceByLocal( "lenses" );
				$t = $lenses->getAdminScreen( $selectedElement, "modules/lenses" );
				
			} else if( $query->gp( "order" ) ) {
				
				$order = $main->modules->gmi( "order" );
				$t = $order->getAdminScreen( $selectedElement, "modules/order".( $query->gp( "stat" ) ? "/stat" : "" ).( $query->gp( "timestat" ) ? "/timestat" : "" ) );
                                
                        } else if( $query->gp( "fitting" ) ) {
				
				$fitting = $main->modules->gmi( "fitting" );
				$t = $fitting->getAdminScreen( $selectedElement, "modules/fitting" );
				
			} else if( $query->gp( "delivery" ) ) {
				
				$delivery = $main->modules->getModuleInstanceByLocal( "delivery" );
				$t = $delivery->getAdminScreen( $selectedElement, "modules/delivery" );
				
			} else if( $query->gp( "mailtemplates" ) ) {
				$t = $this->getmailtemplatesScreen( $selectedElement, "modules/mailtemplates" );
				
			}
			
		} else if( $query->gp( "users" ) ) {
			
			if( $query->gp( "actypes" ) && $this->userLevel == 1 ) {
				$t = $main->users->getAdminActScreen( $selectedElement, "users/actypes" );
			} else if( $query->gp( "list" ) ) {
				$t = $main->users->getAdminListScreen( $selectedElement, "users/list" );
			} else if( $query->gp( "actions" ) ) {
				$t = $main->users->getAdminActionsScreen( $selectedElement, "users/actions" );
			}
			
		} else if( $query->gp( "settings" ) && $this->userLevel == 1 ) {
			
			$t = $this->getSettingsScreen( $selectedElement, "settings" );
			
		} else if( $query->gp( "langs" ) && $this->userLevel == 1 ) {
			
			$t = $lang->getAdminScreen( $selectedElement, "langs".( $query->gp( "global" ) ? "/global" : "" ) );
			
		} else if( $query->gp( "styles" ) ) {
			$t = $this->getstylesScrtreg( $selectedElement, "styles" );
			
		} else if( $query->gp( "listings" ) && $this->userLevel == 1 ) {
			
			$t = $main->listings->getAdminScreen( $selectedElement, "listings".( $query->gp( "global" ) ? "/global" : "" ) );			
			
		} else if( $query->gp( "properties" ) && $this->userLevel == 1 ) {
			
			$t = $main->properties->getAdminScreen( $selectedElement, "properties".( $query->gp( "global" ) ? "/global" : "" ) );
			
		} else if( $query->gp( "metas" ) && $this->userLevel == 1 ) {
			
			$metas = $main->modules->getModuleInstanceByLocal( "metas" );
			$t = $metas->getAdminScreen( $selectedElement, "metas" );		
			
		} else if( $query->gp( "qphrases" ) ) {
				
				$t = $this->getqphraseScreen( $selectedElement, "qphrases" );
			
		} else {
			
			$t = "
			<h1>Добро пожаловать в Систему Администрирования «I-OPTIC.RU»</h1>
			<p>
				Выберите нужный раздел для редактирования
			</p>
			";
			
		}
		
		return $t."
			<script type=\"text/javascript\">
				$(function() {
					$(\"#accordion\").accordion({
						autoHeight: false,
						collapsible: true,
						active: ".$selectedElement.",
						icons: {
    						header: \"ui-icon-circle-arrow-e\",
   							headerSelected: \"ui-icon-circle-arrow-s\"
						}
					});
				});
			</script>
		";
	}
	
	function getstylesScrtreg( &$selectedElement, $path )
	{
		global $mysql, $query, $main;
		
		$saved = "";
		if( $query->gp( 'process' ) ) {
			$data = $_POST['data'];
			@file_put_contents( ROOT_PATH."files/self.css", $data );
			$saved = "<label class='green' style='padding-left: 20px;'>Данные успешно сохранены</label>";
		}
		
		$q = @file_get_contents( ROOT_PATH."files/self.css" );		
		
		$inner .= "
		<p>
			Далее редактируем файл с РУЧНЫМИ СТИЛЯМИ:<br>
			<textarea name='data' style='width: 100%' rows=50>".$q."</textarea>
		</p>
		";
		
		$selectedElement = 3;
		$t = "
			<h1>РУЧНЫЕ СТИЛИ</h1>
			
			<form action=\"".LOCAL_FOLDER."admin/".$path."\" method=POST onsubmit=\"$('#process').attr('value',1);return true;\">
				".$inner."
				<div style='width: 500px; border-top: 1px solid #aaa; padding-top: 5px;'>
					<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
					<input type=submit value=\"Сохранить\" class='button_input' title='Сохранить измененные данные' />".$saved."
				</div>
			</form>
		";
		
		return $t;
	}
	
	function getSettingsScreen( &$selectedElement, $path )
	{
		global $mysql, $query;
		
		$saved = "";
		if( $query->gp( 'process' ) ) {
			foreach( $this->settings as $k => $v ) {
				if( $v['id'] >= 30 )
					continue;
				$val = $query->gp( "setting_".$v['id'] );
				$mysql->mu( "UPDATE `".$mysql->t_prefix."settings` SET `value`='".$val."' WHERE `id`=".$v['id'] );
				$this->settings[$k]['value'] = $val;
				$mysql->settings[$k] = $val;
			}
			$saved = "<label class='green' style='padding-left: 20px;'>Данные успешно сохранены</label>";
		}
		
		$inner = "";
		foreach( $this->settings as $k => $v ) {
			if( $v['id'] >= 30 )
				continue;
			$inner .= "
			<p>
				".$mysql->settings[$k.'_comment'].":<br>
				<input type=text name=\"setting_".$v['id']."\" value=\"".$v['value']."\" class='text_input' />
			</p>
			";
		}
		
		$selectedElement = 0;
		$t = "
			<h1>Системные установки</h1>
			
			<form action=\"".LOCAL_FOLDER."admin/".$path."\" method=POST onsubmit=\"$('#process').attr('value',1);return true;\">
				".$inner."
				<div style='width: 500px; border-top: 1px solid #aaa; padding-top: 5px;'>
					<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
					<input type=submit value=\"Сохранить\" class='button_input' title='Сохранить измененные данные' />".$saved."
				</div>
			</form>
		";
		
		return $t;
	}
	
	function getContextScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $main, $lang, $utils;
		
		$sel = $query->gp( "sel" );
		$sel = $sel ? $sel : 0;

		$ar = array();
		foreach( $main->modules->modules as $v ) {
			if( $v['content'] )
				$ar[$v['instance']->dbinfo['id']] = $v['instance'];
		}
		$saved = "";
		if( $query->gp( 'process' ) && $sel ) {
			
			$value = $main->modules->getModuleParam( $ar[$sel]->dbinfo['id'], "context_data_" );
			$phrase = $value && is_numeric( $value ) ? $value : 0;
			
			$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."langs` WHERE 1 ORDER BY `id` ASC" );
			while( $r = @mysql_fetch_assoc( $a ) ) {
				$text = $_POST["text_".$r['id']];
				$text = $text ? @str_replace( "'", "\\'", $text ) : "";
				
				if( $phrase ) {
					$lang->updatePhrase( $phrase, $text, $r['id'], $sel );
				} else if( $text ) {
					$phrase = $lang->addNewPhrase( $text, $r['id'], $sel, true );
				}
				
			}
			
			$main->modules->setModuleParam( $ar[$sel]->dbinfo['id'], 'context_data_', $phrase, "!!! Не менять руками !!!" );
			
			$saved = "<label class='green' style='padding-left: 20px;'>Данные успешно сохранены</label>";
			
		}
		
		$inner = "
		
		Выберите раздел для редактирования:<br>
		<input type=hidden name='setting_sel' id='setting_sel' value=0 />
		<select name=\"sel\" style='width: 300px;' onchange=\"$('#setting_sel').attr('value',1);$( '#eform' ).submit();\">
			<option value=\"0\"".( $sel === 0 ? " selected" : "" ).">Выберите раздел</option>
		";
		
		foreach( $ar as $k => $v ) {
			$inner .= "<option value=\"".$k."\"".( $sel == $k ? " selected" : "" ).">".$lang->gp( $v->dbinfo['name'] )."</option>";
		}
		
		$inner .= "
		</select><br><br>
		
		";
		
		if( $sel ) {
			
			if( !@opendir( ROOT_PATH."files/upload/any/".$sel ) ) {
				@mkdir( ROOT_PATH."files/upload/any/".$sel );
				@chmod( ROOT_PATH."files/upload/any/".$sel, 0777 );
				@file_put_contents( ROOT_PATH."files/upload/any/".$sel."/index.html", "Nothing here" );
			}
			if( !@opendir( ROOT_PATH."files/upload/any/".$sel."_files" ) ) {
				@mkdir( ROOT_PATH."files/upload/any/".$sel."_files" );
				@chmod( ROOT_PATH."files/upload/any/".$sel."_files", 0777 );
				@file_put_contents( ROOT_PATH."files/upload/any/".$sel."_files/index.html", "Nothing here" );
			}
			
			$value = $main->modules->getModuleParam( $ar[$sel]->dbinfo['id'], "context_data_" );
			$value = $value ? $value : '';
			
			$l = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."langs` WHERE `id`=".$lang->currentLanguage );
			$phrase = $value && is_numeric( $value ) ? $value : 0;
			if( $phrase ) {
				$p = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."langvars` WHERE `phrase_id`=".$phrase." AND `lang_id`=".$lang->currentLanguage );
			}
			
			$inner .= "
			<script type=\"text/javascript\" src=\"/jsf/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>
			Содержание (по языкам):<br><br>
			
			".$l['name'].":<br>
			<textarea name=\"text_".$l['id']."\" id=\"text_".$l['id']."\" class='textarea_input' rows=30>".( isset( $p ) ? $p['value'] : "" )."</textarea>
			<label id='hid_trans' style='display: none'></label>
			";
			
			$prevcode = $l['content-language'];
			$previd = "text_".$l['id'];
			
			$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."langs` WHERE `id`<>".$lang->currentLanguage." ORDER BY `id` ASC" );
			while( $r = @mysql_fetch_assoc( $a ) ) {
				unset( $p );
				if( $phrase )
					$p = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."langvars` WHERE `phrase_id`=".$phrase." AND `lang_id`=".$r['id'] );
				$nextcode = $r['content-language'];
				$nextid = "text_".$r['id'];
				$inner .= "<br>
				<p style='display: none;'>
					<a href='#' title='Автоперевод сверху вниз' class='forallunknowns' onclick=\"
						if( $( '#".$previd."' ).attr( 'value' ) == '' ) {
							alert( 'Укажите фразу для перевода' );
							return false;
						}
						$( 'html' ).css( 'cursor', 'wait' ); 
						$( '#".$nextid."' ).attr( 'disabled', true );
						translatePhrase( '".$prevcode."', '".$nextcode."', $( '#".$previd."' ).attr( 'value' ), '".$nextid."', 'hid_trans' );
					\">Автоперевод сверху вниз</a>
						<label class='line_between_links'>|</label>
					<a href='#' title='Автоперевод снизу вверх' class='forallunknowns' onclick=\"
						if( $( '#".$nextid."' ).attr( 'value' ) == '' ) {
							alert( 'Укажите фразу для перевода' );
							return false;
						}
						$( 'html' ).css( 'cursor', 'wait' ); 
						$( '#".$previd."' ).attr( 'disabled', true );
						translatePhrase( '".$nextcode."', '".$prevcode."', $( '#".$nextid."' ).attr( 'value' ), '".$previd."', 'hid_trans' );
					\">Автоперевод снизу вверх</a>
				</p>
				".$r['name'].":<br>
				<textarea name=\"".$nextid."\" id=\"".$nextid."\" class='textarea_input' rows=30>".( isset( $p ) ? $p['value'] : "" )."</textarea>
				";
				$prevcode = $nextcode;
				$previd = $nextid;
			}
			
			$inner .= "
				
				
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
        
        plugin_smimage_directory : '/files/upload/any/".$sel."',
        plugin_smexplorer_directory : '/files/upload/any/".$sel."_files',
		file_browser_callback : 'SMPlugins',       

		
		template_replace_values : {
			username : \"Some User\",
			staffid : \"991234\"
		}
	});
</script>	
			";
			
		}
		
		$selectedElement = 1;
		$t = "
			<h1>Редактирование содержания разделов</h1>
			
			<form action=\"".LOCAL_FOLDER."admin/".$path."\" method=POST id='eform'>
				".$inner."
				<table style='border-top: 1px solid #aaa; margin-top: 5px;' width=500><tr><td>
					<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
					<input type=submit value=\"Сохранить\" class='button_input' title='Сохранить измененные данные' onclick=\"$('#process').attr('value',1);return true;\"/>".$saved."
				</td></tr></table>
			</form>
		";
		
		return $t;
	}
	
	function getwrite_messageScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $main, $lang, $utils;
		
		$saved = "";
  
		if( $query->gp( 'process' ) ) {
			
			$text = $_POST["text"];
			$text = $text ? @str_replace( "'", "\\'", $text ) : "";
			
			$title = $_POST["title"];
			$title = $title ? @str_replace( "'", "\\'", $title ) : "";
			
			$adr = $_POST["adr"];
			
			$adr = $adr ? @str_replace( "'", "\\'", $adr ) : "";
			
			/*$from = $_POST["from_adr"];*/
			$from = "info@i-optic.ru";
            
			$mail = $main->modules->gmi( "mail_agent" );
   
			$mail->sendMessage( $adr, $from, $title, $text );
			
			$saved = "<h2 style='color: #0000ff;'><b>Письмо отправлено!</b></h2>";
			
		}
		
		$t = "
		<h1>Формирование и отправка писем клиентам</h1>
		
		".$saved."
		
		<form action=\"".LOCAL_FOLDER."admin/".$path."\" method=POST id='eform'>
		
			<p>
				От кого:<br>
				<select name='from_adr' class='select_input'>
					<option value='info@i-optic.ru'>info@i-optic.ru</option>
				</select>
			</p>
		
			<p>
				E-Mail адрес получателя: <label class='red'>*</label><br>
				<input type=text value='' name='adr' id='adr' class='text_input' />
			</p>
			
			<p>
				Тема сообщения: <label class='red'>*</label><br>
				<input type=text value='' name='title' id='title' class='text_input' />
			</p>
			
			<p>
				Сообщение: <label class='red'>*</label><br>
				<textarea type=text name='text' id='text' class='textarea_input' rows=10></textarea>
			</p>
		
			<table style='border-top: 1px solid #aaa; margin-top: 10px;' width=500><tr><td style='padding-top: 10px;'>
				<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
				<input type=submit value=\"Отправить\" class='button_input' onclick=\"	
					if( $( '#adr' ).val() == '' ) {
						alert( 'Укажите e-mail' );
						return false;
					}
					if( $( '#title' ).val() == '' ) {
						alert( 'Укажите заголовок' );
						return false;
					}
					if( $( '#title' ).val() == '' ) {
						alert( 'Укажите текст сообщения' );
						return false;
					}
					$('#process').attr( 'value', 1 );
					return true;
				\"/>
			</td></tr></table>
		</form>
		";

		return $t;
	}
	
	function getqphraseScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $main, $lang, $utils;
		
		$saved = "";
		
		$selectedElement = 3;
		
		$from = $_POST["from"];
		
		if( $from && $query->gp( 'process' ) ) {
			
			$text = $_POST["text"];
			$text = $text ? @str_replace( "'", "\\'", $text ) : "";
			
			$elem = $main->listings->getListingElementById( 28, $from, true );
			
			if( $elem ) {
				$phrase = $elem['additional_info'] && is_numeric( $elem['additional_info'] ) ? $elem['additional_info'] : 0;
				
				if( $phrase ) {
					$lang->updatePhrase( $phrase, $text, 1, 0 );
				} else if( $text ) {
					$phrase = $lang->addNewPhrase( $text, 1, 0, true );
					$main->listings->setListAddData( $from, $phrase, true );
				}
				
				$saved = "<h2 style='color: #0000ff;'><b>Данные сохранены</b></h2>";
			}
			
		}
		
		$t = "
		<h1>Фразы для вопросиков</h1>
		
		".$saved."
		
		<form action=\"".LOCAL_FOLDER."admin/".$path."\" method=POST id='eform'>
		
			<p>
				Тип вопросика:<br>
				<select name='from' class='select_input' onchange=\"$( '#eform' ).submit();\">
					".$main->listings->getListingForSelecting( 28, $from, 0, "<option value='0'".( !$from ? " selected" : "" ).">Выберите тип вопросика</option>", "", true, '', true )."
				</select>
			</p>
			
		";
		
		if( !$from ) {
			return $t."</form>";
		}
		
		$elem = $main->listings->getListingElementById( 28, $from, true );
		$phrase = $elem['additional_info'] && is_numeric( $elem['additional_info'] ) ? $elem['additional_info'] : 0;
		
		$t .= "
		
			<p>
				Фраза: <br>
				<textarea type=text name='text' id='text' class='textarea_input' rows=40>".( $phrase ? $lang->gp( $phrase ) : '' )."</textarea>
			</p>
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
        
        plugin_smimage_directory : '/files/upload/lenses',
        plugin_smexplorer_directory : '/files/upload/lenses',
		file_browser_callback : 'SMPlugins',       

		
		template_replace_values : {
			username : \"Some User\",
			staffid : \"991234\"
		}
	});
</script>	
		
			<table style='border-top: 1px solid #aaa; margin-top: 10px;' width=500><tr><td style='padding-top: 10px;'>
				<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
				<input type=submit value=\"Сохранить\" class='button_input' onclick=\"	
					$('#process').attr( 'value', 1 );
					return true;
				\"/>
			</td></tr></table>
		</form>
		";

		return $t;
	}
	
	function getmailtemplatesScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $main, $lang, $utils;
		
		$sel = $query->gp( "sel" );
		$sel = $sel ? $sel : 0;

		$ar = array(
			105 => "Письмо после регистрации (№ 105)",
			122 => "Письмо с запросом на восстановлением пароля (№ 122)",
			126 => "Письмо с подтверждением восстановления пароля (№ 126)",
			137 => "Письмо с заказом на примерку для клиента (№ 137)",
			139 => "Письмо с заказом на примерку для админа (№ 139)",
			132 => "Письмо с новым заказом для клиента (№ 132)",
			134 => "Письмо с новым заказом для админа (№ 134)",
                        159 => "Письмо с подпиской на новости сайта и с промокодом (№ 159)",
                        164 => "Письмо для заказчика (№ 164)"
		);
		

		$saved = "";
		if( $query->gp( 'process' ) && $sel ) {
			
			$phrase = $sel;
			
			$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."langs` WHERE 1 ORDER BY `id` ASC" );
			while( $r = @mysql_fetch_assoc( $a ) ) {
				$text = $_POST["text_".$r['id']];
				$text = $text ? @str_replace( "'", "\\'", $text ) : "";
				
				if( $phrase ) {
					$lang->updatePhrase( $phrase, $text, $r['id'], $sel );
				} else if( $text ) {
					$phrase = $lang->addNewPhrase( $text, $r['id'], $sel, true );
				}
				
			}
			
			$saved = "<label class='green' style='padding-left: 20px;'>Данные успешно сохранены</label>";
			
		}
		
		$inner = "
		
		Выберите шаблон для редактирования:<br>
		<input type=hidden name='setting_sel' id='setting_sel' value=0 />
		<select name=\"sel\" id='sel' style='width: 300px;' onchange=\"$('#setting_sel').attr('value',1);$( '#eform' ).submit();\">
			<option value=\"0\"".( $sel === 0 ? " selected" : "" ).">Выберите шаблон</option>
		";
		
		foreach( $ar as $k => $v ) {
			$inner .= "<option value=\"".$k."\"".( $sel == $k ? " selected" : "" ).">".$v."</option>";
		}
		
		$inner .= "
		</select><br><br>
		
		";
		
		if( $sel ) {
			
			$value = $sel;
			
			$l = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."langs` WHERE `id`=".$lang->currentLanguage );
			$phrase = $value && is_numeric( $value ) ? $value : 0;
			if( $phrase ) {
				$p = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."langvars` WHERE `phrase_id`=".$phrase." AND `lang_id`=".$lang->currentLanguage );
			}
			
			$inner .= "
			Содержание (по языкам):<br><br>
			
			".$l['name'].":<br>
			<textarea name=\"text_".$l['id']."\" id=\"text_".$l['id']."\" class='textarea_input' rows=30>".( isset( $p ) ? $p['value'] : "" )."</textarea>
			<label id='hid_trans' style='display: none'></label>
			";
			
			$prevcode = $l['content-language'];
			$previd = "text_".$l['id'];
			
			$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."langs` WHERE `id`<>".$lang->currentLanguage." ORDER BY `id` ASC" );
			while( $r = @mysql_fetch_assoc( $a ) ) {
				unset( $p );
				if( $phrase )
					$p = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."langvars` WHERE `phrase_id`=".$phrase." AND `lang_id`=".$r['id'] );
				$nextcode = $r['content-language'];
				$nextid = "text_".$r['id'];
				$inner .= "<br>
				<p style='display: none;'>
					<a href='#' title='Автоперевод сверху вниз' class='forallunknowns' onclick=\"
						if( $( '#".$previd."' ).attr( 'value' ) == '' ) {
							alert( 'Укажите фразу для перевода' );
							return false;
						}
						$( 'html' ).css( 'cursor', 'wait' ); 
						$( '#".$nextid."' ).attr( 'disabled', true );
						translatePhrase( '".$prevcode."', '".$nextcode."', $( '#".$previd."' ).attr( 'value' ), '".$nextid."', 'hid_trans' );
					\">Автоперевод сверху вниз</a>
						<label class='line_between_links'>|</label>
					<a href='#' title='Автоперевод снизу вверх' class='forallunknowns' onclick=\"
						if( $( '#".$nextid."' ).attr( 'value' ) == '' ) {
							alert( 'Укажите фразу для перевода' );
							return false;
						}
						$( 'html' ).css( 'cursor', 'wait' ); 
						$( '#".$previd."' ).attr( 'disabled', true );
						translatePhrase( '".$nextcode."', '".$prevcode."', $( '#".$nextid."' ).attr( 'value' ), '".$previd."', 'hid_trans' );
					\">Автоперевод снизу вверх</a>
				</p>
				".$r['name'].":<br>
				<textarea name=\"".$nextid."\" id=\"".$nextid."\" class='textarea_input' rows=50>".( isset( $p ) ? $p['value'] : "" )."</textarea>
				";
				$prevcode = $nextcode;
				$previd = $nextid;
			}
			
		}
		
		$selectedElement = 3;
		$t = "
			<h1>Редактирование шаблонов писем</h1>
			
			<form action=\"".LOCAL_FOLDER."admin/".$path."\" method=POST id='eform'>
				".$inner."
				<table style='border-top: 1px solid #aaa; margin-top: 5px;' width=500><tr><td>
					<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
					".( $sel ? "
					<input type=submit value=\"Сохранить\" class='button_input' title='Сохранить измененные данные' onclick=\"$('#process').attr('value',1);return true;\"/>
					&nbsp;&nbsp;&nbsp;&nbsp;
					<input type=submit value=\"Отменить\" class='button_input' title='Сохранить измененные данные' onclick=\"$( '#sel' ).val( 0 ); return true;\"/>".$saved."
					" : "" )."
				</td></tr></table>
			</form>
		";
		
		return $t;
	}
}

?>