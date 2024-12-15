<?php

namespace App\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class TaskController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/tasks', methods: ['GET'])]
    public function getTasks(): JsonResponse
    {
        $tasks = $this->entityManager->getRepository(Task::class)->findAll();

        $data = array_map(function (Task $task) {
            return [
                'id' => $task->getId(),
                'productName' => $task->getProductName(),
                'price' => $task->getPrice(),
                'description' => $task->getDescription(),
                'imageLink' => $task->getImageLink(),
            ];
        }, $tasks);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/api/tasks', methods: ['POST'])]
    public function createTask(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $task = new Task();
        $task->setProductName($data['productName']);
        $task->setPrice($data['price']);
        $task->setDescription($data['description'] ?? null);
        $task->setImageLink($data['imageLink']);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Task created!'], JsonResponse::HTTP_CREATED);
    }
}
