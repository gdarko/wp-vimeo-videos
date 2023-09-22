<?php
/* @var \Vimeify\Core\Plugin $plugin */
/* @var \WP_Query $query */
/* @var bool $single_pages_enabled */
/* @var array $actions */
/* @var string $pagination */
?>

<div class="dgv-table-wrapper table-responsive <?php empty( $query->posts ) ? 'dgv-table-wrapper-empty' : ''; ?>">
    <table class="dgv-table table">
        <thead>
        <tr>
            <th class="dgv-head-title"><?php _e( 'Title', 'wp-vimeo-videos' ); ?></th>
            <th class="dgv-head-date"><?php _e( 'Date', 'wp-vimeo-videos' ); ?></th>
			<?php if ( ! empty( $actions ) ): ?>
                <th class="dgv-head-actions"><?php _e( 'Actions', 'wp-vimeo-videos' ); ?></th>
			<?php endif; ?>
        </tr>
        </thead>
        <tbody>
		<?php if ( ! empty( $query->posts ) ): ?>
			<?php foreach ( $query->posts as $post ): ?>
                <tr>
                    <td class="dgv-row-title"><?php echo get_the_title( $post ); ?></td>
                    <td class="dgv-row-date"><?php echo get_the_date( '', $post ); ?></td>
					<?php if ( ! empty( $actions ) ): ?>
                        <td class="dgv-row-actions">
							<?php foreach ( $actions as $action ): ?>
								<?php
								$icon  = isset( $action['icon'] ) ? $action['icon'] : '';
								$text  = isset( $action['text'] ) ? $action['text'] : '';
								$cback = isset( $action['action'] ) && is_callable( $action['action'] ) ? $action['action'] : null;
								$data  = [];
								if ( ! is_null( $cback ) ) {
									$data = call_user_func( $cback, $post );
								}
								?>
                                <a href="<?php echo ! empty( $data['link'] ) ? esc_url( $data['link'] ) : ''; ?>" target="<?php echo isset( $data['target'] ) ? esc_attr( $data['target'] ) : '_blank'; ?>" title="<?php echo esc_attr( $text ); ?>">
                                    <span class="<?php echo esc_attr( $icon ); ?>"></span>
                                </a>
							<?php endforeach; ?>
                        </td>
					<?php endif; ?>
                </tr>
			<?php endforeach; ?>
		<?php else: ?>
            <tr>
                <td colspan="4"><?php _e( 'No resuls found', 'wp-vimeo-videos' ); ?></td>
            </tr>
		<?php endif; ?>
        </tbody>
    </table>
	<?php if ( $query->max_num_pages > 1 ): ?>
		<?php echo $pagination; ?>
	<?php endif; ?>
</div>
