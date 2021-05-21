<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Field\Drivers;

use Pollen\WpKernel\Field\WordpressFieldDriver;

class MediaFileDriver extends WordpressFieldDriver implements MediaFileDriverInterface
{
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        if (!$this->isBooted()) {
            add_action(
                'admin_enqueue_scripts',
                function () {
                    @wp_enqueue_media();
                }
            );
        }
        parent::boot();
    }

    /**
     * @inheritDoc
     */
    public function defaultParams(): array
    {
        return array_merge(
            parent::defaultParams(),
            [
                /**
                 *
                 */
                'options'  => [],
                /**
                 * @var string filetype Type de fichier permis ou MimeType. ex. image|image/png|video|video/mp4|application/pdf
                 */
                'filetype' => '',
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $defaultClasses = [
            'addnew' => 'FieldMediaFile-addnew',
            'input'  => 'FieldMediaFile-input',
            'reset'  => 'FieldMediaFile-reset ThemeButton--close',
            'wrap'   => 'FieldMediaFile-wrap ThemeInput--media',
        ];
        foreach ($defaultClasses as $k => $v) {
            $this->set(["classes.{$k}" => sprintf($this->get("classes.{$k}", '%s'), $v)]);
        }

        $media_id = $this->get('value', 0);
        if (!$filename = get_attached_file($media_id)) {
            $media_id = 0;
        }

        $this->set(
            [
                'attrs'                                   => array_merge(
                    ['placeholder' => 'Cliquez pour ajouter un fichier'],
                    $this->get('attrs', []),
                    [
                        'autocomplete' => 'off',
                        'data-control' => 'media-file',
                        'data-value'   => $media_id ? get_the_title($media_id) . ' &rarr; ' . basename($filename) : '',
                        'disabled',
                    ]
                ),
                'attrs.data-options.classes'              => $this->get('classes', []),
                'attrs.data-options.library'              => array_merge(
                    $this->get('options', []),
                    [
                        'editing'  => true,
                        'multiple' => false,
                    ]
                ),
                'attrs.data-options.library.library.type' => $this->get('filetype'),
            ]
        );

        return parent::render();
    }
}
