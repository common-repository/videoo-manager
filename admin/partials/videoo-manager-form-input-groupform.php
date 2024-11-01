<?php $is_active = "yes" === get_option(VIDEOO_MANAGER_CONFIG_ACTIVE_ADS_TXT);
if (true === $is_active) {
?>
<div class="<?php echo esc_attr($setting_id); ?>" id="<?php echo esc_attr($setting_id); ?>">
  <h3 class="group-name">
    <?php echo esc_html($setting_label); ?>
  </h3>
  <div class="group-lines">
  </div>
  <fieldset class="group-line-fieldset newline" id="add-new-group-line-fieldset">
    <span title="Add new line" class="dashicons dashicons-plus"></span>
    <input title="This field is required!" type="text" name="group-line-domain" id="group-line-domain" class="group-line-domain" placeholder="Domain*">
    <input title="This field is required!" type="text" name="group-line-network" id="group-line-network" class="group-line-network" placeholder="Account*">
    <select name="group-line-type" id="group-line-type" class="group-line-type">
      <option value="DIRECT">DIRECT</option>
      <option value="RESELLER">RESELLER</option>
    </select>
    <input title="This field is optional." type="text" name="group-line-certification" id="group-line-certification" class="group-line-certification" placeholder="Certification Authority">
    <button class="add-new-group-line dashicons dashicons-plus-alt" id="add-new-group-line"></button>
  </fieldset>
  <textarea name="bulk-group-lines" id="bulk-group-lines" rows="15" class="bulk-group-lines" placeholder="Add bulk lines here, ¡and remeber to separate them by line breaks!"></textarea>
  <button class="button button-primary wide-button add-bulk-group-lines" id="add-bulk-group-lines">Add bulk lines <span class="dashicons dashicons-plus-alt"></span></button>

</div>
<?php }?>
