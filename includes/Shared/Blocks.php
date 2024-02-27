<?php
/********************************************************************
 * Copyright (C) 2024 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2024 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
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

namespace Vimeify\Core\Shared;

use Vimeify\Core\Abstracts\BaseBlock;
use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Abstracts\Interfaces\CacheInterface;
use Vimeify\Core\Shared\Blocks\Upload;
use Vimeify\Core\Shared\Blocks\Video;
use Vimeify\Core\Shared\Blocks\VideosTable;
use Vimeify\Core\Utilities\Formatters\VimeoFormatter;

class Blocks extends BaseProvider {

	/**
	 * The list of available blocks
	 * @var BaseBlock[]
	 */
	protected $blocks = [];

	/**
	 * Registers specific piece of functionality
	 * @return void
	 */
	public function register() {

		$blocks = [
			new Video( $this->plugin ),
			new VideosTable( $this->plugin ),
		];

		$this->blocks = apply_filters( 'dgv_registered_blocks', $blocks, $this->plugin );

		add_action( 'init', [ $this, 'register_blocks' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'register_block_editor_assets' ] );
	}

	/**
	 * Register the blocks
	 * @return void
	 */
	public function register_blocks() {
		foreach ( $this->blocks as $block ) {
			$block->register_block();
		}
	}

	/**
	 * Register the block editor assets
	 * @return void
	 */
	public function register_block_editor_assets() {
		foreach ( $this->blocks as $block ) {
			$block->register_block_editor_assets();
		}
	}
}