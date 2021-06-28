<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Option;

use Pollen\View\ViewInterface;

interface OptionInterface
{
    /**
     * Récupération d'une page de réglage des options.
     *
     * @param string $name Nom de qualification de la page
     *
     * @return OptionPageInterface|null
     */
    public function getPage(string $name): ?OptionPageInterface;

    /**
     * Déclaration d'une page de réglage des options.
     *
     * @param string $name Nom de qualification de la page
     * @param OptionPageInterface|array $attrs Instance de la page|Liste des attributs de configuration.
     *
     * @return OptionPageInterface|null
     */
    public function registerPage(string $name, $attrs = []): ?OptionPageInterface;
}