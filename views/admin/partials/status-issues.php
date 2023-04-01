<?php
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