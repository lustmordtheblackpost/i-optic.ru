<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulesearch extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	var $gl_dbase_string = "`shop`.";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function getHeaderBlock()
	{
		global $mysql, $query, $lang;
		
		$search_text = str_replace( "\\", "", str_replace( "'", "", str_replace( '"', "&quot;", urldecode( $query->gp( "search_text" ) ) ) ) );
		
		$t = "
		<div class='search'><div class='icon' id='search_icon' onclick=\"
			if( $( '#search_text' ).val() == '' || $( '#search_text' ).val() == '".$lang->gp( 19 )."' ) return false; document.location = '".$mysql->settings['local_folder'].$this->dbinfo['local']."/search_text!' + urlencode( $( '#search_text' ).val() );
		\"><img src='/images/lupa.png' /></div>
			<input type=text name='search_text' id='search_text' value='".( $search_text ? $search_text : $lang->gp( 19 ) )."' 
			onfocus=\"if( this.value == '".$lang->gp( 19 )."' ) this.value = '';\"
			onblur=\"if( this.value == '' ) this.value = '".$lang->gp( 19 )."';\"
			onkeypress=\"
				var code = processKeyPress( event );
				if( code == 13 ) $( '#search_icon' ).click();
			\" />
		</div>
		";
		
		return $t;
	}
	
	function getContent()
	{
		global $main, $mysql, $lang, $query, $utils;
		
		$main->templates->setTitle( $this->getName(), true );
		
		$search_text = str_replace( "\\", "", str_replace( "'", "", str_replace( '"', "&quot;", urldecode( $query->gp( "search_text" ) ) ) ) );
		
		$t = "
		<div class='sopli'><div class='all_lines'>
			<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>".$this->getName()."
		</div></div>
		";
		
		$catalog = $main->modules->gmi( "catalog" );
		$page = $query->gp( "page" );
		$page = is_numeric( $page ) && $page > 1 ? $page : 1;
		
		$drawPages = false;
		$items_count = 0;		
		$items = $catalog->getItemsForSearch( $search_text, $page, $drawPages, $items_count );
		
		$pages = "";
		if( false && $drawPages && $items_count > 0 ) {

			$maxonpage = $catalog->getParam( "maxonpage" );
			
			if( $items_count > $maxonpage ) {
				$pagesCount = ceil( $items_count / $maxonpage );

				$pages = "<div class='pages_block'><div class='ppginfo'>Показано с <b>".( $page <= 1 ? 1 : ( ( $page - 1 ) * $maxonpage ) )."</b> по <b>".( ( $page * $maxonpage ) - 1 )."</b> из <b>".$items_count."</b> товаров</div>";
				
				$p = array();
				for( $a = 1; $a <= $pagesCount; $a++ ) {
					$p[$a] = ( $a > 1 ? "" : "" ).( $page == $a ? 
						"<span>".$a."</span>" : 
						"<a href=\"https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'].$this->dbinfo['local']."/search_text!".urlencode( str_replace( "&quot;", '"', $search_text ) )."/page".$a."\">".$a."</a>" );
				}
			
				$tp = "";
				if( count( $p ) >= 10 ) {
					$tp = $p[$page];
					$a = $page - 1;
					while( isset( $p[$a] ) && $a > $page - 5 )
						$tp = $p[$a--].$tp;
					if( ++$a > 1 ) {
						if( $a > 2 )
							$tp = "...".$tp;
						$tp = $p[1].$tp;
					}
					$a = $page + 1;
					while( isset( $p[$a] ) && $a < $page + 5 )
						$tp .= $p[$a++];
					if( --$a < $pagesCount ) {
						if( $a < $pagesCount - 1 )
							$tp .= "...";
						$tp .= $p[$pagesCount];
					}
				} else {
					foreach( $p as $v )
						$tp .= $v;
				}
					
				$pages .= $tp."".( $page > 1 ? "[<a href=\"http://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'].$this->dbinfo['local']."/search_text!".urlencode( str_replace( "&quot;", '"', $search_text ) )."/page".( $page - 1 )."\"><<</a>]" : "" ).( $page < $pagesCount ? ( $page > 1 ? " | " : "" )."[<a href=\"http://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'].$this->dbinfo['local']."/search_text!".urlencode( str_replace( "&quot;", '"', $search_text ) )."/page".( $page + 1 )."\">>></a>]" : "" )."</div>";
			}
		}
		
		$vendors = $main->listings->getListingElementsArray( 7, 0, false, '', true );
		$forms = $main->listings->getListingElementsArray( 2, 0, false, '', true );
		$mats = $main->listings->getListingElementsArray( 5, 0, false, '', true );
		$ColorsList = $main->listings->getListingElementsArraySpec( 3, "`order` DESC, `id` ASC", "", 0, true );
		
		$itemsText = "";
		$c = 1;
		foreach( $items as $id => $data ) {
			$id = $data['id'];
			$forma = $this->getElementByData( $data['properties'], "prop_id", 3 );
			$color = $this->getElementByData( $data['properties'], "prop_id", 1 );
			$data['colors'] = $catalog->getColorArrayOfGood( $id );
			$colors = is_numeric( $color['value'] ) && $color['value'] ? $lang->gp( $ColorsList[$color['value']]['value'], true ) : $color['value'];
			foreach( $data['colors'] as $rr ) {
				$color = $rr['color'];
				if( $color ) {
					$colors .= ( $colors ? ", " : "" ).$lang->gp( $ColorsList[$color]['value'], true );
				}
			}
			$type = $this->getElementByData( $data['properties'], "prop_id", 2 );
			$ct = 0;
			foreach ($data['properties'] as $key) {
				if($key['prop_id'] == 2){
					if($key['value'] == 3){$ct = 3; break;}
					if($key['value'] == 4){
						$ct = 4;
					}
				}
			}
			if($ct == 0){ $ct = $type['value'];}

			$mat = $this->getElementByData( $data['properties'], "prop_id", 5 );
			$link = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog".$ct."/show".$data['id']."/".strtolower( $utils->translitIt( $data['name'] ) )."_".strtolower( $utils->translitIt( $data['article'] ) ).".html";
                        $tovarImage = $this->getElementByData( $data['properties'], "prop_id", 14 );
			$itemsText .= "
			<div class='resultItem'>
				<div class='number'>".$c.".</div>
				<div class='data' style='width: 90%;'>
					<h4 class='titleOfTheGood'><a href='".$link."'>".$data['name']."</a></h4>
					<div class='comments'>
                                            ".( $tovarImage ? "<img style='display: inline-block; width: 30%;' src='".$mysql->settings['local_folder']."files/upload/goods/".$tovarImage['value']."' />" : "" )."
						".( $data['vendor'] ? "<div>Бренд: <span>".$lang->gp( $vendors[$data['vendor']]['value'], true )."</span></div>" : "" )."
						<div>Размер: <span>маленькие</span></div>
						".( $forma ? "<div>Форма: <span>".$lang->gp( $forms[$forma['value']]['value'], true )."</span></div>" : "" )."
						".( $colors ? "<div>Цвет: <span>".$colors."</span></div>" : "" )."
						".( $mat ? "<div>Материал: <span>".$lang->gp( $mats[$mat['value']]['value'], true )."</span></div>" : "" )."
					</div>
				</div>
				<div class='clear'></div>
			</div>
			";
			$c++;
		}
		
		return $t."
		<div class='catalog catalog_nomargin catalog_marginbottom'>
			<div class='all_lines center'>
				<div class='searchBlock'>".( $items_count ? "<h3>Найдено <b>".$items_count."</b> ".$utils->getRightCountString( $value, "результатов", "результат", "результата" )." <b>".$search_text."</b></h3>" : "<h3>По заданному запросу ничего не найдено</h3>" ).$itemsText."</div>
			</div>
		</div>
		";
	}
}

?>