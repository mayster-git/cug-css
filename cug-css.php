<?php
/**
 * Plugin Name: CuG CSS
 * Plugin URI: 
 * Description: Custom CSS
 * Version: 1.0.0
 * Author: CuG
 * Author URI: 
 * License: GPLv3 or later
 * Text Domain: cug-css
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CUG_CSS__VERSION', time() );
define( 'CUG_CSS__PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'CUG_CSS__URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'CUG_CSS__BASENAME', plugin_basename( __FILE__ ) );

require_once CUG_CSS__PATH . 'includes/plugin.php';
\CuG\CSS\Plugin::get_instance()->init();	



