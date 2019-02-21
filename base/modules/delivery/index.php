<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class moduledelivery extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	var $gl_dbase_string = "`shop`.";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function getDeliveryPrice( $id )
	{
		global $mysql, $main;
		
		$r = $mysql->mq( "SELECT `price` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."delivery` WHERE `id`=".$id );
		
		return $r ? $r['price'] : 0;
	}
	
	function getDeliveryName( $id )
	{
		global $mysql, $main;
		
		$r = $mysql->mq( "SELECT `name` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."delivery` WHERE `id`=".$id );
		
		return $r ? $r['name'] : 0;
	}
        
        function getELemesforselect( $selected = 0 )
        {
            global $mysql, $main;
            
            $ret = "";
            $a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."delivery` WHERE `view`=1" );
            while( $r = @mysql_fetch_assoc( $a ) ) {
                   $ret .= "<option".( $selected == $r['id'] ? " selected" : "" )." value=".$r['id'].">".$r['name']."</option>";
            }
            
            return $ret;
        }        
	
	
	//
	// Далее функции для администраторской панели
	//
	
	function getAdminScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;
		
		$open = "";
		
		if( $query->gp( "edit" ) && $query->gp( "process" ) ) {
			
			$deliveryid = $query->gp( "deliveryid" );
			$name = isset( $_POST['name'] ) && $_POST['name'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['name'] )  ) : '';
			$comment = isset( $_POST['comment'] ) && $_POST['comment'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['comment'] )  ) : '';
			$long = isset( $_POST['long'] ) && $_POST['long'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['long'] )  ) : '';
			$price = $query->gp( "price" );
			
			$mysql->mu( "UPDATE ".$this->gl_dbase_string."`".$mysql->t_prefix."delivery` SET 
			
				`name`='".$name."',
				`comment`='".$comment."',
				`long`='".$long."',
				`price`=".$price."
				
				WHERE `id`=".$deliveryid 
			
			);
			
			$query->setProperty( "deliveryid", 0 );
			
		} else if( $query->gp( "addnew" ) && $query->gp( "process" ) ) {
			
			$name = isset( $_POST['name'] ) && $_POST['name'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['name'] )  ) : '';
			$comment = isset( $_POST['comment'] ) && $_POST['comment'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['comment'] )  ) : '';
			$long = isset( $_POST['long'] ) && $_POST['long'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['long'] )  ) : '';
			$price = $query->gp( "price" );
			
			$mysql->mu( "INSERT INTO ".$this->gl_dbase_string."`".$mysql->t_prefix."delivery` VALUES(
				
				0,
				'".$name."',
				'".$comment."',
				'".$long."',
				".$price.",
				1
				
			);" );
			
		} else if( $query->gp( "turndelivery" ) ) {
			
			$id = $query->gp( "turndelivery" );
			$ep = $mysql->mq( "SELECT `view` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."delivery` WHERE `id`=".$id );			
			if( $ep )
				$mysql->mu( "UPDATE ".$this->gl_dbase_string."`".$mysql->t_prefix."delivery` SET `view`=".( $ep['view'] ? "0" : "1" )." WHERE `id`=".$id );
			
		} else if( $query->gp( "delete" ) ) {
			
			$deliveryid = $query->gp( "delete" );
			$mysql->mu( "DELETE FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."delivery` WHERE `id`=".$deliveryid );
			
		}
		
		$selectedElement = 1;
		
		$t = "
			<h1>Список вариантов доставки</h1>
			
			<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."', 1, 'newdelivery' );return false;\">Добавить вариант</a><br><br>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%;'>
				<tr class='list_table_header'>
					<td width=30% nowrap>
						Название и описание
					</td>
					<td width=30% nowrap>
						Сроки доставки
					</td>
					<td width=30% nowrap>
						Стоимость доставки
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
		$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."delivery` WHERE ".$where );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			
			$t .= "
				<tr class='list_table_element'".( !$r['view'] ? " style='background-color: #fcd9d9;'" : "" ).">
					<td style='text-align: left;'>
						<b>".$r['name']."</b>
						<p class='comment'>".$r['comment']."</p>
					</td>
					<td>
						".$r['long']."
					</td>
					<td>
						".( $r['price'] ? $r['price']." руб." : "Бесплатно" )."
					</td>
					<td nowrap>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/turndelivery".$r['id']."\">".( $r['view'] ? "Выключить" : "Включить" )."</a><br>
						<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/deliveryid".$r['id']."', 2, 'editdelivery' );return false;\">Редактировать</a><br>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/delete".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a>
					</td>
				</tr>
			";
			$counter++;
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=4>
						Всего вариантов: ".$counter."
					</td>
				</tr>
		</table>
		
		<script>
			$( function() {
				$( '#newdelivery' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 600, minHeight: 500, maxWidth: 900, maxHeight: 900, width: 700, height: 400, autoOpen: false } );
				$( '#newdelivery' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
				$( '#editdelivery' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 600, minHeight: 500, maxWidth: 900, maxHeight: 900, width: 700, height: 400, autoOpen: false } );
				$( '#editdelivery' ).dialog( 'option', 'titleBack', 'url(".LOCAL_FOLDER.$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
			} );
		</script>
		
		<div id='newdelivery' title='Добавление варианта'></div>
		<div id='editdelivery' title='Редактирование варианта'></div>
		".$open;
		
		return $t;
	}
	
	function getExternal( $wt, $link )
	{
		global $query, $mysql;
		
		if( $wt == 1 ) { // Создать
			
			return $this->getExternalNewdelivery( $link );
			
		} else if( $wt == 2 ) { // 
			
			return $this->getExternalEditdelivery( $link );
			
		} 
		
		return "Unknown delivery query";
	}
	
	function getExternalNewdelivery( $link )
	{
		global $query, $mysql, $main;
		
		$inner = "
				<p>
					Название: <label class='red'>*</label><br>
					<input type=text name=\"name\" id=\"name\" value=\"\" class='text_input' />
				</p>
				<p>
					Описание: <br>
					<input type=text name=\"comment\" id=\"comment\" value=\"\" class='text_input' />
				</p>
				<p>
					Срок доставки: <label class='red'>*</label><br>
					<input type=text name=\"long\" id=\"long\" value=\"\" class='text_input' />
				</p>
				<p>
					Стоимость доставки: <label class='red'>*</label><br>
					<input type=text name=\"price\" id=\"price\" value=\"0\" class='text_input' />
				</p>
			";
			
		return "
				<h1 align=left><b>Добавление варианта доставки</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$link."/addnew\" method=POST onsubmit=\"
					if( $( '#name' ).val() == '' ) { 
						alert( 'Укажите название' ); 
						return false; 
					}
					if( $( '#long' ).val() == '' ) { 
						alert( 'Укажите срок' ); 
						return false; 
					}
					if( $( '#price' ).val() == '' ) { 
						alert( 'Укажите стоимость доставки или 0' ); 
						return false; 
					}
					$( '#process' ).attr( 'value', 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
										
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=submit value=\"Создать\" class='button_input' title='Создать вариант и закрыть окно' />
					</div>
				</form>
		";
	}
	
	function getExternalEditdelivery( $link )
	{
		global $query, $mysql, $main;
		
		$deliveryid = $query->gp( "deliveryid" );
		if( !$deliveryid )
			return "Unknown object id";
			
		$data = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."delivery` WHERE `id`=".$deliveryid );
		if( !$data )
			return "There's no any object with ID ".$deliveryid;
			
		$inner = "
				<p>
					Название: <label class='red'>*</label><br>
					<input type=text name=\"name\" id=\"name\" value=\"".$data['name']."\" class='text_input' />
				</p>
				<p>
					Описание: <br>
					<input type=text name=\"comment\" id=\"comment\" value=\"".$data['comment']."\" class='text_input' />
				</p>
				<p>
					Срок доставки: <label class='red'>*</label><br>
					<input type=text name=\"long\" id=\"long\" value=\"".$data['long']."\" class='text_input' />
				</p>
				<p>
					Стоимость доставки: <label class='red'>*</label><br>
					<input type=text name=\"price\" id=\"price\" value=\"".$data['price']."\" class='text_input' />
				</p>
			";
			
		return "
				<h1 align=left><b>Редактирование варианта доставки</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$link."/edit\" method=POST onsubmit=\"
					$( '#process' ).attr( 'value', 1 );
					
					return true;
				\" style='text-align: left;'>
					".$inner."
										
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=submit value=\"Сохранить\" class='button_input' title='Сохранить изменения' />
						<input type=button value=\"Отмена\" class='button_input' onclick=\"document.location='".LOCAL_FOLDER."admin/".$link."';\" />
					</div>
					
				<input type=hidden name=\"deliveryid\" value=\"".$deliveryid."\" />
					
				</form>
		";
	}
}

?>