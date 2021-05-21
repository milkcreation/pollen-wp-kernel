<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

interface WpQueryInterface
{
    /**
     * Vérifie si la page d'affichage courante correspond au contexte indiqué.
     *
     * @param string $ctag Identifiant de qualification du contexte. ex. 404|archive|singular...
     *
     * @return boolean
     */
    public function is(string $ctag): bool;

    /**
     * Récupération de l'alias de contexte de la page d'affichage courante.
     *
     * @return string|null
     */
    public function ctag(): ?string;
}