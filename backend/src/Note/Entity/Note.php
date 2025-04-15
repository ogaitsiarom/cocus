<?php

namespace App\Note\Entity;

use App\Global\SetTimestamps;
use App\Note\Repository\NoteRepository;
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Note extends SetTimestamps
{
    /**
     * @var ?int $id The note ID
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string $title The note title
     */
    #[ORM\Column]
    private string $title;

    /**
     * @var string $content The note content
     */
    #[ORM\Column]
    private string $content;

    /**
     * @var User $user The User relation
     */
    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], inversedBy: 'notes')]
    private User $user;

    /**
     * Get the ID
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the title
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the content
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set the title
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Set the content
     * @param string $content
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * Set the user
     * @param User $user
     * @return void
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Get the user
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
