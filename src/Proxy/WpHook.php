<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Proxy;

use Pollen\Proxy\AbstractProxy;
use Pollen\Routing\RouteInterface;
use Pollen\WpHook\WpHookerInterface;
use Pollen\WpHook\WpHookableInterface;
/**
 * @method static WpHookerInterface addHookOption(string $hook, string $option, array $params = [])
 * @method static WpHookableInterface[]|array all()
 * @method static WpHookableInterface|null get(string $name)
 * @method static WpHookableInterface|null getById(int $id)
 * @method static string[]|array getHookNames()
 * @method static int[]|array getIds()
 * @method static string[]|array getPaths()
 * @method static RouteInterface|null getRoute(string $name)
 * @method static WpHookableInterface|null getRouteHookable(RouteInterface $route)
 * @method static bool hasId(int $id)
 * @method static bool hasPath(string $path)
 * @method static WpHookerInterface setRoute(WpHookableInterface $hookable, RouteInterface $route)
 */
class WpHook extends AbstractProxy
{
    /**
     * {@inheritDoc}
     *
     * @return WpHookerInterface
     */
    public static function getInstance(): WpHookerInterface
    {
        return parent::getInstance();
    }

    /**
     * @inheritDoc
     */
    public static function getInstanceIdentifier(): string
    {
        return WpHookerInterface::class;
    }
}