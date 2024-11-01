<?php
settings_errors( 'videoo_manager_messages' );
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
?>

<div class="wrap videoo-manager config">

		  <form action="options.php" method="post">
			<?php
			settings_fields( 'videoo_manager_config' );
			do_settings_sections( 'videoo_manager_config' );
			?>
			<div class="div_btn_save"><button class="button button-primary wide-button" name="save-videoo_config-settings" id="save-videoo_config-settings">Save settings</button></div>
		  </form>

	<div  class="preview">
		<h3>Tag Preview</h3>
		<div class="container"><div id="videoo-manager-screen" class="screen"></div></div>
	</div>

</div>
