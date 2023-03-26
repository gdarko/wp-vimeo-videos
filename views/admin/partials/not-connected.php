<?php
/* @var \Vimeify\Core\Plugin $plugin */
?>

<h2><?php _e( 'Invalid API Details', 'wp-vimeo-videos-pro' ); ?></h2>

<div class="wvv-box">

    <h3><?php _e('Oh, snap!', 'wp-vimeo-videos-pro'); ?></h3>

    <p><?php _e( 'Your Vimeo API credentials are missing or are invalid. Go to the <strong>Settings > Vimeo</strong> screen and enter valid vimeo details.', 'wp-vimeo-videos-pro' ); ?></p>

    <p>
		<?php echo sprintf(__('Please go to the %s and re-generate your access token with all the required scopes. If you need help check the link bellow.', 'wp-vimeo-videos-pro'), '<a target="_blank" href="https://developer.vimeo.com/">Vimeo developer portal</a>', '<strong>upload</strong>' ); ?>
    </p>

    <hr/>

    <p>
        <a href="<?php echo admin_url( 'upload.php?page=' . \Vimeify\Core\Backend\Ui::PAGE_VIMEO ); ?>"
           class="button"><?php _e( 'Back', 'wp-vimeo-videos-pro' ); ?></a>

        <a href="<?php echo admin_url( 'options-general.php?page=' .  \Vimeify\Core\Backend\Ui::PAGE_SETTINGS . '&action=settings' ); ?>"
           class="button-primary"><?php _e( 'Settings', 'wp-vimeo-videos-pro' ); ?></a>
    </p>

</div>