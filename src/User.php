<?php

namespace YG\Phalcon\Auth;

/**
 * @property string $id
 * @property string $username
 * @property string $email
 * @property string $name
 */
class User
{
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __get($name)
    {
        $methodName = 'get' . ucfirst($name);
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        return $this->data[$name] ?? '';
    }
}