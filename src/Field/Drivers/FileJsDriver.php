<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Field\Drivers;

use Pollen\Field\Drivers\FileJsDriver as BaseFileJsDriver;

class FileJsDriver extends BaseFileJsDriver
{
    /**
     * @inheritDoc
     */
    public function defaultParams(): array
    {
        return array_merge(
            parent::defaultParams(),
            [
                'dirname' => WP_CONTENT_DIR . '/uploads',
            ]
        );
    }
}