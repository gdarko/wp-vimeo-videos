<?php

namespace Vimeify\Core\Utilities;

use Vimeify\Core\Abstracts\Interfaces\PluginInterface;
use Vimeify\Core\Plugin;

class ProcessManager {

	private $processes = array();

	/**
	 * List of instances
	 * @var array
	 */
	private $instances = array();

	/**
	 * The plugin instance
	 * @var Plugin
	 */
	protected $plugin = null;

	/**
	 * The class singleton isntance
	 * @var ProcessManager
	 */
	private static $instance = null;

	/**
	 * ProcessManager constructor.
	 *
	 * @param Plugin $plugin
	 */
	private function __construct( $plugin = null ) {
		if ( ! is_null( $plugin ) ) {
			$this->plugin = $plugin;
		}
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Init
	 */
	public function init() {
		foreach ( $this->processes as $key => $process ) {
			if ( class_exists( $process ) ) {
				$this->instances[ $key ] = new $process( $this->plugin );
			}
		}
	}

	/**
	 * Returns the process
	 *
	 * @param $key
	 *
	 * @return null|\DGV_Background_Process|\DGV_Async_Request
	 */
	public function get( $key ) {
		if ( isset( $this->instances[ $key ] ) ) {
			return $this->instances[ $key ];
		} else {
			return null;
		}
	}

	/**
	 * Push process
	 *
	 * @param $key
	 * @param $className
	 */
	public function push( $key, $className ) {
		$this->processes[ $key ] = $className;
	}

	/**
	 * Return all processes
	 *
	 * @return DGV_Background_Process[]|DGV_Async_Request[]
	 */
	public function all() {
		return $this->instances;
	}

	/**
	 * Set the plugin instance
	 *
	 * @param Plugin $plugin
	 *
	 * @return void
	 */
	public function set_plugin( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Get the instance
	 *
	 * @param Plugin|PluginInterface $plugin
	 *
	 * @return ProcessManager
	 */
	public static function instance( $plugin = null ) {
		if ( self::$instance == null ) {
			self::$instance = new self( $plugin );
		}

		return self::$instance;
	}

	/**
	 * Create instance
	 *
	 * @param Plugin|PluginInterface $plugin
	 *
	 * @return void
	 */
	public static function create( $plugin ) {
		self::instance( $plugin )->set_plugin( $plugin );
	}

}