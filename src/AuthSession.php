<?php

namespace YG\Phalcon\Auth;

use Phalcon\Di\Injectable;

class AuthSession extends Injectable
{
    public function has(string $authName): bool
    {
        return $this->session->has($authName);
    }

    public function getData(string $authName): array
    {
        return $this->session->get($authName, []);
    }

    public function registerSession(string $authName, array $data): void
    {
        $this->session->set($authName, $data);
    }

    public function removeSession(string $authName): void
    {
        $this->session->remove($authName);
    }
}