<?php

/**
 * The admin-specific functionality of the plugin.Base class
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
 * @author     JM <email@videoo.tv>
 */
class Videoo_Manager_Base {

	/**
	 * Helper functions
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Videoo_Manager_Helpers    $helpers Helper functions
	 */
	protected $helpers;


	/**
	 * Videoo_Manager_Services
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Videoo_Manager_Services    $services Videoo_Manager_Services class
	 */
	protected $services;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( ) {
		$this->helpers = new Videoo_Manager_Helpers();
		$this->services = new Videoo_Manager_Services($this->helpers);
	}

	/**
	 * @return mixed|string
	 */
	public function getStaticLines()
	{
		return $this->services->get_static_videootv_lines();
	}

	protected function sanitize_raw_line(&$line) {
		array_walk($line, function (&$field, $key) {
			$field = trim(sanitize_text_field( wp_unslash($field)));
			if ($key == 0) {
				$field = preg_replace('/(http(s)?):\/\//i', '', $field);
			}
			if ($key == 2) {
				$field = strtoupper($field);
			}
		});
	}

	protected function sanitize_raw_line_str(&$line) {
		$sanitized_line = explode(',',$line);
		$this->sanitize_raw_line($sanitized_line);
		$line = rtrim(implode(',', $sanitized_line), ',');
	}

	protected function sanitize_line(&$line) {
		$line['domain'] = isset($line['domain']) ? preg_replace('/(http(s)?):\/\//i', '', trim(sanitize_text_field( wp_unslash($line['domain'])))) : '';
		$line['network'] = isset($line['network']) ? trim(sanitize_text_field( wp_unslash($line['network']))) : '';
		$line['type'] = isset($line['type']) ? trim(sanitize_text_field( wp_unslash($line['type']))) : '';
		if (isset($line['certification'])) {
			$line['certification'] = trim(sanitize_text_field( wp_unslash($line['certification'])));
		}
	}



}
