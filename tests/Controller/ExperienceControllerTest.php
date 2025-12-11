<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\ResetDatabase;

final class ExperienceControllerTest extends WebTestCase
{
    use ResetDatabase;

    private const string ENDPOINT = '/experience';

    public function testExperiencePageResponseIsSuccessful(): void
    {
        $kernelBrowser = self::createClient();
        $kernelBrowser->request(Request::METHOD_GET, self::ENDPOINT);

        self::assertResponseIsSuccessful();
    }

    public function testExperiencePageContainsCorrectTitle(): void
    {
        $kernelBrowser = self::createClient();
        $kernelBrowser->request(Request::METHOD_GET, self::ENDPOINT);

        self::assertAnySelectorTextContains('.title', 'Experience');
    }
}
