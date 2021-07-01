<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Asset\AssetManagerInterface;
use Pollen\Cookie\CookieJarInterface;
use Pollen\Database\DatabaseManagerInterface;
use Pollen\Debug\DebugManagerInterface;
use Pollen\Faker\FakerInterface;
use Pollen\Field\FieldManagerInterface;
use Pollen\Filesystem\StorageManagerInterface;
use Pollen\Form\FormManagerInterface;
use Pollen\Http\RequestInterface;
use Pollen\Kernel\Application;
use Pollen\Kernel\ApplicationInterface;
use Pollen\Kernel\KernelInterface;
use Pollen\Mail\MailManagerInterface;
use Pollen\Metabox\MetaboxManagerInterface;
use Pollen\Partial\PartialManagerInterface;
use Pollen\Routing\RouterInterface;
use Pollen\Session\SessionManagerInterface;
use Pollen\Support\DateTime;
use Pollen\Support\Env;
use Pollen\WpEnv\WpEnv;
use Pollen\WpHook\WpHookerInterface;
use Pollen\WpKernel\Exception\WpRuntimeException;
use Pollen\WpKernel\Support\Locale;
use Pollen\WpPost\WpPostManagerInterface;
use Pollen\WpTerm\WpTermManagerInterface;
use Pollen\WpUser\WpUserManagerInterface;

/**
 * @property-read WpHookerInterface wp_hook
 * @property-read WpPostManagerInterface wp_post
 * @property-read WpTermManagerInterface wp_term
 * @property-read WpUserManagerInterface wp_user
 */
class WpApplication extends Application implements WpApplicationInterface
{
    /**
     * Initialisation.
     *
     * @return void
     */
    public function preBuild(): void
    {
        if ($this->preBuilt === false) {
            parent::preBuild();

            new WpEnv($this->basePath);

            $this->preBuilt = true;
        }
    }

    /**
     * @inheritDoc
     */
    protected function preBuildKernel(): void
    {
        if (!$this->has(KernelInterface::class)) {
            $this->share(KernelInterface::class, new WpKernel($this));
        }
    }

    /**
     * @inheritDoc
     */
    protected function buildServices(): void
    {
        if (!defined('ABSPATH')) {
            throw new WpRuntimeException('ABSPATH Constant is missing.');
        }

        Locale::set(get_locale());
        Locale::setLanguages(get_site_transient('available_translations') ?: []);

        global $locale;
        DateTime::setLocale($locale);

        if ($this->has(DebugManagerInterface::class)) {
            new WpDebug($this->get(DebugManagerInterface::class), $this);
        }

        if ($this->has(RouterInterface::class)) {
            new WpRouting($this->get(RouterInterface::class), $this);
        }

        if ($this->has(AssetManagerInterface::class)) {
            new WpAsset($this->get(AssetManagerInterface::class), $this);
        }

        if ($this->has(CookieJarInterface::class)) {
            new WpCookie($this->get(CookieJarInterface::class), $this);
        }

        if ($this->has(DatabaseManagerInterface::class)) {
            new WpDatabase($this->get(DatabaseManagerInterface::class), $this);
        }

        if ($this->has(FakerInterface::class)) {
            new WpFaker($this->get(FakerInterface::class), $this);
        }

        if ($this->has(FieldManagerInterface::class)) {
            new WpField($this->get(FieldManagerInterface::class), $this);
        }

        if ($this->has(FormManagerInterface::class)) {
            new WpForm($this->get(FormManagerInterface::class), $this);
        }

        if ($this->has(RequestInterface::class)) {
            new WpHttpRequest($this->get(RequestInterface::class), $this);
        }

        if ($this->has(MailManagerInterface::class)) {
            new WpMail($this->get(MailManagerInterface::class), $this);
        }

        if ($this->has(MetaboxManagerInterface::class)) {
            new WpMetabox($this->get(MetaboxManagerInterface::class), $this);
        }

        if ($this->has(PartialManagerInterface::class)) {
            new WpPartial($this->get(PartialManagerInterface::class), $this);
        }

        if ($this->has(SessionManagerInterface::class)) {
            new WpSession($this->get(SessionManagerInterface::class), $this);
        }

        if ($this->has(StorageManagerInterface::class)) {
            new WpFilesystem($this->get(StorageManagerInterface::class), $this);
        }

        parent::buildServices();
    }

    /**
     * Chargement des variables globales d'environnement.
     *
     * @return void
     */
    protected function envLoad(): void
    {
        Env::load($this->getBasePath())->required(['DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_HOST']);
    }

    /**
     * @inheritDoc
     */
    public function registerAliases(): void
    {
        parent::registerAliases();

        if (isset($this->aliases[ApplicationInterface::class])) {
            $this->aliases[ApplicationInterface::class][] = WpApplicationInterface::class;
        }

        foreach (
            [
                WpHookerInterface::class => [
                    'wp_hook',
                ],
                WpPostManagerInterface::class => [
                    'wp_post',
                ],
                WpTermManagerInterface::class => [
                    'wp_term',
                ],
                WpUserManagerInterface::class => [
                    'wp_user',
                ],
            ] as $key => $aliases
        ) {
            foreach ($aliases as $alias) {
                $this->aliases[$alias] = $key;
            }
        }
    }
}