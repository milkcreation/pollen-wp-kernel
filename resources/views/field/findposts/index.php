<?php
/**
 * @var Pollen\Field\FieldViewLoaderInterface $this
 */
?>
<?php $this->before(); ?>
<div data-control="findposts.wrapper">
    <?php echo field('text', [
        'attrs' => $this->get('attrs')
    ]); ?>
    <?php echo field('button', [
        'attrs' => [
            'data-control' => 'findposts.opener'
        ]
    ]); ?>
    <?php $this->insert('modal', $this->all()); ?>
    <?php $this->insert('tmpl', $this->all()); ?>
</div>
<?php $this->after();