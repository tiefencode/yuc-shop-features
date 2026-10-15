<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'yuc_release_date', 'yuc_release_date_shortcode' );

function yuc_release_date_shortcode(): string {
	$release_date = get_post_meta( get_the_ID(), 'release_date', true );
	if ( '' === $release_date ) {
		return '';
	}

	$timezone = wp_timezone();
	$date     = DateTimeImmutable::createFromFormat( '!Y-m-d', $release_date, $timezone )
		?: DateTimeImmutable::createFromFormat( '!d.m.Y', $release_date, $timezone );
	if ( $date && $date < new DateTimeImmutable( 'today', $timezone ) ) {
		return '';
	}

	return wc_print_notice( 'Releasetermin / Release date: ' . esc_html( $release_date ), 'notice', [], true );
}
