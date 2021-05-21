<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column\Drivers;

use Pollen\WpKernel\Column\AbstractColumnDisplayPostTypeController;

class PostSubtitleColumn extends AbstractColumnDisplayPostTypeController
{
    /**
     * {@inheritdoc}
     */
    public function header()
    {
        return $this->item->getTitle() ? : 'Sous-titre';
    }

    /**
     * {@inheritdoc}
     */
    public function content($column_name = null, $post_id = null, $null = null)
    {
        if ($subtitle = get_post_meta($post_id, '_subtitle', true)) :
            return $subtitle;
        else :
            return "<em style=\"color:#AAA;\">" . 'Aucun' . "</em>";
        endif;
    }
}