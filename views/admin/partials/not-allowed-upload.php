<?php
/* @var \Vimeify\Core\Plugin $plugin */
?>

<h2><?php _e( 'Upload to Vimeo', 'wp-vimeo-videos-pro' ); ?></h2>

<div class="wvv-box">
    <h3><?php _e('Oh, snap!', 'wp-vimeo-videos-pro'); ?></h3>
    <p>
	    <?php _e('Sorry. Looks like you are not allowed to upload videos to vimeo.', 'wp-vimeo-videos-pro'); ?>
    </p>
    <?php if ( is_array( $plugin->system()->vimeo()->scopes ) && count( $plugin->system()->vimeo()->scopes ) > 0 ): ?>

    <ul>
        <li><strong><?php _e('Current scopes', 'wp-vimeo-videos-pro'); ?></strong>: <?php echo implode( ', ', $plugin->system()->vimeo()->scopes ); ?></li>
        <?php if(!empty($plugin->system()->vimeo()->scopes_missing)): ?>
         <li><strong><?php _e('Missing scopes', 'wp-vimeo-videos-pro'); ?></strong>: <?php echo implode( ', ', $plugin->system()->vimeo()->scopes_missing ); ?></li>
        <?php endif; ?>
    </ul>

    <?php endif; ?>
    <p>
        <?php echo sprintf(__('Please go to the %s and re-generate your access token with all the required scopes. If you need help check the link bellow.', 'wp-vimeo-videos-pro'), '<a target="_blank" href="https://developer.vimeo.com/">Vimeo developer portal</a>', '<strong>upload</strong>' ); ?>
    </p>

    <hr/>
    
    <p>
        <a target="_blank" href="<?php echo $plugin->documentation_url(); ?>" class="button-primary"><?php _e('Documentation', 'wp-vimeo-videos-pro'); ?></a>
    </p>
</div>
