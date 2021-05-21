<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Cookie\CookieJarInterface;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\WpKernel\Exception\WpRuntimeException;
use Psr\Container\ContainerInterface as Container;
use RuntimeException;
use WP_Site;

class WpCookie
{
    use ContainerProxy;

    /**
     * @var CookieJarInterface
     */
    protected $cookieJar;

    /**
     * @param CookieJarInterface $cookieJar
     * @param Container $container
     */
    public function __construct(CookieJarInterface $cookieJar, Container $container)
    {
        if (!defined('COOKIEHASH')) {
            throw new WpRuntimeException('COOKIEHASH Constant is missing.');
        }

        $this->cookieJar = $cookieJar;
        $this->setContainer($container);

        if (is_multisite() && $site = WP_Site::get_instance(get_current_blog_id())) {
            $domain = config()->get('cookie.domain') ?? $site->domain;
            $path = config()->get('cookie.path') ?? $site->path;

            $this->cookieJar->setDefaults($path, $domain);

            if (!config()->has('cookie.salt')) {
                $this->cookieJar->setSalt(
                    '_' . md5($this->cookieJar->domain . $this->cookieJar->path . COOKIEHASH)
                );
            }
        } else {
            if (!config()->has('cookie.salt')) {
                $this->cookieJar->setSalt('_' . COOKIEHASH);
            }
        }

        if ($cookies = config('cookie.cookies', [])) {
            foreach ($cookies as $k => $v) {
                is_numeric($k) ? $this->cookieJar->make($v) : $this->cookieJar->make($k, $v);
            }
        }
    }
}