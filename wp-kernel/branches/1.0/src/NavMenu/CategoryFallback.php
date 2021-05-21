<?php

declare(strict_types=1);

namespace Pollen\WpKernel\NavMenu;

use Pollen\Support\Html;

class CategoryFallback
{
    /**
     * Menu de navigation par catégorie
     * {@internal A utiliser comme menu de remplacement (fallback_cb) pour un emplacement de menu non affecté}
     * @see wp_list_categories
     */
    public function __invoke(array $args = []): string
    {
        $args = array_merge($defaults = [
            'child_of'            => 0,
            'current_category'    => 0,
            'depth'               => 0,
            'echo'                => true,
            'exclude'             => null,
            'exclude_tree'        => null,
            'feed'                => null,
            'feed_image'          => null,
            'feed_type'           => null,
            'hide_empty'          => 1,
            'hide_title_if_empty' => false,
            'hierarchical'        => true,
            'order'               => 'ASC',
            'order_by'            => 'ID',
            'separator'           => '',
            'show_count'          => 0,
            'show_option_all'     => '',
            'show_option_none'    => '',
            'taxonomy'            => 'category',
            'title_li'            => '',
            'use_desc_for_title'  => 0,
            'menu_id'             => '',
            'menu_class'          => 'menu',
            'container'           => 'div',
            'link_before'         => '',
            'link_after'          => '',
            'before'              => '<ul>',
            'after'               => '</ul>',
            'item_spacing'        => 'discard',
            'walker'              => '',
        ], $args);

        if (!in_array($args['item_spacing'], ['preserve', 'discard'])) {
            $args['item_spacing'] = $defaults['item_spacing'];
        }

        if ('preserve' === $args['item_spacing']) {
            $n = "\n";
        } else {
            $n = '';
        }
        $menu = '';

        $list_args = array_diff_key($args, array_flip([
            'menu_id',
            'menu_class',
            'container',
            'link_before',
            'link_after',
            'before',
            'after',
            'item_spacing',
            'walker'
        ]));
        $list_args['echo'] = false;
        $list_args['title_li'] = '';
        $menu .= wp_list_categories($list_args);

        $container = sanitize_text_field($args['container']);

        if (empty($container)) {
            $container = 'div';
        }

        if ($menu) {
            if (
                isset($args['fallback_cb']) &&
                ($this === $args['fallback_cb']) &&
                ('ul' !== $container)
            ) {
                $args['before'] = "<ul";
                if (!empty($args['menu_id'])) {
                    $args['before'] .= ' id="' . Html::e($args['menu_id']) . '"';
                }

                if (!empty($args['menu_class'])) {
                    $args['before'] .= ' class="' . Html::e($args['menu_class']) . '"';
                }

                $args['before'] .= ">$n";
                $args['after'] = '</ul>';
            }

            $menu = $args['before'] . $menu . $args['after'];
        }

        $attrs = '';
        if (!empty($args['container_id'])) {
            $attrs .= ' id="' . Html::e($args['container_id']) . '"';
        }

        if (!empty($args['container_class'])) {
            $attrs .= ' class="' . Html::e($args['container_class']) . '"';
        }

        $menu = "<$container$attrs>" . $menu . "</$container>$n";

        if ($args['echo']) {
            echo $menu;
        }

        return $menu;
    }
}