<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column;

class AbstractColumnDisplayTaxonomyController extends AbstractColumnDisplayController implements
    ColumnDisplayTaxonomyInterface
{
    /**
     * {@inheritdoc}
     */
    public function content($content = null, $column_name = null, $term_id = null)
    {
        parent::content($content, $column_name, $term_id);
    }
}