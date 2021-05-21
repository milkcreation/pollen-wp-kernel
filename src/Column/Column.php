<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Pollen\WpKernel\WpScreen;
use Pollen\WpKernel\WpScreenInterface;
use WP_Screen;

final class Column implements ColumnInterface
{
    /**
     * Liste des éléments affichés sur la page courante.
     * @var ColumnItemController[]
     */
    protected $currents = [];

    /**
     * Liste des éléments déclarés.
     * @var ColumnItemController[]
     */
    protected $items = [];

    /**
     * Liste des éléments à déclarer.
     * @var array
     */
    protected $registred = [];

    /**
     * Liste des éléments à supprimer.
     * @var array
     */
    protected $unregistred = [];

    /**
     * Instance de l'écran d'affichage courant.
     * @var WpScreenInterface
     */
    protected $screen;

    /**
     * CONSTRUCTEUR.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('wp_loaded', function () {
            foreach (config('column', []) as $screen => $items) {
                foreach ($items as $name => $attrs) {
                    $name = (is_numeric($name)) ? Str::random() : $name;

                    $_screen = (preg_match('/(.*)@(post_type|taxonomy|user)/', $screen))
                        ? "list::{$screen}" : $screen;

                    $this->items[] = new ColumnItemController($name, $attrs, $_screen);
                }
            }
        }, 0);

        add_action('current_screen', function (WP_Screen $wp_current_screen) {
            $this->screen = new WpScreen($wp_current_screen);

            foreach ($this->items as $item) {
                $item->load($this->screen);
            }

            $this->parseColumn($this->screen->getObjectType(), $this->screen->getObjectName());
        });

        add_action('admin_init', function () {
            if (!defined('DOING_AJAX') || DOING_AJAX !== true) :
                return;
            endif;
            if (!in_array(request()->get('action'), ['inline-save', 'inline-save-tax'])) :
                return;
            endif;

            switch(request()->get('action')) :
                case 'inline-save' :
                    $this->screen = WpScreen::get('list::' . request()->post('post_type') . '@post_type');
                    break;
                case 'inline-save-tax' :
                    $this->screen = WpScreen::get('list::' . request()->post('taxonomy') . '@taxonomy');
                    break;
            endswitch;

            foreach ($this->items as $item) :
                $item->load($this->screen);
            endforeach;

            $this->parseColumn($this->screen->getObjectType(), $this->screen->getObjectName());
        }, 1000000);
    }

    /**
     * @inheritdoc
     */
    public function add($screen, $name, $attrs = [])
    {
        config()->set("column.{$screen}.{$name}", $attrs);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function parseColumn($object_type, $object_name)
    {
        switch ($object_type) :
            case 'post_type' :
                add_filter("manage_edit-{$object_name}_columns", [$this, 'parseColumnHeaders'], 1);
                add_action("manage_{$object_name}_posts_custom_column", [$this, 'parseColumnContents'], 25, 2);
                break;
            case 'taxonomy' :
                add_filter("manage_edit-{$object_name}_columns", [$this, 'parseColumnHeaders']);
                add_filter("manage_{$object_name}_custom_column", [$this, 'parseColumnContents'], 25, 3);
                break;
            case 'user' :
                add_filter("manage_{$object_name}_columns", [$this, 'parseColumnHeaders']);
                add_filter("manage_{$object_name}_custom_column", [$this, 'parseColumnContents'], 25, 3);
                break;
            default :
                add_filter('manage_columns', [$this, 'parseColumnHeaders']);
                add_filter('manage_custom_column', [$this, 'parseColumnContents'], 25, 3);
                break;
        endswitch;
    }

    /**
     * @inheritdoc
     */
    final public function parseColumnHeaders($headers)
    {
        $this->currents = (new Collection($this->items))->filter(function (ColumnItemController $item) {
            return $item->isActive();
        });

        // Traitement des colonnes système.
        $i = 0;
        foreach ($headers as $name => $title) :
            /** @var ColumnItemController $column */
            $column = new ColumnItemController(
                    $name,
                    [
                        'title'    => $title,
                        'position' => 0.99+$i++,
                    ],
                    $this->screen
            );
            $column->load($this->screen);
            $this->currents[] = $column;
        endforeach;

        // Ordonnacement
        $max = (new Collection($this->currents))->max(
            function (ColumnItemController $item) {
                return $item->getPosition();
            }
        );
        if ($max) :
            $pad = 0;
            (new Collection($this->currents))->each(
                function (ColumnItemController $item) use (&$pad, $max) {
                    $position = $item->getPosition() ? : ++$pad+$max;

                    return $item->set('position', absint($position));
                }
            );
        endif;

        $this->currents = (new Collection($this->currents))->sortBy(
            function (ColumnItemController $item) {
                return $item->getPosition();
            }
        );

        // Définition des entêtes.
        $headers = [];
        foreach ($this->currents as $c) :
            $headers[$c->getName()] = $c->getHeader();
        endforeach;

        remove_filter(current_filter(), [$this, 'parseColumnHeaders']);

        return $headers;
    }

    /**
     * @inheritdoc
     */
    final public function parseColumnContents()
    {
        foreach ($this->currents as $c) :
            $echo = false;

            $output = '';
            switch ($this->screen->getObjectType()) :
                case 'post_type' :
                    $column_name = func_get_arg(0);
                    $echo = true;

                    if ($column_name !== $c->getName()) :
                        continue 2;
                    endif;
                    break;
                default:
                case 'taxonomy' :
                case 'custom' :
                    $output = func_get_arg(0);
                    $column_name = func_get_arg(1);

                    if ($column_name !== $c->getName()) :
                        continue 2;
                    endif;
                    break;
            endswitch;

            $content = $c->getContent() ?: $output;
            $output = is_callable($content) ? call_user_func_array($content, func_get_args()) : $content;

            if ($echo) :
                echo $output;
                break;
            else :
                return $output;
            endif;
        endforeach;

        return '';
    }

    /**
     * @inheritDoc
     */
    public function stack(string $screen, array $columns): ColumnInterface
    {
        foreach ($columns as $name => $attrs) {
            $this->add($screen, $name, $attrs);
        }

        return $this;
    }
}