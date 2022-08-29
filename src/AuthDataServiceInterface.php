<?php

namespace YG\Phalcon\Auth;

interface AuthDataServiceInterface
{
    public function getPermissionLevel(string $permissionCode, ?string $moduleName = null): ?int;

    public function isIpAddressAllowed(string $ipAddress): bool;
}