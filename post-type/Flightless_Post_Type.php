<?php

class Flightless_Post_Type {

	public static function init() {
		add_action('init', array(__CLASS__, 'check_rewrite_rules'), 10000, 0);
	}

	/**
	 * @static
	 *
	 * If the post types registered through this API have changed,
	 * rewrite rules need to be flushed.
	 */
	public static function check_rewrite_rules() {
		if ( get_option('flightless_cpt_registry') != self::$post_type_registry ) {
			add_action('init', 'flush_rewrite_rules', 100, 0);
			update_option('flightless_cpt_registry', self::$post_type_registry);
		}
	}

	private static $post_type_registry = array();

	/**
	 * Hello. What's your name?
	 * @var string
	 */
	public $post_type_label_singular = '';
	public $post_type_label_plural = '';
	/**
	 * @var string The label that will be shown on the front end title bar
	 */
	public $front_end_label = '';

	protected $supports = array('title', 'editor', 'author', 'thumbnail', 'excerpt');

	public $description = 'A custom post type';
	public $hierarchical = FALSE;

	public $capability_type = 'post';
	public $capabilities = array();
	public $map_meta_cap = FALSE;

	public $menu_icon = NULL;
	public $menu_position = 5;

	public $public = TRUE;
	public $publicly_queryable = NULL;
	public $exclude_from_search = NULL;
	public $has_archive = TRUE;
	public $slug = '';
	public $query_var = TRUE;

	public $show_ui = NULL;
	public $show_in_menu = NULL;
	public $show_in_nav_menus = NULL;
	public $show_in_admin_bar = NULL;

	public $permalink_epmask = EP_PERMALINK;
	public $can_export = TRUE;
	public $taxonomies = array();

	/**
	 * The ID of the post type
	 * @var string
	 */
	protected $post_type = '';

	public function __construct( $post_type ) {
		$this->post_type = $post_type;
		$this->add_hooks();
	}

	/**
	 * Hook the post type into worpress
	 */
	protected function add_hooks() {
		add_action('init', array($this, 'register_post_type'), 10, 0);
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ), 10, 1 );
		//add_filter('template_include', array( $this, 'select_post_template' ), 10, 1 );
		add_filter('post_type_archive_title', array($this, 'filter_post_type_archive_title'), 10, 1);
	}

	/**
	 * Register this post type with WordPress
	 *
	 * @return void
	 */
	public function register_post_type() {
		$response = register_post_type($this->post_type, $this->post_type_args());
		if ( !is_wp_error($response) ) {
			self::$post_type_registry[$this->post_type] = get_class($this);
		}
	}

	/**
	 * The the post type defined by this class
	 *
	 * @param string $format Either 'id' (for the post type ID) or 'object' (for the WP post type object)
	 * @return object|string
	 */
	public function get_post_type( $format = 'id' ) {
		switch ( $format ) {
			case 'object':
				return get_post_type_object($this->post_type);
			default:
				return $this->post_type;
		}
	}

	/**
	 * Return the slug of the supertype
	 *
	 * @return string supertype slug
	 */
	public function get_slug() {
		return $this->slug?$this->slug:$this->post_type;
	}

	/**
	 * Build the args array for the post type definition
	 *
	 * @return array
	 */
	protected function post_type_args() {
		$args = array(
			'labels' => $this->post_type_labels(),
			'description' => $this->description,
			'public' => $this->public,
			'publicly_queryable' => $this->publicly_queryable,
			'show_ui' => $this->show_ui,
			'show_in_menu' => $this->show_in_menu,
			'show_in_nav_menus' => $this->show_in_nav_menus,
			'menu_icon' => $this->menu_icon,
			'capability_type' => $this->capability_type,
			'capabilities' => $this->capabilities,
			'hierarchical' => $this->hierarchical,
			'supports' => $this->supports,
			'has_archive' => $this->has_archive,
			'taxonomies' => $this->taxonomies,
			'rewrite' => array(
				'slug' => $this->get_slug(),
				'with_front' => FALSE,
			),
			'query_var' => $this->query_var,
			'menu_position' => $this->menu_position,
			'exclude_from_search' => $this->exclude_from_search,
			'can_export' => $this->can_export,
		);

		$args = apply_filters('flightless_custom_post_type_args', $args, $this->post_type);
		$args = apply_filters('flightless_custom_post_type_args_'.$this->post_type, $args);

		return $args;
	}

	/**
	 * Build the labels array for the post type definition
	 *
	 * @param string $single
	 * @param string $plural
	 * @return array
	 */
	protected function post_type_labels( $single = '', $plural = '' ) {
		$single = $single?$single:$this->post_type_label('singular');
		$plural = $plural?$plural:$this->post_type_label('plural');
		$labels = array(
			'name' => $plural,
			'singular_name' => $single,
			'add_new' => __('Add New', 'flightless'),
			'add_new_item' => sprintf(__('Add New %s', 'flightless'),$single),
			'edit_item' => sprintf(__('Edit %s', 'flightless'),$single),
			'new_item' => sprintf(__('New %s', 'flightless'),$single),
			'view_item' => sprintf(__('View %s', 'flightless'),$single),
			'search_items' => sprintf(__('Search %s', 'flightless'),$plural),
			'not_found' => sprintf(__('No %s Found', 'flightless'),$plural),
			'not_found_in_trash' => sprintf(__('No %s Found in Trash', 'flightless'),$plural),
			'menu_name' => $plural,
		);

		$labels = apply_filters('flightless_custom_post_type_labels', $labels, $this->post_type);
		$labels = apply_filters('flightless_custom_post_type_labels_'.$this->post_type, $labels);

		return $labels;
	}



	/**
	 * Add messaging for this custom post type.
	 *
	 * @param array $messages list of alert messages
	 * @return array
	 */
	public function post_updated_messages( $messages = array() ) {
		global $post, $post_ID;

		$messages[$this->post_type] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __('%s updated. <a href="%s">View the %s...</a>', 'flightless'), $this->post_type_label(), esc_url( get_permalink($post_ID) ), strtolower($this->post_type_label()) ),
			2 => __('Custom field updated.', 'flightless'),
			3 => __('Custom field deleted.', 'flightless'),
			4 => sprintf( __('%s updated.', 'flightless'), $this->post_type_label() ),
			5 => isset($_GET['revision']) ? sprintf( __('%s restored to revision from %s', 'flightless'), $this->post_type_label(), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __('%s published. <a href="%s">View %s</a>', 'flightless'), $this->post_type_label(), esc_url( get_permalink($post_ID) ), strtolower($this->post_type_label()) ),
			7 => sprintf( __('%s saved.', 'flightless'), $this->post_type_label() ),
			8 => sprintf( __('%s submitted. <a target="_blank" href="%s">Preview %s</a>', 'flightless'), $this->post_type_label(), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ), strtolower($this->post_type_label()) ),
			9 => sprintf( __('%3$s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview %s</a>', 'flightless'),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ), strtolower($this->post_type_label()) ),
			10 => sprintf( __('%s draft updated. <a target="_blank" href="%s">Preview %s</a>', 'flightless'), $this->post_type_label(), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ), strtolower($this->post_type_label()) ),
		);

		return $messages;
	}

	public function post_type_label( $quantity = 'singular' ) {
		switch ( $quantity ) {
			case 'plural':
				if ( !$this->post_type_label_plural ) {
					$this->set_post_type_label($this->post_type_label_singular);
				}
				return $this->post_type_label_plural;
			default:
				if ( !$this->post_type_label_singular ) {
					$this->set_post_type_label();
				}
				return $this->post_type_label_singular;
		}
	}

	public function set_post_type_label( $singular = '', $plural = '' ) {
		if ( !$singular ) {
			$singular = $this->post_type;
		}
		if ( !$plural ) {
			$plural = $singular.'s'; // a highly robust technique for making any word plural
		}
		$this->post_type_label_singular = $singular;
		$this->post_type_label_plural = $plural;
	}

	/**
	 * Get the label to display for this post type on public-facing pages
	 *
	 * @return string
	 */
	public function public_label() {
		if ( $this->front_end_label ) {
			return $this->front_end_label;
		} else {
			return $this->post_type_label('plural');
		}
	}

	public function filter_post_type_archive_title( $title ) {
		if ( is_post_type_archive($this->post_type) ) {
			$title = $this->public_label();
		}
		return $title;
	}


	public function add_support( $features = array() ) {
		if ( !is_array($features) ) {
			$features = array($features);
		}
		$this->supports = array_unique(array_merge($this->supports, $features));
		return $this->supports;
	}

	public function remove_support( $features = array() ) {
		if ( !is_array($features) ) {
			$features = array($features);
		}
		$this->supports = array_diff($this->supports, $features);
		return $this->supports;
	}
}
