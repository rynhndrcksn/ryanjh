<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Twig\Attribute\AsTwigFilter;

readonly class SanitizeExtension
{
    public function __construct(
        private HtmlSanitizerInterface $htmlSanitizer,
    ) {
    }

    #[AsTwigFilter('sanitize', isSafe: ['html'])]
    public function sanitize(?string $html): string
    {
        if ($html === null) {
            return '';
        }

        return $this->htmlSanitizer->sanitize($html);
    }
}
