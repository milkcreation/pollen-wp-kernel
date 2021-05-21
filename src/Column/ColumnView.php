<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Column;

use BadMethodCallException;
use Exception;

class ColumnView
{
    /**
     * Liste des méthodes héritées.
     * @var array
     */
    protected $mixins = [];

    /**
     * @inheritDoc
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, $this->mixins)) {
            try {
                return call_user_func_array([$this->engine->params('column'), $method], $parameters);
            } catch (Exception $e) {
                throw new BadMethodCallException(
                    sprintf(
                        'La méthode [%s] de la colonne n\'est pas disponible.',
                        $method
                    )
                );
            }
        } else {
            return parent::__call($method, $parameters);
        }
    }
}