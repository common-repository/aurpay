( function ( $ ) {
	var connectionBanner = $( '.aurpay-usage-notice' ),
		connectionBannerDismiss = $( '.aurpay-usage-notice__dismiss' );

	// Move the banner below the WP Welcome notice on the dashboard
	$( window ).on( 'load', function () {
		wpWelcomeNotice.insertBefore( connectionBanner );
	} );

	// Dismiss the connection banner via AJAX
	connectionBannerDismiss.on( 'click', function () {
		$( connectionBanner ).hide()

	} );

} )( jQuery );
