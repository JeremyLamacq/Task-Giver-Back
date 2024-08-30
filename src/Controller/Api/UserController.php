<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\BelongsToRepository;
use App\Repository\UserRepository;
use App\Service\JwtHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use App\Service\PasswordValidation;

/**
 * @Route("/api")
 */
class UserController extends AbstractController
{
    private $serializer;
    private $passwordHasher;
    private $jwtHandler;

    public function __construct(SerializerInterface $serializer, UserPasswordHasherInterface $passwordHasher, JwtHandler $jwtHandler)
    {
        $this->serializer = $serializer;
        $this->passwordHasher = $passwordHasher;
        $this->jwtHandler = $jwtHandler;
    }
    
    // ============= Route CRUD ==================================================

    // ! TO DELETE (Actively used)
    /**
     * @Route("/users", name="app_api_user_list" , methods={"GET"})
     */
    public function list(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findall();
        return $this->json($users, JsonResponse::HTTP_OK, [],["groups" => "userList"] );
    }

    /**
     * @Route("/users/{id}", name="app_api_user_get", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function getUser(User $user = null): JsonResponse
    {
        if (!$user) {return $this->json(["error" => "User does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        return $this->json([$user], JsonResponse::HTTP_OK, [],["groups" => "user"] );
    }

    // ! TO DELETE (actively used)
    /**
     * @Route("/users/{id}", name="app_api_user_update", methods={"PUT"}, requirements={"id"="\d+"})
     */
    public function update(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager, User $user = null): JsonResponse
    {
        if (!$user) {return $this->json(["error" => "User does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        try {
            $updatedUser = $this->serializer->deserialize(
                $request->getContent(),
                User::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $user,
                    "groups" => "userUpdate"
            ]);
        } catch (NotEncodableValueException $e) {
            return $this->json(["error" => "Invalid json"], JsonResponse::HTTP_BAD_REQUEST);
        }

        $content = $request->toArray();
        
        if(isset($content["password"])){
            $plainPassword = $content["password"];
            $user->setPassword($this->passwordHasher->hashPassword($updatedUser, $plainPassword));
        }
        $user->setUpdatedAt(new \DateTimeImmutable());

        $errors = $validator->validate($updatedUser, null, ["Default", "update"]);
        if (count($errors) > 0) {
            $dataErrors = [];
            foreach($errors as $error){
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json($dataErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($user);
        $entityManager->flush();
        return $this->json(null, JsonResponse::HTTP_NO_CONTENT,[], []);
    }

    // ! TO DELETE (actively used)
    /**
     * @Route("/users/{id}", name="app_api_user_delete", methods={"DELETE"}, requirements={"id"="\d+"})
     */
    public function delete(UserRepository $userRepository, User $user = null): JsonResponse
    {
        if (!$user) {return $this->json(["error" => "User does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        $userRepository->remove($user, true);
        return $this->json("User deleted successfully.", JsonResponse::HTTP_OK, [],[] );
    }

    // ============= End of route CRUD =============================================

    /**
     * @Route("/users/search", name="app_api_user_search", methods={"POST"})
     */
    public function searchByEmail(Request $request, UserRepository $userRepository): JsonResponse
    {
        $email = $request->toArray()["email"];
        if(!$email){return $this->json(["error" => "Invalid json : 'email' attribute required"], JsonResponse::HTTP_BAD_REQUEST);}
        
        $user = $userRepository->findOneBy(["email" => $email]);
        if (!$user) {return $this->json(["error" => "User does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        return $this->json([$user], JsonResponse::HTTP_OK, [],["groups" => "userRestricted"] );
    }

    /**
     * @Route("/users/profil", name="app_api_user_profil", methods={"GET"})
     */
    public function getProfil(): JsonResponse
    {
        $connectedUser = $this->jwtHandler->getUser();
        if (!$connectedUser) {return $this->json(["error" => "User does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        return $this->json([$connectedUser], JsonResponse::HTTP_OK, [],["groups" => "userProfil"] );
    }

    /**
     * @Route("/users/profil", name="app_api_user_profil_update", methods={"PUT"})
     */
    public function updateProfil(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager, PasswordValidation $passwordValidator): JsonResponse
    {
        $connectedUser = $this->jwtHandler->getUser();

        if (!$connectedUser) {return $this->json(["error" => "User does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        try {
            $user = $this->serializer->deserialize(
                $request->getContent(),
                User::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $connectedUser,
                    "groups" => "userUpdate"
            ]);
        } catch (NotEncodableValueException $e) {
            return $this->json(["error" => "Invalid json"], JsonResponse::HTTP_BAD_REQUEST);
        }

        $content = $request->toArray();
        
        if(isset($content["oldPassword"])){
            $oldPlainPassword = $content["oldPassword"];
            if(!$this->passwordHasher->isPasswordValid($user, $oldPlainPassword))
                {return $this->json(["error" => "Old password field is not correct."], JsonResponse::HTTP_BAD_REQUEST);}
            if(isset($content["newPassword"])){
                $plainPassword = $content["newPassword"];
                $errors = $passwordValidator->validatePlainPassword($plainPassword);
                if (count($errors) > 0) {
                    $dataErrors = [];
                    foreach($errors as $error){
                        $dataErrors["password"][] = $error->getMessage();
                    }
                    return $this->json($dataErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
                }

                $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
            }
        }
        
        $user->setUpdatedAt(new \DateTimeImmutable());

        $errors = $validator->validate($user, null, ["Default", "update"]);
        if (count($errors) > 0) {
            $dataErrors = [];
            foreach($errors as $error){
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json($dataErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($user);
        $entityManager->flush();
        return $this->json(null, JsonResponse::HTTP_NO_CONTENT,[], []);
    }

    /**
     * @Route("/users/{id}/teams", name="app_api_user-team_list", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function listTeam(BelongsToRepository $belongsToRepository, User $user = null): JsonResponse
    {
        if (!$user) {return $this->json(["error" => "User does not exist."], JsonResponse::HTTP_NOT_FOUND);}
    
        $teams = $belongsToRepository->findTeamsByUser($user);
    
        return $this->json($teams, JsonResponse::HTTP_OK, [], []);
    }

}

