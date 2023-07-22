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
?>


<tr>
	<th style="width: 20%">
		<?php _e( 'PHP Version', 'wp-vimeo-videos-pro' ); ?>
	</th>
	<td>
		<?php echo PHP_VERSION; ?>
	</td>
</tr>
<?php if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ): ?>
	<tr>
		<th>
			<?php _e( 'Web Server', 'wp-vimeo-videos-pro' ); ?>
		</th>
		<td>
			<?php echo esc_html($_SERVER['SERVER_SOFTWARE']); ?>
		</td>
	</tr>
<?php endif; ?>
<tr>
	<th>
		<?php _e( 'Max Upload Size', 'wp-vimeo-videos-pro' ); ?>
	</th>
	<td>
		<?php echo ini_get( 'upload_max_filesize' ); ?>
	</td>
</tr>
<tr>
	<th>
		<?php _e( 'Max Post Size', 'wp-vimeo-videos-pro' ); ?>
	</th>
	<td>
		<?php echo ini_get( 'post_max_size' ); ?>
	</td>
</tr>
<tr>
	<th>
		<?php _e( 'Max Exec Time', 'wp-vimeo-videos-pro' ); ?>
	</th>
	<td>
		<?php echo ini_get( 'max_execution_time' ); ?>
	</td>
</tr>
<tr>
	<th>
		<?php _e( 'Max Input Time', 'wp-vimeo-videos-pro' ); ?>
	</th>
	<td>
		<?php echo ini_get( 'max_input_time' ); ?>
	</td>
</tr>