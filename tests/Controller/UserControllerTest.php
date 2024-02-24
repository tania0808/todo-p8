<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\DatabaseWebTest;

class UserControllerTest extends DatabaseWebTest
{
    public function getUser(): User
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        return $userRepository->findOneByEmail('user0@gmail.com');
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
        $this->assertAnySelectorTextSame('p', 'Accès refusé.');
    }

    public function testLogIn(): void
    {
        $testUser = $this->getUser();

        $this->client->loginUser($testUser);
        $crawler = $this->client->request('GET', '/tasks/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('button', 'Ajouter');
    }


    public function testGetUsersList()
    {
        $this->loginAdminUser();
        $crawler = $this->client->request('GET', '/users');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
    }
    public function testAddUser()
    {
        $this->loginAdminUser();
        $crawler = $this->client->request('GET', '/users/create');
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'user_test',
            'user[plain_password][first]' => 'admin123',
            'user[plain_password][second]' => 'admin123',
            'user[email]' => 'user_test@gmail.com',
            'user[roles]' => 'ROLE_USER',
        ]);
        $this->client->submit($form);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', 'L\'utilisateur a bien été ajouté.');
    }

    public function testAddExistingUser()
    {
        $this->loginAdminUser();
        $crawler = $this->client->request('GET', '/users/create');
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'user0',
            'user[plain_password][first]' => 'admin123',
            'user[plain_password][second]' => 'admin123',
            'user[email]' => 'user_test@gmail.com',
            'user[roles]' => 'ROLE_USER',
        ]);
        $this->client->submit($form);
        $this->assertSelectorTextContains('div.invalid-feedback', 'Ce nom d\'utilisateur est déjà utilisé.');
    }

    public function testEditUser()
    {
        $this->loginAdminUser();
        $testUser = $this->getUser();
        $id = $testUser->getId();
        $crawler = $this->client->request('GET', "/users/$id/edit");

        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => 'user_new',
            'user[plain_password][first]' => 'admin12',
            'user[plain_password][second]' => 'admin12',
            'user[email]' => 'user_new@gmail.com',
            'user[roles]' => 'ROLE_ADMIN',
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();
        $this->assertAnySelectorTextContains('div.alert.alert-success', 'Superbe ! L\'utilisateur a bien été modifié');
    }

}
