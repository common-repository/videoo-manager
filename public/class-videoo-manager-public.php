<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://videoo.tv
 * @since      1.0.0
 *
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/public
 * @author     José Lamas <email@videoo.tv>
 */
class Videoo_Manager_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $videoo_manager    The ID of this plugin.
	 */
	private $videoo_manager;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The config of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Array    $config    The config data of this plugin.
	 */
	private $config;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $videoo_manager       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $videoo_manager, $version, $config) {

		$this->videoo_manager = $videoo_manager;
		$this->version = $version;
		$this->config = json_decode($config, false);

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() { }

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() { }

	/**
	 * Insert VideooTag in the middle of the content
	 *
	 * @param string $content El contenido del post
	 * @uses $this->config Un poco endogámico, pero no hay manera
	 * @return string $paragraph El content con la tag de VideooTv insertada
	 */
	public function insert_tag($content) {
		if (is_single() && "yes" === $this->config->active) {
			// Especificamos el cierre de párrafo para el límite del explode
			$closing_p = '</p>';
			// Generamos un array de párrafos nuevo con el content
			$paragraphs = explode( $closing_p, $content );

			$index_paragraph = (int)floor($this->config->position) - 1;
			// Iteramos nuevamente los párrafos por clave valor, porque necesitamos ambas
			foreach ($paragraphs as $index => $paragraph) {
				// Eliminamos los espacios en blanco del content que hayan podido quedar
				if ( trim( $paragraph ) ) {
					// A cada párrafo le recuperamos su cierre
					$paragraphs[$index] .= $closing_p;
				}
				// Iteramos el array de anuncios e índices de anuncios, solo por item
				if ( $index_paragraph == $index) {
					// Si el índice del párrafo
					// queda a mitad del texto
					// se concatena la tag
					$paragraphs[(int)floor($index_paragraph)] .= "<script defer id=\"videoo-library\" data-id=\"".$this->config->id."\" src=\"https://static.videoo.tv/".$this->config->id.".js\"></script>";
				}
			}
			$content = implode( '', $paragraphs );
		}

		// Devolvemos un join sin caracter de los párrafos
		return $content;
	}

}
