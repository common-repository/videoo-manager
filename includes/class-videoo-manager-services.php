<?php
/**
 * Work services for videoo-manager
 *
 * @link       http://videoo.tv
 * @since      1.0.0
 *
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/admin
 */

/**
 * Work services for videoo-manager.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/admin
 * @author     José Lamas <email@videoo.tv>
 */
class Videoo_Manager_Services {

	/**
	 * Helper functions
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $helpers Helper functions
	 */
	private $helpers;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $helpers      The plugins helpers
	 */
	public function __construct($helpers) {
		$this->helpers = $helpers;
  }

  public function check_writable($file_data) {
    return is_writable($file_data['filename']) ?: $this->warn_admin($file_data);
  }

  /**
   * Devuelve las líneas estáticas de videootv del backup realizado en el activator y por cron en el sistema de ficheros
  */
  public function get_static_videootv_lines() {
	  $static_videootv_lines =  @file_get_contents(VIDEOO_MANAGER_NET_LINES_BCK);
		if (false === $static_videootv_lines || '' === $static_videootv_lines) { //Si no puede abrirlo o está vacío intenta recuperarlo de la red
			$static_videootv_lines = $this->get_remote_static_videootv_lines();
		}
	 return $static_videootv_lines;
  }

  /**
   * Recupera de remoto las líneas estáticas de videootv, las graba en su fichero de backup y las recupera
  */
  public function get_remote_static_videootv_lines() {
	  $static_videootv_lines = @file_get_contents(VIDEOO_MANAGER_NET_LINES);
	  $this->mutate_dg_publisher_id($static_videootv_lines);
	  if (false !== $static_videootv_lines && '' !== $static_videootv_lines) { //Si puede descargarlo y no viene vacío
		  @file_put_contents(VIDEOO_MANAGER_NET_LINES_BCK, $static_videootv_lines);
	  }
	  return $static_videootv_lines;
  }

	/**
	 * Recupera el publisher_id
	 */
  public function get_publisher_id() {
	  $publisher_id = get_option('videoo_manager_publisher_id');
	  if (false === $publisher_id) {
		  $publisher_id = $this->get_remote_sellers_publisher_id();
	  }
	  return $publisher_id;
  }

	/**
	 * Recupera del sellers.json el publisherId
	 */
	public function get_remote_sellers_publisher_id() {
		$publisher_id = null;
		$sellers_json = @file_get_contents(VIDEOO_MANAGER_NET_SELLERS_JSON);
		if (false !== $sellers_json && '' !== $sellers_json) { //Si puede descargarlo y no viene vacío
			$data = json_decode($sellers_json, true);
			if ($data !== null) {
				$publisher_id = $this->extract_publisher_id($data);
				if ($publisher_id !== null)
					update_option("videoo_manager_publisher_id", $publisher_id);
			}
		}

		return $publisher_id;
	}

  public function make_adstxt_backup () { //Se hace una copia diaria
	  $date_str = date('Ymd').time();
	  $ads_txt_content = @file_get_contents(VIDEOO_MANAGER_ADS_TXT_FILE);
	  if (false !== $ads_txt_content) {
		  @file_put_contents(VIDEOO_MANAGER_ADS_TXT_BACKUP_DIR.'ads_txt_'.$date_str.'.back', $ads_txt_content);
	  }
	  //Elimina ficheros obsoletos. Se guardan como máximo 365 ficheros de backup
	  $files = glob(VIDEOO_MANAGER_ADS_TXT_BACKUP_DIR."*.back");
	  if (is_array($files)) {
		  rsort($files);
		  if (count($files) > 365) {
			  $file_to_delete =  $files[array_key_last($files)];
			  @unlink($file_to_delete);
		  }
	  }
  }

	/**
	 * write_file - escribe los archivos y asigna las flags que le dan si existen
	 *
	 * @param String $file_name
	 * @param String $file_content
	 * @param String $flags
	 * @return void
	 */
	public function write_file($file_name, $file_content, $flags = null) {
		if (!empty($file_name)) {
			$flags = $flags ?? LOCK_EX;
			@file_put_contents($file_name, $file_content, $flags);
		}
	}

	public function create_dir($path, $permission = 0777, $recursive = true) {
		if (!file_exists($path)) {
			@mkdir($path, $permission, $recursive);
		}
	}

	public function create_empty_working_files() {
		if (!file_exists(VIDEOO_MANAGER_ADS_TXT_BACK_FILE)) {
			@touch(VIDEOO_MANAGER_ADS_TXT_BACK_FILE);
		}
		if (!file_exists(VIDEOO_MANAGER_NET_LINES_BCK)) {
			@touch(VIDEOO_MANAGER_NET_LINES_BCK);
		}
	}

	/**
	 * Modifica el publisher-id de digital-green al recuperar las líneas de la red de videoo.tv en remoto
	 */
	private function mutate_dg_publisher_id(&$static_videoo_lines) {
		$rows = preg_split("/\r\n|\n|\r/", $static_videoo_lines);
		if (is_array($rows)) {
			array_walk($rows, function (&$row) {
				$fields = explode(',',$row);
				if (is_array($fields) && 'digitalgreen.es' === trim($fields[0])) {
					$fields[1] = $this->get_publisher_id();
					$row = implode(',', $fields);
				}
			});
			$static_videoo_lines = implode("\n", $rows);
		}
	}

	private function extract_publisher_id($sellers_json) {
		$publisher_id = null;
		$domain = $this->get_site_domain(get_bloginfo( 'url' ));//tiene en cuenta instalaciones multisite
		if (array_key_exists('sellers', $sellers_json)) {
			$key = array_search($domain, array_column($sellers_json['sellers'], 'domain'));
			if (false !== $key) {
				$publisher_id = $sellers_json['sellers'][$key]['seller_id'] ?? null;
			}
		}

		return $publisher_id;
	}

	private function get_site_domain($url) {
		$url_info = parse_url($url);
		$domain = isset($url_info['host']) ? $url_info['host'] : '';
		if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $result)){
			return $result['domain'];
		}
		return '';
	}


	private function warn_admin($file_data) {
		return [
			'is_writable' => is_writable($file_data['filename']),
			'filename'=> $file_data['filename'],
			'permissions' => substr(decoct(fileperms($file_data['filename'])), 3)
		];
	}
}
