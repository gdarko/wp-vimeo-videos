<?php
/* @var WP_DGV_Api_Helper $vimeo_helper */
/* @var WP_DGV_Db_Helper $db_helper */
?>

<h2><?php _e( 'Upload to Vimeo', 'wp-vimeo-videos' ); ?></h2>

<div class="wvv-box">
    <h3><?php _e('Oh, snap!', 'wp-vimeo-videos'); ?></h3>
    <p>
		<?php _e('Sorry. Looks like you are not allowed to upload videos to vimeo.', 'wp-vimeo-videos'); ?>
    </p>
	<?php if ( is_array( $vimeo_helper->scopes ) && count( $vimeo_helper->scopes ) > 0 ): ?>

        <ul>
            <li><strong><?php _e('Current scopes', 'wp-vimeo-videos'); ?></strong>: <?php echo esc_html(implode( ', ', $vimeo_helper->scopes )); ?></li>
			<?php if(!empty($vimeo_helper->scopes_missing)): ?>
                <li><strong><?php _e('Missing scopes', 'wp-vimeo-videos'); ?></strong>: <?php echo esc_html(implode( ', ', $vimeo_helper->scopes_missing )); ?></li>
			<?php endif; ?>
        </ul>

	<?php endif; ?>
    <p>
		<?php echo sprintf(__('Please go to the %s and re-generate your access token with all the required scopes. If you need help check the link bellow.', 'wp-vimeo-videos'), '<a target="_blank" href="https://developer.vimeo.com/">Vimeo developer portal</a>', '<strong>upload</strong>' ); ?>
    </p>

    <hr/>

    <p>
        <a target="_blank" href="<?php echo esc_url(wvv_get_guide_url()); ?>" class="button-primary"><?php _e('Documentation', 'wp-vimeo-videos'); ?></a>
    </p>
</div>
