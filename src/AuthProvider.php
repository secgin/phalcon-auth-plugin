<?php

namespace YG\Phalcon\Auth;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class AuthProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $config = $di->getShared('config');
        $authConfig = $config->auth->toArray() ?? [];
        $authConfig['cacheDir'] = $config->application->cacheDir . 'session/';

        $di->setShared('authPermission', Permission::class);
        $di->setShared('authSession', AuthSession::class);
        $di->setShared('authResourcePermission', ResourcePermission::class);
        $di->setShared('auth', function() use ($authConfig) {
            return new Auth($authConfig);
        });
    }
}