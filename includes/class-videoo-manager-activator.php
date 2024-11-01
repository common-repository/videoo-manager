<?php
/**
 * Fired during plugin activation
 *
 * @link       http://videoo.tv
 * @since      1.0.0
 *
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/includes
 * @author     José Lamas <email@videoo.tv>
 */
class Videoo_Manager_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		//ads.txt backup on activate
		$helpers = new Videoo_Manager_Helpers();
		$services = new Videoo_Manager_Services($helpers);
		$services->create_dir(VIDEOO_MANAGER_BASE_PATH);
		$services->create_dir(VIDEOO_MANAGER_ADS_TXT_BACKUP_DIR);
		$services->create_empty_working_files();
		$services->make_adstxt_backup();
	}

}
