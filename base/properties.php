<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

class Properties
{
	var $global = false;
	var $gl_dbase_string = "`shop`.";
	
	var $types = array(
		1 => "Строчка текста",
		2 => "Выбор из списка",
		3 => "Множественный выбор из списка",
		4 => "Чекбокс",
		5 => "Несколько строковых элементов",
		6 => "Блок текста",
		7 => "Множественный выбор графических файлов",
		8 => "Один графический файл"
	);
	
	function init()
	{
		global $query;
		
		$this->global = $query->gp( "global" ) ? true : false;
	}
	
	function getCurrentList()
	{
		global $mysql, $query, $utils, $lang, $main;
		
		$props = array();
		$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."good_props` WHERE 1 ORDER BY `order` ASC, `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$props[$r['id']] = $r;
		}
		
		return $props;
	}
	
	function getPropertiesOfGood( $id )
	{
		global $mysql;
		
		$props = array();
		$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$id." ORDER BY `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) )
			$props[$r['id']] = $r;
			
		return $props;
	}
	
	//
	// Далее функции для администраторской панели
	//
	
	function getAdminScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;
		
		$open = "";
		if( $query->gp( "process" ) && $query->gp( "createelement" ) ) {
			
			$l_name = $query->gp_letqbe( "l_name" );
			$l_name = $l_name ? @str_replace( "'", "\\'", $l_name ) : "";
			$comment = $query->gp_letqbe( "comment" );
			$comment = $comment ? @str_replace( "'", "\\'", $comment ) : "";
			$order = $query->gp( "order" );
			$order = $order && is_numeric( $order ) ? $order : 500;
			$l_name_phrase = $query->gp( "l_name_phrase" );
			$l_name_phrase = $l_name_phrase == 'on' ? true : false;
			$type = $query->gp( "type" );
			$source = $type == 2 || $type == 3 ? $query->gp( "source" ) : 0;
			//$for = $query->gp( "for" );
			//$for = $for ? $for : 0;
			
			$phrase = $l_name_phrase ? $lang->addNewPhrase( $l_name, 1 ) : $l_name;
			
			$mysql->mu( "INSERT INTO ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."good_props` VALUES(
				0,
				'".$phrase."',
				'".$comment."',
				".$type.",
				'".$source."',
				0,
				'".$order."'
			);" );
			
		} else if( $query->gp( "editelement" ) && $query->gp( "process" ) )	{
			
			$elemid = $query->gp( "editelement" );
			$l_name = $query->gp_letqbe( "l_name" );
			$l_name = $l_name ? @str_replace( "'", "\\'", $l_name ) : "";
			$comment = $query->gp_letqbe( "comment" );
			$comment = $comment ? @str_replace( "'", "\\'", $comment ) : "";
			$order = $query->gp( "order" );
			$order = $order && is_numeric( $order ) ? $order : 500;
			$l_name_phrase = $query->gp( "l_name_phrase" );
			$l_name_phrase = $l_name_phrase == 'on' ? true : false;
			$type = $query->gp( "type" );
			$source = $type == 2 || $type == 3 ? $query->gp( "source" ) : 0;
			//$for = $query->gp( "for" );
			//$for = $for ? $for : 0;
			
			$r = $mysql->mq( "SELECT `name` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."good_props` WHERE `id`=".$elemid );
			
			$phrase = 0;
			if( $l_name_phrase && $l_name && !is_numeric( $r['name'] ) ) {
				$phrase = $lang->addNewPhrase( $l_name, 1 );
			}
			
			$mysql->mu( "UPDATE ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."good_props` SET 
			
				`type`='".$type."',
				`source`='".$source."',
				
				`order`='".$order."', 
				`comment`='".$comment."'".( $phrase || $l_name ? ", 
				`name`='".( $phrase ? $phrase : $l_name )."'" : "" )." 
				
			WHERE `id`=".$elemid );
			
			$query->setProperty( 'editelement', 0 );
			
		} else if( $query->gp( "delete" ) ) {
			
			$id = $query->gp( "delete" );			
			$r = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."good_props` WHERE `id`=".$id );
			
			if( $r ) {
				$mysql->mu( "DELETE FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."good_props` WHERE `id`=".$id );
				
				// тут нужно будет удалить все параметры у товароы
			}
			
		}
		
		//$gtype = $query->gp( "gtype" );
		
		//$razdels = $main->listings->getListingElementsArray( 22, 0, false, '', $this->global );
		$sources = $main->listings->getListingsArray( $this->global );
		
		$selectedElement = 0;
		$t = "
			<h1>Параметры товаров</h1>
			
			<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path.( $gtype ? "/gtype".$gtype : "" )."', 1, 'newproperty' );\">Создать новый параметр</a>
				
			<br><br>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%;'>
				<tr class='list_table_header'>
					<td width=30>
						ID
					</td>
					<td style='text-align: left;'>
						Название и описание
					</td>
					<td>
						Тип и источник
					</td>
					<td>
						Порядок вывода
					</td>
					<td width=110>
						Опции
					</td>
				</tr>
		";
		
		$where = "";
		
		if( !$where )
			$where = "1";
		
		$counter = 0;
		$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."good_props` WHERE ".$where." ORDER BY `order` ASC, `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$t .= "
				<tr class='list_table_element'>
					<td>
						".$r['id']."
					</td>
					<td style='text-align: left;'>
						<b>".( !is_numeric( $r['name'] ) ? $r['name'] : $lang->gp( $r['name'], $this->global ) )."</b>".( $r['comment'] ? "<br>---<br>".str_replace( "\n", "<br>", $r['comment'] ) : "" )."						
					</td>
					<td>
						".$this->types[$r['type']].( $r['source'] ? "<br>---<br><b>".$sources[$r['source']]['name'] : "" )."</b>
					</td>
					<td>
						".$r['order']."
					</td>
					<td nowrap>
						<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/propertyid".$r['id'].( $gtype ? "/gtype".$gtype : "" )."', 2, 'property_settings' );\">Редактировать</a>
							<label class='line_between_links'>|</label>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/delete".$r['id'].( $gtype ? "/gtype".$gtype : "" )."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a>
					</td>
				</tr>
			";
			$counter++;
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=5>
						Всего параметров: ".$counter."
					</td>
				</tr>
		</table>
		
		<script>
			$( function() {
				$( '#property_settings' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 300, maxWidth: 800, maxHeight: 800, width: 600, height: 550, autoOpen: false } );
				$( '#property_settings' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
				$( '#newproperty' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 300, maxWidth: 800, maxHeight: 800, width: 600, height: 550, autoOpen: false } );
				$( '#newproperty' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
			} );
		</script>
		
		<div id='property_settings' title='Настройки параметра'></div>
		<div id='newproperty' title='Новый параметр'></div>
		".$open;
		
		return $t;
	}
	
	function getExternal( $wt, $link )
	{
		global $query, $mysql;
		
		$propertyid = $query->gp( "propertyid" );
		
		if( $wt == 1 ) { // Новый элемент 
			
			return $this->getExternalpropertyNewElement( $link );
			
		} else if( $wt == 2 ) { // Редактирование элемента 
			
			return $this->getExternalpropertyElementEdit( $link );
			
		}
		
		return "Unknown property query";
	}
	
	function getExternalpropertyNewElement( $path )
	{
		global $query, $mysql, $main, $lang;
		
		$gtype = $query->gp( "gtype" );
		
		$types = "";
		foreach( $this->types as $id => $type )
			$types .= "<option value='".$id."'>".$type."</option>";
		
		$inner = "
				<p>
					Наименование параметра: <label class='red'>*</label> (добавление перевода возможно в режиме редактирования параметра)<br>
					<input type=text name=\"l_name\" id=\"l_name\" value=\"\" class='text_input' /><br>
					<input type=checkbox name='l_name_phrase' checked /> - создавать фразу?
				</p>
				<p>
					Тип параметра:<br>
					<select name='type' class='select_input'>
						".$types."
					</select>
				</p>
				<p>
					Источник: (если нужно для выбранного типа параметра)<br>
					<select name='source' class='select_input'>
						".$main->listings->getListingsForSelecting( 0, '', $this->global )."
					</select>
				</p>
				<p>
					Порядок вывода: (чем больше число, тем выше в списке при стандартной сортировке)<br>
					<input type=text name=\"order\" id=\"order\" value=\"500\" class='text_input' />
				</p>
				<p>
					Дополнительная информация:<br>
					<textarea name=\"comment\" id=\"comment\" class='textarea_input' style='width: 100%; height: 60px;'></textarea>
				</p>
			";
			
		return "
				<h1 align=left>Добавление нового параметра</h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$path.( $gtype ? "/gtype".$gtype : "" )."/createelement\" method=POST onsubmit=\"
					if( $( '#l_name' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите наименование параметра' ); 
						return false; 
					}
					$( '#process' ).attr( 'value', 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px; margin-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=submit value=\"Создать\" class='button_input' title='Создать параметр и закрыть окно' />
					</div>
				</form>
		";
		
		return $t;
	}
	
	function getExternalpropertyElementEdit( $link )
	{
		global $query, $mysql, $main, $lang, $utils;
		
		$id = $query->gp( "propertyid" );
		$m = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."good_props` WHERE `id`=".$id );
		if( !$m ) {
			return "Unknown element id";
		}
		
		$gtype = $query->gp( "gtype" );
			
		$types = "";
		foreach( $this->types as $tid => $type )
			$types .= "<option value='".$tid."'".( $m['type'] == $tid ? " selected" : "" ).">".$type."</option>";
		
		$inner = "
				".( is_numeric( $m['name'] ) ? "
				<p>
					Наименование параметра:<br>
					<a href='#' title='Редактирование названия параметра в отдельном окне' class='forallunknowns' onclick=\"
						getWindowContent( '".LOCAL_FOLDER."admin/langs".( $this->global ? "/global" : "" )."/phraseid".$m['name']."/langid1/ajax/whattochange!tochange*element_name_".$id."/toclosename!phrase_edit', 3, 'phrase_edit' );
					\">Редактирование в отдельном окне</a>
				</p>
				" : "
				<p>
					Наименование параметра: <label class='red'>*</label> (добавление перевода возможно в режиме редактирования параметра)<br>
					<input type=text name=\"l_name\" id=\"l_name\" value=\"".$m['name']."\" class='text_input' /><br>
					<input type=checkbox name='l_name_phrase' checked /> - создавать фразу?
				</p>
				" )."
				<p>
					Тип параметра:<br>
					<select name='type' class='select_input'>
						".$types."
					</select>
				</p>
				<p>
					Источник: (если нужно для выбранного типа параметра)<br>
					<select name='source' class='select_input'>
						".$main->listings->getListingsForSelecting( is_numeric( $m['source'] ) ? $m['source'] : 0, '', $this->global )."
					</select>
				</p>
				<p>
					Порядок вывода: (чем больше число, тем выше в списке при стандартной сортировке)<br>
					<input type=text name=\"order\" id=\"order\" value=\"".$m['order']."\" class='text_input' />
				</p>
				<p>
					Дополнительная информация:<br>
					<textarea name=\"comment\" id=\"comment\" class='textarea_input' style='width: 100%; height: 60px;'>".$m['comment']."</textarea>
				</p>
			";
			
		return "
				<h1 align=left>Редактирование параметра <b>\"<label id=\"tochange\">".$lang->getPh( $m['name'], $this->global )."</label>\"</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$link."/editelement".$id.( $gtype ? "/gtype".$gtype : "" )."\" method=POST onsubmit=\"
					$( '#process' ).attr( 'value', 1 );
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
		
				<div id='phrase_edit' title='Редактирование наименования параметра'></div>
		";
	}
}