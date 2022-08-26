<?php

namespace YG\Phalcon\Auth;

interface AuthDataServiceInterface
{
    public function getPermissions(): array;

    public function isAllowedIpAddress(string $ipAddress): bool;
}