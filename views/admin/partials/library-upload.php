<?php
/* @var \Vimeify\Core\Plugin $plugin */

$view_privacy_opts = $plugin->system()->vimeo()->get_view_privacy_options_for_forms('admin');
$view_privacy = (int) $plugin->system()->settings()->get('admin.media_attachments.enable_privacy_option', 0);
?>

<h2><?php _e( 'Upload to Vimeo', 'wp-vimeo-videos-pro' ); ?></h2>

<div class="wvv-box" style="max-width: 500px;">
    <form class="wvv-video-upload" enctype="multipart/form-data" method="post" action="/">
        <div class="form-row">
            <label for="vimeo_title"><?php _e( 'Title', 'wp-vimeo-videos-pro' ); ?></label>
            <input type="text" name="vimeo_title" id="vimeo_title">
        </div>
        <div class="form-row">
            <label for="vimeo_description"><?php _e( 'Description', 'wp-vimeo-videos-pro' ); ?></label>
            <textarea name="vimeo_description" id="vimeo_description"></textarea>
        </div>
        <?php if($view_privacy): ?>
        <div class="form-row">
            <label for="vimeo_view_privacy"><?php _e( 'View Privacy', 'wp-vimeo-videos-pro' ); ?></label>
            <select name="vimeo_view_privacy" id="vimeo_view_privacy">
                <?php foreach($view_privacy_opts as $key => $option): ?>
                    <?php
                    $option_state = $option['default'] && $option['available'] ? 'selected' : '';
                    $option_state .= $option['available'] ? '' : ' disabled';
                    ?>
                <option <?php echo esc_attr($option_state); ?> value="<?php echo esc_attr($key); ?>"><?php echo esc_html( $option['name'] ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="form-row">
            <label for="vimeo_video"><?php _e( 'Video File', 'wp-vimeo-videos-pro' ); ?></label>
            <p class="wvv-mt-0"><input type="file" name="vimeo_video" id="vimeo_video"></p>
            <div class="dgv-progress-bar" style="display: none;">
                <div class="dgv-progress-bar-inner"></div>
                <div class="dgv-progress-bar-value">0%</div>
            </div>
        </div>
        <div class="form-row with-border">
            <div class="dgv-loader" style="display:none;"></div>
            <button type="submit" class="button-primary" name="vimeo_upload" value="1">
				<?php _e( 'Upload', 'wp-vimeo-videos-pro' ); ?>
            </button>
        </div>
    </form>
</div>
<p>
    <a href="<?php echo admin_url( 'upload.php?page=' . \Vimeify\Core\Backend\Ui::PAGE_VIMEO ); ?>"><?php _e( '< Back to library', 'wp-vimeo-videos-pro' ); ?></a>
</p>
