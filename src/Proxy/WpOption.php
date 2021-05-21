<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Proxy;

use Pollen\Proxy\AbstractProxy;
use Pollen\WpKernel\Option\OptionInterface;
use Pollen\WpKernel\Option\OptionPageInterface;

/**
 * @method static OptionPageInterface|null getPage(string $name)
 * @method static OptionPageInterface|null registerPage(string $name, array|OptionContract $attrs = [])
 */
class WpOption extends AbstractProxy
{
    /**
     * {@inheritDoc}
     *
     * @return OptionInterface
     */
    public static function getInstance(): OptionInterface
    {
        return parent::getInstance();
    }

    /**
     * @inheritDoc
     */
    public static function getInstanceIdentifier(): string
    {
        return 'wp.option';
    }
}