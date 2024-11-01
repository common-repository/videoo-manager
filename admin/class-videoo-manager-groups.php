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
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/admin
 * @author     José Lamas <email@videoo.tv>
 */
class Videoo_Manager_Groups extends Videoo_Manager_Base implements Videoo_Manager_Post_Interface {

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
	 * @var      string    $post_data    The ajax post data
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
	 * Undocumented function
	 *
	 * @return void
	 */
	public function ajax_show_groups()
	{
		$show_groups = $this->get_groups();
		wp_send_json($show_groups, 200);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function ajax_show_single_group()
	{
		$return_group = [];
		$check_writable = $this->services->check_writable(['filename' => VIDEOO_MANAGER_ADS_TXT_BACK_FILE]);

		$this->validate();

		if (true === $check_writable) {
			$groups = $this->get_groups();
			foreach ($groups as $group) {
				if($this->helpers->get_group_slug($this->post_data['group_name']) === $this->helpers->get_group_slug($group['group_name'])){
					$return_group = [
						'group_name' => $group['group_name'],
						'group_lines' => $group['group_lines']
					];
					break;
				}
			}
		} else {
			$return_group = $check_writable;
		}

		wp_send_json($return_group, 200);
	}

	/**
	 * Delete group
	 *
	 * @return void
	 */
	public function ajax_delete_group()
	{
		$this->validate();

		$return_value = [];
		$group_name = $this->post_data['group_name'];
		$group_open = false;
		$lines_array = [];
		if (!$fp = fopen($this->filename, 'r+')) { exit; }
		while(!feof($fp)) {
			$raw_line = fgets($fp);
			$ad_line = preg_replace('/(\n|chr(10))+/', "", $raw_line);
			// Si ad_line es igual a group_name, chequea el array

			if($this->helpers->str_starts_with($ad_line, '#') && !preg_match('/(#|\s)+(fin|end).*?/imu', $ad_line, $matches)) {
				$group_open = $this->helpers->get_group_slug($ad_line) === $this->helpers->get_group_slug($group_name);
			}
			// junta en un array
			if($group_open) {
				$lines_array[] = $raw_line;
			}
		}
		// implode y busca en el ads.txt para borrar
		try {
			if (!empty($lines_array)) {
				$delete_lines = implode("", $lines_array);
				$this->services->write_file($this->filename, str_replace($delete_lines, false, @file_get_contents($this->filename)));
			}
			$return_value['deleted'] = !empty($lines_array) ? $group_name : 'none';
		} catch (\Throwable $th) {
			$return_value['error'] = [$th, $group_name];
		}
		fclose($fp);

		wp_send_json($return_value, 200);
	}

	/**
	 * Rename group
	 *
	 * @return void
	 */
	public function ajax_rename_group()
	{
		$this->validate();

		$return_value = [];
		$group_name = $this->post_data['group_name'];
		$group_name_slug = $this->helpers->get_group_slug($group_name);
		$group_new_name = $this->post_data['group_new_name'];
		$group_new_name_slug = $this->helpers->get_group_slug($group_new_name);
		$group_names = [];
		$group_exists = false;
		foreach ($this->get_groups() as $g_index => $group) {
			$group_names[] = $group['group_name'];
		}
		if (!$fp = fopen($this->filename, 'r+')) { exit; }
		while(!feof($fp)) {
			$raw_line = fgets($fp);
			$ad_line = preg_replace('/(\n|chr(10))+/', "", $raw_line);
			// Si ad_line es igual a group_name, chequea el array
			if($this->helpers->str_starts_with($ad_line, '#')) {
				$ad_line = $this->helpers->get_group_slug($ad_line);
				if(!preg_match('/(#|\s)+(fin|end).*?/imu', $ad_line, $matches)
				&& $group_name_slug === $ad_line) {
					foreach ($group_names as $g_name) {
						$group_exists = $group_new_name_slug === $ad_line;
					}
					try {
						$group_exists = $group_exists || preg_match('/(#|\s*)'.str_replace('/', '\/', $group_new_name).'(\s*)?$/imu', $group_name);
						if (!$group_exists) {
							$this->services->write_file($this->filename, str_replace($group_name, "# ".$group_new_name, @file_get_contents($this->filename)));
							$return_value['renamed'] = [$group_name, $group_new_name];
						} else {
							$return_value['exists'] = $group_new_name;
						}
					} catch (\Throwable $th) {
						$return_value['error'] = [$th, $group_name];
					}
				}
			}
		}
		fclose($fp);

		wp_send_json($return_value, 200);
	}

	/**
	 * Create new group
	 *
	 * @return void
	 */
	public function ajax_create_group()
	{
		$this->validate();

		$return_value = [];
		$group_name = $this->post_data['group_name'];
		$group_name_slug = $this->helpers->get_group_slug($group_name);
		$new_group = "\n# $group_name\n";
		$group_exists = false;
		if (!$fp = fopen($this->filename, 'a+')) { exit; }
		while(!feof($fp)) {
			$raw_line = fgets($fp);
			$ad_line = preg_replace('/(\n|chr(10))+/', "", $raw_line);
			// Si ad_line es igual a group_name, chequea el array
			if($this->helpers->str_starts_with($ad_line, '#')) {
				$ad_line = $this->helpers->get_group_slug($ad_line);
				if(!preg_match('/(#|\s)+(fin|end).*?/imu', $ad_line, $matches)
					&& $this->helpers->get_group_slug($group_name_slug) === $ad_line) {
					$group_exists = $group_name_slug === $ad_line;
				}
			}
		}
		if(!$group_exists) {
			try {
				//$ads_txt = fread($fp, filesize($this->filename));
				if(fwrite($fp, $new_group, strlen($new_group)) === FALSE) {
					$return_value['error'] = $group_name;
				} else {
					$return_value['created'] = $group_name;
				}
			} catch (\Throwable $th) {
				$return_value['error'] = [$th, $group_name];
			}
		} elseif($group_exists) {
			$return_value['exists'] = $group_name;
		}
		fclose($fp);

		wp_send_json($return_value, 200);
	}

	public function resolve_duplicate_group_names() {
		$groups = [];
		$lines = [];
		$dupes = [];
		$groups[0]['group_name'] = VIDEOO_MANAGER_NO_GROUP_NAME;

		$check_writable = $this->services->check_writable(['filename' => $this->filename]);

		if (true === $check_writable) {
			if (!$fp = fopen($this->filename, 'r')) { exit; }
			while(!feof($fp)) {
				$ad_line = preg_replace('/(\n|chr(10))+/', "", fgets($fp));
				// si empieza por #
				if ($this->helpers->str_starts_with($ad_line, "#")) {
					// la palabra end es el único indicativo fiable que tenemos
					// si la almohadilla no es un fin de bloque
					if(!preg_match('/(#|\s)+(end|fin).?/i', $ad_line, $matches)) {
						// Abrimos grupo
						// Metemos el grupo dentro del array de grupos
						$groups[] = $ad_line;
					}
				}
				$lines[] = $ad_line;
			}
			fclose($fp);

			foreach ($groups as $g_index => $g_line) {
				foreach ($groups as $gi => $gl) {
					if ($g_line === $gl && $g_index !== $gi) {
						$dupes[] = $gl;
					}
				}
			}
			foreach ($lines as $li => $line) {
				if(in_array($line, $dupes)){
					$lines[$li] = $line .' '. $this->helpers->microtimestamp();
				}
			}
			try {
				$this->services->write_file($this->filename, implode("\n", $lines));
			} catch (\Throwable $th) {
				$return_value['error'] = [$th];
			}
			return true;
		} else {
			return $check_writable;
		}
	}

	public function get_post_data()
	{
		$result = [];
		if ($this->validate_action() && isset($_POST['data'])) {
			if (isset($_POST['data']['group_name'])) {
				$result['group_name'] = sanitize_text_field( wp_unslash($_POST['data']['group_name']));
			}

			if (isset($_POST['data']['group_new_name'])) {
				$result['group_new_name'] = sanitize_text_field( wp_unslash($_POST['data']['group_new_name']));
			}

			$result['action'] = sanitize_text_field( wp_unslash($_POST['action']));

		}

		return $result;

	}

	private function validate_action () {
		return !empty($_POST) && preg_match('/(show|delete|rename|update|create|toggle_active)?_group/', sanitize_text_field( wp_unslash($_POST['action'])));
	}

	private function is_valid () {
		$result = isset($this->post_data['group_name']) && $this->post_data['group_name'] !== null && $this->post_data['group_name'] !== '';
		if ('rename_group' === $this->post_data['action']) {
			$result = $result && isset($this->post_data['group_new_name']) && $this->post_data['group_new_name'] !== null && $this->post_data['group_new_name'] !== '';
		}

		return $result;
	}

	private function validate() {
		if (false === $this->is_valid()) {
			$msg = 'Group Name required';
			if ($this->post_data['action'] === 'delete_group') {
				$msg = 'An error occurred deleting the group';
			} else if ($this->post_data['action'] === 'rename_group') {
				$msg = 'An error occurred renaming the group';
			} else if ($this->post_data['action'] === 'update_group') {
				$msg = 'An error occurred updating the group';
			} else if ($this->post_data['action'] === 'create_group') {
				$msg = 'An error occurred creating the group';
			}

			wp_send_json(['validation_error' => $msg], 200);
		}
	}

	private function get_groups()
	{
		$groups = [];
		$groups_check = [];
		$repes = [];
		$groups[0]['group_name'] = VIDEOO_MANAGER_NO_GROUP_NAME;
		$group_count = 1;
		$static_videootv_lines = explode("\n", $this->getStaticLines());

		$check_writable = $this->services->check_writable(['filename' => $this->filename]);

		if (true === $check_writable) {
			if (!$fp = fopen($this->filename, 'r')) { exit; }
			while(!feof($fp)) {
				$ad_line = preg_replace('/(\n|chr(10))+/', "", fgets($fp));
				// si empieza por #
				if ($this->helpers->str_starts_with($ad_line, "#")) {
					// la palabra end es el único indicativo fiable que tenemos
					// si la almohadilla no es un fin de bloque
					if(!preg_match('/(#|\s)+(end|fin).?/i', $ad_line, $matches)) {
						// Abrimos grupo
						$group_name = $ad_line;
						// Metemos el grupo dentro del array de grupos
						$groups[$group_count]["group_name"] = $group_name;
						// aumentamos el contador
						$group_count++;
					}
				}
				// guardamos las líneas en un array para chequear repes
				if(!empty($ad_line)) $groups_check[] = $ad_line;
				// Añadimos líneas que no sean de inicio de bloque al grupo
				// Buscamos el índice del grupo por el nombre del grupo
				// con el contado menos uno, porque lo sumamos arriba
				if(empty($ad_line) || $this->helpers->str_starts_with($ad_line, "#")) continue;
				$this->sanitize_raw_line_str($ad_line);
				$line_exploded = explode(',', $ad_line);
				$groups[($group_count -1)]["group_lines"][] = [
					"domain" => isset($line_exploded[0]) ? $line_exploded[0] : '',
					"account" => isset($line_exploded[1]) ? $line_exploded[1] : '',
					"type" => isset($line_exploded[2]) ? $line_exploded[2] : '',
					"certification" => isset($line_exploded[3]) ? $line_exploded[3] : '',
					"raw" => $ad_line,
					"repeated" => false
				];
			}
			// Chequeamos los repes
			foreach ($groups_check as $g_index => $g_line) {
				foreach ($groups_check as $gi => $gl) {
					if ($g_line === $gl && $g_index !== $gi) {
						$repes[] = $gl;
					}
				}
			}
			// Buscamos cada línea en los repes
			// iteramos el array
			foreach ($groups as $g_index => $group) {
				// Iteramos cada línea
				foreach ($group['group_lines'] as $gl_i => $gl) {
					// En cada línea, sacamos la ocurrencia por el índice
					// para encontrarla en el  array de groups
					// esta es la ocurrencia
					// $groups[$g_index]['group_lines'][$gl_i]
					$groups[$g_index]['group_lines'][$gl_i]['repeated'] = in_array($gl['raw'], $repes);
					unset($groups[$g_index]['group_lines'][$gl_i]['raw']);
				}
				if (preg_match('/'.trim(str_replace('/', '\/', $static_videootv_lines[0])).'(\s*)?$/imu', $group['group_name'])) {
					unset($groups[$g_index]);
				}
			}
			fclose($fp);
			return $groups;
		} else {
			return $check_writable;
		}
	}

}
