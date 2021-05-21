<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column\Drivers;

use Pollen\WpKernel\Column\AbstractColumnDisplayTaxonomyController;

class TaxonomyColorColumn extends AbstractColumnDisplayTaxonomyController
{
    /**
     * {@inheritdoc}
     */
    public function header()
    {
        return $this->item->getTitle() ? : 'Couleur';
    }

    /**
     * {@inheritdoc}
     */
    public function content($content = null, $column_name = null, $term_id = null)
    {
        if ($color = get_term_meta($term_id, '_color', true)) :
            echo "<div style=\"width:80px;height:80px;display:block;border:solid 1px #CCC;background-color:#F4F4F4;position:relative;\"><div style=\"position:absolute;top:5px;right:5px;bottom:5px;left:5px;background-color:{$color}\"></div></div>";
        endif;
    }
}