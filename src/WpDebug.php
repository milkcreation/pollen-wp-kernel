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

        if (!function_exists('add_action')) {
            return;
        }

        if ($this->debug->config('asset.autoloader', true) === true) {
            add_action(
                'wp_head',
                function () {
                    if ($this->debug->debugBar()->isEnabled()) {
                        echo "<!-- DebugBar -->";
                        echo $this->debug->debugBar()->renderHeadCss();
                        echo $this->debug->debugBar()->renderHeadJs();
                        echo "<!-- / DebugBar -->";
                    }
                },
                999999
            );

            add_action(
                'wp_footer',
                function () {
                    if ($this->debug->debugBar()->isEnabled()) {
                        echo $this->debug->debugBar()->render();
                    }
                },
                999999
            );

            add_action(
                'admin_head',
                function () {
                    if ($this->debug->debugBar()->isEnabled()) {
                        echo "<!-- Debug Bar -->";
                        echo $this->debug->debugBar()->renderHeadCss();
                        echo $this->debug->debugBar()->renderHeadJs();
                        echo "<!-- / Debug Bar -->";
                    }
                },
                999999
            );

            add_action(
                'admin_footer',
                function () {
                    if ($this->debug->debugBar()->isEnabled()) {
                        echo $this->debug->debugBar()->render();
                    }
                },
                999999
            );
        }
    }
}
