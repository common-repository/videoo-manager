<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://videoo.tv
 * @since      1.0.0
 *
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/includes
 * @author     José Lamas <email@videoo.tv>
 */
class Videoo_Manager {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Videoo_Manager_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $videoo_manager    The string used to uniquely identify this plugin.
	 */
	protected $videoo_manager;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'VIDEOO_MANAGER_VERSION' ) ) {
			$this->version = VIDEOO_MANAGER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->videoo_manager = 'videoo-manager';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Videoo_Manager_Loader. Orchestrates the hooks of the plugin.
	 * - Videoo_Manager_i18n. Defines internationalization functionality.
	 * - Videoo_Manager_Admin. Defines all hooks for the admin area.
	 * - Videoo_Manager_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-videoo-manager-loader.php';

		/**
		 * The class responsible for adding an endpoint to WordPress REST API to
		 * return information to crawler.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-videoo-manager-crawler.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */

		/**
		 * The base class and post-data interface.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-videoo-manager-base.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-videoo-manager-post-interface.php';


		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-videoo-manager-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-videoo-manager-public.php';

		/**
		 * The class responsible for defining all actions that handles ads.txt.
		 */

		/**
		 * The class responsible for defining all actions that handles the ads.txt groups.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-videoo-manager-groups.php';

		/**
		 * The class responsible for defining all actions that handles the ads.txt lines.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-videoo-manager-group-lines.php';

		/**
		 * The class responsible for defining all actions that handles the config.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-videoo-manager-config.php';

		/**
		 * The class responsible for defining helpers.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-videoo-manager-helpers.php';

		/**
		 * The class responsible for defining all actions that handles the cron.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-videoo-manager-cron.php';

		/**
		 * The class responsible for defining services.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-videoo-manager-services.php';

		$this->loader = new Videoo_Manager_Loader();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		// General
		$plugin_admin = new Videoo_Manager_Admin( $this->get_videoo_manager(), $this->get_version(), $this->get_pages());
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Pages
		$this->loader->add_action( 'admin_init', $plugin_admin, 'videoo_manager_settings_init' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'videoo_manager_register_settings' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'videoo_manager_check_settings' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'videoo_manager_register_admin_pages' );
		if ("yes" === get_option(VIDEOO_MANAGER_CONFIG_ACTIVE_ADS_TXT)) {
			$this->loader->add_action( 'admin_init', $plugin_admin, 'videoo_manager_check_duplicate_groups' );
			$this->loader->add_action( 'admin_init', $plugin_admin, 'videoo_manager_check_ext_lines' );
		}

		// Ads txt
		$plugin_group_lines = new Videoo_Manager_Group_Lines();
		$plugin_groups = new Videoo_Manager_Groups();
		// Group lines
		$this->loader->add_action( 'wp_ajax_show_group_lines', $plugin_group_lines, 'ajax_show_group_lines' );
		$this->loader->add_action( 'wp_ajax_delete_group_lines', $plugin_group_lines, 'ajax_delete_group_lines' );
		$this->loader->add_action( 'wp_ajax_update_group_lines', $plugin_group_lines, 'ajax_update_group_lines' );
		$this->loader->add_action( 'wp_ajax_save_group_new_lines', $plugin_group_lines, 'ajax_save_group_new_lines' );


		// Groups
		if ("yes" === get_option(VIDEOO_MANAGER_CONFIG_ACTIVE_ADS_TXT)) {
			$this->loader->add_action('wp_ajax_show_single_group', $plugin_groups, 'ajax_show_single_group');
			$this->loader->add_action('wp_ajax_show_groups', $plugin_groups, 'ajax_show_groups');
			$this->loader->add_action('wp_ajax_delete_group', $plugin_groups, 'ajax_delete_group');
			$this->loader->add_action('wp_ajax_delete_group', $plugin_groups, 'ajax_delete_group');
			$this->loader->add_action('wp_ajax_rename_group', $plugin_groups, 'ajax_rename_group');
			$this->loader->add_action('wp_ajax_create_group', $plugin_groups, 'ajax_create_group');
		}

		// Config
		$plugin_save_config_ajax = new Videoo_Manager_Config();
		$this->loader->add_action( 'wp_ajax_save_videoo_config_settings', $plugin_save_config_ajax, 'ajax_save_videoo_config_settings' );
		$this->loader->add_action( 'wp_ajax_toggle_active_ads_txt', $plugin_save_config_ajax, 'ajax_toggle_active_ads_txt' );


		// Cron
		$plugin_cron_data = new Videoo_Manager_Cron();
		if (true === $plugin_cron_data->is_active()) {
			if (false === $plugin_cron_data->exists_schedule($plugin_cron_data::SCHEDULE_HOURLY_KEY)) {
				$this->loader->add_filter( 'cron_schedules', $plugin_cron_data, 'videootv_add_cron_hourly_interval' );
			}
			if (false === $plugin_cron_data->exists_schedule($plugin_cron_data::SCHEDULE_DAILY_KEY)) {
				$this->loader->add_filter( 'cron_schedules', $plugin_cron_data, 'videootv_add_cron_daily_interval' );
			}

			$this->loader->add_action($plugin_cron_data::CRON_HOURLY_HOOK, $plugin_cron_data, 'videootv_ext_cron_hourly_exec');
			$this->loader->add_action($plugin_cron_data::CRON_DAILY_HOOK, $plugin_cron_data, 'videootv_ext_cron_daily_exec');
			$this->loader->add_action('admin_init', $plugin_cron_data, 'videoo_manager_schedule');

		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_config = [
			"id" => get_option('videoo_manager_config_id'),
			"position" => get_option('videoo_manager_config_position'),
			"active" => get_option(VIDEOO_MANAGER_CONFIG_ACTIVE_TAG)
		];

		$plugin_public = new Videoo_Manager_Public( $this->get_videoo_manager(), $this->get_version(), json_encode($plugin_config) );
		$plugin_crawler = new Videoo_Manager_Crawler();


		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'rest_api_init', $plugin_crawler, 'register_routes' );
		$this->loader->add_filter( 'the_content', $plugin_public, 'insert_tag' );

	}

	/**
	 * Run the loader to execute all the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_videoo_manager() {
		return $this->videoo_manager;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Videoo_Manager_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}


	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the pages for the plugin.
	 *
	 * @since     2.0.0
	 * @return    string    The pages for the plugin.
	 */
	public function get_pages() {
		$plugin_pages = [
			"top_page" => [
				"type" => "page",
				"name" => "top_page",
				"slug" => "videoo_manager",
				"title" => "Videoo Manager",
				"file" => "partials/videoo-manager-admin-display.php",
				"icon" => "../assets/img/videootv-icon-neg.png",
				"settings" => [
					["Id", "id", "text"],
					["Position", "position", "number"],
					["Active", "active", "radio"]
				]
			],
			"config" => [
				"type" => "subpage",
				"name" => "config",
				"slug" => "videoo_manager_config",
				"parent_slug" => "videoo_manager",
				"title" => "VideooTV tag config",
				"file" => "partials/videoo-manager-admin-config.php",
				"icon" => "",
				"settings" => [
					["Integration Id", "id", "text"],
					["Paragraph number", "position", "number"],
					["Active", "active", "radio"]
				]
			],
			"adstxt" => [
				"type" => "subpage",
				"name" => "ads_txt",
				"slug" => "videoo_manager_adstxt",
				"parent_slug" => "videoo_manager",
				"title" => "Ads.txt management",
				"file" => "partials/videoo-manager-admin-adstxt.php",
				"icon" => "",
				"settings" => [
					["Group", "groupform", "groupform"],
					["Active", "active", "radio"]
				]
			]
		];
		return $plugin_pages;
	}

}
