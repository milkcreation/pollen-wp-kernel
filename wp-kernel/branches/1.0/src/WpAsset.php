<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Asset\AssetManagerInterface;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\Support\Proxy\HttpRequestProxy;
use Pollen\WpKernel\Exception\WpRuntimeException;
use Psr\Container\ContainerInterface as Container;

class WpAsset
{
    use ContainerProxy;
    use HttpRequestProxy;

    /**
     * @var AssetManagerInterface $asset
     */
    protected $asset;

    /**
     * @param AssetManagerInterface $asset
     * @param Container $container
     */
    public function __construct(AssetManagerInterface $asset, Container $container)
    {
        if (!defined('ABSPATH')) {
            throw new WpRuntimeException('ABSPATH Constant is missing.');
        }

        if (!function_exists('site_url')) {
            throw new WpRuntimeException('site_url function is missing.');
        }

        if (!function_exists('add_action')) {
            throw new WpRuntimeException('add_action function is missing.');
        }

        $this->asset = $asset;
        $this->setContainer($container);

        $this->asset
            ->setBaseDir(ABSPATH)
            ->setBaseUrl(site_url('/'))
            ->setRelPrefix($this->httpRequest()->getRewriteBase());

        $this->asset->addGlobalJsVar('abspath', ABSPATH);
        $this->asset->addGlobalJsVar('url', site_url('/'));
        $this->asset->addGlobalJsVar('rel', $this->httpRequest()->getRewriteBase());

        global $locale;
        $this->asset->addGlobalJsVar('locale', $locale);

        add_action(
            'wp_head',
            function () {
                echo $this->asset->headerStyles();
                echo $this->asset->headerScripts();
            },
            5
        );

        add_action(
            'wp_footer',
            function () {
                echo $this->asset->footerScripts();
            },
            5
        );

        add_action(
            'admin_print_styles',
            function () {
                echo $this->asset->headerStyles();
            }
        );

        add_action(
            'admin_print_scripts',
            function () {
                echo $this->asset->headerScripts();
            }
        );

        add_action(
            'admin_print_footer_scripts',
            function () {
                echo $this->asset->footerScripts();
            }
        );
    }
}