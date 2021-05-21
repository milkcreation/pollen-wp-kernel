<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Metabox\Drivers;

use Pollen\Metabox\MetaboxDriverInterface;
use Pollen\Metabox\Drivers\FilefeedDriver as BaseFilefeedDriver;

class FilefeedDriver extends BaseFilefeedDriver
{
    /**
     * @inheritDoc
     */
    public function boot(): MetaboxDriverInterface
    {
        parent::boot();

        add_action(
            'admin_enqueue_scripts',
            function () {
                @wp_enqueue_media();
            }
        );
        return $this;
    }
}
