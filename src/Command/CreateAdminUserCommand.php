<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Enum\Roles;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Create a new admin user and save them to the DB',
)]
readonly class CreateAdminUserCommand
{
    public function __construct(
        private EntityManagerInterface      $entityManager,
        private UserRepository              $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $email = $symfonyStyle->ask('What is the email of the user?', null, function ($email) {
            if (empty($email)) {
                throw new \RuntimeException('Email cannot be empty');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Invalid email format');
            }

            $existingUser = $this->userRepository->findOneBy(['email' => $email]);
            if ($existingUser instanceof User) {
                throw new \RuntimeException('A user with this email already exists');
            }

            return $email;
        });

        // Ask for password
        $password = $symfonyStyle->askHidden('What is the password?', function ($password) {
            if (empty($password)) {
                throw new \RuntimeException('Password cannot be empty');
            }

            return $password;
        });

        $user = new User();
        $user->setEmail($email);
        $user->setRoles([Roles::User->value, Roles::Admin->value]);
        $hashedPassword = $this->userPasswordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $symfonyStyle->success(sprintf('Admin user "%s" has been created successfully!', $email));

        return Command::SUCCESS;
    }
}
