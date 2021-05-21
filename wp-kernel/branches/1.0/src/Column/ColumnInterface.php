<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column;

interface ColumnInterface
{
    /**
     * Ajout d'un élément.
     *
     * @param string $screen Ecran d'affichage de l'élément.
     * @param string $name Nom de qualification.
     * @param array $attrs Liste des attributs de configuration de l'élément.
     *
     * @return $this
     */
    public function add($screen, $name, $attrs = []);

    /**
     * Traitement de la liste des entêtes de colonnes.
     *
     * @param array $headers Liste des entêtes de colonnes.
     *
     * @return array
     */
    public function parseColumnHeaders($headers);

    /**
     * Traitement de la liste des contenus de colonnes.
     *
     * @return string
     */
    public function parseColumnContents();

    /**
     * Déclaration d'un jeu de colonnes associé à un écran.
     *
     * @param string $screen Nom de qualification de l'écran d'affichage.
     * @param string[][]|array[][]$columns Liste des boîtes de saisie.
     *
     * @return static
     */
    public function stack(string $screen, array $columns): ColumnInterface;
}