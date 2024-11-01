<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://videoo.tv
 * @since      1.0.0
 *
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/admin/partials
 */
  // show error/update messages
  settings_errors( 'videoo_manager_messages' );
?>

<div class="wrap videoo-manager">
  <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
</div>
