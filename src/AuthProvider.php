<?php

namespace YG\Phalcon\Auth;

use Phalcon\Config;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class AuthProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        /** @var Config $config */
        $config = $di->getShared('config');
        $authConfig = $config->get('auth');
        $authConfig = $authConfig ? $authConfig->toArray() : [];
        $authConfig['cacheDir'] = $config->path('application.cacheDir') . 'session/';

        $di->setShared('authPermission', Permission::class);
        $di->setShared('authSession', AuthSession::class);
        $di->setShared('authResourcePermission', ResourcePermission::class);
        $di->setShared('auth', function() use ($authConfig) {
            return new Auth($authConfig);
        });
    }
}