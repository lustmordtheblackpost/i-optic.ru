<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulesitemap extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function getSitemap()
	{
		global $mysql, $main;
		
		$t = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><urlset xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">
<url>
	<loc>https://".$_SERVER['HTTP_HOST']."/</loc>
	<lastmod>".date( 'Y-m-d\Th:i:s' )."+00:00</lastmod> 
	<priority>0.8</priority>
	<changefreq>weekly</changefreq>
</url>";
		
		//
		// Сначала каталоги товаров
		//
		$cat = $main->listings->getListingElementsArrayAll( 22, '', true );
		foreach( $cat as $v ) {
			$t .= "
<url>
	<loc>https://".$_SERVER['HTTP_HOST']."/catalog".$v['id']."</loc>
	<lastmod>".date( 'Y-m-d\Th:i:s' )."+00:00</lastmod> 
	<priority>0.8</priority>
	<changefreq>weekly</changefreq>
</url>";	
		}
		
		//
		// Теперь по модулям
		//
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."modules` WHERE 1 ORDER BY `order` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			if( $r['local'] == 'catalog' || $r['local'] == 'catalog_admin' || $r['local'] == 'metas' || $r['local'] == 'mail_agent' || $r['local'] == 'index' || $r['local'] == 'search' || $r['local'] == 'basket' || $r['local'] == 'order' || $r['local'] == 'profile' || $r['local'] == 'forgot' || $r['local'] == 'same_goods' || $r['local'] == 'yml_export' || $r['local'] == 'sitemap' || $r['local'] == 'feedback' ) 
				continue;
			$t .= "
<url>
	<loc>https://".$_SERVER['HTTP_HOST']."/".$r['local']."</loc>
	<lastmod>".date( 'Y-m-d\Th:i:s' )."+00:00</lastmod> 
	<priority>0.5</priority>
	<changefreq>monthly</changefreq>
</url>";
			if( $r['local'] == 'usefultips' ) {
				$aa = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."issues` WHERE `view`=1 ORDER BY `order` DESC" );
				while( $rr = @mysql_fetch_assoc( $aa ) ) {
					$t .= "
<url>
	<loc>https://".$_SERVER['HTTP_HOST']."/".$r['local']."/".$rr['link']."</loc>
	<lastmod>".date( 'Y-m-d\Th:i:s' )."+00:00</lastmod> 
	<priority>0.3</priority>
	<changefreq>monthly</changefreq>
</url>";
				}
			}
		}
		
		//
		// Теперь по товарам
		//
		$a = $mysql->mqm( "SELECT `id`,`r`,`sub_r` FROM `shop`.`".$mysql->t_prefix."tovar` WHERE `view`=1" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$t .= "
<url>
	<loc>https://".$_SERVER['HTTP_HOST']."/catalog".( $r['sub_r'] ? $r['sub_r'] : $r['r'] )."/show".$r['id']."</loc>
	<lastmod>".date( 'Y-m-d\Th:i:s' )."+00:00</lastmod> 
	<priority>1.0</priority>
	<changefreq>daily</changefreq>
</url>";
		}
		
		$t .= "</urlset>";
		
		@file_put_contents( ROOT_PATH."sitemap.xml", $t );
		
		return $t;
	}
}

?>