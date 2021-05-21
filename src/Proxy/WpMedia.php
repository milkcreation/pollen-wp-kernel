<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Proxy;

use Pollen\Proxy\AbstractProxy;

/**
 * @method static string|null getBase64Src(int $id)
 * @method static string|null getSrcFilename(string $src)
 */
class WpMedia extends AbstractProxy
{
    /**
     * @inheritDoc
     */
    public static function getInstanceIdentifier(): string
    {
        return 'wp.media';
    }
}