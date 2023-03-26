<?php

namespace Vimeify\Core\Abstracts\Interfaces;

use Vimeify\Core\Utilities\TemporaryDirectory;

interface SystemInterface {

	/**
	 * The Vimeo API
	 * @return VimeoInterface
	 */
	public function vimeo();

	/**
	 * The Database API
	 * @return DatabaseInterface
	 */
	public function database();

	/**
	 * The Settings API
	 * @return SettingsInterface
	 */
	public function settings();

	/**
	 * The Settings API
	 * @return LoggerInterface
	 */
	public function logger();

	/**
	 * The Views API
	 * @return ViewsInterface
	 */
	public function views();

	/**
	 * The Requests API
	 * @return RequestsInterface
	 */
	public function requests();

	/**
	 * The config data
	 * @return array
	 */
	public function config( $key = null, $default = null );

	/**
	 * Returns the tmp dir path
	 * @return TemporaryDirectory
	 */
	public function tmp_dir();

}