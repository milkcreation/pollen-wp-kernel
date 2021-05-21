<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column\Drivers;

use Pollen\WpKernel\Column\AbstractColumnDisplayTaxonomyController;

class TaxonomyIconColumn extends AbstractColumnDisplayTaxonomyController
{
    /**
     * {@inheritdoc}
     */
    public function header()
    {
        return $this->item->getTitle() ? : 'Icone';
    }

    /**
     * {@inheritdoc}
     */
    public function content($content = null, $column_name = null, $term_id = null)
    {
        if (($icon = get_term_meta($term_id, $this->item->get('attrs.name', '_icon'), true)) && file_exists($this->item->get('attrs.dir') . "/{$icon}") && ($data = file_get_contents($this->item->get('attrs.dir') . "/{$icon}"))) :
            echo "<img src=\"data:image/svg+xml;base64," . base64_encode($data) . "\" width=\"80\" height=\"80\" />";
        endif;
    }
}