<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress or ClassicPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.tukutoi/
 * @since             0.0.1
 * @package           Tkt_Template_Builder
 *
 * @wordpress-plugin
 * Plugin Name:       TukuToi Template Builder
 * Plugin URI:        https://plugin.com/tkt-template-builder-uri/
 * Description:       TukuToi Template Builder allows you to create any kind of Template for your WordPress or ClassicPress website, directly from within the Admin area, without editing PHP Files.
 * Version:           1.3.0
 * Author:            bedas
 * Requires at least: 4.9.0
 * Tested up to:      5.8
 * Author URI:        https://www.tukutoi//
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tkt-template-builder
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TKT_TEMPLATE_BUILDER_VERSION', '1.3.0' );

/**
 * The code that runs during plugin activation.
 *
 * This action is documented in includes/class-tkt-template-builder-activator.php
 * Full security checks are performed inside the class.
 */
function tkt_tmplt_bldr_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tkt-template-builder-activator.php';
	Tkt_Template_Builder_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 *
 * This action is documented in includes/class-tkt-template-builder-deactivator.php
 * Full security checks are performed inside the class.
 */
function tkt_tmplt_bldr_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tkt-template-builder-deactivator.php';
	Tkt_Template_Builder_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'tkt_tmplt_bldr_activate' );
register_deactivation_hook( __FILE__, 'tkt_tmplt_bldr_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tkt-template-builder.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * Generally you will want to hook this function, instead of callign it globally.
 * However since the purpose of your plugin is not known until you write it, we include the function globally.
 *
 * @since    0.0.1
 */
function tkt_tmplt_bldr_run() {

	$plugin = new Tkt_Template_Builder();
	$plugin->run();

}
add_action( 'init', 'tkt_tmplt_bldr_run' );
