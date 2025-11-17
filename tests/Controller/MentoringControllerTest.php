<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class MentoringControllerTest extends WebTestCase
{
    private const string ENDPOINT = '/mentoring';

    public function testMentoringPageResponseIsSuccessful(): void
    {
        $kernelBrowser = self::createClient();
        $kernelBrowser->request(Request::METHOD_GET, self::ENDPOINT);

        self::assertResponseIsSuccessful();
    }

    public function testMentoringPageContainsCorrectTitle(): void
    {
        $kernelBrowser = self::createClient();
        $kernelBrowser->request(Request::METHOD_GET, self::ENDPOINT);

        self::assertAnySelectorTextContains('.title', 'Mentoring');
    }
}
