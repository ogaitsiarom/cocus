<?php

namespace App\Note\Service;

use App\Note\Dto\CreateNoteDto;
use App\Note\Dto\UpdateNoteDto;
use App\Note\Entity\Note;
use App\Note\Mapper\NoteMapper;
use App\Note\Repository\NoteRepository;
use App\User\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class NoteService
{
    public function __construct(private NoteRepository $noteRepository)
    {
    }

    /**
     * Get one note
     * @param int $id
     * @param UserInterface $user
     * @return Note|array
     */
    public function getNote(int $id, UserInterface $user): Note|array
    {
        return $this->noteRepository->findByIdAndOrUser($user, $id);
    }

    /**
     * Get a list of notes
     * @param UserInterface $user
     * @return Note[]|array
     */
    public function getNotes(UserInterface $user): array
    {
        $notes = $this->noteRepository->findByIdAndOrUser($user);
        return NoteMapper::toDtoArray($notes);

    }

    /**
     * Create a note
     * @param CreateNoteDto $createNoteDto
     * @param User|UserInterface $user
     * @return Note
     */
    public function createNote(CreateNoteDto $createNoteDto, User|UserInterface $user): Note
    {
        $note = new Note();
        $note->setTitle($createNoteDto->getTitle());
        $note->setContent($createNoteDto->getContent());
        $note->setUser($user);
        return $this->noteRepository->save($note);
    }

    /**
     * Update a note
     * @param Note $note
     * @param CreateNoteDto $createNoteDto
     * @return Note
     */
    public function updateNote(Note $note, UpdateNoteDto $createNoteDto): Note
    {
        if ($createNoteDto->getTitle() || $createNoteDto->getContent()) {
            $note->setTitle($createNoteDto->getTitle() ?? $note->getTitle());
            $note->setContent($createNoteDto->getContent() ?? $note->getContent());
            return $this->noteRepository->save($note);
        }
        return $note;
    }

    /**
     * Delete a note
     * @param Note $note
     * @return bool
     */
    public function deleteNote(Note $note): bool
    {
        return $this->noteRepository->delete($note);
    }
}