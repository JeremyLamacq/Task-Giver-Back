<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Entity\Team;
use App\Repository\CategoryRepository;
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
class CategoryController extends AbstractController
{
    private $serializer;
    private $jwtHandler;

    public function __construct(SerializerInterface $serializer, JwtHandler $jwtHandler)
    {
        $this->serializer = $serializer;
        $this->jwtHandler = $jwtHandler;
    }

    /**
     * @Route("/teams/{id}/categories", name="app_api_team-category_list", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function listCategory(Team $team = null): JsonResponse
    {
        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        $categories = $team->getCategories();
        return $this->json($categories, JsonResponse::HTTP_OK, [],["groups" => "categoryList"] );
    }

    /**
     * @Route("/teams/{id}/categories/{category}", name="app_api_team-category_get", methods={"GET"}, requirements={"id"="\d+", "category"="\d+"})
     */
    public function getCategory( Team $team = null, Category $category = null): JsonResponse
    {
        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        if (!$category) {return $this->json(["error" => "Category does not exist."], JsonResponse::HTTP_NOT_FOUND);} 
        return $this->json($category, JsonResponse::HTTP_OK, [],["groups" => "category"] );
    }

    /**
     * @Route("/teams/{id}/categories", name="app_api_team-category_create", methods={"POST"})
     */
    public function createCategory(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager, Team $team): JsonResponse
    {
        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        try {
            $category = $this->serializer->deserialize(
                $request->getContent(),
                Category::class,
                'json',
                [
                    "groups" => "categoryCreate"
                ]);
        } catch (NotEncodableValueException $e) {
            return $this->json(["error" => "Invalid json"], JsonResponse::HTTP_BAD_REQUEST);
        }

        $team->addCategory($category);

        $errors = $validator->validate($category, null, ["Default", "create"]);
        if (count($errors) > 0) {
            $dataErrors = [];
            foreach($errors as $error){
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json($dataErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json([$category], JsonResponse::HTTP_CREATED,[
            "Location" => $this->generateUrl("app_api_team-category_get", ["id" => $team->getId(), "category" => $category->getId()])
        ], ["groups" => "category"]);
    }

    /**
     * @Route("/teams/{id}/categories/{category}", name="app_api_team-category_update", methods={"PUT"}, requirements={"id"="\d+", "category"="\d+"})
     */
    public function updateCategory(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager, Team $team = null, Category $category = null): JsonResponse
    {
        if (!$team) {return $this->json(["error" => "Team does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        if (!$category) {return $this->json(["error" => "Category does not exist."], JsonResponse::HTTP_NOT_FOUND);}

        try {
            $this->serializer->deserialize(
                $request->getContent(),
                Category::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $category,
                    "groups" => "categoryUpdate"
                ]);
        } catch (NotEncodableValueException $e) {
            return $this->json(["error" => "Invalid json"], JsonResponse::HTTP_BAD_REQUEST);
        }

        $team->addCategory($category);
        $category->setUpdatedAt(new DateTimeImmutable());


        $errors = $validator->validate($category, null, ["Default", "update"]);
        if (count($errors) > 0) {
            $dataErrors = [];
            foreach($errors as $error){
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json($dataErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT,[], []);
    }

    /**
     * @Route("/teams/{id}/categories/{category}", name="app_api_team-category_delete", methods={"DELETE"}, requirements={"id"="\d+", "category"="\d+"})
     */
    public function deleteCategory(CategoryRepository $categoryRepository, Category $category = null): JsonResponse
    {
        if (!$category) {return $this->json(["error" => "Category does not exist."], JsonResponse::HTTP_NOT_FOUND);}
        $categoryRepository->remove($category, true);
        return $this->json("Category deleted successfully.", JsonResponse::HTTP_OK, [],[] );
    }
}