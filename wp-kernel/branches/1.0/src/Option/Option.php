<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Option;

use Pollen\Support\ParamsBag;

class Option implements OptionInterface
{
    /**
     * Liste des pages de réglages des options déclarées.
     * @var OptionPageInterface[]|array
     */
    protected $pages = [];

    /**
     * CONSTRUCTEUR.
     *
     * @return void
     */
    public function __construct()
    {
        add_action(
            'init',
            function () {
                foreach (config('options', []) as $name => $attrs) {
                    if ($attrs !== false) {
                        $this->registerPage($name, $attrs);
                    }
                }

                if (!$this->getPage('tify_options')) {
                    $params = new ParamsBag();
                    $params->set(config('options.tify_options', []));

                    if (!$params->get('title')) {
                        $params->set('title', 'Options du site');
                    }

                    if (!$params->has('admin_bar')) {
                        $params->set('admin_bar', true);
                    }

                    $this->registerPage('tify_options', $params->all());
                }
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function getPage(string $name): ?OptionPageInterface
    {
        return $this->pages[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function registerPage(string $name, $attrs = []): ?OptionPageInterface
    {
        if ($attrs instanceof OptionPageInterface) {
            $page = $attrs;
        } elseif (is_array($attrs)) {
            $page = (new OptionPage())->set($attrs);
        } else {
            $page = null;
        }

        if ($page instanceof OptionPageInterface) {
            $page->setManager($this)->setName($name)->boot();

            return $this->pages[$name] = $page->parse();
        }

        return null;
    }
}