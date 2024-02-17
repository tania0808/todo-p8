<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const USER_REFERENCE = 'user_';

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i <= 15; $i++) {
            $user = new User();
            $user->setUsername('user'.$i);
            $user->setEmail('user' . $i . '@gmail.com');
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'admin123')
            );

            $this->addReference(self::USER_REFERENCE.$i, $user);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
