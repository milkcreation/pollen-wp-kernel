<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column\Drivers;

use Pollen\Support\Proxy\PartialProxy;
use Pollen\WpKernel\Column\AbstractColumnDisplayPostTypeController;
use Pollen\WpPost\WpPostQuery;

class PostThumbnailColumn extends AbstractColumnDisplayPostTypeController
{
    use PartialProxy;

    /**
     * {@inheritdoc}
     */
    public function header()
    {
        return $this->item->getTitle() ? : '<span class="dashicons dashicons-format-image"></span>';
    }

    /**
     * Mise en file des scripts de l'interface d'administration.
     *
     * @return void
     */
    public function admin_enqueue_scripts(): void
    {
        $column_name = "column-{$this->item->getName()}";
        asset()->addInlineCss(
            ".wp-list-table th.$column_name,.wp-list-table td.$column_name{width:80px;text-align:center;}" .
            ".wp-list-table td.$column_name img{max-width:80px;max-height:60px;}"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function content($column_name = null, $post_id = null, $null = null)
    {
        $thumb = null;

        if ($qp = WpPostQuery::createFromId($post_id)) {
            $thumb = $qp->getThumbnail([60, 60]);
        }

        if (!$thumb) {
            $thumb = $this->partial('holder', [
                'width'  => 60,
                'height' => 60,
            ])->render();
        }

        return $thumb;
    }

    /**
     * {@inheritdoc}
     */
    public function load($wp_screen): void
    {
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
    }
}