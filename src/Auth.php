<?php

namespace YG\Phalcon\Auth;

use Exception;
use Phalcon\Di\Injectable;

class Auth extends Injectable implements AuthInterface
{
    private string
        $authName,
        $cacheDir;

    private bool $useAllowedIpAddress;

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

    public function isAllowedIpAddress(): bool
    {
        return !$this->useAllowedIpAddress or
                $this->authDataService->isAllowedIpAddress($this->request->getClientAddress());
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
        $this->cacheDir = $options['cacheDir'] ?? sys_get_temp_dir() . '/';
        $this->useAllowedIpAddress = $options['useAllowedIpAddress'] ?? false;
    }

    private function saveIpAddressOfLastLogin(): void
    {
        $file = $this->cacheDir . 'session/auth_' . $this->user->id;
        file_put_contents($file, $this->request->getClientAddress());
    }

    private function getIpAddressOfLastLogin(): string
    {
        $file = $this->cacheDir . 'session/auth_' . $this->user->id;
        return file_exists($file) ? file_get_contents($file) : '';
    }
}