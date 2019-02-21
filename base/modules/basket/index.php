<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulebasket extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	var $gl_dbase_string = "`shop`.";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function isThereIndexPage()
	{
		return true;
	}
        
        function getMainmenuLink()
	{
		global $main, $mysql;
                
                $c = $this->getCurrentUserBasketItemsCount();
		
		return $this->getTopWidget()."<a class='right basket_home' style='display: inline !important;' href=\"".$mysql->settings['local_folder']."order\"><img src='/images/basket.png' /><span>".$this->getName()."</span><div class='counter".( !$c ? " invisible" : "" )."'>".$c."</div></a>";
	}
	
	function getMobileMenuLink()
	{
		global $main, $mysql, $lang;
		
		return "<a class='mmenu' href=\"".$mysql->settings['local_folder']."order\">".$this->getName()."</a>";
	}
        
        function processHeaderBlock()
        {
            global $query, $main, $mysql;
            
            $catalog = $main->modules->gmi( "catalog" );
            $lenses = $main->modules->gmi( "lenses" );
            
            if( $this->isSelected() && $query->gp( "remove" ) ) {
                $curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
                $curSid = $main->users->sid;
                $r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE (`session`='".$curSid."'".( $curUser ? " OR `user`=".$curUser : "" ).") AND `id`=".$query->gp( "remove" ) );
                if( $r ) {
                    $mysql->mu( "DELETE FROM `".$mysql->t_prefix."basket` WHERE `id`=".$query->gp( "remove" ) );
                    header( "Location: /order" );
                }
                return;
            }
            
            if( $this->isSelected() && $query->gp( "ochkiid" ) ) {
                $ochkiId = $query->gp( "ochkiid" );
                $realGood = $catalog->getItem( $ochkiId );
                if( !$realGood )
                    return;
                
                $ltype = $query->gp( "t" );
		$type = $query->gp( "p" );
		$dio = $query->gp( "d" );
                $lense = $query->gp( "lense" );
                $lense = $lense ? $lense : 0;
		
		$add = $query->gp( "add" );
		
		$od_sph = $query->gp( "od_sph" );
		$os_sph = $query->gp( "os_sph" );
		$od_cyl = $query->gp( "od_cyl" );
		$os_cyl = $query->gp( "os_cyl" );
		$od_axis = $query->gp( "od_axis" );
		$os_axis = $query->gp( "os_axis" );
		$od_add = $query->gp( "od_add" );
		$os_add = $query->gp( "os_add" );
		$oculus_pd = $query->gp( "oculus_pd" );
		$oculus_pd_d = $query->gp( "oculus_pd_d" );
		$oculus_pd_s = $query->gp( "oculus_pd_s" );
		$add_move = $query->gp( "add_move" );
		$add_shadow = $query->gp( "add_shadow" );
		$add_color = $query->gp( "add_color" );
                $promo = $query->gp( "promo" );
                
                $array = array( 
			't' => $ltype,
			'p' => $type,
			'd' => $dio,
			'ochkiid' => $ochkiId,
			'add' => $add,
			'od_sph' => $od_sph,
			'os_sph' => $os_sph,
			'od_cyl' => $od_cyl,
			'os_cyl' => $os_cyl,
			'od_axis' => $od_axis,
			'os_axis' => $os_axis,
			'od_add' => $od_add,
			'os_add' => $os_add,
			'oculus_pd' => $oculus_pd,
			'oculus_pd_d' => $oculus_pd_d,
			'oculus_pd_s' => $oculus_pd_s,
			'add_move' => $add_move,
			'add_shadow' => $add_shadow,
			'add_color' => $add_color
		);
                
                $lenses = $lenses->getLenseWithTypes( $dio, $type, $ltype, $lense );
                if( $add && $lense ) {
			$prices = $main->listings->getListingElementsArray( 13, $type, false, '', true );
			if( isset( $prices[$add] ) ) {
				$lenses[$lense]['price'] += $prices[$add]['additional_info'];
			}
		}
                
                $discount = $main->modules->gmi( "discount" )->getDiscountForGood( $realGood );
                $realGood['discount_price'] = $discount ? $realGood['price'] - ( $realGood['price'] / 100 * $discount['percent'] ) : 0;
                $realGood['discount_asis'] = $realGood['price'] - ceil( $realGood['discount_price'] );
                
                $main_summa = $lense ? intval( $lenses[$lense]['price'] ) : 0;
		$main_summa += intval( $realGood['discount_price'] ? ceil( $realGood['discount_price'] ) : $realGood['price'] );
                
                $options = json_encode( $array );
                
                $curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
                $curSid = $main->users->sid;
                
                $r = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."basket` WHERE (`session`='".$curSid."'".( $curUser ? " OR `user`=".$curUser : "" ).") AND `ochkiid`=".$ochkiId." AND `order`=0 AND `lense`=0" );
                if( $r )
                    $this->removeFromBasket( $ochkiId, $curUser, $curSid );
                    
		$mysql->mu( "INSERT INTO `".$mysql->t_prefix."basket` VALUES(
						0,
						".$curUser.",
						'".$curSid."',
						".$ochkiId.",
						1,
						'".$main_summa."',
						".time().",
						0,
						'".$options."',
						".( $lense ? $lense : 0 ).",
                                                ".( $lense ? intval( $lenses[$lense]['price'] ) : 0 )."
		);" );
                header( "Location: /order" );
            }
        }
	
	function getTopWidget()
	{
		global $main, $lang, $mysql, $utils;
		
		$t = "
			<script>
				function addToBasket( id )
				{
                                    processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', 1, '&id=' + id, 'processAfterChangingBasket( data );' );
				}
				
				function processAfterChangingBasket( data )
				{
                                    $( '.basket_home .counter' ).html( data ).show();
				}
			</script>
		";
		
		return $t;
	}
	
	function getItemBasketString( $id, $count = 1, $checkColorId = '' )
	{
		global $main, $lang, $mysql, $utils;
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		$r = $mysql->mq( "SELECT `count` FROM `".$mysql->t_prefix."basket` WHERE (`session`='".$curSid."'".( $curUser ? " OR `user`=".$curUser : "" ).") AND `good`=".$id." AND `order`=0" );
		
		if( $r ) {
			$t = "
			<div class='basket_item_in' onclick=\"$( this ).parent().parent().attr( 'cl', '1' ); deleteFromBasket( ".$id." );\">
				<span>-</span> ".$lang->gp( 203, true )."
			</div>
			";
		} else {
			$t = "
			<div onclick=\"$( this ).parent().parent().attr( 'cl', '1' ); 
				".( $checkColorId ? "
				if( $( '#".$checkColorId."' ).val() == '0' ) {
					var id = $( this ).addClass( 'basket_action_No_color' );
					$( '.set_color_message' ).fadeIn( 'fast' );
					setTimeout( function() { id.removeClass( 'basket_action_No_color' ); $( '.set_color_message' ).fadeOut( 'fast' ); }, 1500 );
					return false;
				}
				" : "" )."
				addToBasket( ".$id.", ".$count.( $checkColorId ? ", 48, $( '#".$checkColorId."' ).val()" : "" )." );
			\">
				<span>+</span> ".$lang->gp( 202, true )."
			</div>
			";
		}
		
		return $t;
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
			case 1: // Добавить/обновить товар в корзину
			
				$ochkiId = $query->gp( "id" );
				
                                $catalog = $main->modules->gmi( "catalog" );
                                $realGood = $catalog->getItem( $ochkiId );
                                if( !$realGood )
                                    return $this->getCurrentUserBasketItemsCount();
                
                                $discount = $main->modules->gmi( "discount" )->getDiscountForGood( $realGood );
                                $realGood['discount_price'] = $discount ? $realGood['price'] - ( $realGood['price'] / 100 * $discount['percent'] ) : 0;
                                $realGood['discount_asis'] = $realGood['price'] - ceil( $realGood['discount_price'] );
                                $main_summa = intval( $realGood['discount_price'] ? ceil( $realGood['discount_price'] ) : $realGood['price'] );
                
                                $r = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."basket` WHERE (`session`='".$curSid."'".( $curUser ? " OR `user`=".$curUser : "" ).") AND `ochkiid`=".$ochkiId." AND `order`=0 AND `lense`=0" );
                                if( $r )
                                    return $this->getCurrentUserBasketItemsCount();
                    
                                $mysql->mu( "INSERT INTO `".$mysql->t_prefix."basket` VALUES(
						0,
						".$curUser.",
						'".$curSid."',
						".$ochkiId.",
						1,
						'".$main_summa."',
						".time().",
						0,
						'',
						0,
                                                0
                                );" );
                                
                                return $this->getCurrentUserBasketItemsCount();
				
			case 2: // Удалить элемент корзины
			
				$this->removeFromBasket( $query->gp( "id" ), $curUser, $curSid );

				$itemsPrice = $this->countMainSumma();
				$itemsCount = $this->getCurrentUserBasketItemsCount();

				return ( $itemsCount ? $utils->getRightCountString( $itemsCount, $lang->gp( 52 ), $lang->gp( 53 ), $lang->gp( 54 ) ).", ".$utils->digitsToRazryadi( $itemsPrice )." ".$main->listings->getListingElementValueById( 21, 206, true ) : $lang->gp( 28 ) )."^^^".$this->getItemBasketString( $query->gp( "id" ) );
				
			case 3: // Изменить кол-во товара в корзине
			
				$id = $query->gp( "id" );
				$count = $query->gp( "count" );
				
				if( !$id || !$count || !is_numeric( $id ) || !is_numeric( $count ) || $count > 999999 || $count < 1 )
					return;
					
				$newprice = $this->changeBasketCount( $id, $count, $curUser, $curSid );
				
				$itemsPrice = $this->countMainSumma();
				$itemsCount = $this->getCurrentUserBasketItemsCount();
		
				return $utils->getRightCountString( $itemsCount, $lang->gp( 52 ), $lang->gp( 53 ), $lang->gp( 54 ) ).", ".$utils->digitsToRazryadi( $itemsPrice )." ".$main->listings->getListingElementValueById( 21, 206, true )."^^^^^^".$id."^^^".$utils->digitsToRazryadi( $newprice )."^^^".$utils->digitsToRazryadi( $itemsPrice );
				
			case 4: // Установить цвет товара в корзине
			
				$id = $query->gp( "id" );
				$newcolor = $query->gp( "newcolor" );
				
				$r = $main->modules->gmi( "catalog" )->getItem( $id );
				
				if( !$r || !$id || !$newcolor || !is_numeric( $id ) || !is_numeric( $newcolor ) || $newcolor < 1 )
					return;
					
				$newprice = $main->modules->gmi( "catalog" )->getItemPriceWithProperty( $id, 48, $newcolor );
				$newprice = $newprice != null ? $newprice : 0;
				
				$tt = explode( ";", $r['add'] );
				$side = isset( $tt[1] ) ? $tt[1] : ( $tt[0] ? $tt[0] : 0 );
				$add = "48,".$newcolor.( $side ? ";".$side : "" );
				$mysql->mu( "UPDATE `".$mysql->t_prefix."basket` SET
					
						`price`='".$newprice."',
						`add`='".$add."'
						
					 WHERE (`session`='".$curSid."'".( $curUser ? " OR `user`=".$curUser : "" ).") AND `good`=".$id." AND `order`=0" );
				
				$itemsPrice = $this->countMainSumma();
				$itemsCount = $this->getCurrentUserBasketItemsCount();
		
				return $utils->getRightCountString( $itemsCount, $lang->gp( 52 ), $lang->gp( 53 ), $lang->gp( 54 ) ).", ".$utils->digitsToRazryadi( $itemsPrice )." ".$main->listings->getListingElementValueById( 21, 206, true )."^^^^^^".$id."^^^".$utils->digitsToRazryadi( $this->getTotalPriceOfItemInBasket( $id, $curUser, $curSid ) )."^^^".$utils->digitsToRazryadi( $itemsPrice )."^^^".$utils->digitsToRazryadi( $newprice )."^^^".$main->listings->getListingElementValueById( 2, $newcolor, true );
				
			case 5: // Посчитать сумму скидки на заказ
			
				$discount_main_summa = $this->countMainSumma();
				
				$discounts = $main->modules->gmi( "actions" )->getDiscountsForType( 270 );
				foreach( $discounts as $data ) {
					$tt = explode( "^", $data['options'] );
					if( $tt[0] != 270 )
						continue;
					if( $tt[2] == 1 && !$curUser )
						continue;
					if( strstr( $tt[1], "%" ) ) {
						$discount_value = str_replace( "%", '', $tt[1] );
						$discount_main_summa -= floor( $discount_main_summa / 100 * $discount_value );
					} else {
						$discount_main_summa -= $tt[1];
					}
				}
				
				return $utils->digitsToRazryadi( $discount_main_summa );
		}
	}
	
	function addToBasket( $good, $count, $curUser, $curSid, $param_id = 0, $param_value = '', $side_choice = 0 )
	{
		global $mysql, $main;
		
		$r = $main->modules->gmi( "catalog" )->getItem( $good );
		if( !$r )
			return;
                
                $discount = $main->modules->gmi( "discount" )->getDiscountForGood( $r );
                $r['discount_price'] = $discount ? $r['price'] - ( $r['price'] / 100 * $discount['percent'] ) : 0;
                $r['discount_asis'] = $r['price'] - ceil( $r['discount_price'] );
			
		$add = "";
		$price = intval( $r['discount_price'] ? ceil( $r['discount_price'] ) : $r['price'] );
		if( $param_id ) {
			$price_data = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_prices` WHERE `tovar_id`=".$good." AND `property`=".$param_id." AND `prop_value`=".$param_value." ORDER BY `date` DESC" );
			if( $price_data ) {
                                if( $discount ) {
                                    $price_data['discount_price'] = $discount ? $price_data['price'] - ( $price_data['price'] / 100 * $discount['percent'] ) : 0;
                                }
				$price = intval( $price_data['discount_price'] ? ceil( $price_data['discount_price'] ) : $price_data['price'] );
                        }
			$add = $param_id.",".$param_value;
		}
		
		if( $side_choice )
			$add = ( $add ? ";" : "" ).$side_choice;
		
		$r = $mysql->mq( "SELECT `count` FROM `".$mysql->t_prefix."basket` WHERE (`session`='".$curSid."'".( $curUser ? " OR `user`=".$curUser : "" ).") AND `good`=".$good." AND `order`=0" );
		if( $r ) {
			$count += $r['count'];
			$mysql->mu( "UPDATE `".$mysql->t_prefix."basket` SET
					
						`count`='".$count."',
						`price`='".$price."'
						
					 WHERE (`session`='".$curSid."'".( $curUser ? " OR `user`=".$curUser : "" ).") AND `good`=".$good." AND `order`=0" );
		} else {
			$mysql->mu( "INSERT INTO `".$mysql->t_prefix."basket` VALUES(
						
						".$curUser.",
						'".$curSid."',
						'".$good."',
						'".$count."',
						'".$price."',
						".time().",
						0,
						'".$add."'
						
					);" );
		}
		
		return;
	}
	
	function changeBasketCount( $good, $count, $curUser, $curSid )
	{
		global $mysql, $main;
		
		if( !$main->modules->gmi( "catalog" )->getItem( $good ) )
			return;
			
		$r = $mysql->mq( "SELECT `price` FROM `".$mysql->t_prefix."basket` WHERE (`session`='".$curSid."'".( $curUser ? " OR `user`=".$curUser : "" ).") AND `good`=".$good." AND `order`=0" );
		$newmainprice = 0;
		if( $r ) {
			$mysql->mu( "UPDATE `".$mysql->t_prefix."basket` SET
					
						`count`='".$count."'
						
					 WHERE (`session`='".$curSid."'".( $curUser ? " OR `user`=".$curUser : "" ).") AND `good`=".$good." AND `order`=0" );
			$newmainprice = $count * $r['price'];
		}
		
		return $newmainprice;
	}
	
	function getTotalPriceOfItemInBasket( $good, $curUser, $curSid )
	{
		global $mysql, $main;
		
		if( !$main->modules->gmi( "catalog" )->getItem( $good ) )
			return 0;
			
		$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE (`session`='".$curSid."'".( $curUser ? " OR `user`=".$curUser : "" ).") AND `good`=".$good." AND `order`=0" );
		return $r['count'] * $r['price'];
	}
	
	function removeFromBasket( $good, $curUser, $curSid )
	{
		global $mysql, $main;
		
		$mysql->mu( "DELETE FROM `".$mysql->t_prefix."basket` WHERE (`session`='".$curSid."'".( $curUser ? " OR `user`=".$curUser : "" ).") AND `ochkiid`=".$good." AND `order`=0" );
	}
	
	function canContinueProcessing()
	{
		global $mysql, $main;
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `order`=0".( $curUser ? " AND `user`=".$curUser : " AND `session`='".$curSid."'" ) );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			
			$props = $main->properties->getPropertiesOfGood( $r['good'] );
			
			$colorCount = 0;
			foreach( $props as $p )
				if( $p['prop_id'] == 48 && $p['value'] )
					$colorCount++;
			
			$tt = explode( ",", $r['add'] );
			$selectedColor = isset( $tt[1] ) && $tt[0] == 48 ? true : false;
			
			if( !$selectedColor && $colorCount > 1 )
				return false;
		}
		
		return true;
	}
	
	function getContent() 
	{
		global $lang, $main, $mysql, $query, $utils;
		
		$actions = $main->modules->gmi( "actions" );
		$itemsCount = $this->getCurrentUserBasketItemsCount();
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		$main->templates->setTitle( $this->getName(), true );
		$content = $lang->gp( $this->getParam( 'context_data_' ) );
		
		if( !$itemsCount ) {
			return "
			<h1 class='pageTitle fullyOwned'>".$this->getName()."</h1>
			
			<p>".$lang->gp( 217, true ).". <a href=\"http://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog\">".$lang->gp( 218, true )."</a></p>
			
			".$main->modules->gmi( "order" )->getOrdersScreen()."
			".$main->modules->gmi( "order" )->getDeliveredOrdersScreen()."
			
			".( $content ? "<div class='widget_blocks'><div class='inner_widget'>
			<h3>".$lang->gp( 216, true )."</h3>

			".$content."</div></div>" : "" )."
			";
		}
		
		$catalog = $main->modules->gmi( "catalog" );
		
		$inner = "
			<table cellspacing=0 cellpadding=0 border=0 class='basket' width=100%>
				<tr>
					<td class='iTitle' align=left valign=middle width=65% nowrap>
						".$lang->gp( 210, true )."
					</td>
					<td class='iTitle' align=center valign=middle width=15% nowrap>
						".$lang->gp( 211, true )."
					</td>
					<td class='iTitle' align=center valign=middle width=10%>
						".$lang->gp( 212, true ).", руб.
					</td>
					<td class='iTitle iTitle_last' align=center valign=middle width=10%>
						".$lang->gp( 213, true ).", руб.
					</td>
				</tr>
		";
		
		$ColorsList = $main->listings->getListingElementsArraySpec( 2, "`order` DESC, `id` ASC", "", -1, true );
		$sides = $main->listings->getListingElementsArray( 29, 0, false, '', true );
		
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `order`=0".( $curUser ? " AND `user`=".$curUser : " AND `session`='".$curSid."'" ) );
		$c = 0;
		$main_summa = 0;
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$class = ++$c < $itemsCount ? "iElem" : "iElem iElem_bottom";
			
			$good = $catalog->getItem( $r['good'] );
			$summa = $r['price'] * $r['count'];
			
			$props = $main->properties->getPropertiesOfGood( $r['good'] );
			$link = "http://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog".$good['r']."/show".$r['good'];			
			
			$colorCount = 0;
			foreach( $props as $p )
				if( $p['prop_id'] == 48 && $p['value'] )
					$colorCount++;
			
			$tt = explode( ";", $r['add'] );
			$side_choice = $good['r'] == 210 ? ( isset( $tt[1] ) ? $tt[1] : ( $tt[0] ? $tt[0] : 0 ) ) : 0;
			$tt = explode( ",", $tt[0] );
			$selectedColor = "";
			if( isset( $tt[1] ) && $tt[0] == 48 )
				$selectedColor = is_numeric( $tt[1] ) && $tt[1] ? $lang->gp( $ColorsList[$tt[1]]['value'], true ) : $tt[1];
				
			$preview = $this->getElementByData( $props, "prop_id", 51 );
			$preview = $preview ? "<img class='tovarImage' src=\"http://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."files/upload/goods/thumbs/".$preview['value']."\" alt='".$lang->gp( 200, true )." «".$good['name'].( $side_choice ? ( $side_choice == 263 ? " правая" : " левая" ) : '' )."»' />" : "<img class='tovarImage' src=\"http://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."images/no_image.png\" alt=\"".$lang->gp( 201, true )."\" />";
			
			$colorSelectBlock = "";
			$redBack = false;
			if( $colorCount > 0 ) {
				if( !$selectedColor && $colorCount > 1 ) {
					$selectedColor = " <a href=\"\" onclick=\"
						lastEvent = event;
						processSimpleAsyncReqForModule( 'catalog', 1, '&id=".$r['good']."', 'showColorChangeWindow( data );' );
						return false;
					\">".$lang->gp( 261, true )."</a>
					<input type=hidden class='need_color_select' id='color_of_".$good['id']."' value='0' />";
					$redBack = true;
				} else if( !$selectedColor ) {
					$color = $this->getElementByData( $props, "prop_id", 48 );
					$selectedColor = is_numeric( $color['value'] ) && $color['value'] ? $lang->gp( $ColorsList[$color['value']]['value'], true ) : $color['value'];
				}
				$colorSelectBlock = "<div class='colorSelectDiv' id='colorSelectDiv_".$good['id']."'>".$lang->gp( 260, true ).": ".$selectedColor."</div>";
			}
			
			$inner .= "
				<tr".( $redBack ? " class='needColor'" : "" ).">
					<td class='".$class."' align=left valign=middle>
						<a href=\"".$link."\" title='".$good['name'].( $side_choice ? ( $side_choice == 263 ? " правая" : " левая" ) : '' )."'>
							".( $preview ? $preview : "" ).$good['name'].( $side_choice ? ( $side_choice == 263 ? " правая" : " левая" ) : '' )."
						</a>".$colorSelectBlock."
						<div class='clear'></div>
					</td>
					<td class='".$class."' align=center valign=middle nowrap>
						<div class='enterStringBasket'>
							<input type=text id='block_".$good['id']."' value=\"".$r['count']."\" autocomplete='off' 
								onchange=\"countBlocks( ".$good['id']." );\" 
								onkeyup=\"countBlocks( ".$good['id']." );\" 
							tabindex='".$c."' />
							<img src=\"http://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."images/delete.png\" class='pointer' onclick=\"
								if( !confirm( 'Вы уверены?' ) ) 
									return false; 
								deleteFromBasketOld( ".$good['id']." );
							\" />
						</div>
					</td>
					<td class='".$class."' align=center valign=middle id='price_".$good['id']."'>
						".$utils->digitsToRazryadi( $r['price'] )."
					</td>
					<td class='".$class." iElem_last iPrice' align=center valign=middle id='summ_".$good['id']."'>
						".$utils->digitsToRazryadi( $summa )."
					</td>
				</tr>
			";
			$main_summa += $summa;
		}
		
		// Работаем с возможными скидками на весь заказ - 270 код
		
		$weekday = date('w');
		$isWeekEnd = $weekday == 0 || $weekday == 6 ? 1 : 0;
		
		$discounts = $actions->getDiscountsForType( 270 );
		$discount_main_summa = $main_summa;
		foreach( $discounts as $data ) {
			$tt = explode( "^", $data['options'] );
			if( $tt[0] != 270 )
				continue;
			if( $tt[2] == 1 && !$curUser )
				continue;
			if( !isset( $tt[3] ) || ( $tt[3] == 1 && !$isWeekEnd ) )
				continue;
			if( strstr( $tt[1], "%" ) ) {
				$discount_value = str_replace( "%", '', $tt[1] );
				$discount_main_summa -= floor( $discount_main_summa / 100 * $discount_value );
			} else {
				$discount_main_summa -= $tt[1];
			}
		}
		
		$tt = explode( "/", date( "d/m" ) );
		if( intval( $tt[1] ) && intval( $tt[1] ) == 2 && intval( $tt[0] ) && intval( $tt[0] ) >= 20 && intval( $tt[0] ) <= 26 ) {
			
		}
		
		if( $discount_main_summa == $main_summa )
			$discount_main_summa = 0;
			
		$delPrice = $main->modules->gmi( "delivery" )->canBeDelivered( $main_summa );
		
		
		
		$inner .= "
			</table>
			
		<div class='resultPrice'>
			".$lang->gp( 214, true )." <span id='resultPrice'".( $discount_main_summa ? " class='discount'" : "" ).">".$utils->digitsToRazryadi( $main_summa )."</span> <span".( $discount_main_summa ? " class='discount'" : "" ).">руб.</span>".( $discount_main_summa ? "<br>".$lang->gp( intval( $tt[1] ) && intval( $tt[1] ) == 3 && intval( $tt[0] ) && intval( $tt[0] ) >= 6 && intval( $tt[0] ) <= 12 ? "С учетом праздничной скидки!" : 298, true )." <span id='resultPrice_discount'>".$utils->digitsToRazryadi( $discount_main_summa )."</span> <span>руб.</span>" : "" )."
		</div>
		
		
		
		<input type=button value=\"".$lang->gp( 215, true )."\" id='contin' onclick=\"".( $delPrice !== true ? "alert( 'Сумма вашего заказа должна превышать ".$delPrice." рублей' ); return false;" : "" )."
			if( !checkAllColorsOfTheGoods() ) {
				alert( '".$lang->gp( 262, true )."' );
				return false;
			}
			document.location = 'http://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."order';
		\" />
		
		<style>
			.need_color_select { }
		</style>
		
		<script>
		
			var summa = ".$main_summa.";
			var summa_discount = ".$discount_main_summa.";
			var lastEvent;
		
			function countBlocks( id )
			{
				var cc = getDigits( $( '#block_' + id ).val() );
				if( cc.toString().length != 0 && cc <= 0 )
					$( '#block_' + id ).val( 1 );
				cc = cc.length > 0 && cc > 0 ? cc : 1;
				if( cc.length > 6 ) {
					cc = cc.toString().substr( 0, 6 );
					$( '#block_' + id ).val( cc );
				}
				var count = Math.floor( cc );
				
				changeBusketValue( id, count, 'localChangings( data );' );
			}
			
			function localChangings( data )
			{
				if( !data.length > 0 || data == '0' )
					return;
				var ar = data.toString().split( '^^^' );
				if( ar.count < 5 ) 
					return;
				
				$( '#summ_' + ar[2] ).html( ar[3] );
				$( '#resultPrice' ).html( ar[4] );
				summa = ar[4];
				if( summa_discount != 0 ) {
					processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', 5, '', '$( \"#resultPrice_discount\" ).html( data ); summa_discount = data;' );					
				}
			}
			
			function checkAllColorsOfTheGoods()
			{
				var done = true;
				$( '.need_color_select' ).each( function() {
					if( this.value == '0' )
						done = false;
				} );
				
				return done;
			}
			
			function showColorChangeWindow( data )
			{
				var g = getCurrentMouseCoords( lastEvent );
				$( '.color_select_screen div' ).html( data );
				$( '.color_select_screen' ).css( 'top', ( g.pageY - 153 ) + 'px' ).css( 'left', ( g.pageX + 5 ) + 'px' ).fadeIn( 'fast' );
			}
			
			function setNewColor( color, elem )
			{
				processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', 4, '&id=' + elem + '&newcolor=' + color, 'processAfterChangingBasket( data, ' + elem + ' ); localChangings( data ); localChangingsColor( data );' );
				$( '.color_select_screen' ).fadeOut( 'fast' );
				setTimeout( function() { $( '.color_select_screen div' ).html( '' ); }, 300 );
			}
			
			function localChangingsColor( data )
			{
				if( !data.length > 0 || data == '0' )
					return;
				var ar = data.toString().split( '^^^' );
				if( ar.count < 5 ) 
					return;
				
				$( '#price_' + ar[2] ).html( ar[5] );
				$( '#colorSelectDiv_' + ar[2] + ' a' ).remove();
				$( '#colorSelectDiv_' + ar[2] ).html( $( '#colorSelectDiv_' + ar[2] ).html() + ' ' + ar[6] );
				$( '#color_of_' + ar[2] ).val( '1' );
				$( '#colorSelectDiv_' + ar[2] ).parent().parent().removeClass( 'needColor' );
			}
			
		</script>
		";
		
		$t = "
			<h1 class='pageTitle fullyOwned'>".$this->getName()."</h1>
			
			".$inner."
			
			".$main->modules->gmi( "order" )->getOrdersScreen()."
			".$main->modules->gmi( "order" )->getDeliveredOrdersScreen()."
			
			<div class='clear'>&nbsp;</div><div class='clear'>&nbsp;</div>
			".( $content ? "<div class='widget_blocks'><div class='inner_widget'>
			<h3>".$lang->gp( 216, true )."</h3>

			".$content."</div></div>" : "" )."
		";
		
		return $t;
	}
	
	function getCurrentUserBasketItems( $goodId = 0 )
	{
		global $mysql, $main;
		
		$ar = array();
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE (`session`='".$main->users->sid."'".( $main->users->auth ? " OR `user`=".$main->users->userArray['id'] : "" ).") AND `order`=0".( $goodId ? " AND `good`=".$goodId : "" ) );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$ar[$r['good']] = $r;
		}
		
		return $ar;
	}
	
	function setBasketItemOrder( $good, $order )
	{
		global $mysql, $main;
		
		$mysql->mu( "UPDATE `".$mysql->t_prefix."basket` SET `order`=".$order." WHERE (`session`='".$main->users->sid."'".( $main->users->auth ? " OR `user`=".$main->users->userArray['id'] : "" ).") AND `order`=0 AND `ochkiid`=".$good );
	}
        
	function getCurrentUserBasketItemsCount()
	{
		global $mysql, $main;
		
		return $mysql->getTableRecordsCount( "`".$mysql->t_prefix."basket`", "(`session`='".$main->users->sid."'".( $main->users->auth ? " OR `user`=".$main->users->userArray['id'] : "" ).") AND `order`=0" );
	}
	
	function countMainSumma()
	{
		global $mysql, $main;
		
		$a = $mysql->mqm( "SELECT `price`,`count` FROM `".$mysql->t_prefix."basket` WHERE (`session`='".$main->users->sid."'".( $main->users->auth ? " OR `user`=".$main->users->userArray['id'] : "" ).") AND `order`=0" );
		$summa = 0;
		while( $r = @mysql_fetch_assoc( $a ) )
			$summa += ( $r['count'] * $r['price'] );
		
		return $summa;
	}
        
        function getDiscountSumma()
	{
		global $mysql, $main;
		
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE (`session`='".$main->users->sid."'".( $main->users->auth ? " OR `user`=".$main->users->userArray['id'] : "" ).") AND `order`=0" );
		$summa = 0;
		while( $r = @mysql_fetch_assoc( $a ) ) {
                    $data = $main->modules->gmi( "catalog" )->getItem( $r['ochkiid'] );
                    if( !$data )
                            continue;                
                    $discount = $main->modules->gmi( "discount" )->getDiscountForGood( $data );
                    if( !$discount )
                        continue;
                    $data['discount_price'] = $discount ? $data['price'] - ( $data['price'] / 100 * $discount['percent'] ) : 0;
                    $data['discount_asis'] = $data['price'] - ceil( $data['discount_price'] );
                    $summa += ( $r['count'] * $data['discount_asis'] );
                }
		
		return $summa;
	}
}

?>