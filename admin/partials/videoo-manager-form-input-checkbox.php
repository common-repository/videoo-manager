<input
  type="checkbox"
  name="<?php echo esc_attr('videoo_manager_'.$pagename.'_'.$setting_id); ?>"
  id="<?php echo esc_attr('videoo_manager_'.$pagename.'_'.$setting_id); ?>"
  <?php echo  esc_attr($this->helpers->check_checked('videoo_manager_'.$pagename.'_'.$setting_id, $value)); ?>
  value="<?php echo esc_attr($value); ?>">
<label for="<?php echo  esc_attr('videoo_manager_'.$pagename.'_'.$setting_id); ?>" ><?php echo esc_html( $setting_label); ?></label>
