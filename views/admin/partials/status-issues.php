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

// Detect problems
$problems = $plugin->system()->vimeo()->find_problems();
?>

<?php if ( ! empty( $problems ) && count( $problems ) > 0 ): ?>
	<tr class="wvv-problems">
		<th style="width: 20%">
			<?php _e( 'Detected Problems', 'wp-vimeo-videos-pro' ); ?>
		</th>
		<td>
			<p class="wvv-problem-head"><?php _e( 'Fix the following problems to ensure proper function:', 'wp-vimeo-videos-pro' ); ?></p>
			<ol>
				<?php foreach ( $problems as $problem ): ?>
					<li>
						<div class="wvv-problem-wrapper">
							<div class="wvv-problem--info">
								<p><?php echo esc_html($problem['info']); ?></p>
								<p><a class="wvv-problem-fix-trigger" href="#"><?php _e( 'Fix prolbem', 'wp-vimeo-videos-pro' ); ?></a></p>
							</div>
							<div class="wvv-problem--fix" style="display: none;">
								<?php echo wp_kses($problem['fix'], wp_kses_allowed_html('post')); ?>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</td>
	</tr>
<?php endif; ?>