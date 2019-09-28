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
                    <?php  echo 'Vimeo ' . ucfirst($vimeo_helper->user_type); ?>
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
		<?php endif; ?>
        <tr>
            <th>
			    <?php _e( 'PHP Version', 'wp-vimeo-videos' ); ?>
            </th>
            <td>
	            <?php echo PHP_VERSION; ?>
            </td>
        </tr>
    </table>
</div>