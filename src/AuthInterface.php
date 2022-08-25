<?php

namespace YG\Phalcon\Auth;

/**
 * @property Permission         $authPermission
 * @property ResourcePermission $authResourcePermission
 * @property AuthSession        $authSession
 *
 * @property User               $user
 */
interface AuthInterface
{
    public function login(array $user): void;

    public function logout(): void;

    public function isLogin(): bool;

    public function isPublicResource(string $class, string $method): bool;

    public function hasAllowed(string $permissionCode, int $permissionLevel, ?string $module = null): bool;

    public function hasAllowedResource(string $class, string $method, ?string $module = null): bool;
}