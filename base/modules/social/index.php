<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulesocial extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function getFooterBlock()
	{
		return "
		<img src='/images/facebook.png' class='pointer' style='margin-right: 16px;' onclick=\"urlmove('".$this->getParam( 'facebook_link' )."',1);\" />
		<img src='/images/instagram.png' class='pointer' onclick=\"urlmove('".$this->getParam( 'instagram_link' )."',1);\" />
		";
	}
	
	function getShareWidget( $linkVk, $linkFabebook, $linkToGoogle, $linkToTwitter, $title )
	{
		global $lang, $mysql, $utils, $main, $query;
		
		return "";
		
		return "
		<div class='shareBlock'>Поделиться: 
			<img src='https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."images/vk.png' onclick=\"shareToVK( '".$linkVk."', '".$title."' );\" />
			<img src='https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."images/facebook.png' onclick=\"shareToFaceBook( '".$linkFabebook."', '".$title."' );\" />
			<img src='https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."images/google.png' onclick=\"shareToGoogle( '".$linkToGoogle."', '".$title."' );\" />
			<img src='https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."images/twitter.png' onclick=\"shareToTwitter( '".$linkToTwitter."', '".$title."' );\" />
		</div>
		".$this->getJSShareBlock()."
		";
	}
	
	function getInstagramWidget()
	{
		return "<!-- INSTANSIVE WIDGET --><script src=\"//instansive.com/widget/js/instansive.js\"></script><iframe src=\"//instansive.com/widgets/3b160d1ee3e9e330879534fdbdfc53d2026fb63a.html\" id=\"instansive_3b160d1ee3\" name=\"instansive_3b160d1ee3\"  scrolling=\"no\" allowtransparency=\"true\" class=\"instansive-widget\" style=\"width: 100%; border: 0; overflow: hidden;\"></iframe>";
	}
	
	function getJSShareBlock()
	{
		return "
		<script>
			function shareToTwitter( link, title )
			{
				var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    			var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
    			
    			var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    			var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
    			
    			var w = 600, h = 600;
    			var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    			var top = ((height / 2) - (h / 2)) + dualScreenTop;
    			
				var newWindow = window.open( 'https://twitter.com/intent/tweet?url=' + encodeURIComponent( link ) + ( title && title != undefined ? '&text=' + title : '' ), '', 'width=' + w + ', height=' + h + ', top=' + top + ', left=' + left );
				
				if (window.focus)
        			newWindow.focus();
			}
			
			function shareToGoogle( link, title )
			{
				var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    			var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
    			
    			var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    			var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
    			
    			var w = 600, h = 600;
    			var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    			var top = ((height / 2) - (h / 2)) + dualScreenTop;
    			
				var newWindow = window.open( 'https://plus.google.com/share?url=' + encodeURIComponent( link ) + ( title && title != undefined ? '&text=' + title : '' ), '', 'width=' + w + ', height=' + h + ', top=' + top + ', left=' + left );
				
				if (window.focus)
        			newWindow.focus();
			}
			
			function shareToVK( link, title )
			{
				var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    			var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
    			
    			var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    			var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
    			
    			var w = 600, h = 600;
    			var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    			var top = ((height / 2) - (h / 2)) + dualScreenTop;
    			
				var newWindow = window.open( 'http://vk.com/share.php?url=' + encodeURIComponent( link ) + ( title && title != undefined ? '&text=' + title : '' ), '', 'width=' + w + ', height=' + h + ', top=' + top + ', left=' + left );
				
				if (window.focus)
        			newWindow.focus();
			}
			
			function shareToFaceBook( link, title )
			{
				var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    			var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
    			
    			var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    			var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
    			
    			var w = 600, h = 600;
    			var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    			var top = ((height / 2) - (h / 2)) + dualScreenTop;
    			
				var newWindow = window.open( 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent( link ) + ( title && title != undefined ? '&text=' + title : '' ), '', 'width=' + w + ', height=' + h + ', top=' + top + ', left=' + left );
				
				if (window.focus)
        			newWindow.focus();
			}
			
			function assignClickFuncToButton( func, but )
			{
				but.click( function() { func(); } );
			}
		</script>
		";
	}
}

?>