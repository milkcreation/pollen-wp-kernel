<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use Pollen\Mail\MailManagerInterface;
use Pollen\Support\Proxy\ContainerProxy;
use Psr\Container\ContainerInterface as Container;

class WpMail
{
    use ContainerProxy;

    /**
     * @var MailManagerInterface
     */
    protected $mail;

    /**
     * @param MailManagerInterface $mail
     * @param Container $container
     */
    public function __construct(MailManagerInterface $mail, Container $container)
    {
        $this->mail = $mail;
        $this->setContainer($container);

        if (!$this->mail->defaults('from')) {
            $this->mail->defaults(['from' => $this->getAdminAddress()]);
        }

        if (!$this->mail->defaults('to')) {
            $this->mail->defaults(['to' => $this->getAdminAddress()]);
        }

        if (!$this->mail->defaults('charset')) {
            $this->mail->defaults(['charset' => get_bloginfo('charset')]);
        }

        add_filter(
            'wp_mail_from',
            function ($from_email) {
                if (preg_match('/^wordpress@/', $from_email)) {
                    [$admin_email, $admin_name] = $this->getAdminAddress();

                    $from_email = $admin_email ?? $from_email;

                    add_filter(
                        'wp_mail_from_name',
                        function ($from_name) use ($admin_name) {
                            return $admin_name ?? $from_name;
                        }
                    );
                }
                return $from_email;
            }
        );
    }

    /**
     * Récupération de l'adresse de destination de l'administrateur de site.
     *
     * @return array
     */
    protected function getAdminAddress(): array
    {
        $admin_email = get_option('admin_email');
        $admin_name = ($user = get_user_by('email', get_option('admin_email'))) ? $user->display_name : '';

        return [$admin_email, $admin_name];
    }
}