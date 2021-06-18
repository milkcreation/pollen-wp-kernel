<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Kernel\ApplicationInterface;
use Pollen\Kernel\KernelInterface;

interface WpKernelInterface extends KernelInterface
{
    /**
     * {@inheritDoc}
     *
     * @return WpApplicationInterface
     */
    public function getApp(): ApplicationInterface;
}
