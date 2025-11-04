<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class HomeControllerTest extends WebTestCase
{
    public function testHomePageResponseIsSuccessful(): void
    {
        $kernelBrowser = self::createClient();
        $kernelBrowser->request(Request::METHOD_GET, '/');

        self::assertResponseIsSuccessful();
    }

    public function testHomePageContainsCorrectTitle(): void
    {
        $kernelBrowser = self::createClient();
        $kernelBrowser->request(Request::METHOD_GET, '/');

        self::assertAnySelectorTextContains('.title', 'Home');
    }
}
