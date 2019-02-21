<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
//

@include_once( ROOT_PATH."smtp/SMTP.php" );
@include_once( ROOT_PATH."smtp/PHPMailer.php" );
@include_once( ROOT_PATH."smtp/Exception.php" );

class modulemail_agent extends RootModule
{
    var $dbinfo = null;
    var $curFilePath = "";
    
    function init( $dbinfo )
    {
        $this->dbinfo = $dbinfo;
        $this->curFilePath = $this->getRootFilePath( __FILE__ );
    }
    
    function sendMessage( $to, $from, $subject, $message )
    {
        
        /*$tt = explode( ".", $_SERVER['HTTP_HOST'] );
  
        $adress = $tt[count( $tt ) - 2].".".$tt[count( $tt ) - 1];
  
        
        $to = str_replace( "[SERVER]", $adress, $to );
  
        $from = str_replace( "[SERVER]", $adress, $from );
  
        
        $head = "Content-Type: text/html; charset=UTF-8\n";
        $head .= "Content-Transfer-Encoding: quoted-printable\n";
        $head .= "From: ".$from."\n";*/
        
        return $this->send($to, $from, $subject, $message);
        /*return $this->sendHTMLMessage( $to, $from, $subject, $message );*/
        // return @mail( $to, $subject, $message, $head );
    }
    
    function send( $to, $from, $subject, $message){
        try{
            
            $mail = new \PHPMailer\PHPMailer\PHPMailer();
            $mail->CharSet = 'UTF-8';
    
            // Настройки SMTP
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPDebug = 0;
            $mail->Host = "ssl://smtp.yandex.ru";
            $mail->Port = 465;
            $mail->Username = "info@i-optic.ru";
            $mail->Password = "q123Q123";
            // От кого
            $mail->setFrom($from);
    
            // Кому
            $mail->addAddress($to);
    
            // Тема письма
            $mail->Subject = $subject;
    
            // Тело письма
            $mail->msgHTML($message);
            
            $mail->send();
            
        } catch (Exception $e){
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }
    // Старый функционал отправки сообщений
    /*function sendHTMLMessage( $to, $from, $subject, $message, $returnPath = '' )
    {
        $tt = explode( ".", $_SERVER['HTTP_HOST'] );
        $adress = $tt[count( $tt ) - 2].".".$tt[count( $tt ) - 1];
        $to = str_replace( "[SERVER]", $adress, $to );
        $from = str_replace( "[SERVER]", $adress, $from );
        
        $boundary = uniqid('np');
        
        $html = "This is a MIME encoded message.";
   
         $html .= "\r\n\r\n--".$boundary."\r\n";
         $html .= "Content-type: text/plain;charset=utf-8\r\n\r\n";
         $html .= strip_tags( str_replace( "</p>", "\n", $message ) );
         
         $html .= "\r\n\r\n--".$boundary."\r\n";
         $html .= "Content-type: text/html;charset=utf-8\r\n\r\n";
         $html .= $message;

         $html .= "\r\n\r\n--".$boundary."--";
 
        // INSERTED BLOCK
        $headers = 'From: no-reply@i-optic.ru' . "\r\n" .
        'Content-type: text/html; charset=utf-8' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
        file_put_contents ( 'files/maillog.txt', '|| TO ||:'.$to.'|| subject ||:'.$subject.'|| html ||:'.$html.'|| headers ||:'.$headers.'|| returnPath ||:'.$returnPath );
        //exit;
        $a = mail('g_masleev@yahoo.com', 'optic mail test', '|| TO ||', $headers);
  
        return mail( $to, $subject, $html, $headers, $returnPath ? $returnPath : "-f ".$from );
    }*/
}
