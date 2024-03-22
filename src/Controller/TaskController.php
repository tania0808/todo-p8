<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task_list')]
    #[IsGranted('ROLE_USER')]
    public function index(TaskRepository $taskRepository, #[MapQueryParameter] bool $isDone = false): RedirectResponse|Response
    {
        return $this->render('task/list.html.twig', ['tasks' => $taskRepository->findAllByIsDone($isDone)]);
    }

    #[Route('/tasks/create', name: 'task_create')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $em): RedirectResponse|Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $task->setAuthor($this->getUser());

            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a bien été ajoutée.');

            return $this->redirectToRoute('task_list', ['isDone' => $task->isDone()]);
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit')]
    #[IsGranted('edit', 'task')]
    public function edit(Task $task, Request $request, EntityManagerInterface $em): RedirectResponse|Response
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list', ['isDone' => $task->isDone()]);
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/toggle', name: 'task_toggle')]
    #[IsGranted('toggle', 'task')]
    public function toggleTask(Task $task, EntityManagerInterface $em): RedirectResponse
    {
        $task->toggle(!$task->isDone());
        $em->flush();

        $message = $task->isDone() ? 'faite' : 'non terminée';
        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme ' . $message, $task->getTitle()));

        return $this->redirectToRoute('task_list', ['isDone' => $task->isDone()]);
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete')]
    #[IsGranted('delete', 'task')]
    public function deleteTask(Task $task, EntityManagerInterface $em): RedirectResponse
    {
        $em->remove($task);
        $em->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list', ['isDone' => false]);
    }
}
