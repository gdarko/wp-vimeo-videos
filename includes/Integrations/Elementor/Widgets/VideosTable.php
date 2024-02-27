<?php

namespace Vimeify\Core\Integrations\Elementor\Widgets;

use Vimeify\Core\Frontend\Views\VideosTable as VideosTableView;

/**
 * Elementor oEmbed Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class VideosTable extends \Elementor\Widget_Base {

	/**
	 * The view
	 * @var VideosTableView $view
	 */
	private $view;

	/**
	 * Constructor
	 *
	 * @param $data
	 * @param $args
	 *
	 * @throws \Exception
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		$plugin = vimeify()->plugin();

		$this->view = apply_filters( 'dgv_frontend_view_videos_table', null, $plugin );
		if ( is_null( $this->view ) ) {
			$this->view = new VideosTableView( $plugin );
		}
	}

	/**
	 * Get widget name.
	 *
	 * Retrieve oEmbed widget name.
	 *
	 * @return string Widget name.
	 * @since 1.0.0
	 * @access public
	 */
	public function get_name() {
		return 'videos-table';
	}

	/**
	 * Get style dependencies.
	 *
	 * Retrieve the list of style dependencies the element requires.
	 *
	 * @return array
	 * @since 1.9.0
	 * @access public
	 *
	 */
	public function get_style_depends() {
		return $this->view->get_required_styles();
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve oEmbed widget title.
	 *
	 * @return string Widget title.
	 * @since 1.0.0
	 * @access public
	 */
	public function get_title() {
		return esc_html__( 'Vimeify Videos Table', 'vimeify' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve oEmbed widget icon.
	 *
	 * @return string Widget icon.
	 * @since 1.0.0
	 * @access public
	 */
	public function get_icon() {
		return 'eicon-video-camera';
	}

	/**
	 * Get custom help URL.
	 *
	 * Retrieve a URL where the user can get more information about the widget.
	 *
	 * @return string Widget help URL.
	 * @since 1.0.0
	 * @access public
	 */
	public function get_custom_help_url() {
		return 'https://vimeify.com/codex/elementor';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the oEmbed widget belongs to.
	 *
	 * @return array Widget categories.
	 * @since 1.0.0
	 * @access public
	 */
	public function get_categories() {
		return [ 'general' ];
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the oEmbed widget belongs to.
	 *
	 * @return array Widget keywords.
	 * @since 1.0.0
	 * @access public
	 */
	public function get_keywords() {
		return [ 'vimeo', 'video', 'videos', 'table' ];
	}

	/**
	 * Register oEmbed widget controls.
	 *
	 * Add input fields to allow the user to customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Content', 'vimeify' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$authors = $this->view->get_authors();
		$options = [
			'any' => esc_html__( 'Any', 'vimeify' ),
		];
		if ( ! empty( $authors ) ) {
			foreach ( $authors as $key => $value ) {
				$options[ $key ] = $value;
			}
		}

		$this->add_control(
			'author',
			[
				'label'   => esc_html__( 'Author', 'vimeify' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'any',
				'options' => $options,
			]
		);

		$categories = $this->view->get_categories();
		$options    = [
			'any' => esc_html__( 'Any', 'vimeify' ),
		];
		if ( ! empty( $categories ) ) {
			foreach ( $categories as $key => $value ) {
				$options[ $key ] = $value;
			}
		}
		$this->add_control(
			'categories',
			[
				'label'       => esc_html__( 'Categories', 'vimeify' ),
				'label_block' => true,
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'default'     => [ 'any' ],
				'options'     => $options,
				'multiple'    => true,
			]
		);

		$this->add_control(
			'order',
			[
				'label'   => esc_html__( 'Order Direction', 'vimeify' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'asc',
				'options' => [
					'desc' => esc_html__( 'DESC', 'vimeify' ),
					'asc'  => esc_html__( 'ASC', 'vimeify' ),
				],
			]
		);

		$this->add_control(
			'orderby',
			[
				'label'   => esc_html__( 'Order By', 'vimeify' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'title',
				'options' => [
					'title' => esc_html__( 'Title', 'vimeify' ),
					'date'  => esc_html__( 'Date', 'vimeify' ),
				],
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'   => esc_html__( 'Videos number', 'vimeify' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 500,
				'step'    => 1,
				'default' => max( 3, (int) get_option( 'posts_per_page' ) ),
			]
		);

		$this->add_control(
			'show_pagination',
			[
				'label' => esc_html__( 'Show Pagination', 'vimeify' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'vimeify' ),
				'label_off' => esc_html__( 'Hide', 'vimeify' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Render oEmbed widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();



		$params = [
			'posts_per_page'  => ! empty( $settings['posts_per_page'] ) ? (int) $settings['posts_per_page'] : 6,
			'author'          => ! empty( $settings['author'] ) && is_numeric( $settings['author'] ) && (int) $settings['author'] >= 1 ? (int) $settings['author'] : 'any',
			'categories'      => ! empty( $settings['categories'] ) && is_array( $settings['categories'] ) ? array_filter( array_map( 'intval', $settings['categories'] ), function ( $v ) {
				return $v > 0;
			} ) : [],
			'order'           => ! empty( $settings['order'] ) ? $settings['order'] : 'desc',
			'order_by'        => ! empty( $settings['orderby'] ) ? (int) $settings['orderby'] : 'date',
			'show_pagination' => isset( $settings['show_pagination'] ) ? 'yes' === $settings['show_pagination'] : false,
		];


		echo '<div class="vimeify-videos-table-widget">';
		echo $this->view->output( $params );
		echo '</div>';

	}

}