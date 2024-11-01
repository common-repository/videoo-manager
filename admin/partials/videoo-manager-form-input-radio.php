<fieldset class="fieldset_input_radio_button">
  <label>Active:</label>
  <input type="radio" name="<?php echo esc_attr('videoo_manager_'.$pagename.'_'.$setting_id); ?>" id="<?php echo esc_attr('videoo_manager_'.$pagename.'_'.$setting_id.'_yes'); ?>" <?php echo  esc_attr($this->helpers->check_checked("yes", $value)); ?> value="yes">
  <label for="<?php echo esc_attr('videoo_manager_'.$pagename.'_'.$setting_id.'_yes'); ?>" >Yes</label>

  <input type="radio" name="<?php echo esc_attr('videoo_manager_'.$pagename.'_'.$setting_id); ?>" id="<?php echo esc_attr('videoo_manager_'.$pagename.'_'.$setting_id.'_no'); ?>" <?php echo esc_attr($this->helpers->check_checked("no", $value)); ?> value="no">
  <label for="<?php echo esc_attr('videoo_manager_'.$pagename.'_'.$setting_id.'_no'); ?>" >No</label>
</fieldset>
