<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Proxy;

use Pollen\Proxy\AbstractProxy;
use Pollen\WpUser\WpUserManagerInterface;
use Pollen\WpUser\WpUserQueryInterface;
use Pollen\WpUser\WpUserRoleInterface;
use Pollen\WpUser\WpUserRoleManagerInterface;
use WP_User;
use WP_User_Query;

/**
 * @method static WpUserQueryInterface[]|array fetch(WP_User_Query|array|null $query = null)
 * @method static WpUserQueryInterface|null get(string|int|WP_User|null $user = null)
 * @method static WpUserRoleInterface|null getRole(string $name)
 * @method static WpUserRoleInterface registerRole(string $name, WpUserRoleInterface|array $roleDef = [])
 * @method static WpUserRoleManagerInterface roleManager()
 */
class WpUser extends AbstractProxy
{
    /**
     * {@inheritDoc}
     *
     * @return WpUserManagerInterface
     */
    public static function getInstance(): WpUserManagerInterface
    {
        return parent::getInstance();
    }

    /**
     * @inheritDoc
     */
    public static function getInstanceIdentifier(): string
    {
        return WpUserManagerInterface::class;
    }
}