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

<h2 class="dgv-skip-margins"><?php _e( 'Vimeo Videos', 'wp-vimeo-videos-pro' ); ?>

    <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . \Vimeify\Core\Backend\Ui::PAGE_VIMEO . '&action=new' ) ); ?>" class="page-title-action"><?php _e( 'Upload new', 'wp-vimeo-videos-pro' ); ?></a>

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

	$list_table = new \Vimeify\Core\Backend\ListTables\VideosTable( $plugin );

	$list_table->prepare_items();

	$list_table->search_box( __( 'Search', 'wp-vimeo-videos-pro' ), 'search_id' );

	$list_table->views();

	$list_table->display();

	?>

</form>

