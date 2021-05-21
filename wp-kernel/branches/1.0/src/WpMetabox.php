<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Metabox\MetaboxManagerInterface;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\Support\Proxy\HttpRequestProxy;
use Psr\Container\ContainerInterface as Container;
use Pollen\WpKernel\Metabox\Contexts\SideContext;
use Pollen\WpKernel\Metabox\Contexts\SideContextInterface;
use Pollen\WpKernel\Metabox\Drivers\FilefeedDriver;
use Pollen\WpKernel\Metabox\Drivers\ImagefeedDriver;
use Pollen\WpKernel\Metabox\Drivers\VideofeedDriver;
use WP_Post;
use WP_Screen;
use WP_Term;
use WP_User;

class WpMetabox
{
    use ContainerProxy;
    use HttpRequestProxy;

    /**
     * @var MetaboxManagerInterface
     */
    protected $metabox;

    /**
     * Liste des indices de qualification de données de post.
     * @var array
     */
    protected $postKeys = [
        'ID',
        'post_author',
        'post_date',
        'post_date_gmt',
        'post_content',
        'post_title',
        'post_excerpt',
        'post_status',
        'comment_status',
        'ping_status',
        'post_password',
        'post_name',
        'to_ping',
        'pinged',
        'post_modified',
        'post_modified_gmt',
        'post_content_filtered',
        'post_parent',
        'guid',
        'menu_order',
        'post_type',
        'post_mime_type',
        'comment_count',
    ];

    /**
     * Liste des indices de qualification de données de terme de taxonomie.
     * @var array
     */
    protected $termKeys = [
        'description',
        'name',
        'parent',
        'term_id',
        'slug',
    ];

    /**
     * Liste des indices de qualification de données utilisateur.
     * @var array
     */
    protected $userKeys = [
        'ID',
        'login',
        'first_name',
        'last_name',
    ];

    /**
     * @param MetaboxManagerInterface $metabox
     * @param Container $container
     */
    public function __construct(MetaboxManagerInterface $metabox, Container $container)
    {
        $this->metabox = $metabox;
        $this->setContainer($container);

        return;
        $this->registerContexts();
        $this->registerOverride();

        $this->metabox
            ->registerContext('side', SideContext::class)
            ->boot();

        add_action(
            'current_screen',
            function (WP_Screen $wp_screen) {
                $wpAdminScreen = new MetaboxWpAdminScreen($wp_screen);

                if ($this->metaboxManager->hasScreen($wpAdminScreen->getAlias())) {
                    $this->metaboxManager->setCurrentScreen($wpAdminScreen->getAlias())->dispatch();
                    $drivers = $this->metaboxManager->all();

                    switch ($wpAdminScreen->getObjectType()) {
                        case 'post_type' :
                            // Liste des contextes Wordpress
                            // _____________________________________
                            //'edit_form_top',
                            //'edit_form_before_permalink',
                            //'edit_form_after_title',
                            //'edit_form_after_editor',
                            //'submitpage_box',
                            //'submitpost_box',
                            //'edit_page_form',
                            //'edit_form_advanced',
                            //'dbx_post_sidebar'
                            // _____________________________________
                            if ($wpAdminScreen->is_block_editor()) {
                                add_meta_box(
                                    'blockEditor-metabox',
                                    'Réglages',
                                    function (WP_Post $wp_post) {
                                        echo $this->metaboxManager->render('tab', $wp_post);
                                    }
                                );
                            } else {
                                add_action(
                                    $wpAdminScreen->getObjectName(
                                    ) === 'page' ? 'edit_page_form' : 'edit_form_advanced',
                                    function (WP_Post $wp_post) {
                                        echo $this->metaboxManager->render('tab', $wp_post);
                                    },
                                );
                            }
                            array_walk(
                                $drivers,
                                function (MetaboxDriverInterface $driver) use ($wpAdminScreen) {
                                    $name = $driver->getName();
                                    $post_type = $wpAdminScreen->getObjectName();
                                    if ($name && !in_array($name, $this->postKeys) && !PostType::meta()->exists(
                                            $post_type,
                                            $name
                                        )) {
                                        PostType::meta()->registerSingle($post_type, $name);
                                    }
                                    if ($driver->getContext() instanceof SideContextInterface) {
                                        add_action(
                                            'add_meta_boxes',
                                            function () use ($driver) {
                                                add_meta_box(
                                                    $driver->getAlias(),
                                                    $driver->getTitle(),
                                                    function (...$args) use ($driver) {
                                                        echo $driver->setArgs($args)->render();
                                                    },
                                                    null,
                                                    'side'
                                                );
                                            }
                                        );
                                    }
                                    if (($driver->getValue() === null) && ($driver->getName() !== null)) {
                                        $driver->setHandler(
                                            function (MetaboxDriverInterface $driver, WP_Post $wp_post) {
                                                $driver->set('wp_post', $wp_post);
                                                if (in_array($driver->getName(), $this->postKeys)) {
                                                    $driver->setValue($wp_post->{$name});
                                                } else {
                                                    $driver->setValue(
                                                        get_post_meta(
                                                            $wp_post->ID,
                                                            $driver->getName(),
                                                            true
                                                        )
                                                    );
                                                }
                                            }
                                        );
                                    }
                                }
                            );
                            break;
                        case 'options' :
                            add_settings_section(
                                'metabox-tab',
                                null,
                                function () {
                                    echo $this->metaboxManager->render('tab');
                                },
                                $wpAdminScreen->getObjectName()
                            );
                            array_walk(
                                $drivers,
                                function (MetaboxDriverInterface $driver) {
                                    if (($driver->getValue() === null) && ($driver->getName() !== null)) {
                                        $driver->setHandler(
                                            function (MetaboxDriverInterface $driver) {
                                                $driver->setValue(get_option($driver->getName()));
                                            }
                                        );
                                    }
                                }
                            );
                            break;
                        case 'taxonomy' :
                            add_action(
                                $wpAdminScreen->getObjectName() . '_edit_form',
                                function (WP_Term $wp_term, string $taxonomy) {
                                    echo $this->metaboxManager->render('tab', $wp_term, $taxonomy);
                                },
                                10,
                                2
                            );
                            array_walk(
                                $drivers,
                                function (MetaboxDriverInterface $driver) use ($wpAdminScreen) {
                                    $name = $driver->getName();
                                    $taxonomy = $wpAdminScreen->getObjectName();
                                    if ($name && !in_array($name, $this->termKeys) && !Taxonomy::meta()->exists(
                                            $taxonomy,
                                            $name
                                        )) {
                                        Taxonomy::meta()->registerSingle($taxonomy, $name);
                                    }
                                    if (($driver->getValue() === null) && ($driver->getName() !== null)) {
                                        $driver->setHandler(
                                            function (MetaboxDriverInterface $driver, WP_Term $wp_term) {
                                                $driver->set('wp_term', $wp_term);
                                                if (in_array($driver->getName(), $this->termKeys)) {
                                                    $driver->setValue($wp_term->{$name});
                                                } else {
                                                    $driver->setValue(
                                                        get_term_meta(
                                                            $wp_term->term_id,
                                                            $driver->getName(),
                                                            true
                                                        )
                                                    );
                                                }
                                            }
                                        );
                                    }
                                }
                            );
                            break;
                         case 'user' :
                             add_action('show_user_profile', function (WP_User $wp_user) {
                                 echo $this->metaboxManager->render('tab', $wp_user);
                             });
                             add_action('edit_user_profile', function (WP_User $wp_user) {
                                 echo $this->metaboxManager->render('tab', $wp_user);
                             });
                             array_walk(
                                 $drivers,
                                 function (MetaboxDriverInterface $driver) {
                                     $name = $driver->getName();
                                     if ($name && !in_array($name, $this->userKeys) && !User::meta()->exists($name)) {
                                         User::meta()->registerSingle($name);
                                     }
                                     if (($driver->getValue() === null) && ($driver->getName() !== null)) {
                                         $driver->setHandler(
                                             function (MetaboxDriverInterface $driver, WP_User $wp_user) {
                                                 $driver->set('wp_user', $wp_user);
                                                 if (in_array($driver->getName(), $this->userKeys)) {
                                                     $driver->setValue($wp_user->{$name});
                                                 } else {
                                                     $driver->setValue(
                                                         get_user_meta(
                                                             $wp_user->ID,
                                                             $driver->getName(),
                                                             true
                                                         )
                                                     );
                                                 }
                                             }
                                         );
                                     }
                                 }
                             );
                         break;
                    }
                } elseif ($wpAdminScreen->getAlias() === '@options') {
                    $objectName = $this->httpRequest()->input('option_page', '');
                    $screen = "{$objectName}@options";

                    if ($this->metaboxManager->hasScreen("{$objectName}@options")) {
                        $this->metaboxManager->setCurrentScreen($screen)->dispatch();
                        add_filter(
                            'allowed_options',
                            function ($allowed_options) use ($objectName) {
                                if (!isset($allowed_options[$objectName])) {
                                    $allowed_options[$objectName] = [];
                                }
                                return $allowed_options;
                            }
                        );
                        $drivers = $this->metaboxManager->all();
                        array_walk(
                            $drivers,
                            function (MetaboxDriverInterface $driver) use ($objectName) {
                                if ($name = $driver->getName()) {
                                    register_setting($objectName, $name);
                                }
                            }
                        );
                    }
                }
            }
        );
    }

    /**
     * Déclaration des contextes d'affichage.
     *
     * @return void
     */
    public
    function registerContexts(): void
    {
        $this->getContainer()->add(
            SideContext::class,
            function () {
                return new SideContext($this->metaboxManager);
            }
        );
    }

    /**
     * Déclaration de la surchage des services.
     *
     * @return void
     */
    public
    function registerOverride(): void
    {
        $this->getContainer()->add(
            BaseFilefeedDriver::class,
            function () {
                return new FilefeedDriver($this->metaboxManager);
            }
        );
        $this->getContainer()->add(
            BaseImagefeedDriver::class,
            function () {
                return new ImagefeedDriver($this->metaboxManager);
            }
        );
        $this->getContainer()->add(
            BaseVideofeedDriver::class,
            function () {
                return new VideofeedDriver($this->metaboxManager);
            }
        );
    }
}
