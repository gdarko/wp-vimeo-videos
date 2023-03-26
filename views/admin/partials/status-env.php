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