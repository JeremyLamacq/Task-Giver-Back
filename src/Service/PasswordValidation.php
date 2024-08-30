<?php
namespace App\Service;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

class PasswordValidation
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Verify that the given plain password is valid according to the following assertion :
     *  - 6 characters or more.
     *
     * @param String $password
     * @return ConstraintViolationListInterface
     */
    public function validatePlainPassword(String $password): ConstraintViolationListInterface
    {
        $passwordValidator = Validation::createValidator();
        $violations = $passwordValidator->validate($password, [
            new Length(['min' => 6]),
            new NotBlank(),
        ]);
        return $violations;
    }

}