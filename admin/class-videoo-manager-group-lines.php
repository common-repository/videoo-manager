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
 * The admin-specific functionality of the plugin.
 * Pending of use SplFileObject
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/admin
 * @author     José Lamas <email@videoo.tv>
 */
class Videoo_Manager_Group_Lines extends Videoo_Manager_Base implements Videoo_Manager_Post_Interface {

	private const DELETE_GROUP_LINES_ACTION = 'delete_group_lines';
	private const UPDATE_GROUP_LINES_ACTION = 'update_group_lines';
	private const ADD_GROUP_LINES_ACTION = 'save_group_new_lines';

	/**
	 * The ads.txt file
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $filename The ads.txt filein.
	 */
	private $filename;

	/**
	 * The ajax data
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $post_data    The ajax post data
	 */
	private $post_data;



	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		parent::__construct();
    	$this->filename = VIDEOO_MANAGER_ADS_TXT_FILE;
		$this->post_data = $this->get_post_data();
  }

	/**
	 * Delete group lines
	 *
	 * @return void
	 */
	public function ajax_delete_group_lines()
	{
		$nogroup_slug = $this->helpers->get_group_slug(VIDEOO_MANAGER_NO_GROUP_NAME);
		$return_value = [];
		$delete_lines = $this->post_data->lines_data->delete_lines;
		$group_name = $this->helpers->get_group_slug($this->post_data->lines_data->group_name);
		$ads_txt_lines = [];
		$clean_lines = [];
		$group_open = false;
		$nogroup_open = true;

		if ($this->validate_delete_group_lines($delete_lines) !== true) {
			$return_value['validation_error'] = 'No lines to delete';
			wp_send_json($return_value, 200);
		}

		if (!$fp = fopen($this->filename, 'r+')) { exit; }
		while(!feof($fp)) {
			$raw_line = fgets($fp);
			$ad_line = preg_replace('/(\n|chr(10))+/', "", $raw_line);
			// Si ad_line es igual a group_name, chequea el array

			if($this->helpers->str_starts_with($ad_line, '#') && !preg_match('/(#|\s)+(fin|end).*?/imu', $ad_line, $matches)) {
				$group_open = $this->helpers->get_group_slug($ad_line) === $group_name;
				$nogroup_open = false;
				if ($group_open && $group_name !== $nogroup_slug)
					$ads_txt_lines = [];
			}
			if(($group_open || $nogroup_open)) {
				$ads_txt_lines[] = $raw_line;
			}
		}
		foreach ($ads_txt_lines as $line) {
			$sanitized_line = $line;
			$this->sanitize_raw_line_str($sanitized_line);
			if(!in_array($sanitized_line, $delete_lines)) {
				$clean_lines[] = $line;
			}
		}
		if(!empty($clean_lines) || $nogroup_slug === $group_name) {
			try {
				$this->services->write_file($this->filename, str_replace(implode('', $ads_txt_lines), implode('', $clean_lines), @file_get_contents($this->filename)));
				$return_value['deleted'] =  $delete_lines ;
			} catch (\Throwable $th) {
				$return_value['error'] = [$th, $delete_lines];
			}
		} else {
			$return_value['not_deleted'] =  $delete_lines ;
		}
		fclose($fp);

		wp_send_json($return_value, 200);
	}

	/**
	 * Update group lines
	 *
	 * @return void
	 */
	public function ajax_update_group_lines()
	{
		$nogroup_slug = $this->helpers->get_group_slug(VIDEOO_MANAGER_NO_GROUP_NAME);
		$update_lines = $this->post_data->lines_data->update_lines;
		$return_value = $this->validate_update_group_lines($update_lines);
		$group_name = $this->helpers->get_group_slug($this->post_data->lines_data->group_name);
		$ads_txt_lines = [];
		$new_lines = [];
		$changed_lines = [];
		$group_open = false;
		$nogroup_open = true;

		if (!$fp = fopen($this->filename, 'r+')) { exit; }
		while(!feof($fp)) {
			$raw_line = fgets($fp);
			$ad_line = preg_replace('/(\n|chr(10))+/', "", $raw_line);
			// Si ad_line es igual a group_name, chequea el array
			if($this->helpers->str_starts_with($ad_line, '#') && !preg_match('/(#|#\s*)+(end|fin).?/i', $ad_line, $matches)) {
				$group_open = $this->helpers->get_group_slug($ad_line) === $group_name;
				$nogroup_open = false;
				if ($group_open && $group_name !== $nogroup_slug)
					$ads_txt_lines = [];
			}

			if(($group_open || $nogroup_open)) {
				$ads_txt_lines[] = $raw_line;
			}
		}

		foreach ($ads_txt_lines as $i_line => $a_line) {
			foreach ($update_lines as $u_line) {
				$this->sanitize_raw_line_str($a_line);
				if (empty($a_line)) break;
				if ($a_line === $u_line->prev) {
					$new_lines[$i_line] = rtrim(implode(',',$u_line->new),',');
					$changed_lines[] = $u_line;
					break;
				} else {
					$new_lines[$i_line] = $a_line;
				}
			}
		}
		foreach ($update_lines as $u_line) {
			if (!in_array($u_line, $changed_lines)) {
				$return_value['not_updated'][] = $u_line;
			}
		}

		if(!empty($new_lines)) {
			try {
				$this->services->write_file($this->filename, str_replace(implode('', $ads_txt_lines), implode("\n", $new_lines)."\n", @file_get_contents($this->filename)));
				$return_value['updated'] = $changed_lines;
			} catch (\Throwable $th) {
				$return_value['error'] = [$th, $update_lines];
			}
		}
		fclose($fp);
		wp_send_json($return_value, 200);
	}

	/**
	 * Save new lines
	 *
	 * @return void
	 */
	public function ajax_save_group_new_lines()
	{
		$save_lines = $this->post_data->lines_data;
		$return_value = $this->validate_save_group_new_lines($save_lines);
		$group_name = $this->helpers->get_group_slug($this->post_data->group_name);
		$ads_txt_lines = [];
		$ads_txt_new_lines = [];
		$check_lines = [];
		$group_open = false;

		if (!$fp = fopen($this->filename, 'r+')) { exit; }
		while(!feof($fp)) {
			$raw_line = fgets($fp);
			$ad_line = preg_replace('/(\n|chr(10))+/', "", $raw_line);
			if($this->helpers->str_starts_with($ad_line, '#') && !preg_match('/(#|#\s*)+(end|fin).?/i', $ad_line, $matches)) {
				$group_open = $this->helpers->get_group_slug($ad_line) === $group_name;
			}
			if($group_open) {
				$ads_txt_lines[] = $raw_line;
				$this->sanitize_raw_line_str($raw_line); //Sanitizo después para mantener el reemplazo original.ón.
				$ads_txt_new_lines[] = $raw_line."\n";
				$check_lines[] = $raw_line;
			}
		}
		foreach ($save_lines as $s_index => $s_line) {
			if(empty($s_line['certification'])) unset($s_line['certification']);
			foreach ($s_line as $k => $v) {
				$s_line[$k] = $this->helpers->str_sanitize($v);
			}
			$line_to_save = implode(',', $s_line);
			if(!in_array($line_to_save, $check_lines)) {
				$ads_txt_new_lines[] = $line_to_save."\n";
			} else {
				$return_value['already_exists'][] = $s_line;
				unset($save_lines[$s_index]);
			}
		}
		// delete repes
		$ads_txt_new_lines = $this->helpers->delete_repes($ads_txt_new_lines);

		// Save
		if(!empty($save_lines)) {
			foreach ($save_lines as $k => &$v) { //Sanitize lines
				$v = $this->helpers->str_sanitize($v);
			}
			try {
				$this->services->write_file($this->filename, str_replace(implode('', $ads_txt_lines), implode('', $ads_txt_new_lines), @file_get_contents($this->filename)));
				$return_value['saved'] =  $save_lines;
			} catch (\Throwable $th) {
				$return_value['error'] = [$th, $save_lines];
			}
		}
		fclose($fp);
		wp_send_json($return_value, 200);
	}

	/**
	 * Check and save external lines
	 *
	 * @return void
	 */
	public function save_ext_group_lines()
	{
		$ext_backup_raw = $this->get_ext_backup_group($this->getStaticLines(), true);
		$ext_txt_lines = explode("\n", $this->getStaticLines());
		$ext_txt_backup = explode("\n", $ext_backup_raw);
		$group_txt_lines = [];
		$group_name = !empty(trim($ext_txt_backup[0])) ? trim($ext_txt_backup[0]) : trim($ext_txt_lines[0]);
		$group_titles = [];
		$nogroup_lines = [];
		$delete_lines = [];
		$first_group_open = false;
		$first_group = '';
		$group_exists = false;
		$group_open = false;

		if(!is_writable($this->filename)) return false;
		if (!$fp = fopen($this->filename, 'r+')) { exit; }
		if(0 === filesize($this->filename)) {
			$this->services->write_file($this->filename, trim($ext_txt_lines[0]));
		}
		while(!feof($fp)) {
			$raw_line = fgets($fp);
			$ad_line = preg_replace('/(\n|chr(10))+/', "", $raw_line);
			if($this->helpers->str_starts_with($ad_line, '#') && !preg_match('/(#|#\s*)+(end|fin).?/i', $ad_line, $matches)) {
				if(!in_array($ad_line, $group_titles)) {
					$group_titles[] = $ad_line;
					$first_group_open = $ad_line === $group_titles[0];
				}
				$group_open = $this->helpers->get_group_slug($ad_line) === $this->helpers->get_group_slug($group_name);
			}
			$group_exists = in_array(trim($ext_txt_lines[0]), $group_titles);
			if($group_exists && $group_open) {
				// Añadir líneas de texto limpias
				$group_txt_lines[] = $ad_line;
				// duplicamos el array de las líneas del grupo para saber qué remplazar
				$group_raw_lines[] = $raw_line;
			}
			// $this->helpers->debugThis($group_exists, $group_titles, 'die');
			// rellenar el primer grupo para meter las líneas de nogroup por si acaso
			if($first_group_open) {
				$first_group .= $raw_line;
			}
		}
		foreach ($ext_txt_lines as $e_index => $e_line) {
			// !in_array(ext_line, group_lines)?
			// 	$group_lines[]ext_line
			$e_line = trim($e_line);

			if(empty($e_line)) {
				unset($ext_txt_lines[$e_index]);
				continue;
			}
			// ya que estamos, trimeamos las líneas para que no de problemas con delete_group
			$ext_txt_lines[$e_index] = $e_line;
			if(!in_array($e_line, $group_txt_lines) && !empty($e_line)) {
				// $group_txt_lines[] = $e_line;
				array_splice($group_txt_lines, $e_index, 0, $e_line);
			}
		}

		foreach ($group_txt_lines as $g_index => $g_line) {
			$g_line = trim($g_line);
			if(empty($g_line)) {
				unset($group_txt_lines[$g_index]);
				continue;
			}
			$group_txt_lines[$g_index] = $g_line;
			if(!in_array($g_line, $ext_txt_backup) && !in_array($g_line, $ext_txt_lines) & !empty($g_line)) {
				// 	$nogroup[] = group_line
				// chequeamos que cumple con los estándares de una línea de adstxt

				if ($this->helpers->check_ad_line($g_line)['check']){
					$nogroup_lines[] = $g_line;
				}
				unset($group_txt_lines[$g_index]);
			}
			if(!in_array($g_line, $ext_txt_lines) && in_array($g_line, $ext_txt_backup) && !empty($g_line)) {
				// in_array(g_line, ext_txt_backup)?
				$delete_lines[] = $g_line;
				unset($group_txt_lines[$g_index]);
			}
		}
		fclose($fp);
		// guardamos el nuevo grupo de videootv
		if(!empty($group_raw_lines) && !empty($group_txt_lines)) {
			$this->services->write_file($this->filename, str_replace( implode('', $group_raw_lines), implode("\n", $group_txt_lines)."\n", @file_get_contents($this->filename)));
		}
		// escribimos al principio del archivo las líneas sin grupo
		if(!empty($nogroup_lines)) {
			$this->services->write_file($this->filename, str_replace( $first_group, implode("\n", $nogroup_lines)."\n$first_group", @file_get_contents($this->filename)));
		}
		// Sobrescribimos el backup
		if(!empty($group_txt_lines)) {
			$this->services->write_file(VIDEOO_MANAGER_ADS_TXT_BACK_FILE, implode("\n", $group_txt_lines));
		}
		// $this->helpers->debugThis($group_exists, $group_titles, 'die');
		if ( !$group_exists) {
			@file_put_contents($this->filename, "\n".$ext_backup_raw, LOCK_EX|FILE_APPEND);
		}
		return true;
	}

	public function get_post_data()
	{
		$result = new stdClass();
		if ($this->validate_action() && isset($_POST["data"])) {
			$action =  sanitize_text_field( wp_unslash($_POST['action']));
			if (self::DELETE_GROUP_LINES_ACTION === $action) {
				$result->lines_data = $this->sanitize_modal_action(json_decode($_POST["data"]["lines_data"]));
			} else if (self::UPDATE_GROUP_LINES_ACTION === $action) {
				$result->lines_data = $this->sanitize_modal_action(json_decode($_POST["data"]["lines_data"]));
			} else if (self::ADD_GROUP_LINES_ACTION === $action) {
				$result->action = sanitize_text_field(wp_unslash($_POST["data"]["action"]));
				$result->group_name = sanitize_text_field(wp_unslash($_POST["data"]["group_name"]));
				if (isset($_POST["data"]["line_data"])) {
					$result->lines_data = $this->sanitize_line_data($_POST["data"]["line_data"]);
				}
				if (isset($_POST["data"]["lines_data"])) {
					$result->lines_data = $this->sanitize_lines_data($_POST["data"]["lines_data"]);
				}
			}
		}

		return $result;
	}

	private function validate_action () {
		return !empty($_POST) && preg_match('/(show|delete|update|save)?_group_(new_)?lines/', sanitize_text_field( wp_unslash($_POST['action'])));
	}

	private function validate_save_group_new_lines(&$save_lines) {
		$return_value = [];
		$save_lines = array_filter($save_lines, function ($line) use (&$return_value) {
			$result = true;
			if(!isset($line['domain']) || !$this->helpers->check_url($line['domain']) || '' === $line['domain']) {
				$return_value['has_domain_invalid'][] = $line;
				$result = false;
			}
			if(!isset($line['network']) || '' === $line['network']) {
				$return_value['has_network_invalid'][] = $line;
				$result = false;
			}
			if(!isset($line['type']) || !preg_match('/(\bDIRECT\b|\bRESELLER\b)/i', $line['type'])) {
				$return_value['has_type_invalid'][] = $line;
				$result = false;
			}
			return $result;
		});
		return $return_value;
	}

	private function validate_update_group_lines(&$update_lines) {
		$return_value = [];
		$update_lines = array_filter($update_lines, function ($line) use (&$return_value) {
			$result = true;
			if ($line->prev === null || $line->new === null) {
				$return_value['not_updated'][] = $line;
				$result = false;
			}
			return $result;
		});
		return $return_value;
	}

	private function validate_delete_group_lines($delete_lines) {
		if ($delete_lines == null || count($delete_lines) == 0) {
			return false;
		}

		return true;
	}

	private function sanitize_lines_data($lines) {
		array_walk($lines, [$this,'sanitize_line']);
		return $lines;
	}

	private function sanitize_line_data($line) {
		$this->sanitize_line($line);
		return  [$line];
	}

	private function sanitize_modal_action($payload) {
		if (null !== $payload) {
			$payload->modal_action = property_exists($payload, 'modal_action') && in_array($payload->modal_action, ['delete','update']) ? $payload->modal_action : null;
			$payload->group_name = sanitize_text_field(wp_unslash($payload->group_name)) ?? null;
			if ("delete" === $payload->modal_action) {
				if (property_exists($payload, 'delete_lines') && is_array($payload->delete_lines)) {
					array_walk($payload->delete_lines, [$this,'sanitize_raw_line_str']);
				} else {
					$payload->delete_lines = null;
				}
			}
			if ("update" === $payload->modal_action) {
				if (property_exists($payload, 'update_lines') && is_array($payload->update_lines)) {
					array_walk($payload->update_lines, [$this,'sanitize_update_line']);
				} else {
					$payload->update_lines = null;
				}
			}

		}
		return $payload;
	}

	private function sanitize_update_line(&$line) {
		$line->prev = trim(sanitize_text_field( wp_unslash($line->prev))) ?? null;
		if (property_exists($line, 'new') && is_array($line->new)) {
			$this->sanitize_raw_line($line->new);
		} else {
			$line->new = null;
		}
	}

	/**
	 * Returns the backup group name and lines
	 *
	 * @return String|Void
	 */
	private function get_ext_backup_group($group)
	{
		$bck_group_lines = [];
		$ext_file = VIDEOO_MANAGER_ADS_TXT_BACK_FILE;
		if(file_exists($ext_file) && (0 < filesize($ext_file)) && is_writable($ext_file)) {
			if (!$fp = fopen($ext_file, 'a+')) { exit; }
			while(!feof($fp)) {
				$bck_group_lines[] = trim(fgets($fp));
			}
			fclose($fp);

			return implode("\n", $bck_group_lines);
		}

		if(is_writable($ext_file)) {
			$this->services->write_file($ext_file, $group);
		}

		return $group;
	}

}
