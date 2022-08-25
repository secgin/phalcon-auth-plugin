<?php

namespace YG\Phalcon\Auth;

use Phalcon\Di\Injectable;

class ResourcePermission extends Injectable
{
    /**
     *  Özniteliklerden bir sınıfın yada metodun herkese açık(Public) olup olmadığını kontrol eder.
     *
     * @param string $class
     * @param string $method
     *
     * @return bool
     */
    public function isPublic(string $class, string $method): bool
    {
        $methodAnnotations = $this->annotations->getMethod($class, $method);
        if ($methodAnnotations->has('Public'))
            return true;

        $annotations = $this->annotations->get($class);
        $classAnnotations = $annotations->getClassAnnotations();

        return $classAnnotations and !$methodAnnotations->has('Private') and $classAnnotations->has('Public');
    }

    /**
     * Özniteliklerden yetki kodunu ve yetki düzeyini alır. [permissionCode, permissionLevel] formatında dizi döner.
     *
     * @param string $class
     * @param string $method
     *
     * @return array
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
}