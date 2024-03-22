<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Factory\TaskFactory;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Tests\DatabaseWebTest;
use Doctrine\ORM\EntityManagerInterface;

class TaskControllerTest extends DatabaseWebTest
{
    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        parent::setUp();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    public function testGetCreateTaskPage()
    {
        $this->loginUser();
        $crawler = $this->client->request('GET', '/tasks/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('button', 'Ajouter');
    }

    public function testCreateTask()
    {
        // Given
        $this->loginUser();
        $crawler = $this->client->request('GET', '/tasks/create');


        // When
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'Emails',
            'task[content]' => 'Envoyer les emails',
        ]);
        $this->client->submit($form);

        // Then
        $this->assertResponseRedirects('/tasks?isDone=0');
        $this->client->followRedirect();
        $this->assertAnySelectorTextContains('div.alert.alert-success', 'La tâche a bien été ajoutée.');
    }


    public function testTaskEdit()
    {
        // Given
        $task = $this->createTask();

        // When
        $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('Modifier', [
            'task[title]' => 'Emails',
            'task[content]' => 'Envoyer les emails',
        ]);

        // Then
        $taskRepository = $this->client->getContainer()->get(TaskRepository::class);
        $editedTask = $taskRepository->find($task->getId());

        $this->assertEquals('Emails', $editedTask->getTitle());
        $this->assertEquals('Envoyer les emails', $editedTask->getContent());
    }

    public function testToggleTask()
    {
        // Given
        $task = $this->createTask();

        // When
        $this->client->request('GET', '/tasks/' . $task->getId() . '/toggle');

        // Then
        $taskRepository = $this->client->getContainer()->get(TaskRepository::class);
        $toggledTask = $taskRepository->find($task->getId());

        $this->assertResponseRedirects('/tasks?isDone=1');
        $this->assertNotNull($toggledTask);
        $this->assertTrue($toggledTask->isDone());
    }

    public function testDeleteTask()
    {
        // Given
        $task = $this->createTask();
        $id = $task->getId();

        // When
        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');

        // Then
        $taskRepository = $this->client->getContainer()->get(TaskRepository::class);
        $deletedTask = $taskRepository->find($id);
        $this->assertNull($deletedTask);
        $this->assertResponseRedirects('/tasks?isDone=0');
    }

    public function createTask(): Task
    {
        $user = $this->loginUser();
        $task = new Task();
        $task->setTitle('Phone');
        $task->setContent('Appeler le client');
        $task->setAuthor($user);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }

    public function testGuardAgainstEditWithoutPermission()
    {
        // Given
        $task = TaskFactory::new()->createOne();
        $this->loginUser();

        // When
        $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');

        // Then
        $this->assertResponseStatusCodeSame(403);
        $this->assertAnySelectorTextContains('p', 'Accès refusé.');
    }

    public function testEveryoneCanToggleAnonymousTask()
    {
        // Given
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $anonymousUser = $userRepository->findOneByUserName('anonymous');
        $task = TaskFactory::new()->createOne(['author' => $anonymousUser ]);
        $this->loginUser();

        // When
        $this->client->request('GET', '/tasks/' . $task->getId() . '/toggle');

        // Then
        $this->assertResponseRedirects('/tasks?isDone=' . ($task->isDone() ? '0' : '1'));
    }

    public function testGuardAgainstDeleteWithoutPermission()
    {
        // Given
        $task = TaskFactory::new()->createOne();
        $this->loginUser();

        // When
        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');

        // Then
        $this->assertResponseStatusCodeSame(403);
        $this->assertAnySelectorTextContains('p', 'Accès refusé.');
    }

    public function testAdminCanDeleteAnonymousTask()
    {
        // Given
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $anonymousUser = $userRepository->findOneByUserName('anonymous');
        $task = new Task();
        $task->setTitle('Phone');
        $task->setContent('Appeler le client');
        $task->setAuthor($anonymousUser);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->loginAdminUser();

        // When
        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');

        // Then
        $this->assertResponseRedirects('/tasks?isDone=0');
    }

    public function testUserCanNotDeleteAnonymousTask()
    {
        // Given
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $anonymousUser = $userRepository->findOneByUserName('anonymous');
        $task = new Task();
        $task->setTitle('Phone');
        $task->setContent('Appeler le client');
        $task->setAuthor($anonymousUser);
        $this->entityManager->persist($task);
        $this->entityManager->flush();
        $this->loginUser();

        // When
        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');

        // Then
        $this->assertResponseStatusCodeSame(403);
        $this->assertAnySelectorTextContains('p', 'Accès refusé.');
    }

    public function testAccessNotExistingTask()
    {
        // Given
        $this->loginUser();

        // When
        $this->client->request('GET', '/tasks/999/edit');

        // Then
        $this->assertResponseStatusCodeSame(404);
    }
}