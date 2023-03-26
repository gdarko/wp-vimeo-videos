<?php
/* @var \Vimeify\Core\Plugin $plugin */
/* @var int $id */

$mimetype = get_post_mime_type( $id );
$is_video = strpos( $mimetype, 'video/' ) !== false;

?>

<div class="wvv-button-wrap">

	<?php if ( ! $is_video ): ?>

		<p><?php _e( 'Not a video', 'wp-vimeo-videos-pro' ); ?></p>

	<?php else: ?>

		<?php
		$data = get_post_meta( $id, 'dgv', true );
		?>

		<?php if ( ! isset($data['vimeo_id']) ): ?>

			<?php if ( ! $plugin->system()->vimeo()->can_upload() ): ?>

                <p><?php _e( "Sorry! You are missing the 'upload' scope. Please check your Vimeo account and request 'upload' access to be able to upload videos from your WordPress site.", 'wp-vimeo-videos-pro' ); ?></p>

			<?php elseif ( ! current_user_can( 'upload_files' ) ): ?>

                <p><?php _e( "Sorry! You don't have the required access to upload files.", 'wp-vimeo-videos-pro' ); ?></p>

			<?php else: ?>

				<p><a target="_blank" class="button-primary dgv-upload-attachment" data-id="<?php echo esc_attr($id); ?>"><?php _e( 'Upload to Vimeo', 'wp-vimeo-videos-pro' ); ?></a></p>

			<?php endif; ?>

		<?php else: ?>

			<?php
			$link   = $plugin->system()->database()->get_vimeo_link( $data['local_id'] );
			?>

			<p><?php _e( 'Video uploaded to Vimeo.', 'wp-vimeo-videos-pro' ); ?></p>
            <p>
				<?php if(current_user_can( 'delete_posts' ) && $plugin->system()->vimeo()->can_delete()): ?>
					<a href="#" class="button-primary dgv-delete-attachment" data-id="<?php echo esc_attr($id); ?>"><?php  _e( 'Delete from Vimeo', 'wp-vimeo-videos-pro' ); ?></a>
				<?php endif; ?>
                
				<a target="_blank" class="button" href="<?php echo esc_url($link); ?>"><?php echo  __( 'Vimeo Link', 'wp-vimeo-videos-pro' ); ?></a>
			</p>

		<?php endif; ?>

	<?php endif; ?>

</div>