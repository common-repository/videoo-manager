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
class Videoo_Manager_Cron extends Videoo_Manager_Base
{

	const SCHEDULE_HOURLY_KEY = 'hourly';

	const SCHEDULE_DAILY_KEY = 'daily';

	const CRON_HOURLY_HOOK = 'videootv_ext_cron_hourly_hook';

	const CRON_DAILY_HOOK = 'videootv_ext_cron_daily_hook';

	private $plugin_group_lines;

	private $schedule_hourly = [];
	private $schedule_daily = [];


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		parent::__construct();
		$this->set_schedulers();
		$this->plugin_group_lines = new Videoo_Manager_Group_Lines();
	}


	public function is_active() {
		return $this->helpers->is_cron_active();
	}
	/**
	 * videootv_ext_cron_hourly_exec
	 *
	 * @return void
	 */
	public function videootv_ext_cron_hourly_exec()
	{
		//Download Videoo.tv lines from remote and local save
		$this->services->get_remote_sellers_publisher_id();
		$this->services->get_remote_static_videootv_lines();

		//Check videootv static lines
		if ("yes" === get_option(VIDEOO_MANAGER_CONFIG_ACTIVE_ADS_TXT)) {
			$this->plugin_group_lines->save_ext_group_lines();
		}


	}

	/**
	 * videootv_ext_cron_daily_exec
	 *
	 * @return void
	 */
	public function videootv_ext_cron_daily_exec()
	{
		//Daily backup ads.txt
		$this->services->make_adstxt_backup();
	}

	/**
	 * videoo_manager_schedule
	 *
	 * @return void
	 */
	public function videoo_manager_schedule()
	{
		if (!wp_next_scheduled(self::CRON_HOURLY_HOOK)) {
			wp_schedule_event(time(), self::SCHEDULE_HOURLY_KEY, self::CRON_HOURLY_HOOK);
		}

		if (!wp_next_scheduled(self::CRON_DAILY_HOOK)) {
			wp_schedule_event(time(), self::SCHEDULE_DAILY_KEY, self::CRON_DAILY_HOOK);
		}
	}

	public function videootv_add_cron_hourly_interval($schedules)
	{
		array_merge($schedules, $this->schedule_hourly);
		return $schedules;
	}

	public function videootv_add_cron_daily_interval($schedules)
	{
		array_merge($schedules, $this->schedule_daily);
		return $schedules;
	}

	public function exists_schedule($schedule)
	{
		return array_key_exists($schedule, wp_get_schedules());
	}

	public function videoo_manager_unschedule()
	{
		$timestamp = wp_next_scheduled(self::CRON_HOURLY_HOOK);
		wp_unschedule_event($timestamp, self::CRON_HOURLY_HOOK);

		$timestamp = wp_next_scheduled(self::CRON_DAILY_HOOK);
		wp_unschedule_event($timestamp, self::CRON_DAILY_HOOK);
	}


	private function set_schedulers()
	{
		$this->schedule_hourly[self::SCHEDULE_HOURLY_KEY] = [ //Default hourly
			'interval' => 3600,
			'display' => esc_html__('Once Hourly')];

		$this->schedule_daily[self::SCHEDULE_DAILY_KEY] = [ //Default daily
			'interval' => 86400,
			'display' => esc_html__('Once Daily')];
	}

}
