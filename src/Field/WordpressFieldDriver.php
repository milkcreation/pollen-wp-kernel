<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Field;

use Pollen\Field\FieldDriver;

abstract class WordpressFieldDriver extends FieldDriver implements WordpressFieldDriverInterface
{
    /**
     * @inheritDoc
     */
    public function viewDirectory(): string
    {
        return __DIR__ . '/Resources/views/' . $this->getAlias();
    }
}