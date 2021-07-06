<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Debug\DebugManagerInterface;
use Pollen\Support\Proxy\ContainerProxy;
use Psr\Container\ContainerInterface as Container;

class WpDebug
{
    use ContainerProxy;

    /**
     * Debug Manager instance.
     * @var DebugManagerInterface
     */
    protected DebugManagerInterface $debug;

    /**
     * @param DebugManagerInterface $debug
     * @param Container $container
     */
    public function __construct(DebugManagerInterface $debug, Container $container)
    {
        $this->debug = $debug;
        $this->setContainer($container);
    }
}
