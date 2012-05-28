<?php
/**
 * @package Flightless
 * @version 1.0
 */
/*
Plugin Name: Flightless Admin
Plugin URI: https://github.com/flightless/flightless-wp-library
Description: Admin pages
Author: Flightless, Inc.
Author URI: http://flightless.us
Contributors: jbrinley
Version: 1.0
*/

if ( !class_exists('Flightless_Admin_Page') && !class_exists('Flightless_Network_Admin_Page') ) {
	include_once('Flightless_Admin_Page.php');
	include_once('Flightless_Network_Admin_Page.php');
	include_once('Flightless_Settings_Section.php');
}