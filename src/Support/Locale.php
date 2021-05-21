<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Support;

class Locale
{
    /**
     * Identifiant de qualification de la locale.
     * @var string|null
     */
    protected static $locale;

    /**
     * Paramètres de languages disponibles.
     * @var array[]
     */
    protected static $languages = [];

    /**
     * Définition de l'identifiant de qualification de locale.
     *
     * @return string|null
     */
    public static function get(): ?string
    {
        return self::$locale;
    }

    /**
     * Définition de l'identifiant de qualification de locale.
     *
     * @param string $locale
     *
     * @return void
     */
    public static function set(string $locale): void
    {
        self::$locale = $locale;
    }

    /**
     * Récupération des paramètres d'un langage disponible.
     *
     * @param string|null $locale
     *
     * @return array|null
     */
    public static function getLanguage(string $locale = null): ?array
    {
        return self::$languages[$locale??self::$locale] ?? null;
    }

    /**
     * Définition des paramètres des langages disponibles.
     *
     * @param array $languages Liste des paramètres de configuration des langages disponibles.
     *
     * @return void
     */
    public static function setLanguages(array $languages): void
    {
        self::$languages = $languages;
    }
}