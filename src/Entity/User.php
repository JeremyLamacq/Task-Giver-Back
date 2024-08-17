<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, groups={"create", "update"})
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"member", "memberList"})
     * @Groups({"userProfil", "user", "userList", "userRestricted", "userRestrictedList"})
     * @Groups({"team"})
     * @Groups({"task", "taskList"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank
     * @Groups({"member", "memberList"})
     * @Groups({"userProfil", "user", "userList", "userRestricted", "userRestrictedList"})
     * ---------------------------------------
     * @Groups({"userCreate"})
     */
    private $email;

    /**
     * @ORM\Column(type="json") 
     * @Assert\NotNull
     * @Groups({"user", "userList", "userProfil"})
     * ---------------------------------------
     * @Groups({"userUpdate"})
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotNull
     * @Assert\Length(
     *      min = 6,
     *      groups={"create"}
     * )
     * @Groups({"user", "userList"})
     * ---------------------------------------
     * @Groups({"userCreate"})
     */
    private $password;
            
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"member", "memberList"})
     * @Groups({"userProfil", "user", "userList", "userRestricted", "userRestrictedList"})
     * @Groups({"team"})
     * @Groups({"task", "taskList"})
     * ---------------------------------------
     * @Groups({"userCreate", "userUpdate"})
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"member", "memberList"})
     * @Groups({"userProfil", "user", "userList", "userRestricted", "userRestrictedList"})
     * @Groups({"team"})
     * @Groups({"task", "taskList"})
     * ---------------------------------------
     * @Groups({"userCreate", "userUpdate"})
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Groups({"member", "memberList"})
     * @Groups({"userProfil", "user", "userList", "userRestricted", "userRestrictedList"})
     * @Groups({"team"})
     * ---------------------------------------
     * @Groups({"userCreate", "userUpdate"})
     */
    private $company;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="assignedTo")
     * @Groups({"member"})
     */
    private $assignedTasks;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="createdBy", orphanRemoval=true)
     * @Groups({"member"})
     */
    private $createdTasks;

    /**
     * @ORM\OneToMany(targetEntity=BelongsTo::class, mappedBy="user", orphanRemoval=true, cascade={"remove"})
     * @Groups({"userProfil", "user", "userList", "userRestricted", "userRestrictedList"})
     */
    private $belongsTos;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->assignedTasks = new ArrayCollection();
        $this->createdTasks = new ArrayCollection();
        $this->belongsTos = new ArrayCollection();
        $this->setCreatedAt(new \DateTimeImmutable());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

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

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getAssignedTasks(): Collection
    {
        return $this->assignedTasks;
    }

    public function addAssignedTask(Task $assignedTask): self
    {
        if (!$this->assignedTasks->contains($assignedTask)) {
            $this->assignedTasks[] = $assignedTask;
            $assignedTask->setAssignedTo($this);
        }

        return $this;
    }

    public function removeAssignedTask(Task $assignedTask): self
    {
        if ($this->assignedTasks->removeElement($assignedTask)) {
            // set the owning side to null (unless already changed)
            if ($assignedTask->getAssignedTo() === $this) {
                $assignedTask->setAssignedTo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getCreatedTasks(): Collection
    {
        return $this->createdTasks;
    }

    public function addCreatedTask(Task $createdTask): self
    {
        if (!$this->createdTasks->contains($createdTask)) {
            $this->createdTasks[] = $createdTask;
            $createdTask->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCreatedTask(Task $createdTask): self
    {
        if ($this->createdTasks->removeElement($createdTask)) {
            // set the owning side to null (unless already changed)
            if ($createdTask->getCreatedBy() === $this) {
                $createdTask->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BelongsTo>
     */
    public function getBelongsTos(): Collection
    {
        return $this->belongsTos;
    }

    public function addBelongsTo(BelongsTo $belongsTo): self
    {
        if (!$this->belongsTos->contains($belongsTo)) {
            $this->belongsTos[] = $belongsTo;
            $belongsTo->setUser($this);
        }

        return $this;
    }

    public function removeBelongsTo(BelongsTo $belongsTo): self
    {
        if ($this->belongsTos->removeElement($belongsTo)) {
            // set the owning side to null (unless already changed)
            if ($belongsTo->getUser() === $this) {
                $belongsTo->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Return true if the user belongs to the team provided as first arguement, has a validated attribut with the value provided as second argument and has at least one of the teamRoles provided as third argument.
     * Return false otherwise.
     *
     * @param Team $team
     * @param boolean $validated
     * @param array $teamRoles
     * @return boolean
     */
    public function isMemberOf(Team $team, bool $validated = true, array $teamRoles = BelongsTo::acceptedTeamRoles): bool
    {
        foreach ($this->belongsTos as $belongsTo){
            if($belongsTo->getTeam() === $team){
                if($belongsTo->isValidated() === $validated){
                    foreach($teamRoles as $teamRole){
                        if($belongsTo->hasTeamRole($teamRole)){return true;}
                    }
                }
            }
        }
        return false;
    }
}
