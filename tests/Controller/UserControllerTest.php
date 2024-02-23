<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function loginUser(): void
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByRole('ROLE_USER');
        $this->client->loginUser($testUser);
    }
    public function loginAdminUser(): void
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByRole('ROLE_ADMIN');
        $this->client->loginUser($testUser);
    }

    public function testCreateUserWithAdminRole(): void
    {
        $this->loginAdminUser();

        $crawler = $this->client->request('GET', '/users/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un utilisateur');
    }

    public function testCreateUserWithoutAdminRole()
    {
        $this->loginUser();
        $crawler = $this->client->request('GET', '/users/create');

        $this->assertResponseStatusCodeSame(403);
        $this->assertSelectorTextContains('p', 'Accès refusé.');
    }

    public function testLogIn(): void
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user0@gmail.com');

        $this->client->loginUser($testUser);
        $crawler = $this->client->request('GET', '/tasks/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('button', 'Ajouter');
    }

    public function addUser()
    {

    }
}
