jQuery( function ( $ ) {

	getLocation();

	$( '.coverks-door-open' ).click( function ( e ) {

		e.preventDefault();

		var $text = this;
		$( $text ).html( 'Opening...' );

		var latitude = $( 'a.coverks-door-open' ).attr( 'data-coverks-latitude' );
		var longitude = $( 'a.coverks-door-open' ).attr( 'data-coverks-longitude' );

		$.ajax( {
			type: "GET",
			cache: false,
			url: "https://app.coverks.no/wp-json/coverks/v1/openclose/",
			data: { "latitude": latitude, "longitude": longitude  },
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
			},
			error: function () {
				console.log( 'There was an error. Please try again.' );
				$( this ).html( 'Error. Please try again.' );
			},

		} );
		
		
		setTimeout(function(){ 
		
			$( $text ).html( 'Locking Doors...' );
			
			setTimeout(function(){ 
				$( $text ).html( 'Open Doors' );
			}, 1000);
			
		}, 3000);

		return false;

	} );

	$( '.coverks-door-unlock' ).click( function ( e ) {

		e.preventDefault();
		
		var $text = this;
		$( $text ).html( 'Opening...' );

		var coverksDoorID = $( this ).data( 'coverks-door-id' );

		$.ajax( {
			type: "GET",
			cache: false,
			url: "https://app.coverks.no/wp-json/coverks/v1/open/" + coverksDoorID,
			data: coverksDoorID,
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
			},
			error: function () {
				console.log( 'There was an error. Please try again.' );
			},

		} );
		
		setTimeout(function(){ 
		
			$( $text ).html( 'Open' );
			
		}, 3000);


		return false;

	} );

	$( '.coverks-door-lock' ).click( function ( e ) {

		e.preventDefault();
		
		var $text = this;
		$( $text ).html( 'Locking...' );

		var coverksDoorID = $( this ).data( 'coverks-door-id' );

		$.ajax( {
			type: "GET",
			cache: false,
			url: "https://app.coverks.no/wp-json/coverks/v1/close/" + coverksDoorID,
			data: coverksDoorID,
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
			},
			error: function () {
				console.log( 'There was an error. Please try again.' );
			},

		} );
		
		setTimeout(function(){ 
		
			$( $text ).html( 'Lock' );
			
		}, 3000);

		return false;

	} );

	$( '.coverks-light-on' ).click( function ( e ) {

		e.preventDefault();

		var $text = this;
		$( $text ).html( 'Locking...' );

		var coverksLightID = $( this ).data( 'coverks-light-id' );

		$.ajax( {
			type: "GET",
			cache: false,
			url: "https://app.coverks.no/wp-json/coverks/v1/lightin/" + coverksLightID,
			data: coverksLightID,
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
			},
			error: function () {
				console.log( 'There was an error. Please try again.' );
			},

		} );

		setTimeout(function(){

			$( $text ).html( 'Lock' );

		}, 3000);

		return false;

	} );

	$( '.coverks-light-off' ).click( function ( e ) {

		e.preventDefault();

		var $text = this;
		$( $text ).html( 'Locking...' );

		var coverksLightID = $( this ).data( 'coverks-light-id' );

		$.ajax( {
			type: "GET",
			cache: false,
			url: "https://app.coverks.no/wp-json/coverks/v1/lightoff/" + coverksLightID,
			data: coverksLightID,
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
			},
			error: function () {
				console.log( 'There was an error. Please try again.' );
			},

		} );

		setTimeout(function(){

			$( $text ).html( 'Lock' );

		}, 3000);

		return false;

	} );

	function getLocation() {
		
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(savePosition);
		}
		
	}
	
	function savePosition( position ) {
		
		var latitude = position.coords.latitude;
		var longitude = position.coords.longitude;
		
		$( 'a.coverks-door-open' ).attr( 'data-coverks-latitude', latitude );
		$( 'a.coverks-door-open' ).attr( 'data-coverks-longitude', longitude );
		
	}

} );
