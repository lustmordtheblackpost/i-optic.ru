<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulebrands extends RootModule
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
		
		$main->templates->setTitle( $this->getName(), true );
                
                $brands = "";
                $brandsList = $main->listings->getListingElementsArray( 7, 0, false, '', true );
                foreach( $brandsList as $id => $v ) {
                    $brands .= "<div class='one' style='line-height: 1.6; margin-left: 10px;'><a href='/catalog/?vendor=".$id."&showallcategories=1'>".$lang->gp( $v['value'], true )."</a></div>";
                }
		
		$t = "
		<div class='sopli'><div class='all_lines'>
			<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>".$this->getName()."
		</div></div>
		
		<div class='catalog catalog_nomargin catalog_marginbottom'>
			<div class='all_lines'>
				".$brands."
			</div>
		</div>
		";
		
		return $t;
	}
}

?>