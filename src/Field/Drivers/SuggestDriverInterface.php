<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Field\Drivers;

use Pollen\Field\Drivers\SuggestDriverInterface as BaseSuggestDriverInterface;
use Pollen\Http\ResponseInterface;

interface SuggestDriverInterface extends BaseSuggestDriverInterface
{
    /**
     * Traitement de la réponse Xhr de récupération des posts Wordpress associés.
     *
     * @param array ...$args Liste dynamique de variables passés en argument dans l'url de requête.
     *
     * @return ResponseInterface
     */
    public function xhrResponsePostQuery(...$args): ResponseInterface;

    /**
     * Traitement de la réponse Xhr de récupération des termes de taxonomie Wordpress associés.
     *
     * @param array ...$args Liste dynamique de variables passés en argument dans l'url de requête.
     *
     * @return ResponseInterface
     */
    public function xhrResponseTermQuery(...$args): ResponseInterface;

    /**
     * Traitement de la réponse Xhr de récupération des utilisateurs Wordpress associés.
     *
     * @param array ...$args Liste dynamique de variables passés en argument dans l'url de requête.
     *
     * @return ResponseInterface
     */
    public function xhrResponseUserQuery(...$args): ResponseInterface;
}