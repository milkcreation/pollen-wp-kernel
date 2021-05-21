<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column;

class AbstractColumnDisplayPostTypeController extends AbstractColumnDisplayController implements
    ColumnDisplayPostTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function content($column_name = null, $post_id = null, $null = null)
    {
        parent::content($column_name, $post_id, $null);
    }
}