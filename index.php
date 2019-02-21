<?php

error_reporting( E_ALL ^ E_NOTICE );

@set_time_limit( 0 );
@ini_set( "memory_limit", "-1" ); //50M
@ini_set( "post_max_size", "10M" );
@ini_set( "upload_max_filesize", "10M" );

define( "in_ochki", 1 );
define( "ROOT_PATH", join( strstr( __FILE__, "/" ) ? "/" : "\\", array_slice( preg_split( "/[\/\\\]+/", __FILE__ ), 0, -1 ) ).( strstr( __FILE__, "/" ) ? "/" : "\\" ) );

//
// Подключение файла для обработки запросов Rewrite
//
if( !file_exists( ROOT_PATH."base/query.php" ) ) {
	die( "ERROR" );
}
@include_once( ROOT_PATH."base/query.php" );
$query = new Query();
$query->init();
$query->checkSimpleReq(); // Проверка на "легкие" запросы, которые не требуют загрузки дополнительных файлов (пример: текущее время, пути и тд.)
if( $query->gp( "_" ) || $query->gp( "favicon.ico" ) )
	exit;

//
// Подключение файла с работой с базой данных MySQL
//
if( !file_exists( ROOT_PATH."base/mysql.php" ) ) {
	die( "ERROR" );
}
@include_once( ROOT_PATH."base/mysql.php" );
$mysql = new MySQL();
if( !$mysql->init() ) die( "Database error" );

//
// Подключение файла с основными функциями системы
//
if( !file_exists( ROOT_PATH."base/utils.php" ) ) {
	die( "ERROR" );
}
@include_once( ROOT_PATH."base/utils.php" );
$utils = new Utils();

//
// Подключение файла для обработки языковых функций
//
if( !file_exists( ROOT_PATH."base/lang.php" ) ) {
	die( "ERROR" );
}
@include_once( ROOT_PATH."base/lang.php" );
$lang = new Lang();
$lang->init();
$lang->checkSimpleReq(); // Проверка на "легкие" запросы, которые не требуют загрузки дополнительных файлов (пример: получение фразы на выбранном языке)

if( !file_exists( ROOT_PATH."base/ochki.php" ) ) {
	die( "ERROR" );
}
@include_once( ROOT_PATH."base/ochki.php" );
$main = new Ochki();
$main->checkSimpleReq(); // Проверка на "легкие" запросы, когда нужны модули (работа с корзиной к примеру)
$main->init();

if( $query->gp( "yandex_get_yml" ) ) {
	$yml = $main->modules->gmi( "yml_export" );
	echo $yml->process();
	exit;
}

if( $query->gp( "current_sitemap.xml" ) ) {
	$sitemap = $main->modules->gmi( "sitemap" );
	echo $sitemap->getSitemap();
	exit;
}

if( $query->gp( "yandex_api" ) || $query->gp( "yandex_api_test" ) ) {
	$yml = $main->modules->gmi( "yml_export" );
	echo $yml->processAPI( $query->gp( "yandex_api_test" ) ? true : false );
	exit;
}

if( $query->gp( "yandex_payments" ) ) {
	$main->modules->gmi( "yandex_payments" )->processIncoming();
	exit;
}

if( $query->gp( "ext" ) ) {
	if( !file_exists( ROOT_PATH."base/external.php" ) ) {
		die( "ERROR" );
	}
	@include_once( ROOT_PATH."base/external.php" );
	$ext = new ExternalQueries();
	echo $ext->run();
	exit;
}

if( $query->gp( "admin" ) ) {
	if( !file_exists( ROOT_PATH."base/admin.php" ) ) {
		die( "ERROR" );
	}
	@include_once( ROOT_PATH."base/admin.php" );
	$admin = new Admin();
	if( !$admin->init() ) die( "Admin initialization error" );
	define( "LOCAL_FOLDER", $admin->settings['local_folder']['value'] );
	$admin->start();
	exit;
}

// if( !isset( $_SERVER['HTTPS'] ) ) {
// 	header( 'location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
// 	exit;
// }

$main->setCookies();
$main->putHeaders();

echo $main->templates->changeMetas( $main->templates->changeTitle( $utils->cleanTabs( $main->printHEAD().$main->printHTMLHeader().$main->printHTMLContext().$main->printHTMLFooter() ) ) );

?>
