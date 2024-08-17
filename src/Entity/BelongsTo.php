<?php

namespace App\Entity;

use App\Repository\BelongsToRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=BelongsToRepository::class)
 * @UniqueEntity("user", "team", groups={"create", "update"})
 */
class BelongsTo
{
    public const acceptedTeamRoles = [
        "LEADER",
        "GIVER",
        "TASKER"
    ];

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="belongsTos")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotNull
     * @Groups({"member", "memberList"})
     * @Groups({"team"})
     * ---------------------------------------
     * @Groups({"memberCreate"})
     */
    private $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="belongsTos")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotNull
     * @Groups({"user", "userRestricted", "userProfil"})
     */
    private $team;

    /**
     * @ORM\Column(type="json")
     * @Assert\NotNull
     * @Groups({"member", "memberList"})
     * @Groups({"user", "userRestricted", "userProfil"})
     * @Groups({"team"})
     * ---------------------------------------
     * @Groups({"memberCreate","memberUpdate"})
     */
    private $teamRoles = [];

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"member", "memberList"})
     * @Groups({"user", "userRestricted", "userProfil"})
     * @Groups({"team"})
     * ---------------------------------------
     * @Groups({"memberUpdate"})
     */
    private $validated;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"user", "userRestricted"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"user", "userRestricted"})
     */
    private $updatedAt;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTimeImmutable());
        // TODO : Wait for front to make the interface that would allow to accept or refuse an invitation.
        // Will need to make 2 routes for accepting or refusing an invitation to a team.
        $this->setValidated(true);
        // $this->setValidated(false);
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getTeamRoles(): ?array
    {

        return $this->teamRoles;
    }

    public function setTeamRoles(array $teamRoles): self
    {
        $this->teamRoles = $teamRoles;

        return $this;
    }

    public function isValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated): self
    {
        $this->validated = $validated;
        
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Return true if teamRole is present in the array teamRoles.
     * Return false otherwise.
     *
     * @param String $teamRole
     * @return boolean
     */
    public function hasTeamRole(String $teamRole): bool
    {
        return in_array($teamRole, $this->getTeamRoles());
    }

    /**
     * Return an array containing all the teamRoles from the array provided as first argument that are part of a whitelist of teamRoles provided as second argument.
     * Also remove duplicate entries.
     *
     * @param array $teamRoles
     * @param array $acceptedTeamRoles
     * @return array
     */
    public static function filterTeamRolesInArray(array $teamRoles, array $acceptedTeamRoles = self::acceptedTeamRoles): array
    {
        $result = [];
        foreach($acceptedTeamRoles as $acceptedTeamRole){
            if(in_array($acceptedTeamRole, $teamRoles)){
                $result[] = $acceptedTeamRole;
            }
        }
        return array_unique($result);
    }
}
