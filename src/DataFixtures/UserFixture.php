<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $adminPassword = $_ENV['ADMIN_PASSWORD'] ?? 'devAdminPass';
        $userPassword  = $_ENV['USER_PASSWORD']  ?? 'devUserPass';

       
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setFullName($faker->name());
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, $adminPassword)
        );
        $manager->persist($admin);

        
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setFullName($faker->name());
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $userPassword)
        );
        $manager->persist($user);

        $manager->flush();
    }
}
