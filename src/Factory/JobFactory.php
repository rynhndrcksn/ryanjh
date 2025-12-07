<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Job;
use App\Enum\EmploymentType;
use Random\RandomException;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Job>
 */
final class JobFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    #[\Override]
    public static function class(): string
    {
        return Job::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @throws \DateMalformedStringException if creating the $endDate goes wrong
     * @throws RandomException               if creating the $endDate goes wrong
     */
    #[\Override]
    protected function defaults(): array
    {
        $startDate = \DateTimeImmutable::createFromMutable(self::faker()->dateTime());
        $endDate   = $startDate->modify('+'.random_int(1, 730).' days');

        return [
            'title'          => self::faker()->jobTitle(),
            'employer'       => self::faker()->company(),
            'location'       => self::faker()->city().', Washington, United States',
            'description'    => self::faker()->paragraphs(3, true),
            'employmentType' => self::faker()->randomElement(EmploymentType::cases()),
            'startDate'      => $startDate,
            'endDate'        => $endDate,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this// ->afterInstantiate(function(Job $job): void {})
        ;
    }
}
