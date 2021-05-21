<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Metabox;

use WP_Screen;

/**
 * @mixin WP_Screen
 */
interface MetaboxWpAdminScreenInterface
{
    /**
     * Récupération de paramètres de WP_Screen.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get(string $key);

    /**
     * Délégation d'appel des méthodes de WP_Screen.
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments);

    /**
     * Récupération de l'instance WP_Screen associée.
     *
     * @param WP_Screen|string $screen
     *
     * @return static|null
     */
    public static function get($screen = ''): ?MetaboxWpAdminScreenInterface;

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
     * @return WP_Screen
     */
    public function getWpScreen(): WP_Screen;

    /**
     * Vérification de correspondance avec l'écran d'affichage courant.
     *
     * @return bool
     */
    public function isCurrent(): bool;

    /**
     * Traitement des attributs de configuration.
     *
     * @return static
     */
    public function parse(): MetaboxWpAdminScreenInterface;
}