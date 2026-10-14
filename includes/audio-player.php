<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'yuc_player', 'yuc_player_shortcode' );

function yuc_player_shortcode(): string {
	if ( ! function_exists( 'pods' ) ) {
		return '';
	}

	$pod = pods( 'product', get_the_ID() );
	if ( ! $pod ) {
		return '';
	}

	$release = $pod->field( 'release_listen' );
	if ( $release ) {
		$files = pods( 'release', $release['ID'] )->field( 'files' );
		if ( empty( $files[0]['ID'] ) ) {
			return '';
		}

		ob_start();
		include YUC_SHOP_FEATURES_DIR . 'templates/release-player.php';
		return ob_get_clean();
	}

	$bandcamp_id   = $pod->field( 'bandcamp_album_id' );
	$soundcloud_id = $pod->field( 'soundcloud_id' );
	$is_track      = (bool) $pod->field( 'single_track' );

	if ( $bandcamp_id ) {
		$embed = sprintf(
			'<div class="player-bandcamp"><iframe style="border: 0; width: 100%%; height: 120px;" src="%s"></iframe></div>',
			esc_url( 'https://bandcamp.com/EmbeddedPlayer/' . ( $is_track ? 'track=' : 'album=' ) . $bandcamp_id . '/size=large/bgcol=333333/linkcol=ffffff/tracklist=false/artwork=none/transparent=true/' )
		);
	} elseif ( $soundcloud_id ) {
		$embed = sprintf(
			'<div class="player-soundcloud"><iframe width="100%%" height="%s" scrolling="no" frameborder="no" allow="autoplay" src="%s"></iframe></div>',
			$is_track ? 'auto' : '340',
			esc_url( 'https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/' . ( $is_track ? 'tracks/' : 'playlists/' ) . $soundcloud_id . '&color=%23292529&auto_play=true&hide_related=false&show_comments=false&show_user=false&show_reposts=false&show_teaser=false' )
		);
	} else {
		return '';
	}

	return '<div class="external-player">'
		. '<button type="button" class="external-player-trigger">&#9654; Listen to ' . esc_html( get_the_title() ) . '</button>'
		. '<template>' . $embed . '</template>'
		. '</div>';
}

add_action( 'wp_enqueue_scripts', 'yuc_player_enqueue_assets' );

function yuc_player_enqueue_assets(): void {
	if ( ! is_product() ) {
		return;
	}

	wp_enqueue_style( 'yuc-audio-player', YUC_SHOP_FEATURES_URL . 'assets/audio-player.css', [], YUC_SHOP_FEATURES_VERSION );
	wp_enqueue_script( 'yuc-audio-player', YUC_SHOP_FEATURES_URL . 'assets/audio-player.js', [], YUC_SHOP_FEATURES_VERSION, true );
}
