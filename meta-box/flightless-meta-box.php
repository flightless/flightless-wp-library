<?php
/**
 * @package Flightless
 * @version 1.0
 */
/*
Plugin Name: Flightless Meta Box
Plugin URI: https://github.com/flightless/flightless-wp-library
Description: Meta boxes
Author: Flightless, Inc.
Author URI: http://flightless.us/
Contributors: jbrinley
Version: 1.0
*/

include_once('Flightless_Meta_Box.php');
Flightless_Meta_Box::init();

/**
 * @param string $post_type
 * @param string $meta_box_class
 * @param array $args
 * @return Flightless_Meta_Box
 */
function add_flightless_meta_box( $post_type, $meta_box_class, $args = array() ) {
	if ( !class_exists($meta_box_class) ) {
		return FALSE;
	}
	return new $meta_box_class( $post_type, $args );
}