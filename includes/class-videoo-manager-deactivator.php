<?php
/**
 * Fired during plugin deactivation
 *
 * @link       http://videoo.tv
 * @since      1.0.0
 *
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/includes
 * @author     José Lamas <email@videoo.tv>
 */
class Videoo_Manager_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		$plugin_cron_data = new Videoo_Manager_Cron( null, null);
		$plugin_cron_data->videoo_manager_unschedule();

		/** Delete plugin options fron wp bd*/
		delete_option('videoo_manager_config_id');
		delete_option('videoo_manager_config_position');
		delete_option('videoo_manager_publisher_id');
		delete_option(VIDEOO_MANAGER_CONFIG_ACTIVE_TAG);
		delete_option(VIDEOO_MANAGER_CONFIG_ACTIVE_ADS_TXT);

		//ads.txt backup on deactivate
		$helpers = new Videoo_Manager_Helpers();
		$services = new Videoo_Manager_Services($helpers);
		$services->make_adstxt_backup();
	}

}
