<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column\Drivers;

use Pollen\WpKernel\Column\AbstractColumnDisplayPostTypeController;

class PostMenuOrderColumn extends AbstractColumnDisplayPostTypeController
{
    /**
     * {@inheritdoc}
     */
    public function header()
    {
        return $this->item->getTitle() ? : 'Ordre d\'affich.';
    }

    /**
     * Mise en file des scripts de l'interface d'administration.
     *
     * @return void
     */
    public function admin_enqueue_scripts(): void
    {
        $column_name = "column-{$this->item->getName()}";
        asset()->addInlineCss(".wp-list-table th.{$column_name},.wp-list-table td.{$column_name}{width:120px;}");
    }

    /**
     * {@inheritdoc}
     */
    public function content($column_name = null, $post_id = null, $null = null)
    {
        $level = 0;
        $post = get_post($post_id);

        if (0 == $level && (int)$post->post_parent > 0) :
            $find_main_page = (int)$post->post_parent;
            while ($find_main_page > 0) :
                $parent = get_post($find_main_page);

                if (is_null($parent)) :
                    break;
                endif;

                $level++;
                $find_main_page = (int)$parent->post_parent;
            endwhile;
        endif;
        $_level = "";

        for ($i = 0; $i < $level; $i++) :
            $_level .= "<strong>&mdash;</strong> ";
        endfor;

        return $_level . $post->menu_order;
    }

    /**
     * {@inheritdoc}
     */
    public function load($wp_screen)
    {
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
    }
}