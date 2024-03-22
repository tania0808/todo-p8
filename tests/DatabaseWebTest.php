<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DatabaseWebTest extends WebTestCase
{
    protected EntityManager $em;
    protected KernelBrowser $client;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {

        $this->client = static::createClient();
        $this->client->disableReboot();

        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
        $this->em->beginTransaction();
        $this->em->getConnection()->setAutoCommit(false);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        if($this->em->getConnection()->isTransactionActive()) {
            $this->em->rollback();
        }
    }

    public function loginUser(): User
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByRole('ROLE_USER');
        $this->client->loginUser($testUser);
        return $testUser;
    }
    public function loginAdminUser(): User
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByRole('ROLE_ADMIN');
        $this->client->loginUser($testUser);
        return $testUser;
    }
}