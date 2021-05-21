<?php
/**
 * @var Pollen\Field\FieldViewLoaderInterface $this
 */
?>
<?php $this->before(); ?>
    <div <?php $this->attrs(); ?>>
        <?php echo partial('tag', [
            'attrs' => [
                'class'        => 'FieldMediaImage-open',
                'data-control' => 'media-image.open',
                'href'         => '#' . $this->get('attrs.id', ''),
            ],
            'tag'   => 'a',
        ]); ?>

        <?php echo partial('tag', $this->get('preview', [])); ?>

        <?php echo partial('tag', $this->get('sizer', [])); ?>

        <?php if ($infos = $this->get('infos', '')) : ?>
            <span class="FieldMediaImage-info"><?php echo $infos; ?></span>
        <?php endif; ?>

        <?php if ($content = $this->get('content', '')) : ?>
            <div class="FieldMediaImage-content"><?php echo $content; ?></div>
        <?php endif; ?>

        <?php echo field('hidden', [
            'attrs' => [
                'class' => 'FieldMediaImage-input',
                'data-control' => 'media-image.input'
            ],
            'name'  => $this->getName(),
            'value' => $this->getValue(),
        ]); ?>

        <?php if ($this->get('removable', true)) : ?>
            <?php echo partial('tag', [
                'attrs' => [
                    'class'        => 'FieldMediaImage-remove ThemeButton--remove',
                    'data-control' => 'media-image.remove',
                    'href'         => '#' . $this->get('attrs.id', ''),
                ],
                'tag'   => 'a',
            ]); ?>
        <?php endif; ?>
    </div>
<?php $this->after();