<?php
/**
 * @var Pollen\WpKernel\Option\OptionPageTemplateInterface $this
 */
?>
<div style="margin-right:300px; margin-top:20px;">
    <div style="float:left; width: 100%;">
        <?php settings_fields($this->get('option_group')); ?>
        <?php do_settings_sections($this->get('option_group')); ?>
    </div>

    <div style="margin-right:-300px; width: 280px; float:right;">
        <div id="submitdiv">
            <h3 class="hndle"><span><?php echo 'Enregistrer'; ?></span></h3>
            <div style="padding:10px;">
                <div class="submit">
                    <?php submit_button(); ?>
                </div>
            </div>
        </div>
    </div>
</div>