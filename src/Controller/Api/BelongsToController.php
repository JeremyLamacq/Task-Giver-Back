<?php

namespace App\Controller\Api;

use App\Entity\BelongsTo;
use App\Entity\Team;
use App\Repository\BelongsToRepository;
use App\Service\JwtHandler;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface as SerializerSerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 */
class BelongsToController extends AbstractController
{
    private $serializer;
    private $passwordHasher;
    private $jwtHandler;

    public function __construct(SerializerSerializerInterface $serializer, UserPasswordHasherInterface $passwordHasher, JwtHandler $jwtHandler)
    {
        $this->serializer = $serializer;
        $this->passwordHasher = $passwordHasher;
        $this->jwtHandler = $jwtHandler;
    }

    /**
     * @Route("/teams/{id}/members", name="app_api_team-member_list", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function listMember(Team $team = null, Request $request, BelongsToRepository $belongsToRepository): JsonResponse
    {
        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        $filters = ["team" => $team];

        // TODO: Make a custom findBy or querybuilder for the teamRoles case.
        // if($request->query->has('role')){
        //     $teamRole = (String) $request->query->get('role');
        //     $filters += array("teamRoles" => $teamRole);
        // }
        if($request->query->has('validated')){
            $validated = (bool) $request->query->get('validated');
            $filters += array("validated" => $validated);
        }

        $belongsto = $belongsToRepository->findBy($filters);
        return $this->json($belongsto, JsonResponse::HTTP_OK, [],["groups" => "memberList"] );
    }

    /**
    * @Route("/teams/{id}/members/{member}", name="app_api_team-member_get", methods={"GET"}, requirements={"id"="\d+", "member"="\d+"})
    */
    public function getMember(Team $team = null, int $member, BelongsToRepository $belongsToRepository): JsonResponse
    {
        $belongsTo = $belongsToRepository->findOneBy(['team' => $team,'user' => $member]);
        if(!$team){return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        if(!$belongsTo){return $this->json(["error" => "Member does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        return $this->json([$belongsTo], JsonResponse::HTTP_OK, [], ["groups" => "team"]);
    }

    /**
     * @Route("/teams/{id}/members", name="app_api_team-member_create", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function createMember(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager, Team $team = null): JsonResponse
    {
        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        try {
            $belongsTo = $this->serializer->deserialize(
                $request->getContent(),
                BelongsTo::class,
                'json',
                [
                    "groups" => "memberCreate"
            ]);
        } catch (NotEncodableValueException $e) {
            return $this->json(["error" => "Invalid json"], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        if($belongsTo->getUser()=== null)
            {return $this->json(["error" => "User does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        // Prevent the addition of the LEADER teamrole.
        $filteredTeamRoles = BelongsTo::filterTeamRolesInArray(
            $belongsTo->getTeamRoles(),
            array_diff(BelongsTo::acceptedTeamRoles, array("LEADER")));
        $belongsTo->setTeamRoles($filteredTeamRoles);


        $team->addBelongsTo($belongsTo);

        $errors = $validator->validate($belongsTo, null, ["Default", "create"]);
        if (count($errors) > 0) {
            $dataErrors = [];
            foreach($errors as $error){
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json($dataErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($belongsTo);
        $entityManager->flush();
        
        return $this->json([$belongsTo], JsonResponse::HTTP_CREATED,[
            "Location" => $this->generateUrl("app_api_team-member_get", ["id" => $team->getId(), "member" => $belongsTo->getUser()->getId()])
        ], ["groups" => "member"]);
    }

    /**
     * @Route("/teams/{id}/members/{member}", name="app_api_team-member_update", methods={"PUT"}, requirements={"id"="\d+", "member"="\d+"})
     */
    public function updateMember(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager, Team $team = null, int $member, BelongsToRepository $belongsToRepository): JsonResponse
    {
        $belongsTo = $belongsToRepository->findOneBy(['team' => $team,'user' => $member]);

        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        if (!$belongsTo) {return $this->json(["error" => "Member does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        // Check if the member already has the Leader Role.
        $isLeader = $belongsTo->hasTeamRole("LEADER");

        try {
            $this->serializer->deserialize(
                $request->getContent(),
                BelongsTo::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $belongsTo,
                    "groups" => "memberUpdate"
                ]);
        } catch (NotEncodableValueException $e) {
            return $this->json(["error" => "Invalid json"], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Ensure members with LEADER teamRole do not lose it and thoses without it can't receive it.
        $teamRoles = $belongsTo->getTeamRoles();
        $acceptedTeamRoles = BelongsTo::acceptedTeamRoles;
        if($isLeader){$teamRoles[] = "LEADER";}
        else {$acceptedTeamRoles = array_diff($acceptedTeamRoles, array("LEADER"));}
        
        $filteredTeamRoles = BelongsTo::filterTeamRolesInArray(
            $teamRoles,
            $acceptedTeamRoles
            );
        $belongsTo->setTeamRoles($filteredTeamRoles);

        $belongsTo->setUpdatedAt(new DateTimeImmutable());

        $errors = $validator->validate($belongsTo, null, ["Default", "update"]);
        if (count($errors) > 0) {
            $dataErrors = [];
            foreach($errors as $error){
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json($dataErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($belongsTo);
        $entityManager->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT,[], []);
    }

    /**
     * @Route("/teams/{id}/members/{member}", name="app_api_team-member_delete", methods={"DELETE"}, requirements={"id"="\d+", "member"="\d+"})
     */
    public function deleteMember(Team $team = null, int $member, BelongsToRepository $belongsToRepository ): JsonResponse
    {   
        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        $belongsTo = $belongsToRepository->findOneBy(['team' => $team, 'user' => $member]);
        if(!$belongsTo){return $this->json(["error" => "Member does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        $belongsToRepository->remove($belongsTo, true);

        return $this->json(["message" => "Member deleted successfully."], JsonResponse::HTTP_OK);
    }
}
