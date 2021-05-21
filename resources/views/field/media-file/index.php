<?php
/**
 * @var Pollen\Field\FieldViewLoaderInterface $this
 */
?>
<?php $this->before(); ?>
<?php echo field('text', ['attrs' => $this->get('attrs')]); ?>
<?php $this->after();