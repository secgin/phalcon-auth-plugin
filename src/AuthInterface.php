<?php

namespace YG\Phalcon\Auth;

/**
 * @property Permission               $authPermission
 * @property ResourcePermission       $authResourcePermission
 * @property AuthSession              $authSession
 *
 * @property User                     $user
 */
interface AuthInterface
{
    const
        NOT_LOGGED_IN = 'not_logged_in',
        NOT_ALLOWED_IP_ADDRESS = 'not_allowed_ip_address',
        NOT_ALLOWED_RESOURCE = 'not_allowed_resource';

    const
        ALLOW = 1,
        DENY = 0;

    public function login(array $user): void;

    public function logout(): void;

    public function isLogin(): bool;

    /**
     * @return bool|string
     */
    public function hasAllowed(string $class, string $method, ?string $module = null);

    public function setDefaultAction(int $action): void;
}