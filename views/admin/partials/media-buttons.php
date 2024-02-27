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
/* @var int $id */

$mimetype = get_post_mime_type( $id );
$is_video = strpos( $mimetype, 'video/' ) !== false;

?>

<div class="wvv-button-wrap">

	<?php if ( ! $is_video ): ?>

		<p><?php _e( 'Not a video', 'vimeify' ); ?></p>

	<?php else: ?>

		<?php
		$data = get_post_meta( $id, 'dgv', true );
		?>

		<?php if ( ! isset($data['vimeo_id']) ): ?>

			<?php if ( ! $plugin->system()->vimeo()->can_upload() ): ?>

                <p><?php _e( "Sorry! You are missing the 'upload' scope. Please check your Vimeo account and request 'upload' access to be able to upload videos from your WordPress site.", 'vimeify' ); ?></p>

			<?php elseif ( ! current_user_can( 'upload_files' ) ): ?>

                <p><?php _e( "Sorry! You don't have the required access to upload files.", 'vimeify' ); ?></p>

			<?php else: ?>

				<p><a target="_blank" class="button-primary dgv-upload-attachment" data-id="<?php echo esc_attr($id); ?>"><?php _e( 'Upload to Vimeo', 'vimeify' ); ?></a></p>

			<?php endif; ?>

		<?php else: ?>

			<?php
			$link   = $plugin->system()->database()->get_vimeo_link( $data['local_id'] );
			?>

			<p><?php _e( 'Video uploaded to Vimeo.', 'vimeify' ); ?></p>
            <p>
				<?php if(current_user_can( 'delete_posts' ) && $plugin->system()->vimeo()->can_delete()): ?>
					<a href="#" class="button-primary dgv-delete-attachment" data-id="<?php echo esc_attr($id); ?>"><?php  _e( 'Delete from Vimeo', 'vimeify' ); ?></a>
				<?php endif; ?>
                
				<a target="_blank" class="button" href="<?php echo esc_url($link); ?>"><?php echo  __( 'Vimeo Link', 'vimeify' ); ?></a>
			</p>

		<?php endif; ?>

	<?php endif; ?>

</div>