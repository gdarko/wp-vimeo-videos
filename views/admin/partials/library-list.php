<?php
/* @var \Vimeify\Core\Plugin $plugin */
?>

<h2 class="dgv-skip-margins"><?php _e( 'Vimeo Videos', 'wp-vimeo-videos-pro' ); ?>

    <a href="<?php echo esc_url( admin_url( 'upload.php?page=' . \Vimeify\Core\Backend\Ui::PAGE_VIMEO . '&action=new' ) ); ?>" class="page-title-action"><?php _e( 'Upload new', 'wp-vimeo-videos-pro' ); ?></a>

	<?php if ( current_user_can( 'manage_options' ) ): ?>
        <a href="<?php echo esc_url( $plugin->settings_url() ); ?>" class="page-title-action" title="<?php _e( 'Settings', 'wp-vimeo-videos-pro' ); ?>">
            <span class="dashicons dashicons-admin-tools"></span>
        </a>

	<?php endif; ?>

    <a id="dgv-vimeo-stats" href="#" class="page-title-action" title="<?php _e( 'Statistics', 'wp-vimeo-videos-pro' ); ?>">
        <span class="dashicons dashicons-chart-bar"></span>
    </a>

</h2>

<form method="get">

    <input type="hidden" name="page" value="<?php echo esc_attr( \Vimeify\Core\Backend\Ui::PAGE_VIMEO ); ?>">

	<?php

	$list_table = new \Vimeify\Core\Backend\VideosTable( $plugin );

	$list_table->prepare_items();

	$list_table->search_box( __( 'Search', 'wp-vimeo-videos-pro' ), 'search_id' );

	$list_table->views();

	$list_table->display();

	?>

</form>

