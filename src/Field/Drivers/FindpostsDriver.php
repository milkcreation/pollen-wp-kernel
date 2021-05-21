<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Field\Drivers;

use Pollen\Http\JsonResponse;
use Pollen\Http\ResponseInterface;
use Pollen\Support\Arr;
use Pollen\Support\Proxy\AssetProxy;
use Pollen\WpKernel\Field\WordpressFieldDriver;
use WP_Post;
use WP_Query;

class FindpostsDriver extends WordpressFieldDriver implements FindpostsDriverInterface
{
    use AssetProxy;

    /**
     * @inheritDoc
     */
    public function defaultParams(): array
    {
        return array_merge(
            parent::defaultParams(),
            [
                /**
                 * @var array $query_args
                 */
                'query_args' => [],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $uniqid = md5(uniqid('', true) . $this->getIndex());

        if (!$post_types = $this->pull('query_args.post_type')) {
            $post_types = get_post_types(['public' => true]);
            $post_types = array_values($post_types);
        }

        $available_post_types = [];
        foreach ($post_types as $pt) {
            if ($obj = get_post_type_object($pt)) {
                $available_post_types[$pt] = $obj->label;
            }
        }

        if (count($available_post_types) > 1) {
            $available_post_types = array_merge(['any' => 'Tous'], $available_post_types);
        }

        $this->set(
            [
                'uniqid'               => $uniqid,
                'attrs.data-control'   => 'findposts',
                'attrs.data-options'   => [
                    'ajax'       => [
                        'url'      => $this->getXhrUrl(),
                        'dataType' => 'json',
                        'method'   => 'POST',
                    ],
                    'uniqid'     => $uniqid,
                    'post_types' => join(',', $post_types),
                ],
                'attrs.id'             => "FieldFindposts--{$uniqid}",
                'available_post_types' => $available_post_types,
                'modal.attrs'          => [
                    'id'           => "FieldFindposts-modal--{$uniqid}",
                    'class'        => "find-box FieldFindposts-modal",
                    'data-control' => 'findposts.modal',
                    'style'        => 'display: none;',
                ],
                'tmpl.attrs'           => [
                    'id'           => "FieldFindposts-response--{$uniqid}",
                    'data-control' => 'findposts.tmpl',
                ],
                'post_types'           => $post_types,
            ]
        );

        $this->asset()->addGlobalJsVar(
            $this->getAlias() . 'l10n',
            [
                'error' => 'Une erreur s\'est produite. Veuillez recharger la page et essayer à nouveau.',
            ],
            true
        );

        return parent::render();
    }

    /**
     * @inheritDoc
     */
    public function xhrResponse(...$args): ResponseInterface
    {
        if (!wp_verify_nonce($this->httpRequest()->request->get('_ajax_nonce'), 'Findposts')) {
            return new JsonResponse(
                [
                    'success' => false,
                    'data'    => __('Invalid nonce'),
                ]
            );
        }

        $query_args = wp_parse_args(
            [
                'post_type'      => explode(',', $this->httpRequest()->request->get('post_type', 'any')),
                'post_status'    => 'any',
                'posts_per_page' => 50,
            ],
            $this->httpRequest()->input('query_args', [])
        );

        $s = Arr::stripslashes($this->httpRequest()->input('ps', ''));
        if ('' !== $s) {
            $query_args['s'] = $s;
        }

        /** @var WP_Post[]|array $post */
        $results = (new WP_Query())->query($query_args);

        if (empty($results)) {
            return new JsonResponse(
                [
                    'success' => false,
                    'data'    => __('No items found.'),
                ]
            );
        }
        $posts = [];

        foreach ($results as $i => $r) {
            $posts[] = [
                'ID'          => $r->ID,
                'post_title'  => trim($r->post_title) ?: __('(no title)'),
                'post_type'   => ($type = get_post_type_object($r->post_type))
                    ? $type->labels->singular_name : '--',
                'post_status' => ($st = get_post_status_object($r->post_status)) ? $st->label : '--',
                'post_date'   => ('0000-00-00 00:00:00' !== $r->post_date)
                    ? mysql2date(__('Y/m/d'), $r->post_date) : '--',
                'alt'         => ($i % 2 !== 0) ? 'alternate' : '',
                'value'       => get_permalink($r->ID),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data'    => $posts,
        ]);
    }
}