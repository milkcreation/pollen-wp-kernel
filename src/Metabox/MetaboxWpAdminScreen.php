<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Metabox;

use BadMethodCallException;
use Throwable;
use Pollen\Support\Proxy\HttpRequestProxy;
use Pollen\WpUser\WpUserProxy;
use Pollen\WpKernel\Proxy\WpOption;
use WP_Screen;

/**
 * @mixin WP_Screen
 */
class MetaboxWpAdminScreen implements MetaboxWpAdminScreenInterface
{
    use HttpRequestProxy;
    use WpUserProxy;
    /**
     * Instance de l'écran en relation.
     * @var WP_Screen|null
     */
    protected $wpScreen;

    /**
     * Nom de qualification de l'objet Wordpress associé.
     * @var string|null
     */
    protected $hookName = null;

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
     * @param WP_Screen $wp_screen Objet screen Wordpress.
     */
    public function __construct(WP_Screen $wp_screen)
    {
        $this->wpScreen = $wp_screen;

        $this->parse();
    }

    /**
     * @inheritDoc
     */
    public function __get(string $key)
    {
        return $this->getWpScreen()->{$key};
    }

    /**
     * @inheritDoc
     */
    public function __call(string $method, array $arguments)
    {
        try {
            return $this->getWpScreen()->{$method}(...$arguments);
        } catch (Throwable $e) {
            throw new BadMethodCallException(
                sprintf(
                    'MetaboxDriver [%s] method call [%s] throws an exception: %s',
                    $this->getAlias(),
                    $method,
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @inheritDoc
     */
    public static function get($screen = ''): ?MetaboxWpAdminScreenInterface
    {
        if ($screen instanceof MetaboxWpAdminScreenInterface) {
            return $screen;
        }

        if ($screen instanceof WP_Screen) {
            return new static($screen);
        }

        if (is_string($screen)) {
            if (preg_match('/(.*)@(post_type|post_types|taxonomy|taxonomies|user|users)/', $screen, $matches)) {
                $attrs = [];
                switch ($matches[3]) {
                    case 'post_type' :
                        $attrs = [
                            'id'        => $matches[1],
                            'base'      => 'post',
                            'action'    => '',
                            'post_type' => $matches[1],
                            'taxonomy'  => '',
                        ];
                        break;
                    case 'post_types' :
                        $attrs = [
                            'id'        => 'edit-' . $matches[1],
                            'base'      => 'edit',
                            'action'    => '',
                            'post_type' => $matches[1],
                            'taxonomy'  => '',
                        ];
                        break;
                    case 'taxonomy' :
                        $attrs = [
                            'id'        => 'edit-' . $matches[1],
                            'base'      => 'term',
                            'action'    => '',
                            'post_type' => '',
                            'taxonomy'  => $matches[1],
                        ];
                        break;
                    case 'taxonomies' :
                        $attrs = [
                            'id'        => 'edit-' . $matches[1],
                            'base'      => 'edit-tags',
                            'action'    => '',
                            'post_type' => '',
                            'taxonomy'  => $matches[1],
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
                    case 'users' :
                        $attrs = [
                            'id'        => 'users',
                            'base'      => 'users',
                            'action'    => '',
                            'post_type' => '',
                            'taxonomy'  => '',
                        ];
                        break;
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
        return $this->id;
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
    public function getWpScreen(): WP_Screen
    {
        return $this->wpScreen;
    }

    /**
     * @inheritDoc
     */
    public function isCurrent(): bool
    {
        $currentScreen = get_current_screen();

        return (($currentScreen !== null) &&
            (
                ($currentScreen->id === $this->id) ||
                (($currentScreen->id === 'profile') && ($this->base === 'user-edit'))
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function parse(): MetaboxWpAdminScreenInterface
    {
        if (preg_match('/^settings_page_(.*)/', $this->id, $matches)) {
            $this->objectName = $matches[1];
            $this->objectType = 'options';
        } elseif ($this->id === 'options' && $this->base === 'options') {
            $this->objectName = '';
            $this->objectType = 'options';
        } elseif (
            ($this->base === 'term') &&
            preg_match('/^edit-(.*)/', $this->id, $matches) &&
            taxonomy_exists($matches[1])
        ) {
            $this->objectName = $matches[1];
            $this->objectType = 'taxonomy';
        } elseif (
            ($this->base === 'edit-tags') &&
            preg_match('/^edit-(.*)/', $this->id, $matches) &&
            taxonomy_exists($matches[1])
        ) {
            $this->objectName = $matches[1];
            $this->objectType = 'taxonomy';
        } elseif (
            ($this->base === 'edit') &&
            preg_match('/^edit-(.*)/', $this->id, $matches) &&
            post_type_exists($matches[1])
        ) {
            $this->objectName = $matches[1];
            $this->objectType = 'post_type';
        } elseif (post_type_exists($this->id)) {
            $this->objectName = $this->id;
            $this->objectType = 'post_type';
        } elseif ($this->id === 'users') {
            $this->objectName = $this->id;
            $this->objectType = 'user';
        } elseif (
        ((
                ($this->base === 'user-edit') &&
                ($user_id = (int)$this->httpRequest()->input('user_id', 0)) &&
                ($user = $this->wpUser($user_id))) ||
            (($this->base === 'profile') && ($user = $this->wpUser(get_current_user_id()))
            ))
        ) {
            $this->objectName = implode('|', array_keys($user->getRoles()));
            $this->objectType = 'user';
        } elseif (preg_match('/^(.*)_page_(.*)$/', $this->id, $matches) && Option::getPage($matches[2])) {
            $this->objectName = $matches[2];
            $this->objectType = 'options';
        }

        return $this;
    }
}
