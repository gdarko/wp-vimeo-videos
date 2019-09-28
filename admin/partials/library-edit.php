<?php
/* @var WP_DGV_Api_Helper $vimeo_helper */
/* @var WP_DGV_Db_Helper $db_helper */
?>

<h2><?php echo get_the_title( $_GET['id'] ); ?></h2>

<?php
$vimeo_id = $db_helper->get_vimeo_id( $_GET['id'] );
?>

<div class="dgv-preview-wrap">
    <div class="dgv-preview">
		<?php echo do_shortcode( '[vimeo_video id="' . $vimeo_id . '"]' ); ?>
    </div>
</div>