<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Enum\UserStatus;
use Faker\Factory;


class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {


        for ($i = 1; $i <= 20; $i++) {
            $user = new User();


            // Create a Faker instance
            $faker = Factory::create();

            $user->setName($faker->name());                
            $user->setEmail($faker->unique()->safeEmail()); 

            // Randomize status
            $user->setStatus(rand(0, 1) ? UserStatus::ACTIVE : UserStatus::INACTIVE);

            /** * Logic to satisfy the assignment:
             * We need at least some in 7 days, some in 15.
             * We will use a random chance to place users in different "buckets".
             */
            $chance = rand(1, 100);

            if ($chance <= 30) {
                // 30% chance: Created within the last 7 days
                $daysBack = rand(0, 7);
            } elseif ($chance <= 60) {
                // 30% chance: Created between 8 and 15 days ago
                $daysBack = rand(8, 15);
            } else {
                // 40% chance: Older than 15 days (up to 2 months)
                $daysBack = rand(16, 60);
            }

            $date = new \DateTimeImmutable("-$daysBack days");
            $user->setCreatedAt($date);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
