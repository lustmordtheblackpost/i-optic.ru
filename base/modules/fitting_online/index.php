<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulefitting_online extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
        
        function getMobileMenuLink()
	{
		global $main, $mysql, $lang;
		
		return "<img src='/images/onlne.png' class='pointer' onclick=\"urlmove('".$mysql->settings['local_folder'].$this->dbinfo['local']."');\" />";
	}
        
        function getVeryFirstHTMLBlock()
        {
            global $mysql, $utils;
            
            return "<link rel=\"stylesheet\" href=\"//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css\">
		<script src=\"https://code.jquery.com/ui/1.12.1/jquery-ui.js\"></script>
                <script src=\"".$mysql->settings['local_folder'].$utils->javascript_files_path."jquery.ui.touch-punch.min.js\"></script><div class='fixed_load fixed_load_exact'>
			<div class='window'>
				<div class='closer' onclick=\"
					$( '.fixed_load' ).fadeOut( 400, function() { 
						if( old_fixed_loaddata != '' ) {
							$( '.fixed_load' ).find( '.inner' ).html( old_fixed_loaddata ); 
						}
						fixed_beforeClose();
					});
				\"><img src='/images/smx.png' alt='closer' /></div>
				<div class='inner'>
					".$this->getFirst()."
				</div>
			</div>
		</div><script>
			var old_fixed_loaddata = '';
			function afterCllabaclddssssd( data )
			{
                            if( $( '.fixed_load' ).is( ':visible' ) ) {
				if( old_fixed_loaddata == '' ) {
					old_fixed_loaddata = $( '.fixed_load' ).find( '.inner' ).html();
				}
				$( '.fixed_load' ).find( '.inner' ).html( data );
                            } else {
                                $( '.fitting_content' ).find( '.inner' ).html( data );
                                setTimeout( function() {
                                    var ratio = ( 396 / ( $( '.fitting_content .iloader' ).height() + 4 ) );
                                    $( '.fitting_content .iloader' ).height( $( '.fitting_content .iloader' ).width() / ratio );
                                }, 200 );
                            }
			}
			
			function fixed_beforeClose()
			{
				
			}
                        
                        var foridAfter = 0;
                        
                        function showfixed_load( forid )
                        {
                            if( forid != undefined && forid )
                                foridAfter = forid;
                            if( $( '.top_line_white .fitting_online' ).is( ':visible' ) ) { 
                                $( '.fixed_load .window' ).css( 'top', $( '.fixed_load .window' ).height() * -1 );
                                $( '.fixed_load' ).fadeIn( 400 ); 
                                var ttop = ( $( '.fixed_load' ).height() - $( '.fixed_load .window' ).height() ) / 2;
                                if( ttop < 0 )
                                    ttop = 0;
                                $( '.fixed_load .window' ).animate( { top: ttop }, 400 );
                            } else { 
                                urlmove('/fitting_online');
                            }
                        }
		</script>";
        }
	
	function getHeaderBlock( $noscripts = false )
	{
		global $mysql, $query, $lang, $main, $utils;
		
		$t = "<div class='fitting_online' onclick=\"showfixed_load();\" onmouseover=\"$( this ).find( 'img' ).attr( 'src', '/images/virtual-2.gif' );\"  onmouseout=\"$( this ).find( 'img' ).attr( 'src', '/images/face.png' );\">
			<img src='/images/face.png' width=52 height=59 onmouseover=\"$( this ).attr( 'src', '/images/virtual-2.gif' );\"  onmouseout=\"$( this ).attr( 'src', '/images/face_big.png' );\" />
			<label onmouseover=\"$( this ).parent().find( 'img' ).attr( 'src', '/images/virtual-2.gif' );\"  onmouseout=\"$( this ).parent().find( 'img' ).attr( 'src', '/images/face_big.png' );\">
				".$lang->gp( 23 )."<br/>
				<strong>".$main->templates->psl( $lang->gp( 26 ), true )."</strong>
			</label>
		</div>";
		
		return $t;
	}
        
        function getContent()
        {
            global $lang, $main, $mysql;
		
		$main->templates->setTitle( $this->getName(), true );
		
		$t = "
		<div class='sopli'><div class='all_lines'>
			<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>".$this->getName()."
		</div></div>
		
		<div class='catalog catalog_nomargin'>
			<div class='all_lines fixed_load fitting_content'><div class='window'><div class='inner'>
				".$this->getFirst()."
			</div></div></div>
		</div>
                <script>
                    $(window).load(function()
                    {
                        $( '.fixed_load_exact' ).remove();
                    });
                </script>
		";
		
		return $t;
        }
	
	function parseExternalRequest()
	{
		global $query, $main, $utils, $lang, $mysql;
		
		$type = $query->gp( "localtype" );

		switch( $type ) {
			case 1: // Получить блок редактирования с загруженным файлом
				$p = $query->gp( 'p' );
				$p = $p >= 1 && $p <= 2 ? $p : 1;
				return $this->getSecond( $p );
				
			case 2: // Получить блок выборки
				return $this->getFirst();
				
			case 3: // Пользовательский файл
					$fname = $query->gp_post( "fname" );
    				if( !isset( $_FILES[$fname] ) || !file_exists( $_FILES[$fname]['tmp_name'] ) )
						return 0;
						
					$extensions = "jpg|jpeg|gif|png"; 
					$extensions = $extensions && @strpos( $extensions, "|" ) > 0 ? @explode( "|", $extensions ) : null;
					
					$_FILES[$fname]['name'] = @addslashes( @htmlspecialchars( $_FILES[$fname]['name'] ) );
					$t = @explode( ".", $_FILES[$fname]['name'] );
					$ext = @strtolower( $t[@count( $t ) - 1] );
					
					if( @array_search( $ext, $extensions ) === false )
						return 0;
							
					$tmp = $_FILES[$fname]['tmp_name'];
					$newname = md5( $tmp.time().$_FILES[$fname]['name'] );
					$newfile = $newname.".".$ext;
					$path = ROOT_PATH."tmp/".$newfile;
					
    				$s = @getimagesize( $tmp );
    				if( !$s || !$s[0] || !$s[1] )
    					return 0;
    				   					
    				@move_uploaded_file( $tmp, $path );
    				return file_exists( $path ) ? $newfile : 0;
    				
			case 4: // Сохранение BLOB
			
				$left = $query->gp( "left" );
				$top = $query->gp( "top" );
				$width = $query->gp( "width" );
				$height = $query->gp( "height" );
				$deg = $query->gp( "deg" );
				$deg = $deg ? $deg : 0;
			
				$tmp = $_FILES['image']['tmp_name'];
				
				$fileName = md5( time().$_FILES['image']['tmp_name'] ).".png";
				$src_img = @imagecreatefrompng( $tmp );
				$s = @getimagesize( $tmp );
				$dest_img_big = @imagecreatetruecolor( $s[0] - ( 58 * 2 ), $s[1] );
				@imagecopyresampled( $dest_img_big, $src_img, 0, 0, 58, 0, $s[0] - ( 58 * 2 ), $s[1], $s[0] - ( 58 * 2 ), $s[1] );
				@imagepng( $dest_img_big, ROOT_PATH."files/upload/usersData/".$fileName );
				@imagedestroy( $src_img );
				@imagedestroy( $dest_img_big );
				
				if( !$main->users->auth )
					$mysql->mu( "DELETE FROM `".$mysql->t_prefix."blobs` WHERE `sid`='".$main->users->sid."'" );
				
				$mysql->mu( "INSERT INTO `".$mysql->t_prefix."blobs` VALUES(
					0,
					".( $main->users->auth ? $main->users->userArray['id'] : 0 ).",
					'".$main->users->sid."',
					'".$fileName."',
					".$left.",
					".$top.",
					".$width.",
					".$height.",
					".$deg.",
                                        ".time()."
				);" );
				
				$r = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."blobs` WHERE `sid`='".$main->users->sid."' ORDER BY `id` DESC" );
					
				return $r['id'];
		}
	}
	
	function getFirst()
	{
		global $mysql, $query, $lang, $main;
		
		return "
					<h3>Загрузите свою фотографию</h3>
					<div class='loader'>
						<div class='elem' id='imageloaderdiv' style='background-image: url(/images/addPhoto.png);' onclick=\"processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '1', '&p=1', 'afterCllabaclddssssd( data );' );\"><div class='text'>Загрузить<br/>фотографию</div></div>
						<div class='elem' style='background-image: url(/images/webcamera.png);' onclick=\"processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '1', '&p=2', 'afterCllabaclddssssd( data );' );\"><div class='text'>Использовать<br/>Веб-камеру</div></div>
					</div>
					<div class='add'>
						<h4>Советы, как сделать фотографию лучше</h4>
						<div class='elem'><img src='/images/girl_1.png' /><span>Убедитесь, что<br/>лицо расположено<br/>в анфас</span></div>
						<div class='elem'><img src='/images/girl_2.png' /><span>Снимите<br/>свои очки</span></div>
						<div class='elem'><img src='/images/girl_3.png' /><span>Избегайте наклонов<br/>или смещения головы</span></div>
						<div class='elem'><img src='/images/girl_4.png' /><span>Используйте<br/>изображения с<br/>хорошим освещением</span></div>
						<div class='clear'></div>
					</div>
		";
	}
        
	function getSecond( $type )
	{
		global $mysql, $query, $lang, $main;
		
		$text = "";
		if( $type == 1 ) 
			$text = "Загрузить фото";
		else if( $type == 2 ) 
			$text = "Сделать снимок";
		
		return "
					<div class='i_top i_block'>
						<div class='bleft'>
							<div class='back' onclick=\"".( $type == 2 ? "fixed_beforeClose();" : "" )."processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '2', '', 'afterCllabaclddssssd( data );' );\">Назад</div>
						</div>
						<div class='bright'>
							<div class='load' id='bright_loader'>".$text."</div>
						".( $type == 2 ? "
							<div class='reload'>Переснять</div><div class='use' onclick=\"uploadImageToServer( $( '.video .canvas' ), 'reloadPageWithLoadedData( data );' );\">Примерить оправу</div>
						" : "
							<div class='reload'>Другое</div><div class='use' onclick=\"uploadImageToServer( $( '.video .canvas' ), 'reloadPageWithLoadedData( data );' );\">Примерить оправу</div>
						" )."
						</div>
                                                <div class='clear'></div>
					</div>
					<div class='i_middle i_block'>
						<div class='bleft'>
							<h3>Настройте изображение</h3>
							<div class='line'>
								<div class='ed'>1</div>
								<div class='et'>Разместите лицо по центру изображения</div>
								<div class='ei'><img src='/images/girl_u1.png' /></div>
								<div class='clear'></div>
							</div>
							<div class='line'>
								<div class='ed'>2</div>
								<div class='et'>Выровняйте изображение</div>
								<div class='ei'><img src='/images/girl_u2.png' /></div>
								<div class='clear'></div>
							</div>
						</div>
						<div class='bright'>
							<div class='iloader'>
								<div class='l_left'></div>
								<div class='l_right'></div>
								<div class='l_center' id='l_center'><div class='ochki invisible draggable ui-widget-content' id='draggable'><img src='/images/ochki_empty.png' class='mm' /><div class='additional_elements sizer sizer_lefttop ui-resizable-handle ui-resizable-nw'></div><div class='additional_elements sizer sizer_leftbottom ui-resizable-handle ui-resizable-sw'></div><div class='additional_elements sizer sizer_righttop ui-resizable-handle ui-resizable-ne'></div><div class='additional_elements sizer sizer_rightbottom ui-resizable-handle ui-resizable-se'></div><div class='additional_elements rotater rotater_left'></div><div class='additional_elements rotater rotater_right'></div></div></div>
								".( $type == 2 ? "<div class='video'><video autoplay id=\"vid\"></video><div class='canvas'></div></div>" : "<div class='video'><div class='canvas'></div></div>" )."
								<div class='bbom'><div class='over'></div>
									<div class='side_block'>
										Размер изображения
										<div class='slider' id='slider_size'></div>
									</div>
									<div class='side_block side_block_right'>
										Наклон изображения
										<div class='slider' id='slider_rotate'></div>
									</div>
									<div class='center_block'>
										<div class='s_top' id='button_moveup'></div>
										<div class='s_bottom' id='button_movedown'></div>
										<div class='s_left' id='button_moveleft'></div>
										<div class='s_right' id='button_moveright'></div>
									</div>
								</div>
							</div>
						</div>
					</div>
		<script>
		
			function uploadImageToServer( object, afterSaving )
			{
				html2canvas( object, {
  					onrendered: function( canvas ) {
    					canvas.toBlob( function( blob ) {
							processSimpleAsyncReqForModuleWithFormdata( '".$this->dbinfo['local']."', '4', blob, $( '#draggable' ).position().left, $( '#draggable' ).position().top, $( '#draggable' ).width(), $( '#draggable' ).height(), $( '#draggable' ).find( '.mm' ).attr( 'deg' ), afterSaving );
						});
 					}
				});
			}
			
			function reloadPageWithLoadedData( data )
			{
				if( data != '' && data ) {
					setCookie( 'output', 2, { path: '/' } );
					setCookie( 'selected_selfface', data, { path: '/' } );
					setTimeout( function(){urlmove( '/catalog".( $query->gp( "catalog" ) != 1 ? $query->gp( "catalog" ) : '' )."' + ( foridAfter ? '/show' + foridAfter : '' ) );}, 200 );
				}
			}
		
			".( $type == 2 ? "
				var video = document.querySelector(\"#vid\"),
       			localMediaStream = null,
       			onCameraFail = function (e) {
            		console.log('Camera did not work.', e);
            		$( '.i_top .bright .load' ).hide();
            		alert( 'Ваша камера не функционирует' );
        		};
       			navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
        		window.URL = window.URL || window.webkitURL;
        		navigator.getUserMedia({video: true}, function(stream) {
            		video.srcObject=stream;
	            	localMediaStream = stream;
	            	$('#vid').on(\"play\", function (e) {
	            		var scale = $( '#vid' ).height() / $( '#vid' ).width();
	            		$( '#vid' ).height( $( '#vid' ).parent().height() );
	            		$( '#vid' ).width( $( '#vid' ).height() / scale );
	            		$( '#vid' ).css( 'margin-left', ( $( '#vid' ).parent().width() - $( '#vid' ).width() ) / 2 ).css( 'opacity', 1 );
					});
					$( '.i_top .bright .load' ).click(function() {
						var canvas = document.createElement('canvas');
        				canvas.width = $('#vid').width();
        				canvas.height = $('#vid').height();
        				canvas.getContext('2d').drawImage($('#vid')[0], 0, 0, canvas.width, canvas.height);
        				var img = document.createElement('img');
        				img.src = canvas.toDataURL();
        				$( '.video .canvas' ).html('');
        				$( '.video .canvas' )[0].prepend(img);
        				$( '.video .canvas' ).css( 'opacity', 1 );
        				$( '.video .canvas' ).width( $( '.video' ).width() );
        				$( '.video .canvas' ).height( $( '.video' ).height() );
        				var img = $( '.video .canvas' ).find( 'img' );
        				img.css( 'margin-left', ( $( '#vid' ).parent().width() - $( '#vid' ).width() ) / 2 );
        				$( '.i_top .bright .load' ).hide();
        				$( '.i_top .bright .reload' ).show();
						$( '.i_top .bright .use' ).show();
						$( '.bbom .over' ).fadeOut();
						$( '#vid' ).css( 'opacity', 0 );
						$( '.iloader .ochki' ).show();
						$( '#draggable' ).draggable({ containment: 'parent', scroll: false, cancel: '.additional_elements' }).resizable({ containment: 'parent', aspectRatio: 145 / 74, handles: 'nw, ne, sw, se' });
					});
					$( '.i_top .bright .reload' ).click(function() {
						$( '.i_top .bright .load' ).show();
						$( '.i_top .bright .reload' ).hide();
						$( '.i_top .bright .use' ).hide();
						$( '.video .canvas' ).css( 'opacity', 0 );
						$( '.bbom .over' ).fadeIn();
						$( '#vid' ).css( 'opacity', 1 );
						$( '#slider_size' ).slider( 'option', 'value', 50 );
						$( '#slider_rotate' ).slider( 'option', 'value', 50 );
						$( '.iloader .ochki' ).hide();
					});
					fixed_beforeClose = function() {
						$( '#vid' ).fadeOut( 200 ); 
						localMediaStream.getTracks()[0].stop();
					};
					$( '#slider_size' ).slider( { step: 1, value: 50, slide: function(event,ui) {
						var change = ( ui.value - 50 ) * 2;
						var img = $( '.video .canvas' ).find( 'img' );
						if( img.attr( 'or_width' ) == undefined || img.attr( 'or_width' ) == '' || img.attr( 'or_width' ) == 0 ) {
							img.attr( 'or_width', img.width() );
							img.attr( 'or_height', img.height() );
							img.attr( 'or_marginleft', getDigits( img.css( 'margin-left' ) ) );
						}
						img.width( Math.floor( img.attr( 'or_width' ) ) + Math.floor( change ) );
						img.css( 'margin-left', Math.fround( img.attr( 'or_marginleft' ) ) * -1 + ( ( Math.floor( img.attr( 'or_width' ) ) - img.width() ) / 2 ) );
						img.css( 'margin-top', ( Math.floor( img.attr( 'or_height' ) ) - img.height() ) / 2 );
					} } );
					$( '#slider_rotate' ).slider( { step: 1, value: 50, slide: function(event,ui) {
						var change = ( ui.value - 50 );
						var img = $( '.video .canvas' ).find( 'img' );
						degrees = 0 + Math.floor( change );
						img.css({
  							'-webkit-transform' : 'rotate('+degrees+'deg)',
     						'-moz-transform' : 'rotate('+degrees+'deg)',  
      						'-ms-transform' : 'rotate('+degrees+'deg)',  
       						'-o-transform' : 'rotate('+degrees+'deg)',  
          					'transform' : 'rotate('+degrees+'deg)',  
               				'zoom' : 1
    					});
					} } );
					$( '#button_moveup' ).click(function(){
						var img = $( '.video .canvas' ).find( 'img' );
						var y_offset = Math.floor( img.attr( 'y_offset' ) );
						if( !y_offset )
							y_offset = 0;
						if( y_offset <= -50 || y_offset >= 50 )
							return;
						y_offset -= 2;
						img.attr( 'y_offset', y_offset );
						img.css( 'top', y_offset );
					});
					$( '#button_movedown' ).click(function(){
						var img = $( '.video .canvas' ).find( 'img' );
						var y_offset = Math.floor( img.attr( 'y_offset' ) );
						if( !y_offset )
							y_offset = 0;
						if( y_offset <= -50 || y_offset >= 50 )
							return;
						y_offset += 2;
						img.attr( 'y_offset', y_offset );
						img.css( 'top', y_offset );
					});
					$( '#button_moveleft' ).click(function(){
						var img = $( '.video .canvas' ).find( 'img' );
						var x_offset = Math.floor( img.attr( 'x_offset' ) );
						if( !x_offset )
							x_offset = 0;
						if( x_offset <= -50 || x_offset >= 50 )
							return;
						x_offset -= 2;
						img.attr( 'x_offset', x_offset );
						img.css( 'left', x_offset );
					});
					$( '#button_moveright' ).click(function(){
						var img = $( '.video .canvas' ).find( 'img' );
						var x_offset = Math.floor( img.attr( 'x_offset' ) );
						if( !x_offset )
							x_offset = 0;
						if( x_offset <= -50 || x_offset >= 50 )
							return;
						x_offset += 2;
						img.attr( 'x_offset', x_offset );
						img.css( 'left', x_offset );
					});
					
					$( '.rotater_left' ).mousedown(function(e){
						$( this ).attr( 'dragging', 1 );
						$( this ).data('p0', { x: e.pageX, y: e.pageY });
					});
					$( '.rotater_left' ).parent().parent().mouseup(function(e){
						$( this ).find( '.rotater_left' ).attr( 'dragging', 0 );
					}).mousemove(function(e){
						if( $( this ).find( '.rotater_left' ).attr( 'dragging' ) == 1 ) {
							var p0 = $(this).find( '.rotater_left' ).data('p0');
        					var p1 = { x: e.pageX, y: e.pageY };
        					var d = p0.y - p1.y;
							$( this ).find( '.mm' ).css( 'transform', 'rotate(' + d + 'deg)' );
							$( this ).find( '.mm' ).attr( 'deg', d );
						}
					});
					
					$( '.rotater_right' ).mousedown(function(e){
						$( this ).attr( 'dragging', 1 );
						$( this ).data('p0', { x: e.pageX, y: e.pageY });
					});
					$( '.rotater_right' ).parent().parent().mouseup(function(e){
						$( this ).find( '.rotater_right' ).attr( 'dragging', 0 );
					}).mousemove(function(e){
						if( $( this ).find( '.rotater_right' ).attr( 'dragging' ) == 1 ) {
							var p0 = $(this).find( '.rotater_right' ).data('p0');
        					var p1 = { x: e.pageX, y: e.pageY };
        					var d = p0.y - p1.y;
       						d = d * -1;
							$( this ).find( '.mm' ).css( 'transform', 'rotate(' + d + 'deg)' );
							$( this ).find( '.mm' ).attr( 'deg', d );
						}
					});
					
    	   		}, onCameraFail);
    	   	" : "" )."
    	   	
    	   	".( $type == 1 ? "
    	   		$.loadScript('/jsf/jquery.ou.1.1.2.packed.js', function(){
    	   			$( '#bright_loader' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'action',
       						action: '/getblock',
       						data: {
    							ext: '1',
    							type: '100',
    							localtype: '3',
    							module: '".$this->dbinfo['local']."',
    							totemp: '1',
    							fname: 'action'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	alert( 'Неверное расширение файла' );
                					return false;
					            }
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								alert( 'Ошибка обработки файла' );
       							} else {
       								$( '.video .canvas' ).html( '<img src=\"".$mysql->settings['local_folder']."tmp/' + data + '\" />' );
       								function checkLoadImage(){
       									$( '.video .canvas' ).width( $( '.video' ).width() );
        								$( '.video .canvas' ).height( $( '.video' ).height() );
       									var img = $( '.video .canvas' ).find( 'img' );
       									if( img.height() > 0 ) {
       										var scale = img.height() / img.width();
	            							img.height( img.parent().parent().height() );
	            							img.width( img.height() / scale );
	            							var ml = ( img.parent().parent().width() - img.width() ) / 2;
	            							img.attr( 'or_ml', ml );
	            							img.css( 'margin-left', ml ).parent().css( 'opacity', 1 );
	            							$( '.i_top .bright .load' ).hide();
        									$( '.i_top .bright .reload' ).show();
											$( '.i_top .bright .use' ).show();
											$( '.bbom .over' ).fadeOut();
											$( '.iloader .ochki' ).show();
											$( '#draggable' ).draggable({ containment: 'parent', scroll: false, cancel: '.additional_elements' }).resizable({ containment: 'parent', aspectRatio: 145 / 74, handles: 'nw, ne, sw, se' });
											$( '.i_top .bright .reload' ).click(function() {
												$( '.i_top .bright .load' ).show();
												$( '.i_top .bright .reload' ).hide();
												$( '.i_top .bright .use' ).hide();
												$( '.video .canvas' ).css( 'opacity', 0 ).html('');
												$( '.bbom .over' ).fadeIn();
												$( '#slider_size' ).slider( 'option', 'value', 50 );
												$( '#slider_rotate' ).slider( 'option', 'value', 50 );
												$( '.iloader .ochki' ).hide();
											});
											fixed_beforeClose = function() {
												$( '.video .canvas' ).html('');
											};
											
											$( '#slider_size' ).slider( { step: 1, value: 50, slide: function(event,ui) {
												var change = ( ui.value - 50 ) * 2;
												var img = $( '.video .canvas' ).find( 'img' );
												if( img.attr( 'or_width' ) == undefined || img.attr( 'or_width' ) == '' || img.attr( 'or_width' ) == 0 ) {
													img.attr( 'or_width', img.width() );
													img.attr( 'or_height', img.height() );
												}
												var scale = img.attr( 'or_width' ) / img.attr( 'or_height' );
												img.width( Math.floor( img.attr( 'or_width' ) ) + Math.floor( change ) );
												img.height( img.width() / scale );
												img.css( 'margin-left', Math.fround( img.attr( 'or_ml' ) ) + Math.floor( ( Math.floor( img.attr( 'or_width' ) ) - img.width() ) / 2 ) );
												img.css( 'margin-top', ( Math.floor( img.attr( 'or_height' ) ) - img.height() ) / 2 );
											} } );
											$( '#slider_rotate' ).slider( { step: 1, value: 50, slide: function(event,ui) {
												var change = ( ui.value - 50 );
												var img = $( '.video .canvas' ).find( 'img' );
												degrees = 0 + Math.floor( change );
												img.css({
  													'-webkit-transform' : 'rotate('+degrees+'deg)',
     												'-moz-transform' : 'rotate('+degrees+'deg)',  
      												'-ms-transform' : 'rotate('+degrees+'deg)',  
       												'-o-transform' : 'rotate('+degrees+'deg)',  
          											'transform' : 'rotate('+degrees+'deg)',  
               										'zoom' : 1
    											});
											} } );
											
											$( '#button_moveup' ).click(function(){
												var img = $( '.video .canvas' ).find( 'img' );
												var y_offset = Math.floor( img.attr( 'y_offset' ) );
												if( !y_offset )
													y_offset = 0;
												if( y_offset <= -50 || y_offset >= 50 )
													return;
												y_offset -= 2;
												img.attr( 'y_offset', y_offset );
												img.css( 'top', y_offset );
											});
											$( '#button_movedown' ).click(function(){
												var img = $( '.video .canvas' ).find( 'img' );
												var y_offset = Math.floor( img.attr( 'y_offset' ) );
												if( !y_offset )
													y_offset = 0;
												if( y_offset <= -50 || y_offset >= 50 )
													return;
												y_offset += 2;
												img.attr( 'y_offset', y_offset );
												img.css( 'top', y_offset );
											});
											$( '#button_moveleft' ).click(function(){
												var img = $( '.video .canvas' ).find( 'img' );
												var x_offset = Math.floor( img.attr( 'x_offset' ) );
												if( !x_offset )
													x_offset = 0;
												if( x_offset <= -50 || x_offset >= 50 )
													return;
												x_offset -= 2;
												img.attr( 'x_offset', x_offset );
												img.css( 'left', x_offset );
											});
											$( '#button_moveright' ).click(function(){
												var img = $( '.video .canvas' ).find( 'img' );
												var x_offset = Math.floor( img.attr( 'x_offset' ) );
												if( !x_offset )
													x_offset = 0;
												if( x_offset <= -50 || x_offset >= 50 )
													return;
												x_offset += 2;
												img.attr( 'x_offset', x_offset );
												img.css( 'left', x_offset );
											});
											
					$( '.rotater_left' ).mousedown(function(e){
						$( this ).attr( 'dragging', 1 );
						$( this ).data('p0', { x: e.pageX, y: e.pageY });
					});
					$( '.rotater_left' ).parent().parent().mouseup(function(e){
						$( this ).find( '.rotater_left' ).attr( 'dragging', 0 );
					}).mousemove(function(e){
						if( $( this ).find( '.rotater_left' ).attr( 'dragging' ) == 1 ) {
							var p0 = $(this).find( '.rotater_left' ).data('p0');
        					var p1 = { x: e.pageX, y: e.pageY };
        					var d = p0.y - p1.y;
							$( this ).find( '.mm' ).css( 'transform', 'rotate(' + d + 'deg)' );
							$( this ).find( '.mm' ).attr( 'deg', d );
						}
					});
					
					$( '.rotater_right' ).mousedown(function(e){
						$( this ).attr( 'dragging', 1 );
						$( this ).data('p0', { x: e.pageX, y: e.pageY });
					});
					$( '.rotater_right' ).parent().parent().mouseup(function(e){
						$( this ).find( '.rotater_right' ).attr( 'dragging', 0 );
					}).mousemove(function(e){
						if( $( this ).find( '.rotater_right' ).attr( 'dragging' ) == 1 ) {
							var p0 = $(this).find( '.rotater_right' ).data('p0');
        					var p1 = { x: e.pageX, y: e.pageY };
        					var d = p0.y - p1.y;
       						d = d * -1;
							$( this ).find( '.mm' ).css( 'transform', 'rotate(' + d + 'deg)' );
							$( this ).find( '.mm' ).attr( 'deg', d );
						}
					});
					
       									} else {
       										setTimeout(checkLoadImage,100);
       									}
       								};
       								checkLoadImage();
       							}
			       			}
						} );
					} );
    	   		});
    	   	" : "" )."
    	   	
		</script>
		";
	}
	
	function getFilerBlock()
	{
		global $mysql, $query, $lang, $main;
		
		$t = "
		<div class='fitting_online' onclick=\"showfixed_load();\" onmouseover=\"$( this ).find( 'img' ).attr( 'src', '/images/virtual-2.gif' );\"  onmouseout=\"$( this ).find( 'img' ).attr( 'src', '/images/face_big.png' );\">
			<img src='/images/face_big.png' width=101 height=115 onmouseover=\"$( this ).attr( 'src', '/images/virtual-2.gif' );\"  onmouseout=\"$( this ).attr( 'src', '/images/face_big.png' );\" />
			<label onmouseover=\"$( this ).parent().find( 'img' ).attr( 'src', '/images/virtual-2.gif' );\"  onmouseout=\"$( this ).parent().find( 'img' ).attr( 'src', '/images/face_big.png' );\">
				".$lang->gp( 23 )."<br/>
				<strong>".$main->templates->psl( $lang->gp( 26 ), true )."</strong>
			</label>
		</div>
		";
		
		return $t;
	}
	
	function getInGoodBlock( $id = 0 )
	{
		global $mysql, $query, $lang, $main;
		
		$t = "
		<div class='inner_online' onclick=\"showfixed_load(".$id.");\" onmouseover=\"$( this ).find( 'img' ).attr( 'src', '/images/virtual-2.gif' );\"  onmouseout=\"$( this ).find( 'img' ).attr( 'src', '/images/face_big.png' );\">
			<img src='/images/face_big.png' onmouseover=\"$( this ).attr( 'src', '/images/virtual-2.gif' );\"  onmouseout=\"$( this ).attr( 'src', '/images/face_big.png' );\" />
			<label onmouseover=\"$( this ).parent().find( 'img' ).attr( 'src', '/images/virtual-2.gif' );\"  onmouseout=\"$( this ).parent().find( 'img' ).attr( 'src', '/images/face_big.png' );\">
				".$lang->gp( 23 )."<br/>
				<strong>".$main->templates->psl( $lang->gp( 26 ), true )."</strong>
			</label>
		</div>
		";
		
		return $t;
	}
}

?>