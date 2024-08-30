<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 * @ORM\Table(name="task")
 */
class Task
{
    const UNASSIGNED = 0;
    const ASSIGNED = 1;
    const ONGOING = 2;
    const DONE = 3;
    const REJECTED = 4;
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"member"})
     * @Groups({"user", "userRestricted"})
     * @Groups({"team"})
     * @Groups({"task", "taskList"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Groups({"member"})
     * @Groups({"user", "userRestricted"})
     * @Groups({"team"})
     * @Groups({"task", "taskList"})
     * ---------------------------------------
     * @Groups({"taskCreate", "taskUpdate"})
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="tasks")
     * @Groups({"user", "userRestricted"})
     * @Groups({"team"})
     * @Groups({"task", "taskList"})
     * ---------------------------------------
     * @Groups({"taskCreate", "taskUpdate"})
     */
    private $category;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     * @Groups({"task", "taskList"})
     * ---------------------------------------
     * @Groups({"taskCreate", "taskUpdate"})
     */
    private $description;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\NotNull
     * @Assert\Range(
     *      min = 1,
     *      max = 5,
     *      notInRangeMessage = "The difficulty field must be an integer between {{ min }} and {{ max }}",
     * )
     * @Groups({"user", "userRestricted"})
     * @Groups({"team"})
     * @Groups({"task", "taskList"})
     * ---------------------------------------
     * @Groups({"taskCreate", "taskUpdate"})
     */
    private $difficulty;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     * @Groups({"user"})
     * @Groups({"task", "taskList"})
     */
    private $team;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Assert\NotNull
     * @Groups({"member"})
     * @Groups({"user", "userRestricted"})
     * @Groups({"team"})
     * @Groups({"task", "taskList"})
     * ---------------------------------------
     * @Groups({"taskCreate", "taskUpdate"})
     */
    private $acceptDeadline;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Assert\NotNull
     * @Groups({"member"})
     * @Groups({"user", "userRestricted"})
     * @Groups({"team"})
     * @Groups({"task", "taskList"})
     * ---------------------------------------
     * @Groups({"taskCreate", "taskUpdate"})
     */
    private $completionDeadline;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"member"})
     * @Groups({"user", "userRestricted"})
     * @Groups({"team"})
     * @Groups({"task", "taskList"})
     */
    private $datetimeAccepted;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"member"})
     * @Groups({"user", "userRestricted"})
     * @Groups({"team"})
     * @Groups({"task", "taskList"})
     */
    private $datetimeCompleted;

    /**
     * @ORM\Column(type="smallint", options={"default" : 0})
     * @Assert\NotNull
     * @Assert\Range(
     *      min = 0,
     *      max = 4,
     *      notInRangeMessage = "The status field must be an integer between {{ min }} and {{ max }}",
     * )
     * @Groups({"member"})
     * @Groups({"user", "userRestricted"})
     * @Groups({"team"})
     * @Groups({"task", "taskList"})
     */
    private $status;

    /**
     * @ORM\Column(type="boolean")
     */
    private $rejected;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="assignedTasks")
     * @Groups({"task", "taskList"})
     * ---------------------------------------
     * @Groups({"taskCreate", "taskUpdate"})
     */
    private $assignedTo;

    /**
     * @ORM\ManyToOne(targetEntity=user::class, inversedBy="createdTasks")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     * @Groups({"task", "taskList"})
     */
    private $createdBy;

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
        $this->setStatus(0);
        $this->setCreatedAt(new \DateTimeImmutable());
        $this->setRejected(false);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDifficulty(): ?int
    {
        return $this->difficulty;
    }

    public function setDifficulty(int $difficulty): self
    {
        $this->difficulty = $difficulty;

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

    public function getAcceptDeadline(): ?\DateTimeImmutable
    {
        return $this->acceptDeadline;
    }

    public function setAcceptDeadline(\DateTimeImmutable $acceptDeadline): self
    {
        $this->acceptDeadline = $acceptDeadline;

        return $this;
    }

    public function getCompletionDeadline(): ?\DateTimeImmutable
    {
        return $this->completionDeadline;
    }

    public function setCompletionDeadline(\DateTimeImmutable $completionDeadline): self
    {
        $this->completionDeadline = $completionDeadline;

        return $this;
    }

    public function getDatetimeAccepted(): ?\DateTimeImmutable
    {
        return $this->datetimeAccepted;
    }

    public function setDatetimeAccepted(?\DateTimeImmutable $datetimeAccepted): self
    {
        $this->datetimeAccepted = $datetimeAccepted;

        return $this;
    }

    public function getDatetimeCompleted(): ?\DateTimeImmutable
    {
        return $this->datetimeCompleted;
    }

    public function setDatetimeCompleted(?\DateTimeImmutable $datetimeCompleted): self
    {
        $this->datetimeCompleted = $datetimeCompleted;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $assignedTo): self
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    public function getCreatedBy(): ?user
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?user $createdBy): self
    {
        $this->createdBy = $createdBy;

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

    public function isRejected(): ?bool
    {
        return $this->rejected;
    }

    public function setRejected(bool $rejected): self
    {
        $this->rejected = $rejected;

        return $this;
    }

}
