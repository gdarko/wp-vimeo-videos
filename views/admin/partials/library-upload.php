<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://ideologix.com)
 *
 * This file is part of "Vimeify - Video Uploads for Vimeo"
 *
 * Vimeify - Video Uploads for Vimeo is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * Vimeify - Video Uploads for Vimeo is distributed in the hope that
 * it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with "Vimeify - Video Uploads for Vimeo". If not, see <https://www.gnu.org/licenses/>.
 *
 * ---
 *
 * Author Note: This code was written by Darko Gjorgjijoski <dg@darkog.com>
 * If you have any questions find the contact details in the root plugin file.
 *
 **********************************************************************/

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
    <a href="<?php echo admin_url( 'admin.php?page=' . \Vimeify\Core\Backend\Ui::PAGE_VIMEO ); ?>"><?php _e( '< Back to library', 'wp-vimeo-videos-pro' ); ?></a>
</p>
