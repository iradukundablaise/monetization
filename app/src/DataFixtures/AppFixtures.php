<?php

namespace App\DataFixtures;

use App\Entity\Report;
use App\Entity\User;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher){
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');
        $user = new User();

        $user->setFirstname($faker->firstName);
        $user->setLastname($faker->lastName);
        $user->setEmail($faker->email);
        $user->setUsername($faker->userName);
        $user->setRoles([User::USER_ROLE, User::ADMIN_ROLE]);

        $password = $this->hasher->hashPassword($user, '@Password123');

        $user->setPassword($password);
        // save user
        $manager->persist($user);
        $todayDate = Carbon::now();

        for($i=0; $i<50; $i++){
            $report = new Report();

            $randomInt = rand(1, 7);
            $pageView = rand(100, 10000);
            $uniquePageView = rand(100, $pageView);

            $report->setPageviews($pageView);
            $report->setUniquePageviews($uniquePageView);
            $report->setUser($user);
            $report->setCreatedAt($todayDate->toDate());
            $report->setUpdatedAt($todayDate->add($randomInt, 'day')->toDate());

            $manager->persist($report);
        }

        $manager->flush();
    }
}
