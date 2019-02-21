var isMobile = false; 

if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
    || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) isMobile = true;
    
function ge( el )
{
	return document.getElementById( el );
}

function snv( el, val )
{
	var e = ge( el );
	if( e )
		e.value = val;
}

function urlmove( url, neww )
{
	if( neww != undefined && neww == 1 ) {
		window.open( url );
		return;
	}
	document.location = url;
}

function checkStringForExistOfSymbols( str, smb )
{
	var sl = str.length;
	var scl = smb.length;
	for( a = 0; a < sl; a++ ) {
		var c = str.charAt( a );
		var here = false;
		for( i = 0; i < scl; i++ ) {
			if( c == smb.charAt( i ) ) {
				here = true;
				break;
			}
		}
		if( !here ) {
			return false;
		}
	}
	
	return true;	
}

function getBounds( obj )
{
	var w=obj.offsetWidth;
	var h=obj.offsetHeight;
	var x=y=0;
	while(obj){
		x+=obj.offsetLeft;
		y+=obj.offsetTop;
		obj=obj.offsetParent;
	}
	return{x:x,y:y,width:w,height:h};
}

function processSimpleAsyncReqForModule( modulename, lt, add, inst )
{
	$.ajax({ type: 'POST', url: '/getblock',
  		data : 'ext=1&type=100&module=' + modulename + '&localtype=' + lt + add,
  		success: 
  			function ( data ) { if( inst ) { eval( inst ); } }
	});
}

function processSimpleAsyncReqForModuleWithFormdata( modulename, lt, image, leftc, topc, width, height, deg, inst )
{
	var datas = new FormData();
	datas.append( 'ext', 1 );
	datas.append( 'type', 100 );
	datas.append( 'module', modulename );
	datas.append( 'localtype', lt );
	datas.append( 'left', leftc );
	datas.append( 'top', topc );
	datas.append( 'width', width );
	datas.append( 'height', height );
	if( deg != undefined )
		datas.append( 'deg', deg );
	if( image )
		datas.append( 'image', image );
	$.ajax({ type: 'POST', url: '/getblock',
  		data : datas,
  		contentType: false,
  		processData: false,
  		cache: false,
  		success: 
  			function ( data ) { if( inst ) { eval( inst ); } }
	});
}

function getCurrentMouseCoords( event )
{
	e = event || window.event;

	if( e.pageX == null && e.clientX != null ) {
        var html = document.documentElement;
        var body = document.body;

        e.pageX = e.clientX + (html && html.scrollLeft || body && body.scrollLeft || 0) - (html.clientLeft || 0);
        e.pageY = e.clientY + (html && html.scrollTop || body && body.scrollTop || 0) - (html.clientTop || 0);
    }
    
    return e;
}

function digitsToBold( backup )
{
	len = backup.length;
	nnew = "";
	
	bUsed	=	0;
	slashB	=	0;
	start	=	0;
	
	for( i = 0; i < len; i++ ) {
		if(backup.charCodeAt( i ) >= 48 &&  backup.charCodeAt( i ) <= 57 ) {
			start = 1;
			if( bUsed != 1 ) {
				nnew = nnew + "<b>";
				bUsed = 1;
			}
		} else {
			if( bUsed == 1 && start == 1 )
				slashB = 1;
			start = 0;
		}
		
		if( slashB == 1 ) {
			nnew = nnew + "</b>";
			slashB = 0;
			bUsed = 0;
		}
		nnew = nnew + backup[i];
	}
	
	if( start == 1 )
		nnew = nnew + "</b>";
	
	return nnew;
}

function mtRand( min, max )
{
	return Math.floor( Math.random() * ( max - min + 1 ) ) + min;
}

function mkPass( len )
{
	var len = len ? len : 14;
	var pass = '';
	var rnd = 0;
	var c = '';
	for( i = 0; i < len; i++ ) {
		rnd = mtRand( 0, 2 );
		if( rnd == 0 )
			c = String.fromCharCode( mtRand( 48, 57 ) );
		else if( rnd == 1 )
			c = String.fromCharCode( mtRand( 65, 90 ) );
		else if( rnd == 2 )
			c = String.fromCharCode( mtRand( 97, 122 ) );
		pass += c;
	}
	return pass;
}

function getTextBlock( btype, el )
{
	$.post( '/getblock',
  		{
  			ext: '1',
	    	type: btype,
	    	toplace: el
  		},
  		function ( data )
  		{
  			if( data == null || data == 'Undefined query states' )
  				return;
  			
  			var arr = data.split( '##' );
			$( '#' + arr[0] ).html( arr[1] );
			if( arr.length > 2 && arr[2] != 'undefined' )
				$( '#' + arr[2] ).attr( 'value', arr[3] );
  		}
	);
}

function getTextBlockForModule( modulename, lt, el, add )
{
	$.post( '/getblock',
  		{
  			ext: '1',
	    	type: '100',
	    	module: modulename,
	    	localtype: lt,
	    	additional: add
  		},
  		function ( data )
  		{
  			if( data == null || data == 'Undefined query states' )
  				return;
  			
  			$( '#' + el ).html( data );
  		}
	);
}

function completeArr( arr, spliter )
{
	var nv = '';
	for( var a = 0; a < arr.length; a++ ) {
		nv += (  nv != '' ? "^^" : "" ) + arr[a];
	}
	return nv;
}

function strrev( s )
{
	var ret = '', i = 0;

	for( i = s.length - 1; i >= 0; i-- )
	   ret += s.charAt(i);

	return ret;
}

function processKeyPress( event )
{
	var code = 0;

	if( event.keyCode != undefined )
		code = event.keyCode;
	else if( event.which != undefined )
		code = event.which;
	else if( event.charCode != undefined )
		code = event.charCode;
				
	return code;
}

function getDigits( backup )
{
	var len = backup.length;
	var nnew = "";

	for( i = 0; i < len; i++ ) {
		if( ( backup.charCodeAt( i ) >= 48 && backup.charCodeAt( i ) <= 57 ) || backup.charCodeAt( i ) == 46 ) {
			nnew += backup.charAt( i );
		}
	}
	
	return nnew;
}

function getCookie(name)
{
    var matches = document.cookie.match(new RegExp( "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)" ));
    return matches ? decodeURIComponent(matches[1]) : undefined
}

function setCookie(name, value, props)
{
    props = props || {}
    var exp = props.expires
    if (typeof exp == "number" && exp) {
        var d = new Date()
        d.setTime(d.getTime() + exp*1000)
        exp = props.expires = d
    }
    if(exp && exp.toUTCString) { props.expires = exp.toUTCString() }
 
    value = encodeURIComponent(value)
    var updatedCookie = name + "=" + value
    for(var propName in props){
        updatedCookie += "; " + propName
        var propValue = props[propName]
        if(propValue !== true){ updatedCookie += "=" + propValue }
    }
        
    document.cookie = updatedCookie
 
}
 
function deleteCookie(name)
{
    setCookie(name, null, { expires: -1 })
}

function moveToElem( elem ) 
{
	$( 'body, html' ).animate( { scrollTop: elem.position().top }, 400 );
}

function urlencode( str )
{
	str = (str + '').toString();

	return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+').replace(/\\/g, '');
}