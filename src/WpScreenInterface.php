<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use WP_Screen;

interface WpScreenInterface
{
    /**
     * Récupération de l'instance WP_Screen associée.
     *
     * @param WP_Screen|string $screen
     *
     * @return WpScreenInterface|null
     */
    public static function get($screen = ''): ?WpScreenInterface;

    /**
     * Récupération de l'alias de qualification.
     *
     * @return string
     */
    public function getAlias(): string;

    /**
     * Récupération de l'identifiant de qualification de l'accroche de l'écran Wordpress.
     *
     * @return string
     */
    public function getHookname(): string;

    /**
     * Récupération du nom de qualification de l'objet Wordpress en relation.
     *
     * @return string
     */
    public function getObjectName(): string;

    /**
     * Récupération du type d'objet Wordpress en relation.
     *
     * @return string
     */
    public function getObjectType(): string;

    /**
     * Récupération de l'instance WP_Screen associée.
     *
     * @return WP_Screen|null
     */
    public function getScreen(): ?WP_Screen;

    /**
     * Vérification de correspondance avec l'écran d'affichage courant.
     *
     * @return boolean
     */
    public function isCurrent(): bool;

    /**
     * Traitement des attributs de configuration.
     *
     * @return WpScreenInterface
     */
    public function parse(): WpScreenInterface;
}