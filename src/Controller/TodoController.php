<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TodoRepository;
use App\Repository\SubToDoRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Todo; 
use App\Entity\SubToDo; 
use Doctrine\ORM\EntityManagerInterface;

class TodoController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'index')]
    public function todos(TodoRepository $toDoRepository): Response
    {
        // Aufgaben aus der Datenbank abrufen
        $todos = $toDoRepository->findAll();
        return $this->render('index.html.twig', ['todos' => $todos]);
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
    #[Route('/add_todo', name: 'add_todo')]
    public function addTodo(Request $request): Response
    {
        $newToDo = $request->request->get('new_todo');

        if ($newToDo) {
            // Füge die neue Aufgabe zur Datenbank hinzu
            $todo = new Todo();
            $todo->setName($newToDo);
            $this->entityManager->persist($todo);
            $this->entityManager->flush();
        }

        // Leite die Benutzer zur Todo-Liste zurück
        return $this->redirectToRoute('index');
    }

    #[Route('/api/todos', name: 'api_todo_list', methods: ['GET'])]
    public function getTodos(TodoRepository $toDoRepository): JsonResponse
    {
        $toDoList = $toDoRepository->findAll();

        $toDoArray = [];
        foreach ($toDoList as $toDo) {
            $toDoArray[] = [
                'id' => $toDo->getId(),
                'name' => $toDo->getName(),
                'description' => $toDo->getDescription(),
                'prio' => $toDo->getPrio(),
                'deadline' => $toDo->getDeadline() ? $toDo->getDeadline()->format('Y-m-d H:i:s') : null,
            ];
        }

        return $this->json($toDoArray);
    }
    
    /**
     * Liest eine Aufgabe anhand ihrer ID aus der Datenbank.
     *
     * @Route("/api/todos/{id}", name="api_todo_get", methods={"GET"})
     *
     * @param int $id Die ID der Aufgabe, die gelesen werden soll.
     * @param TodoRepository $todoRepository Das Repository für Aufgaben.
     *
     * @return JsonResponse Die Antwort mit der gelesenen Aufgabe oder einem Fehlercode.
     */
    public function getTodo(int $id, TodoRepository $toDoRepository): JsonResponse
    {
        $todo = $toDoRepository->find($id);

        if (!$todo) {
            // Rückgabe einer Fehlerantwort, wenn die Aufgabe nicht gefunden wurde
            return $this->json(['message' => 'Aufgabe nicht gefunden'], 404);
        }

        // Aufgabe in JSON umwandeln und zurückgeben
        return $this->json([
            'name' => $todo->getName(),
            'description' => $todo->getDescription(),
            'prio' => $todo->getPrio(),
            'deadline' => $todo->getDeadline(),
        ]);
    }

    /**
     * Aktualisiert eine Aufgabe anhand ihrer ID und Benutzereingaben.
     *
     * @Route("/api/todos/{id}", name="api_todo_update", methods={"PUT"})
     *
     * @param int $id Die ID der Aufgabe, die aktualisiert werden soll.
     * @param Request $request Der Symfony-Request mit den Benutzereingaben.
     * @param TodoRepository $todoRepository Das Repository für Aufgaben.
     *
     * @return Response Die Antwort mit der aktualisierten Aufgabe oder einem Fehlercode.
     */
    public function updateTodo(int $id, Request $request, TodoRepository $toDoRepository)
    {
        $todo = $toDoRepository->find($id);

        if (!$todo) {
            // Rückgabe einer Fehlerantwort, wenn die Aufgabe nicht gefunden wurde
            return $this->json(['message' => 'Aufgabe nicht gefunden'], 404);
        }

        $requestData = json_decode($request->getContent(), true);

        // Die aktualisierten Daten aus $requestData verwenden, um die Aufgabe zu aktualisieren
        $todo->setName($requestData['updateName']);
        $todo->setDescription($requestData['updateDescription']);
        $todo->setPrio($requestData['updatePrio']);
        if (!empty($requestData['updateDeadline'])) {
            $deadline = new \DateTime($requestData['updateDeadline']);
            $todo->setDeadline($deadline);
        }

        $this->entityManager->persist($todo);
        $this->entityManager->flush();

        // Rückgabe der aktualisierten Aufgabe und einem 200 Statuscode
        return new Response('Updated.', 200);
    }


    /**
     * Löscht eine Aufgabe anhand ihrer ID.
     * @Route("/api/todos/{id}", name="api_todo_delete", methods={"DELETE", "POST"})
     *
     * @param int $id Die ID der Aufgabe, die gelöscht werden soll.
     * @param TodoRepository $todoRepository Das Repository für Aufgaben.
     *
     * @return JsonResponse Die Antwort mit einer Bestätigung oder einem Fehlercode.
     */
    public function deleteTodo(int $id, TodoRepository $toDoRepository, SubToDoRepository $subToDoRepository): JsonResponse
    {
        $todo = $toDoRepository->find($id);

        if (!$todo) {
            return $this->json(['message' => 'Aufgabe nicht gefunden'], 404);
        }

        $subTodos = $todo->getSubToDos();
        if (count($subTodos) > 0) {
            foreach ($subTodos as $subTodo) {
                $subTodoId = $subTodo->getId();
                $subTodoEntity = $subToDoRepository->find($subTodoId);
                
                if ($subTodoEntity instanceof SubToDo) {
                    $this->entityManager->remove($subTodoEntity);
                } else if ($subTodoEntity == NULL){
                    $errorMessage = sprintf('Keine SubTodo gefunden: ' . $subTodoId . ' TodoId=' . $id);
                    return $this->json($errorMessage, 400); // Bad Request
                } else {
                    // Hier sollten Sie die Löschung des Subtodos überspringen und eine Fehlermeldung ausgeben
                    $errorMessage = sprintf('Todo als SubTodo gefunden.' . $subTodoId);
                    return $this->json($errorMessage, 400); // Bad Request
                }
            }
        }
        $this->entityManager->remove($todo);
        $this->entityManager->flush();

        // Rückgabe einer Erfolgsmeldung und einem 204 Statuscode (No Content)
        return $this->json(['message' => 'Aufgabe erfolgreich gelöscht'], 204);
    }
}
