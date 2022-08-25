<?php

namespace YG\Phalcon\Auth;

abstract class PermissionLevelType
{
    const
        NoneOrHave = 0,
        CRUD = 1;
}