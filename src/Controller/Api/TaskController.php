<?php

namespace App\Controller\Api;

use App\Entity\Task;
use App\Entity\Team;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Service\JwtHandler;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * @Route("/api")
 */
class TaskController extends AbstractController
{
    private $serializer;
    private $jwtHandler;

    public function __construct(SerializerInterface $serializer, JwtHandler $jwtHandler)
    {
        $this->serializer = $serializer;
        $this->jwtHandler = $jwtHandler;
    }

    // ============= Route CRUD ==================================================

    // ! TO DELETE (actively used on front)
    /**
     * @Route("/tasks", name="app_api_task_list" , methods={"GET"})
     */
    public function list(TaskRepository $TaskRepository): JsonResponse
    {
        $tasks = $TaskRepository->findAll();
        return $this->json($tasks, JsonResponse::HTTP_OK,[],["groups" => "taskList"]);
    }

    // ! TO DELETE (actively used front)
    /**
     * @Route("/tasks/{id}", name="app_api_task_get", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function getTaskCrud(Task $task = null): JsonResponse
    {
        if (!$task) {return $this->json(["error" => "Task does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        return $this->json([$task], JsonResponse::HTTP_OK, [],["groups" => "task"] );
    }

    // ============= End of route CRUD ============================================

    /**
     * @Route("/tasks/{id}/accept", name="app_api_task_accept", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function acceptTask(EntityManagerInterface $entityManager, Task $task = null): JsonResponse
    {
        if(!$task){return $this->json(["error" => "Task does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        $connectedUser = $this->jwtHandler->getUser();
        if($task->getAssignedTo() !== $connectedUser)
            {return $this->json(["error" => "Can't accept a Task that is not assigned to you."], JsonResponse::HTTP_FORBIDDEN, [], []);}
        if($task->isRejected())
            {return $this->json("Cannot accept rejected Task.", JsonResponse::HTTP_BAD_REQUEST,[],[]);}
        if($task->getDatetimeAccepted() !== null)
            {return $this->json("Task is already accepted.", JsonResponse::HTTP_OK,[],[]);}

        $task->setDatetimeAccepted(new DateTimeImmutable());
        $entityManager->persist($task);
        $entityManager->flush();
        return $this->json("Task accepted.", JsonResponse::HTTP_OK,[],[]);
    }

    /**
     * @Route("/tasks/{id}/reject", name="app_api_task_reject", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function rejectTask(EntityManagerInterface $entityManager, Task $task = null): JsonResponse
    {
        if(!$task){return $this->json(["error" => "Task does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        $connectedUser = $this->jwtHandler->getUser();
        if($task->getAssignedTo() !== $connectedUser)
            {return $this->json(["error" => "Can't reject a Task that is not assigned to you."], JsonResponse::HTTP_FORBIDDEN, [], []);}
        if($task->isRejected())
            {return $this->json("Task is already rejected.", JsonResponse::HTTP_OK,[],[]);}
        if($task->getDatetimeAccepted() !== null)
            {return $this->json("Cannot reject accepted Task.", JsonResponse::HTTP_BAD_REQUEST,[],[]);}

        $task->setRejected(true);
        $entityManager->persist($task);
        $entityManager->flush();
        return $this->json("Task rejected.", JsonResponse::HTTP_OK,[],[]);
    }

    /**
     * @Route("/tasks/{id}/complete", name="app_api_task_complete", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function completeTask(EntityManagerInterface $entityManager, Task $task = null): JsonResponse
    {
        if(!$task){return $this->json(["error" => "Task does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        $connectedUser = $this->jwtHandler->getUser();
        if($task->getAssignedTo() !== $connectedUser)
            {return $this->json(["error" => "Can't complete a Task that is not assigned to you."], JsonResponse::HTTP_FORBIDDEN, [], []);}
        if($task->isRejected())
            {return $this->json("Cannot complete Task already rejected.", JsonResponse::HTTP_BAD_REQUEST,[],[]);}
        if($task->getDatetimeAccepted() === null)
            {return $this->json("Cannot complete Task without accepting it first.", JsonResponse::HTTP_BAD_REQUEST,[],[]);}
        if($task->getDatetimeCompleted() !== null)
            {return $this->json("Task is already completed.", JsonResponse::HTTP_OK,[],[]);}

        $task->setDatetimeCompleted(new DateTimeImmutable());
        $entityManager->persist($task);
        $entityManager->flush();
        return $this->json("Task completed.", JsonResponse::HTTP_OK,[],[]);
    }

    /**
     * @Route("/teams/{id}/tasks", name="app_api_team-task_list", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function listTask(Team $team = null, Request $request, TaskRepository $taskRepository, UserRepository $userRepository): JsonResponse
    {
        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        $filters = ["team" => $team];

        if($request->query->has('creator')){
            $creator = $userRepository->find(intval($request->query->get('creator'), 10));
            if($creator === null){return $this->json(["error" => "Creator does not exist"], JsonResponse::HTTP_NOT_FOUND);}
            $filters += array("createdBy" => $creator);
        }
        if($request->query->has('assigned')){
            $assigned = $userRepository->find(intval($request->query->get('assigned'), 10));
            if(!$assigned){return $this->json(["error" => "assigned does not exist"], JsonResponse::HTTP_NOT_FOUND);}
            $filters += array("assignedTo" => $assigned);
        }
        if($request->query->has('status')){
            $status = $request->query->get('status');
            $filters += array("status" => $status);
        }

        $tasks = $taskRepository->findBy($filters);

        return $this->json($tasks, JsonResponse::HTTP_OK, [],["groups" => "taskList"] );
    }

    /**
     * @Route("/teams/{id}/tasks/{task}", name="app_api_team-task_get", methods={"GET"}, requirements={"id"="\d+", "task"="\d+"})
     */
    public function getTask(Team $team = null, Task $task = null): JsonResponse
    {
        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        if (!$task) {return $this->json(["error" => "Task does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        return $this->json($task, JsonResponse::HTTP_OK, [],["groups" => "task"] );
    }

    /**
     * @Route("/teams/{id}/tasks", name="app_api_team-task_create", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function createTask(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager, Team $team = null): JsonResponse
    {
        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        $connectedUser = $this->jwtHandler->getUser();
        if (!$connectedUser->isMemberOf($team, true, ["GIVER"])){return $this->json(["error" => "Can't create a task unless you are a member with the GIVER role in this team."], JsonResponse::HTTP_FORBIDDEN, [], []);}

        try {
            $task = $this->serializer->deserialize(
                $request->getContent(),
                Task::class,
                'json',
                [
                    "groups" => "taskCreate"
            ]);
        } catch (NotEncodableValueException $e) {
            return $this->json(["error" => "Invalid json"], JsonResponse::HTTP_BAD_REQUEST);
        }

        if($task->getAssignedTo() && !$task->getAssignedTo()->isMemberOf($team, true, ["TASKER"])){
            return $this->json(["error" => "Task assignement is only possible to member of this team with TASKER role."], JsonResponse::HTTP_BAD_REQUEST);
        }

        $team->addTask($task);
        $connectedUser->addCreatedTask($task);

        $errors = $validator->validate($task, null, ["Default", "create"]);
        if (count($errors) > 0) {
            $dataErrors = [];
            foreach($errors as $error){
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json($dataErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($task);
        $entityManager->flush();


        return $this->json([$task], JsonResponse::HTTP_CREATED,[
            "Location" => $this->generateUrl("app_api_task_get", ["id" => $task->getId()])
        ], ["groups" => "task"]);
    }

    /**
     * @Route("/teams/{id}/tasks/{task}", name="app_api_team-task_update", methods={"PUT"}, requirements={"id"="\d+", "task"="\d+"})
     */
    public function updateTask(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager, Team $team = null, Task $task = null): JsonResponse
    {
        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        if (!$task) {return $this->json(["error" => "Task does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        $connectedUser = $this->jwtHandler->getUser();
        if($task->getCreatedBy() !== $connectedUser){return $this->json(["error" => "Can't modify a task that is not created by you."], JsonResponse::HTTP_FORBIDDEN, [], []);}

        $oldAssignedTo = $task->getAssignedTo();

        try {
            $this->serializer->deserialize(
                $request->getContent(),
                Task::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $task,
                    "groups" => "taskUpdate"
                ]);
        } catch (NotEncodableValueException $e) {
            return $this->json(["error" => "Invalid json"], JsonResponse::HTTP_BAD_REQUEST);
        }

        if($task->getAssignedTo() && !$task->getAssignedTo()->isMemberOf($team, true, ["TASKER"])){
            return $this->json(["error" => "Task assignement is only possible to member of this team with TASKER role."], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Reset status if the task is reassigned
        if($task->getAssignedTo() !== $oldAssignedTo){
            if($task->getDatetimeCompleted() !== null){return $this->json(["error" => "Can't reassign an already completed task."], JsonResponse::HTTP_BAD_REQUEST);}
            if($task->getDatetimeAccepted() !== null){$task->setDatetimeAccepted(null);}
            if($task->isRejected()){$task->setRejected(false);}
        }

        $task->setUpdatedAt(new DateTimeImmutable());
        
        $errors = $validator->validate($task, null, ["Default", "update"]);
        if (count($errors) > 0) {
            $dataErrors = [];
            foreach($errors as $error){
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json($dataErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($task);
        $entityManager->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT,[], []);
    }

    /**
     * @Route("/teams/{id}/tasks/{task}", name="app_api_team-task_delete", methods={"DELETE"}, requirements={"id"="\d+", "task"="\d+"})
     */
    public function deleteTask(TaskRepository $taskRepository, Task $task = null): JsonResponse
    {
        if (!$task){return $this->json(["error" => "Task does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        
        $connectedUser = $this->jwtHandler->getUser();
        if($task->getCreatedBy() !== $connectedUser){return $this->json(["error" => "Can't delete a task that is not created by you."], JsonResponse::HTTP_FORBIDDEN, [], []);}

        $taskRepository->remove($task, true);
        return $this->json("Task deleted successfully.", JsonResponse::HTTP_OK, [],[] );
    }
}