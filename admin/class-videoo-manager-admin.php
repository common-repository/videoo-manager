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
class Videoo_Manager_Admin extends Videoo_Manager_Base {

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
	 * The config pages for this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      array    $pages    The config pages for this plugin.
	 */
	private $pages;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $videoo_manager       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $videoo_manager, $version, $pages) {
		parent::__construct();
		$this->videoo_manager = $videoo_manager;
		$this->version = $version;
		$this->pages = $pages;
	}
	/**
	 * Devuelve la propiedad $pages
	 * @return array El array de páginas a crear
	 */
	public function getPages()
	{
		return $this->pages;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Videoo_Manager_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Videoo_Manager_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->videoo_manager, plugin_dir_url( __FILE__ ) . 'css/videoo-manager-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Videoo_Manager_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Videoo_Manager_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ($this->helpers->check_page('videoo_manager_adstxt'))
		 	wp_enqueue_script( $this->videoo_manager.'_ads-txt', plugin_dir_url( __FILE__ ) . 'js/videoo-manager-ads-txt.js', array( 'jquery' ), $this->version, false );

		if ($this->helpers->check_page('videoo_manager_config'))
			wp_enqueue_script( $this->videoo_manager.'_config', plugin_dir_url( __FILE__ ) . 'js/videoo-manager-config.js', array( 'jquery' ), $this->version, false );

	}
	/**
	 * Registro de una página para el menú
	 * @since    2.0.0
	 * @return Boolean Debe devolver algo
	 */
	public function videoo_manager_admin_top_page() {
		require_once plugin_dir_path(__FILE__) . 'partials/videoo-manager-admin-display.php';
		return true;
	}

	/**
	 * Registro de una página para el menú
	 * @since    2.0.0
	 * @return Boolean Debe devolver algo
	 */
	public function videoo_manager_admin_config() {
		require_once plugin_dir_path(__FILE__) . 'partials/videoo-manager-admin-config.php';
		return true;
	}

	/**
	 * Registro de una página para el menú
	 * @since    2.0.0
	 * @return Boolean Debe devolver algo
	 */
	public function videoo_manager_admin_adstxt() {
		require_once plugin_dir_path(__FILE__) . 'partials/videoo-manager-admin-adstxt.php';
		return true;
	}

	public function videoo_manager_register_admin_pages() {
		foreach ($this->pages as $page) {
			$pagefile = $page['file'];
			$admin_page_callback = "admin_page_callback_".$page['slug'];
			$$admin_page_callback = function() use ($pagefile) {
				require_once plugin_dir_path(__FILE__) . $pagefile;
				return true;
			};
			if ("page" === $page["type"]) {
				add_menu_page(
		      $page['title'],
		      $page['title'],
		      'edit_posts',
		      $page['slug'],
		      $$admin_page_callback,
		      plugin_dir_url(__FILE__) . $page['icon'],
					20
		     );
			} else if ("subpage" === $page["type"]) {
				add_submenu_page(
		      $page['parent_slug'],
			      $page['title'],
			      $page['title'],
		      'manage_options',
		      $page['slug'],
		      $$admin_page_callback,
		     );
			}

		}
	}

	/**
	 * Itera un array y registra una página por cada ítem dentro del mismo
	 * Futuro, futura, futurum
	 * @since    2.0.0
	 * @param  Array $pages               Las páginas a registrar
	 * @return Boolean        Devuelve true si no hay error
	 */
	public function videoo_manager_settings_init()	{

		foreach ($this->pages as $page) {
			$req_array = explode('page=', filter_input(INPUT_SERVER, 'REQUEST_URI'));
			if (end($req_array) === $page['slug']) {
				$pagetype = $page['type'];
				$pageslug = ("page" === $pagetype ) ? $page['slug'] : $page['parent_slug'];
				$pagename = $page['name'];
				$pagetype = $page['type'];
				$pagetitle = $page['title'];
				$settings_section_callback = "settings_section_callback_".$page['name'];
				$$settings_section_callback = function() use($pagename, $pagetype, $pageslug, $pagetitle) {
					?>
					<!--<p><?php echo esc_html($pagetitle) ?></p>-->
					<?php
					return true;
				};

				// register a new section in the page
				add_settings_section(
					$page['name'].'_settings_section',
					$page['title'],
					$$settings_section_callback,
					$page['slug']
				);
				foreach ($page['settings'] as $setting) {
					// register a new setting for the page
					$setting_id = $setting[1];
					$setting_label = $setting[0];
					$setting_type = $setting[2];
					$setting_callback = "setting_callback_".$page['name'].'_'.$setting_id;

					// register a new field in the "wporg_settings_section" section, inside the "reading" page
					$$setting_callback = function() use ($pagetype, $pagename, $setting_id, $setting_label, $setting_type) {
						$opt = get_option('videoo_manager_'.$pagename.'_'.$setting_id);

						// output the field
						$value = isset( $opt ) ? esc_attr( $opt ) : '';
						if ("subpage" === $pagetype) {
							require plugin_dir_path(__FILE__) . "partials/videoo-manager-form-input-".$setting_type.".php";
						}
						return true;
					};
					add_settings_field(
						'videoo_manager_'.$page['name'].'_'.$setting_id,
						ucfirst($setting[0]),
						$$setting_callback,
						$page['slug'],
						$page['name'].'_settings_section'
					);
				}
			}
		}
	}

	/**
	 * Registra los settings de cada página para que se puedan guardar correctamente
	 * @since    2.0.0
	 */
	public function videoo_manager_register_settings() {
		foreach ($this->pages as $page) {
			if ("page" !== $page['type']) {
				foreach ($page['settings'] as $setting) {
					register_setting($page['slug'], 'videoo_manager_'.$page['name'].'_'.$setting[1]);
				}
			}
		}
	}

	/**
	 * Comprueba que la instalación de wordpress es correcta para el funcionamiento del plugin y en caso contrario muestra el error
	 * @since    2.0.0
	 */
	public function videoo_manager_check_settings() {

		if ($this->helpers->check_page('videoo')) {
			$path = parse_url(get_site_url(), PHP_URL_PATH);
			//Checkear que el wp está en la raíz del dominio
			if ($path !== null && $path !== false) {
				add_settings_error( 'videoo_manager_messages', 'videoo_manager_message','The wordpress installation is not in the domain root. Modifications to the ads.txt file should be made in the domain root and not inthis installation.', 'warning' );
			}

			//Checkear la recuperación de las líneas estáticas de Videoo.tv
			if ($this->getStaticLines() === false) {
				add_settings_error( 'videoo_manager_messages', 'videoo_manager_message','Could not retrieve the ads.txt lines from Videoo.tv network.', 'warning' );
			}


			//Crea estructura de directorios del plugin si no está creada
			$this->services->create_dir(VIDEOO_MANAGER_BASE_PATH);
			$this->services->create_dir(VIDEOO_MANAGER_ADS_TXT_BACKUP_DIR);
			$this->services->create_empty_working_files();

			//Checkear permisos de escritura de ficheros
			$dirWritable = $this->checkDirPerms(VIDEOO_MANAGER_BASE_PATH);
			if (true === $dirWritable) {
				$this->checkFilePerms(VIDEOO_MANAGER_ADS_TXT_BACK_FILE);
				$this->checkFilePerms(VIDEOO_MANAGER_NET_LINES_BCK);
				$this->checkDirPerms(VIDEOO_MANAGER_ADS_TXT_BACKUP_DIR);
			}
			$this->checkFilePerms(VIDEOO_MANAGER_ADS_TXT_FILE);

			//Checkear que crontab está activo
			if (false === $this->helpers->is_cron_active()) {
				add_settings_error( 'videoo_manager_messages', 'videoo_manager_message','WordPress cron must be enabled. Please put this line in yout wp-config.php file: <code>define("DISABLE_WP_CRON", false);</code>  .', 'warning' );
			}

			if ("yes" !== get_option(VIDEOO_MANAGER_CONFIG_ACTIVE_ADS_TXT) && $this->helpers->check_page('videoo_manager_adstxt')) {
				add_settings_error( 'videoo_manager_messages', 'videoo_manager_message','The ads.txt manager is not active. Please activate it to start working.', 'warning' );
			}
		}


	}

	private function checkDirPerms($dirName) {
		if(!file_exists($dirName) || !is_writable($dirName)) {
			add_settings_error( 'videoo_manager_messages', 'videoo_manager_message', $dirName.' is not writable. Is its parent directory writable by the server?', 'error' );
			return false;
		}
		return true;
	}
	private function checkFilePerms($filename) {
		if(!file_exists($filename) || !is_writable($filename)) {
			$perms = file_exists($filename) ? ' Check the file permissions. Now it\'s '.substr(decoct(fileperms($filename)),3).', requires write permissions.': ' Requires write permissions.';
			$owner_msg = '';
			$owner = @fileowner($filename);
			$web_server_user = @posix_geteuid();
			if (false !== $owner && $owner !== $web_server_user) {
				$owner_name = @posix_getpwuid($owner)['name'] ?? $owner.' (uid)';
				$web_server_user_info = @posix_getpwuid($web_server_user);
				$web_server_user_name = $web_server_user_info['name'] ?? ' ';
				$web_server_group_name = @posix_getgrgid($web_server_user_info['gid'])['name'] ?? ' ';
				$owner_msg = ' The file\'s owner is '.$owner_name.'. Change the owner user to '.$web_server_user_name.' or include '.$owner_name.' in the '.$web_server_group_name.' group.';
			}

			add_settings_error( 'videoo_manager_messages', 'videoo_manager_message', $filename.' is not writable.'.$owner_msg.$perms, 'error' );
			return false;
		}
		return true;
	}

	public function videoo_manager_check_duplicate_groups() {
		if ($this->helpers->check_page('videoo_manager_adstxt')) {
			$videoo_manager_groups = new Videoo_Manager_Groups();
			$videoo_manager_groups->resolve_duplicate_group_names();
		}
	}

	public function videoo_manager_check_ext_lines() {
		if ($this->helpers->check_page('videoo_manager_adstxt')) {
			$videoo_manager_groups = new Videoo_Manager_Group_Lines();
			$videoo_manager_groups->save_ext_group_lines();
		}
	}

}
