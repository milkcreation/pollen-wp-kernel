<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

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
interface WpApplicationInterface extends ApplicationInterface
{
}