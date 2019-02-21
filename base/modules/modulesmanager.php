<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

class ModulesManager
{
	var $modules = array();
	var $pathTomodules = "base/modules/";
	
	function init()
	{
		global $mysql;
		
		if( !file_exists( ROOT_PATH.$this->pathTomodules."rootmodule.php" ) ) {
			die( $lang->getPh( 1 ) );
		}
		@include_once( ROOT_PATH.$this->pathTomodules."rootmodule.php" );
		
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."modules` WHERE `include`=1 ORDER BY `order` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			if( !file_exists( ROOT_PATH.$this->pathTomodules.$r['local']."/index.php" ) ) {
				continue;
			}
			$this->modules[$r['id']] = $r;
			$this->modules[$r['id']]['instance'] = null;
			@include_once( ROOT_PATH.$this->pathTomodules.$r['local']."/index.php" );
			eval( "\$this->modules[\$r['id']]['instance'] = new module".$r['local']."();" );
			$this->modules[$r['id']]['instance']->init( $r );
		}
	}
	
	function getModuleNameById( $id )
	{
		global $mysql;
		
		if( isset( $this->modules[$id] ) )
			return $this->modules[$id]['local'];
		else {
			$r = $mysql->mq( "SELECT `local` FROM `".$mysql->t_prefix."modules` WHERE `id`=".$id );
			return $r ? $r['local'] : "";
		}
	}
	
	function getModuleInstanceByLocal( $local )
	{
		if( !$local )
			return null;
		
		foreach( $this->modules as $v )
			if( $v['local'] == $local )
				return $v['instance'];
		
		return null;
	}
	
	function getModuleInstanceByID( $id )
	{
		if( !$id )
			return null;
		
		return isset( $this->modules[$id] ) ? $this->modules[$id]['instance'] : null;
	}
	
	function gmi( $local )
	{
		return $this->getModuleInstanceByLocal( $local );
	}
	
	function gmii( $id )
	{
		return $this->getModuleInstanceByID( $id );
	}
	
	function getModuleParam( $moduleid, $paramName )
	{
		global $mysql;
		
		$r = $mysql->mq( "SELECT `value` FROM `".$mysql->t_prefix."modules_settings` WHERE `module`=".$moduleid." AND `setting_name`='".$paramName."'" );
		
		return $r ? $r['value'] : false;
	}
	
	function setModuleParam( $moduleid, $paramName, $newValue, $comment = '' )
	{
		global $mysql;
		
		if( $mysql->mq( "SELECT `module` FROM `".$mysql->t_prefix."modules_settings` WHERE `module`=".$moduleid." AND `setting_name`='".$paramName."'" ) )
			$mysql->mu( "UPDATE `".$mysql->t_prefix."modules_settings` SET `value`='".$newValue."' WHERE `module`=".$moduleid." AND `setting_name`='".$paramName."'" );
		else {
			$a = $mysql->mqm( "SELECT `id` FROM `".$mysql->t_prefix."modules_settings` WHERE 1 ORDER BY `id` ASC" );
			$newid = 1;
			while( $r = @mysql_fetch_assoc( $a ) ) {
				if( $r['id'] == $newid ) {
					$newid++;
				} else 
					break;
			}
			
			$mysql->mu( "INSERT INTO `".$mysql->t_prefix."modules_settings` VALUES(".$newid.",".$moduleid.",'".$paramName."','".$newValue."','".$comment."');" );
		}
	}
	
	function getModuleParams( $moduleid )
	{
		global $mysql;
		
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."modules_settings` WHERE `module`=".$moduleid );
		$params = array();
		while( $r = @mysql_fetch_assoc( $a ) )
			$params[$r['setting_name']] = $r['value'];
		
		return $params ? $params : null;
	}
	
	function getModulesCSS()
	{
		foreach( $this->modules as $k => $v ) {
			if( $v['instance']->isSelected() )
				return $v['instance']->getCSS();
		}
	}
	
	function processHeaders()
	{
		foreach( $this->modules as $k => $v )
			$v['instance']->processHeaderBlock();
	}
	
	function processSetCookies()
	{
		foreach( $this->modules as $k => $v )
			$v['instance']->processSetCookiesBlock();
	}
	
	function printHEAD()
	{
		$t = "";
		foreach( $this->modules as $k => $v )
			$t .= $v['instance']->processHeadBlock();
			
		return $t;
	}
	
	function printHTMLHeader()
	{
		$t = "";
		foreach( $this->modules as $k => $v )
			$t .= $v['instance']->processHTMLHeaderBlock();
			
		return $t;
	}
	
	function printContent()
	{
		global $query;
		
		$t = '';
		foreach( $this->modules as $k => $v ) {
			if( $v['instance']->isSelected() ) {
				$t = $v['instance']->getContent();
				break;
			}
		}
		
		return $t;
	}
	
	function getSelectedModuleId()
	{
		global $query;
		
		$t = '';
		foreach( $this->modules as $k => $v ) {
			if( $v['instance']->isSelected() ) {
				return $v['instance']->dbinfo['id'];
			}
		}
		
		return 0;
	}
	
	function getTitleChangeBlock()
	{
		global $query;
		
		$t = '';
		foreach( $this->modules as $k => $v ) {
			if( $v['instance']->isSelected() ) {
				$t = $v['instance']->getTitleChangeBlock();
				break;
			}
		}
		
		return $t;
	}
	
	function printHTMLFooter()
	{
		$t = "";
		foreach( $this->modules as $k => $v )
			$t .= $v['instance']->processHTMLFooterBlock();
			
		return $t;
	}
	
	function printPluginBlocks()
	{
		$t = "";
		foreach( $this->modules as $k => $v )
			$t .= $v['instance']->getPluginBlock();
			
		return $t;
	}
	
	function getFooterLinks( $add_to_right = "" )
	{
		global $mysql, $lang;
		
		$final = 0;
		foreach( $this->modules as $k => $v )
			if( $v['instance']->getFooterLink() )
				$final = $k;
		
		$t = "";
		foreach( $this->modules as $k => $v )
			$t .= $v['instance']->getFooterLink( $final != $k ? $add_to_right : "" );
			
		return "<a href='".$mysql->settings['local_folder']."'>".$lang->getPh( 3 )."</a>".$add_to_right.$t;
	}
	
	function getSopli()
	{
		$t = "";
		foreach( $this->modules as $k => $v )
			$t .= $v['instance']->getSopli();
			
		return $t;
	}
	
	function printBodyOnload()
	{
		$t = "";
		foreach( $this->modules as $k => $v )
			$t .= $v['instance']->getJSToBody();
			
		return $t;
	}
	
	function getAuthLinks( $add_to_right = "" )
	{
		global $mysql, $lang, $main;
		
		$final = 0;
		foreach( $this->modules as $k => $v )
			if( $v['instance']->getAuthLink() )
				$final = $k;
		
		$t = "";
		foreach( $this->modules as $k => $v )
			$t .= $v['instance']->getAuthLink( $final != $k ? $add_to_right : "" );
			
		return $t;
	}
	
	function getMainmenuItems()
	{
		global $lang;
		
		$t = "";
		
		$c = 0;
		foreach( $this->modules as $k => $v ) {
			if( $v['topmenu'] )
				$t .= $v['instance']->getMainmenuLink( ++$c );
		}
		
		return $t;
	}
	
	function getServicemenuItems()
	{
		global $lang;
		
		$t = "";
		
		$c = 0;
		foreach( $this->modules as $k => $v ) {
			if( $v['servicemenu'] )
				$t .= $v['instance']->getServicemenuLink( ++$c );
		}
		
		return $t;
	}
	
	function getSubmenuItems()
	{
		global $lang;
		
		$t = "";
		
		foreach( $this->modules as $k => $v )
			$t .= $v['instance']->getSubmenuLink();
		
		return $t;
	}
	
	function getWidgetItems()
	{
		global $lang;
		
		$t = "";
		
		foreach( $this->modules as $k => $v )
			$t .= $v['instance']->getWidget();
		
		return $t;
	}
	
	function getMobileMenuLinks()
	{
		global $lang;
		
		foreach( $this->modules as $k => $v ) {
			if( $v['mmenu'] )
				$t .= $v['instance']->getMobileMenuLink();
		}
		
		return $t;
	}
	
	function getItemsForMap()
	{
		$t = "";
		
		foreach( $this->modules as $k => $v )
			if( $v['instance']->isInMainMenu() )
				$t .= $v['instance']->getMapLink();
		
		return $t.$this->gmi( "contacts" )->getMapLink();
	}
	
	//
	// Далее функции для администраторской панели
	//
	
	function getModulesSelectList( $empty_string = "Нет", $selected = 0 )
	{
		global $mysql, $lang;

		$t = "<option value=0".( !$selected ? " selected" : "" ).">".$empty_string."</option>";
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."modules` WHERE 1 ORDER BY `order` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			if( !file_exists( ROOT_PATH."".$this->pathTomodules."".$r['local']."/index.php" ) ) {
				continue;
			}
			$t .= "<option value=".$r['id'].( $selected == $r['id'] ? " selected" : "" ).">".$lang->getPh( $r['name'] )." (".$r['local'].")</option>";
		}
		return $t;
	}
	
	function getAdminListScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang;
		
		$open = "";
		if( $query->gp( "edit" ) && $query->gp( "process" ) ) {
			
			$id = $query->gp( "edit" );
			$local = $query->gp( "m_local" );
			$comment = $query->gp( "m_comment" );
			
			$f_menu = $query->gp( "f_menu" );
			$f_menu = $f_menu == 'on' ? 1 : 0;
			$s_menu = $query->gp( "s_menu" );
			$s_menu = $s_menu == 'on' ? 1 : 0;
			$m_menu = $query->gp( "m_menu" );
			$m_menu = $m_menu == 'on' ? 1 : 0;
			$con = $query->gp( "con" );
			$con = $con == 'on' ? 1 : 0;
			
			$r = $mysql->mq( "SELECT `local` FROM `".$mysql->t_prefix."modules` WHERE `id`=".$id );
			if( $r && $r['local'] != $local ) {
				if( @rename( ROOT_PATH."".$this->pathTomodules."".$r['local']."/", ROOT_PATH."".$this->pathTomodules."".$local."/" ) ) {
					$cont = @str_replace( "class module".$r['local']." extends RootModule", "class module".$local." extends RootModule", @file_get_contents( ROOT_PATH."".$this->pathTomodules."".$local."/index.php" ) );
					if( @file_put_contents( ROOT_PATH."".$this->pathTomodules."".$local."/index.php", $cont ) )
						$mysql->mu( "UPDATE `".$mysql->t_prefix."modules` SET `local`='".$local."',  `comment`='".$comment."', `topmenu`=".$f_menu.", `servicemenu`=".$s_menu.", `mmenu`=".$m_menu.", `content`=".$con." WHERE `id`=".$id );
					else
						@rename( ROOT_PATH."".$this->pathTomodules."".$local."/", ROOT_PATH."".$this->pathTomodules."".$r['local']."/" );
				}
			} else if( $r ) {
				$mysql->mu( "UPDATE `".$mysql->t_prefix."modules` SET `comment`='".$comment."', `topmenu`=".$f_menu.", `servicemenu`=".$s_menu.", `mmenu`=".$m_menu.", `content`=".$con." WHERE `id`=".$id );
			}
			
		} else if( $query->gp( "createmodule" ) && $query->gp( "process" ) ) {
			
			$name = $query->gp( "m_name" );
			$local = strtolower( $query->gp( "m_local" ) );
			$comment = $query->gp( "m_comment" );
			
			$f_menu = $query->gp( "f_menu" );
			$f_menu = $f_menu == 'on' ? 1 : 0;
			$s_menu = $query->gp( "s_menu" );
			$s_menu = $s_menu == 'on' ? 1 : 0;
			$m_menu = $query->gp( "m_menu" );
			$m_menu = $m_menu == 'on' ? 1 : 0;
			$con = $query->gp( "con" );
			$con = $con == 'on' ? 1 : 0;
			
			if( @mkdir( ROOT_PATH.$this->pathTomodules.$local ) || @is_dir( ROOT_PATH.$this->pathTomodules.$local ) ) {
				if( !@is_dir( ROOT_PATH.$this->pathTomodules.$local ) )
					@chmod( ROOT_PATH.$this->pathTomodules.$local, 0777 );
			
				$r = $mysql->mq( "SELECT `order` FROM `".$mysql->t_prefix."modules` WHERE 1 ORDER BY `order` DESC" );
				$order = $r ? $r['order'] + 1 : 1;
			
				$a = $mysql->mqm( "SELECT `id` FROM `".$mysql->t_prefix."modules` WHERE 1 ORDER BY `id` ASC" );
				$moduleid = 1;
				while( $r = @mysql_fetch_assoc( $a ) ) {
					if( $r['id'] == $moduleid ) {
						$moduleid++;
					} else 
						break;
				}
			
				$phrase = $lang->addNewPhrase( $name, 1, $moduleid );
				
				$mysql->mu( "INSERT INTO `".$mysql->t_prefix."modules` VALUES(".$moduleid.",".$phrase.",'".$local."',1,'".$comment."',".$order.",".$f_menu.",".$s_menu.",".$m_menu.",".$con.");" );
			
				if( @is_dir( ROOT_PATH.$this->pathTomodules.$local ) ) {
					$cont = @str_replace( "%newname%", $local, @file_get_contents( ROOT_PATH."".$this->pathTomodules."newmodule.tpl" ) );
					@file_put_contents( ROOT_PATH.$this->pathTomodules.$local."/index.php", $cont );
				}
			}
			
		} else if( $query->gp( "turnmodule" ) ) {
			
			$id = $query->gp( "turnmodule" );
			$ep = $mysql->mq( "SELECT `include` FROM `".$mysql->t_prefix."modules` WHERE `id`=".$id );			
			if( $ep )
				$mysql->mu( "UPDATE `".$mysql->t_prefix."modules` SET `include`=".( $ep['include'] ? "0" : "1" )." WHERE `id`=".$id );
			
		} else if( $query->gp( "moveup" ) ) {
			
			$id = $query->gp( "moveup" );
			$r = $mysql->mq( "SELECT `order` FROM `".$mysql->t_prefix."modules` WHERE `id`=".$id );
			if( $r ) {
				$rr = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."modules` WHERE `order`=".( $r['order'] - 1 ) );
				$mysql->mu( "UPDATE `".$mysql->t_prefix."modules` SET `order`=".( $r['order'] - 1 )." WHERE `id`=".$id );
				$mysql->mu( "UPDATE `".$mysql->t_prefix."modules` SET `order`=".( $r['order'] )." WHERE `id`=".$rr['id'] );
			}
			
		} else if( $query->gp( "movedown" ) ) {
			
			$id = $query->gp( "movedown" );
			$r = $mysql->mq( "SELECT `order` FROM `".$mysql->t_prefix."modules` WHERE `id`=".$id );
			if( $r ) {
				$rr = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."modules` WHERE `order`=".( $r['order'] + 1 ) );
				$mysql->mu( "UPDATE `".$mysql->t_prefix."modules` SET `order`=".( $r['order'] + 1 )." WHERE `id`=".$id );
				$mysql->mu( "UPDATE `".$mysql->t_prefix."modules` SET `order`=".( $r['order'] )." WHERE `id`=".$rr['id'] );
			}
			
		} else if( $query->gp( "delete" ) ) {
			
			$id = $query->gp( "delete" );			
			$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."modules` WHERE `id`=".$id );
			
			if( $r ) {
				$a = $mysql->mqm( "SELECT `id`,`order` FROM `".$mysql->t_prefix."modules` WHERE `order`>".$r['order']." ORDER BY `order` ASC" );
				while( $rr = @mysql_fetch_assoc( $a ) )
					$mysql->mu( "UPDATE `".$mysql->t_prefix."modules` SET `order`=".( $rr['order'] - 1 )." WHERE `id`=".$rr['id'] );
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."modules` WHERE `id`=".$id );
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."modules_settings` WHERE `module`=".$id );
				//$mysql->mu( "DELETE FROM `".$mysql->t_prefix."langvars` WHERE `module`=".$id );
				$utils->rmdir_notempty( ROOT_PATH."".$this->pathTomodules."".$r['local']."/" );
			}
			
		}
		
		$selectedElement = 1;
		$t = "
			<h1>Список подключенных модулей (плагинов, виджетов)</h1>
			
			<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."', 4, 'newmodule' );\">Создать новый модуль</a><br><br>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%;'>
				<tr class='list_table_header'>
					<td width=30>
						ID
					</td>
					<td width=100%>
						Название и описание
					</td>
					<td>
						Код
					</td>
					<td>
						В верхнем меню?
					</td>
					<td>
						В меню Обслуживание?
					</td>
					<td>
						В мобильном меню?
					</td>
					<td>
						Контент?
					</td>
					<td width=110>
						Включен?
					</td>
					<td width=110>
						Порядок
					</td>
					<td width=110>
						Опции
					</td>
				</tr>
		";
		
		$counter = 0;
		$r = $mysql->mq( "SELECT `order` FROM `".$mysql->t_prefix."modules` WHERE 1 ORDER BY `order` DESC" );
		$lastorder = $r ? $r['order'] : 1;
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."modules` WHERE 1 ORDER BY `order` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$t .= "
				<tr class='list_table_element'".( !$r['include'] ? " style='background-color: #fcd9d9;'" : "" ).">
					<td valign=top>
						".$r['id']."
					</td>
					<td style='text-align: left;'>
						<b><label id='module_name_".$r['id']."'>".$lang->getPh( $r['name'] )."</label></b>
						<p class='comment'>
							".str_replace( "\n", "<br>", $r['comment'] )."
						</p>
					</td>
					<td>
						".$r['local']."
					</td>
					<td>
						".( $r['topmenu'] ? "Да" : "Нет" )."
					</td>
					<td>
						".( $r['servicemenu'] ? "Да" : "Нет" )."
					</td>
					<td>
						".( $r['mmenu'] ? "Да" : "Нет" )."
					</td>
					<td>
						".( $r['content'] ? "Да" : "Нет" )."
					</td>
					<td>
						".( $r['include'] ? "Да" : "Нет" )."
					</td>
					<td>
						".$r['order']."
						".( $r['order'] > 1 ? "<a href=\"".LOCAL_FOLDER."admin/".$path."/moveup".$r['id']."\"><img src=\"".LOCAL_FOLDER."images/up.gif\" style='".( $r['order'] == $lastorder ? "margin-top: 2px;" : "" )."position: absolute;margin-left: 3px;' title='Поднять выше' /></a>" : "" )."
						".( $r['order'] != $lastorder ? "<a href=\"".LOCAL_FOLDER."admin/".$path."/movedown".$r['id']."\"><img src=\"".LOCAL_FOLDER."images/down.gif\" style='".( $r['order'] > 1 ? "margin-top: 7px;" : "margin-top: 2px;" )."position: absolute;margin-left: 3px;' title='Опустить ниже' /></a>" : "" )."
					</td>
					<td nowrap>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/turnmodule".$r['id']."\">".( $r['include'] ? "Выключить" : "Включить" )."</a>
							<label class='line_between_links'>|</label>
						<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/moduleid".$r['id']."', 1, 'module_settings' );\">Настройки</a>
							<label class='line_between_links'>|</label>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/delete".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a>
					</td>
				</tr>
			";
			$counter++;
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=10>
						Всего модулей: ".$counter."
					</td>
				</tr>
		</table>
		
		<script>
			$( function() {
				$( '#module_settings' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 400, maxWidth: 700, maxHeight: 600, width: 500, height: 400, autoOpen: false } );
				$( '#module_settings' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
				$( '#newmodule' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 400, maxWidth: 700, maxHeight: 600, width: 600, height: 400, autoOpen: false } );
				$( '#newmodule' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
			} );
		</script>
		
		<div id='module_settings' title='Настройки модуля'></div>
		<div id='newmodule' title='Новый модуль'></div>
		".$open;
		
		return $t;
	}
	
	function getAdminSettingsScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang;
		
		$selectedElement = 1;
		$moduleid = $query->gp( "moduleid" );
		$open = "";
		
		if( $query->gp( "newparam" ) && $query->gp( "process" ) ) {
			
			$moduleid = $query->gp( "moduleid" );
			$sn = $query->gp( "m_settingname" );
			$sn = @str_replace( "'", "", @str_replace( '"', "", @str_replace( "&amp;", "", @str_replace( "&quot;", "", @str_replace( " ", "", $sn ) ) ) ) );
			$sv = $_POST['m_value'];
			$sc = $query->gp_letqbe( "m_comment" );
			$sc = $sc ? @str_replace( "'", "\\'", $sc ) : "";
			
			$a = $mysql->mqm( "SELECT `id` FROM `".$mysql->t_prefix."modules_settings` WHERE 1 ORDER BY `id` ASC" );
			$newid = 1;
			while( $r = @mysql_fetch_assoc( $a ) ) {
				if( $r['id'] == $newid ) {
					$newid++;
				} else 
					break;
			}
			
			$mysql->mu( "INSERT INTO `".$mysql->t_prefix."modules_settings` VALUES(".$newid.",".$moduleid.",'".$sn."','".$sv."','".$sc."');" );
			
		} else if( $query->gp( "param" ) && $query->gp( "process" ) && $query->gp( "edit" ) ) {
			
			$param = $query->gp( "param" );
			
			$sn = $query->gp( "m_settingname" );
			$sn = @str_replace( "'", "", @str_replace( '"', "", @str_replace( "&amp;", "", @str_replace( "&quot;", "", @str_replace( " ", "", $sn ) ) ) ) );
			$sv = $_POST['m_value'];
			$sc = $query->gp_letqbe( "m_comment" );
			$sc = $sc ? @str_replace( "'", "\\'", $sc ) : "";
			
			$mysql->mu( "UPDATE `".$mysql->t_prefix."modules_settings` SET `setting_name`='".$sn."', `value`='".$sv."', `comment`='".$sc."' WHERE `id`=".$param );
			
		} else if( $query->gp( "delete" ) ) {
			
			$param = $query->gp( "delete" );
			$mysql->mu( "DELETE FROM `".$mysql->t_prefix."modules_settings` WHERE `id`=".$param );
			
		}
		
		$t = "
			<h1>Редактируемые параметры модулей</h1>
			
			<form method=POST action=\"".LOCAL_FOLDER."admin/".$path."/\" id='settings_form' style='padding: 0px; margin: 0px;'>
			<p>
				Выберите модуль, параметры которого вы хотите просмотреть:<br>
				<select name=\"moduleid\" id=\"moduleid\" class='select_input' onchange=\"ge( 'settings_form' ).submit();\">
					".$this->getModulesSelectList( "Выберите модуль", $moduleid )."
				</select>
			</p>
			</form>
		";
		
		if( !$moduleid )
			return $t;
		
		$t .= "
			<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/moduleid".$moduleid."', 2, 'newparam' );\">Добавить новый параметр</a><br><br>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 800px;'>
				<tr class='list_table_header'>
					<td width=30>
						ID
					</td>
					<td width=30%>
						Наименование и описание
					</td>
					<td width=70%>
						Значение
					</td>
					<td width=110>
						Опции
					</td>
				</tr>
		";
		
		$counter = 0;
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."modules_settings` WHERE `module`=".$moduleid." ORDER BY `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$t .= "
				<tr class='list_table_element'>
					<td valign=top>
						".$r['id']."
					</td>
					<td style='text-align: left;' valign=top>
						Параметр <b>".$r['setting_name']."</b>
					</td>
					<td valign=top style='text-align: left;'>
						".$utils->getCorrectWidthOfString( $r['value'], 200 ).( strlen( $r['value'] ) > 200 ? "<p><label class='big black' style='border-top: 1px dotted #333;'>Общая длина текста - ".strlen( $r['value'] )." символов</label></p>" : "" )."
					</td>
					<td nowrap>
						<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/moduleid".$moduleid."/param".$r['id']."', 3, 'paramedit' );\">Изменить</a>
							<label class='line_between_links'>|</label>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/moduleid".$moduleid."/delete".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a>
					</td>
				</tr>
			";
			if( $r['comment'] ) {
				$t .= "
				<tr class='list_table_element'>
					<td valign=top style='border: none;'>
						&nbsp;
					</td>
					<td style='text-align: left; border: none;' valign=top colspan=2>
						<p class='comment'>
							".$r['comment']."
						</p>
					</td>
					<td nowrap style='border: none;'>
						&nbsp;
					</td>
				</tr>
				";
			}
			$counter++;
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=4>
						Всего параметров: ".$counter."
					</td>
				</tr>
		</table>
		
		<script>
			$( function() {
				$( '#newparam' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 550, minHeight: 400, maxWidth: 700, maxHeight: 800, width: 600, height: 500, autoOpen: false } );
				$( '#newparam' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
				$( '#paramedit' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 550, minHeight: 400, maxWidth: 700, maxHeight: 800, width: 600, height: 500, autoOpen: false } );
				$( '#paramedit' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
			} );
		</script>
		
		<div id='newparam' title='Новый параметр'></div>
		<div id='paramedit' title='Редактирование параметра'></div>
		".$open;
		
		return $t;
	}
	
	function getExternal( $wt, $link )
	{
		global $query, $mysql;
		
		$module_id = $query->gp( "moduleid" );
		
		if( $wt == 1 ) { // Выдать установки модуля
			
			return $this->getExternalModuleSettings( $module_id, $link );
			
		} else if( $wt == 2 ) { // Новый параметр модуля
			
			return $this->getExternalNewParam( $module_id, $link );
			
		} else if( $wt == 3 ) { // Редактирование параметра модуля
			
			return $this->getExternalEditParam( $module_id, $link );
			
		} else if( $wt == 4 ) { // Новый модуль
			
			return $this->getExternalNewModule( $link );
			
		}
		
		return "Unknown module query";
	}
	
	function getExternalModuleSettings( $module_id, $link )
	{
		global $query, $mysql, $lang, $utils;
		
		$m = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."modules` WHERE `id`=".$module_id );
		if( !$m ) {
			return "Unknown module id";
		}
			
		$inner = "
				<p>
					Название модуля:<br>
					<a href='#' title='Редактирование названия в отдельном окне' class='forallunknowns' onclick=\"
						getWindowContent( '".LOCAL_FOLDER."admin/langs/phraseid".$m['name']."/langid1/ajax/whattochange!tochange*module_name_".$module_id."/toclosename!phrase_edit', 3, 'phrase_edit' );
					\">Редактирование в отдельном окне</a>
				</p>
				<p>
					Код модуля: <label class='red'>*</label> (только латинские буквы и знаки _ или -)<br>
					<input type=text name=\"m_local\" id=\"m_local\" value=\"".$m['local']."\" class='text_input' />
				</p>
				<p>
					В верхнем меню?: <input type=checkbox name=\"f_menu\" ".( $m['topmenu'] ? " checked" : "" )." />
				</p>
				<p>
					В блоке Обслуживание?: <input type=checkbox name=\"s_menu\" ".( $m['servicemenu'] ? " checked" : "" )." />
				</p>
				<p>
					В мобильном меню?: <input type=checkbox name=\"m_menu\" ".( $m['mmenu'] ? " checked" : "" )." />
				</p>
				<p>
					Есть ли контент?: <input type=checkbox name=\"con\" ".( $m['content'] ? " checked" : "" )."/>
				</p>
				<p>
					Описание модуля:<br>
					<textarea name=\"m_comment\" id=\"m_comment\" class='textarea_input' style='width: 100%; height: 60px;'>".$m['comment']."</textarea>
				</p>
			";
			
		return "
				<h1 align=left>Настройки модуля <b>\"<label id=\"tochange\">".$lang->getPh( $m['name'] )."</label>\"</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$link."/edit".$module_id."\" method=POST onsubmit=\"
					if( $( '#m_local' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите код модуля' ); 
						return false; 
					}
					$( '#process' ).attr( 'value', 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=submit value=\"Сохранить\" class='button_input' title='Сохранить измененные данные и закрыть окно' />
					</div>
				</form>
				
				<script>
					$( function() {
						$( '#phrase_edit' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 550, minHeight: 400, maxWidth: 700, maxHeight: 800, width: 600, height: 500, autoOpen: false } );
						$( '#phrase_edit' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
					} );
				</script>
		
				<div id='phrase_edit' title='Редактирование названия модуля'></div>
		";
	}
	
	function getExternalNewModule( $link )
	{
		global $query, $mysql, $lang;
		
		$inner = "
				<p>
					Название модуля: <label class='red'>*</label> (добавление перевода возможно в режиме редактирования модуля)<br>
					<input type=text name=\"m_name\" id=\"m_name\" value=\"\" class='text_input' />
				</p>
				<p>
					Код модуля: <label class='red'>*</label> (только латинские буквы и знаки _ или -)<br>
					<input type=text name=\"m_local\" id=\"m_local\" value=\"\" class='text_input' />
				</p>
				<p>
					В верхнем меню?: <input type=checkbox name=\"f_menu\" />
				</p>
				<p>
					В блоке Обслуживание?: <input type=checkbox name=\"s_menu\" />
				</p>
				<p>
					В мобильном меню?: <input type=checkbox name=\"m_menu\" />
				</p>
				<p>
					Есть ли контент?: <input type=checkbox name=\"con\" />
				</p>
				<p>
					Описание модуля:<br>
					<textarea name=\"m_comment\" id=\"m_comment\" class='textarea_input' style='width: 100%; height: 60px;'></textarea>
				</p>
			";
			
		return "
				<h1 align=left>Создание нового модуля</h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$link."/createmodule\" method=POST onsubmit=\"
					if( $( '#m_name' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите название модуля' ); 
						return false; 
					}
					if( $( '#m_local' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите код модуля' ); 
						return false; 
					}
					$( '#process' ).attr( 'value', 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=submit value=\"Создать\" class='button_input' title='Создать модуль и закрыть окно' />
					</div>
				</form>
		";
	}
	
	function getExternalNewParam( $module_id, $link )
	{
		global $query, $mysql, $lang;
		
		$m = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."modules` WHERE `id`=".$module_id );
		if( !$m ) {
			return "Unknown module id";
		}
			
		$inner = "
				<p>
					Наименование параметра (используйте латинские буквы и ТОЛЬКО символы '-' или '_'): <label class='red'>*</label><br>
					<input type=text name=\"m_settingname\" id=\"m_settingname\" value=\"\" class='text_input' />
				</p>
				<p>
					Описание параметра:<br>
					<textarea name=\"m_comment\" id=\"m_comment\" class='textarea_input' style='width: 100%; height: 60px;'></textarea>
				</p>
				<p>
					Значение параметра: <label class='red'>*</label><br>
					<textarea name=\"m_value\" id=\"m_value\" class='textarea_input' style='width: 100%; height: 200px;'></textarea>
				</p>
			";
			
		return "
				<h1 align=left>Новый параметр для модуля <b>\"<label id=\"tochange\">".$lang->getPh( $m['name'] )."</label>\"</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$link."/moduleid".$module_id."/newparam\" method=POST onsubmit=\"
					if( $( '#m_settingname' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите наименование параметра' ); 
						return false; 
					}
					if( $( '#m_value' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите значение параметра' ); 
						return false; 
					}
					$( '#process' ).attr( 'value', 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=submit value=\"Добавить\" class='button_input' title='Добавить новый параметр и закрыть окно' />
					</div>
				</form>
		";
	}
	
	function getExternalEditParam( $module_id, $link )
	{
		global $query, $mysql, $lang;
		
		$param = $query->gp( "param" );
		
		$m = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."modules` WHERE `id`=".$module_id );
		if( !$m ) {
			return "Unknown module id";
		}
		
		$p = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."modules_settings` WHERE `id`=".$param );
		if( !$p ) {
			return "Unknown parametr id";
		}
			
		$inner = "
				<p>
					Наименование параметра (используйте латинские буквы и ТОЛЬКО символы '-' или '_'): <label class='red'>*</label><br>
					<input type=text name=\"m_settingname\" id=\"m_settingname\" value=\"".$p['setting_name']."\" class='text_input' />
				</p>
				<p>
					Описание параметра:<br>
					<textarea name=\"m_comment\" id=\"m_comment\" class='textarea_input' style='width: 100%; height: 60px;'>".$p['comment']."</textarea>
				</p>
				<p>
					Значение параметра:<br>
					<textarea name=\"m_value\" id=\"m_value\" class='textarea_input' style='width: 100%; height: 200px;'>".$p['value']."</textarea>
				</p>
			";
			
		return "
				<h1 align=left>Редактирование параметра модуля <b>\"<label id=\"tochange\">".$lang->getPh( $m['name'] )."</label>\"</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$link."/moduleid".$module_id."/param".$param."/edit\" method=POST onsubmit=\"
					if( $( '#m_settingname' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите наименование параметра' ); 
						return false; 
					}
					$( '#process' ).attr( 'value', 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=submit value=\"Сохранить\" class='button_input' title='Сохранить изменения и закрыть окно' />
					</div>
				</form>
		";
	}
}

?>