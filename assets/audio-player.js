document.querySelectorAll( '.player-yuc' ).forEach( function ( player ) {
	var audio   = player.querySelector( 'audio' );
	var title   = player.querySelector( '.player-yuc-tracktitle' );
	var tracks  = player.querySelectorAll( '.player-yuc-playlist a' );
	var current = 0;

	function play( index ) {
		if ( index < 0 || index >= tracks.length ) {
			return;
		}
		current           = index;
		audio.src         = tracks[ index ].href;
		title.textContent = tracks[ index ].textContent;
		audio.play();
	}

	tracks.forEach( function ( link, index ) {
		link.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			play( index );
		} );
	} );

	player.querySelector( '.prev' ).addEventListener( 'click', function () {
		play( current - 1 );
	} );
	player.querySelector( '.next' ).addEventListener( 'click', function () {
		play( current + 1 );
	} );
	audio.addEventListener( 'ended', function () {
		play( ( current + 1 ) % tracks.length );
	} );
} );

document.querySelectorAll( '.external-player' ).forEach( function ( wrapper ) {
	wrapper.querySelector( '.external-player-trigger' ).addEventListener( 'click', function () {
		wrapper.replaceChildren( wrapper.querySelector( 'template' ).content );
	} );
} );
