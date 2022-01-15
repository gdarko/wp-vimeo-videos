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
	            <?php if ( $vimeo_helper->is_connected && $vimeo_helper->is_authenticated_connection ): ?>
                    <p class="wvv-status-green"><?php _e( 'Connected', 'wp-vimeo-videos' ); ?></p>
	            <?php elseif($vimeo_helper->is_connected && !$vimeo_helper->is_authenticated_connection): ?>
                    <p class="wvv-status-yellow"><?php _e( 'Connected (Unauthenticated)', 'wp-vimeo-videos' ); ?></p>
	            <?php else: ?>
                    <p class="wvv-status-red"><?php _e( 'Not Connected', 'wp-vimeo-videos' ); ?></p>
	            <?php endif; ?>
            </td>
        </tr>
		<?php if ( $vimeo_helper->is_connected ): ?>

			<?php if ( $vimeo_helper->is_authenticated_connection ): ?>
                <tr>
                    <th>
						<?php _e( 'User', 'wp-vimeo-videos' ); ?>
                    </th>
                    <td>
                        <a href="<?php echo esc_url( $vimeo_helper->user_link); ?>" target="_blank"><?php echo esc_html($vimeo_helper->user_name); ?></a>
                    </td>
                </tr>
			<?php endif; ?>

			<?php if ( $vimeo_helper->is_authenticated_connection ): ?>
                <tr>
                    <th>
						<?php _e( 'Plan', 'wp-vimeo-videos' ); ?>
                    </th>
                    <td>
						<?php echo 'Vimeo ' . ucfirst( esc_html($vimeo_helper->user_type) ); ?>
                    </td>
                </tr>
			<?php endif; ?>

            <tr>
                <th>
					<?php _e( 'App', 'wp-vimeo-videos' ); ?>
                </th>
                <td>
					<?php echo esc_html($vimeo_helper->app_name); ?>
                </td>
            </tr>
            <tr>
                <th>
					<?php _e( 'Scopes', 'wp-vimeo-videos' ); ?>
                </th>
                <td>
					<?php
					if ( ! empty( $vimeo_helper->scopes ) ) {
						echo esc_html( implode( ', ', $vimeo_helper->scopes ) );
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
						$reset = esc_html($vimeo_helper->upload_quota['periodic']['reset_date']);
						echo sprintf( __( '%s / %s (resets on %s)', 'wp-vimeo-videos' ), $used, $max, $reset );
						?>
                    </td>
                </tr>
			<?php endif; ?>
			<?php if ( isset( $vimeo_helper->headers['x-ratelimit-limit'] ) && is_numeric( $vimeo_helper->headers['x-ratelimit-limit'] ) ): ?>
                <tr>
                    <th>
						<?php _e( 'Rate Limits', 'wp-vimeo-videos' ); ?>
                    </th>
                    <td>
						<?php
						$used  = (int) $vimeo_helper->headers['x-ratelimit-limit'] - (int) $vimeo_helper->headers['x-ratelimit-remaining'];
						$max   = (int) $vimeo_helper->headers['x-ratelimit-limit'];
						$reset = esc_html( $vimeo_helper->headers['x-ratelimit-reset'] );
						echo sprintf( __( '%d / %d per minute (resets on %s)', 'wp-vimeo-videos' ), $used, $max, $reset );
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
	                <?php echo esc_html( $_SERVER['SERVER_SOFTWARE'] ); ?>
                </td>
            </tr>
		<?php endif; ?>
        <tr>
            <th>
				<?php _e( 'Max Upload Size', 'wp-vimeo-videos' ); ?>
            </th>
            <td>
				<?php echo esc_html(ini_get( 'upload_max_filesize' )); ?>
            </td>
        </tr>
        <tr>
            <th>
				<?php _e( 'Max Post Size', 'wp-vimeo-videos' ); ?>
            </th>
            <td>
				<?php echo esc_html(ini_get( 'post_max_size' )); ?>
            </td>
        </tr>

	    <?php
	    // Detect problems
	    $problems = $vimeo_helper->find_problems();
	    ?>

	    <?php if(!empty($problems) && count($problems)>0): ?>
            <tr class="wvv-problems">
                <th>
				    <?php _e( 'Detected Problems', 'wp-vimeo-videos' ); ?>
                </th>
                <td>
                    <p class="wvv-problem-head"><?php _e('Fix the following problems to ensure proper function:'); ?></p>
                    <ol>
					    <?php foreach($problems as $problem): ?>
                            <li>
                                <div class="wvv-problem-wrapper">
                                    <div class="wvv-problem--info">
                                        <p><?php echo esc_html($problem['info']); ?></p>
                                        <p><a class="wvv-problem-fix-trigger" href="#"><?php _e('Fix prolbem', 'wp-vimeo-videos'); ?></a></p>
                                    </div>
                                    <div class="wvv-problem--fix" style="display: none;">
									    <?php echo esc_html($problem['fix']); ?>
                                    </div>
                                </div>
                            </li>
					    <?php endforeach; ?>
                    </ol>
                </td>
            </tr>
	    <?php endif; ?>

    </table>
</div>
