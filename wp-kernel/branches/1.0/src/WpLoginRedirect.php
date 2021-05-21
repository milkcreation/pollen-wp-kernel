<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Http\RedirectResponse;
use Pollen\Support\Proxy\HttpRequestProxy;
use Pollen\WpKernel\Exception\WpRuntimeException;

/**
 * @see https://fr.wordpress.org/plugins/sf-move-login/
 */
class WpLoginRedirect
{
    use HttpRequestProxy;

    /**
     * Indicateur d'activation.
     * @var boolean
     */
    protected $enabled = false;

    /**
     * @var array
     */
    protected $endpoints = [];

    /**
     * Cartographie des redirections associés aux actions.
     * @var array
     */
    protected $map = [
        'login'        => 'wp-login.php',
        'logout'       => 'wp-login.php?action=logout',
        'resetpass'    => 'wp-login.php?action=resetpass',
        'lostpassword' => 'wp-login.php?action=lostpassword',
        'register'     => 'wp-login.php?action=register',
        'postpass'     => 'wp-login.php?action=postpass',
    ];

    /**
     * CONSTRUCTEUR.
     *
     * @param array $endpoints Liste des points d'accès associés aux actions d'authentification.
     *
     * @return void
     */
    public function __construct(array $endpoints = [])
    {
        if (!function_exists('add_action')) {
            throw new WpRuntimeException('add_action function is missing.');
        }

        if (!function_exists('is_admin')) {
            throw new WpRuntimeException('is_admin function is missing.');
        }

        if (!function_exists('apply_filters')) {
            throw new WpRuntimeException('apply_filters function is missing.');
        }

        if (!function_exists('get_option')) {
            throw new WpRuntimeException('get_option function is missing.');
        }

        $this->endpoints = $endpoints;

        add_action('after_setup_theme', function () {
            global $pagenow;

            if (!(is_admin() &&
                !((defined('DOING_AJAX') && DOING_AJAX) || ('admin-post.php' === $pagenow && req::input('action'))))
            ) {
                return;
            }

            $scheme = is_user_admin() ? 'logged_in' : apply_filters('auth_redirect_scheme', '');

            if (!wp_validate_auth_cookie('', $scheme)) {
                $this->_accessDenied();
            }
        }, 12);

        add_action('admin_init', function () {
            if (get_option('wp_login_redirect_endpoints', '') !== $this->_getEndpoints()) {
                $this->_flushRewriteRules();
            }
        });

        add_action('login_init', function () {
            if (!is_user_logged_in()) {
                if (get_option('wp_login_redirect_endpoints', '') !== $this->_getEndpoints()) {
                    $this->_flushRewriteRules();
                }

                if ($this->_isCheatin()) {
                    $this->_accessDenied();
                }
            }
        }, 0);

        add_filter('site_url', function ($url, $path, $scheme, $blog_id) {
            if (!empty($path) &&
                is_string($path) &&
                false === strpos($path, '..') &&
                0 === strpos(ltrim($path, '/'), 'wp-login.php')
            ) {
                $blog_id = (int)$blog_id;

                // Base url
                if (empty($blog_id) || get_current_blog_id() === $blog_id || !is_multisite()) {
                    $url = get_option('siteurl');
                } else {
                    switch_to_blog($blog_id);
                    $url = get_option('siteurl');
                    restore_current_blog();
                }

                $url = set_url_scheme($url, $scheme);
                return rtrim($url, '/') . '/' . ltrim($this->_setPath($path), '/');
            }

            return $url;
        }, 10, 4);

        add_filter('network_site_url', function ($url, $path, $scheme) {
            if (!empty($path) &&
                is_string($path) &&
                false === strpos($path, '..') &&
                0 === strpos(ltrim($path, '/'), 'wp-login.php')
            ) {
                return site_url($path, $scheme);
            }

            return $url;
        }, 10, 3);

        add_filter('logout_url', function ($url) {
            return $this->_getActionUrl('logout', $url);
        }, 1);

        add_filter('lostpassword_url', function ($url) {
            return $this->_getActionUrl('lostpassword', $url);
        }, 1);

        add_filter('wp_redirect', function ($location) {
            $baseuri = explode('?', $location);
            if (site_url(reset($baseuri)) === site_url('wp-login.php')) {
                return site_url($location, 'wp-login.php', 'login', get_current_blog_id());
            }

            return $location;
        }, 1);

        remove_action('template_redirect', 'wp_redirect_admin_locations', 1000);
    }

    /**
     * Lancement de l'action lorsque l'accès n'est pas autorisé.
     *
     * @return mixed
     */
    private function _accessDenied()
    {
        return (new RedirectResponse(home_url('404')))->send();
    }

    /**
     * Réinitialisation des régles de réécritures d'url.
     *
     * @return void
     */
    private function _flushRewriteRules(): void
    {
        global $wp_rewrite;

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';

        /** @todo test d'écriture du fichier .htaccess * */
        update_option('rewrite_rules', '');
        $wp_rewrite->wp_rewrite_rules();

        if (function_exists('save_mod_rewrite_rules')) {
            save_mod_rewrite_rules();
        }

        if (function_exists('iis7_save_url_rewrite_rules')) {
            iis7_save_url_rewrite_rules();
        }

        update_option('wp_login_redirect_endpoints', $this->_getEndpoints());

        wp_redirect(
            (stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://') .
            $_SERVER['HTTP_HOST'] .
            $_SERVER['REQUEST_URI']
        );
    }

    /**
     * Récupération de l'url associée à un action.
     *
     * @param string $action login|logout|resetpass|lostpassword|register|postpass.
     * @param string $url
     *
     * @return string
     */
    private function _getActionUrl(string $action, string $url): string
    {
        if (!$endpoint = $this->_getEndpoint($action)) {
            return $url;
        }

        if ($url && (false === strpos($url, '/' . $endpoint))) {
            $path = $this->_getEndpoint('login') ?: 'wp-login.php';

            $url = str_replace(
                [$path, '&amp;', '?amp;', '&'],
                [$endpoint, '&', '?', '&amp;'],
                isset($_action) ? $url : remove_query_arg('action', $url)
            );
        }

        return $url;
    }

    /**
     * Récupération de la liste.
     *
     * @param bool $encoded
     *
     * @return string|array
     */
    private function _getEndpoints(bool $encoded = true)
    {
        return $encoded ? base64_encode(serialize($this->endpoints)) : $this->endpoints;
    }

    /**
     * Récupération de la liste.
     *
     * @param string $action login|logout|resetpass|lostpassword|register|postpass.
     *
     * @return string
     */
    private function _getEndpoint(string $action): ?string
    {
        return $this->endpoints[$action] ?? null;
    }

    /**
     * Vérification d'une tentative d'accès à une url protégée.
     *
     * @return bool
     */
    private function _isCheatin(): bool
    {
        $rel = rtrim($this->httpRequest()->getBaseUrl() . $this->httpRequest()->getPathInfo(), '/');

        foreach ($this->map as $action => $endpoint) {
            $root = parse_url(home_url('/'));
            $endpoint = (isset($root['path']) ? rtrim($root['path'], '/') : '') . "/{$endpoint}";

            if (($rel === $endpoint) && $this->_getEndpoint($action)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Définition des chemins des urls d'accès.
     *
     * @param string $path
     *
     * @return string
     */
    private function _setPath(string $path): string
    {
        $slugs = $this->_getEndpoints(false);

        $query = parse_url($path, PHP_URL_QUERY);
        if (!empty($query)) {
            parse_str($query, $params);
            $action = !empty($params['action']) ? $params['action'] : 'login';

            if (isset($params['key'])) {
                $action = 'resetpass';
            }

            if (!isset($slugs[$action]) && !isset($other[$action]) && false === has_filter('login_form_' . $action)) {
                $action = 'login';
            }
        } else {
            $action = 'login';
        }

        if (isset($slugs[$action])) {
            $path = str_replace('wp-login.php', $slugs[$action], $path);
            $path = remove_query_arg('action', $path);
        } else {
            $path = str_replace('wp-login.php', $slugs['login'], $path);
            $path = remove_query_arg('action', $path);
            $path = add_query_arg('action', $action, $path);
        }

        return '/' . ltrim($path, '/');
    }
}