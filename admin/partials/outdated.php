<?php
/* @var WP_DGV_Api_Helper $vimeo_helper */
/* @var WP_DGV_Db_Helper $db_helper */
?>

<h2><?php _e( 'Outdated PHP version', 'wp-vimeo-videos' ); ?></h2>

<div id="wp-vimeo-videos" class="wvv-box">

    <div class="form-row">

        <p><?php _e( sprintf( __( 'The plugin requires at least PHP %s You currently have %s. Please contact the hosting provider to change your PHP version.', 'wp-vimeo-videos' ), WP_VIMEO_VIDEOS_MIN_PHP_VERSION, PHP_VERSION ) ); ?></p>

    </div>

</div>