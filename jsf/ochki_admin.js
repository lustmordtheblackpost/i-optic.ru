function urlmove( url, neww )
{
	if( neww != undefined && neww == 1 ) {
		window.open( url );
		return;
	}
	document.location = url;
}

function getWindowContent( link, windowtype, windowid )
{
	$.post( link,
  			{
  				external: '1',
  				getwindow: windowtype,
  				brt: navigator.appName
  			},
  			function( data )
			{
				if( !data ) {
					alert( 'Error while getting window content' );
					return;
				}
		
				$( '#' + windowid ).html( data );
				$( '#' + windowid ).dialog( 'open' ).dialog( 'moveToTop' );
			}
	);
}

function processSimpleAsyncReqForModuleAdmin( modulename, lt, add, inst )
{
	$.ajax({ type: 'POST', url: '/admin/getblock',
  		data : 'external=1&type=100&module=' + modulename + '&localtype=' + lt + add,
  		success: 
  			function ( data ) { if( inst ) { eval( inst ); } }
	});
}

function traslateCode( code, wheretoplace, wheretostore )
{
	$.get( "/trans_lang_code!" + code,
  			function( data )
			{
				$( 'html' ).css( 'cursor', '' );
				
				if( !data ) {
					alert( 'Ошибка перевода, либо фраза не может быть переведена' );
					return;
				}
		
				$( '#' + wheretostore ).html( data );
				$( '#' + wheretoplace ).attr( 'value', $( '#' + wheretostore ).html() ).attr( 'disabled', false );
			}
	);
}

function translatePhrase( input, output, phrase, wheretoplace, wheretostore )
{
	$.post( "/translate",
			{
  				intt: input,
  				outtt: output,
  				phrasetrans: phrase
  			},
  			function( data )
			{
				$( 'html' ).css( 'cursor', '' );
				
				if( !data ) {
					alert( 'Ошибка перевода, либо фраза не может быть переведена' );
					return;
				}
				
				$( '#' + wheretostore ).html( data );
				$( '#' + wheretoplace ).attr( 'value', $( '#' + wheretostore ).html() ).attr( 'disabled', false );
			}
	);
}

function putFloatForm( link, form_name )
{
	$.post( link, $( "#" + form_name ).serialize() );
}

function getGeneratedPassword( wheretoplace )
{
	$.get( "/generate_new_password",
  			function( data )
			{
				$( '#' + wheretoplace ).attr( 'value', data );
			}
	);
}