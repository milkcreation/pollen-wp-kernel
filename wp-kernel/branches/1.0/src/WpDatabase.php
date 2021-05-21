<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Database\DatabaseManagerInterface;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\WpDb\WpDb;
use Pollen\WpDb\WpDbInterface;
use Psr\Container\ContainerInterface as Container;

class WpDatabase
{
    use ContainerProxy;

    /**
     * @var DatabaseManagerInterface
     */
    protected $db;

    /**
     * @param DatabaseManagerInterface $db
     * @param Container $container
     */
    public function __construct(DatabaseManagerInterface $db, Container $container)
    {
        $this->db = $db;
        $this->setContainer($container);

        $this->containerAdd(WpDbInterface::class, new WpDb([], $this->getContainer()), true);
    }
}