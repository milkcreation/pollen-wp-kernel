<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column;

interface ColumnDisplayInterface
{
    /**
     * Initialisation du controleur.
     *
     * @return void
     */
    public function boot();

    /**
     * Affichage du contenu.
     *
     * @param mixed $var1.
     * @param mixed $var2.
     * @param mixed $var3.
     *
     * @return string
     */
    public function content($var1, $var2, $var3 = null);

    /**
     * Affichage de l'entête.
     *
     * @return string
     */
    public function header();

    /**
     * Chargement de la page d'administration courante de Wordpress.
     *
     * @param \WP_Screen $wp_screen Instance du controleur d'écran de la page d'administration courante de Wordpress.
     *
     * @return void
     */
    public function load($wp_screen);

    /**
     * Récupération d'un instance du controleur de liste des gabarits d'affichage ou d'un gabarit d'affichage.
     * {@internal Si aucun argument n'est passé à la méthode, retourne l'instance du controleur de liste.}
     * {@internal Sinon récupére l'instance du gabarit d'affichage et passe les variables en argument.}
     *
     * @param null|string view Nom de qualification du gabarit.
     * @param array $data Liste des variables passées en argument.
     *
     * @return PlatesEngine|string
     */
    public function viewer($view = null, $data = []);
}