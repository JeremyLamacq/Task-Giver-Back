<?php

namespace App\Controller\Api;

use App\Entity\Team;
use App\Entity\BelongsTo;
use App\Service\JwtHandler;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

/**
 * @Route("/api")
 */
class TeamController extends AbstractController
{
    private $serializer;
    private $jwtHandler;

    public function __construct(SerializerInterface $serializer, JwtHandler $jwtHandler)
    {
        $this->serializer = $serializer;
        $this->jwtHandler = $jwtHandler;
    }

    // ! UNUSED
    /**
     * @Route("/teams", name="app_api_team_list" , methods={"GET"})
     * 
     */
    public function list(TeamRepository $TeamRepository): JsonResponse
    {
        $teams = $TeamRepository->findAll();
        return $this->json($teams, JsonResponse::HTTP_OK,[],["groups" => "teamList"]);
    }

    /**
     * @Route("/teams/{id}", name="app_api_team_get", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function getTeam(Team $team = null): JsonResponse
    {
        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        return $this->json([$team], JsonResponse::HTTP_OK, [],["groups" => "team"] );
    }

    /**
     * @Route("/teams", name="app_api_team_create", methods={"POST"})
     */
    public function create(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        $connectedUser = $this->jwtHandler->getUser();
        if (!$connectedUser) {return $this->json(["error" => "Current user does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        try {
            $team = $this->serializer->deserialize(
                $request->getContent(),
                Team::class,
                'json',
                [
                    "groups" => "teamCreate"
            ]);
        } catch (NotEncodableValueException $e) {
            return $this->json(["error" => "Invalid json"], JsonResponse::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($team, null, ["Default", "create"]);
        if (count($errors) > 0) {
            $dataErrors = [];
            foreach($errors as $error){
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json($dataErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $belongsTo = new BelongsTo();
        $connectedUser->addBelongsTo($belongsTo);
        $team->addBelongsTo($belongsTo);
        $belongsTo->setTeamRoles(["LEADER"]);
        $belongsTo->setValidated(true);

        $entityManager->persist($belongsTo);
        $entityManager->persist($team);
        $entityManager->flush();

        return $this->json([$team], JsonResponse::HTTP_CREATED,[
            "Location" => $this->generateUrl("app_api_team_get", ["id" => $team->getId()])
        ], ["groups" => "team"]);
    }

    /**
     * @Route("/teams/{id}", name="app_api_team_update", methods={"PUT"}, requirements={"id"="\d+"})
     */
    public function update(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager, Team $team = null): JsonResponse
    {
        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        $connectedUser = $this->jwtHandler->getUser();
        if (!$connectedUser) {return $this->json(["error" => "Current user does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        try {
            $this->serializer->deserialize(
                $request->getContent(),
                Team::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $team,
                    "groups" => "teamUpdate"
            ]);
        } catch (NotEncodableValueException $e) {
            return $this->json(["error" => "Invalid json"], JsonResponse::HTTP_BAD_REQUEST);
        }

        if(!$connectedUser->isMemberOf($team, true, ["LEADER"])){
            return $this->json(["error" => "Cannot modify a team you are not the leader of."], JsonResponse::HTTP_FORBIDDEN, [], []);
        }

        $team->setUpdatedAt(new \DateTimeImmutable());

        $errors = $validator->validate($team, null, ["Default", "update"]);
        if (count($errors) > 0) {
            $dataErrors = [];
            foreach($errors as $error){
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json($dataErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($team);
        $entityManager->flush();
        return $this->json(null, JsonResponse::HTTP_NO_CONTENT,[], []);
    }

    /**
     * @Route("/teams/{id}", name="app_api_team_delete", methods={"DELETE"}, requirements={"id"="\d+"})
     */
    public function delete(TeamRepository $TeamRepository, team $team = null): JsonResponse
    {
        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        $connectedUser = $this->jwtHandler->getUser();
        if (!$connectedUser) {return $this->json(["error" => "Current user does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        if(!$connectedUser->isMemberOf($team, true, ["LEADER"])){
            return $this->json(["error" => "Cannot delete a team you are not the leader of."], JsonResponse::HTTP_FORBIDDEN, [], []);
        }
        
        $TeamRepository->remove($team, true);
        return $this->json("Team deleted successfully.", JsonResponse::HTTP_OK, [],[] );
    }

}