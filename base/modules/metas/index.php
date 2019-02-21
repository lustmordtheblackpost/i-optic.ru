<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulemetas extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	var $currentMetas = array();
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function prepareMetas( $module = 0 )
	{
		global $mysql;
		
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."metas` WHERE `view`=1".( $module ? " AND ( `module`=0 OR `module`=".$module." )" : " AND `module`=0" ) );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			
			$tocheck = "";
			if( $r['name'] )
				$tocheck = $r['name'];
			else if( $r['http-equiv'] )
				$tocheck = $r['http-equiv'];
			else
				$tocheck = $r['property'];
				
			if( $module && !$r['module'] && isset( $this->currentMetas[$tocheck] ) ) 
				continue;			
			$this->currentMetas[$tocheck] = $r;
		}
	}
	
	function getMetas()
	{
		global $main;
		
		$t = "";
		
		foreach( $this->currentMetas as $r ) {
			$content = str_replace( "\n", " ", $main->templates->psl( $r['content'] ) );
			$content = str_replace( "[current_site_path]", ( strpos( $_SERVER['HTTP_HOST'], "http" ) === false ? "https://" : "" ).$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $content );
			$content = str_replace( "[current_site_name]", ( strpos( $_SERVER['HTTP_HOST'], "http" ) === false ? "https://" : "" ).$_SERVER['HTTP_HOST'], $content );
			$t .= "<meta".( isset( $r['name'] ) && $r['name'] ? " name=\"".$r['name']."\" id=\"".$r['name']."\"" : "" ).( isset( $r['http-equiv'] ) && $r['http-equiv'] ? " http-equiv=\"".$r['http-equiv']."\"" : "" ).( isset( $r['property'] ) && $r['property'] ? " property=\"".$r['property']."\"" : "" )." content=\"".$content."\" />
";
		}
		
		return $t;
	}
	
	function updateMeta( $name, $newValue, $type = 0 )
	{
		if( isset( $this->currentMetas[strtolower( $name )] ) ) {
			$this->currentMetas[strtolower( $name )]['content'] = $newValue;
		} else {
			$newmeta = array();
			$newmeta[!$type ? 'name' : $type] = $name;
			$newmeta['content'] = $newValue;
			$this->currentMetas[$name] = $newmeta;
		}
	}
	
	function appendToMeta( $name, $value )
	{
		if( isset( $this->currentMetas[strtolower( $name )] ) )
			$this->currentMetas[strtolower( $name )]['content'] .= $value;
	}
	
	//
	// Админские фишки
	//
	function getAdminScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;

		if( $query->gp( "createmeta" ) && $query->gp( "process" ) ) {
			
			$name = isset( $_POST['b_name'] ) ? str_replace( "'", "", str_replace( '"', "&quot;", $_POST['b_name'] ) ) : "";
			$httpequiv = isset( $_POST['http-equiv'] ) ? str_replace( "'", "", str_replace( '"', "&quot;", $_POST['http-equiv'] ) ) : "";
			$property = isset( $_POST['property'] ) ? str_replace( "'", "", str_replace( '"', "&quot;", $_POST['property'] ) ) : "";
			$content = isset( $_POST['content'] ) ? str_replace( "'", "", str_replace( '"', "&quot;", $_POST['content'] ) ) : "";
			$module = $query->gp( "module" );
			$module = $module ? $module : 0;
			
			$mysql->mu( "INSERT INTO `".$mysql->t_prefix."metas` VALUES(
					0,
					'".$name."',
					'".$httpequiv."',
					'".$property."',
					'".$content."',
					'".$module."',
					1
			);" );
			
		} else if( $query->gp( "edit" ) && $query->gp( "process" ) ) {
			
			$id = $query->gp( "edit" );
			$r = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."metas` WHERE `id`=".$id );
			
			if( $r ) {
				$name = isset( $_POST['b_name'] ) ? str_replace( "'", "", str_replace( '"', "&quot;", $_POST['b_name'] ) ) : "";
				$httpequiv = isset( $_POST['http-equiv'] ) ? str_replace( "'", "", str_replace( '"', "&quot;", $_POST['http-equiv'] ) ) : "";
				$property = isset( $_POST['property'] ) ? str_replace( "'", "", str_replace( '"', "&quot;", $_POST['property'] ) ) : "";
				$content = isset( $_POST['content'] ) ? str_replace( "'", "", str_replace( '"', "&quot;", $_POST['content'] ) ) : "";
				$module = $query->gp( "module" );
				$module = $module ? $module : 0;
			
				$mysql->mu( "UPDATE `".$mysql->t_prefix."metas` SET

				`name`='".$name."',
				`http-equiv`='".$httpequiv."',
				`property`='".$property."',
				`content`='".$content."',
				`module`='".$module."'
				
				WHERE `id`=".$id
				);
			}
			
		} else if( $query->gp( "turnmeta" ) ) {
			
			$id = $query->gp( "turnmeta" );
			$ep = $mysql->mq( "SELECT `view` FROM `".$mysql->t_prefix."metas` WHERE `id`=".$id );			
			if( $ep )
				$mysql->mu( "UPDATE `".$mysql->t_prefix."metas` SET `view`=".( $ep['view'] ? "0" : "1" )." WHERE `id`=".$id );
				
		} else if( $query->gp( "delete" ) ) {
			
			$id = $query->gp( "delete" );
			$ep = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."metas` WHERE `id`=".$id );			
			if( $ep )
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."metas` WHERE `id`=".$id );
			
		}
		
		$ar = array();
		foreach( $main->modules->modules as $v )
			$ar[$v['instance']->dbinfo['id']] = $v['instance'];
		
		$selectedElement = 0;
		$t = "
			<h1>Список META тегов</h1>
			
			<a href=\"#\" onclick=\"getWindowContent( '".$mysql->settings['local_folder']."admin/".$path."', 1, 'newmeta' );return false;\">Создать новый тег</a><br><br>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%;'>
				<tr class='list_table_header'>
					<td width=30>
						ID
					</td>
					<td width=10%>
						Название тега &lt;name>
					</td>
					<td width=10%>
						Идентификатор тега &lt;http-equiv>
					</td>
					<td width=10%>
						ИЛИ тег &lt;property>
					</td>
					<td width=40%>
						Содержание
					</td>
					<td width=20%>
						Модуль
					</td>
					<td width=110>
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
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."metas` WHERE ".$where." ORDER BY `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$t .= "
				<tr class='list_table_element'".( !$r['view'] ? " style='background-color: #fcd9d9;'" : "" ).">
					<td valign=middle>
						".$r['id']."
					</td>
					<td>
						".( $r['name'] ? "<label id='meta_name_".$r['id']."'>".$r['name']."</label>" : "-" )."
					</td>
					<td>
						".( $r['http-equiv'] ? "<label id='meta_http_".$r['id']."'>".$r['http-equiv']."</label>" : "-" )."
					</td>
					<td>
						".( $r['property'] ? "<label id='meta_property_".$r['id']."'>".$r['property']."</label>" : "-" )."
					</td>
					<td style='text-align: left;'>
						".$r['content']."
					</td>
					<td>
						".( $r['module'] && isset( $ar[$r['module']] ) ? $lang->gp( $ar[$r['module']]->dbinfo['name'] ) : "Работает везде" )."
					</td>
					<td>
						".( $r['view'] ? "Да" : "Нет" )."
					</td>
					<td nowrap>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/turnmeta".$r['id']."\">".( $r['view'] ? "Выключить" : "Включить" )."</a><br>
						<a href=\"#\" onclick=\"getWindowContent( '".LOCAL_FOLDER."admin/".$path."/metaid".$r['id']."', 2, 'meta_settings' );return false;\">Редактировать</a><br>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/delete".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a>
					</td>
				</tr>
			";
			$counter++;
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=8>
						Всего тегов: ".$counter."
					</td>
				</tr>
		</table>
		
		<script>
			$( function() {
				$( '#meta_settings' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 500, maxWidth: 700, maxHeight: 700, width: 600, height: 700, autoOpen: false } );
				$( '#meta_settings' ).dialog( 'option', 'titleBack', 'url(".$mysql->settings['local_folder'].$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
				$( '#newmeta' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 600, maxWidth: 700, maxHeight: 800, width: 600, height: 500, autoOpen: false } );
				$( '#newmeta' ).dialog( 'option', 'titleBack', 'url(".$mysql->settings['local_folder'].$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
			} );
		</script>
		
		<div id='meta_settings' title='Настройки тега'></div>
		<div id='newmeta' title='Новый тег'></div>
		
		";
		
		return $t;
	}
	
	function getExternal( $wt, $link )
	{
		global $query, $mysql;
		
		if( $wt == 1 ) { // Новый тег
			
			return $this->getExternalNewmeta( $link );
			
		} else if( $wt == 2 ) { // Редактирование тега
			
			return $this->getExternalEditmeta( $link );
				
		}
		
		return "Unknown metas query";
	}
	
	function getExternalNewmeta( $link )
	{
		global $query, $mysql, $lang, $main;
		
		$m = "";
		foreach( $main->modules->modules as $v )
			$m .= "<option value=\"".$v['instance']->dbinfo['id']."\">".$v['instance']->getName()."</option>";
		
		$inner = "
				<p>
					Название тега &lt;name>: <br>
					<input type=text name=\"b_name\" id=\"b_name\" value=\"\" class='text_input' />
				</p>
				<p>
					Идентификатор тега &lt;http-equiv> <label style='color: #256995; font-size: 90%;'>(укажите только тогда, когда нужна конвертация тега в HTTP заголовок)</label>: <br>
					<input type=text name=\"http-equiv\" id=\"http-equiv\" value=\"\" class='text_input' />
				</p>	
				<p>
					ИЛИ тег &lt;property> <label style='color: #256995; font-size: 90%;'>(используется некоторыми социальными сетями)</label>: <br>
					<input type=text name=\"property\" id=\"property\" value=\"\" class='text_input' />
				</p>
				<p>
					Содержание тега <label class='red'>*</label>: <br>
					<textarea name=\"content\" id=\"content\" class='textarea_input' style='width: 100%; height: 60px;'></textarea>
				</p>			
				<p>
					Модуль, в котором работает данный тег <label style='color: #256995; font-size: 90%;'>(если выберите модуль, то тег будет работать только в данном модуле, при этом тег будет иметь приоритет)</label>:<br>
					<select name='module' id='module'>
						<option value=0>Тег работает везде</option>
						".$m."
					</select>
				</p>	
			";
			
		return "
				<h1 align=left>Создание нового тега</h1>
				
				<form action=\"".$mysql->settings['local_folder']."admin/".$link."/createmeta\" method=POST onsubmit=\"
					if( $( '#b_name' ).val() == '' && $( '#http-equiv' ).val() == '' && $( '#property' ).val() == '' ) { 
						alert( 'Укажите название тега или его идентификатор' ); 
						return false; 
					}
					if( $( '#content' ).val() == '' ) { 
						alert( 'Укажите содержание тега' ); 
						return false; 
					}
					$( '#process' ).attr( 'value', 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=submit value=\"Создать\" class='button_input' title='Создать тег и закрыть окно' />
					</div>
				</form>
		";
	}
	
	function getExternalEditmeta( $link )
	{
		global $query, $mysql, $lang, $main;
		
		$metaid = $query->gp( "metaid" );
		$b = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."metas` WHERE `id`=".$metaid );
		if( !$b ) {
			return "Unknown meta id";
		}
		
		$m = "";
		foreach( $main->modules->modules as $v )
			$m .= "<option value=\"".$v['instance']->dbinfo['id']."\"".( $b['module'] == $v['instance']->dbinfo['id'] ? " selected" : "" ).">".$v['instance']->getName()."</option>";
		
		$inner = "
				<p>
					Название тега &lt;name>: <br>
					<input type=text name=\"b_name\" id=\"b_name\" value=\"".$b['name']."\" class='text_input' />
				</p>
				<p>
					Идентификатор тега &lt;http-equiv> <label style='color: #256995; font-size: 90%;'>(укажите только тогда, когда нужна конвертация тега в HTTP заголовок)</label>: <br>
					<input type=text name=\"http-equiv\" id=\"http-equiv\" value=\"".$b['http-equiv']."\" class='text_input' />
				</p>
				<p>
					ИЛИ тег &lt;property> <label style='color: #256995; font-size: 90%;'>(используется некоторыми социальными сетями)</label>: <br>
					<input type=text name=\"property\" id=\"property\" value=\"".$b['property']."\" class='text_input' />
				</p>
				<p>
					Содержание тега <label class='red'>*</label>: <br>
					<textarea name=\"content\" id=\"content\" class='textarea_input' style='width: 100%; height: 60px;'>".$b['content']."</textarea>
				</p>			
				<p>
					Модуль, в котором работает данный тег <label style='color: #256995; font-size: 90%;'>(если выберите модуль, то тег будет работать только в данном модуле, при этом тег будет иметь приоритет)</label>:<br>
					<select name='module' id='module'>
						<option value=0".( $b['module'] == 0 ? " selected" : "" ).">Тег работает везде</option>
						".$m."
					</select>
				</p>	
			";
			
		return "
				<h1 align=left>Редактирование тега</h1>
				
				<form action=\"".$mysql->settings['local_folder']."admin/".$link."/edit".$metaid."\" method=POST onsubmit=\"
					if( $( '#b_name' ).val() == '' && $( '#http-equiv' ).val() == '' && $( '#property' ).val() == '' ) { 
						alert( 'Укажите название тега или его идентификатор' ); 
						return false; 
					}
					if( $( '#content' ).val() == '' ) { 
						alert( 'Укажите содержание тега' ); 
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