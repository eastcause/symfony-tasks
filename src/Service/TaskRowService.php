<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\TaskRow;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\TaskRowRepository;
use Doctrine\ORM\EntityManagerInterface;

class TaskRowService
{
    private EntityManagerInterface $entityManager;
    private TaskRowRepository $taskRowRepository;
    private TaskRepository $taskRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->taskRowRepository = $entityManager->getRepository(TaskRow::class);
        $this->taskRepository = $entityManager->getRepository(Task::class);
    }

    public function getRows(?User $user) : ?array {
        return $this->taskRowRepository->findBy([
            'user' => $user
        ]);
    }

    public function getTaskRow(?User $user, ?int $id) : ?TaskRow {
        return $this->taskRowRepository->findOneBy([
            'user' => $user,
            'id' => $id
        ]);
    }

    public function getTaskRowByDescription(?User $user, string $desc) : ?TaskRow {
        return $this->taskRowRepository->findOneBy([
            'user' => $user,
            'description' => $desc
        ]);
    }

    public function getTasks(?int $taskRowId) : ? array {
        $tasks = $this->taskRepository->findBy([
            'task_row' => $taskRowId
        ]);
        return array(
            '0' => $this->sortTask($tasks, 0),
            '1' => $this->sortTask($tasks, 1),
            '2' => $this->sortTask($tasks, 2)
        );
    }

    public function sortTask(array $tasks, int $status) : array {
        $collection = array();
        /**
         * @var Task $task
         */
        foreach ($tasks as $task) {
            if ($task->getStatus() == $status) {
                $collection[] = $task;
            }
        }
        return $collection;
    }

    public function removeTaskRow(TaskRow $taskRow) : bool{
        try {
            foreach ($taskRow->getTasks() as $task) {
                $this->entityManager->remove($task);
            }
            $this->entityManager->remove($taskRow);
            $this->entityManager->flush();
        }catch (\Exception $exception){
            return false;
        }
        return true;
    }

    public function getTask(int $id) :?Task {
        return $this->taskRepository->findOneBy(['id' => $id]);
    }

    public function createTask(TaskRow $taskRow, int $status, string $text) : bool {
        try {
            $task = new Task();
            $task->setTaskRow($taskRow);
            $task->setDescription($text);
            $task->setStatus($status);
            $task->setCreatedAt(new \DateTime());

            $this->entityManager->persist($task);
            $this->entityManager->flush();
        }catch (\Exception $exception){
            return false;
        }
        return true;
    }

    public function removeTask(Task $task): bool {
        try {
            $this->entityManager->remove($task);
            $this->entityManager->flush();
        }catch (\Exception $e){
            return false;
        }
        return true;
    }

    public function moveTask(Task $task, int $status) : int {
        try {
            if ($status === $task->getStatus()) {
                return 1;
            }
            if ($status < 0 || $status > 2) {
                return 2;
            }
            $task->setStatus($status);

            $this->entityManager->persist($task);
            $this->entityManager->flush();
        }catch (\Exception $e){
            return 0;
        }
        return 3;
    }

}