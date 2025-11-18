<?php

declare(strict_types=1);

namespace App\Enum;

enum EmploymentType: string
{
    case FullTime = 'Full-time';
    case PartTime = 'Part-time';
    case Contract = 'Contract';
}
