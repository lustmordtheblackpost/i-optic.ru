<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

class Templates
{
	var $localPath = "";
	var $isIndex = false;
	var $four = false;
	
	var $title = "";
	var $gl_dbase_string = "`shop`.";
	
	function init()
	{
		global $mysql;
		
		$this->localPath = $mysql->settings['local_folder'];
	}
	
	function printHEAD()
	{
		global $lang, $mysql, $utils, $main, $query;
		
		$selectedId = $main->modules->getSelectedModuleId();
		$this->title = $lang->gp( 9 );
		
		if( !$selectedId )
			$query->setProperty( "index", 1 );
		
		$main->modules->gmi( "metas" )->prepareMetas( $selectedId );
		// <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,700,800&amp;subset=cyrillic' rel='stylesheet'>
		return "%metas%
		<meta http-equiv=\"content-language\" content=\"".$lang->currentLanguageCL."\" />
		<link rel=\"icon\" href=\"/favicon.png\" type=\"image/png\">
		<link rel=\"shortcut icon\" href=\"/favicon.png\" type=\"image/png\">
		<noindex>
		
		<script src=\"https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'].$utils->javascript_files_path."jquery-1.12.3.min.js\" type=\"text/javascript\"></script>
		<script src=\"https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'].$utils->javascript_files_path."jqui/jquery-ui.js\"></script>
		<link rel=\"stylesheet\" href=\"https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'].$utils->javascript_files_path."jqui/jquery-ui.css\">
		<script type=\"text/javascript\" src=\"https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."jsf/jquery.ou.1.1.2.packed.js\"></script>
		
		<link rel=\"stylesheet\" type=\"text/css\" href=\"".$this->localPath."css/ochki.css\"/>
		<script src=\"".$this->localPath.$utils->javascript_files_path."ochki.js\" type=\"text/javascript\"></script>
		<script src=\"".$this->localPath.$utils->javascript_files_path."html2canvas.js\" type=\"text/javascript\"></script>
		<script src=\"".$this->localPath.$utils->javascript_files_path."Blob.js\" type=\"text/javascript\"></script>
		<script src=\"".$this->localPath.$utils->javascript_files_path."canvas-to-blob.js\" type=\"text/javascript\"></script>
		</noindex>
		
		<link href=\"".$this->localPath."files/self.css\" rel=\"stylesheet\" type=\"text/css\"/>
                <link rel=\"stylesheet\" type=\"text/css\" href=\"".$this->localPath."css/fontawesome/css/font-awesome.min.css\"/>
		
		<!--<link rel=\"apple-touch-icon\" sizes=\"57x57\" href=\"".$this->localPath."images/icon/apple-icon-57x57.png\">
		<link rel=\"apple-touch-icon\" sizes=\"60x60\" href=\"".$this->localPath."images/icon/apple-icon-60x60.png\">
		<link rel=\"apple-touch-icon\" sizes=\"72x72\" href=\"".$this->localPath."images/icon/apple-icon-72x72.png\">
		<link rel=\"apple-touch-icon\" sizes=\"76x76\" href=\"".$this->localPath."images/icon/apple-icon-76x76.png\">
		<link rel=\"apple-touch-icon\" sizes=\"114x114\" href=\"".$this->localPath."images/icon/apple-icon-114x114.png\">
		<link rel=\"apple-touch-icon\" sizes=\"120x120\" href=\"".$this->localPath."images/icon/apple-icon-120x120.png\">
		<link rel=\"apple-touch-icon\" sizes=\"144x144\" href=\"".$this->localPath."images/icon/apple-icon-144x144.png\">
		<link rel=\"apple-touch-icon\" sizes=\"152x152\" href=\"".$this->localPath."images/icon/apple-icon-152x152.png\">
		<link rel=\"apple-touch-icon\" sizes=\"180x180\" href=\"".$this->localPath."images/icon/apple-icon-180x180.png\">
		<link rel=\"icon\" type=\"image/png\" sizes=\"192x192\"  href=\"".$this->localPath."images/icon/android-icon-192x192.png\">
		<link rel=\"icon\" type=\"image/png\" sizes=\"32x32\" href=\"".$this->localPath."images/icon/favicon-32x32.png\">
		<link rel=\"icon\" type=\"image/png\" sizes=\"96x96\" href=\"".$this->localPath."images/icon/favicon-96x96.png\">
		<link rel=\"icon\" type=\"image/png\" sizes=\"16x16\" href=\"".$this->localPath."images/icon/favicon-16x16.png\"> -->
		<link rel=\"manifest\" href=\"".$this->localPath."images/icon/manifest.json\">
		<meta name=\"msapplication-TileColor\" content=\"#ffffff\">
		<meta name=\"msapplication-TileImage\" content=\"".$this->localPath."images/icon/ms-icon-144x144.png\">
		<meta name=\"theme-color\" content=\"#ffffff\">
                
		<title>%title%</title>
		
		<noindex>
		<script>
			$(window).load(function()
			{
                                $( '.fixed_payment' ).height( $( window ).height() );
				var leng = 0;
				$( '.top_line_white .iblock' ).each( function(){
					if( $( this ).hasClass( 'phone_callback_block' ) )
						return;
					leng += $( this ).width();
				});
				$( '.phone_callback_block' ).width( $( '.all_lines' ).width() - Math.floor( leng ) - 1 ).animate( { opacity: 1 }, 300 );
				
				$( 'img.primerka' ).hover(function(){
					if( $( this ).hasClass( 'primerka_selected' ) )
						return;
					$( this ).attr( 'src', '/images/good_home_h.png' );
				}, function(){
					if( $( this ).hasClass( 'primerka_selected' ) )
						return;
					$( this ).attr( 'src', '/images/good_home.png' );
				}).click(function(){
					if( $( this ).hasClass( 'primerka_selected' ) ) {
						removeFittingElem( $( this ).attr( 'data-id' ) );
						$( this ).removeClass( 'primerka_selected' );
						$( this ).attr( 'src', '/images/good_home.png' );
						return;
					}
					addFittingElem( $( this ).attr( 'data-id' ) );
					$( this ).addClass( 'primerka_selected' );
					$( this ).attr( 'src', '/images/good_home_s.png' );
				});
				
				$( 'div.primerka' ).hover(function(){
					if( $( this ).hasClass( 'primerka_selected' ) )
						return;
					$( this ).find( 'img' ).attr( 'src', '/images/good_home_h.png' );
				}, function(){
					if( $( this ).hasClass( 'primerka_selected' ) )
						return;
					$( this ).find( 'img' ).attr( 'src', '/images/good_home.png' );
				}).click(function(){
					if( $( this ).hasClass( 'primerka_selected' ) ) {
						removeFittingElem( $( this ).attr( 'data-id' ) );
						$( this ).removeClass( 'primerka_selected' );
						$( this ).find( 'img' ).attr( 'src', '/images/good_home.png' );
						return;
					}
					addFittingElem( $( this ).attr( 'data-id' ) );
					$( this ).addClass( 'primerka_selected' );
					$( this ).find( 'img' ).attr( 'src', '/images/good_home_s.png' );
				});
				
				$( '.colors .one .colorname' ).each(function(){
					$( this ).css( 'margin-left', ( ( $( this ).width() ) / 2 * -1 ) - 3 );
				});
                                
                                $( '#brandlist' ).hover(function(){
                                    hidebrands = false;
                                    $( '#brandlist' ).removeClass( 'selected' );
                                    $( '.brands' ).fadeIn( 200 );
                                }, function(){
                                    hidebrands = true;
                                    setTimeout(function(){
                                        if( hidebrands )
                                            $( '.brands' ).fadeOut( 200 );
                                    }, 50 );                                    
                                });
                                
                                $( '.brands' ).hover(function(){
                                    $( '#brandlist' ).addClass( 'selected' );
                                    hidebrands = false;
                                }, function(){
                                    hidebrands = true;
                                    setTimeout(function(){
                                        if( hidebrands ) {
                                            $( '#brandlist' ).removeClass( 'selected' );
                                            $( '.brands' ).fadeOut( 200 );
                                        }
                                    }, 50 );                                    
                                });
                                
                                if( isMobile ) {
                                    $( '.search' ).detach().prependTo( $( '.mobile_menu' ) ).css( 'opacity', 1 );
                                }
                                
                                $( '#mobile_nav' ).click(function(){
                                    if( $( '.mobile_menu' ).is( ':visible' ) ) {
                                        hideMobileMenu();
                                    } else {
                                        showMobileMenu();
                                    }
                                });
			});
                        
                        var hidebrands = false;
			
			$(window).resize(function()
			{
				var leng = 0;
				$( '.top_line_white .iblock' ).each( function(){
					if( $( this ).hasClass( 'phone_callback_block' ) )
						return;
					leng += $( this ).width();
				});
				$( '.phone_callback_block' ).width( $( '.all_lines' ).width()  - Math.floor( leng ) - 1 );
			});
			
			$(document).mousemove(function(e) {
                            window.x = e.pageX;
                            window.y = e.pageY;
			});
			
			function replaceGoodsData( ar, prefix, id, output )
			{
				if( output == 1 ) {
					$( '#' + prefix + 'Pic_' + id ).css( 'background', 'url(' + ar[0] + ') no-repeat' );
					$( '#' + prefix + 'Pic_' + id ).css( 'background-size', ar[1] );
					$( '#' + prefix + 'Pic_' + id ).css( 'background-position', ar[2] );
				} else {
					$( '#' + prefix + 'Pic_' + id ).find( 'img' ).attr( 'src', ar[0] );
				}
				$( '#' + prefix + 'Name_' + id ).html( ar[3] );
				$( '#' + prefix + 'Price_' + id ).html( ar[4] );
				$( '#' + prefix + 'Link_' + id ).attr( 'href', ar[5] );
			}
			
			function addFittingElem( id )
			{
				processSimpleAsyncReqForModule( 'fitting', '1', '&good=' + id, 'afterFittingAdd( data );' );
			}
			
			function removeFittingElem( id, removeIcon )
			{
				processSimpleAsyncReqForModule( 'fitting', '3', '&good=' + id, 'afterFittingAdd( data );' );
				if( removeIcon != undefined && removeIcon ) {
					$( '.good_item .primerka' ).each(function(){
						if( $( this ).attr( 'data-id' ) == id ) {
							$( this ).removeClass( 'primerka_selected' );
							if( $( this ).prop('tagName').toLowerCase() == 'img' ) {
								$( this ).attr( 'src', '/images/good_home.png' );
							} else {
								$( this ).find( 'img' ).attr( 'src', '/images/good_home.png' );
							}
						}
					});
				}
			}
			
			function afterFittingAdd( data )
			{
				if( data != '' ) {
					$( '.top_home .table' ).html( data );
					processSimpleAsyncReqForModule( 'fitting', '2', '', 'afterFittingAct( data );' );
				}
			}
			
			function afterFittingAct( data )
			{
				if( data != '' ) {
					if( data == '0' || data == 0 ) {
						$( '.top_home .counter' ).fadeOut( 300, function() { $( '.top_home .counter' ).html( data ); } );
					} else {
						$( '.top_home .counter' ).html( data );
						$( '.top_home .counter' ).fadeIn( 300 );
					}
				}
			}
			
			jQuery.loadScript = function (url, callback) {
                            jQuery.ajax({
        			url: url,
        			dataType: 'script',
        			success: callback,
        			async: true
                            });
			}
                        
                        var catalog_add_menu_line_over = false;
                        var catalog_add_menu_line_height = 0;
                        $(window).scroll(function()
                        {
                            if( isMobile ) {
                                processMobileScroll();
                                return;
                            }
                            var tag = $( 'html, body' );
                            if( !catalog_add_menu_line_height )
                                catalog_add_menu_line_height = $( '.catalog_add_menu_line' ).height();
                            var hcheck = $( 'header' ).height();
                            if( !catalog_add_menu_line_over )
                                hcheck -= catalog_add_menu_line_height;
                            if( tag.scrollTop() >= hcheck ) {
                                $( 'header' ).css( 'padding-bottom', catalog_add_menu_line_height + 'px' );
                                $( '.catalog_add_menu_line' ).css( 'position', 'fixed' ).css( 'z-index', 10000 ).css( 'top', '0px' ).css( 'left', '0px' ).css( 'right', '0px' );
                                catalog_add_menu_line_over = true;
                            } else {
                                $( 'header' ).css( 'padding-bottom', '0px' );
                                $( '.catalog_add_menu_line' ).css( 'position', 'relative' ).css( 'z-index', 100 ).css( 'top', 'auto' ).css( 'left', 'auto' ).css( 'right', 'auto' );
                                catalog_add_menu_line_over = false;
                            }
                        });
                        
                        function processMobileScroll()
                        {
                            var tag = $( 'html, body' );
                        }
		</script>
		</noindex>
		".'<script type="text/javascript" src="//cdn.callbackhunter.com/cbh.js?hunter_code=5754e9ce6f5c698440579dfe25f17449" charset="UTF-8"></script>';
	}
	
	function printHTMLHeader()
	{
		global $lang, $mysql, $utils, $main, $query;
		
		$selectedId = $main->modules->getSelectedModuleId();
		
		$catalog = $query->gp( "catalog" );
		
                $showallcategories = $query->gp( "showallcategories" );
                
		$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."listings_elements` WHERE `listing`=1 ORDER BY `order` ASC, `id` ASC" );
		$tt = "";
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$tt .= "<a href=\"".$this->localPath."catalog".$r['id']."/".strtolower( $utils->translitIt( $lang->gp( $r['value'], true ) ) ).".html\"".( !$showallcategories && $catalog == $r['id'] ? " class='selected'" : "" )."><span>".$lang->gp( $r['value'], true )."</span></a> ";
		}
                
                $brands = "";
                $brandsList = $main->listings->getListingElementsArray( 7, 0, false, '', true );
                foreach( $brandsList as $id => $v ) {
                    $brands .= "<div class='one'><a href='/catalog/?vendor=".$id."&showallcategories=1'".( $query->gp( "vendor" ) == $id ? " class='selected'" : "" ).">".$lang->gp( $v['value'], true )."</a></div><div class='line'></div>";
                }
                
                $tt .= "<a href=\"#\" onclick='return false;' id='brandlist'".( $showallcategories && $query->gp( "vendor" ) ? " class='selected'" : "" )."><span>Бренды</span><div class='brands'><div class='cols'>".$brands."</div><div class='closebottomlines'></div></div></a>";
		
		$t = $main->modules->gmi( "callback" )->getFloatBlock().$main->modules->gmi( "catalog" )->getFloatBlock().$main->modules->gmi( "profile" )->getPluginBlock()."
		<div class='modal'></div>
                <noindex><div class='mobile_menu'>
			".$main->modules->getMobileMenuLinks()."
		</div></noindex>".$main->modules->gmi( "fitting_online" )->getVeryFirstHTMLBlock()."
                
		<header>
			<div class='top_line'>
				<div class='all_lines'><div id='mobile_nav' class='mobile_nav pointer'><img src='/images/mm.png' /></div><div class='toplinemenu'>".$main->modules->getMainmenuItems()."</div></div>
			</div>
			<div class='top_line_white'>
				<div class='all_lines'>
					<div class='iblock'>
						<img src='/images/logo.png' class='logo' onclick=\"urlmove( '/' );\" />
					</div><div id='mobileaction'></div>
					<div class='iblock iblock_exact'>
						".$main->modules->gmi( "search" )->getHeaderBlock()."
					</div>
					<div class='iblock'>
						".$main->modules->gmi( "fitting_online" )->getHeaderBlock()."
					</div>
					<div class='iblock phone_callback_block'>
						<h3 class='phone'>".$mysql->settings['local_phone']."</h3>
						<div class='rest'>
							<a id='callbackcall' href='#' onclick=\"showCallbackfloat( 400 ); return false;\">".$lang->gp( 20 )."</a>
							<a href='whatsapp://send?text=Hello&phone=79037473642' style='margin: 0px;'><img src='/images/whatsup.png' /></a>
							<a href='skype://+79037473642' style='margin: 0px;'><img src='/images/skype.png' /></a>
							<a href='viber://chat?number=79037473642' style='margin: 0px;'><img src='/images/viber.png' /></a>
						</div>
					</div>
				</div>
			</div>
			<div class='catalog_add_menu_line'>
				<div class='all_lines'>
					<nav class='menu'>
						".$tt."
					</nav>
				</div>
			</div>
		</header>
		<noindex>
			<script>
				function showMobileMenu()
				{
                                        $( '.mobile_menu' ).show().animate( { left: '0px' }, 200 );
                                        $( 'body' ).animate( { left: '240px' }, 200 );
                                        $( 'header .top_line' ).animate( { left: '240px', right: '-240px' }, 200 );
				}
			
				function hideMobileMenu()
				{
					$( '.mobile_menu' ).animate( { left: '-240px' }, 200, function() { $( '.mobile_menu' ).hide(); } );
                                        $( 'body' ).animate( { left: '0px' }, 200 );
                                        $( 'header .top_line' ).animate( { left: '0px', right: '0px' }, 200 );
				}
			</script>
		</noindex>
		";
		
		return $t;
	}	
	
	function printHTMLContext()
	{
		global $main, $mysql, $lang, $query;
		
		if( $main->users->showActivated ) {
			$main->modules->gmi( "order" )->updateOrderWithUser( $main->users->showActivated );
		}
		
		$success_payment = "";
		if( $query->gp( "success_payment" ) || strpos( $_SERVER['REQUEST_URI'], "success_payment" ) !== false ) {
			$success_payment = "
			<h1 class='pageTitle fullyOwned'>Успешная оплата!</h1>
			<p>Процесс оплаты произведен успешно! Можете зайти в раздел Корзина, чтобы увидеть свой заказ и следить за его исполнением!<br><br>Спасибо!</p>
			";
			$main->templates->setTitle( "Успешная оплата!", true );
		} else if( $query->gp( "error_payment" ) || strpos( $_SERVER['REQUEST_URI'], "error_payment" ) !== false ) {
			$success_payment = "
			<h1 class='pageTitle fullyOwned'>Ошибка при оплате!</h1>
			<p>К сожалению процесс оплаты не произведен</p>
			";
			$main->templates->setTitle( "Ошибка при оплате!", true );
		}
		
		$tt = explode( "/", date( "d/m" ) );
		
		$out = '';
		if( $this->four ) {
			$out = "<div class='catalog'>
			<div class='all_lines center'>
			<h1 class='pageTitle fullyOwned'>".$lang->gp( 60, true )."</h1>
			<h2 class='red'>404</h2>
			<p>".$lang->gp( 59, true )."</p></div></div>
			";
		}
		
		return "
		<section>
			".( $out ? $out : ( $success_payment ? $success_payment : $main->modules->printContent() ) )."
		</section>
		";
	}
	
	function getSopli()
	{
		global $main, $mysql, $lang, $query;
		
		$selectedId = $main->modules->getSelectedModuleId();
		
		if( $selectedId == 1 ) {
			$show = $query->gp( "show" );
			return $main->modules->gmi( "catalog" )->getSopli( null );
		}
			
		return ( $selectedId != 3 ? "<a href='".$mysql->settings['local_folder']."'>".$main->modules->gmi( "index" )->getName()."</a> / " : "" )."<span>".$main->modules->getModuleInstanceByID( $selectedId )->getName()."</span>";
	}
	
	function getNavMenu( $class = "", $onlick = "" )
	{
		return "<svg width=\"100%\" height=\"100%\" viewBox=\"0 0 64 52\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xml:space=\"preserve\" style=\"fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:1.41421;\"".( $class ? " class='".$class."'" : "" ).( $onlick ? " onclick=\"".$onlick."\"" : "" )."><path d=\"M64,43.28l-64,0l0,8l64,0l0,-8ZM64,21.616l-64,0l0,8l64,0l0,-8ZM64,0.72l-64,0l0,8l64,0l0,-8Z\" style=\"fill:#fff;\"/></svg>";
	}
	
	function getNavCatalogMenu( $class = "", $onlick = "" )
	{
		return "<svg width=\"100%\" height=\"100%\" viewBox=\"0 0 64 52\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xml:space=\"preserve\" style=\"fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:1.41421;\"".( $class ? " class='".$class."'" : "" ).( $onlick ? " onclick=\"".$onlick."\"" : "" )."><rect id=\"XMLID_1_\" width=\"31\" height=\"25\"/>
<rect id=\"XMLID_5_\" x=\"33\" width=\"31\" height=\"25\"/>
<rect id=\"XMLID_7_\" y=\"27\" width=\"31\" height=\"25\"/>
<rect id=\"XMLID_6_\" x=\"33\" y=\"27\" width=\"31\" height=\"25\"/></svg>";
	}
	
	function getNavX( $class = "", $onlick = "" )
	{
		return "<svg width=\"100%\" height=\"100%\" viewBox=\"0 0 52 52\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xml:space=\"preserve\" style=\"fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:1.41421;\"".( $class ? " class='".$class."'" : "" ).( $onlick ? " onclick=\"".$onlick."\"" : "" )."><path d=\"M31.657,26.176l19.799,19.799l-5.657,5.657l-19.799,-19.799l-19.799,19.799l-5.657,-5.657l19.799,-19.799l-19.799,-19.799l5.657,-5.657l19.799,19.799l19.799,-19.799l5.657,5.657l-19.799,19.799Z\" style=\"fill:#fff;\"/></svg>";
	}
	
	function printHTMLFooter()
	{
		global $main, $mysql, $lang, $query;
		
                $selectedId = $main->modules->getSelectedModuleId();
                
		$t = "
		<div class='greenline'>
			<div class='all_lines'>
				<div class='social'>
					".$lang->gp( 21, true )."
					<div style='margin-top: 13px;'>
						".$main->modules->gmi( "social" )->getFooterBlock()."
					</div>
				</div>
				".$main->modules->gmi( "actions" )->getSmallSignBlock()."
			</div>
		</div>
		<footer>
			<div class='lightgreenline'>
				<div class='all_lines'>
					<div class='block block_first'>".$main->modules->gmi( 'issues' )->getTItleForFooter()."</div>
					<div class='block block_second'>
						<h3 class='title'>".$lang->gp( 22, true )."</h3>
						".$main->modules->getServicemenuItems()."
						<div>
							<img src='/images/cards.png' />
						</div>
					</div>
					<div class='block'>
						<h3 class='title'>".$main->modules->gmi( 'contacts' )->getName()."</h3>
						<div>
							".$main->templates->psl( $lang->gp( 23, true ), true )."
						</div>
                                            <div style='margin-top: 10px;'>Мы в соц. сетях</div>
                                            <div style='padding-left: 10px;'>
                                                <a href='https://www.facebook.com/internetoptica/' target=_BLANK><img src='/images/fb.png' style='margin-right: 6px; margin-top: -5px;' /></a>
                                                <a href='https://vk.com/iopticru' target=_BLANK><img src='/images/vk.png' style='margin-right: 6px; margin-top: -5px;' /></a>
                                                <a href='https://www.instagram.com/ioptic.ru/' target=_BLANK><img src='/images/instagram_1.png' style='margin-right: 6px; margin-top: -5px;' /></a>
                                            </div>
					</div>
					<div class='clear'></div>
				</div>
			</div>
			<div class='footerGreyLine'>
				<div class='all_lines center'>
					<div><a href='".$main->modules->gmi( "rules" )->dbinfo['local']."'>".$main->modules->gmi( "rules" )->getName()."</a> / <a href='".$main->modules->gmi( "confidentiality" )->dbinfo['local']."'>".$main->modules->gmi( "confidentiality" )->getName()."</a> / ".$this->psl( $lang->gp( 24, true ) )."</div>
					<span class='mobile_copy'>".str_replace( "ООО", "<br/>ООО", $this->psl( $lang->gp( 24, true ) ) )."</span>
				</div>
			</div>
		</footer><div class='fixed_payment'></div>
		".'<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
	(function (d, w, c) {
    	(w[c] = w[c] || []).push(function() {
        	try {
            	w.yaCounter49350217 = new Ya.Metrika2({
                	id:49350217,
                	clickmap:true,
                	trackLinks:true,
                	accurateTrackBounce:true
            	});
        	} catch(e) { }
    	});
 
    	var n = d.getElementsByTagName("script")[0],
        	s = d.createElement("script"),
        	f = function () { n.parentNode.insertBefore(s, n); };
    	s.type = "text/javascript";
    	s.async = true;
    	s.src = "https://mc.yandex.ru/metrika/tag.js";
 
    	if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
    	} else { f(); }
	})(document, window, "yandex_metrika_callbacks2");
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/49350217" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-121305847-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag("js", new Date());
 
  gtag("config", "UA-121305847-1");
</script>
'."";
		
		return $t;
	}
	
	function setTitle( $newTitle, $add = true )
	{
		$this->title = $add ? $newTitle." — ".$this->title : $newTitle;
		$this->title = str_replace( '"', "", str_replace( "&quot;", "", $this->title ) );
	}
	
	function changeTitle( $html )
	{
		return str_replace( "%title%", $this->title, $html );
	}
	
	function changeMetas( $html )
	{
		global $main;
		
		return str_replace( "%metas%", $main->modules->gmi( "metas" )->getMetas(), $html );
	}
	
	function psl( $text, $br = false )
	{
		return $this->processScriptLanguage( $text, $br );
	}
	
	function processScriptLanguage( $text, $br = false )
	{
		global $lang, $mysql;
		
		if( !$text )
			return $text;
			
		if( strpos( $text, "[getph_" ) !== false ) {
			$t = explode( "[getph_", $text );
			$c = count( $t );
			$text = $t[0];
			for( $i = 1; $i < $c; $i++ ) {
				$phrase = substr( $t[$i], 0, strpos( $t[$i], "]" ) );
				$text .= str_replace( $phrase."]", $lang->getPh( $phrase ), $t[$i] );
			}
		}
		
		if( strpos( $text, "[gp_" ) !== false ) {
			$t = explode( "[gp_", $text );
			$c = count( $t );
			$text = $t[0];
			for( $i = 1; $i < $c; $i++ ) {
				$phrase = substr( $t[$i], 0, strpos( $t[$i], "]" ) );
				$text .= str_replace( $phrase."]", $lang->getPh( $phrase ), $t[$i] );
			}
		}
		
		if( strpos( $text, "[getphglobal_" ) !== false ) {
			$t = explode( "[getphglobal_", $text );
			$c = count( $t );
			$text = $t[0];
			for( $i = 1; $i < $c; $i++ ) {
				$phrase = substr( $t[$i], 0, strpos( $t[$i], "]" ) );
				$text .= str_replace( $phrase."]", $lang->getPh( $phrase, true ), $t[$i] );
			}
		}
		
		$text = str_replace( "\r", "", $text );
		$text = str_replace( "[/b]", "</b>", $text );
		$text = str_replace( "[b]", "<b>", $text );
		$text = str_replace( "[/b]", "</b>", $text );
		$text = str_replace( "[p]", "<p>", $text );
		$text = str_replace( "[/p]", "<p>", $text );
		$text = str_replace( "[h1]", "<h1>", $text );
		$text = str_replace( "[/h1]", "</h1>", $text );
		$text = str_replace( "[ol]", "<ol>", $text );
		$text = str_replace( "[/ol]", "</ol>", $text );
		$text = str_replace( "[ul]", "<ul>", $text );
		$text = str_replace( "[/ul]", "</ul>", $text );
		$text = str_replace( "[li]", "<li>", $text );
		$text = str_replace( "[/li]", "</li>", $text );
		$text = str_replace( "[small]", "<font style='font-size: 0.6em;'>", $text );
		$text = str_replace( "[/small]", "</font>", $text );
		$text = str_replace( "[red]", "<font style='color: red;'>", $text );
		$text = str_replace( "[/red]", "</font>", $text );
		$text = str_replace( "[green]", "<font style='color: green;'>", $text );
		$text = str_replace( "[/green]", "</font>", $text );
		$text = str_replace( "[hr]", "<hr>", $text );
		$text = str_replace( "[br]", "<br>", $br ? str_replace( "\n", "<br>", $text ) : $text );
		
		$text = str_replace( "[site_name_nolink]", $lang->getPh( 9 )." (".$_SERVER['HTTP_HOST'].")", $text );
		$text = str_replace( "[site_name]", "<a href=\"https://".$_SERVER['HTTP_HOST']."\" target=_BLANK>".$lang->getPh( 9 )." (".$_SERVER['HTTP_HOST'].")</a>", $text );
		$text = str_replace( "[hostname]", "https://".$_SERVER['HTTP_HOST'], $text );
		
		$text = str_replace( "[year]", date( "Y" ), $text );
		$text = str_replace( "[local_email]", "<a href=\"mailto:".$mysql->settings['local_email']."\" target=_BLANK>".$mysql->settings['local_email']."</a>", $text );
		$text = str_replace( "[local_phone]", "<h2>".$mysql->settings['local_phone']."</h2>", $text );
		$text = str_replace( "[local_phone_print]", $mysql->settings['local_phone'], $text );
		
		
		return $text;
	}
}

?>