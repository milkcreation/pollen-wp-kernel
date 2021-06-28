<?php
/**
 * @var Pollen\WpKernel\Option\OptionPageTemplateInterface $this
 */
?>
<div class="wrap">
    <?php if ($title = $this->get('page_title', '')) : ?>
        <h1><?php echo $title; ?></h1><br>
    <?php endif; ?>

    <?php $this->isSettingsPage() ?: settings_errors(); ?>

    <form method="post" action="<?php echo admin_url('options.php'); ?>">
        <?php $this->insert($this->get('template', 'standard'), $this->all()); ?>
    </form>
</div>