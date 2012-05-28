<?php
/**
 * @package Flightless
 * @version 1.0
 */
/*
Plugin Name: Flightless Post Type
Plugin URI: https://github.com/flightless/flightless-wp-library
Description: Custom post types helper
Author: Flightless, Inc.
Author URI: http://flightless.us
Contributors: jbrinley
Version: 1.0
*/

if ( !class_exists('Flightless_Post_Type') ) {
	include_once('Flightless_Post_Type.php');
	Flightless_Post_Type::init();
}