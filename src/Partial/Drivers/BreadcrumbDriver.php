<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Partial\Drivers;

use Pollen\Partial\Drivers\Breadcrumb\BreadcrumbCollectionInterface;
use Pollen\Partial\Drivers\BreadcrumbDriver as BaseBreadcrumbDriver;
use Pollen\WpKernel\Partial\Drivers\Breadcrumb\BreadcrumbCollection;

class BreadcrumbDriver extends BaseBreadcrumbDriver
{
    /**
     * @inheritDoc
     */
    public function collection(): BreadcrumbCollectionInterface
    {
        if (is_null($this->collection)) {
            $this->collection = new BreadcrumbCollection($this);
        }

        return $this->collection;
    }
}