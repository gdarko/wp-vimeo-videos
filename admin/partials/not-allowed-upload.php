<?php
/* @var WP_DGV_Api_Helper $vimeo_helper */
/* @var WP_DGV_Db_Helper $db_helper */
?>

<h2><?php _e( 'Upload to Vimeo', 'wp-vimeo-videos' ); ?></h2>

<div class="wvv-box">
    <p>
	    <?php _e('Sorry. Looks like you are not allowed to upload videos to vimeo.', 'wp-vimeo-uploads'); ?>
    </p>
    <p>
		<?php if ( is_array( $vimeo_helper->scopes ) && count( $vimeo_helper->scopes ) > 0 ): ?>
            <?php _e('Your current user scopes on vimeo are', 'wp-vimeo-uploads'); ?>: <?php echo implode( ', ', $vimeo_helper->scopes ); ?>
		<?php endif; ?>
    </p>
    <p>
        <?php echo sprintf(__('Simply go to the %s and request %s scope to your application.', 'wp-vimeo-videos'), '<a href="https://developer.vimeo.com/">Vimeo developer portal</a>', '<strong>upload</strong>' ); ?>
    </p>
</div>
