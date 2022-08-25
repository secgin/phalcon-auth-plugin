<?php

namespace YG\Phalcon\Auth;

interface AuthDataServiceInterface
{
    public function getPermissions(): array;
}