<?php

return [
    'enabled' => false,

    'endpoints' => [
        'login'        => '{{private_key}}_login',
        'logout'       => '{{private_key}}_logout',
        'resetpass'    => '{{private_key}}_resetpass',
        'lostpassword' => '{{private_key}}_lostpassword',
        'register'     => '{{private_key}}_register',
        'postpass'     => '{{private_key}}_postpass',
    ],
];