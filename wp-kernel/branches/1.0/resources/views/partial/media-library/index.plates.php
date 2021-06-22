<?php
/**
 * @var Pollen\Partial\PartialTemplate $this
 */
?>
<?php $this->before(); ?>
    <div <?php $this->attrs(); ?>>
        <?php $this->insert('button', $this->all()); ?>
    </div>
<?php $this->after();