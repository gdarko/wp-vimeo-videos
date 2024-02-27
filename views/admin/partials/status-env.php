<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/wp-vimeo-videos/
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
?>


<tr>
	<th style="width: 20%">
		<?php _e( 'PHP Version', 'vimeify' ); ?>
	</th>
	<td>
		<?php echo PHP_VERSION; ?>
	</td>
</tr>
<?php if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ): ?>
	<tr>
		<th>
			<?php _e( 'Web Server', 'vimeify' ); ?>
		</th>
		<td>
			<?php echo esc_html($_SERVER['SERVER_SOFTWARE']); ?>
		</td>
	</tr>
<?php endif; ?>
<tr>
	<th>
		<?php _e( 'Max Upload Size', 'vimeify' ); ?>
	</th>
	<td>
		<?php echo ini_get( 'upload_max_filesize' ); ?>
	</td>
</tr>
<tr>
	<th>
		<?php _e( 'Max Post Size', 'vimeify' ); ?>
	</th>
	<td>
		<?php echo ini_get( 'post_max_size' ); ?>
	</td>
</tr>
<tr>
	<th>
		<?php _e( 'Max Exec Time', 'vimeify' ); ?>
	</th>
	<td>
		<?php echo ini_get( 'max_execution_time' ); ?>
	</td>
</tr>
<tr>
	<th>
		<?php _e( 'Max Input Time', 'vimeify' ); ?>
	</th>
	<td>
		<?php echo ini_get( 'max_input_time' ); ?>
	</td>
</tr>
<tr>
    <th>
		<?php _e( 'Memory Limit', 'vimeify' ); ?>
    </th>
    <td>
		<?php echo sprintf('WP %s / PHP %s', WP_MEMORY_LIMIT, ini_get('memory_limit')); ?>
    </td>
</tr>
<tr>
    <th>
		<?php _e( 'Operating System', 'vimeify' ); ?>
    </th>
    <td>
		<?php echo PHP_OS; ?>
    </td>
</tr>