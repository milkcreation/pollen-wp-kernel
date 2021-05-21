<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column;

abstract class AbstractColumnDisplayController implements ColumnDisplayInterface
{
    /**
     * Instance de l'élément.
     * @var ColumnItemController
     */
    protected $item;

    /**
     * Instance du moteur de gabarits d'affichage.
     * @return PlatesEngine
     */
    protected $viewer;

    /**
     * CONSTRUCTEUR.
     *
     * @param ColumnItemController $item
     *
     * @return void
     */
    public function __construct(ColumnItemController $item)
    {
        $this->item = $item;

        add_action('current_screen', function ($wp_current_screen) {
            if ($wp_current_screen->id === $this->item->getScreen()->getHookname()) {
                $this->load($wp_current_screen);
            }
        });

        $this->boot();
    }

    /**
     * Récupération de l'affichage depuis l'instance.
     *
     * @return string
     */
    public function __invoke()
    {
        return call_user_func_array([$this, 'content'], func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function content($var1, $var2, $var3 = null)
    {
        return 'Pas de contenu à afficher';
    }

    /**
     * {@inheritdoc}
     */
    public function header()
    {
        return $this->item->getTitle() ?: $this->item->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function load($wp_screen)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function viewer($view = null, $data = [])
    {
        if (!$this->viewer) {
            $this->viewer = View::getPlatesEngine(array_merge([
                'directory' => class_info($this)->getDirname() . '/views',
                'factory'   => ColumnView::class,
                'column'    => $this,
            ], config('column.viewer', []), $this->item->get('viewer', [])));
        }

        if (func_num_args() === 0) {
            return $this->viewer;
        }

        return $this->viewer->render($view, $data);
    }
}