<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://videoo.tv
 * @since      1.0.0
 *
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/admin
 */

/**
 *
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/admin
 * @author     José Lamas <email@videoo.tv>
 */
class Videoo_Manager_Config extends Videoo_Manager_Base implements Videoo_Manager_Post_Interface {

	/**
	 * The ajax data
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $post_data    The ajax post data
	 */
	private $post_data;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		parent::__construct();
		$this->post_data = $this->get_post_data();
  }

	public function ajax_save_videoo_config_settings()	{
		if ($this->validate_post_data() === true) {
			foreach ($this->post_data as $key => $value) {
				update_option("videoo_manager_$key", $value);
			}
		}

		wp_send_json($this->post_data, 200);
	}

	public function ajax_toggle_active_ads_txt()
	{
		update_option(VIDEOO_MANAGER_CONFIG_ACTIVE_ADS_TXT, $this->post_data['config_active']);

		wp_send_json($this->post_data, 200);
	}

	public function get_post_data()
	{
		$result = [];
		if ($this->validate_action() && isset($_POST["data"])) {
			if (isset($_POST["data"]["config_id"])) {
				$result['config_id'] = sanitize_key( wp_unslash($_POST["data"]["config_id"]));
			}

			if (isset($_POST["data"]["config_position"])) {
				$result['config_position'] = is_numeric(trim($_POST["data"]["config_position"])) ? trim($_POST["data"]["config_position"]) : 0;
			}

			if (isset($_POST["data"]["config_active"])) {
				$result['config_active'] = in_array($_POST["data"]["config_active"], ['yes','no']) ? $_POST["data"]["config_active"] : 'no';
			}
		}
		return $result;
	}

	private function validate_action () {
		return !empty($_POST) && preg_match('/(save_videoo_config_settings|toggle_active_ads_txt)/', sanitize_text_field( wp_unslash($_POST['action'])));
	}

	private function validate_post_data() {
		$result = true;
		if (!isset ($this->post_data['config_id'] ) || $this->post_data['config_id'] == '') {
			$result = false;
		}

		if (!isset ($this->post_data['config_position'] ) || !is_numeric($this->post_data['config_position']) || $this->post_data['config_position'] < 1) {
			$result = false;
		}

		if (!isset ($this->post_data['config_active'] ) || !in_array($this->post_data['config_active'], ['yes','no'])) {
			$result = false;
		}

		return $result;
	}


}
