<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Kernel\Application;
use Pollen\Kernel\ApplicationInterface;
use Pollen\WpHook\WpHookerInterface;
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