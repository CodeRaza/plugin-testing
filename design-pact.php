<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://farazahmad.net
 * @since             1.0.0
 * @package           Design_Pact
 *
 * @wordpress-plugin
 * Plugin Name:       Design Pact
 * Plugin URI:        https://designpact.one
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Faraz Ahmad
 * Author URI:        https://farazahmad.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       design-pact
 * Domain Path:       /languages
 */

// var_dump($_SERVER['HTTP_HOST']);
// var_dump(get_option('dpdv_info_email'));
// var_dump(get_option('dpdv_support_email'));
// die;
// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('DESIGN_PACT_VERSION', '1.0.4');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-design-pact-activator.php
 */
function activate_design_pact()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-design-pact-activator.php';
	Design_Pact_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-design-pact-deactivator.php
 */
function deactivate_design_pact()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-design-pact-deactivator.php';
	Design_Pact_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_design_pact');
register_deactivation_hook(__FILE__, 'deactivate_design_pact');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-design-pact.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_design_pact()
{

	$plugin = new Design_Pact();
	$plugin->run();
}
run_design_pact();
add_filter('https_ssl_verify', '__return_false');
