<?php

class Flightless_Network_Admin_Page extends Flightless_Admin_Page {
	protected $parent = 'settings.php';
	protected $permission = 'manage_network';


	public function save() {
		// settings API doesn't work at the network level, so we save it ourselves
		check_admin_referer($this->slug.'-options');

		$this->save_each_field();

		/**
		 *  Handle settings errors
		 */
		// If no settings errors were registered add a general 'updated' message.
		if ( !count( get_settings_errors() ) )
			add_settings_error('general', 'settings_updated', __('Settings saved.'), 'updated');
		set_transient('settings_errors', get_settings_errors(), 30);

		wp_redirect(add_query_arg(array('settings-updated' => 'true'), $this->page_url()));
		exit();
	}

	protected function add_hooks() {
		add_action( 'network_admin_menu', array( $this, 'register' ), 10, 0 );
		add_action( 'network_admin_edit_'.$this->slug, array( $this, 'save' ), 10, 0);
	}

	protected function action_url() {
		return add_query_arg(array('action' => $this->slug), network_admin_url('edit.php'));
	}

	protected function save_each_field() {
		// recreate part of options.php

		$whitelist_options = apply_filters( 'whitelist_options', array() );
		if ( !isset( $whitelist_options[ $this->slug ] ) )
			wp_die( __( '<strong>ERROR</strong>: options page not found.' ) );

		$options = $whitelist_options[ $this->slug ];

		if ( $options ) {
			foreach ( $options as $option ) {
				$option = trim($option);
				$value = null;
				if ( isset($_POST[$option]) )
					$value = $_POST[$option];
				if ( !is_array($value) )
					$value = trim($value);
				$value = stripslashes_deep($value);
				update_site_option($option, $value);
			}
		}
	}

	protected function page_url() {
		$url = parent::page_url();
		$url = str_replace(admin_url(), network_admin_url(), $url);
		return $url;
	}
}
