<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column;

interface ColumnDisplayTaxonomyInterface extends ColumnDisplayInterface
{
    /**
     * Affichage du contenu de la colonne.
     *
     * @param string $content Contenu de la colonne.
     * @param string $column_name Identification de la colonne.
     * @param int $term_id Identifiant de qualification du terme.
     *
     * @return void
     */
    public function content($content = null, $column_name = null, $term_id = null);
}