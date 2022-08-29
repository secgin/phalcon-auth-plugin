<?php

namespace YG\Phalcon\Auth;

use Phalcon\Di\Injectable;

class ResourcePermission extends Injectable
{
    /**
     *  Metodun yada sınıfın herkese açık olup olmadığını kontrol eder.
     */
    public function isPublic(string $class, string $method, int $defaultAction): bool
    {
        if ($defaultAction == AuthInterface::DENY)
            return $this->hasPublic($class, $method);

        return !$this->hasPrivate($class, $method);
    }

    /**
     * Metodın ek açıklamalarından yetki kodunu ve yetki düzeyini alır. [permissionCode, permissionLevel] formatında
     * dizi döner.
     *
     * @return string[]
     */
    public function getPermissionValues(string $class, string $method): array
    {
        $annotations = $this->annotations->get($class);
        $methodAnnotations = $this->annotations->getMethod($class, $method);

        $permissionLevel = 0;
        $permissionCode = null;
        if ($methodAnnotations->has('Private'))
        {
            $privateAnnotations = $methodAnnotations->get('Private');

            if ($privateAnnotations->hasArgument(0))
                $permissionLevel = $privateAnnotations->getArgument(0);

            if ($privateAnnotations->hasArgument(1))
                $permissionCode = $privateAnnotations->getArgument(1);
        }

        if ($permissionCode == null)
        {
            $classAnnotations = $annotations->getClassAnnotations();
            if ($classAnnotations != null and $classAnnotations->has('Private'))
            {
                $classPrivateAnnotation = $classAnnotations->get('Private');

                if ($classPrivateAnnotation->hasArgument(0))
                    $permissionCode = $classPrivateAnnotation->getArgument(0);
            }
        }

        return [$permissionCode, $permissionLevel];
    }

    /**
     * Bir metodun tüm ip adreslerine izin verilip verilmediğini kontrol eder.
     */
    public function hasIpAllowed(string $class, string $method): bool
    {
        $methodAnnotations = $this->annotations->getMethod($class, $method);
        if ($methodAnnotations->has('IpAllowed'))
            return true;

        $annotations = $this->annotations->get($class);
        $classAnnotations = $annotations->getClassAnnotations();

        return $classAnnotations and $methodAnnotations->has('IpAllowed');
    }

    private function hasPrivate(string $class, string $method): bool
    {
        $methodAnnotations = $this->annotations->getMethod($class, $method);
        if ($methodAnnotations->has('Private'))
            return true;

        $annotations = $this->annotations->get($class);
        $classAnnotations = $annotations->getClassAnnotations();

        return $classAnnotations and $methodAnnotations->has('Private');
    }

    private function hasPublic(string $class, string $method): bool
    {
        $methodAnnotations = $this->annotations->getMethod($class, $method);
        if ($methodAnnotations->has('Public'))
            return true;

        $annotations = $this->annotations->get($class);
        $classAnnotations = $annotations->getClassAnnotations();

        return $classAnnotations and $methodAnnotations->has('Public');
    }
}