<?php
/********************************************************************
 * Copyright (C) 2024 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2024 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/vimeify/
 *
 * Vimeify - Formerly "WP Vimeo Videos" is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * Vimeify - Formerly "WP Vimeo Videos" is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this plugin. If not, see <https://www.gnu.org/licenses/>.
 *
 * Code developed by Darko Gjorgjijoski <dg@darkog.com>.
 **********************************************************************/

/* @var \Vimeify\Core\Plugin $plugin */
?>

<h2><?php _e( 'Invalid API Details', 'vimeify' ); ?></h2>

<div class="wvv-box">

    <h3><?php _e('Oh, snap!', 'vimeify'); ?></h3>

    <p><?php _e( 'Your Vimeo API credentials are missing or are invalid. Go to the <strong>Settings > Vimeo</strong> screen and enter valid vimeo details.', 'vimeify' ); ?></p>

    <p>
		<?php echo sprintf(__('Please go to the %s and re-generate your access token with all the required scopes. If you need help check the link bellow.', 'vimeify'), '<a target="_blank" href="https://developer.vimeo.com/">Vimeo developer portal</a>', '<strong>upload</strong>' ); ?>
    </p>

    <hr/>

    <p>
        <a href="<?php echo admin_url( 'admin.php?page=' . \Vimeify\Core\Backend\Ui::PAGE_VIMEO ); ?>"
           class="button"><?php _e( 'Back', 'vimeify' ); ?></a>

        <a href="<?php echo admin_url( 'admin.php?page=' .  \Vimeify\Core\Backend\Ui::PAGE_SETTINGS ); ?>"
           class="button-primary"><?php _e( 'Settings', 'vimeify' ); ?></a>
    </p>

</div>