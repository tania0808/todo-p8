<?php

namespace App\Tests\Controller;

use App\Tests\DatabaseWebTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends DatabaseWebTest
{

    public function testBadCredentials()
    {
        // When
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'user0',
            '_password' => 'admin',
        ]);

        $this->client->submit($form);

        // Then
        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertAnySelectorTextContains('div.alert.alert-danger', 'Mot de passe ou identifiant invalide.');
    }
    public function testLogin(): void
    {
        // When
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'user0',
            '_password' => 'admin123',
        ]);
        $this->client->submit($form);

        // Then
        $this->assertResponseRedirects('/');

        $this->client->followRedirect();
        $this->assertAnySelectorTextContains('a.btn-danger', 'Se déconnecter');
    }
}
