# Audio player

Status: live · Prio: medium · Code: `includes/audio-player.php`, `templates/release-player.php`, `assets/audio-player.js`, `assets/audio-player.css`

**Value:** Customers can listen to the release on the product page.

Origin: `inc/yuc-template-functions.php:3-103`, `content-release.php:16-67`, `assets/javascript/audio-player.js`, `style.css:248-310` of the old child theme.

Plays the release on the product page. Two variants, decided by Pods fields on the product:

1. `release_listen` set → the linked `release` post provides `files` (MP3 attachments). Render an HTML5 `<audio>` element with a playlist, track title, previous/next controls.
2. No release, but `bandcamp_album_id` (numeric, e.g. `1474487848`) or `soundcloud_id` set → a "▶ Listen to {title}" trigger; on click, replace it with the Bandcamp or SoundCloud iframe (`single_track` decides between track and album/playlist embed). The iframe is not loaded before the click.

## Implementation

- Shortcode `[yuc_player]`, placed in the Single Product template in the Site Editor. Unlike the old hook `woocommerce_after_add_to_cart_button`, this also renders for out-of-stock products (WooCommerce renders no add-to-cart form there [Code]).
- Template `templates/release-player.php` for the playlist markup; iframe markup inline in the shortcode callback.
- `assets/audio-player.js` in plain JavaScript, scoped to the player container (not every `<audio>` on the page). The old off-by-one (last track skipped on auto-advance) is fixed; after the last track the playlist starts over. Assets enqueued on `wp_enqueue_scripts` for `is_product()`: enqueuing from the shortcode callback printed the CSS in the footer and the hidden playlist flashed on load.
- `assets/audio-player.css` from `style.css:248-310`; trigger and previous/next are `<button>` elements with a minimal reset.
- The external embed sits in a `<template>` next to the trigger button and is moved into the DOM on click.
- Pods access via `pods( 'product', $id )->field( … )`; the release's `files` field returns attachment arrays (`ID`, `post_title`). File URLs via `wp_get_attachment_url( $file['ID'] )`, not the stored `guid`, so they follow the site URL. Without Pods the shortcode renders nothing.

## Test on staging

A product with a release plays tracks, previous/next and auto-advance reach the last track; a product with only a Bandcamp ID shows the trigger and loads the iframe on click, no iframe request before; a product with neither renders nothing; an out-of-stock product still shows the player.
