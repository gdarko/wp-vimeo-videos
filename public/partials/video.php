<?php
/* @var string $vimeo_id */

$vimeo_id  = esc_attr( $vimeo_id );
$embed_url = esc_url( sprintf( 'https://player.vimeo.com/video/%s', $vimeo_id ) );
?>
<div class='dgv-embed-container'>
	<?php echo sprintf( "<iframe src='%s' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>", $embed_url ); ?>
</div>
