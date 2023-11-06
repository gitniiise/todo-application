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

    /**
     * Konstruktor für die SubToDoController-Klasse.
     *
     * @param EntityManagerInterface $entityManager Der Entity Manager, der für die Datenbankinteraktion verwendet wird.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/sub/to/do", name="app_sub_to_do")
     * 
     * Zeigt die Startseite oder Willkommensseite der Anwendung an.
     * 
     * Diese Methode rendert die Startseite oder Willkommensseite der Anwendung und gibt sie als HTTP-Antwort zurück.
     * 
     * @return Response Eine Symfony Response, die die Startseite der Anwendung enthält.
     */
    public function index(): Response
    {
        return $this->render('sub_to_do/index.html.twig', [
            'controller_name' => 'SubToDoController',
        ]);
    }

    /**
     * @Route("/api/subtodos", name="api_subtodo_list", methods={"GET"})
     *
     * Diese Methode ermöglicht das Aufrufen alles Unteraufgaben.
     *
     * @param SubToDoRepository $subToDoRepository
     * @return JsonResponse
     */
    public function getSubToDosList(SubToDoRepository $subToDoRepository): JsonResponse
    {
        $subToDoList = $subToDoRepository->findAll();

        $subToDoArray = [];
        foreach ($subToDoList as $subToDo) {
            $subToDoArray[] = [
                'id' => $subToDo->getId(),
                'name' => $subToDo->getName(),
                'description' => $subToDo->getDescription(),
                'prio' => $subToDo->getPrio(),
                'deadline' => $subToDo->getDeadline() ? $subToDo->getDeadline()->format('Y-m-d H:i:s') : null,
            ];
        }

        return $this->json($subToDoArray);
    }

    /**
     * @Route("/add_subtodo", name="add_subtodo", methods={"POST"})
     * 
     * Diese Methode ermöglicht das Hinzufügen neuer Unteraufgaben zur Todo-Liste.
     * 
     * @param Request $request Das Symfony Request-Objekt, das die Benutzereingabe enthält.
     * @param TodoRepository $toDoRepository Ein Repository für die Todo-Liste.
     * 
     * @return Response Eine Symfony Response, die den Benutzer nach dem Hinzufügen zur Todo-Liste zurückleitet.
     */
    public function addSubToDo(Request $request, TodoRepository $toDoRepository): Response
    {
        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);

        if ($data) {
            $newSubToDo = $data['new-subtodo-name'];
            $parentId = (int) $data['parentId'];
        }
        if ($newSubToDo && $parentId) {
            // Hole die ToDo-Instanz anhand der parentID
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
     * 
     * Diese Methode wird verwendet, um Unteraufgaben abzurufen, die zu einer bestimmten übergeordneten Aufgabe gehören.
     * 
     * @param int $parentTodoId Die ID der übergeordneten Aufgabe, für die Unteraufgaben abgerufen werden.
     * @param SubToDoRepository $subToDoRepository Ein Repository für die Unteraufgaben.
     * 
     * @return JsonResponse Eine JSON-Antwort, die die abgerufenen Unteraufgaben der Aufgabe enthält.
     */
    public function getSubToDos(int $parentTodoId, SubToDoRepository $subToDoRepository): JsonResponse
    {
        $subToDos = $subToDoRepository->findBy(['parentTodo' => $parentTodoId]);

        // Konvertiere die Unteraufgaben in ein Array, um sie als JSON zu senden
        $subToDoArray = [];
        foreach ($subToDos as $subToDo) {
            $subToDoArray[] = [
                'id' => $subToDo->getId(),
                'name' => $subToDo->getName(),
                'description' => $subToDo->getDescription(),
                'prio' => $subToDo->getPrio(),
                'deadline' => $subToDo->getDeadline(),
            ];
        }
        return new JsonResponse($subToDoArray);
    }

    /**
     * Liest eine Unteraufgabe anhand ihrer ID aus der Datenbank.
     *
     * @Route("/api/subtodos/{id}", name="api_subtodo_get", methods={"GET"})
     *
     * @param int $id Die ID der Unteraufgabe, die gelesen werden soll.
     * @param SubToDoRepository $subToDoRepository Das Repository für Unteraufgabe.
     *
     * @return JsonResponse Die Antwort mit der gelesenen Unteraufgabe oder einem Fehlercode.
     */
    public function getSubToDo(int $id, SubToDoRepository $subToDoRepository): JsonResponse
    {
        $subtodo = $subToDoRepository->find($id);

        if (!$subtodo) {
            // Rückgabe einer Fehlerantwort, wenn die Unteraufgabe nicht gefunden wurde
            return $this->json(['message' => 'Unteraufgabe nicht gefunden'], 404);
        }

        // Unteraufgabe in JSON umwandeln und zurückgeben
        return $this->json([
            'name' => $subtodo->getName(),
            'description' => $subtodo->getDescription(),
            'prio' => $subtodo->getPrio(),
            'deadline' => $subtodo->getDeadline(),
        ]);
    }

    /**
     * Aktualisiert eine Unteraufgabe anhand ihrer ID und Benutzereingaben.
     *
     * @Route("/api/subtodos/{id}", name="api_subtodo_update", methods={"POST"})
     *
     * @param int $id Die ID der Unteraufgabe, die aktualisiert werden soll.
     * @param Request $request Der Symfony-Request mit den Benutzereingaben.
     * @param SubToDoRepository $subtodoRepository Das Repository für Unteraufgaben.
     *
     * @return Response Die Antwort mit der aktualisierten Unteraufgabe oder einem Fehlercode.
     */
    public function updateSubTodo(int $id, Request $request, SubToDoRepository $subtodoRepository): Response
    {
        $subtodo = $subtodoRepository->find($id);

        if (!$subtodo) {
            // Rückgabe einer Fehlerantwort, wenn die Unteraufgabe nicht gefunden wurde
            return $this->json(['message' => 'Unteraufgabe nicht gefunden'], 404);
        }

        $requestData = json_decode($request->getContent(), true);
        // Die aktualisierten Daten aus $requestData verwenden, um die Unteraufgabe zu aktualisieren
        $subtodo->setName($requestData['updateName']);
        $subtodo->setDescription($requestData['updateDescription']);
        $subtodo->setPrio($requestData['updatePrio']);
        if (!empty($requestData['updateDeadline'])) {
            $deadline = new \DateTime($requestData['updateDeadline']);
            $subtodo->setDeadline($deadline);
        }

        $this->entityManager->persist($subtodo);
        $this->entityManager->flush();

        return new Response('Updated.', 200);
    }


    /**
     * Löscht eine Unteraufgabe anhand ihrer ID.
     * @Route("/api/subtodos/{id}", name="api_subtodo_delete", methods={"DELETE", "POST"})
     *
     * @param int $id Die ID der Unteraufgabe, die gelöscht werden soll.
     * @param SubToDoRepository $subToDoRepository Das Repository für Unteraufgaben.
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
