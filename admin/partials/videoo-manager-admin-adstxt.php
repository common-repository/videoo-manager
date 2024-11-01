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

$is_active = "yes" === get_option(VIDEOO_MANAGER_CONFIG_ACTIVE_ADS_TXT);
$is_active_class = $is_active ? '' : 'disabled';
?>

<div class="<?=esc_attr('wrap videoo-manager ads_txt '.$is_active_class)?>" id="ads-txt-lines">
  <form>
    <?php
    settings_fields( 'videoo_manager_adstxt' );

    do_settings_sections( 'videoo_manager_adstxt' );
    ?>
    <div class="div_btn_save"><button class="button button-primary wide-button" name="save-ads_txt-settings" id="save-ads-txt-settings">Save settings</button></div>
  </form>
	<?php if (true === $is_active) { ?>
  <div id="groups" class="groups">
    <h3>Groups</h3>
    <filedset class="group-management action-buttons">
      <button class="button button-primary wide-button create-group" id="create-group">Create Group <span class="dashicons dashicons-plus-alt"></span></button>
    </filedset>
  </div>
	<?php } ?>
</div>
