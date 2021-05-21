<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Support\Proxy\HttpRequestProxy;
use Pollen\WpKernel\Proxy\WpOption;
use Pollen\WpUser\WpUserProxy;
use WP_Screen;

class WpScreen implements WpScreenInterface
{
    use HttpRequestProxy;
    use WpUserProxy;

    /**
     * Instance de l'écran en relation.
     * @var WP_Screen|null
     */
    protected $screen;

    /**
     * Nom de qualification de l'objet Wordpress associé.
     * @var string|null
     */
    protected $hookName;

    /**
     * Nom de qualification de l'objet Wordpress associé.
     * @var string
     */
    protected $objectName = '';

    /**
     * Typel'objet Wordpress associé.
     * @var string
     */
    protected $objectType = '';

    /**
     * CONSTRUCTEUR.
     *
     * @param WP_Screen $wp_screen Objet screen Wordpress.
     *
     * @return void
     */
    public function __construct(WP_Screen $wp_screen)
    {
        $this->screen = $wp_screen;

        $this->parse();
    }

    /**
     * @inheritDoc
     */
    public static function get($screen = ''): ?WpScreenInterface
    {
        if ($screen instanceof WpScreenInterface) {
            return $screen;
        }
        if ($screen instanceof WP_Screen) {
            return new static($screen);
        }
        if (is_string($screen)) {
            if (preg_match('/(edit|list)::(.*)@(post_type|taxonomy|user)/', $screen, $matches)) {
                $attrs = [];
                if ($matches[1] === 'edit') {
                    switch ($matches[3]) {
                        case 'post_type' :
                            $attrs = [
                                'id'        => $matches[2],
                                'base'      => 'post',
                                'action'    => '',
                                'post_type' => $matches[2],
                                'taxonomy'  => '',
                            ];
                            break;
                        case 'taxonomy' :
                            $attrs = [
                                'id'        => 'edit-' . $matches[2],
                                'base'      => 'term',
                                'action'    => '',
                                'post_type' => '',
                                'taxonomy'  => $matches[2],
                            ];
                            break;
                        case 'user' :
                            $attrs = [
                                'id'        => 'user-edit',
                                'base'      => 'user-edit',
                                'action'    => '',
                                'post_type' => '',
                                'taxonomy'  => '',
                            ];
                            break;
                    }
                } elseif ($matches[1] === 'list') {
                    switch ($matches[3]) {
                        case 'post_type' :
                            $attrs = [
                                'id'        => 'edit-' . $matches[2],
                                'base'      => 'edit',
                                'action'    => '',
                                'post_type' => $matches[2],
                                'taxonomy'  => '',
                            ];
                            break;
                        case 'taxonomy' :
                            $attrs = [
                                'id'        => 'edit-' . $matches[2],
                                'base'      => 'edit-tags',
                                'action'    => '',
                                'post_type' => '',
                                'taxonomy'  => $matches[2],
                            ];
                            break;
                        case 'user' :
                            $attrs = [
                                'id'        => 'users',
                                'base'      => 'users',
                                'action'    => '',
                                'post_type' => '',
                                'taxonomy'  => '',
                            ];
                            break;
                    }
                }

                $screen = clone WP_Screen::get($attrs['id'] ?? '');
                foreach ($attrs as $key => $value) {
                    $screen->{$key} = $value;
                }
            } elseif (preg_match('/(.*)@(options)/', $screen, $matches)) {
                switch ($matches[2]) {
                    case 'options' :
                        $screen = clone WP_Screen::get(
                            ($oPage = Option::getPage($matches[1]))
                                ? $oPage->getHookname() : 'settings_page_' . $matches[1]
                        );
                        break;
                }
            } else {
                $screen = clone WP_Screen::get($screen);
            }

            if ($screen instanceof WP_Screen) {
                return new static($screen);
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getAlias(): string
    {
        return $this->getObjectName() . '@' . $this->getObjectType();
    }

    /**
     * @inheritDoc
     */
    public function getHookname(): string
    {
        return $this->getScreen()->id;
    }

    /**
     * @inheritDoc
     */
    public function getObjectName(): string
    {
        return $this->objectName;
    }

    /**
     * @inheritDoc
     */
    public function getObjectType(): string
    {
        return $this->objectType;
    }

    /**
     * @inheritDoc
     */
    public function getScreen(): ?WP_Screen
    {
        return $this->screen;
    }

    /**
     * @inheritDoc
     */
    public function isCurrent(): bool
    {
        $current_screen = get_current_screen();

        return ($current_screen &&
            (
                ($current_screen->id === $this->getHookname()) ||
                (($current_screen->id === 'profile') && ($this->getHookname() === 'user-edit'))
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function parse(): WpScreenInterface
    {
        if (preg_match('/^settings_page_(.*)/', $this->screen->id, $matches)) {
            $this->objectName = $matches[1];
            $this->objectType = 'options';
        } elseif (
            ($this->screen->base === 'term') &&
            preg_match('/^edit-(.*)/', $this->screen->id, $matches) &&
            taxonomy_exists($matches[1])
        ) {
            $this->objectName = $matches[1];
            $this->objectType = 'taxonomy';
        } elseif (
            ($this->screen->base === 'edit-tags') &&
            preg_match('/^edit-(.*)/', $this->screen->id, $matches) &&
            taxonomy_exists($matches[1])
        ) {
            $this->objectName = $matches[1];
            $this->objectType = 'taxonomy';
        } elseif (
            ($this->screen->base === 'edit') &&
            preg_match('/^edit-(.*)/', $this->screen->id, $matches) &&
            post_type_exists($matches[1])
        ) {
            $this->objectName = $matches[1];
            $this->objectType = 'post_type';
        } elseif (post_type_exists($this->screen->id)) {
            $this->objectName = $this->screen->id;
            $this->objectType = 'post_type';
        } elseif ($this->screen->id === 'users') {
            $this->objectName = $this->screen->id;
            $this->objectType = 'user';
        } elseif (
        ((
                ($this->screen->base === 'user-edit') &&
                ($user_id = (int)$this->httpRequest()->input('user_id', 0)) &&
                ($user = $this->wpUser($user_id))) ||
                (
                    ($this->screen->base === 'profile') &&
                    ($user = $this->wpUser(get_current_user_id())
                )
            ))
        ) {
            $this->objectName = implode('|', array_keys($user->getRoles()));
            $this->objectType = 'user';
        } elseif (preg_match('/^(.*)_page_(.*)$/', $this->screen->id, $matches) && Option::getPage($matches[2])) {
            $this->objectName = $matches[2];
            $this->objectType = 'options';
        }

        return $this;
    }
}