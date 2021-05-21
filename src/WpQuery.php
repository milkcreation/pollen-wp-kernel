<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Support\Proxy\EventProxy;
use Pollen\WpKernel\Exception\WpRuntimeException;
use WP_Query;

class WpQuery implements WpQueryInterface
{
    use EventProxy;

    /**
     * Liste des indicateurs de condition permis.
     * @see https://codex.wordpress.org/Conditional_Tags
     * @var array
     */
    protected $ctags = [
        '404'               => 'is_404',
        'archive'           => 'is_archive',
        'attachment'        => 'is_attachment',
        'author'            => 'is_author',
        'category'          => 'is_category',
        'date'              => 'is_date',
        'day'               => 'is_day',
        'front'             => 'is_front_page',
        'home'              => 'is_home',
        'month'             => 'is_month',
        'page'              => 'is_page',
        'paged'             => 'is_paged',
        'post_type_archive' => 'is_post_type_archive',
        'search'            => 'is_search',
        'single'            => 'is_single',
        'singular'          => 'is_singular',
        'sticky'            => 'is_sticky',
        'tag'               => 'is_tag',
        'tax'               => 'is_tax',
        'template'          => 'is_template',
        'time'              => 'is_time',
        'year'              => 'is_year',
    ];

    /**
     * @return void
     */
    public function __construct()
    {
        if (!function_exists('add_action')) {
            throw new WpRuntimeException('add_action function is missing.');
        }

        add_action(
            'pre_get_posts',
            function (WP_Query &$wp_query) {
                if ($wp_query->is_main_query()) {
                    foreach (config('wp.query', []) as $ctag => $query_args) {
                        if (in_array($ctag, $this->ctags, true) && $wp_query->$ctag()) {
                            foreach ($query_args as $query_arg => $value) {
                                $wp_query->set($query_arg, $value);
                            }
                        }
                    }
                }

                $this->event()->trigger('wp.query', [&$wp_query]);
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function is(string $ctag): bool
    {
        if (preg_match('/^([\w]+)@wp$/', $ctag, $matches)) {
            $ctag = $matches[1];
        }
        return isset($this->ctags[$ctag]) ? call_user_func($this->ctags[$ctag]) : false;
    }

    /**
     * @inheritdoc
     */
    public function ctag(): ?string
    {
        if (is_404()) {
            return '404';
        }
        if (is_search()) {
            return 'search';
        }
        if (is_front_page()) {
            return 'front';
        }
        if (is_home()) {
            return 'home';
        }
        if (is_post_type_archive()) {
            return 'post_type_archive';
        }
        if (is_tax()) {
            return 'tax';
        }
        if (is_attachment()) {
            return 'attachment';
        }
        if (is_single()) {
            return 'single';
        }
        if (is_page()) {
            return 'page';
        }
        if (is_singular()) {
            return 'singular';
        }
        if (is_category()) {
            return 'category';
        }
        if (is_tag()) {
            return 'tag';
        }
        if (is_author()) {
            return 'author';
        }
        if (is_date()) {
            return 'date';
        }
        if (is_archive()) {
            return 'archive';
        }
        return null;
    }
}