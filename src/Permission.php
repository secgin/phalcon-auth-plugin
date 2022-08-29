<?php

namespace YG\Phalcon\Auth;

use Phalcon\Di\Injectable;

/**
 * @property AuthDataServiceInterface $authDataService
 */
class Permission extends Injectable
{
    public function isAllowed(string $code, int $level, ?string $module = null): bool
    {
        $permissionLevel = $this->authDataService->getPermissionLevel($code, $module);

        return $permissionLevel and $permissionLevel >= $level;
    }

    public function isIpAddressAllowed(string $ipAddress): bool
    {
        return $this->authDataService->isIpAddressAllowed($ipAddress);
    }
}