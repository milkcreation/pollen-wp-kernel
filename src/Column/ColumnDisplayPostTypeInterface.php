<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column;

interface ColumnDisplayPostTypeInterface extends ColumnDisplayInterface
{
    /**
     * Affichage du contenu de la colonne
     *
     * @param string $column_name Identifiant de qualification de la colonne.
     * @param int $post_id Identifiant du post.
     * @param null $null Paramètre indisponible.
     *
     * @return string
     */
    public function content($column_name = null, $post_id = null, $null = null);
}