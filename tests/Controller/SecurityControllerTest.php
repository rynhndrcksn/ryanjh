<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Test\ResetDatabase;

final class SecurityControllerTest extends WebTestCase
{
    use ResetDatabase;

    private KernelBrowser $kernelBrowser;

    protected function setUp(): void
    {
        $this->kernelBrowser = self::createClient();
        $container           = self::getContainer();
        $em                  = $container->get('doctrine.orm.entity_manager');
        $userRepository      = $em->getRepository(User::class);

        // Remove any existing users from the test database
        foreach ($userRepository->findAll() as $user) {
            $em->remove($user);
        }

        $em->flush();

        // Create a User fixture
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get('security.user_password_hasher');

        $user = new User()->setEmail('email@example.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));

        $em->persist($user);
        $em->flush();
    }

    public function testLogin(): void
    {
        // Denied - Can't login with invalid email address.
        $this->kernelBrowser->request(Request::METHOD_GET, '/login');
        self::assertResponseIsSuccessful();

        $this->kernelBrowser->submitForm('Sign in', [
            '_username' => 'doesNotExist@example.com',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/login');
        $this->kernelBrowser->followRedirect();

        // Ensure we do not reveal if the user exists or not.
        self::assertSelectorTextContains('.danger', 'Invalid credentials.');

        // Denied - Can't login with invalid password.
        $this->kernelBrowser->request(Request::METHOD_GET, '/login');
        self::assertResponseIsSuccessful();

        $this->kernelBrowser->submitForm('Sign in', [
            '_username' => 'email@example.com',
            '_password' => 'bad-password',
        ]);

        self::assertResponseRedirects('/login');
        $this->kernelBrowser->followRedirect();

        // Ensure we do not reveal the user exists but the password is wrong.
        self::assertSelectorTextContains('.danger', 'Invalid credentials.');

        // Success - Login with valid credentials is allowed.
        $this->kernelBrowser->submitForm('Sign in', [
            '_username' => 'email@example.com',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/admin');
        $this->kernelBrowser->followRedirect();

        self::assertSelectorNotExists('.danger');
    }
}
