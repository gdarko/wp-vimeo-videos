<?php

namespace Vimeify\Core\Utilities;

class ProcessManager {

	private $processes = array();

	/**
	 * List of instances
	 * @var array
	 */
	private $instances = array();

	/**
	 * The class singleton isntance
	 * @var ProcessManager
	 */
	private static $instance = null;

	/**
	 * ProcessManager constructor.
	 */
	private function __construct()
	{
		add_action('plugins_loaded', array($this, 'init'));
	}

	/**
	 * Init
	 */
	public function init()
	{
		foreach ($this->processes as $key => $process) {
			if (class_exists($process)) {
				$this->instances[$key] = new $process();
			}
		}
	}

	/**
	 * Returns the process
	 * @param $key
	 *
	 * @return null|DGV_Background_Process|DGV_Async_Request
	 */
	public function get($key)
	{
		if (isset($this->instances[$key])) {
			return $this->instances[$key];
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
	public function push($key, $className)
	{
		$this->processes[$key] = $className;
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
	 * @return ProcessManager
	 */
	public static function instance()
	{
		if (self::$instance == null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}