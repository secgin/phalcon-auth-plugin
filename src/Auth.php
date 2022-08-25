<?php

namespace YG\Phalcon\Auth;

use Exception;
use Phalcon\Di\Injectable;

class Auth extends Injectable implements AuthInterface
{
    private string $authName;

    private User $user;

    public function __construct(array $options = [])
    {
        $this->setOptions($options);

        $this->assignUser($this->authSession->getData($this->authName));
    }

    /**
     * @throws Exception
     */
    public function login(array $user): void
    {
        if ($this->isLogin())
            return;

        if (empty($user['id']) || empty($user['username']))
            throw new LoginRequiredFieldException('User id and username is empty');

        $this->assignUser($user);
        $this->authSession->registerSession($this->authName, $user);
        $this->saveIpAddressOfLastLogin();
    }

    public function logout(): void
    {
        $this->authSession->removeSession($this->authName);
    }

    public function isLogin(): bool
    {
        $isLogin = $this->authSession->has($this->authName);

        if ($isLogin and $this->getIpAddressOfLastLogin() != $this->request->getClientAddress())
        {
            $this->logout();
            return false;
        }

        return $isLogin;
    }

    public function isPublicResource(string $class, string $method): bool
    {
        return $this->authResourcePermission->isPublic($class, $method);
    }

    public function hasAllowed(string $permissionCode, int $permissionLevel, string $module = null): bool
    {
        return $this->authPermission->hasAllowed($permissionCode, $permissionLevel, $module);
    }

    public function hasAllowedResource(string $class, string $method, ?string $module = null): bool
    {
        if ($this->isPublicResource($class, $method))
            return true;

        if (!$this->isLogin())
            return false;

        list($permissionCode, $permissionLevel) = $this->authResourcePermission->getPermissionValues($class, $method);

        return $permissionCode == null or $this->hasAllowed($permissionCode, $permissionLevel, $module);
    }

    public function __get($propertyName)
    {
        if ($propertyName == 'user')
            return $this->user;

        return parent::__get($propertyName);
    }

    private function assignUser(array $data): void
    {
        $this->user = new User($data);
    }

    private function setOptions(array $options)
    {
        $this->authName = $options['authName'] ?? 'auth';
    }

    private function saveIpAddressOfLastLogin(): void
    {
        $file = sys_get_temp_dir() . '/auth_' . $this->user->id . 'txt';
        file_put_contents($file, $this->request->getClientAddress());
    }

    private function getIpAddressOfLastLogin(): string
    {
        $file = sys_get_temp_dir() . '/auth_' . $this->user->id . 'txt';
        return file_exists($file) ? file_get_contents($file) : '';
    }
}