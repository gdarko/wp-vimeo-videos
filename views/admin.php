<div class="dgv-wrap">
	<?php if ( isset( $_GET['action'] ) && $_GET['action'] === 'new' ): ?>
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
            <div class="form-row">
                <label for="privacy_view"><?php _e( 'Privacy', 'dg-vimeo-upload' ); ?></label>
                <select name="privacy_view" id="privacy_view">
                    <option selected value="anybody"><?php _e( 'Anybody', 'dg-vimeo-upload' ); ?></option>
                    <option value="contacts"><?php _e( 'Contacts', 'dg-vimeo-upload' ); ?></option>
                    <option value="disable"><?php _e( 'Disable', 'dg-vimeo-upload' ); ?></option>
                    <option value="nobody"><?php _e( 'Nobody', 'dg-vimeo-upload' ); ?></option>
                    <option value="users"><?php _e( 'Users', 'dg-vimeo-upload' ); ?></option>
                </select>
            </div>
            <div class="form-row">
                <a href="<?php echo admin_url( 'upload.php?page=' . DGV_Backend::PAGE_HANDLE ); ?>" onclick="return confirm('Are you sure you want to leave this page? This action can not be reverted');" class="button">View library</a>
                <input type="submit" class="button-primary" name="vimeo_upload" value="Upload">
            </div>
        </form>
	<?php elseif ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' && isset( $_GET['id'] ) ): ?>
        <h2><?php echo get_the_title( $_GET['id'] ); ?></h2>
		<?php
		$vimeo_id = dgv_get_vimeo_id( $_GET['id'] );
		echo '<div class="dgv-preview-wrap">';
		echo do_shortcode( '[vimeo_video id="' . $vimeo_id . '"]' );
		echo '</div>';
		?>
	<?php elseif ( isset( $_GET['action'] ) && $_GET['action'] === 'settings' ): ?>
        <h2><?php _e('Vimeo Settings'); ?></h2>
        <form id="dg-vimeo-settings" class="dg-vimeo-form" method="post" action="">
            <div class="form-row">
                <label for="dgv_client_id"><?php _e( 'Client ID', 'dg-vimeo-upload' ); ?></label>
                <input type="text" name="dgv_client_id" id="dgv_client_id" value="<?php echo get_option('dgv_client_id'); ?>">
            </div>
            <div class="form-row">
                <label for="dgv_client_secret"><?php _e( 'Client Secret', 'dg-vimeo-upload' ); ?></label>
                <input type="text" name="dgv_client_secret" id="dgv_client_secret" value="<?php echo get_option('dgv_client_secret'); ?>">
            </div>
            <div class="form-row">
                <label for="dgv_access_token"><?php _e( 'Access Token', 'dg-vimeo-upload' ); ?></label>
                <input type="text" name="dgv_access_token" id="dgv_access_token" value="<?php echo get_option('dgv_access_token'); ?>">
            </div>
            <div class="form-row">
                <input type="submit" class="button-primary" name="dgv_settings_save" value="Save">
            </div>
        </form>
	<?php elseif ( ! isset( $_GET['action'] ) ): ?>
        <h2><?php _e( 'Vimeo Videos', 'dg-vimeo-videos' ); ?>
            <a href="<?php echo admin_url( 'upload.php?page=' . DGV_Backend::PAGE_HANDLE . '&action=new' ); ?>" class="page-title-action"><?php _e( 'Upload new', 'dg-vimeo-videos' ); ?></a>
            <a href="<?php echo admin_url( 'upload.php?page=' . DGV_Backend::PAGE_HANDLE . '&action=settings' ); ?>" class="page-title-action"><?php _e( 'Settings', 'dg-vimeo-videos' ); ?></a>
        </h2>
        <form method="post">
            <input type="hidden" name="page" value="ttest_list_table">
			<?php
			$list_table = new DGV_Videos_Table();
			$list_table->prepare_items();
			//$list_table->search_box( 'search', 'search_id' );
			$list_table->display();
			?>
        </form>
	<?php endif; ?>
</div>