<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @Route("/api")
 */
class SignupController extends AbstractController
{
    private $serializer;
    private $passwordHasher;

    public function __construct(SerializerInterface $serializer, UserPasswordHasherInterface $passwordHasher)
    {
        $this->serializer = $serializer;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @Route("/signup", name="app_api_signup", methods={"POST"})
     */
    public function signup(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {  
        try {
            $user = $this->serializer->deserialize(
                $request->getContent(),
                User::class,
                'json',
                [
                    "groups" => "userCreate"
                ]);
        } catch (NotEncodableValueException $e) {
            return $this->json(["error" => "Invalid json"], Response::HTTP_BAD_REQUEST);
        }

        $user->setRoles(['ROLE_USER']);

        $errors = $validator->validate($user, null, ["Default", "create"]);
        if (count($errors) > 0) {
            $dataErrors = [];
            foreach($errors as $error){
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json($dataErrors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $plainPassword = $user->getPassword();
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([$user], Response::HTTP_CREATED,[
            "Location" => $this->generateUrl("app_api_user_get", ["id" => $user->getId()])
        ], ["groups" => "user"]);
    }
}
