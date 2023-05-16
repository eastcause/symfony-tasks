<?php

namespace App\Controller;

use App\Form\CreateTaskFormType;
use App\Service\TaskRowService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{

    private TaskRowService $taskRowService;

    public function __construct(TaskRowService $taskRowService)
    {
        $this->taskRowService = $taskRowService;
    }

    #[Route('/task/{id}', name: 'app_task')]
    public function index(int $id, Request $request): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Musisz się zalogować!');
            return $this->redirectToRoute('app_login');
        }

        $taskRow = $this->taskRowService->getTaskRow($this->getUser(), $id);
        if (!$taskRow) {
            $this->addFlash('error', 'Nie odnaleziono takiego projektu!');
            return $this->redirectToRoute('app_index');
        }
        if ($taskRow->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Ten projekt nie należy do Ciebie!');
            return $this->redirectToRoute('app_index');
        }

        $tasks = $this->taskRowService->getTasks($id);
        $new = $tasks[0];
        $in = $tasks[1];
        $end = $tasks[2];

        $createForm = $this->createForm(CreateTaskFormType::class);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $task = $this->taskRowService->getTaskRow($this->getUser(), $id);
            if (!$task) {
                return $this->redirectToRoute('app_index');
            }

            $this->taskRowService->createTask($task, $createForm->get('status')->getData(), $createForm->get('text')->getData());

            return $this->redirectToRoute('app_task', ['id' => $id]);
        }

        return $this->render('task/index.html.twig', [
            'controller_name' => 'TaskController',
            'new' => $new,
            'work' => $in,
            'end' => $end,
            'createForm' => $createForm->createView(),
            'project' => $taskRow->getDescription()
        ]);
    }

    #[Route('/task/delete/{id}', name: 'app_task_delete')]
    public function taskDelete(int $id): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Musisz się zalogować!');
            return $this->redirectToRoute('app_login');
        }

        $task = $this->taskRowService->getTask($id);
        if (!$task) {
            $this->addFlash('error', 'Nie odnaleziono taska o takim id!');
            return $this->redirectToRoute('app_index');
        }

        $rowId = $task->getTaskRow()->getId();
        if ($task->getTaskRow()->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Ten projekt nie należy do Ciebie!');
            return $this->redirectToRoute('app_index');
        }

        $this->taskRowService->removeTask($task);
        return $this->redirectToRoute('app_task', ['id' => $rowId]);
    }

    const TaskMove = '/task/move/';

    #[Route('/task/move/{id}/{status}', name: 'app_task_move')]
    public function move(int $id, int $status): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Musisz się zalogować!');
            return $this->redirectToRoute('app_login');
        }
        $task = $this->taskRowService->getTask($id);
        if (!$task) {
            $this->addFlash('error', 'Nie odnaleziono taska o takim id!');
            return $this->redirectToRoute('app_index');
        }

        $rowId = $task->getTaskRow()->getId();
        if ($task->getTaskRow()->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Ten projekt nie należy do Ciebie!');
            return $this->redirectToRoute('app_index');
        }

        $moveTask = $this->taskRowService->moveTask($task, $status);

        if ($moveTask !== 3) {
            $this->addFlash('error', 'Nie udało się przenieść tego zadania!');
            return $this->redirectToRoute('app_index');
        }


        return $this->redirectToRoute('app_task', [
            'id' => $rowId,
        ]);
    }
}
