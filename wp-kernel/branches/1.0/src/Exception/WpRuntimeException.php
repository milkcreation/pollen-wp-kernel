<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Exception;

use RuntimeException;
use Throwable;

class WpRuntimeException extends RuntimeException
{
    /**
     * @param string|null $reason
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $reason = null, string $message = '', int $code = 0, Throwable $previous = null)
    {
        if (empty($message)) {
            if ($reason === null) {
                $message = 'Wordpress does not seem to be installed.';
            }  else {
                $message = sprintf('Wordpress does not seem to be installed [%s].', $reason);
            }
        }

        parent::__construct($message, $code, $previous);
    }
}