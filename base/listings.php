<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

class Listings
{
	var $global = false;
	var $gl_dbase_string = "`shop`.";
	
	function init()
	{
		global $query;
		
		$this->global = $query->gp( "global" ) ? true : false;
	}
	
	function getTreeViewOfElement( $lid, $eid, $global = false )
	{
		global $lang;
		
		if( !$eid )
			return "";
		
		$cc = trim( $eid );
		$spec = "";
		while( true ) {
			$el = $this->getListingElementById( $lid, $cc, $global );
			if( !$el )
				break;
			$spec = ( @is_numeric( $el['value'] ) ? $lang->gp( $el['value'], $global ) : $el['value'] ).( $spec ? " / ".$spec : "" );
			if( !$el['root'] )
				break;
			$cc = $el['root'];
		}
		
		return $spec;
	}
	
	function getListingForSelecting( $id, $selected, $root = 0, $empty = "", $add = "", $onlyone = false, $where = '', $global = false )
	{
		global $mysql, $lang, $utils;
		
		$t = explode( ",", $selected );
		$sel = array();
		foreach( $t as $v )
			@array_push( $sel, trim($v) );
		
		$a = $mysql->mqm( "SELECT * FROM ".( $global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `listing`=".$id." AND `root`=".$root.( $where ? " AND ".$where : '' )." ORDER BY `order` ASC, `id` ASC" );
		$t = "";
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$t .= "<option value=\"".$r['id']."\"".( $utils->searchArrayForValue( $sel, $r['id'] ) === false ? "" : " selected" ).">".$add.$lang->gp( $r['value'], $global )."</option>";
			if( $r['root'] == 0 && !$onlyone )
				$t .= $this->getListingForSelecting( $id, $selected, $r['id'], "", "&nbsp;&nbsp;&nbsp;&nbsp;", $onlyone, $where, $global );
		}
		
		return $empty.$t;
	}
	
	function getListingForSelectingSpecial( $id, $selected, $root = 0, $empty = "", $add = "", $onlyone = false, $global = false )
	{
		global $mysql, $lang, $utils;
		
		$t = explode( ",", $selected );
		$sel = array();
		foreach( $t as $v )
			@array_push( $sel, $v );
		
		$a = $mysql->mqm( "SELECT * FROM ".( $global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `listing`=".$id." AND `root`=".$root." ORDER BY `order` ASC, `id` ASC" );
		$t = "";
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$t .= "<option value=\"".$r['id']."\"".( $utils->searchArrayForValue( $sel, $r['id'] ) === false ? "" : " selected" ).">".$add.$lang->gp( $r['value'], false )."</option>";
			if( !$onlyone )
				$t .= $this->getListingForSelectingSpecial( $id, $selected, $r['id'], "", $add."&nbsp;&nbsp;&nbsp;&nbsp;", false, $global );
		}
		
		return $empty.$t;
	}
	
	function getWhereElementOfId( $id, $root, $what, $global = false )
	{
		global $mysql;
		
		$w = "`".$what."`=".$root;
		$a = $mysql->mqm( "SELECT `id` FROM ".( $global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `listing`=".$id." AND `root`=".$root." ORDER BY `order` ASC, `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) )
			$w .= ( $w ? " OR " : "" ).$this->getWhereElementOfId( $id, $r['id'], $what, $global );
		
		return $w;
	}
	
	function getWhereElementOfIdForProfile( $id, $root, $elem, $global = false )
	{
		global $mysql;
		
		$w = "(`setting`=".$elem." AND `value`=".$root.")";
		$a = $mysql->mqm( "SELECT `id` FROM ".( $global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `listing`=".$id." AND `root`=".$root." ORDER BY `order` ASC, `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) )
			$w .= ( $w ? " OR " : "" ).$this->getWhereElementOfIdForProfile( $id, $r['id'], $elem, $global );
		
		return $w;
	}
	
	function getListingsForSelecting( $selected, $empty = '', $global = false )
	{
		global $mysql, $lang;
		
		$a = $mysql->mqm( "SELECT * FROM ".( $global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings` WHERE 1 ORDER BY `id` ASC" );
		$t = "";
		while( $r = @mysql_fetch_assoc( $a ) )
			$t .= "<option value=\"".$r['id']."\"".( $selected == $r['id'] ? " selected" : "" ).">".$r['name']."</option>";
		
		return $empty.$t;
	}
	
	function getListingsArray( $global = false )
	{
		global $mysql, $lang;
		
		$a = $mysql->mqm( "SELECT * FROM ".( $global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings` WHERE 1 ORDER BY `id` ASC" );
		$ar = array();
		while( $r = @mysql_fetch_assoc( $a ) )
			$ar[$r['id']] = $r;
		
		return $ar;
	}
	
	function getListingElementsArray( $id, $root = 0, $nums = false, $where = '', $global = false, $order = "ASC" )
	{
		global $mysql;
		
		$a = $mysql->mqm( "SELECT * FROM ".( $global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `listing`=".$id." AND `root`=".$root.( $where ? " AND ".$where : "" )." ORDER BY `order` ".$order.", `id` ASC" );
		$ar = array();
		$index = 1;
		while( $r = @mysql_fetch_assoc( $a ) )
			$ar[!$nums ? $r['id'] : $index++] = $r;
			
		return $ar;
	}
	
	function getListingElementsArrayAll( $id, $where = '', $global = false )
	{
		global $mysql;
		
		$a = $mysql->mqm( "SELECT * FROM ".( $global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `listing`=".$id.( $where ? " AND ".$where : "" )." ORDER BY `order` ASC, `id` ASC" );
		$ar = array();
		while( $r = @mysql_fetch_assoc( $a ) )
			$ar[$r['id']] = $r;
			
		return $ar;
	}
	
	function getListingElementsArraySpec( $id, $order = "`order` DESC, `id` ASC", $limit = "", $root = 0, $global = false )
	{
		global $mysql;
		
		if( $root >= 0 )
			$r = " AND `root`=".$root;
		else
			$r = " AND `root`<>0";
		
		$a = $mysql->mqm( "SELECT * FROM ".( $global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `listing`=".$id.$r." ORDER BY ".$order.( $limit ? " LIMIT ".$limit : "" ) );
		$ar = array();
		while( $r = @mysql_fetch_assoc( $a ) )
			$ar[$r['id']] = $r;
			
		return $ar;
	}
	
	function getListingElementById( $listid, $elid, $global = false )
	{
		global $mysql;
		
		$r = $mysql->mq( "SELECT * FROM ".( $global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `listing`=".$listid." AND `id`=".$elid );
		
		return $r ? $r : null;
	}
	
	function getListingElementValueById( $listid, $elid, $global = false, $default = '' )
	{
		global $mysql, $lang;
		
		$r = $mysql->mq( "SELECT `value` FROM ".( $global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `listing`=".$listid." AND `id`=".$elid );
		
		$result = $r ? ( $listid == 14 || ( $listid >= 22 && $listid <= 26 ) ? str_replace( "~", "", $lang->gp( $r['value'], $global ) ) : $lang->gp( $r['value'], $global ) ) : null;
		
		return $default ? ( $result ? $result : $default ) : $result;
	}
	
	function getListingNameById( $id, $global = false )
	{
		global $mysql;
		
		$r = $mysql->mq( "SELECT * FROM ".( $global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings` WHERE `id`=".$id );
		return $r ? $r['name'] : "неизвестен";
	}
	
	function setListAddData( $elem, $data, $global = false )
	{
		global $mysql;
		
		$mysql->mu( "UPDATE ".( $global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` SET `additional_info`='".$data."' WHERE `id`=".$elem );
	}
	
	function getListingElementAddById( $elid, $global = false )
	{
		global $mysql, $lang;
		
		$r = $mysql->mq( "SELECT `additional_info` FROM ".( $global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `id`=".$elid );
		
		return $r ? $lang->gp( $r['additional_info'] ) : null;
	}
	
	//
	// Далее функции для администраторской панели
	//
	
	function deleteElementsReqursivetly( $elem_array )
	{
		global $mysql;
		
		$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `root`=".$elem_array['id'] );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$this->deleteElementsReqursivetly( $r );
		}
		$mysql->mu( "DELETE FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `id`=".$elem_array['id'] );
		if( $elem_array['image'] )
			@unlink( ROOT_PATH."files/upload/listings/".$elem_array['image'] );
		// $mysql->mu( "DELETE FROM `".$mysql->t_prefix."langvars` WHERE `phrase_id`=".$elem_array['value'] ); // - Лучше не удалять, так как может использоваться иными модулями
	}
	
	function getAdminScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang;
		
		$open = "";
		if( $query->gp( "edit" ) && $query->gp( "process" ) ) {
			
			$id = $query->gp( "edit" );
			$name = $query->gp_letqbe( "l_name" );
			$name = $name ? @str_replace( "'", "\\'", $name ) : "";
			$comment = $query->gp_letqbe( "l_comment" );
			$comment = $comment ? @str_replace( "'", "\\'", $comment ) : "";
			
			$mysql->mu( "UPDATE ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings` SET `name`='".$name."', `comment`='".$comment."' WHERE `id`=".$id );
			
		} else if( $query->gp( "process" ) && $query->gp( "createelement" ) ) {
			
			$id = $query->gp( "listingid" );
			$l_name = $query->gp_letqbe( "l_name" );
			$l_name = $l_name ? @str_replace( "'", "\\'", $l_name ) : "";
			$l_add = $query->gp_letqbe( "l_additional" );
			$l_add = $l_add ? @str_replace( "'", "\\'", $l_add ) : "";
			$root = $query->gp( "elementid" );
			$root = $root ? $root : 0;
			$order = $query->gp( "order" );
			$order = $order && is_numeric( $order ) ? $order : 500;
			$l_name_phrase = $query->gp( "l_name_phrase" );
			$l_name_phrase = $l_name_phrase == 'on' ? true : false;
			$image = $query->gp( "image" );
			if( $image ) {
				@copy( ROOT_PATH."tmp/".$image, ROOT_PATH."files/upload/listings/".$image );
				@unlink( ROOT_PATH."tmp/".$image );
			}
			
			$phrase = $l_name_phrase ? $lang->addNewPhrase( $l_name, 1 ) : $l_name;
			
			$mysql->mu( "INSERT INTO ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` VALUES(0,".$id.",".$root.",'".$phrase."','".$l_add."','".$order."','".$image."');" );
			
			$open = "
				<script>
					getWindowContent( '".LOCAL_FOLDER."admin/".$path."/listingid".$id."', 2, 'openlisting' );
				</script>
			";
		
		} else if( $query->gp( "editelement" ) && $query->gp( "process" ) )	{
			
			$id = $query->gp( "listingid" );
			$elemid = $query->gp( "editelement" );
			$l_add = $query->gp_letqbe( "l_additional" );
			$l_add = $l_add ? @str_replace( "'", "\\'", $l_add ) : "";
			$order = $query->gp( "order" );
			$order = $order && is_numeric( $order ) ? $order : 500;
			$l_name_phrase = $query->gp( "l_name_phrase" );
			$l_name_phrase = $l_name_phrase == 'on' ? true : false;
			$l_name = $query->gp_letqbe( "l_name" );
			$l_name = $l_name ? @str_replace( "'", "\\'", $l_name ) : "";
			
			$image = $query->gp( "image" );
			
			$r = $mysql->mq( "SELECT `image`,`value` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `id`=".$elemid );
			if( $r['image'] != $image ) {
				if( $r['image'] ) 
					@unlink( ROOT_PATH."files/upload/listings/".$r['image'] );
				
				@copy( ROOT_PATH."tmp/".$image, ROOT_PATH."files/upload/listings/".$image );
				@unlink( ROOT_PATH."tmp/".$image );
			}
			
			$phrase = 0;
			if( $l_name_phrase && $l_name && !is_numeric( $r['value'] ) ) {
				$phrase = $lang->addNewPhrase( $l_name, 1 );
			}
			
			$mysql->mu( "UPDATE ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` SET `additional_info`='".$l_add."', `order`='".$order."', `image`='".$image."'".( $phrase || $l_name ? ", `value`='".( $phrase ? $phrase : $l_name )."'" : "" )." WHERE `id`=".$elemid );
			
			$open = "
				<script>
					getWindowContent( '".LOCAL_FOLDER."admin/".$path."/listingid".$id."/lasteditelement".$elemid."', 2, 'openlisting' );
				</script>
			";
		
		} else if( $query->gp( "createlisting" ) && $query->gp( "process" ) ) {
			
			$name = $query->gp_letqbe( "l_name" );
			$name = $name ? @str_replace( "'", "\\'", $name ) : "";
			$comment = $query->gp_letqbe( "l_comment" );
			$comment = $comment ? @str_replace( "'", "\\'", $comment ) : "";
			
			$a = $mysql->mqm( "SELECT `id` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings` WHERE 1 ORDER BY `id` ASC" );
			$listingid = 1;
			while( $r = @mysql_fetch_assoc( $a ) ) {
				if( $r['id'] == $listingid ) {
					$listingid++;
				} else 
					break;
			}
			
			$mysql->mu( "INSERT INTO ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings` VALUES(".$listingid.",'".$name."','".$comment."');" );
			
		} else if( $query->gp( "deleteelement" ) ) {
			
			$id = $query->gp( "listingid" );
			$elemid = $query->gp( "deleteelement" );
			
			$this->deleteElementsReqursivetly( $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `id`=".$elemid ) );
				
			$open = "
				<script>
					getWindowContent( '".LOCAL_FOLDER."admin/".$path."/listingid".$id."', 2, 'openlisting' );
				</script>
			";
		} else if( $query->gp( "delete" ) ) {
			
			$id = $query->gp( "delete" );			
			$r = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings` WHERE `id`=".$id );
			
			if( $r ) {
				$mysql->mu( "DELETE FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings` WHERE `id`=".$id );
				$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `listing`=".$id." AND `root`=0" );
				while( $r = @mysql_fetch_assoc( $a ) )
					$this->deleteElementsReqursivetly( $r );
			}
			
		}
		
		/*$aarr = array(
			1 => 9,
			2 => 11,
			3 => 10,
			4 => 14,
			5 => 13,
			6 => 12
		);
		if( $query->gp( "asdasd" ) ) {
			$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `listing`=2" );
			while( $r = @mysql_fetch_assoc( $a ) ) {
				if( !$r['additional_info'] ) 
					continue;
				$tt = explode( ";", $r['additional_info'] );
				$mysql->mu( "UPDATE ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` SET `root`=".$aarr[$tt[1]]." WHERE `id`=".$r['id'] );
			}
		}*/
		
		// <label class='line_between_links'>|</label>	<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."', 4, 'importlisting' );\">Импортировать список</a>
		
		$selectedElement = 0;
		$t = "
			<h1>".( $this->global ? "Глобальные" : "Локальные" )." списки системы</h1>
			
			".( $admin->userLevel == 1 ? "
			<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."', 1, 'newlisting' );\">Создать новый список</a>
				
			<br><br>" : "" )."
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 800px;'>
				<tr class='list_table_header'>
					<td width=30>
						ID
					</td>
					<td style='text-align: left;'>
						Название
					</td>
					<td style='text-align: left;'>
						Описание
					</td>
					<td width=110>
						Всего элементов
					</td>
					<td width=110>
						Опции
					</td>
				</tr>
		";
		
		$counter = 0;
		$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings` WHERE 1 ORDER BY `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$t .= "
				<tr class='list_table_element'>
					<td>
						".$r['id']."
					</td>
					<td style='text-align: left;'>
						".$r['name']."
					</td>
					<td style='text-align: left;'>
						".str_replace( "\n", "<br>", $r['comment'] )."
					</td>
					<td>
						".$mysql->getTableRecordsCount( "".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements`", "`listing`=".$r['id'] )."
					</td>
					<td nowrap>
						<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/listingid".$r['id']."', 2, 'openlisting' );\">Открыть</a>
							<label class='line_between_links'>|</label>
						<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/listingid".$r['id']."', 3, 'listing_settings' );\">Настройки</a>
							".( $admin->userLevel == 1 ? "<label class='line_between_links'>|</label>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/delete".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a>" : "" )."
					</td>
				</tr>
			";
			$counter++;
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=5>
						Всего списков: ".$counter."
					</td>
				</tr>
		</table>
		
		<script>
			$( function() {
				$( '#listing_settings' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 300, maxWidth: 700, maxHeight: 600, width: 500, height: 300, autoOpen: false } );
				$( '#listing_settings' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
				$( '#openlisting' ).dialog( { closeOnEscape: false, zIndex: 3001, stack: true, minWidth: 800, minHeight: 500, maxWidth: 1000, maxHeight: 900, width: 900, height: 800, autoOpen: false } );
				$( '#openlisting' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jquijqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
				$( '#newlisting' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 300, maxWidth: 700, maxHeight: 600, width: 500, height: 300, autoOpen: false } );
				$( '#newlisting' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
				$( '#importlisting' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 300, maxWidth: 700, maxHeight: 600, width: 500, height: 300, autoOpen: false } );
				$( '#importlisting' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
			} );
		</script>
		
		<div id='listing_settings' title='Настройки списка'></div>
		<div id='openlisting' title='Содержание списка'></div>
		<div id='newlisting' title='Новый список'></div>
		<div id='importlisting' title='Импортирование списка'></div>
		".$open;
		
		return $t;
	}
	
	function getExternal( $wt, $link )
	{
		global $query, $mysql;
		
		$listingid = $query->gp( "listingid" );
		
		if( $wt == 1 ) { // Новый список
			
			return $this->getExternalNewListing( $link );
			
		} else if( $wt == 2 ) { // Открыть список
			
			return $this->getExternalOpenListing( $listingid, $link );
			
		} else if( $wt == 3 ) { // Редактировать установки списка
			
			return $this->getExternalListingSettings( $listingid, $link );
			
		} else if( $wt == 5 ) { // Новый элемент списка
			
			return $this->getExternalListingNewElement( $listingid, $link );
			
		} else if( $wt == 6 ) { // Редактирование элемента списка
			
			return $this->getExternalListingElementEdit( $listingid, $link );
			
		}
		
		return "Unknown listing query";
	}
	
	function getExternalNewListing( $link )
	{
		global $query, $mysql, $lang;
		
		$inner = "
				<p>
					Название списка: <label class='red'>*</label> (пример: Города, Пол, Болезни и тд.)<br>
					<input type=text name=\"l_name\" id=\"l_name\" value=\"\" class='text_input' />
				</p>
				<p>
					Описание списка:<br>
					<textarea name=\"l_comment\" id=\"l_comment\" class='textarea_input' style='width: 100%; height: 60px;'></textarea>
				</p>
			";
			
		return "
				<h1 align=left>Создание нового ".( $this->global ? "глобального" : "локального" )." списка</h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$link."/createlisting\" method=POST onsubmit=\"
					if( $( '#l_name' ).val() == '' ) { 
						alert( 'Укажите название списка' ); 
						return false; 
					}
					$( '#process' ).val( 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=submit value=\"Создать\" class='button_input' title='Создать список и закрыть окно' />
					</div>
				</form>
		";
	}
	
	function getExternalListingSettings( $listing_id, $link )
	{
		global $query, $mysql, $lang;
		
		$m = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings` WHERE `id`=".$listing_id );
		if( !$m ) {
			return "Unknown listing id";
		}
			
		$inner = "
				<p>
					Название списка: <label class='red'>*</label> (пример: Города, Пол, Болезни и тд.)<br>
					<input type=text name=\"l_name\" id=\"l_name\" value=\"".$m['name']."\" class='text_input' />
				</p>
				<p>
					Описание списка:<br>
					<textarea name=\"l_comment\" id=\"l_comment\" class='textarea_input' style='width: 100%; height: 60px;'>".$m['comment']."</textarea>
				</p>
			";
			
		return "
				<h1 align=left>Настройки ".( $this->global ? "глобального" : "локального" )." списка <b>\"".$m['name']."\"</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$link."/edit".$listing_id."\" method=POST onsubmit=\"
					if( $( '#l_name' ).val() == '' ) { 
						alert( 'Укажите название списка' ); 
						return false; 
					}
					$( '#process' ).val( 1 );
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
	
	function buildReqList( $listing_id, $codetofind, $root, $path, $padding, $bgcolor, &$counter, &$jso = "", &$jsc = "" )
	{
		global $mysql, $lang, $query;
		
		$lasteditelement = $query->gp( "lasteditelement" );
		$rrr = $lasteditelement ? $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `id`=".$lasteditelement ) : '';
		$last_root = $rrr ? $rrr['root'] : 0;
		
		$inner = "";
		$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `root`=".$root." AND `listing`=".$listing_id.( $codetofind ? " AND `id`=".$codetofind : "" )." ORDER BY `order` ASC, `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$inner .= "
				<tr class='list_table_element'".( $root ? ( !$last_root || $root != $last_root ? " style='display: none;'" : " style='background-color: #".dechex( $bgcolor ).dechex( $bgcolor ).dechex( $bgcolor ).";'" ) : "" )." id='tr_id_".$r['id']."'>
					<td valign=middle>
						".$r['id']."
					</td>
					<td valign=middle>
						".$r['order']."
					</td>
					<td style='text-align: left;".( $padding ? " padding-left: ".$padding."px;" : "" )."'>
						%crest%
						<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/elementid".$r['id']."/listingid".$listing_id."', 6, 'element_edit' );\"><label id='element_name_".$r['id']."' style='cursor: pointer;'>".$lang->getPh( $r['value'], $this->global )."</label></a>
					</td>
					<td style='text-align: left;'>
						".( $r['additional_info'] ? $r['additional_info'] : "&nbsp;" )."
					</td>
					<td>
						".( $r['image'] ? "<img src=\"".LOCAL_FOLDER."files/upload/listings/".$r['image']."\" style='max-width: 100px;' />" : "-" )."
					</td>
					<td  valign=middle nowrap>
						<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/elementid".$r['id']."/listingid".$listing_id."', 5, 'newelement' );\" title='Добавить новый дочерний элемент списка'>Добавить элемент</a>
							<br>
						<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/elementid".$r['id']."/listingid".$listing_id."', 6, 'element_edit' );\">Редактировать</a>
							<br>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/deleteelement".$r['id']."/listingid".$listing_id."\" onclick=\"
							if( !confirm( 'Вы уверены?' ) ) 
								return false; 
							return true;
						\">Удалить</a>
					</td>
				</tr>
			";
			if( $root ) {
				$jso .= "$('#tr_id_".$r['id']."').show().css( 'background-color', '#".dechex( $bgcolor ).dechex( $bgcolor ).dechex( $bgcolor )."' );";
				$jsc .= "$('#tr_id_".$r['id']."').hide();";
			}
			$counter++;
			$ct = $counter;
			$js_open = "";
			$js_close = "";
			$inner .= $this->buildReqList( $listing_id, $codetofind, $r['id'], $path, $padding + 25, $bgcolor - 15, $counter, $js_open, $js_close );
			if( $ct < $counter )
				$inner = str_replace( "%crest%", "
					<img src=\"".LOCAL_FOLDER."images/plus.gif\" onclick=\"
						if( this.title == 'Открыть ветку' ) {
							this.title = 'Закрыть ветку';
							this.src = '".LOCAL_FOLDER."images/minus.gif';
							".$js_open."
						} else {
							this.title = 'Открыть ветку';
							this.src = '".LOCAL_FOLDER."images/plus.gif';
							".$js_close."
						}
					\" title='Открыть ветку' style='float: left; cursor: pointer; padding-right: 5px;' />
				", $inner );
			else 
				$inner = str_replace( "%crest%", "<img src=\"".LOCAL_FOLDER."images/s.gif\" width=20 height=1 />", $inner );
		}
		
		return $inner;
	}
	
	function getExternalOpenListing( $listing_id, $path )
	{
		global $query, $mysql, $main, $lang, $utils;
		
		$l = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings` WHERE `id`=".$listing_id );
		if( !$l ) {
			return "Unknown listing id";
		}
		
		$codetofind = $query->gp( "codetofind" );
		if( $codetofind && !is_numeric( $codetofind ) )
			$codetofind = 0;
			
		$counter = 0;
		$inner = $this->buildReqList( $listing_id, $codetofind, 0, $path, 0, 250, $counter );
		
		return "
				<h1 align=left>Список элементов списка <b>\"".$l['name']."\"</b></h1>
				
				<div align=left style='margin-bottom: 10px;'>
					<input type=button value=\"Добавить элемент\" class='button_input_small_long' title='Добавить новый корневой элемент списка' style='float: right;' onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/listingid".$listing_id."', 5, 'newelement' );\" />
					Найти элемент по ID 
					<input type=text class='text_input_small' id='p_to_find' value=\"".( $codetofind ? $codetofind : "" )."\" style='margin-left: 6px; margin-right: 6px; ' /> 
					<input type=button value=\"Найти\" class='button_input_small' title='Найти указанный элемент' onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/listingid".$listing_id."/codetofind!' + ( ge( 'p_to_find' ).value == '' ? '0' : ge( 'p_to_find' ).value ), 2, 'openlisting' );\" />
					&nbsp;&nbsp;
					<input type=button value=\"Все\" class='button_input_small' title='Показать все элементы' onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/listingid".$listing_id."', 2, 'openlisting' );\" />
				</div>
				
				<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%'>
					<tr class='list_table_header'>
						<td width=5%>
							ID
						</td>
						<td width=10%>
							Порядок
						</td>
						<td width=40% style='text-align: left;'>
							Наименование
						</td>
						<td width=30% style='text-align: left;'>
							Дополнительно
						</td>
						<td width=20%>
							Изображение
						</td>
						<td width=110>
							Опции
						</td>
					</tr>
				
					".$inner."
				
					<tr class='list_table_footer'>
						<td colspan=6>
							Всего элементов: ".$counter."
						</td>
					</tr>
				</table>
		
				<script>
					$( function() {
						$( '#element_edit' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 550, minHeight: 500, maxWidth: 700, maxHeight: 800, width: 600, height: 600, autoOpen: false } );
						$( '#element_edit' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
						
						$( '#newelement' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 550, minHeight: 300, maxWidth: 700, maxHeight: 700, width: 600, height: 500, autoOpen: false } );
						$( '#newelement' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
					} );
				</script>
		
				<div id='element_edit' title='Редактирование элемента'></div>
				<div id='newelement' title='Новый элемент'></div>
		";
	}
	
	function getExternalListingNewElement( $listing_id, $path )
	{
		global $query, $mysql, $main, $lang;
		
		$l = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings` WHERE `id`=".$listing_id );
		if( !$l ) {
			return "Unknown listing id";
		}
		
		$root = $query->gp( "elementid" );
		$rootArray = $root ? $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `id`=".$root ) : null;
		
		$inner = ( $root ? "
				<p>
					Корневой элемент: <b>".$lang->getPh( $rootArray['value'], $this->global )." (".$root.")</b>
				</p>
		" : "" )."
				<p>
					Наименование элемента: <label class='red'>*</label> (добавление перевода возможно в режиме редактирования элемента)<br>
					<input type=text name=\"l_name\" id=\"l_name\" value=\"\" class='text_input' /><br>
					<input type=checkbox name='l_name_phrase' /> - создавать фразу?
				</p>
				<p>
					Порядок вывода: (чем больше число, тем выше в списке при стандартной сортировке)<br>
					<input type=text name=\"order\" id=\"order\" value=\"500\" class='text_input' />
				</p>
				<p>
					Дополнительная информация:<br>
					<textarea name=\"l_additional\" id=\"l_additional\" class='textarea_input' style='width: 100%; height: 60px;'></textarea>
				</p>
				
				<label class=\"uploadbutton\" id=\"image_upload\">
					<span id=\"image_upload_innerspan\">
						Выберите изображение (если нужно)
					</span>
				</label>
				
				<input type=hidden name=\"image\" id=\"image\" value=\"\" />
				<div class='error' id='banner_error'></div>
				<div id='image_image' style='margin-top: 7px; margin-bottom: 7px;'></div>
				
				<script>
					$( '#image_upload' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'image',
       						action: '".$mysql->settings['local_folder']."admin/getblock',
       						data: {
    							external: '1',
    							type: '13',
    							extensions: 'gif|jpg|jpeg|png',
    							totemp: '1',
    							fname: 'image'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	$( '#image_error' ).html( 'Неверное расширение файла' ).show();
                					return false;
					            }
       		
					            $( '#image_error' ).hide();
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								$( '#image_error' ).html( 'Ошибка обработки файла' ).show();
       							} else {
       								$( '#image_image' ).html( '<img src=\"".$mysql->settings['local_folder']."tmp/' + data + '\" />' );
       								$( '#image_error' ).hide();
       								
			       					$( '#image' ).val( data );
			       					
       							}
			       			}
						} );
					} );
				</script>
			";
			
		return "
				<h1 align=left>Добавление нового ".( $root ? "дочернего " : "" )."элемента</h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$path."/listingid".$listing_id."/createelement\" method=POST onsubmit=\"
					if( $( '#l_name' ).val() == '' ) { 
						alert( 'Укажите наименование элемента' ); 
						return false; 
					}
					$( '#process' ).val( 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px; margin-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						".( $root ? "<input type=hidden name=\"elementid\" id=\"elementid\" value=\"".$root."\" />" : "" )."
						<input type=submit value=\"Создать\" class='button_input' title='Создать элемент и закрыть окно' />
					</div>
				</form>
		";
		
		return $t;
	}
	
	function getExternalListingElementEdit( $listingid, $link )
	{
		global $query, $mysql, $lang, $utils;
		
		$id = $query->gp( "elementid" );
		$m = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."listings_elements` WHERE `id`=".$id );
		if( !$m ) {
			return "Unknown element id";
		}
			
		$inner = "
				".( is_numeric( $m['value'] ) ? "
				<p>
					Наименование элемента:<br>
					<a href='#' title='Редактирование наименования в отдельном окне' class='forallunknowns' onclick=\"
						getWindowContent( '".LOCAL_FOLDER."admin/langs".( $this->global ? "/global" : "" )."/phraseid".$m['value']."/langid1/ajax/whattochange!tochange*element_name_".$id."/toclosename!phrase_edit', 3, 'phrase_edit' );
					\">Редактирование в отдельном окне</a>
				</p>
				" : "
				<p>
					Наименование элемента: <label class='red'>*</label> (добавление перевода возможно в режиме редактирования элемента)<br>
					<input type=text name=\"l_name\" id=\"l_name\" value=\"".$m['value']."\" class='text_input' /><br>
					<input type=checkbox name='l_name_phrase' /> - создавать фразу?
				</p>
				" )."
				<p>
					Порядок вывода: (чем больше число, тем выше в списке при стандартной сортировке)<br>
					<input type=text name=\"order\" id=\"order\" value=\"".$m['order']."\" class='text_input' />
				</p>
				<p>
					Дополнительная информация:<br>
					<textarea name=\"l_additional\" id=\"l_additional\" class='textarea_input' style='width: 100%; height: 60px;'>".$m['additional_info']."</textarea>
				</p>
				
				<label class=\"uploadbutton\" id=\"image_upload\">
					<span id=\"image_upload_innerspan\">
						Выберите изображение (если нужно)
					</span>
				</label>
				
				<input type=hidden name=\"image\" id=\"image\" value=\"".$m['image']."\" />
				<div class='error' id='image_error'></div>
				<div id='image_image' style='margin-top: 7px; margin-bottom: 7px;'>
					".( $m['image'] ? "<img src=\"".$mysql->settings['local_folder']."files/upload/listings/".$m['image']."\" />" : "" )."
				</div>
				
				<script>
					$( '#image_upload' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'image',
       						action: '".$mysql->settings['local_folder']."admin/getblock',
       						data: {
    							external: '1',
    							type: '13',
    							extensions: 'gif|jpg|jpeg|png',
    							totemp: '1',
    							fname: 'image'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	$( '#image_error' ).html( 'Неверное расширение файла' ).show();
                					return false;
					            }
       		
					            $( '#image_error' ).hide();
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								$( '#image_error' ).html( 'Ошибка обработки файла' ).show();
       							} else {
       								
       								$( '#image_image' ).html( '<img src=\"".$mysql->settings['local_folder']."tmp/' + data + '\" />' );
       								$( '#image_error' ).hide();
       								
			       					$( '#image' ).val( data );
			       					
       							}
			       			}
						} );
					} );
				</script>
			";
			
		return "
				<h1 align=left>Редактирование элемента <b>\"<label id=\"tochange\">".$lang->getPh( $m['value'], $this->global )."</label>\"</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$link."/editelement".$id."/listingid".$listingid."\" method=POST onsubmit=\"
					$( '#process' ).val( 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px; margin-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=submit value=\"Сохранить\" class='button_input' title='Сохранить измененные данные и закрыть окно' />
					</div>
				</form>
				
				<script>
					$( function() {
						$( '#phrase_edit' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 550, minHeight: 400, maxWidth: 700, maxHeight: 800, width: 550, height: 500, autoOpen: false } );
						$( '#phrase_edit' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
					} );
				</script>
		
				<div id='phrase_edit' title='Редактирование наименования элемента'></div>
		";
	}
}