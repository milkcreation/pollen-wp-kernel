<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Proxy;

use Pollen\Proxy\AbstractProxy;
use Pollen\WpTerm\WpTaxonomyInterface;
use Pollen\WpTerm\WpTaxonomyManagerInterface;

/**
 * @method static WpTaxonomyInterface[]|array all()
 * @method static WpTaxonomyInterface|null get(string $name)
 * @method static WpTaxonomyInterface register(string $name,  WpTaxonomyInterface|array $taxonomyDef)
 */
class WpTaxonomy extends AbstractProxy
{
    /**
     * {@inheritDoc}
     *
     * @return WpTaxonomyManagerInterface
     */
    public static function getInstance(): WpTaxonomyManagerInterface
    {
        return parent::getInstance();
    }

    /**
     * @inheritDoc
     */
    public static function getInstanceIdentifier(): string
    {
        return WpTaxonomyManagerInterface::class;
    }
}