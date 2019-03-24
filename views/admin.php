<div class="dgv-wrap">
	<?php if ( ! DGV_Plugin::php_version_satisfied() ) { ?>
        <h2><?php _e( 'Outdated PHP version', 'dg-vimeo-upload' ); ?></h2>
        <div id="dg-vimeo-upload" class="dg-vimeo-form">
            <div class="form-row">
                <p><?php _e( sprintf( __( 'The plugin requires at least PHP 7.1. You currently have %s. Please contact the hosting provider to change your PHP version.', 'wp-vimeo-videos' ), PHP_VERSION ) ); ?></p>
            </div>
        </div>
	<?php } else { ?>
		<?php if ( isset( $_GET['action'] ) && $_GET['action'] === 'new' ) { ?>
			<?php
			$is_api_connected = dgv_check_api_connection();
			$is_site_accessible = dgv_is_wp_accessible_from_public();
			?>
			<?php if ( false !== $is_api_connected && $is_site_accessible  ) { ?>
                <h2><?php _e( 'Upload to Vimeo', 'dg-vimeo-upload' ); ?></h2>
                <form id="dg-vimeo-upload" class="dg-vimeo-form" enctype="multipart/form-data" method="post" action="">
                    <div class="form-row">
                        <label for="title"><?php _e( 'Title', 'dg-vimeo-upload' ); ?></label>
                        <input type="text" name="title" id="title">
                    </div>
                    <div class="form-row">
                        <label for="description"><?php _e( 'Description', 'dg-vimeo-upload' ); ?></label>
                        <textarea name="description" id="description"></textarea>
                    </div>
                    <div class="form-row">
                        <label for="vimeo_video"><?php _e( 'Video File', 'dg-vimeo-upload' ); ?></label>
                        <input type="file" name="vimeo_video" id="vimeo_video">
                    </div>
                    <div class="form-row with-border">
                        <a href="<?php echo admin_url( 'upload.php?page=' . DGV_Plugin::PAGE_HANDLE ); ?>" onclick="return confirm('Are you sure you want to leave this page? This action can not be reverted');" class="button">View library</a>
                        <input type="submit" class="button-primary" name="vimeo_upload" value="Upload">
                    </div>
                </form>
			<?php } else { ?>
				<?php if(!$is_site_accessible): ?>
                    <h2><?php _e( 'Your site is not public', 'dg-vimeo-upload' ); ?></h2>
                    <form id="dg-vimeo-upload" class="dg-vimeo-form" enctype="multipart/form-data" method="post" action="">
                        <div class="form-row">
                            <p><?php _e( 'Blah! It looks like you are trying to run the plugin from localhost. At this time the plugin will only work on sites hosted to public.', 'wp-vimeo-uploads' ); ?></p>
                        </div>
                        <div class="form-row with-border">
                            <a href="<?php echo admin_url( 'upload.php?page=' . DGV_Plugin::PAGE_HANDLE ); ?>" class="button"><?php _e( 'Back', 'wp-vimeo-uploads' ); ?></a>
                        </div>
                    </form>
				<?php else: ?>
                    <h2><?php _e( 'Invalid API Details', 'dg-vimeo-upload' ); ?></h2>
                    <form id="dg-vimeo-upload" class="dg-vimeo-form" enctype="multipart/form-data" method="post" action="">
                        <div class="form-row">
                            <p><?php _e( 'Blah! Your api details are missing or are invalid. Go to the Settings screen and enter valid vimeo details.', 'wp-vimeo-uploads' ); ?></p>
                        </div>
                        <div class="form-row with-border">
                            <a href="<?php echo admin_url( 'upload.php?page=' . DGV_Plugin::PAGE_HANDLE ); ?>" class="button"><?php _e( 'Back', 'wp-vimeo-uploads' ); ?></a>
                            <a href="<?php echo admin_url( 'upload.php?page=' . DGV_Plugin::PAGE_HANDLE . '&action=settings' ); ?>" class="button-primary"><?php _e( 'Go to Settings', 'wp-vimeo-uploads' ); ?></a>
                        </div>
                    </form>
				<?php endif; ?>
			<?php } ?>
		<?php } elseif ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' && isset( $_GET['id'] ) ) { ?>
            <h2><?php echo get_the_title( $_GET['id'] ); ?></h2>
			<?php
			$vimeo_id = dgv_get_vimeo_id( $_GET['id'] );
			echo '<div class="dgv-preview-wrap">';
			echo do_shortcode( '[vimeo_video id="' . $vimeo_id . '"]' );
			echo '</div>';
			?>
		<?php } elseif ( isset( $_GET['action'] ) && $_GET['action'] === 'settings' ) { ?>
            <h2><?php _e( 'Vimeo Settings' ); ?></h2>
            <form id="dg-vimeo-settings" class="dg-vimeo-form" method="post" action="">
                <div class="form-row">
                    <label for="dgv_client_id"><?php _e( 'Client ID', 'dg-vimeo-upload' ); ?></label>
                    <input type="text" name="dgv_client_id" id="dgv_client_id" value="<?php echo get_option( 'dgv_client_id' ); ?>">
                </div>
                <div class="form-row">
                    <label for="dgv_client_secret"><?php _e( 'Client Secret', 'dg-vimeo-upload' ); ?></label>
                    <input type="text" name="dgv_client_secret" id="dgv_client_secret" value="<?php echo get_option( 'dgv_client_secret' ); ?>">
                </div>
                <div class="form-row">
                    <label for="dgv_access_token"><?php _e( 'Access Token', 'dg-vimeo-upload' ); ?></label>
                    <input type="text" name="dgv_access_token" id="dgv_access_token" value="<?php echo get_option( 'dgv_access_token' ); ?>">
                </div>
                <div class="form-row with-border">
                    <a href="<?php echo admin_url( 'upload.php?page=' . DGV_Plugin::PAGE_HANDLE ); ?>" class="button"><?php _e( 'Back', 'wp-vimeo-videos' ); ?></a>
                    <input type="submit" class="button-primary" name="dgv_settings_save" value="Save">
                </div>
            </form>
		<?php } elseif ( ! isset( $_GET['action'] ) ) { ?>
            <h2><?php _e( 'Vimeo Videos', 'wp-vimeo-videos' ); ?>
                <a href="<?php echo admin_url( 'upload.php?page=' . DGV_Plugin::PAGE_HANDLE . '&action=new' ); ?>" class="page-title-action"><?php _e( 'Upload new', 'wp-vimeo-videos' ); ?></a>
                <a href="<?php echo admin_url( 'upload.php?page=' . DGV_Plugin::PAGE_HANDLE . '&action=settings' ); ?>" class="page-title-action"><?php _e( 'Settings', 'wp-vimeo-videos' ); ?></a>
            </h2>
            <form method="post">
                <input type="hidden" name="page" value="ttest_list_table">
				<?php
				$list_table = new DGV_Videos_Table();
				$list_table->prepare_items();
				//$list_table->search_box( 'search', 'search_id' ); //TODO
				$list_table->display();
				?>
            </form>
		<?php } ?>
	<?php } ?>
</div>