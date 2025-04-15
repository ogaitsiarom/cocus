<?php

namespace App\Note\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateNoteDto
{

    /**
     * @var string $title The note title
     */
    #[Assert\NotBlank(message: 'The title cannot be empty')]
    #[Assert\Length(min: 5, max: 255, minMessage: 'Your title must be at least {{ limit }} characters long', maxMessage: 'Your title cannot be longer than {{ limit }} characters')]
    private string $title;

    /**
     * @var string $content The note content
     */
    #[Assert\NotBlank(message: 'The content cannot be empty')]
    #[Assert\Length(min: 5, max: 255, minMessage: 'Your content must be at least {{ limit }} characters long', maxMessage: 'Your content cannot be longer than {{ limit }} characters')]
    private string $content;

    /**
     * @param string $title
     * @param string $content
     */
    public function __construct(string $title, string $content)
    {
        $this->title = $title;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}