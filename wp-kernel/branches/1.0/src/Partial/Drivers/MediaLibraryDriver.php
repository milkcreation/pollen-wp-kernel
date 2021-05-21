<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Partial\Drivers;

use Pollen\Partial\PartialDriver;

class MediaLibraryDriver extends PartialDriver implements MediaLibraryDriverInterface
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
        return array_merge(parent::defaultParams(), [
            'button'  => [
                'tag'     => 'button',
                'content' => 'Ajouter un média',
            ],
            'options' => [
                'title'    => 'Sélectionner les fichiers à associer',
                'editing'  => true,
                'multiple' => true,
                'library'  => [],
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function parseParams(): void
    {
        parent::parseParams();

        $this->set([
            'attrs.data-control'        => 'media-library',
            'attrs.data-options'        => $this->pull('options', []),
            'button.attrs.data-control' => 'media-library.open',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function viewDirectory(): string
    {
        return __DIR__ . '/Resources/views/media-library';
    }
}