<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column\Drivers;

use Pollen\Support\Proxy\PartialProxy;
use Pollen\WpKernel\Column\AbstractColumnDisplayTaxonomyController;
use Pollen\WpTerm\WpTermQuery;

class TaxonomyThumbnailColumn extends AbstractColumnDisplayTaxonomyController
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
     * {@inheritdoc}
     */
    public function content($content = null, $column_name = null, $term_id = null)
    {
        $thumb = null;
        if ($qp = WpTermQuery::createFromId((int)$term_id)) {
            $thumb = $qp->getMetaSingle($this->item->get('attrs.name', '_thumbnail'));
        }

        if (!$thumb) {
            $thumb = $this->partial('holder', [
                'width'  => $this->item->get('attrs.width', 80),
                'height' => $this->item->get('attrs.height', 80),
            ])->render();
        } else {
            $thumb = wp_get_attachment_image($thumb, [
                $this->item->get('attrs.width', 80), $this->item->get('attrs.height', 80)
            ]);
        }

        return $thumb;
    }

    /**
     * {@inheritdoc}
     */
    public function load($wp_screen)
    {
        add_action('admin_enqueue_scripts', function () {
            $w = $this->item->get('attrs.width', 80);
            $h = $this->item->get('attrs.height', 80);
            $col = "column-{$this->item->getName()}";

            asset()->addInlineCss(
                ".wp-list-table th.{$col},.wp-list-table td.{$col}{width:{$w}px;text-align:center;}" .
                ".wp-list-table td.{$col} img{max-width:{$w}px;max-height:{$h}px;}"
            );
        });
    }
}