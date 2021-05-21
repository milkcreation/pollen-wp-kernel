<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Field\Drivers;

use Illuminate\Support\Collection;
use Pollen\Http\JsonResponse;
use Pollen\Http\ResponseInterface;
use Pollen\Field\Drivers\SuggestDriver as BaseSuggestDriver;
use Pollen\WpPost\WpPostQuery;
use Pollen\WpTerm\WpTermQuery;
use Pollen\WpUser\WpUserQuery;
use WP_Post;
use WP_Query;
use WP_Term;
use WP_Term_Query;
use WP_User;
use WP_User_Query;

class SuggestDriver extends BaseSuggestDriver implements SuggestDriverInterface
{
    /**
     * @inheritDoc
     */
    public function parseParams(): void
    {
        $ajax = $this->get('ajax');
        $wp_query = $this->get('wp_query', 'post');

        if ($ajax || $wp_query) {
            if (!$ajax) {
                $this->set('ajax', []);
            }
            $this->set('ajax.data.wp_query', $this->get('wp_query', 'post'));
        }
        parent::parseParams();
    }

    /**
     * @inheritDoc
     */
    public function xhrResponse(...$args): ResponseInterface
    {
        switch ($this->httpRequest()->input('wp_query')) {
            case 'post' :
            default :
                $response = $this->xhrResponsePostQuery(...$args);
                break;
            case 'term' :
                $response = $this->xhrResponseTermQuery(...$args);
                break;
            case 'user' :
                $response = $this->xhrResponseUserQuery(...$args);
                break;
            case 'custom':
                $response = parent::xhrResponse(...$args);
                break;
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function xhrResponsePostQuery(...$args): ResponseInterface
    {
        $term = $this->httpRequest()->input('_term', '');
        $paged = $this->httpRequest()->input('_paged', 1);
        $per_page = get_option('posts_per_page');

        $wpQuery = new WP_Query();

        $query_args = array_merge(
            [
                'post_type'      => 'any',
                'posts_per_page' => $per_page,
            ],
            $this->httpRequest()->input('query_args', []),
            [
                's'     => $term,
                'paged' => $paged,
            ]
        );

        if ($posts = $wpQuery->query($query_args)) {
            $count = count($posts);
            $found = $wpQuery->found_posts;

            if ($this->view()->getOverrideDir() === null) {
                $this->view()->setOverrideDir(dirname(__DIR__) . '/Resources/views/suggest');
            }

            $items = (new Collection($posts))->map(
                function (WP_Post $wp_post) {
                    $post = WpPostQuery::create($wp_post);

                    return [
                        'alt'   => (string)$post->getId(),
                        'label' => $this->view('post-picker_item', compact('post')),
                        'value' => (string)$post->getTitle(true),
                    ];
                }
            )->all();

            $more = (($per_page > 0) && ($count >= $per_page) && ($found > ($paged * $count))) ? [
                'data'   => [
                    '_paged' => ++$paged,
                ],
                'count'  => $count,
                'found'  => $found,
                'loader' => true,
                'html'   => '+',
            ] : null;

            return new JsonResponse(
                [
                    'success' => true,
                    'data'    => compact('items', 'more'),
                ]
            );
        }

        return new JsonResponse(['success' => false]);
    }

    /**
     * @inheritDoc
     */
    public function xhrResponseTermQuery(...$args): ResponseInterface
    {
        $term = $this->httpRequest()->input('_term', '');
        $paged = $this->httpRequest()->input('_paged', 1);
        $per_page = 20;

        $wpTermQuery = new WP_Term_Query();

        $query_args = array_merge(
            [
                'number' => $per_page,
            ],
            $this->httpRequest()->input('query_args', []),
            [
                'search' => '*' . $term . '*',
                'offset' => ($paged - 1) * $per_page,
            ]
        );

        if ($terms = $wpTermQuery->query($query_args)) {
            $count = count($terms);
            $found = new WP_Term_Query(
                array_merge(
                    $query_args,
                    [
                        'count'  => false,
                        'number' => 0,
                        'offset' => 0,
                        'fields' => 'count',
                    ]
                )
            );

            $items = (new Collection($terms))->map(
                function (WP_Term $wp_term) {
                    $term = WpTermQuery::create($wp_term);

                    return [
                        'alt'   => (string)$term->getId(),
                        'label' => (string)$term->getName(),
                        'value' => (string)$term->getName(),
                    ];
                }
            )->all();

            $more = (($per_page > 0) && ($count >= $per_page) && ($found > ($paged * $count))) ? [
                'data'   => [
                    '_paged' => ++$paged,
                ],
                'count'  => $count,
                'found'  => $found,
                'loader' => true,
                'html'   => '+',
            ] : null;

            return new JsonResponse(
                [
                    'success' => true,
                    'data'    => compact('items', 'more'),
                ]
            );
        }
        return new JsonResponse(['success' => false]);
    }

    /**
     * @inheritDoc
     */
    public function xhrResponseUserQuery(...$args): ResponseInterface
    {
        $term = $this->httpRequest()->input('_term', '');
        $paged = $this->httpRequest()->input('_paged', 1);
        $per_page = 20;

        $query_args = array_merge(
            [
                'number' => $per_page,
            ],
            $this->httpRequest()->input('query_args', []),
            [
                'search' => '*' . $term . '*',
                'offset' => ($paged - 1) * $per_page,
            ]
        );

        $wpUserQuery = new WP_User_Query($query_args);

        if ($users = $wpUserQuery->get_results()) {
            $count = count($users);
            $found = $wpUserQuery->get_total();

            $items = (new Collection($users))->map(
                function (WP_User $wp_user) {
                    $user = WpUserQuery::create($wp_user);

                    return [
                        'alt'   => (string)$user->getId(),
                        'label' => (string)$user->getDisplayName(),
                        'value' => (string)$user->getDisplayName(),
                    ];
                }
            )->all();

            $more = (($per_page > 0) && ($count >= $per_page) && ($found > ($paged * $count))) ? [
                'data'   => [
                    '_paged' => ++$paged,
                ],
                'count'  => $count,
                'found'  => $found,
                'loader' => true,
                'html'   => '+',
            ] : null;

            return new JsonResponse(
                [
                    'success' => true,
                    'data'    => compact('items', 'more'),
                ]
            );
        }
        return new JsonResponse(['success' => false]);
    }
}