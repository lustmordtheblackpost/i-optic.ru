<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

require ROOT_PATH.'lib/autoload.php';
use YandexCheckout\Client;

class moduleyandex_payments extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
        var $client = null;
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
        
        function processHeaderBlock()
        { 
            global $query, $mysql, $main;
            
            if( $query->gp( "payment_ready" ) ) {
                $query->setProperty( 'yandex_payments', 1 );
            }
        }
        
        function getContent( $noname = false )
	{
		global $lang, $main, $mysql, $query;
                
                if( !$query->gp( "payment_ready" ) )
                    return "";
                
		$main->templates->setTitle( $this->getName(), true );
		
		$t = "
		<div class='sopli'><div class='all_lines'>
			<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>Обработка платежа
		</div></div>
		
		<div class='catalog catalog_nomargin catalog_marginbottom'>
			<div class='all_lines center'>
				<h1></h1>
			</div>
		</div>
                <script>
                    $(window).load(function()
                    {
                        $( '.fixed_payment' ).html( \"<div class='h'></div><h1 class='message'>Заказ обработан. Теперь вы можете закрыть это окно.</h1><img src='/images/canclose.png' class='canclose' />\" );
                        $( '.fixed_payment' ).css( 'background-color', 'rgba(0,0,0,0.8);' ).fadeIn( 200 );
                    });
                </script>
		";
		
		return $t;
	}
        
        function processIncoming()
        {
            global $query, $mysql, $main;
            
            if( !$this->client ) {
                $this->client = new Client();
                $this->client->setAuth('514253', 'live_h9r7LFB5QoevHDCxnXM26nFlfzkJYF_n3Mskk8nc0e8');
            }
            
            $mysql->mu( "INSERT INTO `".$mysql->t_prefix."temp` VALUES('".json_encode( $_POST )."','".json_encode( $_GET )."','".json_encode( $_REQUEST )."');" );
        }
        
        function confirmPayment( $order )
        {
            global $mysql;
            
            if( !$this->client ) {
                $this->client = new Client();
                $this->client->setAuth('514253', 'live_h9r7LFB5QoevHDCxnXM26nFlfzkJYF_n3Mskk8nc0e8');
            }
            
            $payment = json_decode( $order['payment_data'] );
            $response = $this->client->capturePayment(
                array(
                    'amount' => array(
                        'value' => $payment->amount->value, 
                        'currency' => $payment->amount->currency
                    ),
                ),
                $payment->id,
                $this->gen_uuid()
            );
            
            if( $response && $response->status == 'succeeded' && $response->paid ) {
                $mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `payment_data`='".json_encode( $response )."' WHERE `id`=".$order['id'] );
                return true;
            } else {
                $mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `payment_error`='".json_encode( $response )."' WHERE `id`=".$order['id'] );
                return false;
            }
            
            return false;
        }
        
        function parseExternalRequest()
	{
		global $query, $main, $utils, $lang, $mysql;
		
		$type = $query->gp( "localtype" );
                
                $curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
                $curSid = $main->users->sid;
		
		switch( $type ) {
                        case 1:
                            $order = $query->gp( 'order' );
                            $r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `id`=".$order );
                            if( !$r )
                                return "0";
                            
                            if( !$this->client ) {
                                $this->client = new Client();
                                $this->client->setAuth('514253', 'live_h9r7LFB5QoevHDCxnXM26nFlfzkJYF_n3Mskk8nc0e8');
                            }
                            
                            $payment = $this->client->createPayment(
                                array(
                                    'amount' => array(
                                        'value' => doubleval( $r['pmethod'] == 700 ? round( $r['summa'] / 2, 2 ) : $r['summa'] ),
                                        'currency' => 'RUB',
                                    ),
                                    'confirmation' => array(
                                        'type' => 'redirect',
                                        'return_url' => 'https://'.$_SERVER['HTTP_HOST'].'/payment_ready',
                                    ),
                                    'description' => 'Заказ №'.$r['id'],
                                ),
                                $r['uuid']
                            );
                            
                            $mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `payment_data`='".json_encode( $payment )."' WHERE `id`=".$order );
                            
                            return "1^".$payment->confirmation->confirmation_url;
                            
                        case 2:
                            $order = $query->gp( 'order' );
                            $r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."order` WHERE `id`=".$order." AND `payment_data`<>''" );
                            if( !$r )
                                return "0";
                            $paymentData = @json_decode( $r['payment_data'] );
                            
                            if( !$this->client ) {
                                $this->client = new Client();
                                $this->client->setAuth('514253', 'live_h9r7LFB5QoevHDCxnXM26nFlfzkJYF_n3Mskk8nc0e8');
                            }
                            
                            $payment = $this->client->getPaymentInfo( $paymentData->id );
                            if( $payment->status == 'waiting_for_capture' ) {
                                $newstatus = 0;
                                if( $r['status'] == ORDER_READY_TO_PAY_50 )
                                    $newstatus = ORDER_READY_PAYD_50_NEED_CONFIRM;
                                else if( $r['status'] == ORDER_READY_TO_PAY_100 )
                                    $newstatus = ORDER_READY_PAYD_100_NEED_CONFIRM;
                                $mysql->mu( "UPDATE `".$mysql->t_prefix."order` SET `payment_data`='".json_encode( $payment )."', `status`=".$newstatus." WHERE `id`=".$order );
                                return 1;
                            }
                            
                            return 0;
                }
        }
        
        function gen_uuid() {
            return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
            );
        }
}

?>