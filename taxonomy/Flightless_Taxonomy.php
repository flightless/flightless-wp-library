<?php

class Flightless_Taxonomy {

	private static function init() {
		add_action('plugins_loaded', array(__CLASS__, 'check_rewrite_rules'), 10000, 0);
	}

	/**
	 * @static
	 *
	 * If the post types registered through this API have changed,
	 * rewrite rules need to be flushed.
	 */
	public static function check_rewrite_rules() {
		if ( get_option('Flightless_Taxonomy_registry') != self::$taxonomy_registry ) {
			add_action('init', 'flush_rewrite_rules', 100, 0);
			update_option('Flightless_Taxonomy_registry', self::$taxonomy_registry);
		}
	}

	/**
	 * Get a registered taxonomy object
	 * @static
	 * @param $taxonomy
	 * @return Flightless_Taxonomy|NULL
	 */
	public static function get_taxonomy( $taxonomy ) {
		if ( isset(self::$taxonomy_registry[$taxonomy]) ) {
			return self::$taxonomy_registry[$taxonomy];
		}
		return NULL;
	}

	private static $taxonomy_registry = array();

	/**
	 * The ID of the taxonomy
	 * @var string
	 */
	protected $taxonomy = '';
	protected $label_singular = '';
	protected $label_plural = '';
	protected $default_terms = array();


	public $post_types = array();
	public $public = TRUE;
	public $publicly_queryable = NULL;
	public $show_ui = NULL;
	public $show_in_nav_menus = NULL;
	public $show_tagcloud = NULL;
	public $hierarchical = FALSE;
	public $update_count_callback = NULL;
	public $query_var = NULL;
	public $capabilities = array();
	public $slug = NULL;



	public function __construct( $taxonomy ) {
		$this->taxonomy = $taxonomy;
		$this->add_hooks();
	}

	public function set_default_terms( $terms = array() ) {
		$this->default_terms = $terms;
	}

	public function register_default_terms() {
		// don't do anything if the taxonomy already has terms
		if ( !$this->default_terms || get_terms($this->taxonomy, array('hide_empty'=>FALSE)) ) {
			return;
		}
		foreach( $this->default_terms as $slug => $term ) {
			$args = array();
			if ( !is_numeric($slug) ) {
				$args['slug'] = $slug;
			}
			wp_insert_term($term, $this->taxonomy, $args);
		}
	}

	/**
	 * Hook the post type into worpress
	 */
	protected function add_hooks() {
		add_action( 'init', array( $this, 'register_taxonomy' ), 8, 0 );
		add_action( 'plugins_loaded', array( $this, 'register_default_terms' ), 8, 0 );
	}

	/**
	 * Register this post type with WordPress
	 *
	 * @return void
	 */
	public function register_taxonomy() {
		register_taxonomy($this->taxonomy, $this->post_types, $this->taxonomy_args());
		self::$taxonomy_registry[$this->taxonomy] = $this;
	}

	/**
	 * Build the args array for the post type definition
	 *
	 * @return array
	 */
	protected function taxonomy_args() {
		$args = array(
			'labels' => $this->taxonomy_labels(),
			'public' => $this->public,
			'publicly_queryable' => $this->publicly_queryable,
			'show_ui' => $this->show_ui,
			'show_in_nav_menus' => $this->show_in_nav_menus,
			'show_tagcloud' => $this->show_tagcloud,
			'hierarchical' => $this->hierarchical,
			'update_count_callback' => $this->update_count_callback,
			'query_var' => empty($this->query_var)?$this->taxonomy:$this->query_var,
			'rewrite' => array(
				'slug' => $this->get_slug(),
				'with_front' => FALSE,
				'hierarchical' => $this->hierarchical,
			),
			'capabilities' => $this->capabilities,
		);

		$args = apply_filters('flightless_custom_taxonomy_args', $args, $this->taxonomy);
		$args = apply_filters('flightless_custom_taxonomy_args_'.$this->taxonomy, $args);

		return $args;
	}

	/**
	 * Build the labels array for the post type definition
	 *
	 * @param string $single
	 * @param string $plural
	 * @return array
	 */
	protected function taxonomy_labels( $single = '', $plural = '' ) {
		$single = $single?$single:$this->get_label('singular');
		$plural = $plural?$plural:$this->get_label('plural');
		$labels = array(
			'name' => $plural,
			'singular_name' => $single,
			'search_items' => sprintf(__('Search %s', 'flightless'),$plural),
			'popular_items' => sprintf(__('Popular %s', 'flightless'),$plural),
			'all_items' => sprintf(__('All %s', 'flightless'),$plural),
			'parent_item' => sprintf(__('Parent %s', 'flightless'),$single),
			'parent_item_colon' => sprintf(__('Parent %s:', 'flightless'),$single),
			'edit_item' => sprintf(__('Edit %s', 'flightless'),$single),
			'update_item' => sprintf(__('Update %s', 'flightless'),$single),
			'add_new_item' => sprintf(__('Add New %s', 'flightless'),$single),
			'new_item_name' => sprintf(__('New %s Name', 'flightless'),$single),
			'separate_items_with_commas' => sprintf(__('Separate %s with commas', 'flightless'),$plural),
			'add_or_remove_items' => sprintf(__('Add or remove %s', 'flightless'),$plural),
			'choose_from_most_used' => sprintf(__('Choose from the most used %s', 'flightless'),$plural),
			'menu_name' => $plural
		);

		$labels = apply_filters('flightless_custom_taxonomy_labels', $labels, $this->taxonomy);
		$labels = apply_filters('flightless_custom_taxonomy_labels_'.$this->taxonomy, $labels);

		return $labels;
	}

	public function set_label( $singular = '', $plural = '' ) {
		if ( !$singular ) {
			$singular = $this->taxonomy;
		}
		if ( !$plural ) {
			$plural = $singular.'s'; // a highly robust technique for making any word plural
		}
		$this->label_singular = $singular;
		$this->label_plural = $plural;
	}

	public function get_label( $quantity = 'singular' ) {
		switch ( $quantity ) {
			case 'plural':
				if ( !$this->label_plural ) {
					$this->set_label($this->label_singular);
				}
				return $this->label_plural;
			default:
				if ( !$this->label_singular ) {
					$this->set_label();
				}
				return $this->label_singular;
		}
	}

	/**
	 * Return the slug of the taxonomy
	 *
	 * @return string supertype slug
	 */
	public function get_slug() {
		return $this->slug?$this->slug:$this->taxonomy;
	}
}
