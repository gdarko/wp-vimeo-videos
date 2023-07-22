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

$byte_formatter = new \Vimeify\Core\Utilities\Formatters\ByteFormatter();
$date_formatter = new \Vimeify\Core\Utilities\Formatters\DateFormatter();

?>

<tr>
	<th style="width: 20%">
		<?php _e( 'Status', 'wp-vimeo-videos-pro' ); ?>
	</th>
	<td>
		<?php if ( $plugin->system()->vimeo()->is_connected && $plugin->system()->vimeo()->is_authenticated_connection ): ?>
			<span class="wvv-status-green"><?php _e( 'Connected', 'wp-vimeo-videos-pro' ); ?></span>
		<?php elseif ( $plugin->system()->vimeo()->is_connected && ! $plugin->system()->vimeo()->is_authenticated_connection ): ?>
			<span class="wvv-status-yellow"><?php _e( 'Connected (Unauthenticated)', 'wp-vimeo-videos-pro' ); ?></span>
		<?php else: ?>
			<span class="wvv-status-red"><?php _e( 'Not Connected', 'wp-vimeo-videos-pro' ); ?></span>
		<?php endif; ?>
	</td>
</tr>
<?php if ( $plugin->system()->vimeo()->is_connected ): ?>

	<?php if ( $plugin->system()->vimeo()->is_authenticated_connection ): ?>
		<tr>
			<th style="width: 20%">
				<?php _e( 'User', 'wp-vimeo-videos-pro' ); ?>
			</th>
			<td>
				<a href="<?php echo esc_url( $plugin->system()->vimeo()->user_link ); ?>" target="_blank"><?php echo esc_html( $plugin->system()->vimeo()->user_name ); ?></a>
			</td>
		</tr>
	<?php endif; ?>

	<?php if ( $plugin->system()->vimeo()->is_authenticated_connection ): ?>
		<tr>
			<th style="width: 20%">
				<?php _e( 'Plan', 'wp-vimeo-videos-pro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $plugin->system()->vimeo()->get_plan( true ) ); ?>
			</td>
		</tr>
	<?php endif; ?>

	<tr>
		<th style="width: 20%">
			<?php _e( 'App', 'wp-vimeo-videos-pro' ); ?>
		</th>
		<td>
			<?php echo esc_html($plugin->system()->vimeo()->app_name); ?>
		</td>
	</tr>
	<tr>
		<th style="width: 20%">
			<?php _e( 'Scopes', 'wp-vimeo-videos-pro' ); ?>
		</th>
		<td>
			<?php
			if ( ! empty( $plugin->system()->vimeo()->scopes ) ) {
				echo implode( ', ', $plugin->system()->vimeo()->scopes );
			} else {
				echo __( 'No scopes found', 'wp-vimeo-videos-pro' );
			}
			?>
		</td>
	</tr>
	<?php
	$used = $plugin->system()->vimeo()->get_current_used_quota();
	?>
	<?php if ( $used ): ?>
		<tr>
			<th style="width: 20%">
				<?php _e( 'Quota', 'wp-vimeo-videos-pro' ); ?>
			</th>
			<td>
				<?php
				$used  = $byte_formatter->format( (int) $plugin->system()->vimeo()->get_current_used_quota(), 2 );
				$max   = $byte_formatter->format( (int) $plugin->system()->vimeo()->get_current_max_quota(), 2 );
				$reset = $date_formatter->format_tz( $plugin->system()->vimeo()->get_quota_reset_date() );
				if ( $reset ) {
					echo sprintf( __( '%s / %s (resets on %s)', 'wp-vimeo-videos-pro' ), $used, $max, $reset );
				} else {
					echo sprintf( __( '%s / %s', 'wp-vimeo-videos-pro' ), $used, $max );
				}
				?>
			</td>
		</tr>
	<?php endif; ?>
	<?php if ( isset( $plugin->system()->vimeo()->headers['x-ratelimit-limit'] ) && is_numeric( $plugin->system()->vimeo()->headers['x-ratelimit-limit'] ) ): ?>
		<tr>
			<th style="width: 20%">
				<?php _e( 'Rate Limits', 'wp-vimeo-videos-pro' ); ?>
			</th>
			<td>
				<?php
				$used  = $plugin->system()->vimeo()->headers['x-ratelimit-limit'] - $plugin->system()->vimeo()->headers['x-ratelimit-remaining'];
				$max   = $plugin->system()->vimeo()->headers['x-ratelimit-limit'];
				$reset = $date_formatter->format_tz( $plugin->system()->vimeo()->headers['x-ratelimit-reset'] );
				echo sprintf( __( '%s / %s per minute (resets on %s)', 'wp-vimeo-videos-pro' ), $used, $max, $reset );
				?>
			</td>
		</tr>
	<?php endif; ?>
<?php endif; ?>