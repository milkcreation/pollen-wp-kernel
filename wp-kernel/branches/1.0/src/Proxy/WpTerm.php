<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Proxy;

use Pollen\Proxy\AbstractProxy;
use Pollen\WpTerm\WpTaxonomyInterface;
use Pollen\WpTerm\WpTaxonomyManagerInterface;
use Pollen\WpTerm\WpTermManagerInterface;
use Pollen\WpTerm\WpTermQueryInterface;
use WP_Term;
use WP_Term_Query;

/**
 * @method static WpTermQueryInterface[]|array fetch(WP_Term_Query|array|null $query = null)
 * @method static WpTermQueryInterface|null get(string|int|WP_Term|null $term = null)
 * @method static WpTaxonomyInterface|null getTaxonomy(string $name)
 * @method static WpTaxonomyInterface registerTaxonomy(string $name, $taxonomyDef = [])
 * @method static WpTaxonomyManagerInterface taxonomyManager()
 */
class WpTerm extends AbstractProxy
{
    /**
     * {@inheritDoc}
     *
     * @return WpTermManagerInterface
     */
    public static function getInstance(): WpTermManagerInterface
    {
        return parent::getInstance();
    }

    /**
     * @inheritDoc
     */
    public static function getInstanceIdentifier(): string
    {
        return WpTermManagerInterface::class;
    }
}