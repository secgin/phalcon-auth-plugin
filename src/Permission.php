<?php

namespace YG\Phalcon\Auth;

use Phalcon\Di\Injectable;

/**
 * @property AuthDataServiceInterface $authDataService
 */
class Permission extends Injectable
{
    private array $permissions = [];

    public function hasAllowed(string $code, int $level, ?string $module = null): bool
    {
        if (count($this->permissions) === 0)
            $this->permissions = $this->authDataService->getPermissions();

        $permissionLevel = $module != ''
            ? $this->permissions[$module][$code] ?? null
            : $this->permissions[$code] ?? null;

        return $permissionLevel and $permissionLevel >= $level;
    }
}