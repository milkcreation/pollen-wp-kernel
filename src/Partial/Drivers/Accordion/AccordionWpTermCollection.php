<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Partial\Drivers\Accordion;

use Illuminate\Support\Collection;
use Pollen\Partial\Drivers\Accordion\AccordionCollection;
use WP_Term;
use WP_Term_Query;

class AccordionWpTermCollection extends AccordionCollection
{
    /**
     * Liste des éléments récupérer.
     * @var int[]|array|null
     */
    private static $fetched;

    /**
     * CONSTRUCTEUR.
     *
     * @param WP_Term[]|AccordionWpTermItemInterface[]|array $terms
     *
     * @return void
     */
    public function __construct(array $terms)
    {
        $items = [];

        array_walk($terms, function ($term, $key) use (&$items) {
            if ($term instanceof WP_Term) {
                $key = $term->term_id;

                $items[$key] = new AccordionWpTermItem($key, $term);
            } elseif ($term instanceof AccordionWpTermItemInterface) {
                $items[$key] = $term;
            }
        });

        parent::__construct($items);
    }

    /**
     * Création d'une instance basée sur une liste d'arguments.
     *
     * @param WP_Term_Query|array $args
     * @param bool $with_parents Activation de la liste des parents associés (recommandé).
     *
     * @return static
     */
    public static function createFromArgs(array $args = [], bool $with_parents = true): self
    {
        return new static(static::fetch($args, $with_parents));
    }

    /**
     * Récupération de la liste des terme de taxonomie.
     * @see https://developer.wordpress.org/reference/classes/wp_term_query/
     *
     * @param WP_Term_Query|array $args
     * @param bool $with_parents Activation de la liste des parents associés (recommandé).
     *
     * @return WP_Term[]|array
     */
    public static function fetch($args, bool $with_parents = true): array
    {
        static::$fetched = null;

        $query = $args instanceof WP_Term_Query ? $args : new WP_Term_Query($args);

        /** @var WP_Term[] $terms */
        if ($terms = $query->get_terms()) {
             if ($with_parents) {
                foreach ($terms as $k => $term) {
                    static::fetchParents($term, $terms, $query->query_vars);
                }

                return $terms;
            } else {
                return $terms;
            }
        } else {
            return [];
        }
    }

    /**
     * Récupération recursive de la liste des termes parents associés à un terme enfant.
     *
     * @param WP_Term $term
     * @param WP_Term[]|array $exists
     * @param array $qv
     * @return void
     */
    public static function fetchParents(WP_Term $term, array &$exists = [], array $qv = []): void
    {
        if (is_null(static::$fetched)) {
            static::$fetched = (new Collection($exists ?: []))->pluck('term_id')->all();
        }

        if (!isset(static::$fetched[$term->term_id])) {
            /* * /
             if (isset($qv['child_of']) && ($qv['child_of'] == $term->parent)) {
                return;
            }

            if (isset($qv['parent']) && ($qv['parent'] == $term->parent)) {
                return;
            }
            /**/

            if ($term->parent && ($p = get_term($term->parent, $term->taxonomy)) && $p instanceof WP_Term) {
                array_push($exists, $p);

                if ($p->parent) {
                    static::fetchParents($p, $exists, $qv);
                }
            }
        }
    }
}