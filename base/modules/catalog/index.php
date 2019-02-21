<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulecatalog extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	var $gl_dbase_string = "`shop`.";
	var $options = array();
	var $drawPages = false;
	var $items_count = 0;
	var $showVendors = array();
	var $items = null;
	var $filteredMinPrice = 0;
	var $filteredMaxPrice = 0;
	var $forthyfour = false;
	var $tags = '';
	
	var $goodtoshow = false;
	
	var $imageBackSize = "90%";
	var $NoimageBackSize = "80%";
	var $imageBackPos = "50% 30px";
	var $NoimageBackPos = "50% 30px";
	var $imageBackSize_face = "93%";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function getMobileMenuLink()
	{
		global $main, $mysql, $lang, $utils;
                
                $a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."listings_elements` WHERE `listing`=1 ORDER BY `order` ASC, `id` ASC" );
		$tt = "";
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$tt .= "<a href=\"".$this->localPath."catalog".$r['id']."/".strtolower( $utils->translitIt( $lang->gp( $r['value'], true ) ) ).".html\" class='mmenu".( !$showallcategories && $catalog == $r['id'] ? " selected" : "" )."'>".$lang->gp( $r['value'], true )."</a> ";
		}
		
		return "<div>Оправы</div>".$tt;
	}
        
        function getFloatBlock() 
	{
		global $mysql, $query, $lang, $main, $utils;
		
		$t = "
		<div class='fixed_basket'>
			<div class='window'>
				<div class='inner'>
                                        <h3>Товар добавлен в корзину</h3>
					<div class='bl'><div class='button' onclick=\"hideFixedbasket();\">Продолжить покупку</div></div>
                                        <div class='bl'><div class='button' onclick=\"urlmove( '/order' );\">Оформить заказ</div></div>
				</div>
			</div>
		</div>
		
		<script>
			$(window).resize(function()
			{
				var he = $( '.fixed_basket' ).find( '.window' ).height();
				if( he > 0 )
					$( '.fixed_basket' ).find( '.window' ).css( 'margin-top', he / 2 * -1 );
			});
			
			var basketappierSpeed = 0;
			
			function showFixedbasket( speed, ochkiid )
			{
				basketappierSpeed = speed;
				$( '.fixed_basket' ).css( 'opacity', 0 ).show();
				var he = $( '.fixed_basket' ).find( '.window' ).height();
				$( '.fixed_basket' ).find( '.window' ).css( 'margin-top', he / 2 * -1 );
				$( '.fixed_basket' ).animate( { opacity: 1 }, speed );
                                addToBasket( ochkiid );
			}
			
			function hideFixedbasket( after )
			{
				$( '.fixed_basket' ).animate( { opacity: 0 }, basketappierSpeed, function() { $( '.fixed_basket' ).hide(); if( after != undefined ) after(); } );
			}
		</script>
		";
		
		return $t;
	}
	
	function getOpravaItems()
	{
		global $mysql, $query, $main, $lang;
		
		$op = $main->listings->getListingElementsArray( 2, 0, false, '', true );
		$t = "";
		foreach( $op as $k => $v ) {
			$t .= "
			<div class='item oprava_item' style=\"background: url(/files/upload/listings/".$v['image'].") no-repeat; background-size: contain; background-position: 50% 10px;\" onclick=\"urlmove('".$mysql->settings['local_folder']."catalog1/mujskie.html?forma=".$k."&showallcategories=1');\">
				<h3>".$lang->gp( $v['value'], true )."</h3>
			</div>";
		}
		
		return $t;
	}
	
	function getPopularItems()
	{
		global $mysql, $query, $main, $lang, $utils;
		
		$ColorsList = $main->listings->getListingElementsArraySpec( 3, "`order` DESC, `id` ASC", "", 0, true );
		
		$fitting = $main->modules->gmi( "fitting" );
		
		$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `view`=1 AND `root`=0 ORDER BY `popular` DESC, `order` ASC, `id` ASC limit 4" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$props = $main->properties->getPropertiesOfGood( $r['id'] );
			$tovarImage = $this->getElementByData( $props, "prop_id", 14 );
			
			$link = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog/show".$r['id']."/".strtolower( $utils->translitIt( $r['name'] ) )."_".strtolower( $utils->translitIt( $r['article'] ) ).".html";
            foreach ($props as $value){
                if($value['prop_id'] == 2 and $value['value'] == 4){
                    $sun = true;
                }
            }
			$colors = "";
			$i = 1;
			$p = $this->getElementByData( $props, "prop_id", 1 );
			if( $p && $p['value'] ) {
				$price = $utils->digitsToRazryadi( $r['price'] )." руб.";
				$titleName = "Артикул ".$r['article'].", ".( is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'] );
				$colors .= "
				<div class='one selected' onclick=\"
						$( this ).parent().find( '.one' ).each(function() { $( this ).removeClass( 'selected' ); });
						$( this ).addClass( 'selected' );
						var ar = new Array();
					".( $tovarImage ? "
						ar[0] = '".$mysql->settings['local_folder']."files/upload/goods/thumbs/".$tovarImage['value']."';
						ar[1] = '".$this->imageBackSize."';
						ar[2] = '".$this->imageBackPos."';
					" : "
						ar[0] = '".$mysql->settings['local_folder']."images/no_image.png';
						ar[1] = '".$this->NoimageBackSize."';
						ar[2] = '".$this->NoimageBackPos."';
					" )."
						ar[3] = '".$r['name']."';
						ar[4] = '".$utils->digitsToRazryadi( $r['price'] )." <img src=\'/images/ruble.png\' />';
						ar[5] = 'https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog/show".$r['id']."/".strtolower( $utils->translitIt( $r['name'] ) )."_".strtolower( $utils->translitIt( $r['article'] ) ).".html';
						replaceGoodsData( ar, 'popGooD', ".$r['id'].", 1 );
					\">	
					<div class='colorname'><img src='/images/smarup.png' />".( is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'] )."</div>
					<div class='inside'>
						<div class='back' style='background-color: #".$ColorsList[$p['value']]['additional_info'].";' title='".$titleName.", стоимость ".$price."'></div>
					</div>
				</div>";
			}
			
			$aa = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `root`=".$r['id']." ORDER BY `order` ASC, `id` ASC" );			
			while( $rr = @mysql_fetch_assoc( $aa ) ) {
				if( $i >= 4 ) {
					$colors .= "
					<div class='one' onclick=\"urlmove('".$link."');\">
						<div class='inside'>
							<div class='back' style='background-color: #c1c1c1;' title='Больше цветов'><img src='/images/cross.png' /></div>
						</div>
					</div>
					";
					break;
				}
				$cprops = $main->properties->getPropertiesOfGood( $rr['id'] );
				$ctovarImage = $this->getElementByData( $cprops, "prop_id", 14 );
				$p = $this->getElementByData( $cprops, "prop_id", 1 );

				if( $p && $p['value'] ) {
					$price = $utils->digitsToRazryadi( $rr['price'] )." руб.";
					$titleName = "Артикул ".$rr['article'].", ".( is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'] );
					$colors .= "
					<div class='one' onclick=\"
						$( this ).parent().find( '.one' ).each(function() { $( this ).removeClass( 'selected' ); });
						$( this ).addClass( 'selected' );
						var ar = new Array();
					".( $ctovarImage ? "
						ar[0] = '".$mysql->settings['local_folder']."files/upload/goods/thumbs/".$ctovarImage['value']."';
						ar[1] = '".$this->imageBackSize."';
						ar[2] = '".$this->imageBackPos."';
					" : "
						ar[0] = '".$mysql->settings['local_folder']."images/no_image.png';
						ar[1] = '".$this->NoimageBackSize."';
						ar[2] = '".$this->NoimageBackPos."';
					" )."
						ar[3] = '".( $rr['name'] ? $rr['name'] : $r['name'] )."';
						ar[4] = '".$utils->digitsToRazryadi( $rr['price'] )." <img src=\'/images/ruble.png\' />';
						ar[5] = 'https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog/show".$rr['id']."/".strtolower( $utils->translitIt( $rr['name'] ? $rr['name'] : $r['name'] ) )."_".strtolower( $utils->translitIt( $rr['article'] ) ).".html';
						replaceGoodsData( ar, 'popGooD', ".$r['id'].", 1 );
					\">
						<div class='colorname'><img src='/images/smarup.png' />".( is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'] )."</div>
						<div class='inside'>
							<div class='back' style='background-color: #".$ColorsList[$p['value']]['additional_info'].";' title='".$titleName.", стоимость ".$price."'></div>
						</div>
					</div>
					";
				}
				$i++;
			}
			
			$discount = $main->modules->gmi( "discount" )->getDiscountForGood( $r );
                        $r['discount_price'] = $discount ? $r['price'] - ( $r['price'] / 100 * $discount['percent'] ) : 0;
                
                        $price = $utils->digitsToRazryadi( $r['discount_price'] ? ceil( $r['discount_price'] ) : $r['price'] )." <img src='/images/ruble.png' />".( $discount ? "<div class='old_price'>".$r['price']." Р</div>" : "" );
                
                        $discountBlock = $discount ? "<div class='discounterHover'><div class='h'></div><div>Скидка<span>".$discount['percent']."<label>%</label></span></div></div>" : "";
                        
			$if = $fitting->isInFitting( $r['id'] );
			$t .= "
			<div class='item good_item' id='popgitem_".$r['id']."'><img src='/images/good_home".( $if ? "_s" : "" ).".png' class='primerka".( $if ? " primerka_selected" : "" )." invisible' data-id='".$r['id']."' />".$discountBlock."
				<div class='preview' id='popGooDPic_".$r['id']."' style=\"background: url(".$mysql->settings['local_folder']."files/upload/goods/thumbs/".$tovarImage['value'].") no-repeat; background-size: ".$this->imageBackSize."; background-position: ".$this->imageBackPos.";\">
					<div class='primerit_online' onclick=\"showfixed_load();\">".$lang->gp( 102 )."</div>".( !$sun ?"
					<div class='kupit_online' onclick=\"showFixedbasket( 300, ".$r['id']." );\">".$lang->gp( 103 )."</div>
					<div class='choose_lenses_online' onclick=\"urlmove('".$link."#lenses');\">".$lang->gp( 104 )."</div>
					" : "
					<div class='choose_lenses_online' style=\"right:90px;\" onclick=\"showFixedbasket( 300, ".$r['id']." );\">".$lang->gp( 170 )."</div>
					")." 
					<div class='action_field' onclick=\"urlmove($('#popGooDLink_".$r['id']."').attr( 'href' ));\"></div>
				</div>
				<a href='".$link."' id='popGooDLink_".$r['id']."'>
				<h2 class='good_title' id='popGooDName_".$r['id']."'>".$r['name']."</h2>
				<h3 class='good_price' id='popGooDPrice_".$r['id']."'>".$price."</h3></a>
				</a>
				<div class='colors invisible'>
					".$colors."
				</div>
			</div>";
		}

		return $t;
	}

	function getRecommendItems()
	{
		global $mysql, $query, $main, $lang, $utils;
		
		$ColorsList = $main->listings->getListingElementsArraySpec( 3, "`order` DESC, `id` ASC", "", 0, true );
		
		$fitting = $main->modules->gmi( "fitting" );
		
		$a = $mysql->mqm( "SELECT ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar`.* FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` INNER JOIN ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` ON ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property`.`tovar_id`=".$this->gl_dbase_string."`".$mysql->t_prefix."tovar`.`id` WHERE ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property`.`value`='1' AND ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property`.`prop_id`='17' AND ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar`.`view`=1 AND ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar`.`root`=0 ORDER BY ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar`.`popular` DESC, ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar`.`order` ASC, ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar`.`id` ASC limit 4" );
		$i = 0;
		$t = '';
		while( $r = @mysql_fetch_assoc( $a ) ) {
			if( $this->goodtoshow && $this->goodtoshow['id'] == $r['id'] )
				continue;
			$i++;
			if( $i > 4 )
				break;
			$props = $main->properties->getPropertiesOfGood( $r['id'] );
			$sun = NULL;
            foreach ($props as $value){
                if($value['prop_id'] == 2 and $value['value'] == 4){
                    $sun = true;
                }
            }
			$tovarImage = $this->getElementByData( $props, "prop_id", 14 );
			
			$link = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog/show".$r['id']."/".strtolower( $utils->translitIt( $r['name'] ) )."_".strtolower( $utils->translitIt( $r['article'] ) ).".html";
			
			$colors = "";
			$i = 1;
			$p = $this->getElementByData( $props, "prop_id", 1 );
			if( $p && $p['value'] ) {
				$price = $utils->digitsToRazryadi( $r['price'] )." руб.";
				$titleName = "Артикул ".$r['article'].", ".( is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'] );
				$colors .= "
				<div class='one selected' onclick=\"
						$( this ).parent().find( '.one' ).each(function() { $( this ).removeClass( 'selected' ); });
						$( this ).addClass( 'selected' );
						var ar = new Array();
					".( $tovarImage ? "
						ar[0] = '".$mysql->settings['local_folder']."files/upload/goods/thumbs/".$tovarImage['value']."';
						ar[1] = '".$this->imageBackSize."';
						ar[2] = '".$this->imageBackPos."';
					" : "
						ar[0] = '".$mysql->settings['local_folder']."images/no_image.png';
						ar[1] = '".$this->NoimageBackSize."';
						ar[2] = '".$this->NoimageBackPos."';
					" )."
						ar[3] = '".$r['name']."';
						ar[4] = '".$utils->digitsToRazryadi( $r['price'] )." <img src=\'/images/ruble.png\' />';
						ar[5] = 'https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog/show".$r['id']."/".strtolower( $utils->translitIt( $r['name'] ) )."_".strtolower( $utils->translitIt( $r['article'] ) ).".html';
						replaceGoodsData( ar, 'recGooD', ".$r['id'].", 1 );
					\">
					<div class='colorname'><img src='/images/smarup.png' />".( is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'] )."</div>
					<div class='inside'>
						<div class='back' style='background-color: #".$ColorsList[$p['value']]['additional_info'].";' title='".$titleName.", стоимость ".$price."'></div>
					</div>
				</div>";
			}
			
			$aa = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `root`=".$r['id']." ORDER BY `order` ASC, `id` ASC" );			
			while( $rr = @mysql_fetch_assoc( $aa ) ) {
				if( $i >= 4 ) {
					$colors .= "
					<div class='one' onclick=\"urlmove('".$link."');\">
						<div class='inside'>
							<div class='back' style='background-color: #c1c1c1;' title='Больше цветов'><img src='/images/cross.png' /></div>
						</div>
					</div>
					";
					break;
				}
				$cprops = $main->properties->getPropertiesOfGood( $rr['id'] );
				$ctovarImage = $this->getElementByData( $cprops, "prop_id", 14 );
				$p = $this->getElementByData( $cprops, "prop_id", 1 );
				if( $p && $p['value'] ) {
					$price = $utils->digitsToRazryadi( $rr['price'] )." руб.";
					$titleName = "Артикул ".$rr['article'].", ".( is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'] );
					$colors .= "
					<div class='one' onclick=\"
						$( this ).parent().find( '.one' ).each(function() { $( this ).removeClass( 'selected' ); });
						$( this ).addClass( 'selected' );
						var ar = new Array();
					".( $ctovarImage ? "
						ar[0] = '".$mysql->settings['local_folder']."files/upload/goods/thumbs/".$ctovarImage['value']."';
						ar[1] = '".$this->imageBackSize."';
						ar[2] = '".$this->imageBackPos."';
					" : "
						ar[0] = '".$mysql->settings['local_folder']."images/no_image.png';
						ar[1] = '".$this->NoimageBackSize."';
						ar[2] = '".$this->NoimageBackPos."';
					" )."
						ar[3] = '".( $rr['name'] ? $rr['name'] : $r['name'] )."';
						ar[4] = '".$utils->digitsToRazryadi( $rr['price'] )." <img src=\'/images/ruble.png\' />';
						ar[5] = 'https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog/show".$rr['id']."/".strtolower( $utils->translitIt( $rr['name'] ? $rr['name'] : $r['name'] ) )."_".strtolower( $utils->translitIt( $rr['article'] ) ).".html';
						replaceGoodsData( ar, 'recGooD', ".$r['id'].", 1 );
					\">
						<div class='colorname'><img src='/images/smarup.png' />".( is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'] )."</div>
						<div class='inside'>
							<div class='back' style='background-color: #".$ColorsList[$p['value']]['additional_info'].";' title='".$titleName.", стоимость ".$price."'></div>
						</div>
					</div>
					";
				}
				$i++;
			}
                        
                        $discount = $main->modules->gmi( "discount" )->getDiscountForGood( $r );
                        $r['discount_price'] = $discount ? $r['price'] - ( $r['price'] / 100 * $discount['percent'] ) : 0;
                
			$price = $utils->digitsToRazryadi( $r['discount_price'] ? ceil( $r['discount_price'] ) : $r['price'] )." <img src='/images/ruble.png' />".( $discount ? "<div class='old_price'>".$r['price']." Р</div>" : "" );
                
                        $discountBlock = $discount ? "<div class='discounterHover'><div class='h'></div><div>Скидка<span>".$discount['percent']."<label>%</label></span></div></div>" : "";
                        
			$if = $fitting->isInFitting( $r['id'] );
			$t .= "
			<div class='item good_item' id='recgitem_".$r['id']."'><img src='/images/good_home".( $if ? "_s" : "" ).".png' class='primerka".( $if ? " primerka_selected" : "" )." invisible' data-id='".$r['id']."' />".$discountBlock."
				<div class='preview' id='recGooDPic_".$r['id']."' style=\"background: url(".$mysql->settings['local_folder']."files/upload/goods/thumbs/".$tovarImage['value'].") no-repeat; background-size: ".$this->imageBackSize."; background-position: ".$this->imageBackPos.";\">
					<div class='primerit_online' onclick=\"showfixed_load();\">".$lang->gp( 102 )."</div>"
					.( !$sun ?"
					<div class='kupit_online' onclick=\"showFixedbasket(300, ".$r['id']." );\" > ".$lang->gp( 103 )."</div>
					<div class='choose_lenses_online' onclick=\"urlmove('".$link."#lenses');\">".$lang->gp( 104 )."</div>
					" : "
					<div class='choose_lenses_online' style='right:90px;' onclick=\"showFixedbasket(300, ".$r['id']." );\" > ".$lang->gp( 170 )."</div>
					")."
					<div class='action_field' onclick=\"urlmove($('#recGooDLink_".$r['id']."').attr( 'href' ));\"></div>
				</div>
				<a href='".$link."' id='recGooDLink_".$r['id']."'>
				<h2 class='good_title' id='recGooDName_".$r['id']."'>".$r['name']."</h2>
				<h3 class='good_price' id='recGooDPrice_".$r['id']."'>".$price."</h3></a>
				</a>
				<div class='colors invisible'>
					".$colors."
				</div>
			</div>";
		}
		
		return $t;
	}
	
	function processHeaderBlock()
	{
		global $mysql, $query, $main;
		
		$catalog = $query->gp( "catalog" );
		$catalogData = $main->listings->getListingElementById( 1, $catalog, true );
		
		if( $catalog && !$catalogData ) {
			header('HTTP/1.1 404 Not Found');
			$this->forthyfour = true;
			return;
		}
		
		$show = $query->gp( "show" );
		$this->goodtoshow = $this->getItem( $show );
		if( $catalog && $show && !$this->goodtoshow ) {
			header('HTTP/1.1 404 Not Found');
			$this->forthyfour = true;
			return;
		}
	}
	
	function insertSeenItem( $id )
	{
		global $main, $mysql, $lang, $query;
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		$r = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."seen` WHERE (`sid`='".$curSid."'".( $curUser ? " OR `user`=".$curUser : "" ).") AND `good`=".$id );
		
		if( $r )
			$mysql->mu( "UPDATE `".$mysql->t_prefix."seen` SET `date`=".time()." WHERE `id`=".$r['id'] );
		else 
			$mysql->mu( "INSERT INTO `".$mysql->t_prefix."seen` VALUES(
				0,
				".( $curUser ? $curUser : 0 ).",
				'".$curSid."',
				".$id.",
				".time()."
			);" );
	}
	
	function getContent()
	{
		global $main, $mysql, $lang, $query, $utils;
		
		if( $this->forthyfour ) {
			return "<div class='catalog'>
			<div class='all_lines center'>
				<h1 class='pageTitle fullyOwned'>Ошибка</h1>
				<h2 class='red'>404</h2>
				<p>Указанной страницы не найдено!</p></div></div>
			";
		}
		
		$catalog = $query->gp( "catalog" );
		$catalogData = $main->listings->getListingElementById( 1, $catalog, true );

		if( $query->gp( "show" ) && $this->goodtoshow ) {
			$t = $this->showTovar( $query->gp( "show" ) );
			if( $t )
				return $t;
		}

		$filter = $this->getWidget();	
		
		$main->templates->setTitle( ( !$this->options['showallcategories'] ? $lang->gp( $catalogData['value'], true ) : "Все" )." оправы".( $vendorData ? " — ".$lang->gp( $vendorData['value'], true ) : "" ), true );
		
		$add = $this->buildQueryString();
		$addNosort = $this->buildQueryString( true );
		$pages = "";
		if( $this->drawPages && $this->items_count > 0 ) {

			$maxonpage = $this->getParam( "maxonpage" );
			
			if( $this->items_count > $maxonpage ) {
				$pagesCount = ceil( $this->items_count / $maxonpage );
				$page = $this->options['page'];

				$pages = "<div class='pages_block'>".( $page > 1 ? "<a href=\"https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog".$catalog."/page".( $page - 1 )."/".strtolower( $utils->translitIt( $lang->gp( $catalogData['value'], true ) ) ).".html?".$add."\"><img src='/images/arb.png' /></a>" : "" );
				
				$p = array();
				for( $a = 1; $a <= $pagesCount; $a++ ) {
					$p[$a] = ( $page == $a ? 
						"<span>".$a."</span>" : 
						"<a href=\"https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog".$catalog."/page".$a."/".strtolower( $utils->translitIt( $lang->gp( $catalogData['value'], true ) ) ).".html?".$add."\">".$a."</a>" );
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
					
				$pages .= $tp.
				( $page < $pagesCount ? "<a href=\"https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog".$catalog."/page".( $page + 1 )."/".strtolower( $utils->translitIt( $lang->gp( $catalogData['value'], true ) ) ).".html?".$add."\"><img src='/images/arr.png' /></a>" : "" )."</div>";
			}
		}
		
		$faces = $main->listings->getListingElementsArraySpec( 11, "`order` DESC, `id` ASC", "", 0, true );
		$faces_text = '';
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."blobs` WHERE ".( $main->users->auth ? "`user`=".$main->users->userArray['id'] : "`sid`='".$main->users->sid."'" ) );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$faces_text .= "<div class='one' data-id='".$r['id']."' data-self=1><img src='".$mysql->settings['local_folder']."files/upload/usersData/".$r['data']."' /></div>";
		}
		foreach( $faces as $k => $v ) {
			$faces_text .= "<div class='one' data-id='".$k."' data-self=0><img src='".$mysql->settings['local_folder']."files/upload/listings/".$v['image']."' /></div>";
		}
		/*
                 * <div class='sorting' onblur=\"$( this ).find( '.sub' ).slideUp( 100 );\">
							<div class='seltext'>
								".$this->getRightSortString( $this->options['sort'], $this->options['dir'] )."
							</div>
							<div class='icon' onclick=\"
								if( $( this ).parent().find( '.sub' ).is( ':visible' ) ) {
									$( this ).parent().find( '.sub' ).slideUp( 100 );
								} else {
									$( this ).parent().find( '.sub' ).slideDown( 100 );
								}
							\">
								<img src='".$mysql->settings['local_folder']."images/ardown.png' />
							</div>
							<div class='sub'>
								<a href='https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog".$catalog."/".strtolower( $utils->translitIt( $lang->gp( $catalogData['value'], true ) ) ).".html?".$addNosort."&sort=1&dir=ASC'>По алфавиту А..Я</a>
								<a href='https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog".$catalog."/".strtolower( $utils->translitIt( $lang->gp( $catalogData['value'], true ) ) ).".html?".$addNosort."&sort=1&dir=DESC'>По алфавиту Я..А</a>
								<a href='https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog".$catalog."/".strtolower( $utils->translitIt( $lang->gp( $catalogData['value'], true ) ) ).".html?".$addNosort."&sort=3&dir=ASC'>По популярности А..Я</a>
								<a href='https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog".$catalog."/".strtolower( $utils->translitIt( $lang->gp( $catalogData['value'], true ) ) ).".html?".$addNosort."&sort=3&dir=DESC'>По популярности Я..А</a>
							</div>
						</div>
                 */
		$ad_block = "
					<div class='top_add_block'>
                                                <div class='center'>
                                                    ".$main->modules->gmi( "fitting_online" )->getHeaderBlock( true )."
                                                </div>
						<div class='pages'>".$pages."</div>
						
						<div class='sel_type'>
							<img src='".$mysql->settings['local_folder']."images/".( $this->options['output'] == 1 ? "sel_" : "" )."eye.png' onclick=\"changeOutputAndreload(1);\" />
							<span onclick=\"changeOutputAndreload(1);\" class='pointer'>Показать<br/>оправы</span>
							<img src='".$mysql->settings['local_folder']."images/".( $this->options['output'] == 2 ? "sel_" : "" )."chel.png' onclick=\"changeOutputAndreload(2);\" />
							<span onclick=\"changeOutputAndreload(2);\" class='pointer'>Показать<br/>примерку</span>
						</div>						
						<div class='clear'></div>
					</div>
		";
		if($lang->gp( $catalogData['value'], true ) == "Солнцезащитные"){
					$itemsText = "
		<div class='sopli'><div class='all_lines'>
			<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a>".( !$this->options['showallcategories'] ? "<span>></span><a href='".$mysql->settings['local_folder']."catalog".( $catalog > 1 ? $catalog : "" )."/".strtolower( $utils->translitIt( $lang->gp( $catalogData['value'], true ) ) ).".html'>".$lang->gp( $catalogData['value'], true )." очки</a>" : "" )." 
		</div></div>
		".$main->modules->gmi( "actions" )->getIndexBanners( $this->dbinfo['id'] )."
		<div class='catalog'>
			<div class='all_lines'>
				<div class='filter'>
					".$filter."
				</div>
				<div class='main_data'><div class='md_inner'>
					<h2 class='r_title'>".( !$this->options['showallcategories'] ? $lang->gp( $catalogData['value'], true ) : "Все" )." очки</h2>
					<p class='r_comments'>".( is_numeric( $catalogData['additional_info'] ) ? $lang->gp( $catalogData['additional_info'] ) : $catalogData['additional_info'] )."</p>
					".$ad_block."
					<div class='tags'>
						".$this->tags."
						<div class='clear'></div>
					</div>
					".( $this->options['output'] == 2 ? "<div class='options'>
						<div class='block'>
							<div class='load' onclick=\"showfixed_load();\">
								<img src='".$mysql->settings['local_folder']."images/photoaparat.png' /><br/>
								".$lang->gp( 167, true )."
							</div>
							<div class='or'>или</div>
							".$faces_text."
							<div class='clear'></div>
						</div>
						<div class='texts".( !$main->users->auth ? "" : " texts_auth" )."'>
							".$lang->gp( 164, true )."
							".( !$main->users->auth ? "<p>".$lang->gp( 165, true )." <a href=\"#\" onclick=\"$( '.fixed_profile' ).fadeIn( 400 ); return false;\">".$lang->gp( 166, true )."</a></p>" : "" )."
						</div>
						<div class='clear'></div>
					</div>" : "" )."
					<div class='goodElem'>
		";
	}else{
		$itemsText = "
		<div class='sopli'><div class='all_lines'>
			<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a>".( !$this->options['showallcategories'] ? "<span>></span><a href='".$mysql->settings['local_folder']."catalog".( $catalog > 1 ? $catalog : "" )."/".strtolower( $utils->translitIt( $lang->gp( $catalogData['value'], true ) ) ).".html'>".$lang->gp( $catalogData['value'], true )." оправы</a>" : "" )." 
		</div></div>
		".$main->modules->gmi( "actions" )->getIndexBanners( $this->dbinfo['id'] )."
		<div class='catalog'>
			<div class='all_lines'>
				<div class='filter'>
					".$filter."
				</div>
				<div class='main_data'><div class='md_inner'>
					<h2 class='r_title'>".( !$this->options['showallcategories'] ? $lang->gp( $catalogData['value'], true ) : "Все" )." оправы</h2>
					<p class='r_comments'>".( is_numeric( $catalogData['additional_info'] ) ? $lang->gp( $catalogData['additional_info'] ) : $catalogData['additional_info'] )."</p>
					".$ad_block."
					<div class='tags'>
						".$this->tags."
						<div class='clear'></div>
					</div>
					".( $this->options['output'] == 2 ? "<div class='options'>
						<div class='block'>
							<div class='load' onclick=\"showfixed_load();\">
								<img src='".$mysql->settings['local_folder']."images/photoaparat.png' /><br/>
								".$lang->gp( 167, true )."
							</div>
							<div class='or'>или</div>
							".$faces_text."
							<div class='clear'></div>
						</div>
						<div class='texts".( !$main->users->auth ? "" : " texts_auth" )."'>
							".$lang->gp( 164, true )."
							".( !$main->users->auth ? "<p>".$lang->gp( 165, true )." <a href=\"#\" onclick=\"$( '.fixed_profile' ).fadeIn( 400 ); return false;\">".$lang->gp( 166, true )."</a></p>" : "" )."
						</div>
						<div class='clear'></div>
					</div>" : "" )."
					<div class='goodElem'>
		";
	}
		foreach( $this->items as $number => $itemData ) {
			$itemsText .= $this->showGoodElement( $itemData, $catalog );
		}
		$itemsText .= "<div class='clear'></div></div>
					".$ad_block."
					<div class='clear'></div>
				</div></div>
				<div class='clear'></div>
			</div>
		</div>
		<script>
			$(window).load(function()
			{
				$( '.catalog .main_data' ).width( $( '.catalog .all_lines' ).width() - $( '.catalog .filter' ).width() );
				".( $this->options['output'] == 2 ? "
				
				" : "" )."
				
				$( '.catalog .goodElem .item' ).animate( { opacity: 1 }, 200 );
				$( '.options .block .one' ).click(function(){
					if( $( this ).attr( 'data-self' ) == 1 || $( this ).attr( 'data-self' ) == '1' ) {
                                                setCookie( 'selected_face', 0, { path: '/' } );
						setCookie( 'selected_selfface', $( this ).attr( 'data-id' ), { expires: 3600 * 30, path: '/' } );
					} else {
						setCookie( 'selected_selfface', 0, { path: '/' } );
						setCookie( 'selected_face', $( this ).attr( 'data-id' ), { expires: 3600 * 30, path: '/' } );
					}
					urlmove('".$_SERVER['REQUEST_URI']."');
				});
			});
			
			$(window).resize(function()
			{
				$( '.catalog .main_data' ).width( $( '.catalog .all_lines' ).width() - $( '.catalog .filter' ).width() );
			});
			
			function changeOutputAndreload( value )
			{
				setCookie( 'output', value, { expires: 3600 * 30, path: '/' } );
				urlmove('".$_SERVER['REQUEST_URI']."');
			}
		</script>
		";
		
		return $t.$itemsText.$main->modules->gmi( "lenses" )->getCalculateSizeFloatBlock();
	}
	
	function getWidget()
	{
		global $mysql, $main, $query, $utils, $lang;
		
		if( !$this->isSelected() )
			return "";
			
		$catalog = $query->gp( "catalog" );
		$catalogData = $main->listings->getListingElementById( 1, $catalog, true );
		
		if( !$catalogData )
			return "";
		
		$sort = $this->checkCorrectOfSortValue( $query->gp( "sort" ) );
		$dir = strtolower( $query->gp( "dir" ) );
		$dir = $dir == "desc" ? $dir : "asc";
		
		$start_price = $query->gp( "start_price" );
		$start_price = is_numeric( $start_price ) && $start_price ? ( $start_price > 0 ? $start_price : 1 ) : '';
		$end_price = $query->gp( "end_price" );
		$end_price = is_numeric( $end_price ) && $end_price ? ( $end_price > 0 ? $end_price : 1 ) : '';
		if( $end_price && $start_price > $end_price )
			$start_price = $end_price;
		$page = $query->gp( "page" );
		$page = is_numeric( $page ) && $page > 1 ? $page : 1;
		
		$output = $query->gp( "output" ); 
		$output = $output ? $output : ( isset( $_COOKIE['output'] ) ? $_COOKIE['output'] : 1 );
			
		$this->options['sort'] = $sort;
		$this->options['dir'] = $dir;
		$this->options['start_price'] = $start_price;
		$this->options['end_price'] = $end_price;
		$this->options['page'] = $page;
		$this->options['output'] = $output;
                $this->options['showallcategories'] = $query->gp( "showallcategories" );
                
                $ar = $main->listings->getListingElementsArrayAll( 1, '', true );
		$this->options['types'] = $query->gp( "types" );
		$tt = explode( "_", $this->options['types'] );
		$this->options['types'] = array();
		foreach( $tt as $v ) {
			if( $v ) {
				$this->options['types'][$v] = true;
				$this->tags .= "<div class='tag' data-id='".$v."'>".$lang->gp( $ar[$v]['value'], true )."<img src='/images/greycross.png' /></div>";
			}
		}
		
		$ar = $main->listings->getListingElementsArrayAll( 2, '', true );
		$this->options['forma'] = $query->gp( "forma" );
		$tt = explode( "_", $this->options['forma'] );
		$this->options['forma'] = array();
		foreach( $tt as $v ) {
			if( $v ) {
				$this->options['forma'][$v] = true;
				$this->tags .= "<div class='tag' data-id='".$v."'>".$lang->gp( $ar[$v]['value'], true )."<img src='/images/greycross.png' /></div>";
			}
		}
		
		$ar = $main->listings->getListingElementsArrayAll( 4, '', true );
		$this->options['str'] = $query->gp( "str" );
		$tt = explode( "_", $this->options['str'] );
		$this->options['str'] = array();
		foreach( $tt as $v ) {	
			if( $v ) {
				$this->options['str'][$v] = true;
				$this->tags .= "<div class='tag' data-id='".$v."'>".$lang->gp( $ar[$v]['value'], true )."<img src='/images/greycross.png' /></div>";
			}
		}
		
		$ar = $main->listings->getListingElementsArrayAll( 27, '', true );
		$this->options['size'] = $query->gp( "size" );
		$tt = explode( "_", $this->options['size'] );
		$this->options['size'] = array();
		foreach( $tt as $v ) {	
			if( $v ) {
				$this->options['size'][$v] = true;
				$this->tags .= "<div class='tag' data-id='".$v."'>".$lang->gp( $ar[$v]['value'], true )."<img src='/images/greycross.png' /></div>";
			}
		}
		
		$ar = $main->listings->getListingElementsArrayAll( 3, '', true );
		$this->options['color'] = $query->gp( "color" );
		$tt = explode( "_", $this->options['color'] );
		$this->options['color'] = array();
		foreach( $tt as $v ) {
			if( $v ) {
				$this->options['color'][$v] = true;
				$this->tags .= "<div class='tag' data-id='".$v."'>".$lang->gp( $ar[$v]['value'], true )."<img src='/images/greycross.png' /></div>";
			}
		}
		$ar = $main->listings->getListingElementsArrayAll( 5, '', true );
		$this->options['mat'] = $query->gp( "mat" );
		$tt = explode( "_", $this->options['mat'] );
		$this->options['mat'] = array();
		foreach( $tt as $v ) {
			if( $v ) {
				$this->options['mat'][$v] = true;
				$this->tags .= "<div class='tag' data-id='".$v."'>".$lang->gp( $ar[$v]['value'], true )."<img src='/images/greycross.png' /></div>";
			}
		}
		$ar = $main->listings->getListingElementsArrayAll( 7, '', true );
		$this->options['vendor'] = $query->gp( "vendor" );
		$tt = explode( "_", $this->options['vendor'] );
		$this->options['vendor'] = array();
		foreach( $tt as $v ) {
			if( $v ) {
				$this->options['vendor'][$v] = true;
				$this->tags .= "<div class='tag' data-id='".$v."'>".$lang->gp( $ar[$v]['value'], true )."<img src='/images/greycross.png' /></div>";
			}
		}
		
		$this->items = !$query->gp( "show" ) ? $this->getItems( $catalog, $catalogData ) : array();
                
                $types = $main->listings->getListingElementsArray( 1, 0, false, '', true );
		$typesText = "";
		foreach( $types as $k => $v ) {
			$typesText .= "
			<div class='option".( isset( $this->options['types'][$k] ) && $this->options['types'][$k] ? " option_selected" : "" )."' data-id='".$k."'>
				<div class='check'><div class='outer'><div class='inner'></div></div></div>
				<div class='text'>".$lang->gp( $v['value'], true )."</div>
				<div class='clear'></div>
			</div>";
		}
			
		$forma = $main->listings->getListingElementsArray( 2, 0, false, '', true );
		$formaText = "";
		foreach( $forma as $k => $v ) {
			$picData = $main->listings->getListingElementsArray( 2, $k, true, '', true );
			$pic = $picData && count( $picData ) && isset( $picData[1] ) ? $picData[1]['image'] : $v['image'];
			$formaText .= "
			<div class='option".( isset( $this->options['forma'][$k] ) && $this->options['forma'][$k] ? " option_selected" : "" )."' data-id='".$k."'>
				<div class='check'><div class='outer'><div class='inner'></div></div></div>
				<div class='img'><img src='/files/upload/listings/".$pic."' alt='forma_option' /></div>
				<div class='text'>".$lang->gp( $v['value'], true )."</div>
				<div class='clear'></div>
			</div>";
		}
		
		$str = $main->listings->getListingElementsArray( 4, 0, false, '', true );
		$strText = "";
		foreach( $str as $k => $v ) {
			$strText .= "
			<div class='option".( isset( $this->options['str'][$k] ) && $this->options['str'][$k] ? " option_selected" : "" )."' data-id='".$k."'>
				<div class='check'><div class='outer'><div class='inner'></div></div></div>
				<div class='text'>".$lang->gp( $v['value'], true )."</div>
				<div class='clear'></div>
			</div>";
		}
		
		$size = $main->listings->getListingElementsArray( 27, 0, false, '', true );
		$sizeText = "";
		foreach( $size as $k => $v ) {
			$sizeText .= "
			<div class='option".( isset( $this->options['size'][$k] ) && $this->options['size'][$k] ? " option_selected" : "" )."' data-id='".$k."'>
				<div class='check'><div class='outer'><div class='inner'></div></div></div>
				<div class='text'>".$lang->gp( $v['value'], true )."</div>
				<div class='clear'></div>
			</div>";
		}
		
		$colors = $main->listings->getListingElementsArray( 3, 0, false, '', true );
		$colorsText = "";
		foreach( $colors as $k => $v ) {
			$colorsText .= "
			<div class='colors".( isset( $this->options['color'][$k] ) && $this->options['color'][$k] ? " colors_selected" : "" )."' data-id='".$k."'>
				<div class='color'><div class='colorname'><img src='/images/smardown.png' />".( is_numeric( $v['value'] ) ? $lang->gp( $v['value'], true ) : $v['value'] )."</div><div class='color_exact' style='".( $v['image'] ? "background: url(".$mysql->settings['local_folder']."files/upload/listings/".$v['image'].") no-repeat; background-size: cover; background-position: 50% 50%;" : "background-color: #".$v['additional_info'].";" )."'></div></div>
			</div>
			";
		}
		
		$mat = $main->listings->getListingElementsArray( 5, 0, false, '', true );
		$matText = "";
		foreach( $mat as $k => $v ) {
			$matText .= "
			<div class='option".( isset( $this->options['mat'][$k] ) && $this->options['mat'][$k] ? " option_selected" : "" )."' data-id='".$k."'>
				<div class='check'><div class='outer'><div class='inner'></div></div></div>
				<div class='text'>".$lang->gp( $v['value'], true )."</div>
				<div class='clear'></div>
			</div>";
		}
		
		$vendor = $main->listings->getListingElementsArray( 7, 0, false, '', true );
		$vendorText = "";
		foreach( $vendor as $k => $v ) {
			$vendorText .= "
			<div class='option".( isset( $this->options['vendor'][$k] ) && $this->options['vendor'][$k] ? " option_selected" : "" )."' data-id='".$k."'>
				<div class='check'><div class='outer'><div class='inner'></div></div></div>
				<div class='text'>".$lang->gp( $v['value'], true )."</div>
				<div class='clear'></div>
			</div>";
		}
		
		if( $this->tags )
			$this->tags .= "<div class='tagClear'>Очистить</div>";
		/*
                 * 
                 */
		$t = "
			".$main->modules->gmi( "fitting_online" )->getFilerBlock()."
			<h2 class='f_title'>Фильтр<div class='plus'><div class='v'></div><div class='h'></div></div></h2>
                        
                       <div class='block".( $this->options['showallcategories'] ? "" : " invisible" )."' data-id='types'>
				<h3 class='title'>Раздел</h3><div class='execute' data-id='1' onclick='moveGoodsAfterFilter();'></div>
				".$typesText."
			</div>
			
			<div class='block' data-id='forma'>
				<h3 class='title'>Форма оправы</h3><div class='execute' data-id='1' onclick='moveGoodsAfterFilter();'></div>
				".$formaText."
			</div>
			
			<div class='block' data-id='str'>
				<h3 class='title'>Тип оправы</h3><div class='execute' data-id='2' onclick='moveGoodsAfterFilter();'></div>
				".$strText."
			</div>
			
			<div class='block' data-id='size'>
				<h3 class='title'>Размер оправы</h3><div class='execute' data-id='27' onclick='moveGoodsAfterFilter();'></div>
				<a href='#' onclick=\"showCalculate( 400 ); return false;\" style='position: relative; top: 5px; margin-bottom: 10px; color: #325387;'>Я не знаю своего размера</a>
				".$sizeText."
			</div>
			
			<div class='block' data-id='color'>
				<h3 class='title'>Цвет</h3><div class='execute' data-id='3' onclick='moveGoodsAfterFilter();'></div>
				<div style='margin: 8px;'>".$colorsText."<div class='clear'></div></div>
			</div>
			
			<div class='block' data-id='mat'>
				<h3 class='title'>Материал</h3><div class='execute' data-id='4' onclick='moveGoodsAfterFilter();'></div>
				".$matText."
			</div>
			
			<div class='block' data-id='vendor'>
				<h3 class='title'>Бренд</h3><div class='execute' data-id='5' onclick='moveGoodsAfterFilter();' style='right: 20px;'></div>
				<div class='innerScroll'>".$vendorText."</div>
			</div>
			
			<div class='block' data-id='price'>
				<h3 class='title'>Цена</h3><div class='execute' onclick='moveGoodsAfterFilter();'></div><input type=hidden id='start_price' value='".( $this->options['start_price'] ? $this->options['start_price'] : $this->filteredMinPrice )."' /><input type=hidden id='end_price' value='".( $this->options['end_price'] ? $this->options['end_price'] : $this->filteredMaxPrice )." ' />
				<div class='center' style='height: 40px; color: #000; line-height: 35px;' id='resultPrice'>".( $this->options['start_price'] ? $this->options['start_price'] : $this->filteredMinPrice )." - ".( $this->options['end_price'] ? $this->options['end_price'] : $this->filteredMaxPrice )." Р</div>
				<div id='priceSlider'></div>
			</div>
			
			<div class='block'>
				<input type=button value='Применить' id='filter_button' onclick='moveGoodsAfterFilter();' />
				".( $this->tags ? "<input type=button value='Очистить все' id='clear_button' onclick='clearAllTags();' style='display: inline-block;' />" : "" )."
			</div>
			
			<script>
				$(window).load(function()
				{
					$( '#priceSlider' ).slider({
						range: true,
						min: ".$this->filteredMinPrice.",
						max: ".$this->filteredMaxPrice.",
						values: [ ".( $this->options['start_price'] ? $this->options['start_price'] : $this->filteredMinPrice ).", ".( $this->options['end_price'] ? $this->options['end_price'] : $this->filteredMaxPrice )." ],
						slide: function( event, ui ) {
							$( '#resultPrice' ).html( ui.values[ 0 ] + ' - ' + ui.values[ 1 ] + ' Р' );
							$( '#start_price' ).val( ui.values[ 0 ] );
							$( '#end_price' ).val( ui.values[ 1 ] );
							
							var exec = $( '#priceSlider' ).parent().find( '.execute' );
						
							exec.fadeIn( 300 );
							$( '.execute' ).each( function(){
								if( $( this ).find( '.check' ).attr( 'data-id' ) != exec.attr( 'data-id' ) )
									$( this ).find( '.check' ).fadeOut( 300 );
							});
						
						}
					});
					$( '#priceSlider' ).find( 'span' ).css( 'border-radius', '25px' ).css( 'outline', 'none' );
				
					$( '.option .check' ).parent().click(function(){
						$( '#filter_button' ).show();
						if( $( this ).hasClass( 'option_selected' ) ) {
							$( this ).removeClass( 'option_selected' );
						} else {
							$( this ).addClass( 'option_selected' );
						}
						
						var exec = 0;
						if( $( this ).parent().hasClass( 'innerScroll' ) ) {
							exec = $( this ).parent().parent().find( '.execute' );
						} else {
							exec = $( this ).parent().find( '.execute' );
						}
						
						exec.fadeIn( 300 );
						$( '.execute' ).each( function(){
							if( $( this ).find( '.check' ).attr( 'data-id' ) != exec.attr( 'data-id' ) )
								$( this ).find( '.check' ).fadeOut( 300 );
						});
					});
					$( '.filter .colors' ).click(function(){
						$( '#filter_button' ).show();
						if( $( this ).hasClass( 'colors_selected' ) ) {
							$( this ).removeClass( 'colors_selected' );
						} else {
							$( this ).addClass( 'colors_selected' );
						}
						
						var exec = $( this ).parent().parent().find( '.execute' );
						exec.fadeIn( 300 );
						$( '.execute' ).each( function(){
							if( $( this ).attr( 'data-id' ) != exec.attr( 'data-id' ) )
								$( this ).fadeOut( 300 );
						});
					});
					
					$( '.filter .colors .colorname' ).each(function(){
						$( this ).css( 'margin-left', ( ( $( this ).width() ) / 2 * -1 ) - 3 );
					});
					
					$( '.tags .tag' ).click(function(){
						var tid = $( this ).attr( 'data-id' );
						$( '.block' ).each(function(){
							var ss = '';
							$( this ).find( '.option_selected' ).each(function(){
								if( $( this ).attr( 'data-id' ) == tid ) {
									$( this ).removeClass( 'option_selected' );
									moveGoodsAfterFilter();
								}
							});
							$( this ).find( '.colors_selected' ).each(function(){
								if( $( this ).attr( 'data-id' ) == tid ) {
									$( this ).removeClass( 'colors_selected' );
									moveGoodsAfterFilter();
								}
							});
						});
					});
					
					$( '.tags .tagClear' ).click(function(){
						clearAllTags();
					});
                                        
                                        $( '.f_title' ).click(function(){
                                                if( !isMobile )
                                                    return;
						if( $( this ).hasClass( 'plus_opened' ) ) {
							$( this ).removeClass( 'plus_opened' );
							$( this ).parent().parent().find( '.block' ).slideUp( 100 );
						} else {
							$( this ).addClass( 'plus_opened' );
							$( this ).parent().parent().find( '.block' ).slideDown( 100 );
						}
					});
				});
				
				function clearAllTags()
				{
					$( '.block' ).each(function(){
						$( this ).find( '.option_selected' ).each(function(){
							$( this ).removeClass( 'option_selected' );
						});
						$( this ).find( '.colors_selected' ).each(function(){
							$( this ).removeClass( 'colors_selected' );
						});
					});
					moveGoodsAfterFilter();
				}
								
				function moveGoodsAfterFilter()
				{
					var str = '';
					$( '.block' ).each(function(){
						var ss = '';
						$( this ).find( '.option_selected' ).each(function(){
							ss += ( ss ? '_' : '' ) + $( this ).attr( 'data-id' );
						});
						$( this ).find( '.colors_selected' ).each(function(){
							ss += ( ss ? '_' : '' ) + $( this ).attr( 'data-id' );
						});
						if( ss ) {
							str += ( str ? '&' : '' ) + $( this ).attr( 'data-id' ) + '=' + ss;
						}
					});
					str += '&start_price=' + $( '#start_price' ).val() + '&end_price=' + $( '#end_price' ).val();
					urlmove( '".$mysql->settings['local_folder']."catalog".$catalog."/".strtolower( $utils->translitIt( $lang->gp( $catalogData['value'], true ) ) ).".html?' + str".( $this->options['showallcategories'] ? " + '&showallcategories=1'" : "" )." );
				}
			</script>
		";
		
		return $t;
	}
	
	function checkCorrectOfSortValue( $sort )
	{
		switch( strtolower( $sort ) ) {
			case 'name':
				return 1;
			case 'price':
				return 2;
			case 'popular':
				return 3;
			default:
				return 1;
		}
	}
	
	function getCorrentSortValue( $sort )
	{
		switch( $sort ) {
			case 1:
				return 'name';
			case 2:
				return 'price';
			case 3:
				return 'popular';
		}
	}
	
	function getRightSortString( $sort, $dir )
	{
		switch( $sort ) {
			case 1:
				return $dir == 'asc' ? "По алфавиту А..Я" : "По алфавиту Я..А";
			case 2:
				return $dir == 'asc' ? "По цене А..Я" : "По цене Я..А";
			case 3:
				return $dir == 'asc' ? "По популярности А..Я" : "По популярности Я..А";
		}
	}
	
	function buildQueryString( $nosort = false )
	{
		global $main, $utils, $lang;
		
		$add = "";
		
		if( !$nosort ) {	
			if( $this->options['sort'] )
				$add .= "&sort=".$this->getCorrentSortValue( $this->options['sort'] );
			$add .= "&dir=".$this->options['dir'];
		}
		if( $this->options['start_price'] )
			$add .= "&start_price=".$this->options['start_price'];
		if( $this->options['end_price'] )
			$add .= "&end_price=".$this->options['end_price'];
		$add .= "&output=".$this->options['output'];
                
                if( $this->options['showallcategories'] )
                    $add .= "&showallcategories=1";
			
		$f = "";
		foreach( $this->options['forma'] as $k => $v ) {
			$f .= ( $f ? "_" : "" ).$k;
		}
		if( $f )
			$add .= "&forma=".$f;
			
		$f = "";
		foreach( $this->options['color'] as $k => $v ) {
			$f .= ( $f ? "_" : "" ).$k;
		}
		if( $f )
			$add .= "&color=".$f;
			
		$f = "";
		foreach( $this->options['str'] as $k => $v ) {
			$f .= ( $f ? "_" : "" ).$k;
		}
		if( $f )
			$add .= "&str=".$f;
			
		$f = "";
		foreach( $this->options['size'] as $k => $v ) {
			$f .= ( $f ? "_" : "" ).$k;
		}
		if( $f )
			$add .= "&size=".$f;
                
                $f = "";
		foreach( $this->options['mat'] as $k => $v ) {
			$f .= ( $f ? "_" : "" ).$k;
		}
		if( $f )
			$add .= "&mat=".$f;
			
		$f = "";
		foreach( $this->options['vendor'] as $k => $v ) {
			$f .= ( $f ? "_" : "" ).$k;
		}
		if( $f )
			$add .= "&vendor=".$f;
		
		return $add;
	}
	
	function getColorArrayOfGood( $id )
	{
		global $main, $mysql, $lang, $query, $utils;
		
		$ar = array();
		$aa = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `root`=".$id." ORDER BY `order` ASC, `id` ASC" );			
		while( $rr = @mysql_fetch_assoc( $aa ) ) {
			$rr['properties'] = $main->properties->getPropertiesOfGood( $rr['id'] );
			$p = $this->getElementByData( $rr['properties'], "prop_id", 1 );
			$rr['color'] = $p && $p['value'] ? $p['value'] : 0;
			$ar[$rr['id']] = $rr;
		}
		
		return $ar;
	}
	
	function getItems( $catalog, $catalogData )
	{
		global $main, $mysql, $lang, $query, $utils;
		
		// Потом нужно переработать эту фунция, используя INNER JOIN
		
		$where = "`view`=1 AND `root`=0";

		$wherePrice = '';
		if( $this->options['start_price'] )
			$wherePrice .= " AND `price`>='".$this->options['start_price']."'";
		if( $this->options['end_price'] )
			$wherePrice .= " AND `price`<='".$this->options['end_price']."'";

		if( count( $this->options['vendor'] ) ) {
			$tt = '';
			foreach( $this->options['vendor'] as $k => $v ) {
				$tt .= ( $tt ? " OR " : "" )."`vendor`=".$k;
			}
			if( $tt )
				$where .= " AND (".$tt.")";
		}
		
		$r = $mysql->mq( "SELECT `price` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE ".$where." ORDER BY `price` ASC" );
		$this->filteredMinPrice = intval( $r['price'] );
		$r = $mysql->mq( "SELECT `price` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE ".$where." ORDER BY `price` DESC" );
		$this->filteredMaxPrice = intval( $r['price'] );
		
		$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE ".$where.$wherePrice." ORDER BY `order` ASC, `".$this->getCorrentSortValue( $this->options['sort'] )."` ".$this->options['dir'] );
		$elems = array();
                
                $showAll = $this->options['showallcategories'];
		
		while( $r = mysql_fetch_assoc( $a ) ) {
			
			$r['properties'] = $main->properties->getPropertiesOfGood( $r['id'] );
			
			$go = false;
			foreach( $r['properties'] as $p ) {
				if( $p['prop_id'] == 2 ) {
                                        if( $showAll ) {
                                            $go = true;
                                            break;
                                        } else {
                                            if( count( $this->options['types'] ) ) {
                                                $lgo = false;
                                                foreach( $this->options['types'] as $k => $v ) {
                                                    if( $p['value'] == $k ) {
                                                        $lgo = true;
                                                        break;
                                                    }
                                                }
                                                if( $lgo ) {
                                                    $go = true;
                                                    break;
                                                }
                                            } else {
                                                if( $p['value'] == $catalog ) {
                                                    $go = true;
                                                    break;
                                                }
                                            }
                                        }
				}
			}
			if( !$go )
				continue;
			
			if( count( $this->options['forma'] ) ) {
				$thisForma = $this->getElementByData( $r['properties'], "prop_id", 3 );
				if( !$thisForma )
					continue;
				$go = false;
				foreach( $this->options['forma'] as $k => $v ) {
					if( $thisForma['value'] == $k ) {
						$go = true;
						break;
					}
				}
				if( !$go )
					continue;
			}
			
			if( count( $this->options['str'] ) ) {
				$thisStr = $this->getElementByData( $r['properties'], "prop_id", 4 );
				if( !$thisStr )
					continue;
				$go = false;
				foreach( $this->options['str'] as $k => $v ) {
					if( $thisStr['value'] == $k ) {
						$go = true;
						break;
					}
				}
				if( !$go )
					continue;
			}
			
			if( count( $this->options['size'] ) ) {
				$thisSize = $this->getElementByData( $r['properties'], "prop_id", 19 );
				if( !$thisSize )
					continue;
				$go = false;
				foreach( $this->options['size'] as $k => $v ) {
					if( $thisSize['value'] == $k ) {
						$go = true;
						break;
					}
				}
				if( !$go )
					continue;
			}
                        
                        if( count( $this->options['mat'] ) ) {
				$thisSize = $this->getElementByData( $r['properties'], "prop_id", 5 );
				if( !$thisSize )
					continue;
				$go = false;
				foreach( $this->options['mat'] as $k => $v ) {
					if( $thisSize['value'] == $k ) {
						$go = true;
						break;
					}
				}
				if( !$go )
					continue;
			}
			
			$r['colors'] = $this->getColorArrayOfGood( $r['id'] );
			if( count( $this->options['color'] ) ) {
				$thisColor = $this->getElementByData( $r['properties'], "prop_id", 1 );
				$go = false;
				foreach( $this->options['color'] as $k => $v ) {
					if( $thisColor && $k == $thisColor['value'] ) {
						$go = true;
						break;
					}
					foreach( $r['colors'] as $cid => $cdata ) {
						if( $cdata['color'] && $k == $cdata['color'] ) {
							$go = true;
							break;
						}
					}
					if( $go )
						break;
				}
				if( !$go )
					continue;
			}
			
			if( $r['date'] >= time() - ( 3600 * 24 * 60 ) )
				$r['novinka'] = true;
			
			@array_push( $elems, $r );
		}
		
		$this->items_count = count( $elems );
		$maxonpage = $this->getParam( $this->options['output'] == 2 ? "maxonpage_faces" : "maxonpage" );
		if( $this->items_count > $maxonpage ) {
			$this->drawPages = true;
			$pagesCount = ceil( $this->items_count / $maxonpage );
			
			if( $this->options['page'] > $pagesCount )
				$this->options['page'] = $pagesCount;
			
			$startFrom = ( $this->options['page'] - 1 ) * $maxonpage;
			$new_elems = array();
			$c = 0;
			foreach( $elems as $v ) {
				if( $c >= $startFrom )
					array_push( $new_elems, $v );
				if( ++$c >= $startFrom + $maxonpage )
					break;
			}
			$elems = $new_elems;
		}
		
		return $elems;
	}
	
	function getItemsForSearch( $search_text, &$page, &$drawPages, &$items_count )
	{
		global $main, $mysql, $lang, $query, $utils;
		
		if( !trim( $search_text ) )
			return array();
		
		$where = "`view`=1 AND `root`=0 AND (`name` LIKE '%".$search_text."%' OR `article` LIKE '%".$search_text."%')";
		
		$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE ".$where." ORDER BY `order` ASC, `name` ASC" );
		$elems = array();
		while( $r = @mysql_fetch_assoc( $a ) ) {
			@array_push( $elems, $r );
		}
		
		$tt = explode( " ", $search_text );
		$where = "";
		foreach( $tt as $v ) {
			if( !trim( $v ) )
				continue;
			$where .= ( $where ? " AND " : "" )."(`name` LIKE '%".$v."%' OR `article` LIKE '%".$v."%')";
		}
		if( $where && !count( $elems ) ) {
			$where = "`view`=1 AND `root`=0 AND (".$where.")";
			$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE ".$where." ORDER BY `order` ASC, `name` ASC" );
			$elems = array();
			while( $r = @mysql_fetch_assoc( $a ) ) {
				$cont = true;
				foreach( $elems as $v ) {
					if( $v['id'] == $r['id'] ) {
						$cont = false;
						break;
					}
				}
				if( !$cont )
					continue;
				@array_push( $elems, $r );
			}
		}
		
		$maxonpage = $this->getParam( "maxonpage" );
		$items_count = count( $elems );
		$startFrom = 0;
		if( $items_count > $maxonpage ) {
			$drawPages = true;
			$pagesCount = ceil( $items_count / $maxonpage );
			
			if( $page > $pagesCount )
				$page = $pagesCount;
			
			$startFrom = ( $page - 1 ) * $maxonpage;
		}
		
		$c = 0;
		$count = $maxonpage;
		$ret = array();
		foreach( $elems as $r ) {
			if( $c++ < $startFrom )
				continue;
			$properties = array();
			$aa = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$r['id']." ORDER BY `id` ASC" );
			while( $rr = @mysql_fetch_assoc( $aa ) )
				$properties[$rr['id']] = $rr;
				
			$r['properties'] = $properties;
			@array_push( $ret, $r );
			$count--;
			if( !$count )
				break;
		}
		
		return $ret;
	}
	
	function getItemsSpecial( $catalog, $catalogData, $vendor, $options )
	{
		global $main, $mysql, $lang, $query, $utils;
		
		$currencyTypes = $main->listings->getListingElementsArray( 21, 0, false, '', true );
		$subCatalogies = $main->listings->getListingElementsArraySpec( 22, "`order` DESC, `id` ASC", "", $catalog, true );
		
		$where = "`view`=1 AND `id`<>4012";
		if( $catalogData['root'] ) {
			$where .= " AND `r`=".$catalogData['root']." AND `sub_r`=".$catalogData['id'];
		} else {
			$where .= " AND `r`=".$catalogData['id'];
			$w = "";
			foreach( $subCatalogies as $v ) {
				if( isset( $options['f_'.$v['id']] ) && $options['f_'.$v['id']] )
					$w .= ( $w ? " OR " : "" )."`sub_r`=".$v['id'];
			}
			if( $w )
				$where .= " AND (".$w.")";
			else 
				$where .= " AND (`sub_r`=0)";
		}
		
		$elems = array();
		$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE ".$where." ORDER BY `id` ASC, `order` ASC, `".$this->getCorrentSortValue( $options['sort'] )."` ".$options['dir'] );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			
			$price = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_prices` WHERE `tovar_id`=".$r['id']." ORDER BY `id` ASC, `date` DESC" );
			if( $price ) {
				$r['price'] = $price['price'];
				$r['price_money'] = $currencyTypes[$price['currency_type']];
				
			} else 
				continue;
				
			if( $options['start_price'] && $r['price'] < $options['start_price'] )
				continue;
			if( $options['end_price'] && $r['price'] > $options['end_price'] )
				continue;
				
			$properties = array();
			$aa = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$r['id'] );
			while( $rr = @mysql_fetch_assoc( $aa ) )
				$properties[$rr['id']] = $rr;
				
			if( $vendor ) {
				$r_vendor = $this->getElementByData( $properties, "prop_id", 5 );
				if( $r_vendor['value'] != $vendor )
					continue;
			}
			
			$r['properties'] = $properties;
				
			array_push( $elems, $r );
		}
		
		return $elems;
	}
	
	function getItem( $id )
	{
		global $main, $mysql, $lang, $query, $utils, $admin;
		
		$r = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE ".( isset( $admin ) && $admin->auth ? "" : "`view`=1 AND " )."`id`=".$id );
		
		return $r ? $r : null;
	}
	
	function parseExternalRequest()
	{
		global $query, $main, $utils, $lang, $mysql;
		
		$type = $query->gp( "localtype" );
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		if( !$curSid )
			return;
		
		switch( $type ) {
			case 1: // Выдать блок с цветами определенного товара
			
				$id = $query->gp( "id" );
				$data = $this->getItem( $id );
				if( !$id || !$data )
					return "";
				$currencyTypes = $main->listings->getListingElementsArray( 21, 0, false, '', true );
				$ColorsList = $main->listings->getListingElementsArraySpec( 2, "`order` DESC, `id` ASC", "", -1, true );
				$props = $main->properties->getPropertiesOfGood( $id );
				
				$colors = "";
				foreach( $props as $p ) {
					if( $p['prop_id'] != 48 || !$p['value'] )
						continue;
				
					$price = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_prices` WHERE `tovar_id`=".$id." AND `property`=48 AND `prop_value`=".$p['value']." ORDER BY `date` DESC" );
					if( !$price )
						$price = $utils->digitsToRazryadi( $data['price'] )." ".$lang->gp( $currencyTypes[$data['currency_type']]['value'], true );
					else 
						$price = $utils->digitsToRazryadi( $price['price'] )." ".$lang->gp( $currencyTypes[$price['currency_type']]['value'], true );
				
					$titleName = is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'];
			
					$colors .= "<img src=\"https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'].( isset( $ColorsList[$p['value']]['image'] ) && $ColorsList[$p['value']]['image'] ? "files/upload/listings/".$ColorsList[$p['value']]['image'] : "images/no_color.png" )."\" title=\"".$titleName.", ".$lang->gp( 207, true )." ".$price."\" onclick=\"setNewColor( '".$p['value']."', '".$id."' );\" />";
			
				}

				return $colors;
				
			case 2: // Взять блок с товаром для всплывающего окна на сайте
			
				$id = $query->gp( "id" );
				$itemData = $this->getItem( $id );
				if( !$itemData )
					return "";
					
				return $this->showGoodElement( $id, $itemData, $itemData['sub_r'] ? $itemData['sub_r'] : $itemData['r'], $itemData['vendor'], array(), "", true, null, null );
		}
	}

	function getItemPriceWithProperty( $id, $property, $prop_value )
	{
		global $main, $mysql, $lang, $query, $utils;
		
		$data = $this->getItem( $id );
		
		if( !$data )
			return null;
		
		$value = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_prices` WHERE `tovar_id`=".$id." AND `property`='".$property."' AND `prop_value`='".$prop_value."' ORDER BY `date` DESC" );
                
                if( $value )
                    $data['price'] = $value['price'];
                
                $discount = $main->modules->gmi( "discount" )->getDiscountForGood( $data );
                $data['discount_price'] = $discount ? $data['price'] - ( $data['price'] / 100 * $discount['percent'] ) : 0;
                $data['discount_asis'] = $data['price'] - ceil( $data['discount_price'] );
		
		return $data['discount_price'] ? ceil( $data['discount_price'] ) : $data['price'];
	}
	
	function increaseItemRating( $id )
	{
		global $mysql;
		
		$data = $this->getItem( $id );
		if( $data )
			$mysql->mu( "UPDATE ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` SET `popular`=".( $data['popular'] + 1 )." WHERE `id`=".$id );
	}
	
	//
	//
	//
	
	function getSopli( $data )
	{
		global $main, $mysql, $lang, $query, $utils;
		
		$catalog = $query->gp( "catalog" );
		$vendor = $query->gp( "vendor" );
		
		$catalogData = $data || $catalog ? $main->listings->getListingElementById( 22, $data ? $data['r'] : $catalog, true ) : "";
		$vendorData = ( $data && $data['vendor'] ) || $vendor ? $main->listings->getListingElementById( 1, $data && $data['vendor'] ? $data['vendor'] : $vendor, true ) : "";
		
		$catalogDataRoot = !$data && $catalogData['root'] ? $main->listings->getListingElementById( 22, $catalogData['root'], true ) : '';
		
		$t = "<a href='".$mysql->settings['local_folder']."'>".$main->modules->gmi( "index" )->getName()."</a> / ";
		
		if( $catalogDataRoot ) 
			$t .= "<a href=\"https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'].$this->dbinfo['local'].$catalogDataRoot['id']."\">".$lang->gp( $catalogDataRoot['value'], true )."</a> / ";
		
		if( $data && $data['sub_r'] ) {
			$catalogDataRoot = $main->listings->getListingElementById( 22, $data['sub_r'], true );
			$t .= "<a href=\"https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'].$this->dbinfo['local'].$data['sub_r']."\">".$lang->gp( $catalogDataRoot['value'], true )."</a> / ";
		}
		
		if( $vendorData ) {
			$t .= "<a href=\"https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'].$this->dbinfo['local'].( $data ? ( $data['sub_r'] ? $data['sub_r'] : $data['r'] ) : $catalog ).( $data || $vendor ? "/vendor".( $data['vendor'] ? $data['vendor'] : $vendor ) : "" )."\">".$lang->gp( $vendorData['value'], true )."</a> / ";
		}
		
		$t .= "<span>".str_replace( '?', "-", $data ? $data['name'] : $lang->gp( $catalogData['value'], true ) )."</span>";
		
		return $t;
	}
	
	function showGoodElement( $itemData, $catalog = 0 )
	{
		global $mysql, $utils, $main, $query, $lang;
		
		$ColorsList = $main->listings->getListingElementsArraySpec( 3, "`order` DESC, `id` ASC", "", 0, true );
		$faces = $main->listings->getListingElementsArraySpec( 11, "`order` DESC, `id` ASC", "", 0, true );
		$faces_self = array();
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."blobs` WHERE ".( $main->users->auth ? "`user`=".$main->users->userArray['id'] : "`sid`='".$main->users->sid."'" ) );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$faces_self[$r['id']] = $r;
		}
		
		$tovarImage = $this->getElementByData( $itemData['properties'], "prop_id", $this->options['output'] == 2 ? 15 : 14 );
			
		$link = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog".( $catalog && $catalog > 1 ? $catalog : '' )."/show".$itemData['id']."/".strtolower( $utils->translitIt( $itemData['name'] ) )."_".strtolower( $utils->translitIt( $itemData['article'] ) ).".html";
			
		$colors = "";
		$i = 1;
		$p = $this->getElementByData( $itemData['properties'], "prop_id", 1 );
		if( $p && $p['value'] ) {
			$price = $utils->digitsToRazryadi( $itemData['price'] )." руб.";
			$titleName = "Артикул ".$itemData['article'].", ".( is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'] );
			$colors .= "
				<div class='one selected' onclick=\"
						$( this ).parent().find( '.one' ).each(function() { $( this ).removeClass( 'selected' ); });
						$( this ).addClass( 'selected' );
						var ar = new Array();
					". ( $tovarImage ? "
						ar[0] = '".$mysql->settings['local_folder']."files/upload/goods/thumbs/".$tovarImage['value']."';
						ar[1] = '".$this->imageBackSize."';
						ar[2] = '".$this->imageBackPos."';
					" : "
						ar[0] = '".$mysql->settings['local_folder']."images/no_image.png';
						ar[1] = '".$this->NoimageBackSize."';
						ar[2] = '".$this->NoimageBackPos."';
					" )."
						ar[3] = '".$itemData['name']."';
						ar[4] = '".$utils->digitsToRazryadi( $itemData['price'] )." <img src=\'/images/ruble.png\' />';
						ar[5] = 'https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog/show".$itemData['id']."/".strtolower( $utils->translitIt( $itemData['name'] ) )."_".strtolower( $utils->translitIt( $itemData['article'] ) ).".html';
						replaceGoodsData( ar, 'catalogGoods', ".$itemData['id'].", ".$this->options['output']." );
					\">
					<div class='colorname'><img src='/images/smarup.png' />".( is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'] )."</div>
					<div class='inside'>
						<div class='back' style='".( $ColorsList[$p['value']]['image'] ? "background: url(".$mysql->settings['local_folder']."files/upload/listings/".$ColorsList[$p['value']]['image'].") no-repeat; background-size: cover; background-position: 50% 50%;" : "background-color: #".$ColorsList[$p['value']]['additional_info'].";" )."' title='".$titleName.", стоимость ".$price."'></div>
					</div>
				</div>
			";
		}
			
		foreach( $itemData['colors'] as $rr ) {
				if( $i >= 4 ) {
					$colors .= "
					<div class='one' onclick=\"urlmove('".$link."');\">
						<div class='inside'>
							<div class='back' style='background-color: #c1c1c1;' title='Больше цветов'><img src='/images/cross.png' /></div>
						</div>
					</div>
					";
					break;
				}
				$cprops = $rr['properties'];
				$ctovarImage = $this->getElementByData( $cprops, "prop_id", 14 );
				$color = $rr['color'];
				if( $color ) {
					$price = $utils->digitsToRazryadi( $rr['price'] )." руб.";
					$titleName = "Артикул ".$rr['article'].", ".( $color ? $lang->gp( $ColorsList[$color]['value'], true ) : $color );
					$colors .= "
					<div class='one' onclick=\"
						$( this ).parent().find( '.one' ).each(function() { $( this ).removeClass( 'selected' ); });
						$( this ).addClass( 'selected' );
						var ar = new Array();
					".( $ctovarImage ? "
						ar[0] = '".$mysql->settings['local_folder']."files/upload/goods/thumbs/".$ctovarImage['value']."';
						ar[1] = '".$this->imageBackSize."';
						ar[2] = '".$this->imageBackPos."';
					" : "
						ar[0] = '".$mysql->settings['local_folder']."images/no_image.png';
						ar[1] = '".$this->NoimageBackSize."';
						ar[2] = '".$this->NoimageBackPos."';
					" )."
						ar[3] = '".( $rr['name'] ? $rr['name'] : $itemData['name'] )."';
						ar[4] = '".$utils->digitsToRazryadi( $rr['price'] )." <img src=\'/images/ruble.png\' />';
						ar[5] = 'https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog/show".$rr['id']."/".strtolower( $utils->translitIt( $rr['name'] ? $rr['name'] : $itemData['name'] ) )."_".strtolower( $utils->translitIt( $rr['article'] ) ).".html';
						replaceGoodsData( ar, 'catalogGoods', ".$itemData['id'].", ".$this->options['output']." );
					\">
						<div class='colorname'><img src='/images/smarup.png' />".( $color ? $lang->gp( $ColorsList[$color]['value'], true ) : $color )."</div>
						<div class='inside'>
							<div class='back' style='".( $ColorsList[$color]['image'] ? "background: url(".$mysql->settings['local_folder']."files/upload/listings/".$ColorsList[$color]['image'].") no-repeat; background-size: cover; background-position: 50% 50%;" : "background-color: #".$ColorsList[$color]['additional_info'].";" )."' title='".$titleName.", стоимость ".$price."'></div>
						</div>
					</div>
					";
				}
				$i++;
		}
		
		$selected_selfface = isset( $_COOKIE['selected_selfface'] ) && isset( $faces_self[$_COOKIE['selected_selfface']] ) && $_COOKIE['selected_selfface'] ? $_COOKIE['selected_selfface'] : 0;
		$selected_face = !$selected_selfface ? ( isset( $_COOKIE['selected_face'] ) && $_COOKIE['selected_face'] ? $_COOKIE['selected_face'] : 0 ) : 0;
		if( !$selected_selfface && !$selected_face ) {
			foreach( $faces as $v ) {
				$selected_face = $v['id'];
				break;
			}
		}
			
                $discount = $main->modules->gmi( "discount" )->getDiscountForGood( $itemData );
                $itemData['discount_price'] = $discount ? $itemData['price'] - ( $itemData['price'] / 100 * $discount['percent'] ) : 0;
                
		$price = $utils->digitsToRazryadi( $itemData['discount_price'] ? ceil( $itemData['discount_price'] ) : $itemData['price'] )." <img src='/images/ruble.png' />".( $discount ? "<div class='old_price'>".$itemData['price']." Р</div>" : "" );
                
                $discountBlock = $discount ? "<div class='discounterHover'><div class='h'></div><div>Скидка<span>".$discount['percent']."<label>%</label></span></div></div>" : "";
		
		$if = $main->modules->gmi( "fitting" )->isInFitting( $itemData['id'] );
		$hit = $this->getElementByData( $itemData['properties'], "prop_id", 18 );
		$novinka = isset( $itemData['novinka'] ) && $itemData['novinka'] ? true : false;

        foreach ($itemData['properties'] as $value){
            if($value['prop_id'] == 2 and $value['value'] == 4){
                    $sun = true;
            }
        }
		/*$faceBackgroundImage = '';
		$fbLeft = 58.5;
		$fbTop = 79;
		if( $selected_selfface ) {
			$faceBackgroundImage = $mysql->settings['local_folder']."files/upload/usersData/".$faces_self[$selected_selfface]['data'];
			$fbLeft = $faces_self[$selected_selfface]['left'];
			$fbLeft -= 58.5;
			$fbLeft = 73.66 + $fbLeft;
			$fbTop = $faces_self[$selected_selfface]['top'];
			$fbTop -= 79;
			$fbTop = 178.9 + $fbTop;
		} else {
			$faceBackgroundImage = $mysql->settings['local_folder']."files/upload/listings/".$faces[$selected_face]['image'];
			$fbLeft -= 58.5;
			$fbLeft = 73.66 + $fbLeft;
			$fbTop -= 79;
			$fbTop = 178.9 + $fbTop;
		}*/
		$faceBackgroundImage = '';
		$fbLeft = 66.5;
                $fbLeft_or = 0;
		$fbTop = 166;
		$fbWidth = 0;
		$fbHeight = 0;
		$fbDeg = 0;
		if( $selected_selfface ) {
			$faceBackgroundImage = $mysql->settings['local_folder']."files/upload/usersData/".$faces_self[$selected_selfface]['data'];
			$fbLeft = $faces_self[$selected_selfface]['left'];
			$fbTop = $faces_self[$selected_selfface]['top'];
                        //$fbTop -= 24.54 / 2;
			$fbWidth = $faces_self[$selected_selfface]['width'] ? $faces_self[$selected_selfface]['width'] : 0;
			$fbDeg = $faces_self[$selected_selfface]['deg'] ? $faces_self[$selected_selfface]['deg'] : 0;
		} else {
			$faceBackgroundImage = $mysql->settings['local_folder']."files/upload/listings/".$faces[$selected_face]['image'];
			$fbLeft -= 66.5;
			$fbLeft = 66 + $fbLeft;
			$fbTop -= 166;
			$fbTop = 130 + $fbTop;
                        $fbWidth = 147;
                        $fbHeight = 76;
		}
		if($sun == true){
            $t = "
			<div class='item good_item".( $this->options['output'] == 2 ? "_faces" : "" )."' id='cataloggitem_".$itemData['id']."'><div class='top_border'></div><img src='/images/good_home".( $if ? "_s" : "" ).".png' class='primerka".( $if ? " primerka_selected" : "" )."' data-id='".$itemData['id']."' title=\"Добавить в домашнюю примерку\" /> ".$discountBlock."
				".( $novinka ? "<div class='novinka'>Новинка</div>" : "" )."
				".( $hit ? "<div class='hit".( $novinka ? " after_novinka" : "" )."'>Хит</div>" : "" )."
				".( $this->options['output'] == 1 ? "
					<div class='preview' id='catalogGoodsPic_".$itemData['id']."' style=\"background: url(".$mysql->settings['local_folder']."files/upload/goods/".$tovarImage['value'].") no-repeat; background-size: ".$this->imageBackSize."; background-position: ".$this->imageBackPos.";\">
						<div class='primerit_online' onclick=\"showfixed_load();\">".$lang->gp( 102 )."</div>
						<div class='choose_lenses_online' style='right:90px;' onclick=\"showFixedbasket( 300, ".$itemData['id']." );\">".$lang->gp( 170 )."</div>
						<div class='action_field' onclick=\"urlmove($('#catalogGoodsLink_".$itemData['id']."').attr( 'href' ));\"></div>
					</div>
					" : ( true ? "<div class='preview' id='catalogGoodsPic_".$itemData['id']."'>
                                                <div class='selfOchkiPreview'><img src='".$faceBackgroundImage."' class='face' /><img class='ochki' src='".$mysql->settings['local_folder']."files/upload/goods/".$tovarImage['value']."' style='top: ".$fbTop."px; left: ".$fbLeft."px; width: ".$fbWidth."px;".( $fbDeg ? " transform: rotate(".$fbDeg."deg);" : "" )."' /></div>
						<div class='primerit_online' onclick=\"if( $( '.top_line_white .fitting_online' ).is( ':visible' ) ) { $( '.fixed_load' ).fadeIn( 400 ); } else { urlmove('/fitting_online'); }\">".$lang->gp( 102 )."</div>
						<div class='kupit_online' onclick=\"showFixedbasket( 300, ".$itemData['id']." );\">".$lang->gp( 103 )."</div>
						<div class='choose_lenses_online' onclick=\"urlmove('".$link."#lenses');\">".$lang->gp( 104 )."</div>
						<div class='action_field' onclick=\"urlmove($('#catalogGoodsLink_".$itemData['id']."').attr( 'href' ));\"></div>
					</div>" : "
					<div class='preview' id='catalogGoodsPic_".$itemData['id']."' style=\"background: url(".$faceBackgroundImage.") no-repeat; background-size: ".$this->imageBackSize_face."; background-position: center bottom;\">
						<div class='primerit_online' onclick=\"if( $( '.top_line_white .fitting_online' ).is( ':visible' ) ) { $( '.fixed_load' ).fadeIn( 400 ); } else { urlmove('/fitting_online'); }\">".$lang->gp( 102 )."</div>
						<div class='kupit_online' onclick=\"showFixedbasket( 300, ".$itemData['id']." );\">".$lang->gp( 103 )."</div>
						<div class='choose_lenses_online' onclick=\"urlmove('".$link."#lenses');\">".$lang->gp( 104 )."</div>
						<div class='action_field' onclick=\"urlmove($('#catalogGoodsLink_".$itemData['id']."').attr( 'href' ));\"></div>
						<img src='".$mysql->settings['local_folder']."files/upload/goods/".$tovarImage['value']."' data-left='".$fbLeft_or."' style='top: ".$fbTop."px;".( $fbWidth ? " width: ".$fbWidth."px; max-width: none;" : "" ).( $fbDeg ? " transform: rotate(".$fbDeg."deg);" : "" )."' />
					</div>
					" ) )."
				<a href='".$link."' id='catalogGoodsLink_".$itemData['id']."'>
				<h2 class='good_title' id='catalogGoodsName_".$itemData['id']."'>".$itemData['name']."</h2>
				<h3 class='good_price' id='catalogGoodsPrice_".$itemData['id']."'>".$price."</h3></a>
				<div class='colors'>
					".$colors."
				</div>
			</div>
		";
        }else{
		$t = "
			<div class='item good_item".( $this->options['output'] == 2 ? "_faces" : "" )."' id='cataloggitem_".$itemData['id']."'><div class='top_border'></div><img src='/images/good_home".( $if ? "_s" : "" ).".png' class='primerka".( $if ? " primerka_selected" : "" )."' data-id='".$itemData['id']."' title=\"Добавить в домашнюю примерку\" /> ".$discountBlock."
				".( $novinka ? "<div class='novinka'>Новинка</div>" : "" )."
				".( $hit ? "<div class='hit".( $novinka ? " after_novinka" : "" )."'>Хит</div>" : "" )."
				".( $this->options['output'] == 1 ? "
					<div class='preview' id='catalogGoodsPic_".$itemData['id']."' style=\"background: url(".$mysql->settings['local_folder']."files/upload/goods/".$tovarImage['value'].") no-repeat; background-size: ".$this->imageBackSize."; background-position: ".$this->imageBackPos.";\">
						<div class='primerit_online' onclick=\"showfixed_load();\">".$lang->gp( 102 )."</div>
						<div class='kupit_online' onclick=\"showFixedbasket( 300, ".$itemData['id']." );\">".$lang->gp( 103 )."</div>
						<div class='choose_lenses_online' onclick=\"urlmove('".$link."#lenses');\">".$lang->gp( 104 )."</div>
						<div class='action_field' onclick=\"urlmove($('#catalogGoodsLink_".$itemData['id']."').attr( 'href' ));\"></div>
					</div>
					" : ( true ? "<div class='preview' id='catalogGoodsPic_".$itemData['id']."'>
                                                <div class='selfOchkiPreview'><img src='".$faceBackgroundImage."' class='face' /><img class='ochki' src='".$mysql->settings['local_folder']."files/upload/goods/".$tovarImage['value']."' style='top: ".$fbTop."px; left: ".$fbLeft."px; width: ".$fbWidth."px;".( $fbDeg ? " transform: rotate(".$fbDeg."deg);" : "" )."' /></div>
						<div class='primerit_online' onclick=\"if( $( '.top_line_white .fitting_online' ).is( ':visible' ) ) { $( '.fixed_load' ).fadeIn( 400 ); } else { urlmove('/fitting_online'); }\">".$lang->gp( 102 )."</div>
						<div class='kupit_online' onclick=\"showFixedbasket( 300, ".$itemData['id']." );\">".$lang->gp( 103 )."</div>
						<div class='choose_lenses_online' onclick=\"urlmove('".$link."#lenses');\">".$lang->gp( 104 )."</div>
						<div class='action_field' onclick=\"urlmove($('#catalogGoodsLink_".$itemData['id']."').attr( 'href' ));\"></div>
					</div>" : "
					<div class='preview' id='catalogGoodsPic_".$itemData['id']."' style=\"background: url(".$faceBackgroundImage.") no-repeat; background-size: ".$this->imageBackSize_face."; background-position: center bottom;\">
						<div class='primerit_online' onclick=\"if( $( '.top_line_white .fitting_online' ).is( ':visible' ) ) { $( '.fixed_load' ).fadeIn( 400 ); } else { urlmove('/fitting_online'); }\">".$lang->gp( 102 )."</div>
						<div class='kupit_online' onclick=\"showFixedbasket( 300, ".$itemData['id']." );\">".$lang->gp( 103 )."</div>
						<div class='choose_lenses_online' onclick=\"urlmove('".$link."#lenses');\">".$lang->gp( 104 )."</div>
						<div class='action_field' onclick=\"urlmove($('#catalogGoodsLink_".$itemData['id']."').attr( 'href' ));\"></div>
						<img src='".$mysql->settings['local_folder']."files/upload/goods/".$tovarImage['value']."' data-left='".$fbLeft_or."' style='top: ".$fbTop."px;".( $fbWidth ? " width: ".$fbWidth."px; max-width: none;" : "" ).( $fbDeg ? " transform: rotate(".$fbDeg."deg);" : "" )."' />
					</div>
					" ) )."
				<a href='".$link."' id='catalogGoodsLink_".$itemData['id']."'>
				<h2 class='good_title' id='catalogGoodsName_".$itemData['id']."'>".$itemData['name']."</h2>
				<h3 class='good_price' id='catalogGoodsPrice_".$itemData['id']."'>".$price."</h3></a>
				<div class='colors'>
					".$colors."
				</div>
			</div>
		";}
		return $t;
	}
	
	function combinePropertiesArray( $s, &$d )
	{
		foreach( $d as $kk => $vv ) {
			if( $vv['prop_id'] == 16 )
				unset( $d[$kk] );
		}
		
		foreach( $s as $k => $v ) {
			foreach( $d as $kk => $vv ) {
				if( $vv['prop_id'] == $v['prop_id'] ) {
					$d[$kk] = $v;
					break;
				}
			}
			if( $v['prop_id'] == 16 )
				$d[$k] = $v;
		}
	}
	
	function showTovar( $id )
	{
		global $main, $mysql, $lang, $query, $utils;
                
                $this->goodtoshow['properties'] = $main->properties->getPropertiesOfGood( $this->goodtoshow['id'] );
                
                $data = $this->goodtoshow;
		$rdata = 0;
		if( $data['root'] ) {
			$rdata = $this->getItem( $data['root'] );
			$rdata['properties'] = $main->properties->getPropertiesOfGood( $rdata['id'] );
			$rdata['colors'] = $this->getColorArrayOfGood( $rdata['id'] );
			$tt = $rdata;
			$rdata['article'] = $data['article'];
			$rdata['price'] = $data['price'];
			$rdata['id'] = $data['id'];
			$rdata['date'] = $data['date'];
			$rdata['root'] = $data['root'];
			$this->combinePropertiesArray( $data['properties'], $rdata['properties'] );
			$data = $rdata;
			$rdata = $tt;
		} else {
			$data['colors'] = $this->getColorArrayOfGood( $data['id'] );
		}
		
		$catalog = $query->gp( "catalog" );
		//return $catalog;
		$catalogData = $main->listings->getListingElementById( 1, $catalog, true );
		$options = $this->options;
                
                //
                // From showitem
                //
		$faces_self = array();
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."blobs` WHERE ".( $main->users->auth ? "`user`=".$main->users->userArray['id'] : "`sid`='".$main->users->sid."'" ) );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$faces_self[$r['id']] = $r;
		}
                
                $selected_selfface = $_COOKIE['output'] == 2 && isset( $_COOKIE['selected_selfface'] ) && isset( $faces_self[$_COOKIE['selected_selfface']] ) && $_COOKIE['selected_selfface'] ? $_COOKIE['selected_selfface'] : 0;
                $faceBackgroundImage = '';
		$fbLeft = 66.5;
                $fbLeft_or = 0;
		$fbTop = 166;
		$fbWidth = 0;
		$fbHeight = 0;
		$fbDeg = 0;
		if( $selected_selfface ) {
			$faceBackgroundImage = $mysql->settings['local_folder']."files/upload/usersData/".$faces_self[$selected_selfface]['data'];
			$fbLeft = $faces_self[$selected_selfface]['left'];
                        //$fbLeft_or = $fbLeft;
			//$fbLeft -= 66.5;
			//$fbLeft = 73.66 + $fbLeft;
			$fbTop = $faces_self[$selected_selfface]['top'];
			//$fbTop -= 166;
			//$fbTop = 195 + $fbTop;
			$fbWidth = $faces_self[$selected_selfface]['width'] ? $faces_self[$selected_selfface]['width'] : 0;
			//$fbHeight = $faces_self[$selected_selfface]['height'] ? $faces_self[$selected_selfface]['height'] : 0;
			$fbDeg = $faces_self[$selected_selfface]['deg'] ? $faces_self[$selected_selfface]['deg'] : 0;
		}
                //
                //
                //
                
		$this->insertSeenItem( $id );

		$properties = $main->properties->getCurrentList();
		$vendors = $main->listings->getListingElementsArray( 7, 0, false, '', true );
		$ColorsList = $main->listings->getListingElementsArraySpec( 3, "`order` DESC, `id` ASC", "", 0, true );
		
		$props = $data['properties'];
                
                $discount = $main->modules->gmi( "discount" )->getDiscountForGood( $data );
                $data['discount_price'] = $discount ? $data['price'] - ( $data['price'] / 100 * $discount['percent'] ) : 0;
                
		$price = $utils->digitsToRazryadi( $data['discount_price'] ? $data['discount_price'] : $data['price'] )." <img src='/images/big_ruble.png'>";
                
                
		
                $tovarImage = $selected_selfface ? $this->getElementByData( $data['properties'], "prop_id", $_COOKIE['output'] == 2 ? 15 : 14 ) : '';
		$preview = $this->getElementByData( $props, "prop_id", 14 );
		$preview = $preview ? $preview['value'] : '';
		
		$own_keywords = $this->getElementByData( $props, "prop_id", 13 );
		$own_desc = $this->getElementByData( $props, "prop_id", 12 );
        foreach ($props as $value){
            if($value['prop_id'] == 2 and $value['value'] == 4){
                $sun = true;
            }
        }
		$usedName = str_replace( '"', "", str_replace( "&quot;", "", $data['name'] ) );
		$simple_desc = $usedName.( $data['article'] && !stristr( $data['name'], $data['article'] ) ? " [".$data['article']."]" : "" )." с доставкой по Москве в магазине I-OPTIC.RU | Купить ".$usedName.". ".$lang->gp( $vendors[$brand]['value'], true ).".";
		
		$simple_keywords = $lang->gp( $vendors[$brand]['value'], true ).", ".$vendors[$brand]['additional_info'].", ".$catalogData['additional_info'].", ".$catalogData['additional_info']." ".$lang->gp( $vendors[$brand]['value'], true ).", купить ".$lang->gp( $vendors[$brand]['value'], true );
		
		$metas = $main->modules->gmi( "metas" );
		$main->templates->setTitle( $lang->gp( $catalogData['value'], true )." оправы", true );
		$main->templates->setTitle( $data['name']." по наилучшей цене", true );
		
		$metas->updateMeta( "description", !$own_desc ? $simple_desc : strip_tags( $own_desc['value'] ) );
		if( !$own_keywords )
			$metas->updateMeta( "keywords", $simple_keywords );
		else 
			$metas->updateMeta( "keywords", strip_tags( $own_keywords['value'] ) );
		$metas->updateMeta( "searchtitle", $main->templates->title );
		$metas->updateMeta( 'og:url', "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."?".$this->dbinfo['local']."=".( $data['sub_r'] ? $data['sub_r'] : $data['r'] )."&show=".$data['id'] );
		$metas->updateMeta( 'og:title', $main->templates->title );
		$metas->updateMeta( 'og:description', !$own_desc ? $simple_desc : strip_tags( $own_desc['value'] ) );
		if( $preview )
			$metas->updateMeta( 'og:image', "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."files/upload/goods/".$preview );
		
		$colors = "";
		$currentColorName = '';
		$ColorstoParams = '';
		$p = $this->getElementByData( $rdata ? $rdata['properties'] : $data['properties'], "prop_id", 1 );
		if( $p && $p['value'] ) {
			$price = $utils->digitsToRazryadi( $rdata ? $rdata['price'] : $data['price'] )." руб.";
			$currentColorName = $rdata ? "" : ( is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'] );
			$titleName = "Артикул ".( $rdata ? $rdata['article'] : $data['article'] ).", ".( is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'] );
			$colors .= ( !$rdata ? "" : "<a href='".$mysql->settings['local_folder']."catalog/show".$rdata['id']."/".strtolower( $utils->translitIt( $rdata['name'] ) )."_".strtolower( $utils->translitIt( $rdata['article'] ) ).".html'>" )."
				<div class='one".( $rdata ? "" : " selected" )."'>
					<div class='inside'>
						<div class='back' style='".( $ColorsList[$p['value']]['image'] ? "background: url(".$mysql->settings['local_folder']."files/upload/listings/".$ColorsList[$p['value']]['image'].") no-repeat; background-size: cover; background-position: 50% 50%;" : "background-color: #".$ColorsList[$p['value']]['additional_info'].";" )."' title='".$titleName.", стоимость ".$price."'></div>
					</div>
				</div>".( !$rdata ? "" : "</a>" )."
			";
			$ColorstoParams .= ( $ColorstoParams ? ", " : "" ).( is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'] );
		}
			
		foreach( $data['colors'] as $rr ) {
				$cprops = $rr['properties'];
				$color = $rr['color'];
				if( $color ) {
					$price = $utils->digitsToRazryadi( $rr['price'] )." руб.";
					$currentColorName = $rr['id'] == $data['id'] ? ( $color ? $lang->gp( $ColorsList[$color]['value'], true ) : $color ) : $currentColorName;
					$titleName = "Артикул ".$rr['article'].", ".( $color ? $lang->gp( $ColorsList[$color]['value'], true ) : $color );
					$colors .= "
					".( $rr['id'] == $data['id'] ? "" : "<a href='".$mysql->settings['local_folder']."catalog/show".$rr['id']."/".strtolower( $utils->translitIt( $rr['name'] ? $rr['name'] : $data['name'] ) )."_".strtolower( $utils->translitIt( $rr['article'] ) ).".html'>" )."
					<div class='one".( $rr['id'] == $data['id'] ? " selected" : "" )."'>
						<div class='inside'>
							<div class='back' style='".( $ColorsList[$color]['image'] ? "background: url(".$mysql->settings['local_folder']."files/upload/listings/".$ColorsList[$color]['image'].") no-repeat; background-size: cover; background-position: 50% 50%;" : "background-color: #".$ColorsList[$color]['additional_info'].";" )."' title='".$titleName.", стоимость ".$price."'></div>
						</div>
					</div>".( $rr['id'] == $data['id'] ? "" : "</a>" )."
					";
					$ColorstoParams .= ( $ColorstoParams ? ", " : "" ).( $color ? $lang->gp( $ColorsList[$color]['value'], true ) : $color );
				}
		}
		
		$restInfo = "
		<div class='param param_small'>".$lang->gp( 81 ).": <span>".$data['article']."</span></div>
		".( $data['vendor'] ? "<div class='param param_small'>".$lang->gp( 82 ).": <span>".$lang->gp( $vendors[$data['vendor']]['value'], true )."</span></div>" : "" )."
		<div class='param param_small'>".$lang->gp( 45, true ).": <span>".$ColorstoParams."</span></div>
		";
		foreach( $properties as $prop_id => $prop_data ) {
			
			$elem = $this->getElementByData( $props, "prop_id", $prop_id );
			if( $prop_id == 1 || $prop_id == 2 || ( $prop_id >= 7 && $prop_id <= 18 ) )
				continue;
			$value = "";
			if( $prop_data['type'] == 3 ) {
				$l_elems = $main->listings->getListingElementsArraySpec( $prop_data['source'], "`order` DESC, `id` ASC", "", 0, true );
				foreach( $props as $p ) {
					if( $p['prop_id'] != $prop_id || !isset( $l_elems[$p['value']]['value'] ) || !$l_elems[$p['value']]['value'] )
						continue;
					$value .= ( $value ? ", " : "" ).$lang->gp( $l_elems[$p['value']]['value'], true );
				}
			} else if( $prop_data['type'] == 2 ) {
				$value = $main->listings->getListingElementValueById( $prop_data['source'], $elem['value'], true );
			} else if( $prop_data['type'] == 5 ) {
				foreach( $props as $p ) {
					if( $p['prop_id'] != $prop_id )
						continue;
					$value .= ( $value ? ", " : "" ).$p['value'];
				}
			} else if( $prop_data['type'] == 4 ) {
				$value = $elem['value'] == 1 ? "Да" : "Нет";
			} else
				$value = $elem['value'];
				
			if( !$value )
				continue;
				
			$restInfo .= "<div class='param param_small'>".$lang->gp( $prop_data['name'], true ).": <span>".$value."</span></div>";
		}
		
		$images = '';
		foreach( $props as $p ) {
			if( $p['prop_id'] != 16 )
				continue;
			$images .= "<div class='pics' data-big='".$mysql->settings['local_folder']."files/upload/goods/".$p['value']."'><div class='inner' style='background: #fff url(".$mysql->settings['local_folder']."files/upload/goods/thumbs/".$p['value'].") no-repeat; background-size: contain; background-position: 50% 50%;'></div></div>";
		}
		
		$fullText = $this->getElementByData( $props, "prop_id", 11 );
		$fullText = strpos( $fullText['value'], "\n" ) ? str_replace( "\n", "", $fullText['value'] ) : $fullText['value'];
			
		$mat = $this->getElementByData( $props, "prop_id", 5 );
		$mat = $mat ? $main->listings->getListingElementValueById( 5, $mat['value'], true ) : '';
		
		$size = $this->getElementByData( $props, "prop_id", 19 );
		$size = $size ? $main->listings->getListingElementValueById( 27, $size['value'], true ) : '';
		
                $discount = $main->modules->gmi( "discount" )->getDiscountForGood( $data );
                $data['discount_price'] = $discount ? $data['price'] - ( $data['price'] / 100 * $discount['percent'] ) : 0;
                
		$price = "<span>".intval( $data['discount_price'] ? ceil( $data['discount_price'] ) : $data['price'] )."</span><img src='".$mysql->settings['local_folder']."images/big_ruble.png'>";
                
                $priceadd = ( $discount ? "<div class='old_price' style='position: absolute; top: 53px; left: 20px;'>".$data['price']." Р</div>" : "" );
                
                $discountBlock = $discount ? "<div class='discounterHover'><div class='h'></div><div>Скидка<span>".$discount['percent']."<label>%</label></span></div></div>" : "";
		
		$recomendItems = $this->getRecommendItems();
		
		$if = $main->modules->gmi( "fitting" )->isInFitting( $id );
		
		$t = $main->modules->gmi( "lenses" )->getCalculateSizeFloatBlock()."
		<div class='sopli'><div class='all_lines'>
			<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span><a href='".$mysql->settings['local_folder']."catalog".( $catalog > 1 ? $catalog : "" )."/".strtolower( $utils->translitIt( $lang->gp( $catalogData['value'], true ) ) ).".html'>".$lang->gp( $catalogData['value'], true )." оправы</a><span>></span><span class='thename'>".$usedName."</span>
		</div></div>
		<div class='all_lines'><div class='thegoodtoshow'>
			<div class='images'>".$discountBlock."
				<div class='topline'></div>
				<div class='primerka".( $if ? " primerka_selected" : "" )."' data-id='".$id."'><img src='".$mysql->settings['local_folder']."images/good_home".( $if ? "_s" : "" ).".png' />".$lang->gp( 58 )."</div>
				".( !$selected_selfface ? "<div class='bigimage' style='background: url(".$mysql->settings['local_folder']."files/upload/goods/".$preview.") no-repeat; background-size: contain; background-position: 50% 50%;'><img src='".$mysql->settings['local_folder']."files/upload/goods/".$preview."' /></div>" : "<div class='bigimage'> 
                                    
                                   
                        <div class='faceview'>
                            <img src='".$faceBackgroundImage."' class='face' /><img class='ochki' src='".$mysql->settings['local_folder']."files/upload/goods/".$tovarImage['value']."' style='top: ".$fbTop."px; left: ".$fbLeft."px; width: ".$fbWidth."px;".( $fbDeg ? " transform: rotate(".$fbDeg."deg);" : "" )."' />
			</div>
                                    
                                    </div>")."
				<div class='smallones'>
					<div class='pics' data-big='".$mysql->settings['local_folder']."files/upload/goods/".$preview."'><div class='inner".( !$selected_selfface ? " inner_selected" : "" )."' style='background: #fff url(".$mysql->settings['local_folder']."files/upload/goods/thumbs/".$preview.") no-repeat; background-size: contain; background-position: 50% 50%;'></div></div>
					".$images."
					<div class='pics' data-fit=1>".$main->modules->gmi( "fitting_online" )->getInGoodBlock( $id )."</div>
					<div class='clear'></div>
				</div>
			</div>
			<div class='data'>
				<div class='inner'>
					<h1 class='goodname'>".$usedName."</h1>
					<div class='param'>
						".$lang->gp( 76 ).": <span>".$currentColorName."</span>
					</div>
					<div class='colors'>						
						".$colors."
					</div>
					".( $mat ? "<div class='param'>
						".$lang->gp( 77 ).": <span>".$mat."</span>
					</div>" : "" )."
					<div class='param' style='margin-top: 10px;'>
						Размер: <span>".( $size ? $size : '-' )."</span> <a href='#' onclick=\"showCalculate( 400 ); return false;\" class='right'>Я не знаю своего размера</a>
					</div>
					<div class='price'>
						".$price.$priceadd."
						".( !$sun ?"
						<div><a href='".$mysql->settings['local_folder']."basket?ochkiid=".$data['id']."' onclick=\"$( '.fixed_notlenses' ).fadeIn( 400 ); return false;\">
							<img src='".$mysql->settings['local_folder']."images/corzina.png'>
							".$lang->gp( 78 )."
						</a></div>" : "" )."
					</div>".( !$sun ?" 
					<div class='linsa' onclick=\"moveToElem( $( '.lenses' ) );\">
						<h3>".$lang->gp( 79 )."</h3>
						".$lang->gp( 80 )."
					</div>" : "
					<a href='".$mysql->settings['local_folder']."basket?ochkiid=".$data['id']."' style='text-decoration:none;'><div class='linsa'>
						<h3>".$lang->gp( 170 )."</h3>
					</div></a>
					")."
                                        ".'<div style=\'margin-top: 25px;\'>Поделиться</div><script src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js"></script>
<script src="//yastatic.net/share2/share.js"></script>
<div class="ya-share2" data-services="facebook,vkontakte,viber,whatsapp,telegram" style="margin-left: 0px; margin-top: 5px;"></div>
'."
				</div>
			</div>
			<div class='clear'></div>
		</div></div>
		<div class='fixed_notlenses'>
			<div class='window'>
				<div class='closer' onclick=\"$( '.fixed_notlenses' ).fadeOut( 400 );\"><img src='/images/smx.png' alt='closer' /></div>
				<div class='inner'>
					<div class='left' style='width: 40%;'><img src='/images/not.png' style='width: 90%;' /></div>
					<div class='left' style='width: 60%;'>
						<h3 class='align_left'>Мне кажется<br/>Вы забыли подобрать линзы для оправы!</h3>
						<div class='button' onclick=\"$( '.fixed_notlenses' ).fadeOut( 400 ); moveToElem( $( '.lenses' ) );\">Подобрать линзы</div>
						<a href='".$mysql->settings['local_folder']."basket?ochkiid=".$data['id']."'>Нет, я хочу купить<br/>только оправу</a>
					</div>
				</div>
			</div>
		</div>
		<div class='all_lines short_block'><div class='block_title'><span>".$lang->gp( 168, true )."</span></div>
			<div class='params'>
				<h4>".$lang->gp( 169, true )."</h4>
				<div class='columns'>".$restInfo."</div>
			</div>
			".( $fullText ? "<div class='params params_comment'>
				<h4>".$lang->gp( 169, true )."</h4>
				".$fullText."
			</div>" : "" )." 
			<div class='clear'></div>
		</div>
		
		".( $recomendItems ? "<div class='all_lines short_block'><div class='block_title'><span>".$lang->gp( 20, true )."</span></div>".$recomendItems."<div class='clear'></div></div>" : "" )."
		".$main->modules->gmi( "lenses" )->getBlock( $data['id'] )."
		<script>
			$(window).load(function()
			{
				$( '.thegoodtoshow .images .smallones .pics' ).click(function(){
                                    if( $( this ).attr( 'data-fit' ) == 1 )
                                        return;
                                        ".( $selected_selfface ? "$( '.faceview' ).fadeOut( 100 );" : "" )."
					if( $( this ).find( '.inner_selected' ).hasClass( 'inner' ) )
						return;
					if( $( this ).attr( 'data-fit' ) == 1 ) {
						return;
					}
					$( '.thegoodtoshow .images .bigimage' ).css( 'background', 'url(' + $( this ).attr( 'data-big' ) + ') no-repeat' ).css( 'background-size', 'contain' ).css( 'background-position', '50% 50%' );
					$( '.thegoodtoshow .images .smallones .pics .inner' ).each(function(){ $(this).removeClass( 'inner_selected' ); });
					$( this ).find( '.inner' ).addClass( 'inner_selected' );
				});
			});
		</script>
		";
			
		return $t;
	}
        
        function getListForOrders( $ex = 0 )
        {
            global $query, $main, $utils, $lang, $mysql;
            
            $ar = array();
            $a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `view`=1 AND `root`=0".( $ex ? " AND `id`<>".$ex : '' ) );
            while( $r = @mysql_fetch_assoc( $a ) ) {
                $ar[$r['id']] = $r;
            }
            
            return $ar;
        }
}

?>