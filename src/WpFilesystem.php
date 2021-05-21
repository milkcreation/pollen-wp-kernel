<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Filesystem\StorageManagerInterface;
use Pollen\Support\Proxy\ContainerProxy;
use Psr\Container\ContainerInterface as Container;

class WpFilesystem
{
    use ContainerProxy;
    /**
     * @var StorageManagerInterface
     */
    protected $storage;

    /**
     * @param StorageManagerInterface $storage.
     */
    public function __construct(StorageManagerInterface $storage, Container $container)
    {
        $this->storage = $storage;
        $this->setContainer($container);
    }
}