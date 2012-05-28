<?php

class Flightless_Admin_Page {
	protected $slug = '';
	protected $parent = 'options';
	protected $title = '';
	protected $menu_title = '';
	protected $permission = 'manage_options';
	protected $menu_icon = '';
	protected $menu_position = NULL;

	/** @var Flightless_Settings_Section[] */
	protected $sections = array();
	protected $fields = array();

	/**
	 * @param string $slug
	 * @param array $args
	 */
	public function __construct( $slug, $args = array() ) {
		$args = wp_parse_args( $args, array(
			'parent' => $this->parent, // empty to create a top-level page
			'title' => $this->title, // required
			'menu_title' => $this->menu_title,
			'permission' => $this->permission,
			'menu_icon' => $this->menu_icon,
			'menu_position' => $this->menu_position,
		));

		$this->slug = $slug;
		$this->parent = $args['parent'];
		$this->title = $args['title'];
		$this->menu_title = $args['menu_title']?$args['menu_title']:$this->title;
		$this->menu_icon = $args['menu_icon'];
		$this->menu_position = $args['menu_position'];


		if ( !$this->slug ) {
			throw new InvalidArgumentException(__('$slug is required', 'flightless'));
		}
		if ( !$this->title ) {
			throw new InvalidArgumentException(__('"title" argument is required', 'flightless'));
		}

		$this->add_hooks();
	}

	public function get_slug() {
		return $this->slug;
	}

	public function register() {
		$args = array(
			$this->title,
			$this->menu_title,
			$this->permission,
			$this->slug,
			array($this, 'display_page')
		);
		if ( $this->parent ) {
			$function = 'add_'.$this->parent.'_page';
			if ( !function_exists( $function ) ) {
				$function = 'add_submenu_page';
				array_unshift($args, $this->parent);
			}
		} else {
			$function = 'add_menu_page';
			$args[] = $this->menu_icon;
			$args[] = $this->menu_position;
		}
		call_user_func_array( $function, $args);
	}

	public function display_page() {
		$title = $this->title;
		ob_start();
		echo "<form action='".$this->action_url()."' method='post'>";
		settings_fields( $this->slug );
		do_settings_sections( $this->slug );
		submit_button();
		echo "</form>";
		$content = ob_get_clean();

		// if on options-general.php, WP will have already included this
		require_once(ABSPATH . 'wp-admin/options-head.php');
		include('views/settings-page-wrapper.php');
	}

	public function add_section( $id = 'default', $args = array() ) {
		$this->sections[$id] = new Flightless_Settings_Section($id, $args);
	}

	public function add_field( $id, $args = array() ) {
		$args = wp_parse_args($args, array(
			'section' => 'default',
			'title' => $id,
			'callback' => array( $this, 'display_field' ),
			'args' => array(),
			'option' => $id,
			'sanitize_callback' => ''
		));

		if ( empty($this->sections[$args['section']]) ) {
			$this->add_section($args['section']);
		}

		if ( $args['callback'] == array( $this, 'display_field' ) ) {
			$args['args'] = wp_parse_args( $args['args'], array(
				'name' => $args['option'],
				'current' => get_option($args['option']),
				'description' => '',
			));
		}

		$this->add_settings_field($id, $args['title'], $args['callback'], $args['section'], $args['args']);

		$this->register_setting($args['option'], $args['sanitize_callback']);

		$this->fields[$id] = $args;
	}

	public function display_field( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'name' => '',
			'current' => '',
			'description' => '',
		));
		printf( '<input type="text" value="%s" name="%s" />', $args['current'], $args['name'] );
		if ( $args['description'] ) {
			printf( '<p class="description">%s</p>', $args['description'] );
		}
	}

	protected function add_hooks() {
		add_action( 'admin_menu', array( $this, 'register' ), 10, 0 );
	}

	protected function add_settings_field( $id, $title = '', $callback = '', $section = 'default', $args = array() ) {
		add_settings_field(
			$id,
			$title,
			$callback,
			$this->slug,
			$section,
			$args
		);
	}

	protected function register_setting( $option, $sanitize_callback = '' ) {
		register_setting(
			$this->slug,
			$option,
			$sanitize_callback
		);
	}

	protected function action_url() {
		return admin_url('options.php');
	}

	protected function page_url() {
		return menu_page_url($this->slug, FALSE);
	}
}
