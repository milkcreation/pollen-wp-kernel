<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Proxy;;

use Pollen\Proxy\AbstractProxy;
use Pollen\WpPost\WpPostTypeInterface;
use Pollen\WpPost\WpPostTypeManagerInterface;

/**
 * @method static WpPostTypeInterface[]|array all()
 * @method static WpPostTypeInterface|null get(string $name)
 * @method static WpPostTypeInterface register(string $name, WpPostTypeInterface|array $postTypeDef)
 */
class WpPostType extends AbstractProxy
{
    /**
     * {@inheritDoc}
     *
     * @return WpPostTypeManagerInterface
     */
    public static function getInstance(): WpPostTypeManagerInterface
    {
        return parent::getInstance();
    }

    /**
     * @inheritDoc
     */
    public static function getInstanceIdentifier(): string
    {
        return WpPostTypeManagerInterface::class;
    }
}