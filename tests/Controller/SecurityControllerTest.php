<?php

namespace App\Tests\Controller;

use App\Tests\DatabaseWebTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends DatabaseWebTest
{
    public function testLogin(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'user0',
            '_password' => 'admin123',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/');

        $this->client->followRedirect();
        $this->assertAnySelectorTextContains('a.btn-danger', 'Se déconnecter');
    }
}
