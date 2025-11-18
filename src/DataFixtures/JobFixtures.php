<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\JobFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class JobFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        JobFactory::createMany(5);
        // Mock a "current" job.
        JobFactory::createOne([
            'endDate' => null,
        ]);

        $manager->flush();
    }
}
