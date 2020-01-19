<?php
/* @var WP_DGV_Api_Helper $vimeo_helper */
?>

<div class="vimeo-api-info">
    <table>
        <tr>
            <th style="width: 25%">
				<?php _e( 'Status', 'wp-vimeo-videos' ); ?>
            </th>
            <td>
				<?php if ( $vimeo_helper->is_connected ): ?>
                    <p class="wvv-status-green"><?php _e( 'Connected', 'wp-vimeo-videos' ); ?></p>
				<?php else: ?>
                    <p class="wvv-status-red"><?php _e( 'Not Connected', 'wp-vimeo-videos' ); ?></p>
				<?php endif; ?>
            </td>
        </tr>
		<?php if ( $vimeo_helper->is_connected ): ?>
            <tr>
                <th>
					<?php _e( 'User', 'wp-vimeo-videos' ); ?>
                </th>
                <td>
                    <a href="<?php echo $vimeo_helper->user_link; ?>"
                       target="_blank"><?php echo $vimeo_helper->user_name; ?></a>
                </td>
            </tr>
            <tr>
                <th>
					<?php _e( 'Plan', 'wp-vimeo-videos' ); ?>
                </th>
                <td>
					<?php echo 'Vimeo ' . ucfirst( $vimeo_helper->user_type ); ?>
                </td>
            </tr>
            <tr>
                <th>
					<?php _e( 'App', 'wp-vimeo-videos' ); ?>
                </th>
                <td>
					<?php echo $vimeo_helper->app_name; ?>
                </td>
            </tr>
            <tr>
                <th>
					<?php _e( 'Scopes', 'wp-vimeo-videos' ); ?>
                </th>
                <td>
					<?php
					if ( ! empty( $vimeo_helper->scopes ) ) {
						echo implode( ', ', $vimeo_helper->scopes );
					} else {
						echo __( 'No scopes found', 'wp-vimeo-videos' );
					}
					?>
                </td>
            </tr>
			<?php if ( isset( $vimeo_helper->upload_quota['periodic']['used'] ) ): ?>
                <tr>
                    <th>
						<?php _e( 'Quota', 'wp-vimeo-videos' ); ?>
                    </th>
                    <td>
						<?php
						$used  = wvv_format_bytes( (int) $vimeo_helper->upload_quota['periodic']['used'], 2 );
						$max   = wvv_format_bytes( (int) $vimeo_helper->upload_quota['periodic']['max'], 2 );
						$reset = $vimeo_helper->upload_quota['periodic']['reset_date'];
						echo sprintf( __( '%s / %s (resets on %s)', 'wp-vimeo-videos' ), $used, $max, $reset );
						?>
                    </td>
                </tr>
			<?php endif; ?>
			<?php if ( isset( $vimeo_helper->headers['X-RateLimit-Limit'] ) && is_numeric( $vimeo_helper->headers['X-RateLimit-Limit'] ) ): ?>
                <tr>
                    <th>
						<?php _e( 'Rate Limits', 'wp-vimeo-videos' ); ?>
                    </th>
                    <td>
						<?php
						$used  = $vimeo_helper->headers['X-RateLimit-Limit'] - $vimeo_helper->headers['X-RateLimit-Remaining'];
						$max   = $vimeo_helper->headers['X-RateLimit-Limit'];
						$reset = $vimeo_helper->headers['X-RateLimit-Reset'];
						echo sprintf( __( '%s / %s per minute (resets on %s)', 'wp-vimeo-videos' ), $used, $max, $reset );
						?>
                    </td>
                </tr>
			<?php endif; ?>
		<?php endif; ?>
        <tr>
            <th>
				<?php _e( 'PHP Version', 'wp-vimeo-videos' ); ?>
            </th>
            <td>
				<?php echo PHP_VERSION; ?>
            </td>
        </tr>
		<?php if(isset($_SERVER['SERVER_SOFTWARE'])): ?>
            <tr>
                <th>
					<?php _e( 'Web Server', 'wp-vimeo-videos' ); ?>
                </th>
                <td>
					<?php echo $_SERVER['SERVER_SOFTWARE']; ?>
                </td>
            </tr>
		<?php endif; ?>
        <tr>
            <th>
				<?php _e( 'Max Upload Size', 'wp-vimeo-videos' ); ?>
            </th>
            <td>
				<?php echo ini_get( 'upload_max_filesize' ); ?>
            </td>
        </tr>
        <tr>
            <th>
				<?php _e( 'Max Post Size', 'wp-vimeo-videos' ); ?>
            </th>
            <td>
				<?php echo ini_get( 'post_max_size' ); ?>
            </td>
        </tr>
    </table>
</div>