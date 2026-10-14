<?php
// Expects $files: attachment arrays from the Pods "files" field of the release.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="player-yuc">
	<div class="player-yuc-tracktitle"><?php echo esc_html( trim( $files[0]['post_title'] ) ); ?></div>

	<div class="player-yuc-hud">
		<audio preload="auto" controls controlsList="nodownload" src="<?php echo esc_url( wp_get_attachment_url( $files[0]['ID'] ) ); ?>"></audio>

		<div class="player-yuc-controls">
			<button type="button" class="prev" aria-label="Previous track">&laquo;</button>
			<button type="button" class="next" aria-label="Next track">&raquo;</button>
		</div>
	</div>

	<ol class="player-yuc-playlist">
		<?php foreach ( $files as $file ) : ?>
			<li><a href="<?php echo esc_url( wp_get_attachment_url( $file['ID'] ) ); ?>"><?php echo esc_html( trim( $file['post_title'] ) ); ?></a></li>
		<?php endforeach; ?>
	</ol>
</div>
