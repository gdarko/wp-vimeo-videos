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
        <a target="_blank" href="<?php echo esc_url($plugin->documentation_url()); ?>" class="button-primary"><?php _e('Documentation', 'wp-vimeo-videos-pro'); ?></a>
    </p>
</div>
