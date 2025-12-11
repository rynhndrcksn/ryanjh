<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Enum\Roles;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::createOne([
            'email'         => 'admin@example.com',
            'plainPassword' => 'tada',
            'roles'         => [
                Roles::Admin->value,
                Roles::User->value,
            ],
        ]);

        $manager->flush();
    }
}
