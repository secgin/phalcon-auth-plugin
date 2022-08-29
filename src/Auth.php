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

    private int $defaultAction;

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

    public function hasAllowed(string $class, string $method, ?string $module = null)
    {
        $isPublic = $this->authResourcePermission->isPublic($class, $method, $this->defaultAction);

        if ($isPublic)
            return true;

        if ($this->isLogin() === false)
            return AuthInterface::NOT_LOGGED_IN;

        if ($this->isIpAddressAllowed($class, $method) === false)
            return AuthInterface::NOT_ALLOWED_IP_ADDRESS;
        
        if ($this->isResourceAllowed($class, $method, $module) === false)
            return AuthInterface::NOT_ALLOWED_RESOURCE;

        return true;
    }

    public function setDefaultAction(int $action): void
    {
        $this->defaultAction = $action == 1 ? self::ALLOW : self::DENY;
    }


    private function setOptions(array $options)
    {
        $this->authName = $options['authName'] ?? 'auth';
        $this->cacheDir = $options['cacheDir'] ?? sys_get_temp_dir() . '/';
        $this->useAllowedIpAddress = $options['useAllowedIpAddress'] ?? false;
        $this->setDefaultAction((int)($options['defaultAction'] ?? AuthInterface::DENY));
    }

    private function assignUser(array $data): void
    {
        $this->user = new User($data);
    }

    private function isIpAddressAllowed(string $class, string $method): bool
    {
        return
            $this->useAllowedIpAddress === false ||
            $this->authResourcePermission->hasIpAllowed($class, $method) ||
            $this->authPermission->isIpAddressAllowed($this->request->getClientAddress());
    }

    private function isResourceAllowed(string $class, string $method, ?string $module = null): bool
    {
        list($permissionCode, $permissionLevel) = $this->authResourcePermission->getPermissionValues($class, $method);

        return
            $permissionCode == null ||
            $this->authPermission->isAllowed($permissionCode, $permissionLevel, $module);
    }

    private function saveIpAddressOfLastLogin(): void
    {
        $file = $this->cacheDir . 'auth_' . $this->user->id;
        file_put_contents($file, $this->request->getClientAddress());
    }

    private function getIpAddressOfLastLogin(): string
    {
        $file = $this->cacheDir . 'auth_' . $this->user->id;
        return file_exists($file) ? file_get_contents($file) : '';
    }


    public function __get($propertyName)
    {
        if ($propertyName == 'user')
            return $this->user;

        return parent::__get($propertyName);
    }
}