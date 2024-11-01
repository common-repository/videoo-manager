<?php
/**
 * @wordpress-plugin
 * Plugin Name:     Videoo.Tv Manager
 * Plugin URI:      https://videoo.tv/
 * Description:     Sets and manages your Videoo.Tv service
 * Author:          Digital Green
 * Author URI:      https://digitalgreen.es/
 * Text Domain:     videoo-manager
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Videoo_Manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'VIDEOO_MANAGER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-videoo-manager-activator.php
 */
function activate_videoo_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-videoo-manager-activator.php';
	Videoo_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-videoo-manager-deactivator.php
 */
function deactivate_videoo_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-videoo-manager-deactivator.php';
	Videoo_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_videoo_manager' );
register_deactivation_hook( __FILE__, 'deactivate_videoo_manager' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-videoo-manager.php';



/**
 * Plugin constants
 */

$uploadsDirInfo = wp_upload_dir();


define( 'VIDEOO_MANAGER_UPLOADS', $uploadsDirInfo['basedir']);

define( 'VIDEOO_MANAGER_BASE_PATH', VIDEOO_MANAGER_UPLOADS.'/videoo-manager/');

define( 'VIDEOO_MANAGER_ADS_TXT_BACK_FILE', VIDEOO_MANAGER_BASE_PATH .'ext.txt');

define( 'VIDEOO_MANAGER_ADS_TXT_FILE', ABSPATH .'ads.txt');

define( 'VIDEOO_MANAGER_NET_LINES_BCK', VIDEOO_MANAGER_BASE_PATH .'publisher-ads.txt');

define( 'VIDEOO_MANAGER_CONFIG_FILE', VIDEOO_MANAGER_BASE_PATH .'config.json');

define( 'VIDEOO_MANAGER_ADS_TXT_BACKUP_DIR', VIDEOO_MANAGER_BASE_PATH .'backs/');

define( 'VIDEOO_MANAGER_NET_SELLERS_JSON', 'https://static.videoo.tv/sellers.json');

define( 'VIDEOO_MANAGER_NET_LINES', 'https://static.videoo.tv/publisher-ads.txt');

define( 'VIDEOO_MANAGER_CONFIG_ACTIVE_ADS_TXT', 'videoo_manager_ads_txt_active');

define( 'VIDEOO_MANAGER_CONFIG_ACTIVE_TAG', 'videoo_manager_config_active');


define( 'VIDEOO_MANAGER_NO_GROUP_NAME', '# Nogroup');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_videoo_manager() {

	$plugin = new Videoo_Manager();
	$plugin->run();

}
run_videoo_manager();

