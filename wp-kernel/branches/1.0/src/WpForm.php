<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Form\FormManagerInterface;
use Pollen\Support\Proxy\ContainerProxy;
use Psr\Container\ContainerInterface as Container;

class WpForm
{
    use ContainerProxy;

    /**
     * @var FormManagerInterface
     */
    protected $form;

    /**
     * @param FormManagerInterface $form
     * @param Container $container
     */
    public function __construct(FormManagerInterface $form, Container $container)
    {
        $this->form = $form;
        $this->setContainer($container);

        /**
        add_action(
            'wp',
            function () {
                foreach ($this->form->all() as $form) {
                    $form->events()->on(
                        'field.get.value',
                        function (TriggeredEventInterface $event, &$value) {
                            $value = Arr::stripslashes($value);
                        }
                    );
                }
            }
        );

        add_action(
            'init',
            function () {
                if (is_admin()) {
                    events()->trigger('wp-admin.form.boot');

                    foreach ($this->form->all() as $form) {
                        $this->form->current($form);
                        $form->boot();
                        $this->form->reset();
                    }

                    events()->trigger('wp-admin.form.booted');
                }
            },
            999999
        );
         */
    }
}
