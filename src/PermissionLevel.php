<?php

namespace YG\Phalcon\Auth;

final class PermissionLevel
{
    const
        None = 0,
        Have = 1,
        Read = 3,
        Create = 5,
        Update = 7,
        Delete = 9;
}
