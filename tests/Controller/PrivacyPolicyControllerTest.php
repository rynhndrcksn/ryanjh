<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class PrivacyPolicyControllerTest extends WebTestCase
{
    private const string ENDPOINT = '/privacy-policy';

    public function testPrivacyPolicyPageResponseIsSuccessful(): void
    {
        $kernelBrowser = self::createClient();
        $kernelBrowser->request(Request::METHOD_GET, self::ENDPOINT);

        self::assertResponseIsSuccessful();
    }

    public function testPrivacyPolicyPageContainsCorrectTitle(): void
    {
        $kernelBrowser = self::createClient();
        $kernelBrowser->request(Request::METHOD_GET, self::ENDPOINT);

        self::assertAnySelectorTextContains('.title', 'Privacy Policy');
    }
}
