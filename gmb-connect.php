<?php 
/**
 * Google My Business Connect
 *
 * @package           GoogleMyBusinessConnect
 * @author            Lights On Creative
 * @copyright         2021 Lights On Creative
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Google My Business Connect
 * Plugin URI:        https://lightsoncreative.com
 * Description:       This plugin uses the Google My Business API to pull in and/or modify client's GMB information.
 * Version:           1.0.0
 * Requires at least: 5.4
 * Requires PHP:      7.2
 * Author:            Lights On Creative
 * Author URI:        https://lightsoncreative.com
 * Text Domain:       gmbconnect
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://lightsoncreative.com
 */ 


 defined( 'ABSPATH' ) or exit;

 
 spl_autoload_register( function ( $class ) {

    list( $plugin_space ) = explode( '\\', $class );
    if ( $plugin_space !== 'GMBConnect' ) {
      return;
    }

    /*
    * This folder can be both "gmb-connect" and "gmb-connect-pro".
    */
    $plugin_dir = basename( __DIR__ );

    // Default directory for all code is plugin's /src/.
    $base_dir = plugin_dir_path( __DIR__ ) . $plugin_dir . '/src/';

    // Get the relative class name.
    $relative_class = substr( $class, strlen( $plugin_space ) + 1 );

    // Prepare a path to a file.
    $file = wp_normalize_path( $base_dir . $relative_class . '.php' );

    // If the file exists, require it.
    if ( is_readable( $file ) ) {
      /** @noinspection PhpIncludeInspection */
      require_once $file;
    }
  } );

 register_activation_hook( __FILE__, [ 'GMBConnect\DBTables', 'setup_tables' ] );


/**
 * Setup the rest of the plugin
 */
\GMBConnect\Loader::get_instance();



 