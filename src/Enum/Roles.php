<?php

declare(strict_types=1);

namespace App\Enum;

enum Roles: string
{
    case Admin = 'ROLE_ADMIN';
    case User  = 'ROLE_USER';
}
