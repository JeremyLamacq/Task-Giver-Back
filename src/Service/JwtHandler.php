<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class JwtHandler{

    private $jwtManager;
    private $tokenStorageInterface;
    private $userRepository;

    public function __construct(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager, UserRepository $userRepository)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->userRepository = $userRepository;
    }

    /**
     * Return the current user accessing the API route.
     * Requires the JWT token to be provided as Bearer authentification.
     *
     * @return User|null
     */
    public function getUser() : ?User
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $email = $decodedJwtToken["username"];
        return $this->userRepository->findOneBy(["email" => $email]);
    }

    /**
     * Return the email of the current user making the API call.
     * Requires the JWT token to be provided as Bearer authentification.
     *
     * @return mixed
     */
    public function getEmail() : mixed
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        return $decodedJwtToken["username"];
    }

}