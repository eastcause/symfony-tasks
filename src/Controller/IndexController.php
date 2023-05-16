<?php

namespace App\Controller;

use App\Entity\TaskRow;
use App\Form\CreateTaskRowType;
use App\Service\TaskRowService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{

    private EntityManagerInterface $entityManager;
    private TaskRowService $taskRowService;

    public function __construct(EntityManagerInterface $entityManager, TaskRowService $taskRowService)
    {
        $this->entityManager = $entityManager;
        $this->taskRowService = $taskRowService;
    }

    #[Route('/', name: 'app_index')]
    public function index(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(CreateTaskRowType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $description = $form->get('description')->getData();

            $taskRow = new TaskRow();
            $taskRow->setUser($this->getUser());
            $taskRow->setDescription($description);

            $this->entityManager->persist($taskRow);
            $this->entityManager->flush();

            $this->addFlash('success', "Projekt o nazwie: " . $description . ' został stworzony!');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('index/index.html.twig', [
            'form_task_row' => $form->createView(),
            'task_rows' => $this->taskRowService->getRows($this->getUser())
        ]);
    }

    #[Route('/delete/{id}', name: 'app_delete_row')]
    public function delete(int $id) : Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $taskRow = $this->taskRowService->getTaskRow($this->getUser(), $id);

        if ($taskRow) {
            if ($this->taskRowService->removeTaskRow($taskRow)) {
                $this->addFlash('success', 'Projekt został usunięty!');
            } else {
                $this->addFlash('error', 'Nie udało się usunąć tego projektu!');
            }
        } else {
            $this->addFlash('error', 'Nie udało się usunąć tego projektu!');
        }
        return $this->redirectToRoute('app_index');
    }
}
