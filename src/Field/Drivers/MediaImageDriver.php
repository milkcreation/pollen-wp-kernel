<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Field\Drivers;

use Pollen\WpKernel\Field\WordpressFieldDriver;

class MediaImageDriver extends WordpressFieldDriver implements MediaImageDriverInterface
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
            parent::defaultParams(), [
            /**
             * @var string $content Contenu HTML d'enrichissement de l'affichage de l'interface de saisie.
             */
            'content'    => 'Cliquez sur la zone',
            /**
             * @var string|int $default Image par défaut. Affiché lorsqu'aucune image n'est séléctionnée.
             */
            'default'    => null,
            /**
             * @var string $format Format de l'image. cover (défaut)|contain
             */
            'format'     => 'cover',
            /**
             * @var int $height Hauteur de l'image en pixel. 100 par defaut.
             */
            'height'     => 100,
            /**
             * @var bool|string $infos Etiquette d'information complémentaires. {{largeur}} x {{hauteur}} par défaut
             */
            'infos'      => true,
            /**
             * @var bool $removable Activation de la suppression de l'image active.
             */
            'removable'  => true,
            /**
             * @var string|array $size Taille de l'attachment utilisé pour la prévisualisation de l'image. 'large' par défaut.
             */
            'size'       => 'large',
            /**
             * @var int $width Largeur de l'image en pixel. 100 par défaut.
             */
            'width'      => 100,
            /**
             *
             */
            'value_none' => '',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $this->set(
            [
                'attrs.aria-selected' => 'false',
                'attrs.style'         => "max-width:{$this->get('width')}px;max-height:{$this->get('height')}px;",
            ]
        );

        if ($infos = $this->get('infos')) {
            $this->set(
                'infos',
                is_string($infos)
                    ? $infos : sprintf('%dpx / %dpx', $this->get('width'), $this->get('height'))
            );
        } else {
            $this->set('infos', '');
        }

        if ($default = $this->get('default')) {
            if (is_numeric($default)) {
                if ($img = wp_get_attachment_image_src($default, $this->get('size'))) {
                    $this->set(
                        [
                            'preview.attrs.data-default' => $img[0],
                            'preview.attrs.style'        => "background-image:url({$img[0]})",
                        ]
                    );
                }
            } elseif (is_string($default)) {
                $this->set(
                    [
                        'preview.attrs.data-default' => $default,
                        'preview.attrs.style'        => "background-image:url({$default})",
                    ]
                );
            }
        }

        if (($value = $this->getValue()) && ($value !== $this->get('value_none'))) {
            if (is_numeric($value)) {
                if ($img = wp_get_attachment_image_src($value, $this->get('size'))) {
                    $this->set(
                        [
                            'attrs.aria-selected' => 'true',
                            'preview.attrs.style' => "background-image:url({$img[0]})",
                        ]
                    );
                }
            } elseif (is_string($value)) {
                $this->set(
                    [
                        'attrs.aria-selected' => 'true',
                        'preview.attrs.style' => "background-image:url({$value})",
                    ]
                );
            }
        }

        $this->set(
            [
                'attrs.data-control'         => 'media-image',
                'attrs.data-format'          => $this->get('format'),
                'attrs.data-options'         => [
                    'value_none' => $this->get('value_none'),
                ],
                'preview.attrs.class'        => 'FieldMediaImage-preview',
                'preview.attrs.data-control' => 'media-image.preview',
                'sizer'                      => [
                    'attrs' => [
                        'class'        => 'FieldMediaImage-sizer',
                        'data-control' => 'media-image.sizer',
                        'style'        => 'width:100%;padding-top:' .
                            (100 * ($this->get('height') / $this->get('width'))) . '%',
                    ],
                ],
            ]
        );

        return parent::render();
    }
}