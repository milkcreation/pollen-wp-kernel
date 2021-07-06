<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Asset\AssetManagerInterface;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\Support\Proxy\HttpRequestProxy;
use Pollen\Support\Html;
use Pollen\WpKernel\Asset\WordpressAsset;
use Pollen\WpKernel\Exception\WpRuntimeException;
use Psr\Container\ContainerInterface as Container;
use WP_Dependencies;
use WP_Scripts;
use WP_Styles;

class WpAsset
{
    use ContainerProxy;
    use HttpRequestProxy;

    protected AssetManagerInterface $asset;

    /**
     * @param AssetManagerInterface $asset
     * @param Container $container
     */
    public function __construct(AssetManagerInterface $asset, Container $container)
    {
        if (!defined('ABSPATH')) {
            throw new WpRuntimeException('ABSPATH Constant is missing.');
        }

        if (!function_exists('home_url')) {
            throw new WpRuntimeException('home_url function is missing.');
        }

        if (!function_exists('site_url')) {
            throw new WpRuntimeException('site_url function is missing.');
        }

        if (!function_exists('add_action')) {
            throw new WpRuntimeException('add_action function is missing.');
        }

        $this->asset = $asset;
        $this->setContainer($container);

        if ($this->asset->getBasePath() === null) {
            $this->asset->setBasePath(ABSPATH);
        }

        if ($this->asset->getBaseUrl() === null) {
            $this->asset->setBaseUrl(home_url('/'));
        }

        $this->asset->addGlobalJsVar(
            'wp',
            [
                'abspath'  => ABSPATH,
                'home_url' => home_url('/'),
                'site_url' => site_url('/'),
                'scope'    => $this->httpRequest()->getRewriteBase(),
            ]
        );

        global $locale;
        $this->asset->addGlobalJsVar('locale', $locale);


        add_action(
            'wp_head',
            function () {
                echo $this->asset->getHead();
            },
            5
        );

        add_action(
            'wp_footer',
            function () {
                echo $this->asset->getFooter();
            },
            5
        );

        add_action(
            'admin_print_scripts',
            function () {
                echo $this->asset->getHead();
            }
        );

        add_action(
            'admin_print_footer_scripts',
            function () {
                echo $this->asset->getFooter();
            }
        );

        // remove_action('wp_head', 'wp_print_styles', 8);
        // remove_action('wp_head', 'wp_print_head_scripts', 9);
        // remove_action('wp_footer', 'wp_print_footer_scripts', 20);

        add_action('wp_head', [$this, 'wpHeadStylesAsAsset']);
        add_action('wp_footer', [$this, 'wpFooterScriptsAsAsset']);
    }

    /**
     * Handle Wordpress head queued styles as Pollen asset queue.
     *
     * @return void
     */
    public function wpHeadStylesAsAsset(): void
    {
        if (!function_exists('site_url')) {
            throw new WpRuntimeException('site_url function is missing.');
        }

        global $wp_styles;

        do_action('wp_print_styles');

        if (!($wp_styles instanceof WP_Styles)) {
            return;
        }

        $styleHandles = $this->wpDependencyDoItems($wp_styles);

        foreach ($styleHandles as $handle) {
            $wpDep = $wp_styles->query($handle);
            $name = "$handle-css";

            $this->asset->enqueueCss(
                new WordpressAsset($name, $wpDep),
                [
                    'id'    => $name,
                    'media' => isset($wpDep->args) ? Html::e($wpDep->args) : 'all',
                ]
            );
        }
    }

    /**
     * Handle Wordpress footer queued styles as Pollen asset queue.
     *
     * @return void
     */
    public function wpFooterScriptsAsAsset(): void
    {
        if (!function_exists('site_url')) {
            throw new WpRuntimeException('site_url function is missing.');
        }

        if (!function_exists('apply_filters')) {
            throw new WpRuntimeException('apply_filters function is missing.');
        }

        global $wp_scripts, $concatenate_scripts;

        if (!($wp_scripts instanceof WP_Scripts)) {
            return;
        }

        script_concat_settings();
        $wp_scripts->do_concat = $concatenate_scripts;

        $scriptHandles = $this->wpDependencyDoItems($wp_scripts);

        foreach ($scriptHandles as $handle) {
            $wpDep = $wp_scripts->query($handle);
            $name = "$handle-js";

            $this->asset->enqueueJs(
                new WordpressAsset($name, $wpDep),
                true,
                [
                    'id' => $name,
                ]
            );
        }

        if (apply_filters('print_footer_scripts', true)) {
            _print_scripts();
        }

        $wp_scripts->reset();
    }

    /**
     * @param WP_Dependencies $wpDeps
     *
     * @return array
     */
    protected function wpDependencyDoItems(WP_Dependencies $wpDeps): array
    {
        $handles = $wpDeps->queue;
        $wpDeps->all_deps($handles);

        foreach ($wpDeps->to_do as $key => $handle) {
            if (isset($wpDeps->registered[$handle]) && !in_array($handle, $wpDeps->done, true)) {
                $wpDeps->done[] = $handle;

                unset($wpDeps->to_do[$key]);
            }
        }

        return $wpDeps->done;
    }
}