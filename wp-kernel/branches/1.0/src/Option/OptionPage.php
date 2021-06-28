<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Option;

use Pollen\Support\ParamsBag;
use Pollen\Support\Proxy\ViewProxy;
use Pollen\View\ViewInterface;
use WP_Admin_Bar;

class OptionPage extends ParamsBag implements OptionPageInterface
{
    use ViewProxy;

    /**
     * Nom de qualification.
     */
    protected string $name = '';

    /**
     * Instance du gestionnaire d'options.
     */
    protected ?OptionInterface $manager = null;

    /**
     * Template view instance.
     */
    protected ?ViewInterface $view = null;

    /**
     * CONSTRUCTEUR.
     *
     * @return void
     */
    public function __construct()
    {
        add_action(
            'admin_menu',
            function () {
                if ($attrs = $this->get('admin_menu', [])) {
                    if ($attrs['parent_slug']) {
                        $hookname = add_submenu_page(
                            $attrs['parent_slug'],
                            $attrs['page_title'],
                            $attrs['menu_title'],
                            $attrs['capability'],
                            $attrs['menu_slug'],
                            $attrs['function']
                        );
                    } else {
                        $hookname = add_menu_page(
                            $attrs['page_title'],
                            $attrs['page_title'],
                            $attrs['capability'],
                            $attrs['menu_slug'],
                            $attrs['function'],
                            $attrs['icon_url'],
                            $attrs['position']
                        );
                    }
                    $this->set(compact('hookname'));
                }
            }
        );

        add_action(
            'admin_bar_menu',
            function (WP_Admin_Bar $wp_admin_bar) {
                if (!is_admin() && ($params = $this->get('admin_bar', []))) {
                    $params = is_array($params) ? $params : [];

                    $params['href'] = $params['href'] ??
                        admin_url('/options-general.php?page=' . $this->get('admin_menu.menu_slug', $this->getName()));

                    $wp_admin_bar->add_node($params);
                }
            },
            50
        );

        parent::__construct();
        $this->parse();
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * @inheritDoc
     */
    public function boot(): void { }

    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return [
            'admin_bar'  => false,
            'admin_menu' => true,
            'cap'        => 'manage_options',
            'hookname'   => null,
            'title'      => 'Réglage des options',
            'page_title' => null,
            'view'       => [],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getHookname(): string
    {
        return $this->get('hookname', 'settings_page_' . $this->getName());
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function isSettingsPage(): bool
    {
        return !0 !== strpos($this->getHookname(), "settings_page_");
    }

    /**
     * @inheritDoc
     */
    public function parse(): void
    {
        parent::parse();

        $this->set('name', $this->getName());

        if (is_null($this->get('page_title'))) {
            $this->set('page_title', $this->get('title'));
        }

        if ($admin_menu = $this->get('admin_menu')) {
            $this->set(
                [
                    'admin_menu' => array_merge(
                        [
                            'parent_slug' => 'options-general.php',
                            'page_title'  => $this->get('title'),
                            'menu_title'  => $this->get('title'),
                            'capability'  => $this->get('cap'),
                            'menu_slug'   => $this->getName(),
                            'function'    => function () {
                                echo $this->render();
                            },
                            'icon_url'    => '',
                            'position'    => null,
                        ],
                        is_array($admin_menu) ? $admin_menu : []
                    ),
                ]
            );
        }

        if ($admin_bar = $this->get('admin_bar')) {
            $this->set(
                [
                    'admin_bar' => array_merge(
                        [
                            'id'     => $this->getName(),
                            'title'  => $this->get('title'),
                            'parent' => 'site-name',
                            'group'  => false,
                            'meta'   => [],
                        ],
                        is_array($admin_bar) ? $admin_bar : []
                    ),
                ]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function registerSettings(array $settings): OptionPageInterface
    {
        foreach ($settings as $k => $setting) {
            if (is_numeric($k)) {
                $name = (string)$setting;
                $args = [];
            } else {
                $name = $k;
                $args = $setting;
            }

            register_setting($this->getName(), $name, $args);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        return $this->view('index', $this->all());
    }

    /**
     * @inheritDoc
     */
    public function setManager(OptionInterface $manager): OptionPageInterface
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): OptionPageInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function view(?string $name = null, array $datas = [])
    {
        if ($this->view === null) {
            $directory = class_info($this->manager)->getDirname() . '/Resources/views/';

            $params = array_merge(
                [
                    'directory' => $directory
                ],
                config('options.view', []),
                $this->get('view', [])
            );

            $this->view = $this->viewManager()->createView('plates')
                ->setDirectory($params['directory']);

            if (!empty($params['override_dir'])) {
                $this->view->setOverrideDir($params['override_dir']);
            }

            $this->view->addExtension('isSettingPage', [$this, 'isSettingsPage']);
        }

        if (func_num_args() === 0) {
            return $this->view;
        }

        return $this->view->render($name, $datas);
    }
}