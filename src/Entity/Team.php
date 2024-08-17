<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=TeamRepository::class)
 * @UniqueEntity("title", groups={"create", "update"})
 */
class Team
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"user", "userRestricted", "userProfil"})
     * @Groups({"team", "teamList"})
     * @Groups({"task", "taskList"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Notblank
     * @Groups({"user", "userRestricted"})
     * @Groups({"team", "teamList"})
     * @Groups({"task", "taskList"})
     * ---------------------------------------
     * @Groups({"teamCreate", "teamUpdate"})
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     * @Groups({"team"})
     * ---------------------------------------
     * @Groups({"teamCreate", "teamUpdate"})
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=Category::class, mappedBy="team", orphanRemoval=true)
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="team", orphanRemoval=true)
     */
    private $tasks;

    /**
     * @ORM\OneToMany(targetEntity=BelongsTo::class, mappedBy="team", orphanRemoval=true, cascade={"remove"})
     * @Groups({"team"})
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
        $this->categories = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->belongsTos = new ArrayCollection();
        $this->setCreatedAt(new \DateTimeImmutable());
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->setTeam($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getTeam() === $this) {
                $category->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setTeam($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getTeam() === $this) {
                $task->setTeam(null);
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
            $belongsTo->setTeam($this);
        }

        return $this;
    }

    public function removeBelongsTo(BelongsTo $belongsTo): self
    {
        if ($this->belongsTos->removeElement($belongsTo)) {
            // set the owning side to null (unless already changed)
            if ($belongsTo->getTeam() === $this) {
                $belongsTo->setTeam(null);
            }
        }

        return $this;
    }


}


