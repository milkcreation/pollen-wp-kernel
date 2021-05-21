<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Partial\Drivers;

use Pollen\Partial\Drivers\CurtainMenuDriver as BaseCurtainMenuDriver;
use Pollen\Partial\Drivers\CurtainMenuDriverInterface as BaseCurtainMenuDriverInterface;
use Pollen\Partial\Drivers\CurtainMenu\CurtainMenuCollection;
use Pollen\Partial\Drivers\CurtainMenu\CurtainMenuCollectionInterface;
use WP_Query;
use WP_Term;
use WP_Term_Query;

class CurtainMenuDriver extends BaseCurtainMenuDriver
{
    /**
     * @inheritDoc
     */
    public function parseItems(): BaseCurtainMenuDriverInterface
    {
        $items = $this->get('items', []);

        if ($items instanceof WP_Query) {
            // @todo
        } elseif ($items instanceof WP_Term_Query) {
            if (!empty($items->query_vars['child_of'])) {
                $parent = (string)$items->query_vars['child_of'];
            } else {
                $parent = null;
            }
            $terms = $items->terms;

            $_items = [];
            array_walk(
                $terms,
                function (WP_Term $t) use (&$_items, $parent) {
                    $_parent = (string)$t->parent;
                    $url = get_term_link($t);

                    $_items[(string)$t->term_id] = [
                        'nav'    => $t->name,
                        'parent' => !empty($_parent) && ($_parent !== $parent) ? (string)$t->parent : null,
                        'title'  => $this->partial(
                            'tag',
                            [
                                'attrs'   => [
                                    'href' => $url,
                                ],
                                'content' => $t->name,
                                'tag'     => 'a',
                            ]
                        )->render(),
                        'url'    => $url,
                    ];
                }
            );
            $items = new CurtainMenuCollection($_items, $this->get('selected'));
        } elseif (!$items instanceof CurtainMenuCollectionInterface) {
            $items = new CurtainMenuCollection($items, $this->get('selected'));
        }
        $this->set('items', $items->prepare($this));

        return $this;
    }
}