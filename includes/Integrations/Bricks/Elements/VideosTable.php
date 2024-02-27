<?php

namespace Vimeify\Core\Integrations\Bricks\Elements;

use Vimeify\Core\Frontend\Views\VideosTable as VideosTableView;

class VideosTable extends \Bricks\Element
{

    /**
     * The video View
     * @var VideosTableView
     */
    private $view;

    // Element properties
    public $category = 'general';
    public $name = 'vimeify_videos_table';
    public $icon = 'ti-vimeo-alt';
    public $css_selector = '.vimeify-bricks-video';
    public $scripts = [];

    /**
     * Constructor
     *
     * @param $element
     *
     * @throws \Exception
     */
    public function __construct($element = null)
    {
        parent::__construct($element);
        $plugin = vimeify()->plugin();

        $this->view = apply_filters('dgv_frontend_view_videos_table', null, $plugin);
        if (is_null($this->view)) {
            $this->view = new VideosTableView($plugin);
        }

    }

    /**
     * The video label
     * @return array|string|string[]
     */
    public function get_label()
    {
        return esc_html__('Vimeify: Videos Table', 'vimeify');
    }

    /**
     * The control groups
     * @return void
     */
    public function set_control_groups()
    {
        $this->control_groups['query'] = [
            'title' => esc_html__('Query', 'vimeify'),
            'tab'   => 'content',
        ];

        /*$this->control_groups['query'] = [
            'title' => esc_html__( 'Settings', 'vimeify' ),
            'tab' => 'query',
        ];*/
    }

    /**
     * The builder controls
     * @return void
     */
    public function set_controls()
    {

	    $authors = $this->view->get_authors();
	    $options = [
		    'any' => esc_html__( 'Any', 'vimeify' ),
	    ];
	    if ( ! empty( $authors ) ) {
		    foreach ( $authors as $key => $value ) {
			    $options[ $key ] = $value;
		    }
	    }

        $this->controls['author'] = [
            'tab'         => 'content',
            'group'       => 'query',
            'label'       => esc_html__('Author', 'vimeify'),
            'type'        => 'select',
            'options'     => $options,
            'inline'      => false,
            'clearable'   => false,
            'pasteStyles' => false,
            'searchable'  => false,
            'default'     => 'Any',
        ];


        $categories = $this->view->get_categories();
        $options    = [
            'any' => esc_html__('Any', 'vimeify'),
        ];
        if ( ! empty($categories)) {
           foreach($categories as $id => $value) {
               $options[$id] = $value;
           }
        }

        $this->controls['categories'] = [
            'tab'         => 'content',
            'group'       => 'query',
            'label'       => esc_html__('Categories', 'vimeify'),
            'type'        => 'select',
            'options'     => $options,
            'inline'      => false,
            'clearable'   => false,
            'pasteStyles' => false,
            'searchable'  => false,
            'default'     => 'Any',
            'multiple'    => true,
        ];

        $this->controls['order'] = [
            'tab'         => 'content',
            'group'       => 'query',
            'label'       => esc_html__('Order Direction', 'vimeify'),
            'type'        => 'select',
            'options'     => [
                'desc' => esc_html__('DESC', 'vimeify'),
                'asc'  => esc_html__('ASC', 'vimeify'),
            ],
            'inline'      => false,
            'clearable'   => false,
            'pasteStyles' => false,
            'default'     => 'desc',
        ];

        $this->controls['orderby'] = [
            'tab'         => 'content',
            'group'       => 'query',
            'label'       => esc_html__('Order By', 'vimeify'),
            'type'        => 'select',
            'options'     => [
                'title' => esc_html__('Title', 'vimeify'),
                'date'  => esc_html__('Date', 'vimeify'),
            ],
            'inline'      => false,
            'clearable'   => false,
            'pasteStyles' => false,
            'default'     => 'date',
        ];

        $this->controls['posts_per_page'] = [
            'tab'     => 'content',
            'group'   => 'query',
            'label'   => esc_html__('Videos number', 'vimeify'),
            'type'    => 'number',
            'units'   => true,
            'default' => max(3, (int) get_option('posts_per_page')),
        ];

		$this->controls['show_pagination'] = [
			'tab'     => 'content',
			'group'   => 'query',
			'label'   => esc_html__( 'Show Pagination', 'vimeify' ),
			'type'    => 'checkbox',
			'default' => true,
		];

	}

    /**
     * Enqueue scripts
     * @return void
     */
    public function enqueue_scripts()
    {
        $this->view->enqueue();
    }

    /**
     * Render element
     * @return void
     */
    public function render()
    {
        $root_classes[] = substr($this->css_selector, 1);
        $this->set_attribute('_root', 'class', $root_classes);

        $params = [
	        'posts_per_page'  => ! empty( $this->settings['posts_per_page'] ) ? (int) $this->settings['posts_per_page'] : 6,
	        'author'          => ! empty( $this->settings['author'] ) && is_numeric( $this->settings['author'] ) && (int) $this->settings['author'] >= 1 ? (int) $this->settings['author'] : 'any',
	        'categories'      => ! empty( $this->settings['categories'] ) && is_array( $this->settings['categories'] ) ? array_filter( array_map( 'intval', $this->settings['categories'] ), function ( $v ) {
		        return $v > 0;
	        } ) : [],
	        'order'           => ! empty( $this->settings['order'] ) ? $this->settings['order'] : 'desc',
	        'order_by'        => ! empty( $this->settings['orderby'] ) ? (int) $this->settings['orderby'] : 'date',
	        'show_pagination' => isset( $this->settings['show_pagination'] ) ? (bool) $this->settings['show_pagination'] : false,
        ];

        echo "<div {$this->render_attributes( '_root' )}>"; // Element root attributes
	    echo $this->view->output($params);
        echo '</div>';
    }

}