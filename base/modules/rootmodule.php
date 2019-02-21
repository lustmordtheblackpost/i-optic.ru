<?php

class RootModule
{
	var $dbinfo = null;
	
	var $localPath;
	
	function getRootFilePath( $file )
	{
		global $mysql;
		
		$this->localPath = $mysql->settings['local_folder'];
		
		return join( strstr( $file, "/" ) ? "/" : "\\", array_slice( preg_split( "/[\/\\\]+/", $file ), 0, -1 ) ).( strstr( $file, "/" ) ? "/" : "\\" );
	}
	
	// Инициализация плагина
	function init( $dbinfo ) { }
	
	// Проверка, включен ли модуль (указана ли ссылка)
	function isSelected( $local = '' )
	{
		global $query;
		
		return $query->gp( !$local ? $this->dbinfo['local'] : $local ) ? true : false;
	}
	
	// Получение локального параметра
	function getParam( $paramName )
	{
		global $main;
		
		return $main->modules->getModuleParam( $this->dbinfo['id'], $paramName );
	}
	
	// Установка локального параметра
	function setParam( $paramName, $newValue )
	{
		global $main;
		
		return $main->modules->setModuleParam( $this->dbinfo['id'], $paramName, $newValue );
	}
	
	// Получение локальных параметров
	function getParams()
	{
		global $main;
		
		return $main->modules->getModuleParams( $this->dbinfo['id'] );
	}
	
	// Получение имени модуля в переводе
	function getName()
	{
		global $lang;
		
		return $lang->getPh( $this->dbinfo['name'] );
	}
	
	// Функция формирования заголовка страницы
	function getTitleChangeBlock() { }
	
	// Защищенное получение CSS файла
	function getCSS() { }
	
	// Запуск функции для вывода JS данных в событие onload тега body
	function getJSToBody() { }
	
	// Запуск функции для установки заголовков плагина (если нужно)
	function processHeaderBlock() { }
	
	// Запуск функции для установки кукисов в рамках плагина (если нужно)
	function processSetCookiesBlock() { }
	
	function getWidget() { }
	
	// Запуск функции для вывода блока в теге <HEAD> (если нужно)
	function processHeadBlock() { return ""; }
	
	// Запуск функции для вывода информации вверху страниц ПОД логотипом, баннером и блоком авторизации (если нужно)
	function processHTMLHeaderBlock() { }
	
	// Запуск функции для вывода информации внизу страниц (если нужно)
	function processHTMLFooterBlock() { }
	
	// Запуск функции для получения блока, который нужно вставить в основной шаблон в определенное место (если нужно)
	function getPluginBlock() { }
	
	// Запуск функции для получения ссылок в нижней части экрана
	function getFooterLink( $add_to_right = "" ) { }
	
	// Запуск функции для получения ссылок под областью авторизации
	function getAuthLink( $add_to_right = "" ) { }
	
	// Запуск функции выдачи элементов главного меню в тех модулях, где это нужно делать
	function getMainmenuLink()
	{
		global $main, $mysql;
		
		return "<a href=\"".$mysql->settings['local_folder'].$this->dbinfo['local']."\">".$this->getName()."</a>";
	}
	
	function getServicemenuLink()
	{
		global $main, $mysql;
		
		return "<div><a href=\"".$mysql->settings['local_folder'].$this->dbinfo['local']."\">".$this->getName()."</a></div>";
	}
	
	function isinSmallMenu() { return false; }
	
	function getMobileMenuLink()
	{
		global $main, $mysql;
		
		return "<a class='mmenu' href=\"".$mysql->settings['local_folder'].$this->dbinfo['local']."\">".$this->getName()."</a>";
	}
	
	// Запуск функции выдачи подменю. Акутально только для профайла и его функционала
	function getSubmenuLink() { }

	// Запуск функции выдачи основной рабочей информации в рабочую область. Может быть только один модуль в один момент времени, выдающий эту информацию.
	function getContent( $noname = false )
	{
		global $lang, $main, $mysql;
		
		$main->templates->setTitle( $this->getName(), true );
		
		$t = "
		<div class='sopli'><div class='all_lines'>
			<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>".$this->getName()."
		</div></div>
		
		<div class='catalog catalog_nomargin catalog_marginbottom'>
			<div class='all_lines'>
				".$lang->gp( $this->getParam( 'context_data_' ) )."
			</div>
		</div>
		";
		
		return $t;
	}
	
	// Запуск функции у модулей, чья функция скрытая, либо частично скрытая. К примеру модуль выбора языка, который вызывается из модуля поиска для удобства расположения
	function getHiddenContent() { }
	
	// Запуск функции у модулей, обвечающих за обработку внешних запросов с AJAX
	function parseExternalRequest() { }
	
	// Запуск функций, который выдают ифнормацию на Главную сраницу
	function getIndexInfo() { }
	
	// Если ли выдача на главную. Нужен для ускорения процедуры формирования Главной Страницы. По умолчанию НЕТ
	function isThereIndexPage() { return false; }
	
	// Участвует ли в формировании меню на главной
	function isInMainMenu() { return false; }
	
	// Получить "сопли"
	function getSopli() {  }
	
	function getSearchBlock() { }
	
	function getElementValueByName( $from, $name )
	{
		foreach( $from as $v )
			if( $v['name'] == $name )
				return $v['value'];
	}
	
	function getElementIdByName( $from, $name )
	{
		foreach( $from as $v )
			if( $v['name'] == $name )
				return $v['id'];
	}
	
	function getElementById( $from, $id )
	{
		foreach( $from as $v )
			if( $v['id'] == $id )
				return $v;
	}
	
	function getElementByData( $from, $data, $value )
	{
		foreach( $from as $v )
			if( isset( $v[$data] ) && $v[$data] == $value )
				return $v;
				
		return null;
	}
	
	function searchArrayForValue( $ar, $value )
	{
		foreach( $ar as $array_value )
			if( $array_value == $value )
				return true;
				
		return false;
	}
}

?>