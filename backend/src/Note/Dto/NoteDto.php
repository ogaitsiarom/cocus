<?php

namespace App\Note\Dto;


use Carbon\Carbon;

class NoteDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $content,
        public Carbon $createdAt,
        public ?Carbon $updatedAt = null
    ) {}
}