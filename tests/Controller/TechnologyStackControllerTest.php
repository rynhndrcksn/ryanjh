<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class TechnologyStackControllerTest extends WebTestCase
{
    private const string ENDPOINT = '/technology-stack';

    public function testTechnologyStackPageResponseIsSuccessful(): void
    {
        $kernelBrowser = self::createClient();
        $kernelBrowser->request(Request::METHOD_GET, self::ENDPOINT);

        self::assertResponseIsSuccessful();
    }

    public function testTechnologyStackPageContainsCorrectTitle(): void
    {
        $kernelBrowser = self::createClient();
        $kernelBrowser->request(Request::METHOD_GET, self::ENDPOINT);

        self::assertAnySelectorTextContains('.title', 'Technology Stack');
    }
}
