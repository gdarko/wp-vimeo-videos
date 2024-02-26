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

/* @var \Vimeify\Core\Plugin $plugin */

use Vimeify\Core\Components\Vimeo;
use Vimeify\Core\Utilities\Formatters\ByteFormatter;
use Vimeify\Core\Utilities\Formatters\DateFormatter;

$byte_formatter = new ByteFormatter();
$date_formatter = new DateFormatter();

?>

<tr>
	<th style="width: 20%">
		<?php _e( 'Status', 'wp-vimeo-videos' ); ?>
	</th>
	<td>
		<?php if ( $plugin->system()->vimeo()->is_connected && $plugin->system()->vimeo()->is_authenticated_connection ): ?>
			<span class="wvv-status-green"><?php _e( 'Connected', 'wp-vimeo-videos' ); ?></span>
		<?php elseif ( $plugin->system()->vimeo()->is_connected && ! $plugin->system()->vimeo()->is_authenticated_connection ): ?>
			<span class="wvv-status-yellow"><?php _e( 'Connected (Unauthenticated)', 'wp-vimeo-videos' ); ?></span>
		<?php else: ?>
			<span class="wvv-status-red"><?php _e( 'Not Connected', 'wp-vimeo-videos' ); ?></span>
		<?php endif; ?>
	</td>
</tr>
<?php if ( $plugin->system()->vimeo()->is_connected ): ?>

	<?php if ( $plugin->system()->vimeo()->is_authenticated_connection ): ?>
		<tr>
			<th style="width: 20%">
				<?php _e( 'User', 'wp-vimeo-videos' ); ?>
			</th>
			<td>
				<a href="<?php echo esc_url( $plugin->system()->vimeo()->user_link ); ?>" target="_blank"><?php echo esc_html( $plugin->system()->vimeo()->user_name ); ?></a>
			</td>
		</tr>
	<?php endif; ?>

	<?php if ( $plugin->system()->vimeo()->is_authenticated_connection ): ?>
		<tr>
			<th style="width: 20%">
				<?php _e( 'Plan', 'wp-vimeo-videos' ); ?>
			</th>
			<td>
				<?php echo esc_html( $plugin->system()->vimeo()->get_plan( true ) ); ?>
			</td>
		</tr>
	<?php endif; ?>

	<tr>
		<th style="width: 20%">
			<?php _e( 'App', 'wp-vimeo-videos' ); ?>
		</th>
		<td>
			<?php echo esc_html($plugin->system()->vimeo()->app_name); ?>
		</td>
	</tr>
	<tr>
		<th style="width: 20%">
			<?php _e( 'Scopes', 'wp-vimeo-videos' ); ?>
		</th>
		<td>
			<?php
			if ( ! empty( $plugin->system()->vimeo()->scopes ) ) {
				echo implode( ', ', $plugin->system()->vimeo()->scopes );
			} else {
				echo __( 'No scopes found', 'wp-vimeo-videos' );
			}
			?>
		</td>
	</tr>
	<?php if ( ! empty( $plugin->system()->vimeo()->upload_quota ) ): ?>
        <tr>
            <th>
				<?php _e( 'Quota', 'wp-vimeo-videos' ); ?>
            </th>
            <td>
				<?php
				switch ( $plugin->system()->vimeo()->get_current_quota_type() ) {
					case Vimeo::QUOTA_TYPE_VIDEOS_COUNT:
						$used  = $plugin->system()->vimeo()->get_current_used_quota();
						$max   = $plugin->system()->vimeo()->get_current_max_quota();
						$reset = $plugin->system()->vimeo()->get_quota_reset_date();
						if ( $reset ) {
							echo sprintf( __( '%s / %s (resets on %s)', 'wp-vimeo-videos' ), $used, $max, $reset );
						} else {
							echo sprintf( __( '%s / %s', 'wp-vimeo-videos' ), $used, $max );
						}
						break;
					case Vimeo::QUOTA_TYPE_VIDEOS_SIZE:
						$used  = $byte_formatter->format( (int) $plugin->system()->vimeo()->get_current_used_quota(), 2 );
						$max   = $byte_formatter->format( (int) $plugin->system()->vimeo()->get_current_max_quota(), 2 );
						$reset = $date_formatter->format_tz( $plugin->system()->vimeo()->get_quota_reset_date() );
						if ( $reset ) {
							echo sprintf( __( '%s / %s (resets on %s)', 'wp-vimeo-videos' ), $used, $max, $reset );
						} else {
							echo sprintf( __( '%s / %s', 'wp-vimeo-videos' ), $used, $max );
						}
						break;
					default:
						echo __( 'Unsupported account quota type.', 'wp-vimeo-videos' );
						break;
				}
				?>
            </td>
        </tr>
	<?php endif; ?>
	<?php if ( isset( $plugin->system()->vimeo()->headers['x-ratelimit-limit'] ) && is_numeric( $plugin->system()->vimeo()->headers['x-ratelimit-limit'] ) ): ?>
		<tr>
			<th style="width: 20%">
				<?php _e( 'Rate Limits', 'wp-vimeo-videos' ); ?>
			</th>
			<td>
				<?php
				$used  = $plugin->system()->vimeo()->headers['x-ratelimit-limit'] - $plugin->system()->vimeo()->headers['x-ratelimit-remaining'];
				$max   = $plugin->system()->vimeo()->headers['x-ratelimit-limit'];
				$reset = $date_formatter->format_tz( $plugin->system()->vimeo()->headers['x-ratelimit-reset'] );
				echo sprintf( __( '%s / %s per minute (resets on %s)', 'wp-vimeo-videos' ), $used, $max, $reset );
				?>
			</td>
		</tr>
	<?php endif; ?>
<?php endif; ?>