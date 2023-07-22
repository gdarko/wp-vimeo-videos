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