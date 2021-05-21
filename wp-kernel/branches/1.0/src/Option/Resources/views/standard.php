<?php
/**
 * @var Pollen\WpKernel\Option\OptionPageViewLoader $this
 */
?>
<div>
    <?php settings_fields($this->get('name')); ?>
    <?php do_settings_sections($this->get('name')); ?>
</div>

<?php submit_button(); ?>