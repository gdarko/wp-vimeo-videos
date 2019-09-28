<?php
/* @var WP_DGV_Api_Helper $vimeo_helper */
/* @var WP_DGV_Db_Helper $db_helper */
?>

<div class="wrap">
    <h2 class="wvv-mb-20"><?php _e( 'WP Vimeo Settings', 'wp-vimeo-videos' ); ?></h2>

    <div class="wvv-row">
        <div class="wvv-col-60 wvv-col">
            <form id="dg-vimeo-settings" class="wvv-box" method="post" action="">

                <div class="form-row vimeo-info-wrapper">
					<?php include( 'api.php' ); ?>
                </div>


                <div class="dgv-settings-section">
                    <h2 class="wvv-form-heading"><?php _e( 'API Settings' ); ?></h2>
                    <div class="form-row">
                        <label for="dgv_client_id"><?php _e( 'Client ID', 'wp-vimeo-videos' ); ?></label>
                        <input type="text" name="dgv_client_id" id="dgv_client_id"
                               value="<?php echo get_option( 'dgv_client_id' ); ?>">
                    </div>
                    <div class="form-row">
                        <label for="dgv_client_secret"><?php _e( 'Client Secret', 'wp-vimeo-videos' ); ?></label>
                        <input type="text" name="dgv_client_secret" id="dgv_client_secret"
                               value="<?php echo get_option( 'dgv_client_secret' ); ?>">
                    </div>
                    <div class="form-row">
                        <label for="dgv_access_token"><?php _e( 'Access Token', 'wp-vimeo-videos' ); ?></label>
                        <input type="text" name="dgv_access_token" id="dgv_access_token"
                               value="<?php echo get_option( 'dgv_access_token' ); ?>">
                    </div>
                </div>


                <div class="form-row with-border">
                    <a href="<?php echo admin_url( 'upload.php?page=' . WP_DGV_Admin::PAGE_SETTINGS ); ?>"
                       class="button"><?php _e( 'Back', 'wp-vimeo-videos' ); ?></a>
                    <input type="submit" class="button-primary" name="dgv_settings_save" value="Save">
                    <a target="_blank" style="float:right;"
                       href="https://wordpress.org/support/plugin/wp-vimeo-videos/">Need help? Contact us!</a>
                </div>

            </form>
        </div>
        <div class="wvv-col-40 wvv-col">
			<?php
			$newsHelper = new WP_DGV_Product_News_Helper();
			$news       = $newsHelper->get();
			?>
			<?php if ( ! empty( $news ) ): ?>
                <div class="wvv-news-panel">
                    <h3>Product News</h3>
					<?php foreach ( $news as $news_entry ): ?>
                        <div class="wvv-news-entry">
                            <h3><?php echo $news_entry['title']; ?></h3>
                            <h4>Posted at <?php echo $news_entry['date']; ?></h4>
                            <p><?php echo $news_entry['content']; ?></p>
                        </div>
					<?php endforeach; ?>
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>