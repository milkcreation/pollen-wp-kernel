<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use App\App;
use Pollen\Asset\AssetManagerInterface;
use Pollen\Cookie\CookieJarInterface;
use Pollen\Database\DatabaseManagerInterface;
use Pollen\Debug\DebugManagerInterface;
use Pollen\Field\FieldManagerInterface;
use Pollen\Filesystem\StorageManagerInterface;
use Pollen\Form\FormManagerInterface;
use Pollen\Http\RequestInterface;
use Pollen\Kernel\ApplicationInterface;
use Pollen\Kernel\Kernel;
use Pollen\Mail\MailManagerInterface;
use Pollen\Metabox\MetaboxManagerInterface;
use Pollen\Partial\PartialManagerInterface;
use Pollen\Routing\RouterInterface;
use Pollen\Session\SessionManagerInterface;
use Pollen\Support\DateTime;
use Pollen\WpKernel\Exception\WpRuntimeException;
use Pollen\WpKernel\Support\Locale;
use RuntimeException;

class WpKernel extends Kernel
{
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        if (!$this->isBooted()) {
            if (defined('WP_INSTALLING') && (WP_INSTALLING === true)) {
                return;
            }

            parent::boot();
        }
    }

    /**
     * Chargement de l'application.
     *
     * @return void
     */
    protected function bootApp(): void
    {
        $this->app = class_exists(App::class) ? new App() : new WpApplication();

        if (!$this->app instanceof WpApplicationInterface) {
            throw new RuntimeException(sprintf('Application must be an instance of %s', WpApplicationInterface::class));
        }
    }

    /**
     * @implements
     */
    protected function bootServices(): void
    {
        if (!defined('ABSPATH')) {
            throw new WpRuntimeException('ABSPATH Constant is missing.');
        }

        if (file_exists(ABSPATH . 'wp-admin/includes/translation-install.php')) {
            require_once(ABSPATH . 'wp-admin/includes/translation-install.php');
        }

        Locale::set(get_locale());
        Locale::setLanguages(wp_get_available_translations() ?: []);

        global $locale;
        DateTime::setLocale($locale);

        if ($this->getApp()->has(DebugManagerInterface::class)) {
            new WpDebug($this->getApp()->get(DebugManagerInterface::class), $this->getApp());
        }

        if ($this->getApp()->has(RouterInterface::class)) {
            new WpRouting($this->getApp()->get(RouterInterface::class), $this->getApp());
        }

        if ($this->getApp()->has(AssetManagerInterface::class)) {
            new WpAsset($this->getApp()->get(AssetManagerInterface::class), $this->getApp());
        }

        if ($this->getApp()->has(CookieJarInterface::class)) {
            new WpCookie($this->getApp()->get(CookieJarInterface::class), $this->getApp());
        }

        if ($this->getApp()->has(DatabaseManagerInterface::class)) {
            new WpDatabase($this->getApp()->get(DatabaseManagerInterface::class), $this->getApp());
        }

        if ($this->getApp()->has(FieldManagerInterface::class)) {
            new WpField($this->getApp()->get(FieldManagerInterface::class), $this->getApp());
        }

        if ($this->getApp()->has(FormManagerInterface::class)) {
            new WpForm($this->getApp()->get(FormManagerInterface::class), $this->getApp());
        }

        if ($this->getApp()->has(RequestInterface::class)) {
            new WpHttpRequest($this->getApp()->get(RequestInterface::class), $this->getApp());
        }

        if ($this->getApp()->has(MailManagerInterface::class)) {
            new WpMail($this->getApp()->get(MailManagerInterface::class), $this->getApp());
        }

        if ($this->getApp()->has(MetaboxManagerInterface::class)) {
            new WpMetabox($this->getApp()->get(MetaboxManagerInterface::class), $this->getApp());
        }

        if ($this->getApp()->has(PartialManagerInterface::class)) {
            new WpPartial($this->getApp()->get(PartialManagerInterface::class), $this->getApp());
        }

        if ($this->getApp()->has(SessionManagerInterface::class)) {
            new WpSession($this->getApp()->get(SessionManagerInterface::class), $this->getApp());
        }

        if ($this->getApp()->has(StorageManagerInterface::class)) {
            new WpFilesystem($this->getApp()->get(StorageManagerInterface::class), $this->getApp());
        }

        parent::bootServices();
    }

    /**
     * {@inheritDoc}
     *
     * @return WpApplicationInterface
     */
    public function getApp(): ApplicationInterface
    {
        return parent::getApp();
    }
}
