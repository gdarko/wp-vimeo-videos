<?php
/**
 * Query all videos
 * @param $args
 *
 * @return array
 */
function dgv_videos_get($args) {
	$posts = get_posts(array(
		'post_type' => DGV_PT_VU,
		'posts_per_page' => $args['number'],
		'offset' => $args['offset'],
		'post_status' => 'publish'
	));
	return $posts;
}

/**
 * Get total videos count
 * @return int
 */
function dgv_videos_count() {
	$posts = get_posts(array(
		'post_type' => DGV_PT_VU,
		'posts_per_page' => -1,
		'post_status' => 'publish'
	));
	return count($posts);
}

/**
 * Returns view, allows to pass data
 * @param $view
 * @param array $data
 *
 * @return false|string
 */
function dgv_get_view($view, $data = array()) {
	if(is_array($data) && !empty($data)) {
		extract($data);
	}
	$path = apply_filters('dgv_view_path', DGV_PATH . 'views/'.$view.'.php', $view, $data);
	if(file_exists($path)) {
		ob_start();
		include($path);
		return ob_get_clean();
	} else {
		error_log(__('DGV: Template file does not exists.', 'dg-vimeo-videos'));
		return '';
	}
}

/**
 * Extract vimeo ID out of the vimeo CREATE/UPLOAD response
 * @param $post_ID
 *
 * @return mixed
 */
function dgv_get_vimeo_id($post_ID) {
	$response = get_post_meta( $post_ID, 'dgv_response', true );
	$vimeo_id = str_replace( '/videos/', '', $response );
	return $vimeo_id;
}