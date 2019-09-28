<?php
/* @var WP_DGV_Api_Helper $vimeo_helper */
/* @var WP_DGV_Db_Helper $db_helper */
?>

<h2><?php _e( 'Upload to Vimeo', 'wp-vimeo-videos' ); ?></h2>

<div class="wvv-box" style="max-width: 500px;">
    <form class="wvv-video-upload" enctype="multipart/form-data" method="post" action="/">
        <div class="form-row">
            <label for="vimeo_title"><?php _e( 'Title', 'wp-vimeo-videos' ); ?></label>
            <input type="text" name="vimeo_title" id="vimeo_title">
        </div>
        <div class="form-row">
            <label for="vimeo_description"><?php _e( 'Description', 'wp-vimeo-videos' ); ?></label>
            <textarea name="vimeo_description" id="vimeo_description"></textarea>
        </div>
        <div class="form-row">
            <label for="vimeo_video"><?php _e( 'Video File', 'wp-vimeo-videos' ); ?></label>
            <p><input type="file" name="vimeo_video" id="vimeo_video"></p>
            <div class="dgv-progress-bar" style="display: none;">
                <div class="dgv-progress-bar-inner"></div>
                <div class="dgv-progress-bar-value">0%</div>
            </div>
        </div>
        <div class="form-row with-border">
            <div class="dgv-loader" style="display:none;"></div>
            <button type="submit" class="button-primary" name="vimeo_upload" value="1">
				<?php _e( 'Upload', 'wp-vimeo-videos' ); ?>
            </button>
        </div>
    </form>
</div>
<p>
    <a href="<?php echo admin_url( 'upload.php?page=' . WP_DGV_Admin::PAGE_VIMEO ); ?>"><?php _e( '< Back to library', 'wp-vimeo-videos' ); ?></a>
</p>