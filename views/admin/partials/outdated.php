<?php
/* @var \Vimeify\Core\Plugin $plugin */
?>

<h2><?php _e( 'Outdated PHP version', 'wp-vimeo-videos-pro' ); ?></h2>

<div id="wp-vimeo-videos" class="wvv-box">

    <div class="form-row">

        <p><?php _e( sprintf( __( 'The plugin requires at least PHP %s You currently have %s. Please contact the hosting provider to change your PHP version.', 'wp-vimeo-videos-pro' ), $plugin->minimum_php_version(), PHP_VERSION ) ); ?></p>

    </div>

</div>