<?php
class DGV_Cron {
	/**
	 * Singleton instance
	 * @var DGV_Cron
	 */
	protected static $instance = null;
	/**
	 * Singleton constructor
	 * @return DGV_Cron
	 */
	public static function instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new self;
		}
		return static::$instance;
	}
	public function __construct() {
		add_action('init',array($this, 'init'));
		add_action('dgv_clean_local_videos', array($this, 'clean_local_videos'));
		add_filter('cron_schedules', array($this, 'cron_schedules'), 15, 1);
	}
	public function init() {
		if(!wp_next_scheduled('dgv_clean_local_videos')) {
			wp_schedule_event (time(), 'dgv_every_five_minutes', 'dgv_clean_local_videos');
		}
	}
	public function cron_schedules($schedules){
		if(!isset($schedules["dgv_every_five_minutes"])){
			$schedules["dgv_every_five_minutes"] = array(
				'interval' => 5*60,
				'display' => __('Once every 5 minutes')
			);
		}
		return $schedules;
	}
	public function clean_local_videos() {
		// Get all local videos older than 24 hours.
		$args = array(
			'post_type'   => DGV_PT_VU,
			'posts_per_page' => -1,
			'meta_query'  => array(
				array(
					'key' => 'dgv_local_file',
					'value' => '',
					'compare' => '!='
				)
			),
			'date_query' => array(
				array(
					'before'    => '24 hours ago',
					'inclusive' => true,
				),
			),
		);
		$videos = get_posts($args);
		foreach($videos as $video) {
			$local_path = get_post_meta($video->ID, 'dgv_local_file', true);
			if(!empty($local_path)) {
				if(file_exists($local_path)) {
					@unlink($local_path);
					delete_post_meta($video->ID, 'dgv_local_file');
				}
			}
		}
	}
}
DGV_Cron::instance();