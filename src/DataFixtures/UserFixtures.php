<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\UserStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 1; $i <= 20; $i++) {
            $user = new User();

            $user->setName($faker->name());
            $user->setEmail($faker->unique()->safeEmail());

            // Randomize status
            $user->setStatus(
                rand(0, 1) ? UserStatus::ACTIVE : UserStatus::INACTIVE
            );

            /**
             * Assignment logic:
             * - Some users within 7 days
             * - Some within 15 days
             * - Some older
             */
            $chance = rand(1, 100);

            if ($chance <= 30) {
                $daysBack = rand(0, 7);
            } elseif ($chance <= 60) {
                $daysBack = rand(8, 15);
            } else {
                $daysBack = rand(16, 60);
            }

            $user->setCreatedAt(
                new \DateTimeImmutable("-{$daysBack} days")
            );

            $manager->persist($user);
        }

        $manager->flush();
    }
}
