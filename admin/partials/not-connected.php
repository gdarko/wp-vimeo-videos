<?php
/* @var WP_DGV_Api_Helper $vimeo_helper */
/* @var WP_DGV_Db_Helper $db_helper */
?>

<h2><?php _e( 'Invalid API Details', 'wp-vimeo-videos' ); ?></h2>

<form id="wp-vimeo-videos" class="wvv-box" enctype="multipart/form-data" method="post" action="">

	<div class="form-row">

		<p><?php _e( 'Blah! Your api details are missing or are invalid. Go to the Settings screen and enter valid vimeo details.', 'wp-vimeo-uploads' ); ?></p>

	</div>

	<div class="form-row with-border">

		<a href="<?php echo admin_url( 'upload.php?page=' . WP_DGV_Admin::PAGE_VIMEO ); ?>"
		   class="button"><?php _e( 'Back', 'wp-vimeo-uploads' ); ?></a>

		<a href="<?php echo admin_url( 'options-general.php?page=' .  WP_DGV_Admin::PAGE_SETTINGS . '&action=settings' ); ?>"
		   class="button-primary"><?php _e( 'Go to Settings', 'wp-vimeo-uploads' ); ?></a>

	</div>

</form>