<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Proxy;

use Pollen\Pagination\Adapters\WpQueryPaginatorInterface;
use Pollen\Proxy\AbstractProxy;
use Pollen\WpPost\WpPostManagerInterface;
use Pollen\WpPost\WpPostTypeManagerInterface;
use Pollen\WpPost\WpPostQueryInterface;
use Pollen\WpPost\WpPostTypeInterface;
use WP_Post;
use WP_Query;

/**
 * @method static WpPostQueryInterface[]|array fetch(WP_Query|array|null $query = null)
 * @method static WpPostQueryInterface|null get(string|int|WP_Post|null $post = null)
 * @method static WpPostTypeInterface|null getType(string $name)
 * @method static WpQueryPaginatorInterface|null paginator()
 * @method static WpPostTypeManagerInterface|null postTypeManager()
 * @method static WpPostTypeInterface|null registerType(string $name, array|WpPostTypeInterface $postTypeDef = [])
 */
class WpPost extends AbstractProxy
{
    /**
     * {@inheritDoc}
     *
     * @return WpPostManagerInterface
     */
    public static function getInstance(): WpPostManagerInterface
    {
        return parent::getInstance();
    }

    /**
     * @inheritDoc
     */
    public static function getInstanceIdentifier(): string
    {
        return WpPostManagerInterface::class;
    }
}