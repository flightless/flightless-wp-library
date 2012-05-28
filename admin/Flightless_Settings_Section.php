<?php

class Flightless_Settings_Section {
	protected $id = 'default';
	protected $title = '';
	protected $callback = NULL;

	public function __construct( $id = 'default', $args = array() ) {
		$this->id = $id?$id:$this->id;

		$args = wp_parse_args($args, array(
			'title' => $this->title,
			'callback' => $this->callback,
		));

		$this->title = $args['title'];
		$this->callback = $args['callback']?$args['callback']:array($this, 'display');
	}

	public function register( $page ) {
		add_settings_section(
			$this->id,
			$this->title,
			array($this, 'display'),
			$page
		);
	}

	public function display() {
		// do nothing by default
	}
}
