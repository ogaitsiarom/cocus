<?php

namespace App\Note\Mapper;

use App\Note\Dto\NoteDto;
use App\Note\Entity\Note;

class NoteMapper
{
    /**
     * Converts a single Note entity to a NoteDto.
     */
    public static function toDto(Note $note): NoteDto
    {
        return new NoteDto(
            $note->getId(),
            $note->getTitle(),
            $note->getContent(),
            $note->getCreatedAt(),
            $note->getUpdatedAt()
        );
    }

    /**
     * Converts an array of Note entities to an array of NoteDto objects.
     */
    public static function toDtoArray(array $notes): array
    {
        return array_map(fn(Note $note) => self::toDto($note), $notes);
    }
}