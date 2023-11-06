<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TodoRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SubToDoRepository;
use App\Entity\SubToDo; 

class SubToDoController extends AbstractController implements ServiceSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/sub/to/do', name: 'app_sub_to_do')]
    public function index(): Response
    {
        return $this->render('sub_to_do/index.html.twig', [
            'controller_name' => 'SubToDoController',
        ]);
    }

    /**
     * @Route("/add_todo", name="add_todo", methods: ['POST'])
     * 
     * Diese Methode ermöglicht das Hinzufügen neuer Aufgaben zur Todo-Liste.
     * 
     * @param Request $request Das Symfony Request-Objekt, das die Benutzereingabe enthält.
     * 
     * @return Response Eine Symfony Response, die den Benutzer nach dem Hinzufügen zur Todo-Liste zurückleitet.
     */
   #[Route('/add_subtodo', name: 'add_subtodo')]
    public function addSubToDo(Request $request, TodoRepository $toDoRepository): Response
    {
        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);

        if ($data) {
            $newSubToDo = $data['new-subtodo-name'];
            $parentId = (int) $data['parentId'];
        }
        if ($newSubToDo && $parentId) {
            // Holen Sie die ToDo-Instanz anhand der parentID
            $parentTodo = $toDoRepository->find($parentId);

            if (!$parentTodo) {
                return new Response('Parent ToDo not found.', 404);
            }

            // Füge die neue Unteraufgabe zur Datenbank hinzu
            $subToDo = new SubToDo();
            $subToDo->setName($newSubToDo);
            $subToDo->setParentToDo($parentTodo);
            $this->entityManager->persist($subToDo);
            $this->entityManager->flush(); 
            $subTodoId = $subToDo->getId();

            return $this->json(['subtodoId' => $subTodoId], 200);
        } elseif (!$parentId) {
            return $this->json(['parentId' => $parentId], 500);
        }
        return new Response('Subtodo not added.', 404);
    }

    /**
     * @Route("/api/subtodos/{parentTodoId}", name="api_subtodos", methods={"GET"})
     */
    #[Route('/get_subtodos', name: 'get_subtodos')]
    public function getsubtodos(int $parentTodoId, SubToDoRepository $subToDoRepository): JsonResponse
    {
        $subToDos = $subToDoRepository->findBy(['parentTodo' => $parentTodoId]);

        // Konvertiere die Unteraufgaben in ein Array, um sie als JSON zu senden
        $subToDoArray = [];
        foreach ($subToDos as $subToDo) {
            $subToDoArray[] = [
                'id' => $subToDo->getId(),
                'name' => $subToDo->getName(),
            ];
        }

        return new JsonResponse($subToDoArray);
    }
    public static function getSubscribedServices(): array
    {
        return [
            'App\Repository\SubToDoRepository' => SubToDoRepository::class,
            // Weitere Abhängigkeiten, falls vorhanden
        ];
    }

    /**
     * Aktualisiert eine Unteraufgabe anhand ihrer ID und Benutzereingaben.
     *
     * @Route("/api/subtodos/{id}", name="api_subtodo_update", methods={"PUT"})
     *
     * @param int $id Die ID der Unteraufgabe, die aktualisiert werden soll.
     * @param Request $request Der Symfony-Request mit den Benutzereingaben.
     * @param SubToDoRepository $subtodoRepository Das Repository für Aufgaben.
     *
     * @return Response Die Antwort mit der aktualisierten Unteraufgabe oder einem Fehlercode.
     */
    public function updateSubTodo(int $id, Request $request, SubToDoRepository $subtodoRepository)
    {
        $subtodo = $subtodoRepository->find($id);

        if (!$subtodo) {
            // Rückgabe einer Fehlerantwort, wenn die Aufgabe nicht gefunden wurde
            return $this->json(['message' => 'Aufgabe nicht gefunden'], 404);
        }

        $requestData = json_decode($request->getContent(), true);
        // Die aktualisierten Daten aus $requestData verwenden, um die Aufgabe zu aktualisieren
        $subtodo->setName($requestData['updateName']);
        $subtodo->setDescription($requestData['updateDescription']);
        $subtodo->setPrio($requestData['updatePrio']);
        if (!empty($requestData['updateDeadline'])) {
            $deadline = new \DateTime($requestData['updateDeadline']);
            $subtodo->setDeadline($deadline);
        }

        $this->entityManager->persist($subtodo);
        $this->entityManager->flush();

        // Rückgabe der aktualisierten Aufgabe und einem 200 Statuscode
        return new Response('Updated.', 200);
    }


    /**
     * Löscht eine Unteraufgabe anhand ihrer ID.
     * @Route("/api/subtodos/{id}", name="api_subtodo_delete", methods={"DELETE", "POST"})
     *
     * @param int $id Die ID der Aufgabe, die gelöscht werden soll.
     * @param SubToDoRepository $subToDoRepository Das Repository für Aufgaben.
     *
     * @return JsonResponse Die Antwort mit einer Bestätigung oder einem Fehlercode.
     */
    public function deleteSubTodo(int $id, SubToDoRepository $subToDoRepository): JsonResponse
    {
        $subtodo = $subToDoRepository->find($id);
        if (!$subtodo && !($subtodo instanceof SubToDo)) {
            return $this->json(['message' => 'Unteraufgabe nicht gefunden'], 404);
        }

        $this->entityManager->remove($subtodo);
        $this->entityManager->flush();

        // Rückgabe einer Erfolgsmeldung und einem 204 Statuscode (No Content)
        return $this->json(['message' => 'Unteraufgabe erfolgreich gelöscht'], 204);
    }
}
