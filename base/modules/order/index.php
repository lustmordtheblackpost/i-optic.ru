<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

define( "ORDER_NEW", 135 );
define( "ORDER_READY_TO_PAY_100", 136 );
define( "ORDER_READY_TO_PAY_50", 704 );
define( "ORDER_READY_PAYD_100_NEED_CONFIRM", 137 );
define( "ORDER_READY_PAYD_100_CONFIRMED", 701 );
define( "ORDER_READY_PAYD_50_NEED_CONFIRM", 702 );
define( "ORDER_READY_PAYD_50_CONFIRMED", 703 );
define( "ORDER_READY", 138 );
define( "ORDER_DELEAVERED_PAYED", 139 );
define( "ORDER_CLIENT_THINKING", 140 );
define( "ORDER_CANCELED", 141 );

class moduleorder extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	var $gl_dbase_string = "`shop`.";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function getLastOrder()
	{
		global $mysql, $main;
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		if( !$curSid )
			return null;
		
		$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."order` WHERE ".( $curUser ? "`user`=".$curUser : "`sid`='".$curSid."'" )." ORDER BY `date` DESC" );
		
		return $r ? $r : null;
	}
	
	function getOrderById( $id )
	{
		global $mysql, $main, $query;
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		if( !$curSid )
			return null;
			
		if( !$query->gp( "printanyfromadministrationmode" ) )
			$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."order` WHERE ".( $curUser ? "`user`=".$curUser : "`sid`='".$curSid."'" )." AND `id`=".$id );
		else 
			$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `id`=".$id );
		
		return $r ? $r : null;
	}
	
	function getContent()
	{
		global $lang, $main, $mysql, $query, $utils;
		
		// print_r($query);
		
		if( $query->gp( "process_make_order" ) ) {
			
			$t = $this->processOrder();
			if( $t )
				return $t;
			
		} else if( $query->gp( "print" ) ) {
			
			$id = $query->gp( "print" );
			$order = $this->getOrderById( $id );
			if( $order ) {
				$this->printOrder( $order );
				exit;
			}
			
		} else if( $query->gp( "payment" ) ) {
			
			$id = $query->gp( "payment" );
			$order = $this->getOrderById( $id );
			if( $order && ( $order['pmethod'] == 143 || $order['pmethod'] == 700 ) ) {
				$t = $this->paymentOrder( $order );
				if( $t )
					return $t;
			}
		} else if( $query->gp( "paymentready" ) ) {
			
			$order = $this->getOrderById( $query->gp( "paymentready" ) );
			if( $order ) {
				
				return "<h1 class='pageTitle fullyOwned green'>".$this->getName()." № ".$order['id']." на сумму ".$utils->digitsToRazryadi( $order['summa'] )." руб. оплачен успешно! Спасибо</h1>
				<a href=\"/basket\">Вернуться в список своих заказов</a>
				
				";
				
			}
			
		} else if( $query->gp( "paymentfail" ) ) {
			
			$order = $this->getOrderById( $query->gp( "paymentfail" ) );
			if( $order ) {
				
				return "<h1 class='pageTitle fullyOwned red'>Ошибка оплаты ".$this->getName()."а № ".$order['id']." на сумму ".$utils->digitsToRazryadi( $order['summa'] )." руб.</h1>
				<a href=\"/basket\">Вернуться в список своих заказов</a>
				
				";
				
			}
			
		}
		
		$catalog = $main->modules->gmi( "catalog" );
		$lenses = $main->modules->gmi( "lenses" );
		
                $step = $query->gp( "step" );
                $promo = $query->gp( "promo" );
                $selected_delivery = $query->gp( "selected_delivery" );
                $selected_delivery = $selected_delivery ? $selected_delivery : 0;
                
		/*$ltype = $query->gp( "t" );
		$type = $query->gp( "p" );
		$dio = $query->gp( "d" );
		$ochkiId = $query->gp( "ochkiid" );
                $lense = $query->gp( "lense" );
		
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
		
		$add_path = "";
		if( $od_sph )
			$add_path .= "&od_sph=".$od_sph;
		if( $os_sph )
			$add_path .= "&os_sph=".$os_sph;
		if( $od_cyl )
			$add_path .= "&od_cyl=".$od_cyl;
		if( $os_cyl )
			$add_path .= "&os_cyl=".$os_cyl;
		if( $od_axis )
			$add_path .= "&od_axis=".$od_axis;
		if( $os_axis )
			$add_path .= "&os_axis=".$os_axis;
		if( $od_add )
			$add_path .= "&od_add=".$od_add;
		if( $os_add )
			$add_path .= "&os_add=".$os_add;
		if( $oculus_pd )
			$add_path .= "&oculus_pd=".$oculus_pd;
		if( $oculus_pd_d )
			$add_path .= "&oculus_pd_d=".$oculus_pd_d;
		if( $oculus_pd_s )
			$add_path .= "&oculus_pd_s=".$oculus_pd_s;
		if( $add_move )
			$add_path .= "&add_move=".$add_move;
		if( $add_shadow )
			$add_path .= "&add_shadow=".$add_shadow;
		if( $add_color )
			$add_path .= "&add_color=".$add_color;
				
		$lenses = $lenses->getLenseWithTypes( $dio, $type, $ltype, $lense );
				
		if( $add && $lense ) {
			$prices = $main->listings->getListingElementsArray( 13, $type, false, '', true );
			if( isset( $prices[$add] ) ) {
				$lenses[$lense]['price'] += $prices[$add]['additional_info'];
			}
		}
						
		$main_summa = $lense ? intval( $lenses[$lense]['price'] ) : 0;
						
		$oprava = $catalog->getItem( $ochkiId );
						
		$main_summa += $oprava ? intval( $oprava['price'] ) : 0;
		
		$main->users->saveSessionOption( 2, $main_summa );*/
		
		if( !$main->modules->gmi( "basket" )->getCurrentUserBasketItemsCount() )
			return "
			<div class='sopli'><div class='all_lines'>
				<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>Оформление заказа
			</div></div>
			
			<div class='catalog catalog_nomargin catalog_marginbottom'>
				<div class='all_lines'>
					Ваша корзина пуста
				</div>
			</div>
			";
			
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
                $curSid = $main->users->sid;
		
		$main->templates->setTitle( "Оформление заказа", true );
		
		$lastOrder = $this->getLastOrder();
		$discount_main_summa = 0;
		$localAdress = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'];
		
		$options = $main->users->auth ? $main->users->getUserProfile( $main->users->userArray['id'] ) : '';
		
		$deliveryPrice = 0;
                
                $basketItems = array();
                $a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `order`=0".( $curUser ? " AND `user`=".$curUser : " AND `session`='".$curSid."'" ) );
		$main_summa = 0;
		while( $r = @mysql_fetch_assoc( $a ) ) {
                    $oprava = $catalog->getItem( $r['ochkiid'] );
                    $opravaRoot = $oprava['root'] ? $catalog->getItem( $oprava['root'] ) : 0;
                    if( $opravaRoot )
			$oprava['name'] = $opravaRoot['name'];
                    $oprava['properties'] = $main->properties->getPropertiesOfGood( $oprava['id'] );
                    $preview = $this->getElementByData( $oprava['properties'], "prop_id", 14 );
                    $preview = $preview ? $preview['value'] : '';
                    $color = $this->getElementByData( $oprava['properties'], "prop_id", 1 );
                    $colorName = $color && is_numeric( $color['value'] ) ? $main->listings->getListingElementValueById( 3, $color['value'], true ) : $color['value'];
                    $size = $this->getElementByData( $oprava['properties'], "prop_id", 19 );
                    $size = $size ? $main->listings->getListingElementValueById( 27, $size['value'], true ) : '';
                    $main_summa += $r['price'];
                    $r['oprava'] = $oprava;
                    $r['preview'] = $preview;
                    $r['size'] = $size;
                    $r['color'] = $color;
                    $r['colorName'] = $colorName;
            foreach ($oprava['properties'] as $value){
                if($value['prop_id'] == 2 and $value['value'] == 4){
                    $r['sun'] = "1";
                }
            }
                    $basketItems[$r['id']] = $r;
                }
                if( $promo && $curUser ) {
                    $promoEntry = $mysql->mq( "SELECT `promo` FROM `".$mysql->t_prefix."sign` WHERE `mail`='".$main->users->userArray['ulogin']."'" );
                    if( !$promoEntry ) {
                         $promo = '';
                    } else {
                        $discount_main_summa = $main_summa / 100 * 5;
                        $main_summa -= $discount_main_summa;
                    }
                }
                
                $rightBlock = "";
                $leftBlock = "";
                foreach( $basketItems as $basketId => $data ) {
                    $opts = json_decode( $data['add'] );
                    $add_path = "";
                    if( $opts->od_sph )
                      	$add_path .= "&od_sph=".$opts->od_sph;
                    if( $opts->os_sph )
			$add_path .= "&os_sph=".$opts->os_sph;
                    if( $opts->od_cyl )
			$add_path .= "&od_cyl=".$opts->od_cyl;
                    if( $opts->os_cyl )
			$add_path .= "&os_cyl=".$opts->os_cyl;
                    if( $opts->od_axis )
			$add_path .= "&od_axis=".$opts->od_axis;
                    if( $opts->os_axis )
			$add_path .= "&os_axis=".$opts->os_axis;
                    if( $opts->od_add )
			$add_path .= "&od_add=".$opts->od_add;
                    if( $opts->os_add )
			$add_path .= "&os_add=".$opts->os_add;
                    if( $opts->oculus_pd )
			$add_path .= "&oculus_pd=".$opts->oculus_pd;
                    if( $opts->oculus_pd_d )
			$add_path .= "&oculus_pd_d=".$opts->oculus_pd_d;
                    if( $opts->oculus_pd_s )
			$add_path .= "&oculus_pd_s=".$opts->oculus_pd_s;
                    if( $opts->add_move )
			$add_path .= "&add_move=".$opts->add_move;
                    if( $opts->add_shadow )
			$add_path .= "&add_shadow=".$opts->add_shadow;
                    if( $opts->add_color )
			$add_path .= "&add_color=".$opts->add_color;
                    $l = $lenses->getLenseWithId( $data['lense'] );
                    
                    $discount = $main->modules->gmi( "discount" )->getDiscountForGood( $data['oprava'] );
                    $data['oprava']['discount_price'] = $discount ? $data['oprava']['price'] - ( $data['oprava']['price'] / 100 * $discount['percent'] ) : 0;
                    $data['oprava']['discount_asis'] = $data['oprava']['discount_price'] ? $data['oprava']['price'] - ceil( $data['oprava']['discount_price'] ) : 0;
                    $discount_main_summa += ( $data['oprava']['discount_asis'] ? $data['oprava']['discount_asis'] : 0 );
                    
                    $rightBlock .= ( $rightBlock ? "<div style='float: left; width: 100%; padding-top: 10px; margin-top: 10px; border-top: 1px solid #bbb;'></div><div class='clear'></div>" : "" )."<div class='block_image'>".( $data['preview'] ? "<img src='".$mysql->settings['local_folder']."files/upload/goods/".$data['preview']."' alt='Оправа' />" : '' )."</div>
							
							<div class='block_text'>
								<h4>".$data['oprava']['name']."</h4>
								<div>Цвет: <span>".$data['colorName']."</span></div>
								<div>Размер: <span>".( $data['size'] ? $data['size'] : '-' )."</span></div>
							</div>
							<div class='block_prices'>
								".$utils->digitsToRazryadi( $data['oprava']['price'] )." <img src='".$mysql->settings['local_folder']."images/mini_ruble.png' />
							</div>
							
							".( $l ? "<div class='clear' style='height: 5px;'></div>
							<div class='block_image'>&nbsp;</div><div class='block_text'>
								<div>Линзы: <span>".$l['name']."</span></div>
							</div><div class='block_prices'>
								".$utils->digitsToRazryadi( $data['lense_price'] )." <img src='".$mysql->settings['local_folder']."images/mini_ruble.png' />
							</div>
							
							" : "" )."
							<div class='clear' style='height: 5px;'></div>";
                    
                    
                    $leftBlock .= ( $leftBlock ? "<div style='float: left; width: 100%; padding-top: 30px; margin-top: 10px; border-top: 1px solid #bbb;'></div><div class='clear'></div>" : "" )."<div class='block_image'>".( $data['preview'] ? "<img src='".$mysql->settings['local_folder']."files/upload/goods/".$data['preview']."' alt='Оправа' />" : '' )."</div>
							
								<div class='block_text'>
									".( !$l ? "
									<div class='block_edit'>
										<a href='/basket/remove".$basketId."'><img src='/images/remove.png' /> Удалить</a>
									</div>
									" : "" )."
									<h3>".$data['oprava']['name']."</h3>
									".( $l ? "
									<p>Рецепт</p>
									
									<div class='block_edit'>
										<a href='".$mysql->settings['local_folder']."catalog/show".$data['oprava']['id']."/".strtolower( $utils->translitIt( $data['oprava']['name'] ) )."_".strtolower( $utils->translitIt( $data['oprava']['article'] ) ).".html?t=".$opts->t."&p=".$opts->p."&d=".$opts->d."&lense=".$data['lense'].( $opts->add ? "&add=".$opts->add : "" ).$add_path."'><img src='/images/edit.png' /> Редактировать</a>
										<a href='/basket/remove".$basketId."'><img src='/images/remove.png' /> Удалить</a>
									</div>
									
									<table cellspacing=0>
										<tr>
											<td class='first'></td>
											<td>SPH</td>
											<td>CYL</td>
											<td>AXIS</td>
											".( $opts->t == 78 || $opts->t == 79 ? "<td>ADD</td>" : "" )."
											<td class='last'>PD</td>
										</tr>
										<tr>
											<td class='first'>Левый</td>
											<td>".$main->listings->getListingElementValueById( 14, $opts->os_sph, true, '-' )."</td>
											<td>".$main->listings->getListingElementValueById( 22, $opts->os_cyl, true, '-' )."</td>
											<td>".$main->listings->getListingElementValueById( 23, $opts->os_axis, true, '-' )."</td>
											".( $opts->ltype == 78 || $opts->ltype == 79 ? "<td>".$main->listings->getListingElementValueById( $opts->t == 78 ? 29 : 24, $opts->os_add, true, '-' )."</td>" : "" )."
											<td".( !$opts->oculus_pd_d && !$opts->oculus_pd_s ? " rowspan=2 class='last bottom'" : " class='last'" ).">".( !$opts->oculus_pd_d && !$opts->oculus_pd_s ? $main->listings->getListingElementValueById( 25, $opts->oculus_pd, true, '-' ) : $main->listings->getListingElementValueById( 26, $opts->oculus_pd_s, true ) )."</td>
										</tr>
										<tr>
											<td class='first bottom'>Правый</td>
											<td class='bottom'>".$main->listings->getListingElementValueById( 14, $opts->od_sph, true, '-' )."</td>
											<td class='bottom'>".$main->listings->getListingElementValueById( 22, $opts->od_cyl, true, '-' )."</td>
											<td class='bottom'>".$main->listings->getListingElementValueById( 23, $opts->od_axis, true, '-' )."</td>
											".( $opts->t == 78 || $opts->t == 79 ? "<td class='bottom'>".$main->listings->getListingElementValueById( $opts->t == 78 ? 29 : 24, $opts->od_add, true, '-' )."</td>" : "" )."
											".( !$opts->oculus_pd_d && !$opts->oculus_pd_s ? "" : "<td class='last bottom'>".$main->listings->getListingElementValueById( 26, $opts->oculus_pd_d, true, '-' )."</td>" )."
										</tr>
									</table>
									<p>
										Линзы: <span>".$l['name']."</span>
									</p>
									<p>
										".$main->listings->getListingElementValueById( 13, $opts->p, true ).( $opts->add ? ": <span>".$main->listings->getListingElementValueById( 13, $opts->add, true )."; ".$this->getCorrectColorName( $opts->add_color, $opts->add ).( $opts->add_move ? "; Переход" : "" ).( $opts->add_shadow ? "; Прозрачность ".$main->listings->getListingElementValueById( 19, $opts->add_shadow, true ) : "" )."</span>" : '' )."
									</p>
									" : ( $data['sun'] != 1 ?"
									<p>Вы выбрали оправу без линз. Хотите выбрать линзы сейчас? <a href='".$mysql->settings['local_folder']."catalog/show".$data['oprava']['id']."/".strtolower( $utils->translitIt( $data['oprava']['name'] ) )."_".strtolower( $utils->translitIt( $data['oprava']['article'] ) ).".html#lenses'>Перейти к выбору линзы для данной оправы".$data['sun']."</a></p>
									": "" ))."
								</div>";
                }
		
		$t = "
		<script src=\"/jsf/im/jquery.inputmask.bundle.js\"></script>
  		<script src=\"/jsf/im/phone.js\"></script>
  		<script src=\"/jsf/im/inputmask.binding.js\"></script>
  		
			<div class='sopli'><div class='all_lines'>
				<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>Оформление заказа
			</div></div>
			
			<div class='catalog catalog_nomargin catalog_marginbottom'>
				<div class='all_lines'>
					<div class='itogorder'>
						<div class='in_itog'>
							".$rightBlock."
							<div class='clear' style='height: 5px;'></div><div class='block_image'>&nbsp;</div><div class='block_text block_margintop'>
								<div>Скидка</div>
							</div>
							<div class='block_prices block_margintop' id='discount' data-value=".$discount_main_summa.">
								".$utils->digitsToRazryadi( $discount_main_summa )." <img src='".$mysql->settings['local_folder']."images/mini_ruble.png' />
							</div>

                                                        <div id='haspromo' style='display: none;'>
                                                            <div class='block_image'>&nbsp;</div>
                                                            <div class='block_text' style='margin-top: 5px;'>
                                                            	<div style='color: #F08B48; font-size: 14px; cursor: pointer;' onclick=\"$('#promo').show().focus();\">У меня есть промокод!</div>
                                                            </div>
                                                            <div class='block_prices' style='margin-top: 5px; height: 17px;'>
                                                            	<input type=text id='promo' value='".$promo."' style='height: 15px; margin-top: 0px; padding: 1px; width: 60px; font-size: 10px; display: none; border: 1px solid #444; text-align: center;' />
                                                            </div>
                                                        </div>
							<div class='clear' style='height: 5px;'></div>
							<div class='block_image'>&nbsp;</div><div class='block_text block_margintop'>
								<div>Доставка</div>
							</div>
							<div class='block_prices block_margintop'>
								<span id='result_delivery'>?</span> <img src='".$mysql->settings['local_folder']."images/mini_ruble.png' />
							</div>
							
							<div class='clear' style='height: 5px;'></div>
						</div>
						<div class='footer'>
							<div class='block_image'>&nbsp;</div>
							<div class='block_text'>
								<h3>Итого</h3>
							</div>
							<div class='block_prices block_prices_big'>
								<span id='result_price' data-value=".$main_summa.">".$utils->digitsToRazryadi( $main_summa )."</span> <img src='".$mysql->settings['local_folder']."images/big_ruble.png' />
							</div>
							<div class='clear'></div>
						</div>
					</div>
					<div class='main_data' style='opacity: 0;'><div class='md_inner_marginright'>
						
						<div class='itogtable'>
							<div class='titler'>
								<div class='one current'><div class='h'></div><div class='inr'><span>1</span> Корзина</div></div>
								<div class='one'><div class='h'></div><div class='inr'><span>2</span> Адрес доставки</div></div>
								<div class='one'><div class='h'></div><div class='inr'><span>3</span> Способ доставки</div></div>
								<div class='one last'><div class='h'></div><div class='inr'><span>4</span> Оплата</div></div>
							</div>
							<div class='contents'>
								
                                                                ".$leftBlock."
                                                                
								<div class='block_button' style='width: 100%'>
									<div class='button' onclick=\"
										processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '101', '&p=".urlencode( $_SERVER['REQUEST_URI'] )."' + ( $( '#promo' ).val() ? '&promo=' + $( '#promo' ).val() : '' ), 'updateSituation( data );' );
									\">
										Продолжить
									</div>
								</div>
								<div class='clear'></div>
							</div>
						</div>
					
					</div></div>
					<div class='clear'></div>
				</div>
			</div>
			
			<script>
				$(window).load(function()
				{
					$( '.catalog .main_data' ).width( $( '.catalog .all_lines' ).width() - $( '.catalog .itogorder' ).width() - 4 ).animate( { opacity: 1 }, 200 );
					".( $step ? "
					processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '".$step."', '&p=".urlencode( $_SERVER['REQUEST_URI'] ).( $main->users->goingFromOrder ? "&ulf=1" : "" ).( $selected_delivery ? "&selected_delivery=".$selected_delivery : "" ).( $promo ? "&promo=".$promo : "" )."', 'updateSituation( data ); $( \'.catalog .main_data\' ).animate( { opacity: 1 }, 200 ); ' );
					" : "
					$( '.catalog .main_data' ).animate( { opacity: 1 }, 200 );
					" )."
					/*$( '.in_itog .block_image' ).height( $( '.in_itog .block_image' ).parent().height() );
					$( '.contents .block_image' ).height( $( '.contents .block_image' ).parent().height() );*/
				});
			
				$(window).resize(function()
				{
					$( '.catalog .main_data' ).width( $( '.catalog .all_lines' ).width() - $( '.catalog .itogorder' ).width() - 4 );
					/*$( '.in_itog .block_image' ).height( $( '.in_itog .block_image' ).parent().height() );
					$( '.contents .block_image' ).height( $( '.contents .block_image' ).parent().height() );*/
				});
				
				function updateSituation( data )
				{
					var ar = data.toString().split( '~' );
					$( '.itogtable .titler' ).html( ar[0] );
					$( '.itogtable .contents' ).html( ar[1] );
				}
			</script>
		";
		
		return $t;
	}
	
	function getCorrectColorName( $color, $addType )
	{
		global $main;
		
		if( $addType == 131 )
			return $main->listings->getListingElementValueById( 15, $color, true );
		else if( $addType == 132 )
			return $main->listings->getListingElementValueById( 16, $color, true );
		else if( $addType == 133 )
			return $main->listings->getListingElementValueById( 17, $color, true );
		else if( $addType == 134 || ( $addType >= 677 && $addType <= 680 ) )
			return $main->listings->getListingElementValueById( 18, $color, true );
	}
	
	function processOrder()
	{
		global $lang, $main, $mysql, $query, $utils;
		
		$catalog = $main->modules->gmi( "catalog" );
		$lenses = $main->modules->gmi( "lenses" );
                
                $curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
                $curSid = $main->users->sid;
                
                $selected_delivery = $query->gp( "selected_delivery" );
		$selected_payment = $query->gp( "selected_payment" );
		
		/*$ltype = $query->gp( "t" );
		$type = $query->gp( "p" );
		$dio = $query->gp( "d" );
		$ochkiId = $query->gp( "ochkiid" );
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
		$oprava = $catalog->getItem( $ochkiId );				
		$main_summa = $main->users->getSessionOption( 2 );*/
                
                $main_summa = $main->modules->gmi( "basket" )->countMainSumma();
                if( !$main_summa ) {
                    return "<script>urlmove( '/order' );</script>";
                }
		$del_price = $main->modules->gmi( "delivery" )->getDeliveryPrice( $selected_delivery );
		
		/*$lastSumma = $main->users->getSessionOption( 3 );
		$lastIhki = $main->users->getSessionOption( 4 );
		$lastCD = $main->users->getSessionOption( 5 );
		
		if( $lastCD ) {
			if( $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."order` WHERE `date`=".$lastCD." AND `summa`=".$lastSumma." AND `ochkiid`=".$lastIhki ) )
				return "<script>urlmove('/');</script>";
		}*/
						
		$userId = $curUser;
		if( !$main->users->auth ) {
			$userId = $main->users->getSessionOption( 1 );
			if( !$userId )
				return "";
		}
                
		$profile = $main->users->getUserProfile( $userId );
                $userData = $main->users->getUserById( $userId, false, true );
                $promo = $query->gp( "promo" );
                $discount_main_summa = 0;
                if( $promo && $userId ) {
                    $promoEntry = $mysql->mq( "SELECT `promo` FROM `".$mysql->t_prefix."sign` WHERE `mail`='".$userData['ulogin']."' AND `promo`='".$promo."'" );
                    if( !$promoEntry ) {
                        $promo = '';
                    } else {
                        $discount_main_summa = ceil( $main_summa / 100 * 5 );
                        $main_summa -= $discount_main_summa;
                    }
                } else
                    $promo = '';
                $discount_main_summa += $main->modules->gmi( "basket" )->getDiscountSumma();
                
                if( $selected_payment == 143 ) { // 100% оплата - скидка 10%
                    $disten = ceil( $main_summa / 100 * 10 );
                    $main_summa -= $disten;
                    $discount_main_summa += $disten;
                }
			
		$cd = time();
		
                $orderType = ORDER_NEW;
                if( $selected_payment == 143 )
                    $orderType = ORDER_READY_TO_PAY_100;
                else if( $selected_payment == 700 )
                    $orderType = ORDER_READY_TO_PAY_50;
		
		/*$mysql->mu( "INSERT INTO `".$mysql->t_prefix."order` VALUES(
		
			0,
			".$userId.",
			'".$main->users->sid."',
			".$cd.",
			'".json_encode( $array )."',
			".$ochkiId.",
                        ".$lense.",
			".$selected_delivery.",
			".$selected_payment.",
			".$orderType.",
			".( $main_summa + $del_price ).",
			".$cd.",
			'',
			''
		
		);" );*/
                $mysql->mu( "INSERT INTO `".$mysql->t_prefix."order` VALUES(
		
			0,
			".$userId.",
			'".$main->users->sid."',
			".$cd.",
			'',
			0,
                        0,
			".$selected_delivery.",
			".$selected_payment.",
			".$orderType.",
			".( $main_summa + $del_price ).",
			".$cd.",
			'',
			'','',
                        '".$main->modules->gmi( 'yandex_payments' )->gen_uuid()."'
		);" );
		
		$r = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."order` WHERE `date`=".$cd." AND `summa`=".( $main_summa + $del_price )." ORDER BY `id` DESC" );
		
		if( !$r )
			return "";
                
                $newOrderId = $r['id'];
                
                $a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `order`=0".( $curUser ? " AND `user`=".$curUser : " AND `session`='".$curSid."'" ) );
		while( $r = @mysql_fetch_assoc( $a ) ) {
                    $main->modules->gmi( "basket" )->setBasketItemOrder( $r['ochkiid'], $newOrderId );
                    $catalog->increaseItemRating( $r['ochkiid'] );
                }
                
                if( $promo ) {
                    $mysql->mu( "UPDATE `".$mysql->t_prefix."sign` SET `promo`='' WHERE `mail`='".$userData['ulogin']."'" );
                }
			
		$mail_agent = $main->modules->gmi( "mail_agent" );
		
		$localAdress = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'];
		
		$user = $main->users->getUserById( $userId );

		$template = $main->templates->psl( $lang->gp( 132 ), true );
		$template = str_replace( "[order_number]", "<b>".$newOrderId."</b>", $template );
		$template = str_replace( "[summa]", "<b>".( $main_summa + $del_price )." руб.</b>", str_replace( "[date]", $utils->getFullDate( $cd ), $template ) );
                 
                $template = str_replace( "[payment_link]", $orderType == ORDER_READY_TO_PAY_100 || $orderType == ORDER_READY_TO_PAY_50 ? $lang->gp( 169 )."<a href='https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."order/payment".$newOrderId."'>https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."order/payment".$newOrderId."</a>" : "", $template );
					
		$mail_agent->sendMessage( $user['ulogin'], $this->getParam( "from_to_send_order" ), $main->templates->psl( $lang->gp( 133 ) ), $template );
		
		$template = $main->templates->psl( $lang->gp( 134 ), true );
		$template = str_replace( "[order_number]", "<b>".$newOrderId."</b>", $template );
		$template = str_replace( "[summa]", "<b>".( $main_summa + $del_price )." руб.</b>", $template );
		$template = str_replace( "[dev]", "<b>".( $del_price )." руб.</b>", $template );
		$template = str_replace( "[date]", $utils->getFullDate( $cd ), $template );
                $template = str_replace( "[promo_discount]", $promo ? ", скидка 5% по промо акции".( $selected_payment == 143 ? ", скидка 10% за оплату 100% по карте" : "" ) : ( $selected_payment == 143 ? ", скидка 10% за оплату 100% по карте" : "" ), $template );
		$template = str_replace( "[pmethod]", $main->listings->getListingElementValueById( 21, $selected_payment, true ), $template );
					
		$mail_agent->sendMessage( $this->getParam( "where_send_new_order" ), $this->getParam( "from_to_send_order" ), $main->templates->psl( $lang->gp( 135 ) ), $template );
		
		$template = $main->templates->psl( $lang->gp( 136 ), true );
		$template = str_replace( "[order_number]", "<b>".$newOrderId."</b>", $template );
		$template = str_replace( "[summa]", "<b>".( $main_summa + $del_price )."</b>", $template );
		
		$main->users->saveSessionOption( 3, $main_summa + $del_price );
		$main->users->saveSessionOption( 4, $ochkiId );
		$main->users->saveSessionOption( 5, $cd );
                if( $discount_main_summa )
                    $main->users->saveSessionOption( 33, $discount_main_summa );
                else
                    $main->users->saveSessionOption( 33, 0 );
                
                $text = "";
                if( $orderType == ORDER_READY_TO_PAY_100 || $orderType == ORDER_READY_TO_PAY_50 )
                    $text = str_replace( "[link]", "<a href='https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."order/payment".$newOrderId."'>https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."order/payment".$newOrderId."</a>", $main->templates->psl( $lang->gp( 131, true ), true ) )."<br/><br/><input type=button value='Перейти к оплате заказа' style='height: 25px; line-height: 25px; padding-left: 20px; padding-right: 20px;' onclick=\"urlmove('".$mysql->settings['local_folder']."order/payment".$newOrderId."');\" />";
		
		return "
		<div class='sopli'><div class='all_lines'>
			<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>Оформление заказа
		</div></div>
			
		<div class='catalog catalog_nomargin catalog_marginbottom'>
			<div class='all_lines'>
				".$template."
                                ".( $orderType == ORDER_READY_TO_PAY_100 || $orderType == ORDER_READY_TO_PAY_50 ? "<div style='margin-top: 40px; font-size: 18px;'>".$text."</div>" : "" )." 
			</div>
		</div>
                ";
	}
	
	function getOrdersScreen( $delivered = 0 )
	{
		global $lang, $main, $mysql, $query, $utils;
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		$catalog = $main->modules->gmi( "catalog" );
                $lenses = $main->modules->gmi( "lenses" );
		
		$aa = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `status`".( !$delivered ? "<>" : "=" ).( !$delivered ? ORDER_DELEAVERED_PAYED : $delivered )." AND ".( $curUser ? "`user`=".$curUser : "`sid`='".$curSid."'" )." ORDER BY `date` DESC" );
		$itemsCount = @mysql_num_rows( $aa );
		if( !$itemsCount )
			return "";
			
		$localAdress = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'];
			
		$t = "";
		while( $rr = @mysql_fetch_assoc( $aa ) ) {
                    $t .= "<div class='ordersListElemTop'><div class='h'></div><div class='date'><div class='h'></div><span>".$utils->getFullDate( $rr['date'] )."</span></div><div class='orderStatus'><div class='h'></div><span>".$main->listings->getListingElementValueById( 20, $rr['status'], true )."</span></div></div>";
                    
                    $basketItems = array();
                    $a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `order`=".$rr['id'] );
                    $main_summa = 0;
                    while( $r = @mysql_fetch_assoc( $a ) ) {
                        $oprava = $catalog->getItem( $r['ochkiid'] );
                        $opravaRoot = $oprava['root'] ? $catalog->getItem( $oprava['root'] ) : 0;
                        if( $opravaRoot )
            		$oprava['name'] = $opravaRoot['name'];
                        $oprava['properties'] = $main->properties->getPropertiesOfGood( $oprava['id'] );
                        $preview = $this->getElementByData( $oprava['properties'], "prop_id", 14 );
                        $preview = $preview ? $preview['value'] : '';
                        $color = $this->getElementByData( $oprava['properties'], "prop_id", 1 );
                        $colorName = $color && is_numeric( $color['value'] ) ? $main->listings->getListingElementValueById( 3, $color['value'], true ) : $color['value'];
                        $size = $this->getElementByData( $oprava['properties'], "prop_id", 19 );
                        $size = $size ? $main->listings->getListingElementValueById( 27, $size['value'], true ) : '';
                        $main_summa += $r['price'];
                        $r['oprava'] = $oprava;
                        $r['preview'] = $preview;
                        $r['size'] = $size;
                        $r['color'] = $color;
                        $r['colorName'] = $colorName;
                        $basketItems[$r['id']] = $r;
                    }
                    $t .= "<div class='ordersListElem'>";
                    foreach( $basketItems as $basketId => $data ) {
                        $opts = json_decode( $data['add'] );
                        $l = $lenses->getLenseWithId( $data['lense'] );
                        $dopPrice = 0;
                        if( isset( $opts->add ) && $opts->add ) {
                            $prices = $main->listings->getListingElementsArray( 13, $opts->p, false, '', true );
                            if( isset( $prices[$opts->add] ) ) {
                                $dopPrice = $prices[$opts->add]['additional_info'];
                            }
                        }
                        $discount = $main->modules->gmi( "discount" )->getDiscountForGood( $data['oprava'] );
                        $data['oprava']['discount_price'] = $discount ? $data['oprava']['price'] - ( $data['oprava']['price'] / 100 * $discount['percent'] ) : 0;
                        $data['oprava']['discount_asis'] = $data['oprava']['discount_price'] ? $data['oprava']['price'] - ceil( $data['oprava']['discount_price'] ) : 0;
                        $t .= "<div class='oneElem'><div class='block_image'>".( $data['preview'] ? "<img src='".$mysql->settings['local_folder']."files/upload/goods/".$data['preview']."' alt='Оправа' />" : '' )."</div>
							
							<div class='block_text'>
								<h4>".$data['oprava']['name']."</h4>
								<div>Цвет: <span>".$data['colorName']."</span></div>
								<div>Размер: <span>".( $data['size'] ? $data['size'] : '-' )."</span></div>
							</div>
							<div class='block_prices'>
								".$utils->digitsToRazryadi( $data['oprava']['discount_price'] ? ceil( $data['oprava']['discount_price'] ) : $data['oprava']['price'] )." <img src='".$mysql->settings['local_folder']."images/mini_ruble.png' />
							</div>
							
							".( $l ? "<div class='clear' style='height: 5px;'></div>
							<div class='block_image'>&nbsp;</div><div class='block_text'>
								<div>Линзы: <span>".$l['name']."</span></div>
							</div><div class='block_prices'>
								".$utils->digitsToRazryadi( $data['lense_price'] )." <img src='".$mysql->settings['local_folder']."images/mini_ruble.png' />
							</div>
							" : "" )."
                                                            
                                                        ".( $dopPrice ? "<div class='clear' style='height: 5px;'></div>
							<div class='block_image'>&nbsp;</div><div class='block_text'>
								<div>Включая допы:</div>
							</div><div class='block_prices small'>
								".$utils->digitsToRazryadi( $dopPrice )." <img src='".$mysql->settings['local_folder']."images/mini_ruble.png' />
							</div>
							" : "" )."
                                                            
							<div class='clear' style='height: 5px;'></div>
                        </div>";
                    }
                    
                    $t .= "</div>";
                    
                    $t .= "<div class='ordersListElemBottom'><div class='h'></div><div class='itog'><div class='h'></div><span>Итого</span><label>".$utils->digitsToRazryadi( $rr['summa'] )."</label><i class='fa fa-ruble'></i></div></div>";
                    
                    $t .= "<div class='clear' style='height: 40px;'></div>";
		}
		
		return "<h2 class='inner_title'>Список моих заказов</h2>".( $t ? $t : "<div>Список пуст</div>" );
	}
	
	function getDeliveredOrdersScreen()
	{
		return $this->getOrdersScreen( ORDER_DELEAVERED_PAYED );
	}
	
	function printOrder( $order )
	{
		global $lang, $main, $mysql, $query, $utils;
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
                
                $userArray = $main->users->getUserById( $order['user'] );
		$profile = $main->users->getUserProfile( $order['user'] );
		
		$catalog = $main->modules->gmi( "catalog" );
                $lenses = $main->modules->gmi( "lenses" );
		
		$t = "
		<!DOCTYPE html  \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns=\"http://www.w3.org/1999/xhtml\">
		<head>
			<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />
			<meta http-equiv=\"content-language\" content=\"".$lang->currentLanguageCL."\" />
			<title>Заказ № ".$order['id']." в интернет магазине «".$lang->gp( 9 )."»</title>
                            <style>
                            table.receipt { border: 1px solid #aaaaaa !important; margin-bottom: 15px; }
table.receipt td { padding: 4px !important; padding-left: 15px !important; padding-right: 15px !important; font-size: 12px !important; border-top: none !important; border-left: none !important; border-right: 1px solid #aaaaaa !important; border-bottom: 1px solid #aaaaaa !important; text-align: center !important; }
table.receipt td.first { padding-left: 6px !important; padding-right: 6px !important; text-align: left !important;  }
table.receipt td.last { border-right: none !important; }
table.receipt td.bottom { border-bottom: none !important; }
                            </style>
		</head>
		
		<body><center>
			<table cellspacing=0 cellpadding=0 border=0 width=900><tr><td align=left>
				<img src=\"/images/logo.png\" style='float: right;' />
				<h2>Заказ № ".$order['id']." от ".$utils->getFullDate( $order['date'] )."</h2>
				<div>Заказчик: <b>".( $profile ? $profile['name']." ".$profile['suname'] : "Не указан" )."</b></div>
				<div>Адрес доставки: <b>".( $profile ?  $profile['city']." ".$profile['adress'] : "Не указан" )."</b></div>
				<div>Контактный телефон: <b>".( $order ? $profile['phone'] : "Не указан" )."</b></div>
				<div>E-Mail для связи: <b>".( $userArray ? $userArray['ulogin'] : "Не указан" )."</b></div>
				<div>Комментарий к заказу: <b>".( $order ? $profile['comments'] : "Не указан" )."</b></div>
				<div>Метод доставки: <b>".$main->modules->gmi( "delivery" )->getDeliveryName( $order['dmethod'] )."</b></div>
				<div>Способ оплаты: <b>".$main->listings->getListingElementValueById( 21, $order['pmethod'], true )."</b></div>
			</td></tr><tr><td align=left><br>
			<table cellspacing=0 cellpadding=0 border=1 width=100%>
				<tr>
					<td align=center valign=middle width=30% nowrap>
						Товары
					</td>
					<td  align=center valign=middle width=15%>
						Кол-во
					</td>
					<td align=center valign=middle width=15%>
						Цена, руб.
					</td>
					<td align=center valign=middle width=15%>
						Стоимость, руб.
					</td>
				</tr>
		";
		
		$localAdress = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'];

                $deliveryPrice = $main->modules->gmi( "delivery" )->getDeliveryPrice( $r['dmethod'] );
			
                        $goodsStr = "";
                        
                $basketItems = array();
                $aa = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `order`=".$order['id'] );
		while( $rr = @mysql_fetch_assoc( $aa ) ) {
                    $oprava = $catalog->getItem( $rr['ochkiid'] );
                    $opravaRoot = $oprava['root'] ? $catalog->getItem( $oprava['root'] ) : 0;
                    if( $opravaRoot )
			$oprava['name'] = $opravaRoot['name'];
                    $oprava['properties'] = $main->properties->getPropertiesOfGood( $oprava['id'] );
                    $preview = $this->getElementByData( $oprava['properties'], "prop_id", 14 );
                    $preview = $preview ? $preview['value'] : '';
                    $color = $this->getElementByData( $oprava['properties'], "prop_id", 1 );
                    $colorName = $color && is_numeric( $color['value'] ) ? $main->listings->getListingElementValueById( 3, $color['value'], true ) : $color['value'];
                    $size = $this->getElementByData( $oprava['properties'], "prop_id", 19 );
                    $size = $size ? $main->listings->getListingElementValueById( 27, $size['value'], true ) : '';
                    $main_summa += $rr['price'];
                    $rr['oprava'] = $oprava;
                    $rr['preview'] = $preview;
                    $rr['size'] = $size;
                    $rr['color'] = $color;
                    $rr['colorName'] = $colorName;
                    $basketItems[$rr['id']] = $rr;
                    
                    $lense = $lenses->getLenseWithId( $rr['lense'] );
                    
                    $array = json_decode( $rr['add'] );
                    
                    if( $array->add && $lense ) {
                        $prices = $main->listings->getListingElementsArray( 13, $array->p, false, '', true );
                        if( isset( $prices[$array->add] ) ) {
                            $lense['add_price'] = $prices[$array->add]['additional_info'];
                        }
                    }

                    
                    
                    $goodsStr .= "
				<tr>
					<td align=left valign=middle style='text-align: left; line-height: 60px;'><div style='display: inline-block;' id='oprava_name".$r['id']."_".$rr['id']."'>
					".( $oprava['view'] ? "
						
							".( $preview ? "<img src=\"".$localAdress."files/upload/goods/thumbs/".$preview."\" style='max-height: 60px; float: left; margin-right: 13px;' />" : "" ).$oprava['name'].", ".$colorName."
					" : ( $preview ? "<img src=\"".$localAdress."files/upload/goods/thumbs/".$preview."\" style='max-height: 60px; float: left; margin-right: 13px;' />" : "" )."<label style='text-decoration: line-through;'>".$oprava['name'].", ".$colorName."</label>" )."</div>
                                        
                                            
					</td>
                                        <td align=center>".$rr['count']."</td>
					<td align=center valign=middle>
						".$utils->digitsToRazryadi( $oprava['price'] )."
					</td>
                                        <td align=center valign=middle>
						".$utils->digitsToRazryadi( $oprava['price'] * $rr['count'] )."
					</td>
				</tr>
				<tr>
					<td align=left valign=middle style='text-align: left; padding: 10px;'>
						Линзы:<br/>
						".( $lense ? "
						<label id='lense_name".$r['id']."_".$rr['id']."'>".$lense['name']."</label>
						<p style='margin-top: 5px; margin-bottom: 5px;'>
							".$main->listings->getListingElementValueById( 13, $array->p, true ).( $array->add ? ": <span>".$main->listings->getListingElementValueById( 13, $array->add, true )."; ".$this->getCorrectColorName( $array->add_color, $array->add ).( $array->add_move ? "; Переход" : "" ).( $array->add_shadow ? "; Прозрачность ".$main->listings->getListingElementValueById( 19, $array->add_shadow, true ) : "" )."</span>" : '' )."
						</p>
						Рецепт<br/>
						<table cellspacing=0 class='receipt'>
							<tr>
								<td class='first'></td>
								<td>SPH</td>
								<td>CYL</td>
								<td>AXIS</td>
								<td>ADD</td>
								<td class='last'>PD</td>
							</tr>
							<tr>
								<td class='first'>Левый</td>
								<td>".$main->listings->getListingElementValueById( 14, $array->os_sph, true )."</td>
								<td>".$main->listings->getListingElementValueById( 22, $array->os_cyl, true )."</td>
								<td>".$main->listings->getListingElementValueById( 23, $array->os_axis, true )."</td>
								<td>".$main->listings->getListingElementValueById( $array->t == 78 ? 29 : 24, $array->os_add, true )."</td>
								<td".( !$array->oculus_pd_d && !$array->oculus_pd_s ? " rowspan=2 class='last bottom'" : " class='last'" ).">".( !$array->oculus_pd_d && !$array->oculus_pd_s ? $main->listings->getListingElementValueById( 25, $array->oculus_pd, true ) : $main->listings->getListingElementValueById( 26, $array->oculus_pd_s, true ) )."</td>
							</tr>
							<tr>
								<td class='first bottom'>Правый</td>
								<td class='bottom'>".$main->listings->getListingElementValueById( 14, $array->od_sph, true )."</td>
								<td class='bottom'>".$main->listings->getListingElementValueById( 22, $array->od_cyl, true )."</td>
								<td class='bottom'>".$main->listings->getListingElementValueById( 23, $array->od_axis, true )."</td>
								<td class='bottom'>".$main->listings->getListingElementValueById( $array->t == 78 ? 29 : 24, $array->od_add, true )."</td>
								".( !$array->oculus_pd_d && !$array->oculus_pd_s ? "" : "<td class='last bottom'>".$main->listings->getListingElementValueById( 26, $array->oculus_pd_d, true )."</td>" )."
							</tr>
						</table>
						" : "Не выбрана" )."
					</td>
                                        <td align=center>".$rr['count']."</td>
					<td align=center valign=middle>
						".( $lense ? 
						  	"Линзы - <span id='lenseprice_".$r['id']."'>".$utils->digitsToRazryadi( $rr['lense_price'] )."</span><br/>
							Допы - ".$utils->digitsToRazryadi( $lense['add_price'] ) : 
						  0 )."
					</td>
					<td align=center valign=middle >
						".( $lense ? 
						  	"Линзы - <span id='lenseprice_".$r['id']."'>".$utils->digitsToRazryadi( $rr['lense_price'] )."</span><br/>
							Допы - ".$utils->digitsToRazryadi( $lense['add_price'] ) : 
						  0 )."
					</td>
				</tr>";
                }
                
                $t .= $goodsStr;
			
                $deliveryPrice = $main->modules->gmi( "delivery" )->getDeliveryPrice( $order['dmethod'] );
		if( $deliveryPrice ) {
				$t .= "
				<tr>
					<td align=left valign=middle style='padding: 5px;'>
						Доставка
					</td>
					<td align=center valign=middle style='padding: 5px;'>
						1
					</td>
					<td align=center valign=middle style='padding: 5px;'>
						".$utils->digitsToRazryadi( $deliveryPrice )."
					</td>
					<td align=center valign=middle style='padding: 5px;'>
						".$utils->digitsToRazryadi( $deliveryPrice )."
					</td>
				</tr>
				";
				$gSumma += $deliveryPrice;
				$gCount++;
		}
                
                $discount = $main->users->getSessionOptionWithSID( $order['sid'], 33 );
                if( $discount ) {
                    $t .= "<tr>
					<td align=left valign=middle style='padding: 5px;'>
						Скидка
					</td>
					<td align=center valign=middle style='padding: 5px;'>
						-
					</td>
					<td align=center valign=middle style='padding: 5px;'>
						".$utils->digitsToRazryadi( $main->users->getSessionOptionWithSID( $order['sid'], 33 ) )."
					</td>
					<td align=center valign=middle style='padding: 5px;'>
						".$utils->digitsToRazryadi( $main->users->getSessionOptionWithSID( $order['sid'], 33 ) )."
					</td>
				</tr>";
                }
		
		$t .= "
                        
			<tr>
				<td align=right valign=middle style='padding: 10px;'>
					Итого (с учетом скидок и доставки)
				</td>
				<td align=center valign=middle style='padding: 10px;'>
					–
				</td>
				<td align=center valign=middle style='padding: 10px;'>
					–
				</td>
				<td align=center valign=middle style='padding: 10px;'>
					<b>".$utils->digitsToRazryadi( $order['summa'] )." руб.</b>
				</td>
			</tr>
		";
		
		$t .= "</table>
		<br><br>
		<hr>
		<div style='float: right;'>
			".$main->templates->psl( $lang->gp( 24, true ) )."
		</div>
		".$main->templates->psl( $lang->gp( 9 ), true )."
		</td></tr></table>
		</body>
		</html>";
		
		echo $t;
	}
	
	function paymentOrder( $order )
	{
		global $lang, $main, $mysql, $query, $utils;
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
                if( $order['status'] == ORDER_READY_PAYD_100_CONFIRMED || $order['status'] == ORDER_READY_PAYD_100_NEED_CONFIRM || $order['status'] == ORDER_READY_PAYD_50_CONFIRMED || $order['status'] == ORDER_READY_PAYD_50_NEED_CONFIRM ) {
                    return "<div class='sopli'><div class='all_lines'>
				<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>Оплата заказа
			</div></div>
			
			<div class='catalog catalog_nomargin catalog_marginbottom'>
				<div class='all_lines'>
					<h1 class='pageTitle fullyOwned'>Оплата ".$this->getName()."а № ".$order['id']." на сумму ".$utils->digitsToRazryadi( $order['pmethod'] == 700 ? round( $order['summa'] / 2, 2 ) : $order['summa'] )." руб.</h1>
                                        <div>
                                            Ваш заказ уже оплачен! Спасибо!
                                        </div>
				</div>
			</div>";
                }
                
                if( $order['status'] != ORDER_READY_TO_PAY_100 && $order['status'] != ORDER_READY_TO_PAY_50 ) {
                    return "<div class='sopli'><div class='all_lines'>
				<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>Оплата заказа
			</div></div>
			
			<div class='catalog catalog_nomargin catalog_marginbottom'>
				<div class='all_lines'>
                                        <div>
                                            Ваш заказ не требует безналичной оплаты
                                        </div>
				</div>
			</div>";
                }
                
                $order['uuid'] = $main->modules->gmi( 'yandex_payments' )->gen_uuid();
                $mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `uuid`='".$order['uuid']."' WHERE `id`=".$order['id'] );
                
                return "<div class='sopli'><div class='all_lines'>
				<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>Оплата заказа
			</div></div>
			
			<div class='catalog catalog_nomargin catalog_marginbottom'>
				<div class='all_lines'>
					<h1 class='pageTitle fullyOwned'>Оплата заказа № ".$order['id']." на сумму ".$utils->digitsToRazryadi( $order['pmethod'] == 700 ? round( $order['summa'] / 2, 2 ) : $order['summa'] )." руб.</h1>
                                        <div id='result'>
                                            Открываем окно для оплаты...
                                        </div>
                                        <div style='display: none; margin-top: 20px; color: #ff0000;' id='payerror'>".$lang->gp( 132, true )."</div>
				</div>
			</div>
                <script>
                    $(window).load(function()
                    {
                        setTimeout( function(){ getLinkforpayment(); }, 1500 );
                    });
                    function getLinkforpayment()
                    {
                        processSimpleAsyncReqForModule( 'yandex_payments', '1', '&order=".$order['id']."', 'processAfterCallback( data );' );
                    }
                    function processAfterCallback( data )
                    {
                        var ar = data.toString().split( '^' );
                        if( ar.length > 1 && ( ar[0] == '1' || ar[0] == 1 ) ) {
                            $( '.fixed_payment' ).html( \"<div class='h'></div><div class='window'><div class='closer' onclick='closeFixedPayment();'><img src='/images/smx.png' alt='closer' /></div><div class='inner'><iframe src='\" + ar[1] + \"'></iframe></div></div>\" );
                            $( '.fixed_payment' ).fadeIn( 300, function() { $( '.fixed_payment .window .inner iframe' ).height( $( '.fixed_payment .window' ).height() - 40 ); } );
                            $( '#result' ).html( 'Проведение оплаты...' );
                            startPaymentProcessCheck();
                        } else {
                            $( '#payerror' ).show();
                        }
                    }
                    function closeFixedPayment()
                    {
                        $( '.fixed_payment' ).fadeOut( 300, function() { $( '.fixed_payment' ).html(''); } );
                    }
                    
                    var checkTimer = null;
                    function startPaymentProcessCheck()
                    {
                        checkTimer = setTimeout( function() { checkPaymentOnline(); }, 1000 );
                    }
                    
                    function checkPaymentOnline()
                    {
                        processSimpleAsyncReqForModule( 'yandex_payments', '2', '&order=".$order['id']."', 'contollerForPayment( data );' );
                    }
                    
                    function contollerForPayment( data )
                    {
                        if( data == 1 || data == '1' ) {
                            $( '#result' ).html( 'Платеж по заказу №".$order['id']." успешно проведен!<br/>Наш менеджер свяжется с Вами в ближайшее время!' );
                            $( '.fixed_payment' ).find( 'iframe' ).attr( 'src', 'https://".$_SERVER['HTTP_HOST']."/payment_ready' );
                            return;
                        }
                        checkTimer = setTimeout( function() { checkPaymentOnline(); }, 1000 );
                    }
                </script>";
	}
	
	// Админка
	
	function getNewOrderAdminBlock()
	{
		global $lang, $main, $mysql, $query, $utils, $admin;
		
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `status`=".ORDER_NEW." OR `status`=".ORDER_READY_TO_PAY_100." OR `status`=".ORDER_READY_TO_PAY_50." OR `status`=".ORDER_READY_PAYD_100_NEED_CONFIRM." OR `status`=".ORDER_READY_PAYD_50_NEED_CONFIRM." ORDER BY `date` DESC" );
		$itemsCount = @mysql_num_rows( $a );
		
		if( !$itemsCount )
			return "";
			
		return "
		<div style='position: absolute; z-index: 1000; right: 50px; top: 25px; width: 200px; height: 35px; border: 3px solid #ff0000; background-color: #fff; text-align: center;'>
			<div style='margin-top: 6px; color: #fff; font-size: 14px;'><a href=\"".LOCAL_FOLDER."admin/modules/order/showallnew\">Есть новые заказы: ".$itemsCount."</a></div>
		</div>
		";
	}
	
	function getAdminScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;

		$show = $query->gp( "show" );
                $showallnew = $query->gp( "showallnew" );
		$month = $query->gp( "month" );
		$catalog = $main->modules->gmi( "catalog" );
		$lenses = $main->modules->gmi( "lenses" );
		$filter = $query->gp( "filter" );
		
		$tt = explode( "/", date( "d/m/Y" ) );
		$currentDay = intval( $tt[0] );
		$currentMonth = intval( $tt[1] );
		$currentYear = intval( $tt[2] );
		$monthes = "";
		for( $i = 1; $i <= 12; $i++ ) {
			$needDay = 1;
			$needMonth = $currentMonth - $i > 0 ? $currentMonth - $i : 13 - $i;
			$needYear = $currentMonth - $i <= 0 ? $currentYear - 1 : $currentYear;
			$d = mktime( "00", "00", "00", $needMonth, $needDay, $needYear );
			$monthes .= "<option value=".$d.( $month == $d ? " selected" : "" ).">Заказы ".$utils->getFullDate( $d, false, true )."</option>";
		}
		
		if( $query->gp( "edit" ) && $query->gp( "orderid" ) && $query->gp( "process" ) ) {
			
			$orderid = $query->gp( "orderid" );
                        $orderData = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `id`=".$orderid );
			
                        $city = isset( $_POST['city'] ) && $_POST['city'] ? str_replace( "&nbsp;", " ", $_POST['city'] ) : '';
			$adress = isset( $_POST['adress'] ) && $_POST['adress'] ? str_replace( "&nbsp;", " ", $_POST['adress'] ) : '';
			$phone = isset( $_POST['phone'] ) && $_POST['phone'] ? str_replace( "&nbsp;", " ", $_POST['phone'] ) : '';
			$comments = isset( $_POST['comments'] ) && $_POST['comments'] ? str_replace( "'", " ", $_POST['comments'] ) : '';
			
			$dmethod = $query->gp( "dmethod" );
			$pmethod = $query->gp( "pmethod" );
                        
                        $oldDeliveryPrice = $main->modules->gmi( "delivery" )->getDeliveryPrice( $orderData['dmethod'] );
			$deliveryPrice = $main->modules->gmi( "delivery" )->getDeliveryPrice( $dmethod );
                        
                        $summa = $orderData['summa'] - $oldDeliveryPrice + $deliveryPrice;
			
			$mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET 
			
				`dmethod`='".$dmethod."',
				`pmethod`='".$pmethod."',
                                `summa`=".$summa."
				
				WHERE `id`=".$orderid 
			
			);
                        
                        $main->users->updateProfileField( $orderData['user'], 'city', $city );
                        $main->users->updateProfileField( $orderData['user'], 'adress', $adress );
                        $main->users->updateProfileField( $orderData['user'], 'phone', $phone );
                        $main->users->updateProfileField( $orderData['user'], 'comments', $comments );
                        
			$query->setProperty( "orderid", 0 );
			
		} else if( $query->gp( "delete" ) && $query->gp( "orderid" ) ) {
			
			$mysql->mu( "DELETE FROM `".$mysql->t_prefix."order` WHERE `id`=".$query->gp( "orderid" ) );
			$mysql->mu( "DELETE FROM `".$mysql->t_prefix."basket` WHERE `order`=".$query->gp( "orderid" ) );
			
			$query->setProperty( "orderid", 0 );
			
		} else if( $query->gp( "ch_status" ) && $query->gp( "order_id" ) ) {
			
			//$gsumma = $query->gp( "gsumma" );
			
			//$mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `status`=".$query->gp( "ch_status" ).", `last_action_date`=".time().", `summa`=".$gsumma." WHERE `id`=".$query->gp( "order_id" ) );
			$cancel_reason = isset( $_POST['cancel_reason'] ) && $_POST['cancel_reason'] ? str_replace( "&nbsp;", " ", $_POST['cancel_reason'] ) : '';
			$mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `status`=".$query->gp( "ch_status" ).", `last_action_date`=".time().( $query->gp( "ch_status" ) == 251 && $cancel_reason ? ", `cancel_reason`='".$cancel_reason."'" : "" )." WHERE `id`=".$query->gp( "order_id" ) );
			
		}
		
		$selectedElement = $admin->userLevel == 1 ? 1 : 0;
		
		if( $query->gp( "edit" ) && $query->gp( "orderid" ) ) {
			return $this->getExternalEditOrder( $path );
		}
		
		if( $query->gp( "stat" ) )
			return $this->getAdminStatScreen( $path );

		if( $query->gp( "timestat" ) )
			return $this->getAdminTimeStatScreen( $path );	
		
		$t = "
			<h1>Список заказов <small>(сверху последние)</small></h1>
			
			<form method=POST action='".LOCAL_FOLDER."admin/".$path."' onsubmit=\"if( $( '#show' ).val() != '0' ) this.action = '".LOCAL_FOLDER."admin/".$path."/show' + $( '#show' ).val(); return true;\" id='orders_form'>
				<input type=hidden name='ch_status' id='ch_status' value=0 />
				<input type=hidden name='order_id' id='order_id' value=0 />
				<input type=hidden name='gsumma' id='gsumma' value=0 />
				<input type=hidden name='cancel_reason' id='cancel_reason' value='' />
				<select name='show' id='show' onchange=\"$( '#orders_form' ).submit();\">
					".$main->listings->getListingForSelecting( 20, $show, 0, "<option value=0".( !$show ? " selected" : "" ).">Выберите статус заказов, которые нужно показывать в списке</option>", "", false, '', true )."
				</select>&nbsp;&nbsp;&nbsp;&nbsp;
				<select name='month' id='month' onchange=\"$( '#orders_form' ).submit();\">
					<option value=10000".( $month == 10000 ? " selected" : "" ).">Показывать все заказы</option>
					<option value=0".( !$month ? " selected" : "" ).">Заказы текущего месяца</option>
					".$monthes."
				</select>&nbsp;&nbsp;&nbsp;&nbsp;
				<select name='filter' id='filter' onchange=\"$( '#orders_form' ).submit();\">
					<option value=0".( !$filter ? " selected" : "" ).">Показывать все заказы</option>
					<option value=1".( $filter == 1 ? " selected" : "" ).">Заказы с онлайн оплатой</option>
				</select>
			</form>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%;'>
				<tr class='list_table_header'>
					<td width=40 nowrap>
						№
					</td>
					<td width=170>
						Статус<br>
						<img src=\"".LOCAL_FOLDER."images/s.gif\" width=170 height=1 />
					</td>
					<td width=55% style='text-align: left;'>
						Дата заказа и товары
					</td>
					<td width=30% style='text-align: left;'>
						Контактное лицо, адрес, телефон и почта
					</td>
					<td width=15% style='text-align: left;'>
						Метод доставки и способ оплаты
					</td>
					<td width=110>
						Опции
					</td>
				</tr>
		";
		
		$wdate = "";
		if( !$month ) {
			$d = mktime( "00", "00", "00", $currentMonth, 1, $currentYear );
			$wdate = "`date`>=".$d;
		} else if( $month == 10000 ) {
			
		} else {
			$tt = explode( "/", date( "d/m/Y", $month ) );
			$selectedMonth = intval( $tt[1] );
			$selectedYear = intval( $tt[2] );
			$needDay = 1;
			$needMonth = $selectedMonth == 12 ? 1 : $selectedMonth + 1;
			$needYear = $needMonth == 1 ? $selectedYear + 1 : $selectedYear;
			$d = mktime( "00", "00", "00", $needMonth, $needDay, $needYear );
			$wdate = "`date`>=".$month." AND `date`<=".$d;
		}
		
		$where = $wdate;
		
		if( $show )
                    $where .= ( $where ? " AND " : "" )."`status`=".$show;
                if( $showallnew )
                    $where .= ( $where ? " AND " : "" )." (`status`=135 OR `status`=136 OR `status`=704 OR `status`=137 OR `status`=702)";
			
		if( $filter == 1 )
			$where .= ( $where ? " AND " : "" )."(`pmethod`=143 OR `pmethod`=700 OR `payment_data`<>'')";
			
		$localAdress = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'];
		$ColorsList = $main->listings->getListingElementsArraySpec( 2, "`order` DESC, `id` ASC", "", -1, true );
		$counter = 0;
		if( !$where ) 
			$where = "1";
                
                $lensesArray = $lenses->getListForOrders();
                $opravaArray = $catalog->getListForOrders();
                
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."order` WHERE ".$where." ORDER BY `date` DESC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			
			$delivered = $r['status'] == 139 ? true : false;
				
			$deliveryPrice = $main->modules->gmi( "delivery" )->getDeliveryPrice( $r['dmethod'] );
			
                        $goodsStr = "";
                        
                $basketItems = array();
                $main_summa = 0;
                $aa = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `order`=".$r['id'] );
		while( $rr = @mysql_fetch_assoc( $aa ) ) {
                    $oprava = $catalog->getItem( $rr['ochkiid'] );
                    $opravaRoot = $oprava['root'] ? $catalog->getItem( $oprava['root'] ) : 0;
                    if( $opravaRoot )
			$oprava['name'] = $opravaRoot['name'];
                    $oprava['properties'] = $main->properties->getPropertiesOfGood( $oprava['id'] );
                    $preview = $this->getElementByData( $oprava['properties'], "prop_id", 14 );
                    $preview = $preview ? $preview['value'] : '';
                    $color = $this->getElementByData( $oprava['properties'], "prop_id", 1 );
                    $colorName = $color && is_numeric( $color['value'] ) ? $main->listings->getListingElementValueById( 3, $color['value'], true ) : $color['value'];
                    $size = $this->getElementByData( $oprava['properties'], "prop_id", 19 );
                    $size = $size ? $main->listings->getListingElementValueById( 27, $size['value'], true ) : '';
                    $main_summa += $oprava['price'] + $rr['lense_price'];
                    $rr['oprava'] = $oprava;
                    $rr['preview'] = $preview;
                    $rr['size'] = $size;
                    $rr['color'] = $color;
                    $rr['colorName'] = $colorName;
                    $basketItems[$rr['id']] = $rr;
                    
                    $lense = $lenses->getLenseWithId( $rr['lense'] );
                    
                    $array = json_decode( $rr['add'] );
                    
                    if( $array->add && $lense ) {
                        $prices = $main->listings->getListingElementsArray( 13, $array->p, false, '', true );
                        if( isset( $prices[$array->add] ) ) {
                            $lense['add_price'] = $prices[$array->add]['additional_info'];
                        }
                    }

                    
                        $opravaOptions = "";
                        foreach( $opravaArray as $o_id => $o_data ) {
                            if( $rr['ochkiid'] != $o_id )
                                $opravaOptions .= "<option value=".$o_id.">".$o_data['name']."</option>";
                        }
                        
                        $lensesOptions = "";
                        foreach( $lensesArray as $l_id => $l_data ) {
                            if( ( $lense && $lense['id'] != $l_id ) || !$lense )
                                $lensesOptions .= "<option value=".$l_id.">".$l_data['name']."</option>";
                        }
                        
                        
                        $discount = $main->modules->gmi( "discount" )->getDiscountForGood( $oprava, $rr['date'] );
                $oprava['discount_price'] = $discount ? $oprava['price'] - ( $oprava['price'] / 100 * $discount['percent'] ) : 0;
                    
                    $goodsStr .= "
				<tr id='tr_".$r['id']."_".$rr['id']."'>
					<td align=left valign=middle style='text-align: left; line-height: 60px;'><div style='display: inline-block;' id='oprava_name".$r['id']."_".$rr['id']."'>
					".( $oprava['view'] ? "
						<a href=\"".$localAdress."catalog/show".$rr['ochkiid']."\" target=_BLANK>
							".( $preview ? "<img src=\"".$localAdress."files/upload/goods/thumbs/".$preview."\" style='max-height: 60px; float: left; margin-right: 13px;' />" : "" ).$oprava['name'].", ".$colorName.( $discount ? ", скидка на оправу ".$discount['percent']."%" : "" )."
						</a>
					" : ( $preview ? "<img src=\"".$localAdress."files/upload/goods/thumbs/".$preview."\" style='max-height: 60px; float: left; margin-right: 13px;' />" : "" )."<label style='text-decoration: line-through;'>".$oprava['name'].", ".$colorName."</label>" )."</div>
                                        <select id='newoprava_order".$r['id']."_".$rr['id']."' style='margin-left: 10px;' onchange=\"processSimpleAsyncReqForModuleAdmin( '".$this->dbinfo['local']."', 8, '&order=".$r['id']."&basketitem=".$rr['id']."&newoprava=' + $( this ).val(), 'updateOrderItemAfterOpravaChange( data, ".$r['id'].", ".$rr['id']." );' );\"><option value=0 selected>Вы можете выбрать другую оправу...</option>".$opravaOptions."</select>
                                            
					</td>
					<td align=center valign=middle id='opravaprice_".$r['id']."_".$rr['id']."'>
						".$utils->digitsToRazryadi( $oprava['discount_price'] ? $oprava['discount_price'] : $oprava['price'] ).( $discount ? "<br/>(начальна цена ".$oprava['price'].")" : "" )."
					</td>
				</tr>
				<tr>
					<td align=left valign=middle style='text-align: left;'>
						Линзы:<br/>
						".( $lense ? "
						<label id='lense_name".$r['id']."_".$rr['id']."'>".$lense['name']."</label> <select id='newlense_order".$r['id']."_".$rr['id']."' style='margin-left: 10px;' onchange=\"processSimpleAsyncReqForModuleAdmin( '".$this->dbinfo['local']."', 7, '&order=".$r['id']."&basketitem=".$rr['id']."&newlense=' + $( this ).val(), 'updateOrderItemAfterLenseChange( data, ".$r['id'].", ".$rr['id']." );' );\"><option value=0 selected>Вы можете выбрать другую лизну...</option>".$lensesOptions."</select>
						<p style='margin-top: 5px; margin-bottom: 5px;'>
							".$main->listings->getListingElementValueById( 13, $array->p, true ).( $array->add ? ": <span>".$main->listings->getListingElementValueById( 13, $array->add, true )."; ".$this->getCorrectColorName( $array->add_color, $array->add ).( $array->add_move ? "; Переход" : "" ).( $array->add_shadow ? "; Прозрачность ".$main->listings->getListingElementValueById( 19, $array->add_shadow, true ) : "" )."</span>" : '' )."
						</p>
						Рецепт<br/>
						<table cellspacing=0 class='receipt'>
							<tr>
								<td class='first'></td>
								<td>SPH</td>
								<td>CYL</td>
								<td>AXIS</td>
								<td>ADD</td>
								<td class='last'>PD</td>
							</tr>
							<tr>
								<td class='first'>Левый</td>
								<td>".$main->listings->getListingElementValueById( 14, $array->os_sph, true )."</td>
								<td>".$main->listings->getListingElementValueById( 22, $array->os_cyl, true )."</td>
								<td>".$main->listings->getListingElementValueById( 23, $array->os_axis, true )."</td>
								<td>".$main->listings->getListingElementValueById( $array->t == 78 ? 29 : 24, $array->os_add, true )."</td>
								<td".( !$array->oculus_pd_d && !$array->oculus_pd_s ? " rowspan=2 class='last bottom'" : " class='last'" ).">".( !$array->oculus_pd_d && !$array->oculus_pd_s ? $main->listings->getListingElementValueById( 25, $array->oculus_pd, true ) : $main->listings->getListingElementValueById( 26, $array->oculus_pd_s, true ) )."</td>
							</tr>
							<tr>
								<td class='first bottom'>Правый</td>
								<td class='bottom'>".$main->listings->getListingElementValueById( 14, $array->od_sph, true )."</td>
								<td class='bottom'>".$main->listings->getListingElementValueById( 22, $array->od_cyl, true )."</td>
								<td class='bottom'>".$main->listings->getListingElementValueById( 23, $array->od_axis, true )."</td>
								<td class='bottom'>".$main->listings->getListingElementValueById( $array->t == 78 ? 29 : 24, $array->od_add, true )."</td>
								".( !$array->oculus_pd_d && !$array->oculus_pd_s ? "" : "<td class='last bottom'>".$main->listings->getListingElementValueById( 26, $array->oculus_pd_d, true )."</td>" )."
							</tr>
						</table>
						" : "Не выбрана <select id='newlense' style='margin-left: 10px;' onchange=\"processSimpleAsyncReqForModuleAdmin( '".$this->dbinfo['local']."', 7, '&order=".$r['id']."&basketitem=".$rr['id']."&newlense=' + $( this ).val(), 'updateOrderItemAfterLenseChange( data, ".$r['id'].", ".$rr['id']." );' );\"><option value=0 selected>Вы можете выбрать новую лизну...</option>".$lensesOptions."</select>" )."
					</td>
					<td align=center valign=middle id='td1_".$r['id']."_delivery'>
						".( $lense ? 
						  	"Линзы - <span id='lenseprice_".$r['id']."'>".$utils->digitsToRazryadi( $rr['lense_price'] )."</span><br/>
							Допы - ".$utils->digitsToRazryadi( $lense['add_price'] ) : 
						  0 )."
					</td>
				</tr>";
                }
						
			$goodsStr .= "
				<tr id='tr_".$r['id']."_delivery'>
					<td align=left valign=middle style='text-align: left;'>
						Доставка
					</td>
					<td align=center valign=middle id='td1_".$r['id']."_delivery'>
						".$utils->digitsToRazryadi( $deliveryPrice )."
					</td>
				</tr>
                                <tr id='tr_".$r['id']."_delivery'>
					<td align=left valign=middle style='text-align: left;'>
						Скидка
					</td>
					<td align=center valign=middle id='td1_".$r['id']."_delivery'>
						<span id='discount_price".$r['id']."'>".( $r['summa'] - $deliveryPrice - $main_summa < 0 ? $utils->digitsToRazryadi( abs( $r['summa'] - $deliveryPrice - $main_summa ) ) : 0 )."</span>
					</td>
				</tr>
				<tr id='tr_".$r['id']."_itogo'>
					<td align=right valign=middle style='text-align: right;'>
						Итого
					</td>
					<td align=center valign=middle id='td1_".$r['id']."_itogo'>
						<span id='totalsumma_".$r['id']."'>".$utils->digitsToRazryadi( $r['summa'] )."</span>
					</td>
				</tr>
			";
			
			$userArray = $main->users->getUserById( $r['user'] );
			$profile = $main->users->getUserProfile( $r['user'] );
			
			$t .= "
				<tr class='list_table_element'>
					<td valign=top valign=middle>
						".$r['id']."
					</td>
					<td class='order_".$r['status']."' style='padding: 3px;' align=center valign=middle id='td_order_".$r['id']."'>
						<div class='order_".$r['status']."' id='td_div_order_".$r['id']."' style='margin-bottom: 5px; font-size: 14px;'>".$main->listings->getListingElementValueById( 20, $r['status'], true )."</div>
						<select style='width: 120px;' id='st_change_".$r['id']."' onchange=\"
							$( '#rc_".$r['id']."' ).hide();
							if( this.value == 0 )
								return;
							if( this.value == 140 || this.value == 141 ) {
								$( '#rc_".$r['id']."' ).show();
								return;
							}
							if( this.value == 139 ) {
								$( '#dc_".$r['id']."' ).show();
								return;
							}
							if( !confirm( 'Вы уверены?' ) ) {
								this.value = 0;
								return false;
							}
							processSimpleAsyncReqForModuleAdmin( '".$this->dbinfo['local']."', 3, '&order=".$r['id']."&ch_status=' + this.value, 'updateOrderItemAfterStateChange( data, ".$r['id']." );' );
							$( '#st_change_".$r['id']."' ).val( 0 );
						\">
							".$main->listings->getListingForSelecting( 20, 0, 0, "<option value=0>смена статуса</option>", "", true, "`id`<>".$r['status']." AND `id`<>137 AND `id`<>702", true )."
						</select>
						<div id='rc_".$r['id']."' style='text-align: center; display: none; color: #fff;'>Укажите причину:<br><input type=text value='' id='rc_text_".$r['id']."' /><br><input type=button value='Отправить' onclick=\"
							
							var reas = $( '#rc_text_".$r['id']."' ).val();
							if( reas.length == 0 ) {
								alert( 'Нужно указать причину' );
								return;
							}
							if( !confirm( 'Вы уверены?' ) ) {
								$( '#rc_text_".$r['id']."' ).val( '' );
								$( '#st_change_".$r['id']."' ).val( 0 );
								$( '#rc_".$r['id']."' ).hide();
								return false;
							}
							processSimpleAsyncReqForModuleAdmin( '".$this->dbinfo['local']."', 3, '&order=".$r['id']."&ch_status=' + $( '#st_change_".$r['id']."' ).val() + '&cancel_reason=' + reas, 'updateOrderItemAfterStateChange( data, ".$r['id']." );' );
							$( '#rc_text_".$r['id']."' ).val( '' );
							$( '#st_change_".$r['id']."' ).val( 0 );
							$( '#rc_".$r['id']."' ).hide();
						\"/></div>
						<div id='dc_".$r['id']."' style='text-align: center; display: none; color: #fff;'>Укажите дату оплаты (пример: 31/05/2018. Пусто - сейчас):<br><input type=text value='' id='dc_text_".$r['id']."' /><br><input type=button value='Отправить' onclick=\"
							
							var dreas = $( '#dc_text_".$r['id']."' ).val();
							processSimpleAsyncReqForModuleAdmin( '".$this->dbinfo['local']."', 3, '&order=".$r['id']."&ch_status=' + $( '#st_change_".$r['id']."' ).val() + '&date_reason=' + dreas, 'updateOrderItemAfterStateChange( data, ".$r['id']." );' );
							$( '#dc_text_".$r['id']."' ).val( '' );
							$( '#st_change_".$r['id']."' ).val( 0 );
							$( '#dc_".$r['id']."' ).hide();
						\"/></div>
						<div style='text-align: center; color: #fff;' id='div_cancel_reason_".$r['id']."'>".( ( $r['status'] == 140 || $r['status'] == 141 ) && $r['cancel_reason'] ? "Причина: ".$r['cancel_reason']."" : "" )."</div>
					</td>
					<td style='text-align: left;' valign=top>
						<label style='font-weight: bold; color: #ff0000;'>".$utils->getFullDate( $r['date'], true )."</label>
						<table cellspacing=0 cellpadding=0 border=1 width=100% style='margin-top: 5px; background-color: #fff;' id='goodslisting_".$r['id']."'>
							<tr>
								<td align=center valign=middle width=75%>
									<b>Оправа и линзы</b>
								</td>
								<td align=center valign=middle width=25%>
									<b>Стоимость (руб.)</b>
								</td>
							</tr>
							".$goodsStr."
						</table>
					</td>
					<td style='text-align: left;' valign=top>
						<div style='margin-bottom: 5px;'>Заказчик: <b>".$profile['name']." ".$profile['suname']."</b></div>
						<div style='margin-bottom: 5px;'>Адрес: <b>".$profile['city'].", ".$profile['adress']."</b></div>
						<div style='margin-bottom: 5px;'>Телефон: <b>".$profile['phone']."</b></div>
						<div style='margin-bottom: 5px;'>E-Mail: <b>".( $userArray['ulogin'] ? $userArray['ulogin'] : "Пользователь удален" )."</b>".( $userArray['ulogin'] ? " <a href='#' onclick=\"sendMessageForm( ".$r['id'].", '".$userArray['ulogin']."', '".$lang->gp( 165 )."' ); return false;\">Отправить письмо</a>" : "" )."</div>
						<div style='margin-bottom: 5px;'>Комментарий: <b>".( $profile['comments'] ? $profile['comments'] : "Не указан" )."</b></div>
					</td>
					<td style='text-align: left;' valign=top>
						<b>".$main->modules->gmi( "delivery" )->getDeliveryName( $r['dmethod'] )."</b>,&nbsp;
						<b>".$main->listings->getListingElementValueById( 21, $r['pmethod'], true )."</b>
					</td>
					<td valign=top nowrap>
						<div style='margin-bottom: 5px;'><a href=\"".LOCAL_FOLDER."order/printanyfromadministrationmode/print".$r['id']."\" target=_BLANK>Печать документов</a></div>
						<div style='margin-bottom: 5px;'><a href=\"".LOCAL_FOLDER."admin/".$path.( $show ? "/show".$show : "" ).( $month ? "/month".$month : "" ).( $filter ? "/filter".$filter : "" )."/edit/orderid".$r['id']."\">Редактировать</a></div>
						<div style='margin-bottom: 5px;'><a href=\"".LOCAL_FOLDER."admin/".$path.( $show ? "/show".$show : "" ).( $month ? "/month".$month : "" ).( $filter ? "/filter".$filter : "" )."/delete/orderid".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a></div>
					</td>
				</tr>
			";
			$counter++;
		}
		/* <b>".$main->listings->getListingElementValueById( 25, $r['dtime'], true )."</b>,&nbsp;*/
		
                $t .= "
			<tr class='list_table_footer'>
					<td colspan=6>
						Всего заказов в списке: ".$counter."
					</td>
				</tr>
		</table>
		
		<script>
                
                        var defaultMessage = '', lastorderid = 0;
                
                        function sendMessageForm( orderId, email, title )
                        {
                            lastorderid = orderId;
                            $( '#success' ).hide();
                            $( '#s_email' ).val( email );
                            $( '#s_title' ).val( title );
                            if( $( '#s_message' ).attr( 'data-first' ) == 1 ) {
                                defaultMessage = $( '#s_message' ).val();
                            }
                            $( '#s_message' ).val( defaultMessage );
                            $( '#sendlayer' ).fadeIn( 200 );
                        }
                        
                        function sendOrderMessage()
                        {
                            processSimpleAsyncReqForModuleAdmin( '".$this->dbinfo['local']."', 666, '&orderid=' + lastorderid + '&email=' + $( '#s_email' ).val() + '&title=' + $( '#s_title' ).val() + '&message=' + $( '#s_message' ).val(), '$( \'#success\' ).show(); setTimeout( function(){ $( \'#sendlayer\' ).fadeOut( 200 ); }, 1500 );' );
                        }

			function updateGoodItemAfterChageCount( data, order, good )
			{
				var ar = data.toString().split( '%%%' );
				$( '#gooditemoforder_' + order + '_' + good ).html( ar[0] );
				$( '#orderprice_' + order ).html( ar[1] );
				
				if( ar[2] == '0' ) {
					$( '#tr_' + order + '_delivery' ).hide();
				} else {
					$( '#td1_' + order + '_delivery' ).html( ar[2] );
					$( '#td2_' + order + '_delivery' ).html( ar[2] );
					$( '#tr_' + order + '_delivery' ).show();
				}
			}
			
			function updateGoodItemAfterDelete( data, order, good )
			{
				var ar = data.toString().split( '%%%' );
				$( '#tr_' + order + '_' + good ).remove();
				$( '#orderprice_' + order ).html( ar[0] );
				
				if( ar[1] == '0' ) {
					$( '#tr_' + order + '_delivery' ).hide();
				} else {
					$( '#td1_' + order + '_delivery' ).html( ar[1] );
					$( '#td2_' + order + '_delivery' ).html( ar[1] );
					$( '#tr_' + order + '_delivery' ).show();
				}
			}
			
			function updateOrderItemAfterStateChange( data, order )
			{
				if( data == '0' )
					return;
				var ar = data.toString().split( '%%%' );
				$( '#td_order_' + order ).removeClass( 'order_' + ar[0] ).addClass( 'order_' + ar[1] );
				$( '#td_div_order_' + order ).removeClass( 'order_' + ar[0] ).addClass( 'order_' + ar[1] ).html( ar[2] );
				$( '#div_cancel_reason_' + order ).html( ar[3] );
				$( '#st_change_' + order ).html( ar[4] );
			}
                        
                        function updateOrderItemAfterLenseChange( data, order, basketId )
			{
				if( data == '0' || !data )
					return;
				var ar = data.toString().split( '^' );
				$( '#lense_name' + order + '_' + basketId ).html( ar[0] );
                                
                                $( '#lenseprice_' + order + '_' + basketId  ).html( ar[2] );
                                $( '#discount_price' + order  ).html( ar[6] );
                                $( '#totalsumma_' + order  ).html( ar[7] );
                                $( '#newlense_order' + order + '_' + basketId  ).html( ar[8] );
			}
                        
                        function updateOrderItemAfterOpravaChange( data, order, basketId )
			{
				if( data == '0' || !data )
					return;
				var ar = data.toString().split( '^' );
				$( '#oprava_name' + order + '_' + basketId ).html( ar[0] );
                                
                                $( '#opravaprice_' + order + '_' + basketId  ).html( ar[1] );
                                $( '#discount_price' + order  ).html( ar[6] );
                                $( '#totalsumma_' + order  ).html( ar[7] );
                                $( '#newoprava_order' + order + '_' + basketId  ).html( ar[8] );
			}
			
			function updateNewitemDivAfterSearch( data, div_id )
			{
				$( '#' + div_id ).html( data );
			}
			
			function updateNewitemDivAfterAddpress( data, div_id, order )
			{
				$( '#' + div_id ).html( '' );
				var ar = data.toString().split( '%%%' );
				$( '#orderprice_' + order ).html( ar[1] );
				var tr = $( '#newitem_div_' + order ).parent().parent();
				tr.before( ar[0] );
				
				if( ar[2] == '0' ) {
					$( '#tr_' + order + '_delivery' ).hide();
				} else {
					$( '#td1_' + order + '_delivery' ).html( ar[2] );
					$( '#td2_' + order + '_delivery' ).html( ar[2] );
					$( '#tr_' + order + '_delivery' ).show();
				}
			}
		</script>
		<style>
			.allnewitemsdivs {  }
			.fromelem_div
			{ 
				padding-top: 5px;
				padding-bottom: 5px;
				cursor: pointer;
			}
			.fromelem_div:hover
			{ 
				background-color: #ddd;
			}
		</style>
		";
		
		return $t;
	}
	
	function parseExternalRequest()
	{
		global $query, $main, $utils, $lang, $mysql;
		
		$type = $query->gp( "localtype" );
		
		switch( $type ) {
                    
                        case 666: // Отправить письмо
                            
                                $orderId = $query->gp( "orderid" );
                                $email = $query->gp( "email" );
                                $title = $query->gp( "title" );
                                $message = $query->gp( "message" );
                                
                                $order = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `id`=".$orderId );
                                $userData = $main->users->getUserById( $order['user'] );
                                $message = str_replace( "[username]", $userData['name'], $message );
                                $message = str_replace( "[orderid]", $orderId, $message );
                                $message = str_replace( "[ordersumma]", $order['summa'], $message );
                                $message = str_replace( "[paymentlink]", "<a href='https://".$_SERVER['HTTP_HOST']."/order/payment".$orderId."' target=_BLANK>https://".$_SERVER['HTTP_HOST']."/order/payment".$orderId."</a>", $message );
                                
                                $mail_agent = $main->modules->gmi( "mail_agent" );
                                $mail_agent->sendMessage( $email, $main->modules->gmi( "order" )->getParam( "email_from_admin" ), $main->templates->psl( $title ), $message );
                                
                                return 1;
			
			case 101: // Получить второй этап оформления заказа
			
				$t = "<div class='one passed pointer' onclick=\"urlmove( '/order' );\"><div class='h'></div><div class='inr'><span>1</span> Корзина</div></div>
				<div class='one current'><div class='h'></div><div class='inr'><span>2</span> Адрес доставки</div></div>
				<div class='one'><div class='h'></div><div class='inr'><span>3</span> Способ доставки</div></div>
				<div class='one last'><div class='h'></div><div class='inr'><span>4</span> Оплата</div></div>~";
				
                                $promo = $query->gp( "promo" );
                            
				if( $main->users->auth ) {
					$profile = $main->users->getUserProfile( $main->users->userArray['id'] );
					$p = $query->gp( "p" );
					$p = $p ? $p : $_SERVER['REQUEST_URI'];
                                        
                                        if( $promo ) {
                                            $promoEntry = $mysql->mq( "SELECT `promo` FROM `".$mysql->t_prefix."sign` WHERE `mail`='".$main->users->userArray['ulogin']."' AND `promo`='".$promo."'" );
                                            if( !$promoEntry ) {
                                                $promo = '';
                                            }
                                        }
                                        
					$t .= "
					<div class='all_forms' id='verifyform'>
						<div class='input input_low'>
							<div class='title'>Имя <span class='red'>*</span></div>
							<input type=text name='p_name' id='p_name' value=\"".( isset( $profile['name'] ) ? $profile['name']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Фамилия <span class='red'>*</span></div>
							<input type=text name='p_surname' id='p_surname' value=\"".( isset( $profile['suname'] ) ? $profile['suname']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Телефон <span class='red'>*</span></div>
							<input type=text name='p_phone' id='p_phone' value=\"".( isset( $profile['phone'] ) && $profile['phone'] ? $profile['phone']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" placeholder='+7 ___ ___ __ __' data-inputmask=\"'mask': '+7 999 999 99 99', 'clearMaskOnLostFocus': true, 'clearIncomplete': true, 'showMaskOnHover': true, 'showMaskOnFocus': true\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='header'>Адрес доставки</div>
						<div class='input input_nonemargintop'>
							<div class='title'>Город <span class='red'>*</span></div>
							<input type=text name='p_city' id='p_city' value=\"".( isset( $profile['city'] ) && $profile['city'] ? $profile['city']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Адрес доставки <span class='red'>*</span></div>
							<input type=text name='p_adress' id='p_adress' value=\"".( isset( $profile['adress'] ) && $profile['adress'] ? $profile['adress']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_textarea input_low'>
							<div class='title'>Дополнительная информация</div>
							<textarea type=text name='p_comments' id='p_comments'".( isset( $profile['comments'] ) && $profile['comments'] ? " class='withdata'" : '' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\">".( isset( $profile['comments'] ) && $profile['comments'] ? $profile['comments'] : "" )."</textarea>
						</div>
						<div class='clearError' style=' margin-left: 185px;'><span class='red'></span></div>
					</div>
					<div class='clear'></div>
					<div class='block_button block_button_right'>
						<div class='button' onclick=\"
							if( checkFormOnline() ) {
								processSimpleAsyncReqForModule( 'profile', '4', '&p_surname=' + $( '#p_surname' ).val() + '&p_name=' + $( '#p_name' ).val() + '&p_phone=' + $( '#p_phone' ).val() + '&p_index=' + $( '#p_index' ).val() + '&p_city=' + $( '#p_city' ).val() + '&p_adress=' + $( '#p_adress' ).val() + '&p_comments=' + $( '#p_comments' ).val() + '&promo=".$promo."', 'processStepThree();' );
							}
						\">
							Продолжить
						</div>
					</div>
					<div class='clear'></div>
					<script>
						function processStepThree()
						{
							processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '102', '&p=".urlencode( str_replace( "&amp;", "&", $p ) )."&promo=".$promo."', 'updateSituation( data );' );
						}
					
						function checkFormOnline()
						{
							if( !$( '#p_name' ).attr( 'done' ) ) {
								if( !$( '#p_name' ).val() ) {
									$( '#p_name' ).parent().find( '.error' ).width( $( '#p_name' ).parent().width() - $( '#p_name' ).parent().find( '.title' ).width() - $( '#p_name' ).width() - 50 ).html( 'Укажите имя' ).show();
									$( '#p_name' ).focus();
									return false;
								}
								$( '#p_name' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_surname' ).attr( 'done' ) ) {
								if( !$( '#p_surname' ).val() ) {
									$( '#p_surname' ).parent().find( '.error' ).width( $( '#p_surname' ).parent().width() - $( '#p_surname' ).parent().find( '.title' ).width() - $( '#p_surname' ).width() - 50 ).html( 'Укажите фамилию' ).show();
									$( '#p_surname' ).focus();
									return false;
								}
								$( '#p_surname' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_phone' ).attr( 'done' ) ) {
								if( !$( '#p_phone' ).val() ) {
									$( '#p_phone' ).parent().find( '.error' ).width( $( '#p_phone' ).parent().width() - $( '#p_phone' ).parent().find( '.title' ).width() - $( '#p_phone' ).width() - 50 ).html( 'Укажите телефон' ).show();
									$( '#p_phone' ).focus();
									return false;
								}
								$( '#p_phone' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_city' ).attr( 'done' ) ) {
								if( !$( '#p_city' ).val() ) {
									$( '#p_city' ).parent().find( '.error' ).width( $( '#p_city' ).parent().width() - $( '#p_city' ).parent().find( '.title' ).width() - $( '#p_city' ).width() - 50 ).html( 'Укажите город' ).show();
									$( '#p_city' ).focus();
									return false;
								}
								$( '#p_city' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_adress' ).attr( 'done' ) ) {
								if( !$( '#p_adress' ).val() ) {
									$( '#p_adress' ).parent().find( '.error' ).width( $( '#p_adress' ).parent().width() - $( '#p_adress' ).parent().find( '.title' ).width() - $( '#p_adress' ).width() - 50 ).html( 'Укажите адрес' ).show();
									$( '#p_adress' ).focus();
									return false;
								}
								$( '#p_adress' ).attr( 'done', 1 );
							}
							
							return true;
						}
					</script>
					";					
				} else {
					$form_id = md5( time().mt_rand( 1, 500 ) );
					$form_id_reg = md5( time().mt_rand( 1, 500 ) );
					$p = $query->gp( "p" );
					$p = $p ? $p : $_SERVER['REQUEST_URI'];
                                        
                                        $main->users->goingFromOrder = $query->gp( "ulf" ) ? true : false;
                                        if( $main->users->goingFromOrder )
                                            $main->users->auth_error = $lang->gp( 116 );
                                        
					$t .= "
					<div class='block_menu'>
						<span data-id='1'".( $main->users->goingFromOrder ? " class='link'" : "" ).">Зарегистрироваться</span><div class='divider'></div><span data-id='2'".( !$main->users->goingFromOrder ? " class='link'" : "" ).">Войти</span>
					</div>
					<div class='all_forms".( $main->users->goingFromOrder ? " invisible" : "" )."' id='regform'><form action=\"".str_replace( "&amp;", "&", $p ).( strpos( $p, '?' ) ? "&" : "?" )."promo=".$promo."&step=102\" method=POST id='".$form_id_reg."'><input type=hidden name='fine_regorder' id='fine_regorder' value='0' />
						<div class='input input_low'>
							<div class='title'>Пол</div>
							<div class='radios'><input type=hidden name='p_gender' id='p_gender' value='0' />
								<div class='option' data-param='p_gender' data-value='1'><div></div></div> Мужской&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<div class='option' data-param='p_gender' data-value='2'><div></div></div> Женский
							</div>
						</div>
						<div class='input input_low'>
							<div class='title'>Электронная почта <span class='red'>*</span></div>
							<input type=text name='p_login' id='p_login' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Пароль <span class='red'>*</span></div>
							<input type=password name='p_pass' id='p_pass' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Имя <span class='red'>*</span></div>
							<input type=text name='p_name' id='p_name' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Фамилия <span class='red'>*</span></div>
							<input type=text name='p_surname' id='p_surname' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Телефон <span class='red'>*</span></div>
							<input type=text name='p_phone' id='p_phone' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" placeholder='+7 ___ ___ __ __' data-inputmask=\"'mask': '+7 999 999 99 99', 'clearMaskOnLostFocus': true, 'clearIncomplete': true, 'showMaskOnHover': true, 'showMaskOnFocus': true\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Дата рождения</div>
							<input type=text name='p_bdate' id='p_bdate' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" placeholder='ДД/ММ/ГГГГ' data-inputmask=\"'mask': '99/99/9999', 'clearMaskOnLostFocus': true, 'clearIncomplete': false, 'showMaskOnHover': true, 'showMaskOnFocus': true\" />
						</div>
						<div class='header'>Адрес доставки</div>
						<div class='input input_nonemargintop'>
							<div class='title'>Город <span class='red'>*</span></div>
							<input type=text name='p_city' id='p_city' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Адрес доставки <span class='red'>*</span></div>
							<input type=text name='p_adress' id='p_adress' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_textarea input_low'>
							<div class='title'>Дополнительная информация</div>
							<textarea type=text name='p_comments' id='p_comments' onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\"></textarea>
						</div>
						<div class='input input_low' style='height: auto;'><input type=hidden name='p_ras' id='p_ras' val=0 />
							<div class='title'>&nbsp;</div>
							<div class='check'><div class='box' onclick=\"
								if( $( this ).hasClass( 'box_sel' ) ) {
									$( this ).removeClass( 'box_sel' );
									$( '#p_ras' ).val( 0 );
								} else {
									$( this ).addClass( 'box_sel' );
									$( '#p_ras' ).val( 1 );
								}
							\"><div class='s'></div></div>
								Подписаться на новости и акции сайта
							</div>
						</div>
						<div class='clearError' style=' margin-left: 185px;'><span class='red'></span></div>
					</form></div>
					<div class='all_forms".( !$main->users->goingFromOrder ? " invisible" : "" )."' id='loginform'>
						
					  <form action=\"".str_replace( "&amp;", "&", $p ).( strpos( $p, '?' ) ? "&" : "?" )."promo=".$promo."&step=101\" method=POST id='".$form_id."'><input type=hidden name='fine_order' id='fine_order' value='0' />
						<div class='input input_low'>
							<div class='title'>Email</div>
							<input type=text name='u_login_order' id='u_login_order' value=\"".( $main->users->displayedLogin ? trim( $main->users->displayedLogin ) : "" )."\" onkeypress=\"
								var code = processKeyPress( event );
								if( code == 13 ) {
									$( '#fine_order' ).val( '1' );
									$( '#".$form_id."' ).submit();
								}
							\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
						</div>
						<div class='input input_password'>
							<div class='title'>Пароль</div>
							<input type=password name='u_pass_order' id='u_pass_order' value=\"".( $main->users->displayedPassword ? trim( $main->users->displayedPassword ) : "" )."\" onkeypress=\"
								var code = processKeyPress( event );
								if( code == 13 ) {
									$( '#fine_order' ).val( '1' );
									$( '#".$form_id."' ).submit();
								}
							\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
							<div class='showing' onclick=\"
								if( $( this ).parent().find( 'input' ).attr( 'type' ) == 'password' ) {
									$( this ).addClass( 'showing_selected' );
									$( this ).parent().find( 'input' ).attr( 'type', 'text' );
								} else {
									$( this ).removeClass( 'showing_selected' );
									$( this ).parent().find( 'input' ).attr( 'type', 'password' );
								}								
							\"></div>
						</div>
					  </form>
						<div class='button' style='margin-left: 185px; width: auto; padding-left: 40px; padding-right: 40px;' onclick=\"
							$( '#fine_order' ).val( '1' );
							$( '#".$form_id."' ).submit();
						\">Войти</div>
						<p style='margin-bottom: 0px; margin-left: 185px;'><a href='#' onclick=\"processSimpleAsyncReqForModule( 'profile', '3', '&p=".$p."', 'afterProfiilShow( data );' ); return false;\">Забыл пароль</a></p>
						<div class='clearError' style='margin-left: 185px;'>".( $main->users->auth_error ? "<span class='red'>".$main->users->auth_error."</span>" : "" )."</div>
					
					</div>
					<div class='clear'></div>
					<div class='block_button block_button_right".( $main->users->goingFromOrder ? " invisible" : "" )."'>
						<div class='button' onclick=\"
							if( checkFormOnline() ) {
								$( '#fine_regorder' ).val( '2' ); 
								$( '#".$form_id_reg."' ).submit();
							}
						\">
							Продолжить
						</div>
					</div>
					<div class='clear'></div>
					
					<script>
						setTimeout(function()
						{
							$( '.radios .option' ).click(function(){
								$( '.radios .option' ).removeClass( 'option_selected' );
								$( this ).addClass( 'option_selected' );
								$( '#' + $( this ).attr( 'data-param' ) ).val( $( this ).attr( 'data-value' ) );
							});
							$( '.block_menu span' ).click(function(){
								if( $( this ).hasClass( 'link' ) ) {
									$( '.block_menu span' ).each(function(){
										if( !$( this ).hasClass( 'link' ) ) {
											$( this ).addClass( 'link' );
										}
									});
									$( this ).removeClass( 'link' );
									if( $( this ).attr( 'data-id' ) == '1' || $( this ).attr( 'data-id' ) == 1 ) {
										$( '#regform' ).show();
										$( '#loginform' ).hide();
										$( '.block_button' ).show();
									} else {
										$( '#regform' ).hide();
										$( '#loginform' ).show();
										$( '.block_button' ).hide();
									}
								}
							});
						},300);
						
						function checkFormOnline()
						{
							if( !$( '#p_login' ).attr( 'done' ) ) {
								if( !$( '#p_login' ).val() ) {
									$( '#p_login' ).parent().find( '.error' ).width( $( '#p_login' ).parent().width() - $( '#p_login' ).parent().find( '.title' ).width() - $( '#p_login' ).width() - 50 ).html( 'Укажите email' ).show();
									$( '#p_login' ).focus();
									return false;
								}
								if( $.ajax({ type: 'POST', url: '".$mysql->settings['local_folder']."checkdata', async: false, data: 'ext=1&type=1&v=' + $( '#p_login' ).val() }).responseText != 1 ) {
									$( '#p_login' ).parent().find( '.error' ).width( $( '#p_login' ).parent().width() - $( '#p_login' ).parent().find( '.title' ).width() - $( '#p_login' ).width() - 50 ).html( 'Неверный Email' ).show();
									$( '#p_login' ).focus();
									return false;
								}
								if( $.ajax({ type: 'POST', url: '".$mysql->settings['local_folder']."checkdata', async: false, data: 'ext=2&type=2&v=' + $( '#p_login' ).val() }).responseText != 1 ) {
									$( '#p_login' ).parent().find( '.error' ).width( $( '#p_login' ).parent().width() - $( '#p_login' ).parent().find( '.title' ).width() - $( '#p_login' ).width() - 50 ).html( 'Указанный Email уже используется, нажмите на кнопку войти' ).show();
									$( '#p_login' ).focus();
									return false;
								}
								$( '#p_login' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_pass' ).attr( 'done' ) ) {
								if( !$( '#p_pass' ).val() ) {
									$( '#p_pass' ).parent().find( '.error' ).width( $( '#p_pass' ).parent().width() - $( '#p_pass' ).parent().find( '.title' ).width() - $( '#p_pass' ).width() - 50 ).html( 'Укажите пароль' ).show();
									$( '#p_pass' ).focus();
									return false;
								}
								$( '#p_pass' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_name' ).attr( 'done' ) ) {
								if( !$( '#p_name' ).val() ) {
									$( '#p_name' ).parent().find( '.error' ).width( $( '#p_name' ).parent().width() - $( '#p_name' ).parent().find( '.title' ).width() - $( '#p_name' ).width() - 50 ).html( 'Укажите имя' ).show();
									$( '#p_name' ).focus();
									return false;
								}
								$( '#p_name' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_surname' ).attr( 'done' ) ) {
								if( !$( '#p_surname' ).val() ) {
									$( '#p_surname' ).parent().find( '.error' ).width( $( '#p_surname' ).parent().width() - $( '#p_surname' ).parent().find( '.title' ).width() - $( '#p_surname' ).width() - 50 ).html( 'Укажите фамилию' ).show();
									$( '#p_surname' ).focus();
									return false;
								}
								$( '#p_surname' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_phone' ).attr( 'done' ) ) {
								if( !$( '#p_phone' ).val() ) {
									$( '#p_phone' ).parent().find( '.error' ).width( $( '#p_phone' ).parent().width() - $( '#p_phone' ).parent().find( '.title' ).width() - $( '#p_phone' ).width() - 50 ).html( 'Укажите телефон' ).show();
									$( '#p_phone' ).focus();
									return false;
								}
								$( '#p_phone' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_city' ).attr( 'done' ) ) {
								if( !$( '#p_city' ).val() ) {
									$( '#p_city' ).parent().find( '.error' ).width( $( '#p_city' ).parent().width() - $( '#p_city' ).parent().find( '.title' ).width() - $( '#p_city' ).width() - 50 ).html( 'Укажите город' ).show();
									$( '#p_city' ).focus();
									return false;
								}
								$( '#p_city' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_adress' ).attr( 'done' ) ) {
								if( !$( '#p_adress' ).val() ) {
									$( '#p_adress' ).parent().find( '.error' ).width( $( '#p_adress' ).parent().width() - $( '#p_adress' ).parent().find( '.title' ).width() - $( '#p_adress' ).width() - 50 ).html( 'Укажите адрес' ).show();
									$( '#p_adress' ).focus();
									return false;
								}
								$( '#p_adress' ).attr( 'done', 1 );
							}
							
							return true;
						}
					</script>
					";
				}
				
				return $t;
				
			case 102: // Получить третий этап оформления заказа
			
				$p = $query->gp( "p" );
				$p = $p ? $p : $_SERVER['REQUEST_URI'];
                                
                                $selected_delivery = $query->gp( "selected_delivery" );
                                $selected_delivery = $selected_delivery ? $selected_delivery : 0;
                                $del_cur_price = 0;
                                
                                $promo = $query->gp( "promo" );
                                $curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
                                
                                if( $promo && $curUser ) {
                                    $promoEntry = $mysql->mq( "SELECT `promo` FROM `".$mysql->t_prefix."sign` WHERE `mail`='".$main->users->userArray['ulogin']."' AND `promo`='".$promo."'" );
                                    if( !$promoEntry ) {
                                        $promo = '';
                                    }
                                }
                                
                                $total_summa = $main->modules->gmi( "basket" )->countMainSumma();
                                if( $promo ) {
                                    $discount_main_summa = ceil( $total_summa / 100 * 5 );
                                    $total_summa -= $discount_main_summa;
                                }
				
				$del = '';
				$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."delivery` WHERE `view`=1" );
				while( $r = @mysql_fetch_assoc( $a ) ) {
					$del .= "
					<div class='block_option'>
						<div class='heckbox'>
							<div class='option".( $selected_delivery == $r['id'] ? " option_selected" : "" )."' data-id='".$r['id']."'><div></div></div>
						</div>
						<div class='opis'><div>".$r['name'].( $r['comment'] ? "<span>".$r['comment']."</span>" : "" )."</div></div>
						<div class='deliv'>".$r['long']."</div>
						<div class='price' data-price=".( $r['price'] ? $r['price'] : 0 )." data-total='".$utils->digitsToRazryadi( $total_summa + $r['price'] )."'>".( $r['price'] ? $r['price']." <img src='".$mysql->settings['local_folder']."images/mini_ruble.png' />" : "Бесплатно" )."</div>
					</div>
					";
                                        if( $selected_delivery == $r['id'] )
                                            $del_cur_price = $r['price'];
				}
                                
                                $total_summa += $del_cur_price;

				$t = "<div class='one passed passed_passed pointer' onclick=\"urlmove( '/order&promo=' + $( '#promo' ).val() );\"><div class='h'></div><div class='inr'><span>1</span> Корзина</div></div>
				<div class='one passed pointer' onclick=\"urlmove( '/order?step=101&promo=' + $( '#promo' ).val() );\"><div class='h'></div><div class='inr'><span>2</span> Адрес доставки</div></div>
				<div class='one current'><div class='h'></div><div class='inr'><span>3</span> Способ доставки</div></div>
				<div class='one last'><div class='h'></div><div class='inr'><span>4</span> Оплата</div></div>~
					".$del."				
					<div class='clear'></div>
					<div class='block_button".( $selected_delivery ? "" : " block_button_offline" )." block_button_right'>
						<div class='button' onclick=\"
							if( !selected_delivery )
								return;
							processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '103', '&p=".urlencode( str_replace( "&amp;", "&", $p ) )."&selected_delivery=' + selected_delivery + '&promo=' + $( '#promo' ).val(), 'updateSituation( data );' );
						\">
							Продолжить
						</div>
					</div>
					<div class='clear'></div>
					<script>
						var selected_delivery = ".$selected_delivery.";
						setTimeout(function()
						{
							$( '.contents .block_option .opis div' ).each(function(){
								$( this ).css( 'margin-top', ( $( this ).parent().height() - $( this ).height() ) / 2 ).css( 'opacity', 1 );								
							});
							$( '.contents .block_option .heckbox .option' ).click(function(){
								var no = false;
								if( $( this ).hasClass( 'option_selected' ) ) {
									$( this ).removeClass( 'option_selected' );
									no = true;
								} else {
									$( '.contents .block_option .heckbox .option' ).removeClass( 'option_selected' );
									$( this ).addClass( 'option_selected' );
									$( '.contents .block_button' ).removeClass( 'block_button_offline' );
									selected_delivery = $( this ).attr( 'data-id' );
                                                                        var dtp = $( this ).parent().parent().find( '.price' ).attr( 'data-total' );
                                                                        var dp = $( this ).parent().parent().find( '.price' ).attr( 'data-price' );
                                                                        $( '#result_delivery' ).html( dp );
                                                                        $( '#result_price' ).html( dtp );
								}
							});
                                                        $( '#haspromo' ).show();
                                                        $( '#promo' ).on( 'blur', function(){
                                                            processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '1022', '&promo=' + $( '#promo' ).val(), 'afterCheckPromo( data );' );
                                                        });
                                                        ".( $del_cur_price ? "$( '#result_delivery' ).html('".$del_cur_price."'); $( '#result_price' ).attr( 'data-value', ".$total_summa." ).html('".$utils->digitsToRazryadi( $total_summa )."');" : "" )."
						}, 200);
                                                
                                                function afterCheckPromo( data )
                                                {
                                                    if( !data || data == '' ) {
                                                        $( '#promo' ).css( 'border', '1px solid #444' );
                                                        return;
                                                    } else if( data == '-1' ) {
                                                        $( '#promo' ).css( 'border', '1px solid #ff0000' );
                                                        return;
                                                    }
                                                    $( '#promo' ).css( 'border', '1px solid #444' );
                                                    var ar = data.toString().split( '^' );
                                                    $( '#result_price' ).html( ar[1] );
                                                    $( '#discount' ).html( ar[0] );
                                                }
					</script>
				";	
				
				return $t;
                                
                        case 1022: // ПРоверка промо кода
                            
                                $promo = $query->gp( "promo" );
                                $curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
                                
                                if( $promo && $curUser ) {
                                    $promoEntry = $mysql->mq( "SELECT `promo` FROM `".$mysql->t_prefix."sign` WHERE `mail`='".$main->users->userArray['ulogin']."' AND `promo`='".$promo."'" );
                                    if( !$promoEntry ) {
                                        return "-1";
                                    } else {
                                        //$total_summa = $main->users->getSessionOption( 2 );
                                        $total_summa = $main->modules->gmi( "basket" )->countMainSumma();
                                        $discount_main_summa = ceil( $total_summa / 100 * 5 );
                                        $total_summa -= $discount_main_summa;
                                        $discount_main_summa += $main->modules->gmi( "basket" )->getDiscountSumma();
                                        
                                        return $utils->digitsToRazryadi( $discount_main_summa )." <img src='".$mysql->settings['local_folder']."images/mini_ruble.png' />^".$utils->digitsToRazryadi( $total_summa );
                                    }
                                }
                                
                                return "";                                
				
			case 103: // Получить четвертый этап оформления заказа
			
				$p = $query->gp( "p" );
				$p = $p ? $p : $_SERVER['REQUEST_URI'];
                                
                                $promo = $query->gp( "promo" );
                                $curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
                                $curSid = $main->users->sid;
                                
                                if( $promo && $curUser ) {
                                    $promoEntry = $mysql->mq( "SELECT `promo` FROM `".$mysql->t_prefix."sign` WHERE `mail`='".$main->users->userArray['ulogin']."' AND `promo`='".$promo."'" );
                                    if( !$promoEntry ) {
                                        $promo = '';
                                    }
                                }
				
				$selected_delivery = $query->gp( "selected_delivery" );
				$del_price = $main->modules->gmi( "delivery" )->getDeliveryPrice( $selected_delivery );
				//$total_summa = $main->users->getSessionOption( 2 );
                                $total_summa = $main->modules->gmi( "basket" )->countMainSumma();
                                
                                $discount_main_summa = 0;
                                if( $promo ) {
                                    $discount_main_summa = ceil( $total_summa / 100 * 5 );
                                    $total_summa -= $discount_main_summa;
                                }
                                $discount_main_summa += $main->modules->gmi( "basket" )->getDiscountSumma();
                                
                                /*
                                $discount_main_summa = $main_summa / 100 * 5;
                                $main_summa -= $discount_main_summa;
                                 */
                                
                                $a = $mysql->mqm( "SELECT `lense` FROM `".$mysql->t_prefix."basket` WHERE `order`=0".( $curUser ? " AND `user`=".$curUser : " AND `session`='".$curSid."'" ) );
                                $islense = false;
                                while( $r = @mysql_fetch_assoc( $a ) ) {
                                    if( $r['lense'] ) {
                                        $islense = true;
                                        break;
                                    }
                                }
				
				$del = '';
				$elems = $main->listings->getListingElementsArray( 21, 0, false, '', true );
				foreach( $elems as $k => $v ) {
                                    if( $islense && $k == 145 )
                                        continue;
                                    else if( !$islense && $k == 700 )
                                        continue;
                                    $del .= "
					<div class='block_option'>
						<div class='heckbox'>
							<div class='option' data-id='".$k."'".( $k == 143 ? 
                                                            " data-discount-mainprice='".$utils->digitsToRazryadi( ( $total_summa - ceil( $total_summa / 100 * 10 ) ) + $del_price )."' data-discount-exact='".$utils->digitsToRazryadi( ceil( $total_summa / 100 * 10 ) + $discount_main_summa )."' data-discount-text=\"<span style='float: left; color: #ff0000; font-size: 12px; font-weight: 700; margin-top: 1.5px;'>-10%</span>\"" : 
                                                            " data-discount-mainprice='".$utils->digitsToRazryadi( $total_summa + $del_price )."' data-discount-exact='".$utils->digitsToRazryadi( $discount_main_summa )."' data-discount-text=''" )."><div></div></div>
						</div>
						<div class='opis opis_full'><div>".$lang->gp( $v['value'], true ).( $v['additional_info'] ? "<span>".$v['additional_info']."</span>" : "" )."</div></div>
					</div>
                                    ";
				}
			
				$t = "<div class='one passed passed_passed pointer' onclick=\"urlmove( '/order&promo=' + $( '#promo' ).val() );\"><div class='h'></div><div class='inr'><span>1</span> Корзина</div></div>
				<div class='one passed passed_passed pointer' onclick=\"urlmove( '/order?step=101&promo=' + $( '#promo' ).val() );\"><div class='h'></div><div class='inr'><span>2</span> Адрес доставки</div></div>
				<div class='one passed pointer' onclick=\"urlmove( '/order?step=102&promo=' + $( '#promo' ).val() + '&selected_delivery=".$selected_delivery."' );\"><div class='h'></div><div class='inr'><span>3</span> Способ доставки</div></div>
				<div class='one current last'><div class='h'></div><div class='inr'><span>4</span> Оплата</div></div>~
					".$del."				
					<div class='clear'></div>
					<div class='block_button block_button_offline block_button_right'>
						<div class='button' onclick=\"
							if( !selected_payment )
								return;
							urlmove( '".str_replace( "&amp;", "&", $p ).( strpos( $p, '?' ) ? "&" : "?" )."process_make_order=1&selected_delivery=".$selected_delivery."&selected_payment=' + selected_payment + '&promo=' + $( '#promo' ).val() );
						\">
							Завершить
						</div>
					</div>
					<div class='clear'></div>
					<script>
						var selected_payment = 0;
						setTimeout(function()
						{
							$( '.contents .block_option .opis div' ).each(function(){
								$( this ).css( 'margin-top', ( $( this ).parent().height() - $( this ).height() ) / 2 ).css( 'opacity', 1 );								
							});
							$( '.contents .block_option .heckbox .option' ).click(function(){
								var no = false;
								if( $( this ).hasClass( 'option_selected' ) ) {
									/*$( this ).removeClass( 'option_selected' );*/
									no = true;
								} else {
									$( '.contents .block_option .heckbox .option' ).removeClass( 'option_selected' );
									$( this ).addClass( 'option_selected' );
									$( '.contents .block_button' ).removeClass( 'block_button_offline' );
									selected_payment = $( this ).attr( 'data-id' );
                                                                        var ddm = $( this ).attr( 'data-discount-mainprice' );
                                                                        if( ddm && ddm != undefined ) {
                                                                            var dde = $( this ).attr( 'data-discount-exact' );
                                                                            var ddt = $( this ).attr( 'data-discount-text' );
                                                                            $( '#result_price' ).html( ddm );
                                                                            $( '#discount' ).html( dde + \" <img src='".$mysql->settings['local_folder']."images/mini_ruble.png' />\" );
                                                                            if( ddt != '' )
                                                                                $( '#discount' ).append( ddt );
                                                                        }
								}
							});
							$( '#result_delivery' ).html( '".$utils->digitsToRazryadi( $del_price )."' );
							$( '#result_price' ).html( '".$utils->digitsToRazryadi( $total_summa + $del_price  )."' );
                                                        $( '#discount' ).html( '".$utils->digitsToRazryadi( $discount_main_summa )."  <img src=\'".$mysql->settings['local_folder']."images/mini_ruble.png\' />' );
                                                        ".( $promo ? "$( '#haspromo' ).hide();" : "" )." 
						}, 200);
					</script>
				";	
				
				return $t;
			
			case 1: // Сменить кол-во товара в заказе
			
				$order = $query->gp( "order" );
				$id = $query->gp( "id" );
				$newcount = $query->gp( "newcount" );
				
				$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `id`=".$order );
				$basketItem = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `good`=".$id." AND `order`=".$order );
				
				if( !$order || !$id || !is_numeric( $id ) || !is_numeric( $newcount ) || $newcount > 999 || $newcount < 0 || !$r || !$basketItem )
					return;
					
				$summa = $basketItem['price'] * $basketItem['count'];
				$newsumma = $basketItem['price'] * $newcount;
				
				$allItemsPrice = 0;
				$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `order`=".$order );
				while( $rr = @mysql_fetch_assoc( $a ) )
					$allItemsPrice += ( $rr['count'] * $rr['price'] );
					
				$allItemsPrice -= $summa;
				$allItemsPrice += $newsumma;
				
				$deliveryPrice = $r['dmethod'] != 350 ? $main->modules->gmi( "delivery" )->getDeliveryPrice( $allItemsPrice ) : 0;
				$r['summa'] = $allItemsPrice + $deliveryPrice;
				
				$mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `summa`=".$r['summa']." WHERE `id`=".$order );
				$mysql->mu( "UPDATE `".$mysql->t_prefix."basket` SET `count`=".$newcount." WHERE `good`=".$id." AND `order`=".$order );
				
				return $newsumma."%%%".$r['summa']."%%%".( !$deliveryPrice ? "0" : $utils->digitsToRazryadi( $deliveryPrice ) );
				
			case 2: // удалить товар из заказа
			
				$order = $query->gp( "order" );
				$id = $query->gp( "id" );
				
				$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `id`=".$order );
				$basketItem = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `good`=".$id." AND `order`=".$order );
				
				if( !$order || !$id || !is_numeric( $id ) || !$r || !$basketItem )
					return;
					
				$summa = $basketItem['price'] * $basketItem['count'];
				
				$allItemsPrice = 0;
				$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `order`=".$order );
				while( $rr = @mysql_fetch_assoc( $a ) )
					$allItemsPrice += ( $rr['count'] * $rr['price'] );
					
				$allItemsPrice -= $summa;
				if( $allItemsPrice < 0 )
					$allItemsPrice = 0;
					
				$deliveryPrice = $r['dmethod'] != 350 ? $main->modules->gmi( "delivery" )->getDeliveryPrice( $allItemsPrice ) : 0;
				$r['summa'] = $allItemsPrice + $deliveryPrice;
					
				$mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `summa`=".$r['summa']." WHERE `id`=".$order );
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."basket` WHERE `good`=".$id." AND `order`=".$order );
				
				return $r['summa']."%%%".( !$deliveryPrice ? "0" : $utils->digitsToRazryadi( $deliveryPrice ) );
				
			case 3: // Сменить статус заказа
				
				$order = $query->gp( "order" );
				$ch_status = $query->gp( "ch_status" );
				$cancel_reason = isset( $_POST['cancel_reason'] ) && $_POST['cancel_reason'] ? str_replace( "&nbsp;", " ", $_POST['cancel_reason'] ) : '';
				$date_reason = isset( $_POST['date_reason'] ) && $_POST['date_reason'] ? str_replace( "&nbsp;", " ", $_POST['date_reason'] ) : '';
				if( $date_reason ) {
					$da = explode( "/", trim( $date_reason ) );
					if( count( $da ) == 3 )
						$date_reason = mktime( "00", "00", "00", $da[1], $da[0], $da[2] );
					 else 	
						$date_reason = time();
				} else 
					$date_reason = time();
				
				$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `id`=".$order );
				if( !$r )
					return "0";
					
				if( $r['status'] == 1000 ) {
					
					$mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `status`=".$ch_status.", `last_action_date`=".time().", `date`=".time()." WHERE `id`=".$order );
					
					$template = $main->templates->psl( $lang->gp( 269, true ), true );
					$template = str_replace( "[order_number]", "<b>".$order."</b>", $template );
					$template = str_replace( "[summa]", "<b>".$r['summa']." руб.</b>", $template );
					$template = str_replace( "[date]", $utils->getFullDate( time() ), $template );
					$template = str_replace( "[pmethod]", $main->listings->getListingElementValueById( 27, $r['pmethod'], true ), $template );
					
					$mail_agent->sendMessage( $this->getParam( "where_send_new_order" ), $this->getParam( "from_to_send_order" ), $main->templates->psl( $lang->gp( 248, true ) ), $template );
					
					return "";
				}
				
				if( $ch_status == ORDER_READY_TO_PAY && $r['pmethod'] == 347 ) {
					
					$sid = $r['sid'];
					$user = $r['user'] ? $r['user'] : 0;
					$email = $r['contact_email'] ? $r['contact_email'] : ( $user ? $main->users->getUserloginById( $user ) : "" );
					if( $email ) {
						$exist = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."sid_access` WHERE `user`=".$user." AND `sid`='".$sid."'" );
						if( $exist )
							$access_sid = $exist['access_sid'];
						else {
							$access_sid = md5( $sid.mt_rand( 100000, 9000000 ).time() );
							$mysql->mu( "INSERT INTO `".$mysql->t_prefix."sid_access` VALUES(0,'".$access_sid."','".$sid."',".$user.");" );
							$exist = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."sid_access` WHERE `access_sid`='".$access_sid."'" );
						}
						
						if( $exist ) {
							
							$localAdress = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'];
							$link = $localAdress."sid_access!".$access_sid;
							$template = "Уважаемый покупатель! [date] вы сделали заказ в интернет магазине [site_name] на общую сумму [summa] и выбрали Онлайн-платеж в качестве метода платежа.<br><br>Оператор подтвердил наличие товара и сейчас Вы можете перейти по ссылке [link], чтобы завершить оформление заказа. Просим Вас, в случае возникновения проблем в процессе оплаты товаров, сообщить нам об этих проблемах и мы быстро их решим!<br><br>Спасибо, что вы с нами!";
							$template = $main->templates->psl( $template, false );
							$template = str_replace( "[summa]", "<b>".$r['summa']." руб.</b>", str_replace( "[date]", $utils->getFullDate( $r['date'] ), $template ) );
							$template = str_replace( "[link]", "<a href=\"".$link."\" target=_BLANK>".$link."</a>", $template );
					
							$main->modules->gmi( "mail_agent" )->sendMessage( $email, $this->getParam( "from_to_send_order" ), $main->templates->psl( $lang->gp( 352, true ) ), $template );
							
						}
					}
					
				}
                                
                                if( ( $ch_status == ORDER_READY_PAYD_50_CONFIRMED && $r['status'] == ORDER_READY_PAYD_50_NEED_CONFIRM ) || ( $ch_status == ORDER_READY_PAYD_100_CONFIRMED && $r['status'] == ORDER_READY_PAYD_100_NEED_CONFIRM ) ) {
                                    
                                    if( $main->modules->gmii( 25 )->confirmPayment( $r ) ) {
                                        $mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `status`=".$ch_status.", `last_action_date`=".$date_reason.( ( $ch_status == 140 || $ch_status == 141 ) && $cancel_reason ? ", `cancel_reason`='".$cancel_reason."'" : ", `cancel_reason`=''" )." WHERE `id`=".$order );
                                    } else
                                        return 0;
                                } else {
                                    $mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `status`=".$ch_status.", `last_action_date`=".$date_reason.( ( $ch_status == 140 || $ch_status == 141 ) && $cancel_reason ? ", `cancel_reason`='".$cancel_reason."'" : ", `cancel_reason`=''" )." WHERE `id`=".$order );
                                }
				
				return $r['status']."%%%".$ch_status."%%%".$main->listings->getListingElementValueById( 20, $ch_status, true )."%%%".( ( $ch_status == 140 || $ch_status == 141 ) && $cancel_reason ? "Причина: ".$cancel_reason : "" )."%%%".$main->listings->getListingForSelecting( 20, 0, 0, "<option value=0>смена статуса</option>", "", true, "`id`<>".$ch_status." AND `id`<>137 AND `id`<>702", true );
				
			case 4: // Поиск товара по ключевым словам при добавлении нового товара в заказ
				
				$search_string = isset( $_POST['search_string'] ) && $_POST['search_string'] ? str_replace( "&nbsp;", " ", $_POST['search_string'] ) : '';
				$tt = explode( " ", $search_string );
				$orderid = $query->gp( "orderid" );
				
				$where = "`view`=1 AND (";
				$ww = '';
				foreach( $tt as $v ) {
					if( !$v )
						continue;
					$ww .= ( $ww ? " AND " : "" )."`name` LIKE '%".$v."%'"; 
				}
				$where .= $ww.")";
				
				$t = "";
				$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE ".$where." ORDER BY `order` ASC, `name` ASC LIMIT 10" );
				while( $r = @mysql_fetch_assoc( $a ) ) {
					$t .= "
					<div class='fromelem_div' onclick=\"processSimpleAsyncReqForModuleAdmin( '".$this->dbinfo['local']."', 5, '&itemid=".$r['id']."&orderid=".$orderid."', 'updateNewitemDivAfterSearch( data, \'newitemname_div_".$orderid."\' );' );\">
						".$r['name']." (<span style='color: #ff0000;'>".$r['price']."</span>)
					</div>
					";
				}
				
				return $t;
				
			case 5: // Форма для доабвления выбранного товара
				
				$itemid = $query->gp( "itemid" );
				$orderid = $query->gp( "orderid" );
				
				$r = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `id`=".$itemid );
				$props = $main->properties->getPropertiesOfGood( $r['id'] );
				
				$colorCount = 0;
				foreach( $props as $p )
					if( $p['prop_id'] == 48 && $p['value'] )
						$colorCount++;
				
				$currencyTypes = $main->listings->getListingElementsArray( 21, 0, false, '', true );
				$ColorsList = $main->listings->getListingElementsArraySpec( 2, "`order` DESC, `id` ASC", "", -1, true );
				$sides = $main->listings->getListingForSelecting( 29, 0, 0, "", "", true, '', true );
				$side_choice = $this->getElementByData( $props, "prop_id", 52 );
				
				$colorBlock = "";
				if( $colorCount > 1 ) {
					$colorBlock = "<div>Выберите цвет товара: <select id='colorblock_".$itemid."'>";
					foreach( $props as $p ) {
						if( $p['prop_id'] == 48 && $p['value'] ) {
							$price = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_prices` WHERE `tovar_id`=".$itemid." AND `property`=48 AND `prop_value`=".$p['value']." ORDER BY `date` DESC" );
							if( !$price )
								$price = $utils->digitsToRazryadi( $r['price'] )." ".$lang->gp( $currencyTypes[$r['currency_type']]['value'], true );
							else 
								$price = $utils->digitsToRazryadi( $price['price'] )." ".$lang->gp( $currencyTypes[$price['currency_type']]['value'], true );
				
							$titleName = is_numeric( $p['value'] ) && $p['value'] ? $lang->gp( $ColorsList[$p['value']]['value'], true ) : $p['value'];
							$colorBlock .= "<option value='".$p['value']."'>".$titleName." (".$price.")</option>";
						}
					}
					$colorBlock .= "</select></div>";
				}
				
				$t = "
				<h2>ID ".$r['id']." - <b>".$r['name']."</b> (".$utils->digitsToRazryadi( $r['price'] )." ".$lang->gp( $currencyTypes[$r['currency_type']]['value'], true ).")</h2>
				".$colorBlock."
				".( $side_choice ? "<div>Выберите расположение мойки: <select id='side_choice_".$itemid."'>".$sides."</select></div>" : "" )."
				<input type=button value='Добавить' onclick=\"
					var gg = ge( 'colorblock_".$itemid."' );
					var colorId = gg != undefined ? gg.value : 0;
					var gg = ge( 'side_choice_".$itemid."' );
					var sideId = gg != undefined ? gg.value : 0;
					processSimpleAsyncReqForModuleAdmin( '".$this->dbinfo['local']."', 6, '&itemid=".$itemid."&orderid=".$orderid."' + ( colorId > 0 ? '&color=' + colorId : '' ) + ( sideId > 0 ? '&side=' + sideId : '' ), 'updateNewitemDivAfterAddpress( data, \'newitemname_div_".$orderid."\', ".$orderid." );' );
				\" />
				";
					
				return $t;
				
			case 6: // Добавление нового товара в заказ
				
				$itemid = $query->gp( "itemid" );
				$orderid = $query->gp( "orderid" );
				$color = $query->gp( "color" );
				$side = $query->gp( "side" );
				
				$r = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `id`=".$itemid );
				$order = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `id`=".$orderid );
				
				$add = "";
				$price = intval( $r['price'] );
				if( $color ) {
					$price_data = $mysql->mq( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_prices` WHERE `tovar_id`=".$itemid." AND `property`=48 AND `prop_value`=".$color." ORDER BY `date` DESC" );
					if( $price_data )
						$price = intval( $price_data['price'] );
					$add = "48,".$color;
				}
				
				if( $side )
					$add = ( $add ? ";" : "" ).$side;
					
				$mysql->mu( "INSERT INTO `".$mysql->t_prefix."basket` VALUES(
						
						".$order['user'].",
						'".$order['sid']."',
						'".$itemid."',
						'1',
						'".$price."',
						".time().",
						".$orderid.",
						'".$add."'
						
					);" );
				
				$currencyTypes = $main->listings->getListingElementsArray( 21, 0, false, '', true );
				$ColorsList = $main->listings->getListingElementsArraySpec( 2, "`order` DESC, `id` ASC", "", -1, true );
				
				$orderGoods = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `order`=".$orderid );
				$gItemsCount = @mysql_num_rows( $orderGoods );
				
				$good = $r;
				$props = $main->properties->getPropertiesOfGood( $itemid );
			
				$preview = $this->getElementByData( $props, "prop_id", 51 );
				$preview = $preview ? $preview['value'] : '';
				
				$colorCount = 0;
				foreach( $props as $p )
					if( $p['prop_id'] == 48 && $p['value'] )
						$colorCount++;
				
				$tt = explode( ";", $add );
				$side_choice = isset( $tt[1] ) ? $tt[1] : ( $tt[0] ? $tt[0] : 0 );
				$tt = explode( ",", $tt[0] );
				$selectedColor = "";
				if( isset( $tt[1] ) && $tt[0] == 48 )
					$selectedColor = is_numeric( $tt[1] ) && $tt[1] ? $lang->gp( $ColorsList[$tt[1]]['value'], true ) : $tt[1];
			
				$colorSelectBlock = "";
				if( $colorCount > 0 ) {
					if( !$selectedColor && $colorCount > 1 ) {
						$selectedColor = " <span class='red'>".$lang->gp( 263, true )."</span>";
					} else if( !$selectedColor ) {
						$color = $this->getElementByData( $props, "prop_id", 48 );
						$selectedColor = is_numeric( $color['value'] ) && $color['value'] ? $lang->gp( $ColorsList[$color['value']]['value'], true ) : $color['value'];
					}
					$colorSelectBlock = "<div class='colorSelectDiv'>".$lang->gp( 260, true ).": ".$selectedColor."</div>";
				}
				
				$goodsStr .= "
				<tr id='tr_".$orderid."_".$itemid."'>
					<td class='iElem' align=left valign=middle>".( $gItemsCount )."</td>
					<td class='iElem' align=left valign=middle style='max-width: 230px; min-width: 230px; text-align: left;'>
						<a href=\"/catalog/show".$itemid."\" target=_BLANK>
							".( $preview ? "<img src=\"/files/upload/goods/thumbs/".$preview."\" style='max-width: 30px; float: left; margin-right: 3px;' />" : "" ).$good['name'].( $side_choice ? ( $side_choice == 263 ? " правая" : ( $side_choice == 264 ? " левая" : "" ) ) : '' )."
						</a>".$colorSelectBlock."
					</td>
					<td class='iElem iElem_no_padding' align=center valign=middle>
						<input type=text style='width: 30px; text-align: center;' value='1' onblur=\"
							if( this.value == undefined || this.value == '' || Math.floor( this.value ) == 0 ) {
								this.value = '1';
							} else {
								processSimpleAsyncReqForModuleAdmin( '".$this->dbinfo['local']."', 1, '&order=".$orderid."&id=".$itemid."&newcount=' + this.value, 'updateGoodItemAfterChageCount( data, ".$orderid.", ".$itemid." );' );
							}
						\" />
					</td>
					<td class='iElem iElem_no_padding' align=center valign=middle>
						".$utils->digitsToRazryadi( $price )."
					</td>
					<td class='iElem iElem_no_padding' align=center valign=middle id='gooditemoforder_".$orderid."_".$itemid."'>
						".$utils->digitsToRazryadi( $price )."
					</td>
					<td class='iElem iElem_no_padding' align=center valign=middle>
						<img src='/images/drop.gif' style='cursor: pointer;' onclick=\"
							if( !confirm( 'Вы уверены?' ) )
								return;
							processSimpleAsyncReqForModuleAdmin( '".$this->dbinfo['local']."', 2, '&order=".$orderid."&id=".$itemid."', 'updateGoodItemAfterDelete( data, ".$orderid.", ".$itemid." );' );
						\" />
					</td>
				</tr>
				";
				
				$allItemsPrice = 0;
				$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `order`=".$orderid );
				while( $rr = @mysql_fetch_assoc( $a ) )
					$allItemsPrice += ( $rr['count'] * $rr['price'] );
					
				$deliveryPrice = $order['dmethod'] != 350 ? $main->modules->gmi( "delivery" )->getDeliveryPrice( $allItemsPrice ) : 0;
				$allItemsPrice += $deliveryPrice;
					
				$mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `summa`=".$allItemsPrice." WHERE `id`=".$orderid );
				
				return $goodsStr."%%%".$allItemsPrice."%%%".( !$deliveryPrice ? "0" : $utils->digitsToRazryadi( $deliveryPrice ) );
		
                        case 7: // Изменение линзы в заказе
                            
                            $newlense = $query->gp( "newlense" );
                            $order = $query->gp( "order" );
                            $basketitem = $query->gp( "basketitem" );
                            
                            $data = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `id`=".$order );
                            $basketData = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `id`=".$basketitem );
                            if( !$data || !$newlense || !$basketData || $basketData['lense'] == $newlense )
                                    return "";
                            
                            $lenses = $main->modules->gmi( "lenses" );
                            $catalog = $main->modules->gmi( "catalog" );
                            
                            $oldLenseData = $lenses->getLenseWithId( $basketData['lense'] );
                            $newLenseData = $lenses->getLenseWithId( $newlense );
                            
                            if( !$oldLenseData || !$newLenseData )
                                   return "";

                            $oprava = $catalog->getItem( $basketData['ochkiid'] );

                            $opravaRoot = $oprava['root'] ? $catalog->getItem( $oprava['root'] ) : 0;
                            if( $opravaRoot )
				$oprava['name'] = $opravaRoot['name'];
		
                            $oprava['properties'] = $main->properties->getPropertiesOfGood( $oprava['id'] );
                            $color = $this->getElementByData( $oprava['properties'], "prop_id", 1 );
			
                            $deliveryPrice = $main->modules->gmi( "delivery" )->getDeliveryPrice( $data['dmethod'] );
			
                            $array = json_decode( $basketData['add'] );
			
                            if( $array->add ) {
				$prices = $main->listings->getListingElementsArray( 13, $array->p, false, '', true );
				if( isset( $prices[$array->add] ) ) {
					$newLenseData['add_price'] = $prices[$array->add]['additional_info'];
				}
                            }
                            
                            $opravaPrice = $utils->digitsToRazryadi( $oprava['price'] );
                            $newLensePrice = $utils->digitsToRazryadi( $newLenseData['price'] );
                            $newLenseDop = $utils->digitsToRazryadi( $newLenseData['add_price'] );
                            $discountPrice = $utils->digitsToRazryadi( $main->users->getSessionOptionWithSID( $data['sid'], 33 ) );
                            
                            $summa = 0;
                            $a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `order`=".$order." AND `id`<>".$basketitem );
                            while( $r = @mysql_fetch_assoc( $a ) ) {
                                $summa += $r['price'];
                            }
                            
                            $summa += ( $oprava['price'] + $newLenseData['price'] + $newLenseData['add_price'] );
                            if( $discountPrice ) {
                                $one = ceil( $summa / 100 );
                                $newSumma = $summa - ( $one * 5 );
                                $discountPrice = $summa - $newsumma;
                                $summa = $newSumma;
                                $main->users->updateSessionOptionData( $data['sid'], 33, $discountPrice );
                            }
                            $mysql->mu( "UPDATE `".$mysql->t_prefix."basket` SET `price`=".( $oprava['price'] + $newLenseData['price'] + $newLenseData['add_price'] ).", `lense_price`=".( $newLenseData['price'] + $newLenseData['add_price'] ).", `lense`=".$newlense." WHERE `id`=".$basketitem );
                            $mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `summa`=".( $summa + $deliveryPrice )." WHERE `id`=".$order );
                            
                            $lensesArray = $lenses->getListForOrders( $newLenseData['id'] );
                            $lensesOptions = "";
                            foreach( $lensesArray as $l_id => $l_data ) {
                                $lensesOptions .= "<option value=".$l_id.">".$l_data['name']."</option>";
                            }
                            $workPrice = 0;
                            return $newLenseData['name']."^".$opravaPrice."^".$newLensePrice."^".$newLenseDop."^".$workPrice."^".$deliveryPrice."^".$discountPrice."^".$utils->digitsToRazryadi( $summa + $deliveryPrice )."^<option value=0 selected>Вы можете выбрать другую линзу...</option>".$lensesOptions;
                            
                        case 8: // Изменение оправы в заказе
                            
                            $newoprava = $query->gp( "newoprava" );
                            $order = $query->gp( "order" );
                            $basketitem = $query->gp( "basketitem" );
                            
                            $data = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `id`=".$order );
                            $basketData = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `id`=".$basketitem );
                            if( !$data || !$newoprava || !$basketData || $basketData['ochkiid'] == $newoprava )
                                    return "";
                            
                            $lenses = $main->modules->gmi( "lenses" );
                            $catalog = $main->modules->gmi( "catalog" );
                            
                            $oldOpravaData = $catalog->getItem( $basketData['ochkiid'] );
                            $newOpravaData = $catalog->getItem( $newoprava );
                            
                            if( !$oldOpravaData || !$newOpravaData )
                                   return "";

                            $lenseData = $lenses->getLenseWithId( $basketData['lense'] );
                            $deliveryPrice = $main->modules->gmi( "delivery" )->getDeliveryPrice( $data['dmethod'] );
			
                            $array = json_decode( $basketData['add'] );
			
                            if( $array->add ) {
				$prices = $main->listings->getListingElementsArray( 13, $array->p, false, '', true );
				if( isset( $prices[$array->add] ) ) {
					$lenseData['add_price'] = $prices[$array->add]['additional_info'];
				}
                            }
                            
                            $opravaPrice = $utils->digitsToRazryadi( $newOpravaData['price'] );
                            $lensePrice = $utils->digitsToRazryadi( $lenseData['price'] );
                            $lenseDop = $utils->digitsToRazryadi( $lenseData['add_price'] );
                            $discountPrice = $utils->digitsToRazryadi( $main->users->getSessionOptionWithSID( $data['sid'], 33 ) );
                            
                            $summa = 0;
                            $a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."basket` WHERE `order`=".$order." AND `id`<>".$basketitem );
                            while( $r = @mysql_fetch_assoc( $a ) ) {
                                $summa += $r['price'];
                            }
                            
                            $summa += ( $newOpravaData['price'] + $lenseData['price'] + $lenseData['add_price'] );
                            if( $discountPrice ) {
                                $one = ceil( $summa / 100 );
                                $newSumma = $summa - ( $one * 5 );
                                $discountPrice = $summa - $newsumma;
                                $summa = $newSumma;
                                $main->users->updateSessionOptionData( $data['sid'], 33, $discountPrice );
                            }
                            $mysql->mu( "UPDATE `".$mysql->t_prefix."basket` SET `ochkiid`=".$newoprava.", `price`=".( $newOpravaData['price'] + $lenseData['price'] + $lenseData['add_price'] )." WHERE `id`=".$basketitem );
                            $mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `summa`=".( $summa + $deliveryPrice )." WHERE `id`=".$order );
                            
                            $opravaArray = $catalog->getListForOrders( $newOpravaData['id'] );
                            $opravaOptions = "";
                            foreach( $opravaArray as $o_id => $o_data ) {
                                $opravaOptions .= "<option value=".$o_id.">".$o_data['name']."</option>";
                            }
                            
                            $newOpravaData['properties'] = $main->properties->getPropertiesOfGood( $newOpravaData['id'] );
                            $preview = $this->getElementByData( $newOpravaData['properties'], "prop_id", 14 );
                            $preview = $preview ? $preview['value'] : '';
                            
                            $localAdress = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'];
                            $workPrice = 0;
                            return "<a href=\"".$localAdress."catalog/show".$newOpravaData['id']."\" target=_BLANK>".( $preview ? "<img src=\"".$localAdress."files/upload/goods/thumbs/".$preview."\" style='max-height: 60px; float: left; margin-right: 13px;' />" : "" ).$newOpravaData['name']."</a>^".$opravaPrice."^".$lensePrice."^".$lenseDop."^".$workPrice."^".$deliveryPrice."^".$discountPrice."^".$utils->digitsToRazryadi( $summa + $deliveryPrice )."^<option value=0 selected>Вы можете выбрать другую оправу...</option>".$opravaOptions;
            }
	}
	
	function getExternalEditOrder( $link )
	{
		global $query, $mysql, $main;
		
		$orderid = $query->gp( "orderid" );
		if( !$orderid )
			return "Unknown object id";
			
		$data = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `id`=".$orderid );
		if( !$data )
			return "There's no any object with ID ".$orderid;
			
		$show = $query->gp( "show" );
                
                $userArray = $main->users->getUserById( $data['user'] );
		$profile = $main->users->getUserProfile( $data['user'] );
                
                
						
                                                    
		$inner = "
                                <p>
					Город: <label class='red'>*</label><br>
					<input type=text name=\"city\" id=\"city\" value=\"".$profile['city']."\" class='text_input' />
				</p>
				<p>
					Адрес доставки: <label class='red'>*</label><br>
					<input type=text name=\"adress\" id=\"adress\" value=\"".$profile['adress']."\" class='text_input' />
				</p>
				<p>
					Контактный телефон: <label class='red'>*</label><br>
					<input type=text name=\"phone\" id=\"phone\" value=\"".$profile['phone']."\" class='text_input' />
				</p>
				<p>
					Комментарий: <br>
					<textarea name=\"comments\" class='textarea_input' rows=10>".( $profile['comments'] ? $profile['comments'] : "" )."</textarea>
				</p>
				<p>
					Выбранный метод доставки заказа: <br>
					<select name='dmethod'>
						".$main->modules->gmi( "delivery" )->getELemesforselect( $data['dmethod'] )."
					</select>
				</p>
				<p>
					Выбранный способ оплаты заказа: <br>
					<select name='pmethod'>
						".$main->listings->getListingForSelecting( 21, $data['pmethod'], 0, "", "", false, '', true )."
					</select>
				</p>
			";
			
		return "
				<h1 align=left><b>Редактирование заказа № ".$orderid."</b></h1>
				
				<form action=\"".LOCAL_FOLDER."admin/".$link.( $show ? "/show".$show : "" )."/edit\" method=POST onsubmit=\"
					$( '#process' ).attr( 'value', 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
										
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=submit value=\"Сохранить\" class='button_input' title='Сохранить изменения' />&nbsp;&nbsp;&nbsp;&nbsp;
						<input type=submit value=\"Отмена\" class='button_input' onclick=\"document.location = '".LOCAL_FOLDER."admin/".$link.( $show ? "/show".$show : "" )."';\" />
					</div>
					
				<input type=hidden name=\"orderid\" value=\"".$orderid."\" />
					
				</form>
		";
	}
	
	
	
	function getAdminStatScreen( $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;

		$month = $query->gp( "month" );
		$catalog = $main->modules->gmi( "catalog" );
		
		$tt = explode( "/", date( "d/m/Y" ) );
		$currentDay = intval( $tt[0] );
		$currentMonth = intval( $tt[1] );
		$currentYear = intval( $tt[2] );
		$monthes = "";
		for( $i = 1; $i <= 12; $i++ ) {
			$needDay = 1;
			$needMonth = $currentMonth - $i > 0 ? $currentMonth - $i : 13 - $i;
			$needYear = $currentMonth - $i <= 0 ? $currentYear - 1 : $currentYear;
			$d = mktime( "00", "00", "00", $needMonth, $needDay, $needYear );
			$monthes .= "<option value=".$d.( $month == $d ? " selected" : "" ).">Статистика ".$utils->getFullDate( $d, false, true )."</option>";
		}
		
		$t = "
			<h1>Статистика по заказам (поступлениям средств) за периоды (показываются только полностью доставленные и оплаченные заказы)</h1>
			
			<form method=POST action='".LOCAL_FOLDER."admin/".$path."' onsubmit=\"if( $( '#show' ).val() != '0' ) this.action = '".LOCAL_FOLDER."admin/".$path."/show' + $( '#show' ).val(); return true;\" id='orders_form'>
				<input type=hidden name='ch_status' id='ch_status' value=0 />
				<input type=hidden name='order_id' id='order_id' value=0 />
				<select name='month' id='month' onchange=\"$( '#orders_form' ).submit();\">
					<option value=0".( !$month ? " selected" : "" ).">Статистика текущего месяца</option>
					".$monthes."
				</select>
			</form>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 800px;'>
				<tr class='list_table_header'>
					<td width=40 nowrap>
						№
					</td>
					<td width=50%>
						Контактное лицо, адрес и телефон
					</td>
					<td width=25%>
						Дата заказа<br>
						<b>Дата оплаты</b>
					</td>
					<td width=25%>
						Сумма заказа
					</td>
				</tr>
		";
		
		$wdate = "";
		if( !$month ) {
			$d = mktime( "00", "00", "00", $currentMonth, 1, $currentYear );
			$wdate = " AND `last_action_date`>=".$d;
			//$wdate = " AND `date`>=".$d;
		} else {
			$tt = explode( "/", date( "d/m/Y", $month ) );
			$selectedMonth = intval( $tt[1] );
			$selectedYear = intval( $tt[2] );
			$needDay = 1;
			$needMonth = $selectedMonth == 12 ? 1 : $selectedMonth + 1;
			$needYear = $needMonth == 1 ? $selectedYear + 1 : $selectedYear;
			$d = mktime( "00", "00", "00", $needMonth, $needDay, $needYear );
			$wdate = " AND `last_action_date`>=".$month." AND `last_action_date`<".$d;
			//$wdate = " AND `date`>=".$month." AND `date`<=".$d;
		}
		
		$counter = 0;
		$summa = 0;
		//$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `status`=248".$wdate." ORDER BY `last_action_date` ASC" );
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `status`=248".$wdate." ORDER BY `date` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			
			$t .= "
				<tr class='list_table_element'>
					<td valign=top>
						".$r['id']."
					</td>
					<td style='text-align: left;' valign=middle>
						<div style='margin-bottom: 5px;'>Заказчик: <b>".( $r['contact_name'] ? $r['contact_name'] : "Не указан" )."</b></div>
						<div style='margin-bottom: 5px;'>Адрес: <b>".( $r['adress'] ? $r['adress'] : "Не указан" )."</b></div>
						<div style='margin-bottom: 5px;'>Телефон: <b>".( $r['phone'] ? $r['phone'] : "Не указан" )."</b></div>
					</td>
					<td valign=middle>
						<label>".$utils->getFullDate( $r['date'], true )."</label><br>
						<label><b>".$utils->getFullDate( $r['last_action_date'], true )."</b></label>
					</td>
					<td valign=middle>
						".$r['summa']." руб.
					</td>
				</tr>
			";
			$counter++;
			$summa += $r['summa'];
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=3>
						Всего заказов в списке: ".$counter."
					</td>
					<td style='text-align: center;'>
						Сумма: <b>".$summa."</b> руб.
					</td>
				</tr>
		</table>";
		
		return $t;
	}
	
	function getAdminTimeStatScreen( $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;

		$month = $query->gp( "month" );
		$catalog = $main->modules->gmi( "catalog" );
		
		$tt = explode( "/", date( "d/m/Y" ) );
		$currentDay = intval( $tt[0] );
		$currentMonth = intval( $tt[1] );
		$currentYear = intval( $tt[2] );
		$monthes = "";
		for( $i = 1; $i <= 12; $i++ ) {
			$needDay = 1;
			$needMonth = $currentMonth > 1 ? $currentMonth - $i : 12;
			$needYear = $needMonth == 12 ? $currentYear - 1 : $currentYear;
			$d = mktime( "00", "00", "00", $needMonth, $needDay, $needYear );
			$monthes .= "<option value=".$d.( $month == $d ? " selected" : "" ).">Статистика ".$utils->getFullDate( $d, false, true )."</option>";
		}
		
		$t = "
			<h1>Статистика по времени активности клиентов</h1>
			
			<form method=POST action='".LOCAL_FOLDER."admin/".$path."' id='orders_form'>
				<select name='month' id='month' onchange=\"$( '#orders_form' ).submit();\">
					<option value=10000".( $month == 10000 ? " selected" : "" ).">Показывать все заказы</option>
					<option value=0".( !$month ? " selected" : "" ).">Заказы текущего месяца</option>
					".$monthes."
				</select>
			</form>
			
			<table cellspacing=0 cellpadding=0 border=0 style='width: 720px;'>
				<tr>
		";
		
		$wdate = "";
		if( !$month ) {
			$d = mktime( "00", "00", "00", $currentMonth, 1, $currentYear );
			$wdate = "`date`>=".$d;
		} else if( $month == 10000 ) {
			
		} else {
			$tt = explode( "/", date( "d/m/Y", $month ) );
			$selectedMonth = intval( $tt[1] );
			$selectedYear = intval( $tt[2] );
			$needDay = 1;
			$needMonth = $selectedMonth == 12 ? 1 : $selectedMonth + 1;
			$needYear = $needMonth == 1 ? $selectedYear + 1 : $selectedYear;
			$d = mktime( "00", "00", "00", $needMonth, $needDay, $needYear );
			$wdate = "`date`>=".$month." AND `date`<=".$d;
		}
		
		$wdate = $wdate ? $wdate." AND `status`<>1000" : "`status`<>1000";
		
		$ar = array();
		for( $a = 0; $a < 24; $a++ )
			$ar[$a] = 0;
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."order` WHERE ".$wdate." ORDER BY `last_action_date` ASC" );
		$max = 0;
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$index = intval( date( "H", $r['date'] ) );
			$ar[$index]++;
			if( $ar[$index] > $max )
				$max = $ar[$index];
		}
		
		$height = 350;
		$one = $height / $max;
		
		for( $a = 0; $a < 24; $a++ ) {
			$hex = dechex( floor( 255 / $max * $ar[$a] ) );
			$color = "4f".( strlen( $hex ) < 2 ? "0".$hex : $hex )."ff";
			$font_color = floor( 255 / $max * $ar[$a] ) < 127 ? "#ffffff" : "#000000";
			$t .= "
			<td align=center valign=bottom style='width: 30px; height: ".$height."px;' width=30>
				<div style='min-height: 20px; height: ".( floor( $ar[$a] * $one ) > 20 ? floor( $ar[$a] * $one ) : 20 )."px; background-color: #".$color.";'><div style='padding-top: 5px; font-weight: bold; color: ".$font_color.";'>".$ar[$a]."</div></div>
			</td>
			";
		}
		
		$t .= "
			</tr>
			<tr>
		";
		
		for( $a = 0; $a < 24; $a++ ) {
			$t .= "<td align=center valign=top style='border-top: 1px solid #000; width: 30px; padding: 5px; border-right: 1px solid #000;".( !$a ? " border-left: 1px solid #000;" : "" )."' width=30>".( $a < 10 ? "0" : "" ).$a."-".( ( $a + 1 ) < 10 ? "0" : "" ).( $a + 1 )."</td>";
		}
		
		$t .= "				
			</tr>
		</table>
		";
		
		return $t;
	}
}

?>