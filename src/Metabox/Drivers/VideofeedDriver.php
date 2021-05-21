<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Metabox\Drivers;

use Pollen\Metabox\Drivers\VideofeedDriver as BaseVideofeedDriver;
use Pollen\Metabox\MetaboxDriverInterface;

class VideofeedDriver extends BaseVideofeedDriver
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
