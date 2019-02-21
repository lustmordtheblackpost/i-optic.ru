<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulecallback extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	var $save_error = "";
	var $error_point = '';
	var $output = "";
	var $callback_name = "";
	var $callback_phone = "";
	var $callback_time = "";
	var $callback_q = "";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function getFloatBlock() 
	{
		global $mysql, $query, $lang, $main, $utils;
		
		$t = "
		<script src=\"/jsf/im/jquery.inputmask.bundle.js\"></script>
  		<script src=\"/jsf/im/phone.js\"></script>
  		<script src=\"/jsf/im/inputmask.binding.js\"></script>
		<div class='fixed'>
			<div class='window'>
				<div class='closer' onclick=\"hideCallback( function() { if( old_fixed_data != '' ) $( '.fixed' ).find( '.inner' ).html( old_fixed_data ); } );\"><img src='/images/smx.png' alt='closer' /></div>
				<div class='inner'>
					<h3>".$main->templates->psl( $lang->gp( 73 ), true )."</h3>
					<p>".$main->templates->psl( $lang->gp( 74 ), true )."</p>
					<div class='input'>
						<div class='title'>".$lang->gp( 61, true )."</div>
						<input type=text id='thename' value='' />
					</div>
					<div class='input'>
						<div class='title'>".$lang->gp( 47 )."</div>
						<input type=text id='thephone' value='' placeholder='+7 ___ ___ __ __' data-inputmask=\"'mask': '+7 999 999 99 99', 'clearMaskOnLostFocus': true, 'clearIncomplete': false, 'showMaskOnHover': true, 'showMaskOnFocus': true\" />
					</div>
					<div class='button' onclick=\"
						if( $( '#thename' ).val() == '' ) {
							alert( 'Укажите своё имя' );
							return;
						}
						if( $( '#thephone' ).val() == '+7 ___ ___ __ __' || $( '#thephone' ).val() == '' ) {
							alert( 'Укажите телефон' );
							return;
						}
						processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '1', '&name=' + $( '#thename' ).val() + '&phone=' + $( '#thephone' ).val(), 'afterCllabacl( data );' );
					\">".$lang->gp( 62, true )."</div>
				</div>
			</div>
		</div>
		
		<script>
			var old_fixed_data = '';
			function afterCllabacl( data )
			{
				old_fixed_data = $( '.fixed' ).find( '.inner' ).html();
				var he = $( '.fixed' ).find( '.inner' ).height();
				$( '.fixed' ).find( '.inner' ).html( data ).height( he );
				
				var he = $( '.fixed' ).find( '.window' ).height();
				$( '.fixed' ).find( '.window' ).css( 'margin-top', he / 2 * -1 );
			}
			
			$(window).resize(function()
			{
				var he = $( '.fixed' ).find( '.window' ).height();
				if( he > 0 )
					$( '.fixed' ).find( '.window' ).css( 'margin-top', he / 2 * -1 );
			});
			
			var CallbackappierSpeed = 0;
			
			function showCallbackfloat( speed )
			{
				CallbackappierSpeed = speed;
				$( '.fixed' ).css( 'opacity', 0 ).show();
				var he = $( '.fixed' ).find( '.window' ).height();
				$( '.fixed' ).find( '.window' ).css( 'margin-top', he / 2 * -1 );
				$( '.fixed' ).animate( { opacity: 1 }, speed );
			}
			
			function hideCallback( after )
			{
				$( '.fixed' ).animate( { opacity: 0 }, CallbackappierSpeed, function() { $( '.fixed' ).hide(); if( after != undefined ) after(); } );
			}
		</script>
		";
		
		return $t;
	}
	
	function parseExternalRequest()
	{
		global $query, $main, $utils, $lang, $mysql;
		
		$type = $query->gp( "localtype" );

		switch( $type ) {
			case 1:
				
				$name = $query->gp( "name" );
				$phone = $query->gp( "phone" );
				
				$mail_agent = $main->modules->gmi( "mail_agent" );
				if( $mail_agent  ) {
					
					$template = $main->templates->processScriptLanguage( $lang->gp( 50 ), true );
					$template = str_replace( "[name]", "<b>".$name."</b>", $template );
					$template = str_replace( "[phone]", "<b>".$phone."</b>", $template );
						
					$mail_agent->sendMessage( $this->getParam( "where_to_send_mails" ), $this->getParam( 'email_from' ), $main->templates->psl( $lang->gp( 51 ) ), $template );
						
				}
					
				return "<h3>".$main->templates->psl( $lang->gp( 63, true ), true )."</h3>";
		}
	}
}

?>