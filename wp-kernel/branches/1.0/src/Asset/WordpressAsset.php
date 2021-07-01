<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Asset;

use Pollen\Asset\Asset;
use _WP_Dependency;
use Pollen\Asset\UrlAssetInterface;

class WordpressAsset extends Asset implements UrlAssetInterface
{
    /**
     * Wordpress dependency instance.
     * @var _WP_Dependency
     */
    protected _WP_Dependency $wpDependency;

    /**
     * @param string $name
     * @param _WP_Dependency $wpDependency
     */
    public function __construct(string $name, _WP_Dependency $wpDependency)
    {
        $this->wpDependency = $wpDependency;

        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        return site_url($this->wpDependency->src);
    }
}
