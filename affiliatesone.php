<?php
/**
 * 
 * Plugin Name:         Affiliates One
 * Plugin URI :
 * Description:         Get offers from https://www.affiliates.one/
 * Version:             1.0.1
 * Author:              Repon Hossain
 * Author URI:          https://repon.me
 * Text Domain:         affiliates-one
 * Domain Path:         /languages
 * Requires at least:   5.2
 * 
 */


define('AO_VERSION', '1.0.1');

define('AO_DIR', plugin_dir_path( __FILE__ ) );

define('AO_URI', plugin_dir_url( __FILE__ ) );

require_once AO_DIR . 'helpers.php';
require_once AO_DIR . 'class-affiliates-one.php';
$GLOBALS['affiliates_one'] = new AffiliatesOne();