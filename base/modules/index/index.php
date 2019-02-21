<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class moduleindex extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function getContent( $noname = false )
	{
		global $lang, $main, $mysql;
		
		return 
			$main->modules->gmi( "actions" )->getIndexBanners().
			"<div class='all_lines short_block'><div class='block_title'><span>".$lang->gp( 18, true )."</span></div>".$main->modules->gmi( "catalog" )->getOpravaItems()."<div class='clear'></div></div>".
			$main->modules->gmi( "actions" )->getBannersBlock().
			
			$main->modules->gmi( "actions" )->getSignBlock().
			"<div class='all_lines short_block'><div class='block_title'><span>".$lang->gp( 19, true )."</span></div>".$main->modules->gmi( "catalog" )->getPopularItems()."<div class='clear'></div></div>".
			"<div class='all_lines short_block'><div class='block_title'><span>".$lang->gp( 20, true )."</span></div>".$main->modules->gmi( "catalog" )->getRecommendItems()."<div class='clear'></div></div>
			<div class='infographics'>
				<div class='all_lines'>
					<h1>Как мы работаем</h1>
					<img src='/images/sample_infographics.png' alt='infographics' />
					<div class='block'><img src='/images/si1.png' /><div class='in'>
						<h3>".$lang->gp( 56 )."</h3>
						<p>".$lang->gp( 57 )."</p>
					</div></div>
					<div class='block'><img src='/images/si2.png' /><div class='in'>
						<h3>".$lang->gp( 58 )."</h3>
						<p>".$lang->gp( 60 )."</p>
					</div></div>
					<div class='block'><img src='/images/si3.png' /><div class='in'>
						<h3>".$lang->gp( 61 )."</h3>
						<p>".$lang->gp( 64 )."</p>
					</div></div>
					<div class='block'><img src='/images/si4.png' /><div class='in'>
						<h3>".$lang->gp( 68 )."</h3>
						<p>".$lang->gp( 70 )."</p>
					</div></div>
					<div class='block'><img src='/images/si5.png' /><div class='in'>
						<h3>".$lang->gp( 71 )."</h3>
						<p>".$lang->gp( 72 )."</p>
					</div></div>
					<div class='clear'></div>
				</div>
			</div>
			<div class='all_lines'>
				<div class='content'>
					".$lang->gp( $this->getParam( 'context_data_' ) )."
				</div>
			</div>
			";
	}
}

?>