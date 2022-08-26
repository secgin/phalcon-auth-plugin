<?php

namespace YG\Phalcon\Auth;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class AuthProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $cacheDir = $di->getShared('config')->application->cacheDir;

        $di->setShared('authPermission', Permission::class);
        $di->setShared('authSession', AuthSession::class);
        $di->setShared('authResourcePermission', ResourcePermission::class);
        $di->setShared('auth', function() use ($cacheDir) {
            return new Auth([
                'cacheDir' => $cacheDir
            ]);
        });
    }
}