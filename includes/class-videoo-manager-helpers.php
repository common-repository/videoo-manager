<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://videoo.tv
 * @since      1.0.0
 *
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/includes
 */

/**
 * Helpers to make development easier
 *
 * @since      1.0.0
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/includes
 * @author     José Lamas <email@videoo.tv>
 */
class Videoo_Manager_Helpers {

  /**
   * [str_starts_with description]
   * @param  string $str               [description]
   * @param  string $start                 [description]
   * @return bool             [description]
   */
  public function str_starts_with(string $str, string $start): bool
  {
    return 0 === substr_compare($str, $start, 0, strlen($start));
  }

  /**
   * [check_checked description]
   * @param  string $input  [description]
   * @param  string $value  [description]
   * @return string         [description]
   */
  public function check_checked($input, $value) {
    return ($input === (string)$value) ? 'checked' : '';
  }

	/**
	 * @return boolean
	 */
  public function is_cron_active() {
	  return (true === defined ('DISABLE_WP_CRON') && false === DISABLE_WP_CRON);
  }

  /**
   * microtimestamp
   *
   * @return Integer
   */
  public function microtimestamp() {
    $microtime = explode(' ', microtime());
    return (int)((float)$microtime[0]*1000000)+ (int)$microtime[1];
  }

  /**
   * check_page
   *
   * @param String $check
   * @return Boolean
   */
  public function check_page($check) {
	  // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reason: We are not processing form information.
	  if ( ! isset( $_GET['page'] ) || ! is_string( $_GET['page'] ) ) {
		  return false;
	  }

	  // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reason: We are not processing form information.
	  $page = sanitize_text_field( wp_unslash( $_GET['page']) );


    return $this->str_starts_with($page, $check);
  }

  /**
   * check_url
   *
   * @param String $url
   * @return Boolean
   */
  public function check_url($url) {
    return preg_match('/^((http(s)?):\/\/)?(([A-z0-9\-\_]+){1,256}\.)?([A-z0-9\-\_]+){1,256}\.([A-z0-9@:%._\\/+~\-#=\?\"\']{2,})/i', $url);
  }

  /**
   * check_ad_line
   *
   * @param [type] $ad_line
   * @return Array
   */
  public function check_ad_line($ad_line) {
    $check_ad_line = preg_match('/^ *(.*?) *, *(.*?) *, *(DIRECT|RESELLER) *,?( *(.*?))?$/i', trim($ad_line), $matches);
    return [
      'check' => $check_ad_line,
      'match' => $matches
    ];
  }

  /**
   * delete_repes. Unsets every repeated ocurrence and returns an array
   *
   * @param Array $array_to_check
   * @return void
   */
  public function delete_repes($array_to_check) {
    foreach ($array_to_check as $a_index => $a_line) {
			foreach ($array_to_check as $a_i => $a_l) {
				if ($a_line === $a_l && $a_index !== $a_i) {
					unset($array_to_check[$a_index]);
				}
			}
		}
    return $array_to_check;
  }

  public function str_sanitize($str) {
	  if (null !== $str && '' !== $str)
	  	$str =  preg_replace("/\s|,|\t|\v/", '', $str);
	  return $str;
  }

  /**
   * get_group_slug
   *
   * @param String $group_name
   * @return String
   */
  public function get_group_slug($group_name) {
    return sanitize_title_with_dashes(mb_convert_encoding($group_name, 'UTF-8'));
  }

}
