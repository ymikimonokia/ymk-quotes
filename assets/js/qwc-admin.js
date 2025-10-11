jQuery( document ).ready( function() {
	
	jQuery( document ).on( 'click', '#qwc_quote_complete', function() {
		
		// update the DB quote status
		var data = {
			order_id: qwc_params.order_id,
			status: 'quote-complete',
			action: 'qwc_update_status'
		};
		
		jQuery.post( qwc_params.ajax_url, data, function( response ) {
			// reload the page
			location.reload( true );
		});
	});
	
	jQuery( document ).on( 'click', '#qwc_send_quote', function() {
		// send an email
		var data = {
			order_id: qwc_params.order_id,
			action: 'qwc_send_quote'
		};
		
		jQuery.post( qwc_params.ajax_url, data, function( response ) {
			if ( 'quote-sent' === response ) {
				jQuery( '#qwc_msg' ).html( qwc_params.email_msg );
				jQuery( '#qwc_msg' ).attr( 'display', 'block' ).fadeOut( 5000 );
			}
		});
	});
});


jQuery( document ).on( 'click', '#qwc_send_quote', function() {
		
		console.log('Send Quote button clicked'); // Debug
		
		// send an email
		var data = {
			order_id: qwc_params.order_id,
			action: 'qwc_send_quote'
		};
		
		jQuery.post( qwc_params.ajax_url, data, function( response ) {
			console.log('Send quote response:', response); // Debug
			
			if ( 'quote-sent' === response.trim() ) {
				jQuery( '#qwc_msg' ).html( qwc_params.email_msg );
				jQuery( '#qwc_msg' ).css( 'display', 'inline-block' ).fadeOut( 5000 );
			}
		});
	});
});