<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class modulehelp extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulehelp extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function getMainmenuLink()
	{
		global $main, $mysql;
		
		return "<label class='first_item'><a href=\"".$mysql->settings['local_folder'].$this->dbinfo['local']."\">".$this->getName()."</a></label>";
	}
	
	function getContent( $noname = false )
	{
		global $lang, $main, $mysql, $query;
		
		$main->templates->setTitle( $this->getName(), true );
		
		foreach( $query->newGET as $qValue => $v ) {
			$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."help` WHERE `link`='".str_replace( ".html", "", strtolower( $qValue ) )."' AND `view`=1" );
			if( $r )
				$selected = $r;
		}
		
		$subs = '';
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."help` WHERE `view`=1 ORDER BY `order` DESC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$subs .= "
				<a href=\"".$mysql->settings['local_folder'].$this->dbinfo['local']."/".$r['link'].".html\"".( $selected && $selected['id'] == $r['id'] ? " class='selected'" : "" ).">".$r['title']."</a>
			";
		}
		
		$t = "
		<div class='sopli'><div class='all_lines'>
			<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span><a href='".$mysql->settings['local_folder'].$this->dbinfo['local']."'>".$this->getName()."</a>".( $selected ? "<span>></span>".$selected['title'] : "" )."
		</div></div>
		<div class='catalog catalog_nomargin'>
			<div class='all_lines'>
				<div class='filter subs'>
					".$subs."
				</div>
				<div class='main_data'><div class='md_inner'>
					".( $selected ? $selected['text'] : '' )."
				</div></div>
				<div class='clear'></div>
			</div>
		</div>
		
		<script>
			$(window).load(function()
			{
				$( '.catalog .main_data' ).width( $( '.catalog .all_lines' ).width() - $( '.catalog .filter' ).width() );
			});
			
			$(window).resize(function()
			{
				$( '.catalog .main_data' ).width( $( '.catalog .all_lines' ).width() - $( '.catalog .filter' ).width() );
			});
		</script>
		";
		
		return $t;
	}
	
	function showhelp( $r )
	{
		global $lang, $main, $mysql, $query;
		
		$main->templates->setTitle( strip_tags( $r['title'] ), true );
		
		$main->modules->gmi( 'metas' )->updateMeta( 'og:url', "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
		$main->modules->gmi( 'metas' )->updateMeta( 'og:title', $main->templates->title );
		$main->modules->gmi( 'metas' )->updateMeta( 'og:description', strip_tags( $r['title'] ) );
		
		return "<h1 class='pageTitle fullyOwned'>".$main->modules->gmi( "social" )->getShareWidget( "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."?".$this->dbinfo['local']."=".$r['link'], "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $main->templates->title ).$this->getName()."</h1><a href=\"".$mysql->settings['local_folder'].$this->dbinfo['local']."\">".$lang->gp( 188, true )."</a><h2>".$r['title']."</h2>".$r['text'];
	}
	
	//
	// Далее администраторская область
	//
	
	function getAdminScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;
		
		$this->max_width = $this->getParam( 'max-width' );

		if( $query->gp( "createhelp" ) && $query->gp( "process" ) ) {
			
			$title = isset( $_POST['title'] ) && $_POST['title'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['title'] )  ) : '';
			$text = isset( $_POST['text'] ) && $_POST['text'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['text'] )  ) : '';
			$order = $query->gp( "order" );
			
			$mysql->mu( "INSERT INTO `".$mysql->t_prefix."help` VALUES(
				0,
				'".$title."',
				'".strtolower( $utils->translitIt( $title ) )."',
				'".$text."',
				1,
				'".$order."'
			);" );
			
			$r = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."help` WHERE 1 ORDER BY `id` DESC" );
			
			if( $r ) {
			
				if( !@opendir( ROOT_PATH."files/upload/help/".$r['id'] ) ) {
					@mkdir( ROOT_PATH."files/upload/help/".$r['id'] );
					@chmod( ROOT_PATH."files/upload/help/".$r['id'], 0777 );
					@file_put_contents( ROOT_PATH."files/upload/help/".$r['id']."/index.html", "Nothing here" );
				}
				if( !@opendir( ROOT_PATH."files/upload/help/".$r['id']."_files" ) ) {
					@mkdir( ROOT_PATH."files/upload/help/".$r['id']."_files" );
					@chmod( ROOT_PATH."files/upload/help/".$r['id']."_files", 0777 );
					@file_put_contents( ROOT_PATH."files/upload/help/".$r['id']."_files/index.html", "Nothing here" );
				}
				
			}
			
			return "<script>document.location = '".$mysql->settings['local_folder']."admin/".$path."';</script>";
			
		} else if( $query->gp( "edit" ) && $query->gp( "process" ) ) {
			
			$id = $query->gp( "edit" );
			
			$title = isset( $_POST['title'] ) && $_POST['title'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['title'] )  ) : '';
			$text = isset( $_POST['text'] ) && $_POST['text'] ? str_replace( "&nbsp;", " ", str_replace( "'", "", $_POST['text'] )  ) : '';
			$order = $query->gp( "order" );
			
			$mysql->mu( "UPDATE `".$mysql->t_prefix."help` SET

				`title`='".$title."',
				`link`='".strtolower( $utils->translitIt( $title ) )."',
				`text`='".$text."',
				`order`='".$order."'
				
			WHERE `id`=".$id );
			
			$query->setProperty( "edit", 0 );
			
		} else if( $query->gp( "turnhelp" ) ) {
			
			$id = $query->gp( "turnhelp" );
			$ep = $mysql->mq( "SELECT `view` FROM `".$mysql->t_prefix."help` WHERE `id`=".$id );			
			if( $ep )
				$mysql->mu( "UPDATE `".$mysql->t_prefix."help` SET `view`=".( $ep['view'] ? "0" : "1" )." WHERE `id`=".$id );
				
		} else if( $query->gp( "delete" ) ) {
			
			$id = $query->gp( "delete" );
			$ep = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."help` WHERE `id`=".$id );			
			if( $ep ) {
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."help` WHERE `id`=".$id );
				$dh = @opendir( "files/upload/help/".$id."/" );
				while( $dh && ( $file = @readdir( $dh ) ) !== false )
					if( $file !== '.' && $file !== '..' )
	          			@unlink( "files/upload/help/".$id."/".$file );
				@rmdir( ROOT_PATH."files/upload/help/".$id );
				$dh = @opendir( "files/upload/help/".$id."_files/" );
				while( $dh && ( $file = @readdir( $dh ) ) !== false )
					if( $file !== '.' && $file !== '..' )
	          			@unlink( "files/upload/help/".$id."_files/".$file );
				@rmdir( ROOT_PATH."files/upload/help/".$id."_files" );
			}
			
		}
		
		if( $query->gp( "createhelp" ) ) {
			
			return $this->getExternalNewhelp( $path );
			
		} else if( $query->gp( "edit" ) ) {
			
			return $this->getExternalEdithelp( $path, $query->gp( "edit" ) );
			
		}
		
		$selectedElement = 1;
		$t = "
			<h1>Список статей для раздела «Помощь</h1>
			
			<a href=\"".$mysql->settings['local_folder']."admin/".$path."/createhelp\">Создать новую статью</a><br><br>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%;'>
				<tr class='list_table_header'>
					<td width=50>
						ID
					</td>
					<td width=80% style='text-align: left;'>
						Название статьи
					</td>
					<td width=70>
						Порядок
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
			
		$counter = 0;
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."help` WHERE ".$where." ORDER BY `order` DESC, `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$t .= "
				<tr class='list_table_element'".( !$r['view'] ? " style='background-color: #fcd9d9;'" : "" ).">
					<td valign=middle>
						".$r['id']."
					</td>
					<td style='text-align: left;' valign=middle>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/edit".$r['id']."\"><strong>".$r['title']."</strong></a><br>
					</td>
					<td>
						".$r['order']."
					</td>
					<td>
						".( $r['view'] ? "Да" : "Нет" )."
					</td>
					<td nowrap>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/turnhelp".$r['id']."\">".( $r['view'] ? "Выключить" : "Включить" )."</a><br>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/edit".$r['id']."\">Редактировать</a><br>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/delete".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a>
					</td>
				</tr>
			";
			$counter++;
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=5>
						Всего статей: ".$counter."
					</td>
				</tr>
		</table>
		";
		
		return $t;
	}
	
	function getExternalNewhelp( $link )
	{
		global $query, $mysql, $lang, $main, $utils;
		
		$inner = "
				<p>
					Название статьи: <label class='red'>*</label><br>
					<input type=text name=\"title\" id=\"title\" value=\"\" class='text_input' />
				</p>
				<p>
					Порядок вывода: (чем больше число, тем выше в списке при стандартной сортировке)<br>
					<input type=text name=\"order\" id=\"order\" value=\"500\" class='text_input' />
				</p>
				<p>
					Текст статьи:<br>
					<textarea name=\"text\" id=\"text\" rows=25 class='textarea_input'></textarea>
				</p>
				
				<script type=\"text/javascript\" src=\"".$mysql->settings['local_folder'].$utils->javascript_files_path."tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>
				<script type=\"text/javascript\">
	tinyMCE.init({
		
		mode : \"exact\",
		elements : \"text\",
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
				<h1 align=left>Создание новой статьи</h1>
				
				<form help=\"".$mysql->settings['local_folder']."admin/".$link."\" method=POST id='creatings' style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=hidden name=\"createhelp\" id=\"createhelp\" value=\"0\" />						
						<input type=button value=\"Создать\" class='button_input' onclick=\"
							if( $( '#title' ).attr( 'value' ) == '' ) { 
								alert( 'Укажите название статьи' ); 
								return false; 
							}
							$( '#process' ).attr( 'value', 1 );
							$( '#createhelp' ).attr( 'value', 1 );
							$( '#creatings' ).submit();
						\" />&nbsp;&nbsp;
						<input type=button value=\"Отменить\" class='button_input' onclick=\"$( '#creatings' ).submit();\" />
					</div>
				</form>
		";
	}
	
	function getExternalEdithelp( $link, $helpid )
	{
		global $query, $mysql, $lang, $main, $utils;
		
		$data = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."help` WHERE `id`=".$helpid );
		if( !$data ) {
			return "Unknown help id";
		}
		
		$inner = "
				<p>
					Название статьи: <label class='red'>*</label><br>
					<input type=text name=\"title\" id=\"title\" value=\"".$data['title']."\" class='text_input' />
				</p>
				<p>
					Порядок вывода: (чем больше число, тем выше в списке при стандартной сортировке)<br>
					<input type=text name=\"order\" id=\"order\" value=\"".$data['order']."\" class='text_input' />
				</p>
				<p>
					Текст статьи:<br>
					<textarea name=\"text\" id=\"text\" rows=30 class='textarea_input'>".$data['text']."</textarea>
				</p>
				
				<script type=\"text/javascript\" src=\"".$mysql->settings['local_folder'].$utils->javascript_files_path."tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>
				<script type=\"text/javascript\">
	tinyMCE.init({
		
		mode : \"exact\",
		elements : \"text\",
		language : \"ru\",
		theme : \"advanced\",
		plugins : \"pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave,smimage,smexplorer\",

		
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
        
        plugin_smimage_directory : '/files/upload/help/".$helpid."',
        plugin_smexplorer_directory : '/files/upload/help/".$helpid."_files',
		file_browser_callback : 'SMPlugins',
        
		template_replace_values : {
			username : \"Some User\",
			staffid : \"991234\"
		}
	});
</script>
			";
			
		return "
				<h1 align=left>Редактирование статьи «".$data['title']."»</h1>
				
				<form help=\"".$mysql->settings['local_folder']."admin/".$link."\" method=POST id='creatings' style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=hidden name=\"edit\" id=\"edit\" value=\"0\" />
						<input type=button value=\"Сохранить\" class='button_input' onclick=\"
							if( $( '#title' ).attr( 'value' ) == '' ) { 
								alert( 'Укажите название статьи' ); 
								return false; 
							}
							$( '#process' ).attr( 'value', 1 );
							$( '#edit' ).attr( 'value', ".$helpid." );
							$( '#creatings' ).submit();
						\" />&nbsp;&nbsp;
						<input type=button value=\"Отменить\" class='button_input' onclick=\"$( '#creatings' ).submit();\" />
					</div>
				</form>
		";
	}
}

?>