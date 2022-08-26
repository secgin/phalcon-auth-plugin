<?php

namespace YG\Phalcon\Auth;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class AuthProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $config = $di->getShared('config');
        $cacheDir = $config->application->cacheDir;
        $useAllowedIpAddress = $config->application->useAllowedIpAddress;

        $di->setShared('authPermission', Permission::class);
        $di->setShared('authSession', AuthSession::class);
        $di->setShared('authResourcePermission', ResourcePermission::class);
        $di->setShared('auth', function() use ($cacheDir, $useAllowedIpAddress) {
            return new Auth([
                'cacheDir' => $cacheDir,
                'useAllowedIpAddress' => $useAllowedIpAddress
            ]);
        });
    }
}