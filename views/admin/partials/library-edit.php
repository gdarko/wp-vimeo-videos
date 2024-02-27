<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/vimeify/
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

$video_id    = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : null;
$vimeo_id    = $plugin->system()->database()->get_vimeo_id( $video_id );
$front_pages = (int) $plugin->system()->settings()->get( 'frontend.behavior.enable_single_pages' );
$vimeo_link  = $plugin->system()->database()->get_vimeo_link( $video_id );
$permalink   = get_permalink( $video_id );

$folders_management       = (int) $plugin->system()->settings()->get( 'admin.video_management.enable_folders' );
$embed_presets_management = (int) $plugin->system()->settings()->get( 'admin.video_management.enable_embed_presets' );
$embed_privacy_management = (int) $plugin->system()->settings()->get( 'admin.video_management.enable_embed_privacy' );

$vimeo_formatter = new \Vimeify\Core\Utilities\Formatters\VimeoFormatter();

?>

<h2 class="wvv-mb-0"><?php echo get_the_title( $video_id ); ?></h2>

<?php if ( $front_pages ) : ?>
    <div id="edit-slug-box" class="wvv-p-0">
        <strong><?php _e( 'Permalink:', 'vimeify' ); ?></strong>
        <span id="sample-permalink"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_url( $permalink ); ?></a></span>
    </div>
<?php endif; ?>

<?php if ( ! $plugin->system()->vimeo()->is_connected ): ?>
    <p><?php _e( 'Please enter valid api credentails.', 'vimeify' ); ?></p>
<?php elseif ( ! $plugin->system()->vimeo()->can_edit() ): ?>
    <p><?php _e( 'Edit scope is missing. Please request Edit scope for your access token in the Vimeo Developer Tools in order to be able to edit videos', 'vimeify' ); ?></p>
<?php else: ?>

	<?php
	// Gather data
	$vimeo = array();
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

    <div class="wvv-row">
        <div class="wvv-col-40 wvv-mr-20">
            <div class="wvv-notices-wrapper"></div>
            <!-- Basic Information -->
            <div class="metabox-holder">
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle ui-sortable-handle"><?php _e( 'Basic Information', 'vimeify' ); ?></h2>
                    </div>
                    <div class="inside">
                        <div class="form-row">
                            <div class='dgv-embed-container'>
                                <iframe id="dgv-video-preview" src='<?php echo esc_url( $link ); ?>' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                            </div>
                        </div>
                        <form id="dgv-video-save-basic" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
                            <div class="form-row">
                                <label for="name"><?php _e( 'Name', 'vimeify' ); ?></label>
                                <input type="text" name="name" id="name" value="<?php echo esc_attr( wp_unslash( $video['body']['name'] ) ); ?>" autocomplete="off">
                            </div>
                            <div class="form-row">
                                <label for="description"><?php _e( 'Description', 'vimeify' ); ?></label>
                                <textarea name="description" id="description"><?php echo esc_attr( wp_unslash( $video['body']['description'] ) ); ?></textarea>
                            </div>
                            <div class="form-row">
                                <label for="view_privacy"><?php _e( 'View Privacy', 'vimeify' ); ?></label>
                                <select name="view_privacy" id="view_privacy">
									<?php foreach ( $view_privacy_opts as $key => $option ): ?><?php
										$option_state = isset( $video['body']['privacy']['view'] ) && $video['body']['privacy']['view'] === $key ? ' selected ' : '';
										$option_state .= $option['available'] ? '' : ' disabled';
										?>
                                        <option <?php echo esc_attr( $option_state ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $option['name'] ); ?></option>
									<?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-row">
                                <input type="hidden" name="uri" value="<?php echo esc_attr( $video['body']['uri'] ); ?>">
                                <button type="submit" class="button-primary wvv-mr-10"><?php _e( 'Save', 'vimeify' ); ?></button>
                                <a target="_blank" href="<?php echo esc_url( $vimeo_link ); ?>" class="inline-button"><?php _e( 'View on Vimeo', 'vimeify' ); ?></a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="wvv-col-40">

			<?php if ( $embed_privacy_management ): ?>
                <!-- Embed Privacy -->
                <div class="metabox-holder">
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="hndle ui-sortable-handle"><?php _e( 'Embed Privacy', 'vimeify' ); ?></h2>
                        </div>
                        <div class="inside">
                            <form id="dgv-video-save-embed-privacy" class="submitbox" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
                                <div class="form-row">
                                    <label for="privacy_embed"><?php _e( 'Embed privacy type', 'vimeify' ); ?></label>
                                    <select id="privacy_embed" name="privacy_embed" data-target=".privacy-embed-whitelist-domains" data-show-target-if-value="whitelist" class="dgv-conditional-field">
                                        <option value="public" <?php selected( $video['body']['privacy']['embed'], 'public' ); ?>><?php _e( 'Public', 'vimeify' ); ?></option>
                                        <option value="whitelist" <?php selected( $video['body']['privacy']['embed'], 'whitelist' ); ?>><?php _e( 'Specific domains', 'vimeify' ); ?></option>
                                    </select>
                                </div>
                                <div class="form-row privacy-embed-whitelist-domains" style="<?php echo $video['body']['privacy']['embed'] !== 'whitelist' ? 'display:none;' : ''; ?>">
                                    <label for="privacy_embed_domain"><?php _e( 'Enter domain (without http(s)://)', 'vimeify' ); ?></label>
                                    <input type="text" name="privacy_embed_domain" id="privacy_embed_domain"/>
                                    <button type="submit" name="admin_action" value="add_domain" class="button" disabled><?php _e( 'Add', 'vimeify' ); ?></button>
                                    <input type="hidden" name="uri" value="<?php echo esc_attr( $video['body']['uri'] ); ?>">
                                    <div class="form-row">
                                        <ul class="privacy-embed-whitelisted-domains">
											<?php
											//if($video['body']['privacy']['embed'] === 'whitelist') {
											try {
												$domains = $plugin->system()->vimeo()->get_whitelisted_domains( $video['body']['uri'] );

												if ( $domains['status'] === 200 ) {
													foreach ( $domains['body']['data'] as $domain ) {
														echo '<li>' . $domain['domain'] . ' <a href="#" class="submitdelete dgv-delete-domain" data-uri="' . $video['body']['uri'] . '" data-domain="' . $domain['domain'] . '">(' . __( 'remove', 'vimeify' ) . ')</a> </li>';
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
                                <div class="form-row">
                                    <button type="submit" class="button-primary " name="action" id="save" value="1"><?php _e( 'Save', 'vimeify' ); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div><!-- // Embed Privacy -->
			<?php endif; ?>

			<?php if ( $embed_presets_management ): ?>
                <!-- Embed Presets -->
                <div class="metabox-holder">
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="hndle ui-sortable-handle"><?php _e( 'Embed Preset', 'vimeify' ); ?></h2>
                        </div>
                        <div class="inside">
							<?php if ( ! $plugin->system()->vimeo()->supports_embed_presets() ): ?>
                                <p><?php echo sprintf( __( 'Embed presets are only supported by the following plans:', 'vimeify' ) ); ?></p>
                                <ul class="wvv-std-list">
                                    <li>Vimeo PRO</li>
                                    <li>Vimeo Business</li>
                                    <li>Vimeo Premium</li>
                                </ul><p><?php echo sprintf( __( 'Your current plan is %s.', 'vimeify' ), '<strong>' . 'Vimeo ' . ucfirst( $plugin->system()->vimeo()->user_type ) . '</strong>' ); ?></p>

                                <p><a href="https://vimeo.com/upgrade" target="_blank" class="button"><?php _e( 'Upgrade', 'vimeify' ); ?></a></p>
							<?php else: ?>

								<?php
								$current_preset_uri  = empty( $embed_preset_uri ) ? 'default' : $vimeo_formatter->embed_preset_uri_to_id( $embed_preset_uri );
								$current_preset_name = ! empty( $current_preset_uri ) && ( 'default' != $current_preset_uri ) ? $plugin->system()->vimeo()->get_embed_preset_name( $current_preset_uri ) : __( 'Default (no preset)', 'vimeify' );

								?>
                                <form id="dgv-video-save-embed-preset" class="submitbox" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
                                    <div class="form-row">
                                        <label for="embed_preset_uri">
											<?php _e( 'Embed preset', 'vimeify' ); ?>
                                        </label>
                                        <select id="embed_preset_uri" name="embed_preset_uri" class="dgv-select2" data-action="dgv_embed_preset_search" data-placeholder="<?php _e( 'Select preset...', 'vimeify' ); ?>">
											<?php if ( ! empty( $current_preset_uri ) ): ?>
                                                <option selected value="<?php echo esc_attr( $current_preset_uri ); ?>"><?php echo esc_html( $current_preset_name ); ?></option>
											<?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="form-row">
                                        <input type="hidden" name="video_uri" value="<?php echo esc_attr( $video['body']['uri'] ); ?>">
                                        <button type="submit" class="button-primary " name="action" id="save" value="1"><?php _e( 'Save', 'vimeify' ); ?></button>
                                    </div>
                                </form>

							<?php endif; ?>
                        </div>
                    </div>
                </div><!-- // Embed Presets -->
			<?php endif; ?>

            <!-- Folders -->
			<?php if ( $folders_management ): ?>
                <div class="metabox-holder">
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="hndle ui-sortable-handle"><?php _e( 'Folder', 'vimeify' ); ?></h2>
                        </div>
                        <div class="inside">
							<?php if ( ! $plugin->system()->vimeo()->supports_folders() ): ?>
                                <p><?php echo __( 'Folders are not supported without <strong>interact</strong> scope.', 'vimeify' ); ?></p>

                                <p><?php echo __( 'If you want to use Folders, please go to developer.vimeo.com/apps, regenerate your access token, add <strong>Interact</strong> to the scopes and finally replace your old token in Vimeo settings on your site.' ); ?></p>
							<?php else: ?><?php
								$current_folder_uri  = empty( $folder_uri ) ? 'default' : $folder_uri;
								$current_folder_name = ! empty( $current_folder_uri ) && ( 'default' != $current_folder_uri ) ? $plugin->system()->vimeo()->get_folder_name( $current_folder_uri ) : __( 'Default (no folder)', 'vimeify' );
								?>
                                <form id="dgv-video-save-folders" class="submitbox" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
                                    <div class="form-row">
                                        <label for="folder_uri"><?php _e( 'Video folder', 'vimeify' ); ?></label>
                                        <select id="folder_uri" name="folder_uri" class="dgv-select2" data-action="dgv_folder_search" data-placeholder="<?php _e( 'Select folder...', 'vimeify' ); ?>">
                                            <option value="default" <?php selected( 'default', $current_folder_uri ); ?>><?php _e( 'Default (no folder)', 'vimeify' ); ?></option>
											<?php if ( ! empty( $current_folder_uri ) ): ?>
                                                <option selected value="<?php echo esc_attr( $current_folder_uri ); ?>"><?php echo esc_html( $current_folder_name ); ?></option>
											<?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="form-row">
                                        <input type="hidden" name="video_uri" value="<?php echo esc_attr( $video['body']['uri'] ); ?>">
                                        <button type="submit" class="button-primary" name="action" id="save" value="1"><?php _e( 'Save', 'vimeify' ); ?></button>
                                    </div>
                                </form>
							<?php endif; ?>
                        </div>
                    </div>
                </div><!-- // Folders -->
			<?php endif; ?>

        </div>
    </div>

<?php endif; ?>



