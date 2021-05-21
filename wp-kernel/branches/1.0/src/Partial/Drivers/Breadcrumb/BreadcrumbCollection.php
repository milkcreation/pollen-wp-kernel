<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Partial\Drivers\Breadcrumb;

use Pollen\Event\TriggeredEventInterface;
use Pollen\Partial\Drivers\BreadcrumbDriverInterface;
use Pollen\Partial\Drivers\Breadcrumb\BreadcrumbCollection as BaseBreadcrumbCollection;
use WP_Term;

class BreadcrumbCollection extends BaseBreadcrumbCollection implements BreadcrumbCollectionInterface
{
    /**
     * @param BreadcrumbDriverInterface $manager Instance du pilote de fil d'ariane.
     */
    public function __construct(BreadcrumbDriverInterface $manager)
    {
        parent::__construct($manager);

        events()->on(
            'partial.breadcrumb.prefetch',
            function (TriggeredEventInterface $event, BreadcrumbCollectionInterface $bc) {
                if (!$this->all()) {
                    $this->addRoot(null, true);

                    if (is_embed()) {
                        /** @todo */
                    } elseif (is_404()) {
                        $this->add404();
                    } elseif (is_search()) {
                        $this->addSearch();
                    } elseif (is_front_page()) {
                    } elseif (is_home()) {
                        if ($id = (int)get_option('page_for_posts')) {
                            if ($acs = $this->getPostAncestorsRender($id)) {
                                array_walk(
                                    $acs,
                                    function ($render) {
                                        $this->add($render);
                                    }
                                );
                            }
                            $this->addHome();
                        }
                    } elseif (is_privacy_policy()) {
                        /** @todo */
                    } elseif (is_post_type_archive()) {
                        /** @todo */
                    } elseif (is_tax()) {
                        if ($acsr = $this->getTermAncestorsRender(get_queried_object_id())) {
                            array_walk(
                                $acsr,
                                function ($render) {
                                    $this->add($render);
                                }
                            );
                        }

                        $this->addTax();
                    } elseif (is_attachment()) {
                        /** @todo */
                    } elseif (is_single()) {
                        if (get_post_type() === 'post') {
                            if (($id = (int)get_option('page_for_posts')) && ($pr = $this->getPostRender($id))) {
                                $this->add($pr);
                            }
                        } elseif ($acs = $this->getPostAncestorsRender(get_the_ID())) {
                            array_walk(
                                $acs,
                                function ($render) {
                                    $this->add($render);
                                }
                            );
                        }

                        if ($pr = $this->getPostRender(get_the_ID(), false)) {
                            $this->add($pr);
                        }
                    } elseif (is_page()) {
                        if ($acsr = $this->getPostAncestorsRender(get_the_ID())) {
                            array_walk(
                                $acsr,
                                function ($render) {
                                    $this->add($render);
                                }
                            );
                        }

                        if ($pr = $this->getPostRender(get_the_ID(), false)) {
                            $this->add($pr);
                        }
                    } elseif (is_singular()) {
                        if ($acsr = $this->getPostAncestorsRender(get_the_ID())) {
                            array_walk(
                                $acsr,
                                function ($render) {
                                    $this->add($render);
                                }
                            );
                        }

                        if ($pr = $this->getPostRender(get_the_ID(), false)) {
                            $this->add($pr);
                        }
                    } elseif (is_category()) {
                        $cat_id = get_queried_object_id();

                        if ($acsr = $this->getTermAncestorsRender($cat_id)) {
                            array_walk(
                                $acsr,
                                function ($render) {
                                    $this->add($render);
                                }
                            );
                        }

                        $this->addTax();
                    } elseif (is_tag()) {
                        $this->addTax();
                    } elseif (is_author()) {
                        /** @todo */
                    } elseif (is_date()) {
                        if (is_day()) {
                            $this->add(
                                sprintf(
                                    'Archives du jour : %s',
                                    $this->getRender(get_the_date())
                                )
                            );
                        } elseif (is_month()) {
                            $this->add(
                                sprintf(
                                    'Archives du mois : %s',
                                    $this->getRender(get_the_date('F Y'))
                                )
                            );
                        } elseif (is_year()) {
                            $this->add(
                                sprintf(
                                    'Archives de l\'année : %s',
                                    $this->getRender(get_the_date('Y'))
                                )
                            );
                        }
                    } elseif (is_archive()) {
                        /** @todo */
                    }
                }
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function add404(?string $c = null, $u = false, array $a = [], ?int $p = null, array $w = []): int
    {
        $c = $c ?: 'Erreur 404 - Page introuvable';
        $u = $this->getUrl($u, (string)Url::current());

        return $this->add($this->getRender($c, $u, $a), $p, $w);
    }

    /**
     * @inheritDoc
     */
    public function addHome(?string $c = null, $u = false, array $a = [], ?int $p = null, array $w = []): int
    {
        $c = $c ?: 'Actualités';
        $u = $this->getUrl($u, get_post_type_archive_link('post'));

        return $this->add($this->getRender($c, $u, $a), $p, $w);
    }

    /**
     * @inheritDoc
     */
    public function addSearch(?string $c = null, $u = false, array $a = [], ?int $p = null, array $w = []): int
    {
        $c = $c ?: sprintf(__('Résultats de recherche pour : "%s"', 'tify'), get_search_query());
        $u = $this->getUrl($u, (string)Url::current());

        return $this->add($this->getRender($c, $u, $a), $p, $w);
    }

    /**
     * @inheritDoc
     */
    public function addRoot(?string $c = null, $u = false, array $a = [], ?int $p = null, array $w = []): int
    {
        $c = $c ?: __('Accueil', 'tify');
        $u = $this->getUrl($u, (string)Url::root());
        $a = $u ? array_merge(
            [
                'title' => ($id = get_option('page_on_front'))
                    ? sprintf(__('Revenir à %s', 'tify'), $this->getPostTitle($id))
                    : sprintf(__('Revenir à l\'accueil du site %s', 'tify'), get_bloginfo('name')),
            ],
            $a
        ) : $a;

        return $this->add($this->getRender($c, $u, $a), $p, $w);
    }

    /**
     * @inheritDoc
     */
    public function addTax(?string $c = null, $u = false, array $a = [], ?int $p = null, array $w = []): int
    {
        /** @var WP_Term $term */
        $term = get_queried_object();

        $c = sprintf($c ?: __('%s : %s', 'tify'), get_taxonomy($term->taxonomy)->label, $term->name);
        $u = $this->getUrl($u, (string)get_term_link($term));

        return $this->add($this->getRender($c, $u, $a), $p, $w);
    }

    /**
     * @inheritDoc
     */
    public function getPostAncestorsRender(int $id, bool $url = true, array $attrs = []): array
    {
        $parents = get_post_ancestors($id);

        $items = [];
        foreach (array_reverse($parents) as $post) {
            $items[] = $this->getRender($this->getPostTitle($post), $url ? get_permalink($post) : null, $attrs);
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function getPostRender(int $id, bool $url = true, array $attrs = []): string
    {
        return $this->getRender($this->getPostTitle($id), $url ? get_permalink($id) : null, $attrs);
    }

    /**
     * @inheritDoc
     */
    public function getPostTitle($post): string
    {
        return esc_html(wp_strip_all_tags(get_the_title(get_post($post)->ID)));
    }

    /**
     * @inheritDoc
     */
    public function getTermAncestorsRender(int $id, bool $url = true, array $attrs = []): array
    {
        if (
            ($term = get_term($id)) instanceof WP_Term &&
            $parents = get_ancestors($term->term_id, $term->taxonomy, 'taxonomy')
        ) {
            $items = [];
            foreach (array_reverse($parents) as $pid) {
                if (($t = get_term($pid)) instanceof WP_Term) {
                    $items[] = $this->getRender($t->name, $url ? get_term_link($t) : null, $attrs);
                }
            }

            return $items;
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function getTermRender(int $id, bool $url = true, array $attrs = []): string
    {
        return (($t = get_term($id)) instanceof WP_Term) ? $this->getRender(
            $t->name,
            $url ? get_term_link($t) : null,
            $attrs
        ) : '';
    }

    /**
     * Récupération de l'élèment lors de l'affichage d'une page liste de contenus relatifs à une catégorie
     *
     * @return array
     *
     * /**
     * Récupération de l'élèment lors de l'affichage d'une page liste de contenus relatifs à un auteur
     *
     * @return array
     *
     * public function currentAuthor()
     * {
     * $name = get_the_author_meta('display_name', get_query_var('author'));
     *
     * $part = [
     * 'class'   => $this->getItemWrapperClass(),
     * 'content' => partial(
     * 'tag',
     * [
     * 'tag'     => 'span',
     * 'attrs'   => [
     * 'class' => $this->getItemContentClass(),
     * ],
     * 'content' => sprintf('Auteur : %s', $name),
     * ]
     * ),
     * ];
     *
     * return $part;
     * } */

    /**
     * Récupération de l'élèment lors de l'affichage d'une page liste de contenus relatifs à une date
     *
     * @return array
     *
     * public function currentDate()
     * {
     * if (is_day()) :
     * $content = sprintf(__('Archives du jour : %s', 'tify'), get_the_date());
     * elseif (is_month()) :
     * $content = sprintf(__('Archives du mois : %s', 'tify'), get_the_date('F Y'));
     * elseif (is_year()) :
     * $content = sprintf(__('Archives de l\'année : %s', 'tify'), get_the_date('Y'));;
     * endif;
     *
     * $part = [
     * 'class'   => $this->getItemWrapperClass(),
     * 'content' => partial(
     * 'tag',
     * [
     * 'tag'     => 'span',
     * 'attrs'   => [
     * 'class' => $this->getItemContentClass(),
     * ],
     * 'content' => $content,
     * ]
     * ),
     * ];
     *
     * return $part;
     * } */

    /**
     * Récupération de l'élèment lors de l'affichage d'une page liste de contenus
     *
     * @return array
     *
     * public function currentArchive()
     * {
     * $content = (is_post_type_archive())
     * ? post_type_archive_title('', false)
     * : __('Actualités', 'tify');
     *
     * $part = [
     * 'class'   => $this->getItemWrapperClass(),
     * 'content' => partial(
     * 'tag',
     * [
     * 'tag'     => 'span',
     * 'attrs'   => [
     * 'class' => $this->getItemContentClass(),
     * ],
     * 'content' => $content,
     * ]
     * ),
     * ];
     *
     * return $part;
     * } */

    /**
     * @return \Pollen\Partial\Drivers\TagDriver
     * @todo Suppression des redondances current précédentes
     *
     *
     * protected function partCurrent($attrs)
     * {
     *
     * }  */

    /**
     * @return \Pollen\Partial\Drivers\TagDriver
     * @todo Suppression des redondances link précédentes
     *
     *
     * protected function partLink($attrs)
     * {
     *
     * } */

    /**
     * Récupération des ancêtres selon le contexte
     *
     * @return void
     *
     * protected function getAncestorsPartList()
     * {
     * if (is_attachment()) :
     * if ($parents = \get_ancestors(get_the_ID(), get_post_type())) :
     * if (('post' === get_post_type(reset($parents))) && ($page_for_posts = get_option('page_for_posts'))) :
     * $title = $this->getPostTitle($page_for_posts);
     *
     * $this->parts[] = [
     * 'class'   => $this->getItemWrapperClass(),
     * 'content' => partial(
     * 'tag',
     * [
     * 'tag'     => 'a',
     * 'attrs'   => [
     * 'href'  => get_permalink($page_for_posts),
     * 'title' => sprintf(__('Revenir à %s', 'tify'), $title),
     * 'class' => $this->getItemContentClass(),
     * ],
     * 'content' => $title,
     * ]
     * ),
     * ];
     * endif;
     *
     * reset($parents);
     *
     * foreach (array_reverse($parents) as $parent) :
     * $title = $this->getPostTitle($parent);
     *
     * $this->parts[] = [
     * 'class'   => $this->getItemWrapperClass(),
     * 'content' => partial(
     * 'tag',
     * [
     * 'tag'     => 'a',
     * 'attrs'   => [
     * 'href'  => get_permalink($parent),
     * 'title' => sprintf(__('Revenir à %s', 'tify'), $title),
     * 'class' => $this->getItemContentClass(),
     * ],
     * 'content' => $title,
     * ]
     * ),
     * ];
     * endforeach;
     * endif;
     * elseif (is_home() && is_paged()) :
     * if ($page_for_posts = get_option('page_for_posts')) :
     * $title = $this->getPostTitle($page_for_posts);
     *
     * $this->parts[] = [
     * 'class'   => $this->getItemWrapperClass(),
     * 'content' => partial(
     * 'tag',
     * [
     * 'tag'     => 'a',
     * 'attrs'   => [
     * 'href'  => get_permalink($page_for_posts),
     * 'title' => sprintf(__('Revenir à %s', 'tify'), $title),
     * 'class' => $this->getItemContentClass(),
     * ],
     * 'content' => $title,
     * ]
     * ),
     * ];
     * else :
     * $this->parts[] = [
     * 'class'   => $this->getItemWrapperClass(),
     * 'content' => partial(
     * 'tag',
     * [
     * 'tag'     => 'a',
     * 'attrs'   => [
     * 'href'  => home_url('/'),
     * 'title' => __('Revenir à la liste des actualités', 'tify'),
     * 'class' => $this->getItemContentClass(),
     * ],
     * 'content' => __('Actualités', 'tify'),
     * ]
     * ),
     * ];
     * endif;
     * elseif (is_single()) :
     * // Le type du contenu est un article de blog
     * if (is_singular('post')) :
     * if ($page_for_posts = get_option('page_for_posts')) :
     * $title = $this->getPostTitle($page_for_posts);
     *
     * $this->parts[] = [
     * 'class'   => $this->getItemWrapperClass(),
     * 'content' => partial(
     * 'tag',
     * [
     * 'tag'     => 'a',
     * 'attrs'   => [
     * 'href'  => get_permalink($page_for_posts),
     * 'title' => sprintf(__('Revenir à %s', 'tify'), $title),
     * 'class' => $this->getItemContentClass(),
     * ],
     * 'content' => $title,
     * ]
     * ),
     * ];
     * else :
     * $this->parts[] = [
     * 'class'   => $this->getItemWrapperClass(),
     * 'content' => partial(
     * 'tag',
     * [
     * 'tag'     => 'a',
     * 'attrs'   => [
     * 'href'  => home_url('/'),
     * 'title' => __('Revenir à la liste des actualités', 'tify'),
     * 'class' => $this->getItemContentClass(),
     * ],
     * 'content' => __('Actualités', 'tify'),
     * ]
     * ),
     * ];
     * endif;
     *
     * // Le type de contenu autorise les pages d'archives
     * elseif (($post_type_obj = get_post_type_object(get_post_type())) && $post_type_obj->has_archive) :
     * $title = $post_type_obj->labels->name;
     *
     * $this->parts[] = [
     * 'class'   => $this->getItemWrapperClass(),
     * 'content' => partial(
     * 'tag',
     * [
     * 'tag'     => 'a',
     * 'attrs'   => [
     * 'href'  => get_post_type_archive_link(\get_post_type()),
     * 'title' => sprintf(__('Revenir à %s', 'tify'), $title),
     * 'class' => $this->getItemContentClass(),
     * ],
     * 'content' => $title,
     * ]
     * ),
     * ];
     * endif;
     *
     * // Le contenu a des ancêtres
     * if ($parents = get_ancestors(get_the_ID(), get_post_type())) :
     * foreach (array_reverse($parents) as $parent) :
     * $title = $this->getPostTitle($parent);
     *
     * $this->parts[] = [
     * 'class'   => $this->getItemWrapperClass(),
     * 'content' => partial(
     * 'tag',
     * [
     * 'tag'     => 'a',
     * 'attrs'   => [
     * 'href'  => get_permalink($parent),
     * 'title' => sprintf(__('Revenir à %s', 'tify'), $title),
     * 'class' => $this->getItemContentClass(),
     * ],
     * 'content' => $title,
     * ]
     * ),
     * ];
     * endforeach;
     * endif;
     *
     * elseif (is_page()) :
     * if ($parents = get_ancestors(get_the_ID(), get_post_type())) :
     * foreach (array_reverse($parents) as $parent) :
     * $title = $this->getPostTitle($parent);
     *
     * $this->parts[] = [
     * 'class'   => $this->getItemWrapperClass(),
     * 'content' => partial(
     * 'tag',
     * [
     * 'tag'     => 'a',
     * 'attrs'   => [
     * 'href'  => get_permalink($parent),
     * 'title' => sprintf(__('Revenir à %s', 'tify'), $title),
     * 'class' => $this->getItemContentClass(),
     * ],
     * 'content' => $title,
     * ]
     * ),
     * ];
     * endforeach;
     * endif;
     * endif;
     * } */
}