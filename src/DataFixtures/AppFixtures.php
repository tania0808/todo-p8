<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }
    public function load(ObjectManager $manager): void
    {
        $anonymousUser = new User();
        $anonymousUser->setUsername('anonymous');
        $anonymousUser->setEmail('anonymous@gmail.com');
        $anonymousUser->setPassword(
            $this->passwordHasher->hashPassword($anonymousUser, 'anonymous123')
        );
        $anonymousUser->setRoles(['ROLE_USER']);
        $manager->persist($anonymousUser);

        for ($i = 0; $i < 10; $i++) {
            $task = new Task();
            $task->setTitle('task'.$i);
            $task->setContent('task'.$i);
            $task->setIsDone(rand(0, 1));
            $task->setAuthor($anonymousUser);

            $manager->persist($task);
        }

        $manager->flush();
    }
}
