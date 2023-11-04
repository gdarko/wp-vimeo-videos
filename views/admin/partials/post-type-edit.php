<?php
/* @var \Vimeify\Core\Plugin $plugin
 * @var int $video_id
 * @var int $vimeo_id
 * @var int $front_pages
 * @var int $folders_management
 * @var int $embed_presets_management
 * @var int $embed_privacy_management
 * @var \Vimeify\Core\Utilities\Formatters\VimeoFormatter $vimeo_formatter
 */
?>

<div class="dgv-pedit">
	<?php if ( ! $plugin->system()->vimeo()->is_connected ): ?>
        <p><?php _e( 'Please enter valid api credentails.', 'wp-vimeo-videos' ); ?></p>
	<?php elseif ( ! $plugin->system()->vimeo()->can_edit() ): ?>
        <p><?php _e( 'Edit scope is missing. Please request Edit scope for your access token in the Vimeo Developer Tools in order to be able to edit videos', 'wp-vimeo-videos' ); ?></p>
	<?php else: ?>
		<?php
		// Gather data
		$video = array();
		try {
			$video = $plugin->system()->vimeo()->get_video_by_local_id( $video_id, array(
				'uri',
				'name',
				'description',
				'link',
				'duration',
				'width',
				'height',
				'is_playable',
				'privacy',
				'embed',
				'parent_folder',
                'content_rating',
				'upload'
			) );
		} catch ( \Exception $e ) {
		}

		$view_privacy_opts = $plugin->system()->vimeo()->get_view_privacy_options_for_forms( 'admin' );

		$embed_preset_uri = isset( $video['body']['embed']['uri'] ) && ! empty( $video['body']['embed']['uri'] ) ? $video['body']['embed']['uri'] : null; //eg. /presets/120554271
		$folder_uri       = isset( $video['body']['parent_folder']['uri'] ) && ! empty( $video['body']['parent_folder']['uri'] ) ? $video['body']['parent_folder']['uri'] : null; //eg. /users/120624714/projects/2801250
		$link             = get_post_meta( $video_id, 'dgv_embed_link', true );
		if ( empty( $link ) ) {
			$link = sprintf( 'https://player.vimeo.com/%s', $vimeo_id );
		}
		?>

		<?php do_action( 'dgv_video_edit_top', $plugin, $video, $video_id ); ?>

        <div class="dgv-pedit-section">
            <input type="hidden" name="video_uri" value="<?php echo esc_attr( $video['body']['uri'] ); ?>">
            <div class="dgv-pedit-preview">
                <div class='dgv-embed-container'>
                    <iframe id="dgv-video-preview" src='<?php echo esc_url( $link ); ?>' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                </div>
            </div>
            <div class="dgv-pedit-description">
                <div class="dgv-pedit-section">
                    <h4><?php _e( 'Basic Details', 'wp-vimeo-videos' ); ?></h4>
                    <div class="dgv-pedit-section--fields">
                        <div class="dgv-pedit-section--row">
                            <label for="video_name"><?php _e( 'Name', 'wp-vimeo-videos' ); ?></label>
                            <input type="text" name="video_name" id="video_name" value="<?php echo esc_attr( wp_unslash( $video['body']['name'] ) ); ?>" autocomplete="off">
                        </div>
                        <div class="dgv-pedit-section--row">
                            <label for="video_description"><?php _e( 'Description', 'wp-vimeo-videos' ); ?></label>
                            <textarea name="video_description" id="video_description" rows="4"><?php echo esc_attr( wp_unslash( $video['body']['description'] ) ); ?></textarea>
                        </div>
						<?php do_action( 'dgv_video_edit_basic_section', $plugin, $video, $video_id ); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="dgv-pedit-section">
            <h4><?php _e( 'Privacy Settings', 'wp-vimeo-videos' ); ?></h4>
            <div class="dgv-pedit-section--fields">
                <div class="dgv-pedit-section--row">
                    <label for="view_privacy"><?php _e( 'View Privacy', 'wp-vimeo-videos' ); ?></label>
                    <select name="view_privacy" id="view_privacy">
						<?php foreach ( $view_privacy_opts as $key => $option ): ?><?php
							$option_state = isset( $video['body']['privacy']['view'] ) && $video['body']['privacy']['view'] === $key ? ' selected ' : '';
							$option_state .= $option['available'] ? '' : ' disabled';
							?>
                            <option <?php echo esc_attr( $option_state ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $option['name'] ); ?></option>
						<?php endforeach; ?>
                    </select>
                </div>
                <div class="dgv-pedit-section--row">
                    <label for="privacy_embed"><?php _e( 'Embed privacy type', 'wp-vimeo-videos' ); ?></label>
                    <select id="privacy_embed" name="privacy_embed" data-target=".dgv-embed-privacy-whitelist" data-show-target-if-value="whitelist" class="dgv-conditional-field">
                        <option value="public" <?php selected( $video['body']['privacy']['embed'], 'public' ); ?>><?php _e( 'Public', 'wp-vimeo-videos' ); ?></option>
                        <option value="whitelist" <?php selected( $video['body']['privacy']['embed'], 'whitelist' ); ?>><?php _e( 'Specific domains', 'wp-vimeo-videos' ); ?></option>
                    </select>
                    <div class="dgv-embed-privacy-whitelist" style="<?php echo $video['body']['privacy']['embed'] !== 'whitelist' ? 'display:none;' : ''; ?>">
                        <label for="privacy_embed_domain"><?php _e( 'Enter domain (without http(s)://)', 'wp-vimeo-videos' ); ?></label>
                        <input type="text" name="privacy_embed_domain" id="privacy_embed_domain"/>
                        <button type="submit" name="admin_action" value="add_domain" class="button" disabled><?php _e( 'Add', 'wp-vimeo-videos' ); ?></button>
                        <input type="hidden" name="uri" value="<?php echo esc_attr( $video['body']['uri'] ); ?>">
                        <div class="form-row">
                            <ul class="privacy-embed-whitelisted-domains">
								<?php
								//if($video['body']['privacy']['embed'] === 'whitelist') {
								try {
									$domains = $plugin->system()->vimeo()->get_whitelisted_domains( $video['body']['uri'] );

									if ( $domains['status'] === 200 ) {
										foreach ( $domains['body']['data'] as $domain ) {
											echo '<li>' . $domain['domain'] . ' <a href="#" class="submitdelete dgv-delete-domain" data-uri="' . $video['body']['uri'] . '" data-domain="' . $domain['domain'] . '">(' . __( 'remove', 'wp-vimeo-videos' ) . ')</a> </li>';
										}
									}
								} catch ( \Vimeo\Exceptions\VimeoRequestException $e ) {
									echo "<p style='color:red;'>" . $e->getMessage() . "</p>";
								}
								//}
								?>
                            </ul>
                        </div>
                    </div>
                </div>
				<?php do_action( 'dgv_video_edit_privacy_section', $plugin, $video, $video_id ); ?>
            </div>
        </div>
        <div class="dgv-pedit-section">
            <h4><?php _e( 'Other Settings', 'wp-vimeo-videos' ); ?></h4>
            <div class="dgv-pedit-section--fields">
                <div class="dgv-pedit-section--row">
                    <label for="embed_preset_uri">
						<?php _e( 'Embed preset', 'wp-vimeo-videos' ); ?>
                    </label>
					<?php if ( ! $plugin->system()->vimeo()->supports_embed_presets() ): ?>
                        <p><?php echo sprintf( __( 'Embed presets are only supported by the following plans:', 'wp-vimeo-videos' ) ); ?></p>
                        <ul class="wvv-std-list">
                            <li>Vimeo PRO</li>
                            <li>Vimeo Business</li>
                            <li>Vimeo Premium</li>
                        </ul><p><?php echo sprintf( __( 'Your current plan is %s.', 'wp-vimeo-videos' ), '<strong>' . 'Vimeo ' . ucfirst( $plugin->system()->vimeo()->user_type ) . '</strong>' ); ?></p>
                        <p><a href="https://vimeo.com/upgrade" target="_blank" class="button"><?php _e( 'Upgrade', 'wp-vimeo-videos' ); ?></a></p>
					<?php else: ?>
						<?php
						$current_preset_uri  = empty( $embed_preset_uri ) ? 'default' : $vimeo_formatter->embed_preset_uri_to_id( $embed_preset_uri );
						$current_preset_name = ! empty( $current_preset_uri ) && ( 'default' != $current_preset_uri ) ? $plugin->system()->vimeo()->get_embed_preset_name( $current_preset_uri ) : __( 'Default (no preset)', 'wp-vimeo-videos' );
						?>
                        <select id="embed_preset_uri" name="embed_preset_uri" class="dgv-select2" data-action="dgv_embed_preset_search" data-placeholder="<?php _e( 'Select preset...', 'wp-vimeo-videos' ); ?>">
							<?php if ( ! empty( $current_preset_uri ) ): ?>
                                <option selected value="<?php echo esc_attr( $current_preset_uri ); ?>"><?php echo esc_html( $current_preset_name ); ?></option>
							<?php endif; ?>
                        </select>
					<?php endif; ?>
                </div>
                <div class="dgv-pedit-section--row">
					<?php if ( ! $plugin->system()->vimeo()->supports_folders() ): ?>
                        <p><?php echo __( 'Folders are not supported without <strong>interact</strong> scope.', 'wp-vimeo-videos' ); ?></p>
                        <p><?php echo __( 'If you want to use Folders, please go to developer.vimeo.com/apps, regenerate your access token, add <strong>Interact</strong> to the scopes and finally replace your old token in Vimeo settings on your site.' ); ?></p>
					<?php else: ?>
						<?php
						$current_folder_uri  = empty( $folder_uri ) ? 'default' : $folder_uri;
						$current_folder_name = ! empty( $current_folder_uri ) && ( 'default' != $current_folder_uri ) ? $plugin->system()->vimeo()->get_folder_name( $current_folder_uri ) : __( 'Default (no folder)', 'wp-vimeo-videos' );
						?>
                        <label for="folder_uri"><?php _e( 'Folder', 'wp-vimeo-videos' ); ?></label>
                        <select id="folder_uri" name="folder_uri" class="dgv-select2" data-action="dgv_folder_search" data-placeholder="<?php _e( 'Select folder...', 'wp-vimeo-videos' ); ?>">
                            <option value="default" <?php selected( 'default', $current_folder_uri ); ?>><?php _e( 'Default (no folder)', 'wp-vimeo-videos' ); ?></option>
							<?php if ( ! empty( $current_folder_uri ) ): ?>
                                <option selected value="<?php echo esc_attr( $current_folder_uri ); ?>"><?php echo esc_html( $current_folder_name ); ?></option>
							<?php endif; ?>
                        </select>
					<?php endif; ?>
                </div>
				<?php do_action( 'dgv_video_edit_other_section', $plugin, $video, $video_id ); ?>
            </div>
        </div>
		<?php do_action( 'dgv_video_edit_bottom', $plugin, $video, $video_id ); ?>
	<?php endif; ?>

</div>