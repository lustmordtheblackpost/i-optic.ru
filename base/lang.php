<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

class Lang
{
	var $phrases = array();
	var $global_phrases = array();
	var $currentLanguage, $currentLanguageName, $currentLanguageCL;
	var $global = false;
	var $gl_dbase_string = "`shop`.";
	
	function init()
	{
		global $mysql, $query;
		
		if( $query->gp( "slang" ) && is_numeric( $query->gp( "slang" ) ) ) {
			$sl = $query->gp( "slang" );
			$this->currentLanguage = $sl;
			@setcookie( "clang", $sl, time() + ( 3600 * 24 * 365 ), "/" );
			$uri = str_replace( "slang".$sl."/", "", $_SERVER['REQUEST_URI'] );
			header( "location: ".$uri ); 
		} else
			$this->currentLanguage = !$query->gp( "admin" ) && isset( $_COOKIE['clang'] ) && is_numeric( $_COOKIE['clang'] ) ? $_COOKIE['clang'] : $mysql->settings['default_language'];
		
		$this->currentLanguageName = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."langs` WHERE `id`=".$this->currentLanguage );
		if( !$this->currentLanguageName ) {
			$this->currentLanguage = $mysql->settings['default_language'];
			$this->currentLanguageName = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."langs` WHERE `id`=".$this->currentLanguage );
		}
		$this->currentLanguageCL = $this->currentLanguageName['content-language'];
		$this->currentLanguageName = $this->currentLanguageName['name'];
		
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."langvars` WHERE `lang_id`=".$this->currentLanguage." ORDER BY `phrase_id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) )
			$this->phrases[$r['phrase_id']] = $r['value'];
			
		$this->global = $query->gp( "global" ) ? true : false;
			
		$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."langvars` WHERE `lang_id`=".$this->currentLanguage." ORDER BY `phrase_id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) )
			$this->global_phrases[$r['phrase_id']] = $r['value'];
	}
	
	function getLanguagesArray( $order = "`name` ASC" )
	{
		global $mysql;
		
		$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs` WHERE 1 ORDER BY ".$order );
		$ar = array();
		while( $r = @mysql_fetch_assoc( $a ) )
			$ar[$r['id']] = $r;
		
		return $ar;
	}
	
	function getPh( $number, $global = false )
	{
		return $global ? ( isset( $this->global_phrases[$number] ) && is_numeric( $number )  && strpos( $number, '.' ) === false ? $this->global_phrases[$number] : ( is_numeric( $number ) && strpos( $number, '.' ) === false ? $this->phrases[5] : $number ) ) : ( isset( $this->phrases[$number] ) && is_numeric( $number ) && strpos( $number, '.' ) === false ? $this->phrases[$number] : ( is_numeric( $number ) && strpos( $number, '.' ) === false ? $this->phrases[5] : $number ) );
	}
	
	function gp( $number, $global = false )
	{
		return $this->getPh( $number, $global );
	}
	
	function updatePhrase( $id, $newval, $lang_id, $module = 0 )
	{
		global $mysql;
		
		$r = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE `phrase_id`=".$id." AND `lang_id`=".$lang_id );
		if( $r )
			$mysql->mu( "UPDATE ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` SET `value`='".$newval."' WHERE `phrase_id`=".$id." AND `lang_id`=".$lang_id );
		else 
			$mysql->mu( "INSERT INTO ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` VALUES(".$id.",".$lang_id.",".$module.",'".$newval."');" );
			
		if( !$this->global ) 
			$this->phrases[$id] = $newval;
		else 
			$this->global_phrases[$id] = $newval;
	}
	
	function checkSimpleReq()
	{
		global $query, $mysql, $utils;
		
		if( $query->gp( "trans_lang_code" ) ) {
			$code = $query->gp( "trans_lang_code" );
			$r = $mysql->mq( "SELECT `name` FROM `".$mysql->t_prefix."google_langs` WHERE `content-language`='".$code."'" );
			if( !$r ) {
				echo "Unknown language code";
				exit;
			}
			
			echo $this->getGoogleTranslation( "ru", $code, $r['name'] );
			exit;
		} else if( $query->gp( "translate" ) ) {
			$phrase = urldecode( $query->gp_letqbe( "phrasetrans", isset( $_GET['phrasetrans'] ) ? $_GET['phrasetrans'] : "" ) );
			$in = $query->gp( "intt" );
			$out = $query->gp( "outtt" );			
			if( !$phrase || !$in || !$out ) {
				echo "Unknown language code or no phrase";
				exit;
			}
			
			echo $this->getGoogleTranslation( $in, $out, $phrase );
			exit;
		}
	}
	
	function oldgetGoogleTranslation( $from, $to, $text )
	{
		global $utils;
		
		@include_once( ROOT_PATH."base/curl.php" );
		$c = new Curl();
		$params = array();
		$params[$c->paramURL] = "http://ajax.googleapis.com/ajax/services/language/translate";
		$params[$c->paramPOST] = array( "v" => "1.0", "q" => $text, "langpair" => $from."|".$to );
		$params[$c->paramREFERER] = "http://google.com";
		$params[$c->paramHEADERS] = "Content-type: application/x-www-form-urlencoded";
			
		$r = $c->processConnect( $params );
		$result = @json_decode( $r[$c->paramRESULT], 1 );
		
		return $result['responseStatus'] == 200 ? $result['responseData']['translatedText'] : "";
	}
	
	function getGoogleTranslation( $from, $to, $text )
	{
		global $query;
		
		@include_once( ROOT_PATH."base/curl.php" );
		$c = new Curl();
		$params = array();
		$params[$c->paramURL] = "http://api.microsofttranslator.com/v2/Http.svc/Translate?appId=".$query->microsoft_bing_code."&text=".urlencode( $text )."&from=".$from."&to=".$to."&contentType=text/html";
		$params[$c->paramREFERER] = "http://glopas.net";
		
		$r = $c->processConnect( $params );
		
		return str_replace( '<string xmlns="http://schemas.microsoft.com/2003/10/Serialization/">', '', str_replace( '</string>', '', $r[$c->paramRESULT] ) );
	}
	
	function getLanguagesForSelecting()
	{
		global $mysql;
		
		$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs` WHERE 1 ORDER BY `name` ASC" );
		$t = "";
		while( $r = @mysql_fetch_assoc( $a ) )
			$t .= "<option value=\"".$r['id']."\"".( $this->currentLanguage == $r['id'] ? " selected" : "" ).">".$r['name']."</option>";
		
		return $t;
	}
	
	//
	// Далее функции для администраторской панели
	//
	
	function addNewPhrase( $text, $lang_id, $moduleid = 0, $forcenew = false )
	{
		global $mysql, $utils;
		
		$newitem = $text ? @str_replace( "'", "\\'", $text ) : "";
		
		$r = $mysql->mq( "SELECT `phrase_id` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE `value`='".$newitem."' AND `lang_id`=".$lang_id );
		if( $r && !$forcenew )
			return $r['phrase_id'];
		
		$a = $mysql->mqm( "SELECT `phrase_id` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE 1 GROUP BY `phrase_id` ORDER BY `phrase_id` ASC" );
		$phrase = 1;
		while( $r = @mysql_fetch_assoc( $a ) ) {
			if( $r['phrase_id'] == $phrase ) {
				$phrase++;
			} else 
				break;
		}
		
		$mysql->mu( "INSERT INTO ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` VALUES(".$phrase.",".$lang_id.",".$moduleid.",'".$newitem."');" );
		if( !$this->global ) 
			$this->phrases[$phrase] = $newitem;
		else 
			$this->global_phrases[$phrase] = $newitem;
		
		return $phrase;
	}
	
	function deletePhrase( $id, $module = 0 )
	{
		global $mysql;
		
		$mysql->mu( "DELETE FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE `phrase_id`=".$id.( $module ? " AND `module`=".$module : '' ) );
	}
	
	function getAdminScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin;
		
		$open = "";
		if( $query->gp( "edit" ) && $query->gp( "process" ) ) {
			$id = $query->gp( "edit" );
			$name = $query->gp( "l_name" );
			$content = $query->gp( "l_content" );
			
			$mysql->mu( "UPDATE ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs` SET `name`='".$name."', `content-language`='".$content."' WHERE `id`=".$id );
		} else if( $query->gp( "process" ) && $query->gp( "createlang" ) ) {
			
			$name = $query->gp( "l_name" );
			$content = $query->gp( "l_content" );
			
			$a = $mysql->mqm( "SELECT `id` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs` WHERE 1 ORDER BY `id` ASC" );
			$langid = 1;
			while( $r = @mysql_fetch_assoc( $a ) ) {
				if( $r['id'] == $langid ) {
					$langid++;
				} else 
					break;
			}
			
			$mysql->mu( "INSERT INTO ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs` VALUES(".$langid.",'".$name."','".$content."');" );
			
		} else if( $query->gp( "process" ) && $query->gp( "phraseid" ) ) {
			
			$id = $query->gp( "langid" );
			$page = $query->gp( "page" );
			$onlyempty = $query->gp( "onlyempty" );
			$onlyempty = $onlyempty == 'on' ? true : false;
			$phrase = $query->gp( 'phraseid' );
			$choosen_module = $query->gp( "choosen_module" );
			$choosen_module = $choosen_module ? $choosen_module : 0;
			
			$a = $mysql->mqm( "SELECT `id` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs` WHERE 1" );
			while( $r = @mysql_fetch_assoc( $a ) ) {
				$newitem = $_POST[$r['id']."_".$phrase."_phrase_value"];
				$newitem = $newitem ? str_replace( "'", "\\'", $newitem ) : "";
				$ep = $mysql->mq( "SELECT `phrase_id` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE `phrase_id`=".$phrase." AND `lang_id`=".$r['id'] );
				
				if( $ep && $newitem )
					$mysql->mu( "UPDATE ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` SET `value`='".$newitem."', `module`=".$choosen_module." WHERE `phrase_id`=".$phrase." AND `lang_id`=".$r['id'] );
				else if( $ep && !$newitem )
					$mysql->mu( "DELETE FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE `phrase_id`=".$phrase." AND `lang_id`=".$r['id'] );
				else if( !$ep && $newitem )
					$mysql->mu( "INSERT INTO ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` VALUES(".$phrase.",".$r['id'].",".$choosen_module.",'".$newitem."');" );
			}
			
			$open = "
				<script>
					getWindowContent( '".LOCAL_FOLDER."admin/".$path."/langid".$id.( $page ? "/page".$page : "" ).( $onlyempty ? "/onlyempty!on" : "" )."', 2, 'phrases' );
				</script>
			";
		} else if( $query->gp( "continue" ) ) {
			
			$id = $query->gp( "langid" );
			$page = $query->gp( "page" );
			$onlyempty = $query->gp( "onlyempty" );
			$onlyempty = $onlyempty == 'on' ? true : false;

			$open = "
				<script>
					getWindowContent( '".LOCAL_FOLDER."admin/".$path."/langid".$id.( $page ? "/page".$page : "" ).( $onlyempty ? "/onlyempty!on" : "" )."', 2, 'phrases' );
				</script>
			";
		} else if( $query->gp( "deletephrase" ) ) {
			
			$id = $query->gp( "langid" );
			$deleteall = $query->gp( "deleteall" );
			$page = $query->gp( "page" );
			$onlyempty = $query->gp( "onlyempty" );
			$onlyempty = $onlyempty == 'on' ? true : false;
			
			$mysql->mu( "DELETE FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE `phrase_id`=".$query->gp( 'deletephrase' ).( !$deleteall ? " AND `lang_id`=".$id : "" ) );
			
			$open = "
				<script>
					getWindowContent( '".LOCAL_FOLDER."admin/".$path."/langid".$id.( $page ? "/page".$page : "" ).( $onlyempty ? "/onlyempty!on" : "" )."', 2, 'phrases' );
				</script>
			";
		} else if( $query->gp( "setfromdefault" ) ) {
			
			$id = $query->gp( "setfromdefault" );
			
			$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE `lang_id`=".$mysql->settings['default_language'] );
			while( $r = @mysql_fetch_assoc( $a ) )
				if( !$mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE `phrase_id`=".$r['phrase_id']." AND `lang_id`=".$id ) )
					$mysql->mu( "INSERT INTO ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` VALUES(".$r['phrase_id'].",".$id.",".$r['module'].",'');" );
					
		} else if( $query->gp( "delete" ) ) {
			
			$id = $query->gp( "delete" );
			
			$mysql->mu( "DELETE FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE `lang_id`=".$id );
			$mysql->mu( "DELETE FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs` WHERE `id`=".$id );
			
		}
		
		if( $query->gp( "setdefault" ) && !$this->global ) {
			$mysql->settings['default_language'] = $query->gp( "setdefault" );
			$admin->settings['default_language']['value'] = $mysql->settings['default_language'];
			$mysql->mu( "UPDATE `".$mysql->t_prefix."settings` SET `value`=".$mysql->settings['default_language']." WHERE `id`='".$admin->settings['default_language']['id']."'" );
		}
		
		$selectedElement = 3;
		$t = "
			<h1>".( $this->global ? "Глобальные языки и фразы" : "Локальные языки и фразы" )."</h1>
			
			".( $admin->userLevel == 1 ? "<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."', 5, 'newlanguage' );\">Создать новый язык</a><br><br>" : "" )."
			
			<table cellspacing=0 cellpadding=0 class='list_table'>
				<tr class='list_table_header'>
					<td width=30>
						ID
					</td>
					<td>
						Название
					</td>
					<td>
						Код
					</td>
					<td width=110>
						Всего фраз
					</td>
					<td width=110>
						По умолчанию
					</td>
					<td width=110>
						Опции
					</td>
				</tr>
		";
		
		$counter = 0;
		$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs` WHERE 1 ORDER BY `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$t .= "
				<tr class='list_table_element'>
					<td>
						".$r['id']."
					</td>
					<td>
						".$r['name']."
					</td>
					<td>
						".$r['content-language']."
					</td>
					<td>
						".$mysql->getTableRecordsCount( "".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars`", "`lang_id`=".$r['id'] )."
					</td>
					<td>
						".( !$this->global ? ( $mysql->settings['default_language'] == $r['id'] ? "Да" : "<a href=\"".LOCAL_FOLDER."admin/".$path."/setdefault".$r['id']."\">Установить</a>" ) : "-" )."
					</td>
					<td nowrap style='text-align: left;'>
						<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/langid".$r['id']."', 2, 'phrases' );\">Фразы</a>
						".( $admin->userLevel == 1 ? "
							<label class='line_between_links'>|</label>
						<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/langid".$r['id']."', 1, 'lang_settings' );\">Настройки</a>
							<label class='line_between_links'>|</label>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/delete".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a>
						".( $r['id'] != $mysql->settings['default_language'] ? "
							<label class='line_between_links'>|</label>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/setfromdefault".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\" title='Установить в этот язык все фразы, которых в нем нет, но есть в языке по умолчанию'>Внести</a>" : "" )."
						" : "" )."
					</td>
				</tr>
			";
			$counter++;
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=6>
						Всего языков: ".$counter."
					</td>
				</tr>
		</table>
		
		<script>
			$( function() {
				$( '#lang_settings' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 300, maxWidth: 700, maxHeight: 600, width: 500, height: 300, autoOpen: false } );
				$( '#lang_settings' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
				$( '#phrases' ).dialog( { closeOnEscape: false, zIndex: 3001, stack: true, minWidth: 800, minHeight: 500, maxWidth: 1000, maxHeight: 700, width: 1000, height: 700, autoOpen: false } );
				$( '#phrases' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
				$( '#newlanguage' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 300, maxWidth: 700, maxHeight: 600, width: 500, height: 300, autoOpen: false } );
				$( '#newlanguage' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
			} );
		</script>
		
		<div id='lang_settings' title='Настройки'></div>
		<div id='phrases' title='Фразы'></div>
		<div id='newlanguage' title='Новый язык'></div>
		".$open;
		
		return $t;
	}
	
	function getExternal( $wt, $link )
	{
		global $query, $mysql, $admin;
		
		$lang_id = $query->gp( "langid" );
		
		if( $wt == 1 && $admin->userLevel == 1 ) { // Выдать установки языка
			
			return $this->getExternalLanguageSettings( $lang_id, $link );
			
		} else if( $wt == 2 ) { // Выдать список фраз языка
			
			return $this->getExternalLanguagePhrases( $lang_id, $link );
			
		} else if( $wt == 3 ) { // Редактировать фразу
			
			return $this->getExternalEditPhrase( $lang_id, $link );
			
		} else if( $wt == 4 ) { // Новая фраза
			
			return $this->getExternalNewPhrase( $lang_id, $link );
			
		} else if( $wt == 5 && $admin->userLevel == 1 ) { // Новый язык
			
			return $this->getExternalNewLanguage( $link );
			
		}
		
		return "Unknown language query";
	}
	
	function getExternalLanguageSettings( $lang_id, $link )
	{
		global $query, $mysql;
		
		$l = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs` WHERE `id`=".$lang_id );
		if( !$l ) {
			return "Unknown language id";
		}
			
		$inner = "
				<p>
					Название языка: <label class='red'>*</label><br>
					<input type=text name=\"l_name\" id=\"l_name\" value=\"".$l['name']."\" class='text_input' />&nbsp;&nbsp;
					<a href='#' title='Автоперевод названия выбранного языка на родной (выбранный) язык' class='forallunknowns' onclick=\"
						$( 'html' ).css( 'cursor', 'wait' ); 
						$( '#l_name' ).attr( 'disabled', true ); 
						traslateCode( ge( 'l_content' ).value, 'l_name', 'hid_trans' );
					\">Автоперевод</a><label id='hid_trans' style='display: none'></label>
				</p>
				<p>
					Тип языка для установки кодировки (и перевода):<br>
					<select name=\"l_content\" id=\"l_content\" class='select_input'>
		";
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."google_langs` WHERE 1 ORDER BY `name` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) )
			$inner .= "<option value=\"".$r['content-language']."\"".( $l['content-language'] == $r['content-language'] ? " selected" : "" ).">".$r['name']." (".$r['content-language'].")</option>";
			
		$inner .= "
					</select>
				</p>
			";
			
		return "
				<h1 align=left>Настройки языка <b>\"".$l['name']."\"</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$link."/edit".$lang_id."\" method=POST onsubmit=\"
					if( $( '#l_name' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите наименование языка, либо воспользуйтесь автопереводчиком для быстрого перевода на нужный язык' ); 
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
		";
	}
	
	function getExternalNewLanguage( $link )
	{
		global $query, $mysql;
		
		$inner = "
				<p>
					Название языка: <label class='red'>*</label> (для начала выберите тип языка, а затем можно автоматически перевести название)<br>
					<input type=text name=\"l_name\" id=\"l_name\" value=\"\" class='text_input' />&nbsp;&nbsp;
					<a href='#' title='Автоперевод названия выбранного языка на родной (выбранный) язык' class='forallunknowns' onclick=\"
						$( 'html' ).css( 'cursor', 'wait' ); 
						$( '#l_name' ).attr( 'disabled', true ); 
						traslateCode( ge( 'l_content' ).value, 'l_name', 'hid_trans' );
					\">Автоперевод</a><label id='hid_trans' style='display: none'></label>
				</p>
				<p>
					Тип языка для установки кодировки (и перевода):<br>
					<select name=\"l_content\" id=\"l_content\" class='select_input'>
		";
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."google_langs` WHERE 1 ORDER BY `name` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) )
			$inner .= "<option value=\"".$r['content-language']."\"".( $l['content-language'] == $r['content-language'] ? " selected" : "" ).">".$r['name']." (".$r['content-language'].")</option>";
			
		$inner .= "
					</select>
				</p>
			";
			
		return "
				<h1 align=left>Новый язык</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$link."/createlang\" method=POST onsubmit=\"
					if( $( '#l_name' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите наименование языка, либо воспользуйтесь автопереводчиком для быстрого перевода на нужный язык' ); 
						return false; 
					}
					$( '#process' ).attr( 'value', 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=submit value=\"Создать\" class='button_input' title='Создать язык и закрыть окно' />
					</div>
				</form>
		";
	}
	
	function getExternalLanguagePhrases( $lang_id, $path )
	{
		global $query, $mysql, $main, $admin, $utils;
		
		$l = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs` WHERE `id`=".$lang_id );
		if( !$l ) {
			return "Unknown language id";
		}
		
		$codetofind = $query->gp( "codetofind" );
		if( $codetofind && !is_numeric( $codetofind ) )
			$codetofind = 0;
		$texttofind = $query->gp( "texttofind" );
		$texttofind = urldecode( $texttofind );
		if( $texttofind && $texttofind == '0' )
			$texttofind = '';
		$onlyempty = $query->gp( "onlyempty" );
		$onlyempty = $onlyempty == 'on' ? true : false;
		
		$langs_count = $mysql->getTableRecordsCount( "".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs`", "1" );
			
		$maxonpage = 30;
		$page = $query->gp( "page" );
		$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE `lang_id`=".$lang_id.( $codetofind ? " AND `phrase_id`=".$codetofind : "" ).( $texttofind ? " AND `value` LIKE '%".$texttofind."%'" : "" )." ORDER BY `phrase_id` ASC" );
		$items_count = @mysql_num_rows( $a );
		$total = $items_count ? $items_count : 0;
		$pages = "";
		if( $items_count > $maxonpage && !$onlyempty ) {
			$pagesCount = ceil( $items_count / $maxonpage );
			if( $page > $pagesCount )
				$page = $pagesCount;
			else if( $page < 1 )
				$page = 1;
			$startFrom = ( $page - 1 ) * $maxonpage;
			for( $a = 1; $a <= $pagesCount; $a++ )
				$pages .= $page == $a ? "<font size=3><b>$a</b></font>&nbsp;&nbsp;" : "<a href=\"".LOCAL_FOLDER."admin/".$path."/langid".$lang_id."/page".$a."/continue".( $onlyempty ? "/onlyempty!on" :"" )."\">".$a."</a>&nbsp;&nbsp;";
			$pages = "<div align=center>Страницы: $pages</div>";
			$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE `lang_id`=".$lang_id.( $codetofind ? " AND `phrase_id`=".$codetofind : "" )." ORDER BY `phrase_id` ASC LIMIT ".$startFrom.",".$maxonpage );
			$items_count = @mysql_num_rows( $a );
		}
		
		$counter = 0;		
			
		$inner = "";
		
		while( $r = @mysql_fetch_assoc( $a ) ) {
			
			$pc = $mysql->getTableRecordsCount( "".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars`", "`phrase_id`=".$r['phrase_id'] );
			if( $onlyempty && $pc == $langs_count )
				continue;
			
			$mn = $main->modules->getModuleNameById( $r['module'] );
			
			$inner .= "
				<tr class='list_table_element'>
					<td valign=middle>
						".$r['phrase_id']."
					</td>
					<td style='text-align: left;'>
						".strip_tags( $r['value'] )."
					</td>
					<td valign=middle>
						".$pc."
					</td>
					<td valign=middle>
						".( $mn ? $mn : "&nbsp;" )."
					</td>
					<td  valign=middle nowrap>
						<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/phraseid".$r['phrase_id']."/langid".$lang_id."/page".$page."".( $onlyempty ? "/onlyempty!on" :"" )."', 3, 'phrase_edit' );\">Редактировать</a>
						".( $admin->userLevel == 1 ? "	<label class='line_between_links'>|</label>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/deletephrase".$r['phrase_id']."/langid".$lang_id."/page".$page."".( $onlyempty ? "/onlyempty!on" :"" )."\" onclick=\"
							if( !confirm( 'Вы уверены?' ) ) 
								return false; 
							if( confirm( 'Удалять эту фразу во всех языках?' ) )
								this.href += '/deleteall';
							return true;
						\">Удалить</a>" : "" )."
					</td>
				</tr>
			";
			$counter++;
		}
			
		return "
				<h1 align=left>Список фраз языка <b>\"".$l['name']."\"</b></h1>
				
				<div align=left style='margin-bottom: 10px;'>
					".( $admin->userLevel == 1 ? "<input type=button value=\"Добавить фразу\" class='button_input_small_long' title='Добавить новую фразу' style='float: right;' onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/langid".$lang_id."/page".$page."".( $onlyempty ? "/onlyempty!on" :"" )."', 4, 'newphrase' );\" />" : "" )."
					Найти фразу по ID 
					<input type=text class='text_input_small' id='p_to_find' value=\"".( $codetofind ? $codetofind : "" )."\" style='margin-left: 6px; margin-right: 6px; ' /> 
					по тексту 
					<input type=text class='text_input_small' id='p_to_find_bytext' value=\"".( $texttofind ? $texttofind : "" )."\" style='margin-left: 6px; margin-right: 6px; ' /> 
					<input type=button value=\"Найти\" class='button_input_small' title='Найти указанную фразу' onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/langid".$lang_id."".( $onlyempty ? "/onlyempty!on" :"" )."/codetofind!' + ( ge( 'p_to_find' ).value == '' ? '0' : ge( 'p_to_find' ).value ) + '/texttofind!' + ( ge( 'p_to_find_bytext' ).value == '' ? '0' : ge( 'p_to_find_bytext' ).value ), 2, 'phrases' );\" />
					&nbsp;&nbsp;
					<input type=button value=\"Все\" class='button_input_small' title='Показать все фразы' onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/langid".$lang_id."/page".$page."".( $onlyempty ? "/onlyempty!on" :"" )."', 2, 'phrases' );\" />
				</div>
				".( $admin->userLevel == 1 ? "<div align=left style='margin-top: 5px; margin-bottom: 5px;'>
					<input type=checkbox name=\"onlyempty\" id=\"onlyempty\" ".( $onlyempty ? " checked" : "" )."/> - показывать только фразы, где нет переводов
					<input type=button value=\"Применить\" class='button_input_small_long' onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/langid".$lang_id."/page".$page."' + ( ge( 'onlyempty' ).checked ? '/onlyempty!on' : '' ), 2, 'phrases' );\" />
				</div>" : "" )."
				".$pages."
				<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%'>
					<tr class='list_table_header'>
						<td width=40>
							ID
						</td>
						<td width=100%>
							Фраза
						</td>
						<td width=150>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Языки&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
						<td width=150>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Модуль&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
						<td width=110>
							Опции
						</td>
					</tr>
				
					".$inner."
				
					<tr class='list_table_footer'>
						<td colspan=5>
							Всего фраз: ".$counter."
						</td>
					</tr>
				</table>
		
				<script>
					$( function() {
						$( '#phrase_edit' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 550, minHeight: 400, maxWidth: 700, maxHeight: 800, width: 600, height: 500, autoOpen: false } );
						$( '#phrase_edit' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
						
						$( '#newphrase' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 550, minHeight: 400, maxWidth: 700, maxHeight: 800, width: 600, height: 500, autoOpen: false } );
						$( '#newphrase' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
					} );
				</script>
		
				<div id='phrase_edit' title='Редактирование фразы'></div>
				<div id='newphrase' title='Новая фраза'></div>
		";
	}
	
	function getExternalEditPhrase( $lang_id, $path )
	{
		global $query, $mysql, $main;
		
		$l = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs` WHERE `id`=".$lang_id );
		if( !$l ) {
			return "Unknown language id";
		}
		
		$phrase = $query->gp( "phraseid" );
		$p = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE `phrase_id`=".$phrase." AND `lang_id`=".$lang_id );
		$module = $p && $p['module'] ? $p['module'] : 0;
		$page = $query->gp( "page" );
		$onlyempty = $query->gp( "onlyempty" );
		$onlyempty = $onlyempty == 'on' ? true : false;
		
		$inner = "
					".$l['name'].":<br>
					<textarea name=\"".$l['id']."_".$phrase."_phrase_value\" id=\"".$l['id']."_".$phrase."_phrase_value\" class='textarea_input' style='width: 100%; height: 60px;'>".( $p ? $p['value'] : "" )."</textarea>
					<label id='hid_trans' style='display: none'></label>
		";
		
		$prevcode = $l['content-language'];
		$previd = $l['id']."_".$phrase."_phrase_value";
		
		$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs` WHERE `id`<>".$lang_id." ORDER BY `name` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$p = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE `phrase_id`=".$phrase." AND `lang_id`=".$r['id'] );
			if( !$module )
				$module = $p && $p['module'] ? $p['module'] : $module;
			$nextcode = $r['content-language'];
			$nextid = $r['id']."_".$phrase."_phrase_value";
			$inner .= "
				<p>
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
				<textarea name=\"".$r['id']."_".$phrase."_phrase_value\" id=\"".$r['id']."_".$phrase."_phrase_value\" class='textarea_input' style='width: 100%; height: 60px;'>".( $p ? $p['value'] : "" )."</textarea>
			";
			$prevcode = $nextcode;
			$previd = $nextid;
		}
		
		$ajax = $query->gp( "ajax" );
		$toclosename = "";
		$whattochange = "";
		if( $ajax ) {
			$toclosename = $query->gp( "toclosename" );
			$whattochange = $query->gp( "whattochange" );
			if( $whattochange ) {
				$tt = explode( "*", $whattochange );
				$whattochange = "";
				foreach( $tt as $v )
					$whattochange .= "$( '#".$v."' ).html( $( '#".$lang_id."_".$phrase."_phrase_value' ).attr( 'value' ) );";
			}
		}
			
		$t = "
				<h1 align=left>Редактирование фразы № ".$phrase."</h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$path."/langid".$lang_id."/page".$page."".( $onlyempty ? "/onlyempty!on" :"" )."\" method=POST id='phrase_edit_form' style='text-align: left;'>
					".$inner."
					<p>
					Для использования фразы в модуле:<br>
					<select class='select_input' name='choosen_module' id='choosen_module'>
						".$main->modules->getModulesSelectList( "Не указывать", $module )."
					</select>
					</p>
					<div style='width: 300px; border-top: 1px solid #aaa; margin-top: 10px; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"1\" />
						<input type=hidden name=\"phraseid\" id=\"phraseid\" value=\"".$phrase."\" />
						<input type=submit value=\"Сохранить\" class='button_input' title='Сохранить измененные данные и закрыть окно' onclick=\"
							".( $ajax ? "
							".( $whattochange ? $whattochange : "" )."
							putFloatForm( '".LOCAL_FOLDER."admin/".$path."/langid".$lang_id."".( $onlyempty ? "/onlyempty!on" :"" )."/ajax', 'phrase_edit_form' );
							$( '#".$toclosename."' ).dialog( 'close' );
							return false;
							" : "" )."
						\"/>
					</div>
				</form>
		";
		
		return $t;
	}
	
	function getExternalNewPhrase( $lang_id, $path )
	{
		global $query, $mysql, $main;
		
		$l = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs` WHERE `id`=".$lang_id );
		if( !$l ) {
			return "Unknown language id";
		}
		
		$a = $mysql->mqm( "SELECT `phrase_id` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langvars` WHERE 1 GROUP BY `phrase_id` ORDER BY `phrase_id` ASC" );
		$phrase = 1;
		while( $r = @mysql_fetch_assoc( $a ) ) {
			if( $r['phrase_id'] == $phrase ) {
				$phrase++;
			} else 
				break;
		}
		$page = $query->gp( "page" );
		
		$inner = "
					".$l['name'].":<br>
					<textarea name=\"".$l['id']."_".$phrase."_phrase_value\" id=\"".$l['id']."_".$phrase."_phrase_value\" class='textarea_input' style='width: 100%; height: 60px;'></textarea>
					<label id='hid_trans' style='display: none'></label>
		";
		$inner_js = "if( $( '#".$l['id']."_".$phrase."_phrase_value' ).attr( 'value' ) != '' ) one = true;";
		
		$prevcode = $l['content-language'];
		$previd = $l['id']."_".$phrase."_phrase_value";
		
		$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."langs` WHERE `id`<>".$lang_id." ORDER BY `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$nextcode = $r['content-language'];
			$nextid = $r['id']."_".$phrase."_phrase_value";
			$inner_js .= "if( $( '#".$nextid."' ).attr( 'value' ) != '' ) one = true;";
			$inner .= "
				<p>
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
				<textarea name=\"".$r['id']."_".$phrase."_phrase_value\" id=\"".$r['id']."_".$phrase."_phrase_value\" class='textarea_input' style='width: 100%; height: 60px;'></textarea>
			";
			$prevcode = $nextcode;
			$previd = $nextid;
		}
			
		$t = "
				<h1 align=left>Добавление новой фразы (будет № <b><u>".$phrase."</u></b>)</h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$path."/langid".$lang_id."/page".$page."\" method=POST onsubmit=\"
					$( '#process' ).attr( 'value', 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<p>
					Для использования фразы в модуле:<br>
					<select class='select_input' name='choosen_module' id='choosen_module'>
						".$main->modules->getModulesSelectList( "Не указывать" )."
					</select>
					</p>
					<div style='width: 300px; border-top: 1px solid #aaa; margin-top: 10px; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=hidden name=\"phraseid\" id=\"phraseid\" value=\"".$phrase."\" />
						<input type=submit value=\"Добавить\" class='button_input' title='Добавить данные и закрыть окно' onclick=\"
							var one = false;
							".$inner_js."
							if( !one ) {
								alert( 'Укажите хотя бы одну фразу' );
								return false;
							}
							return true;
						\"/>
					</div>
				</form>
		";
		
		return $t;
	}
}

?>