<?php

declare(strict_types=1);

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum EmploymentType: string implements TranslatableInterface
{
    case FullTime = 'Full-time';
    case PartTime = 'Part-time';
    case Contract = 'Contract';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return match ($this) {
            self::FullTime => self::FullTime->value,
            self::PartTime => self::PartTime->value,
            self::Contract => self::Contract->value,
        };
    }
}
