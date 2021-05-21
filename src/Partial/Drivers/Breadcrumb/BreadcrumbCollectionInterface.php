<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Partial\Drivers\Breadcrumb;

use Pollen\Partial\Drivers\Breadcrumb\BreadcrumbCollectionInterface as BaseBreadcrumbCollectionInterface;
use WP_Post;

interface BreadcrumbCollectionInterface extends BaseBreadcrumbCollectionInterface
{
    /**
     * Ajout d'un élément de page 404.
     *
     * @param string|null $c Définition du contenu.
     * @param string|bool $u Définition de l'url.
     * @param array $a Liste des attributs HTML de l'élément.
     * @param int|null $p Position de l'élément.
     * @param array $w Liste des attributs de configuration de l'encapsuleur.
     *
     * @return int
     */
    public function add404(?string $c = null, $u = false, array $a = [], ?int $p = null, array $w = []): int;

    /**
     * Ajout d'un élément de page liste des articles du blog.
     *
     * @param string|null $c Définition du contenu.
     * @param string|bool $u Définition de l'url.
     * @param array $a Liste des attributs HTML de l'élément.
     * @param int|null $p Position de l'élément.
     * @param array $w Liste des attributs de configuration de l'encapsuleur.
     *
     * @return int
     */
    public function addHome(?string $c = null, $u = false, array $a = [], ?int $p = null, array $w = []): int;

    /**
     * Ajout d'un élément de page de résultats de recherche.
     *
     * @param string|null $c Définition du contenu.
     * @param string|bool $u Définition de l'url.
     * @param array $a Liste des attributs HTML de l'élément.
     * @param int|null $p Position de l'élément.
     * @param array $w Liste des attributs de configuration de l'encapsuleur.
     *
     * @return int
     */
    public function addSearch(?string $c = null, $u = false, array $a = [], ?int $p = null, array $w = []): int;

    /**
     * Ajout d'un élément de la racine du site.
     *
     * @param string|null $c Définition du contenu.
     * @param string|bool $u Définition de l'url.
     * @param array $a Liste des attributs HTML de l'élément.
     * @param int|null $p Position de l'élément.
     * @param array $w Liste des attributs de configuration de l'encapsuleur.
     *
     * @return int
     */
    public function addRoot(?string $c = null, $u = false, array $a = [], ?int $p = null, array $w = []): int;

    /**
     * Ajout d'un élément de page liste des contenus associés à une taxonomie.
     *
     * @param string|null $c Définition du contenu.
     * @param string|bool $u Définition de l'url.
     * @param array $a Liste des attributs HTML de l'élément.
     * @param int|null $p Position de l'élément.
     * @param array $w Liste des attributs de configuration de l'encapsuleur.
     *
     * @return int
     */
    public function addTax(?string $c = null, $u = false, array $a = [], ?int $p = null, array $w = []): int;

    /**
     * Récupération des ancêtres selon le contexte
     *
     * @param int $id Identifiant de qualification du post enfant.
     * @param bool $url Activation de l'url.
     * @param array $attrs Liste des attributs HTML de l'élément.
     *
     * @return string[]|array
     */
    public function getPostAncestorsRender(int $id, bool $url = true, array $attrs = []): array;

    /**
     * Ajout d'un élément associé à un post.
     *
     * @param int $id Identifiant de qualification du post enfant.
     * @param bool $url Activation de l'url.
     * @param array $attrs Liste des attributs HTML de l'élément.
     *
     * @return string
     */
    public function getPostRender(int $id, bool $url = true, array $attrs = []): string;

    /**
     * Intitulé de d'un élément relatif à un post
     *
     * @param int|WP_Post $post
     *
     * @return string
     */
    public function getPostTitle($post): string;

    /**
     * Ajout d'un élément associé à un terme de taxonomie.
     *
     * @param int $id Identifiant de qualification du post enfant.
     * @param bool $url Activation de l'url.
     * @param array $attrs Liste des attributs HTML de l'élément.
     *
     * @return string
     */
    public function getTermRender(int $id, bool $url = true, array $attrs = []): string;
}