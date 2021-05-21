<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Partial\PartialManagerInterface;
use Pollen\Partial\Drivers\BreadcrumbDriver as BaseBreadcrumbDriver;
use Pollen\Partial\Drivers\CurtainMenuDriver as BaseCurtainMenuDriver;
use Pollen\Partial\Drivers\DownloaderDriver as BaseDownloaderDriver;
use Pollen\Partial\Drivers\ImageLightboxDriver as BaseImageLightboxDriver;
use Pollen\Partial\Drivers\ModalDriver as BaseModalDriver;
//use Pollen\Partial\Drivers\PaginationDriver as BasePaginationDriver;
//use Pollen\Partial\Drivers\PdfViewerDriver as BasePdfViewerDriver;
use Pollen\Support\Proxy\ContainerProxy;
use Psr\Container\ContainerInterface as Container;
use Pollen\WpKernel\Partial\Drivers\BreadcrumbDriver;
use Pollen\WpKernel\Partial\Drivers\CurtainMenuDriver;
use Pollen\WpKernel\Partial\Drivers\DownloaderDriver;
use Pollen\WpKernel\Partial\Drivers\ImageLightboxDriver;
use Pollen\WpKernel\Partial\Drivers\MediaLibraryDriver;
use Pollen\WpKernel\Partial\Drivers\ModalDriver;

class WpPartial
{
    use ContainerProxy;

    /**
     * Définition des pilotes spécifiques à Wordpress.
     * @var array
     */
    protected $drivers = [
        'media-library' => MediaLibraryDriver::class,
    ];

    /**
     * @var PartialManagerInterface
     */
    protected $partial;

    /**
     * @param PartialManagerInterface $partial
     * @param Container $container
     */
    public function __construct(PartialManagerInterface $partial, Container $container)
    {
        $this->partial = $partial;
        $this->setContainer($container);

        $this->registerDrivers();
        $this->registerOverride();

        foreach ($this->drivers as $name => $alias) {
            $this->partial->register($name, $alias);
        }
    }

    /**
     * Déclaration des pilotes spécifiques à Wordpress.
     *
     * @return void
     */
    public function registerDrivers(): void
    {
        $this->containerAdd(MediaLibraryDriver::class, function () {
            return new MediaLibraryDriver($this->containerGet(PartialManagerInterface::class));
        });
    }

    /**
     * Déclaration des controleurs de surchage des portions d'affichage.
     *
     * @return void
     */
    public function registerOverride(): void
    {
        $this->containerAdd(BaseBreadcrumbDriver::class, function () {
            return new BreadcrumbDriver($this->containerGet(PartialManagerInterface::class));
        });

        $this->containerAdd(BaseCurtainMenuDriver::class, function () {
            return new CurtainMenuDriver($this->containerGet(PartialManagerInterface::class));
        });

        $this->containerAdd(BaseDownloaderDriver::class, function () {
            return new DownloaderDriver($this->containerGet(PartialManagerInterface::class));
        });

        $this->containerAdd(BaseImageLightboxDriver::class, function () {
            return new ImageLightboxDriver($this->containerGet(PartialManagerInterface::class));
        });

        $this->containerAdd(BaseModalDriver::class, function () {
            return new ModalDriver($this->containerGet(PartialManagerInterface::class));
        });

        /**
        $this->containerAdd(BasePaginationDriver::class, function () {
            return new PaginationDriver($this->partialManager);
        });

        $this->containerAdd(BasePdfviewerDriver::class, function () {
            return new PdfViewerDriver($this->partialManager);
        });
         */
    }
}