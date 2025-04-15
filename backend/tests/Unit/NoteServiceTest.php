<?php

namespace App\Tests\Unit;

use App\Note\Dto\CreateNoteDto;
use App\Note\Dto\NoteDto;
use App\Note\Dto\UpdateNoteDto;
use App\Note\Entity\Note;
use App\Note\Repository\NoteRepository;
use App\Note\Service\NoteService;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Security\Core\User\UserInterface;

class NoteServiceTest extends TestCase
{
    private NoteRepository $noteRepository;
    private NoteService $noteService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->noteRepository = $this->createMock(NoteRepository::class);
        $this->noteService = new NoteService($this->noteRepository);
    }

    public function testGetNote(): void
    {
        $user = $this->createMock(UserInterface::class);
        $noteMock = $this->createMock(Note::class);

        $this->noteRepository
            ->expects($this->once())
            ->method('findByIdAndOrUser')
            ->with($user, 1)
            ->willReturn([$noteMock]);

        $result = $this->noteService->getNote(1, $user);

        $this->assertEquals($noteMock, $result[0]);

    }

    public function testGetNoteNotFound(): void
    {
        $user = $this->createMock(UserInterface::class);

        $this->noteRepository
            ->expects($this->once())
            ->method('findByIdAndOrUser')
            ->with($user, 99)
            ->willReturn([]);

        $result = $this->noteService->getNote(99, $user);

        $this->assertEmpty($result);
    }


    /**
     * @throws ReflectionException
     */
    public function testGetNotes(): void
    {
        $user = $this->createMock(User::class);

        $user->method('getId')->willReturn(1);

        $note1 = new Note();
        $this->setEntityId($note1, 1);
        $note1->setTitle('Note 1');
        $note1->setContent('Content 1');
        $this->setEntityCreatedAt($note1, new \Carbon\Carbon('2023-01-01 12:00:00'));

        $note2 = new Note();
        $this->setEntityId($note2, 2);
        $note2->setTitle('Note 2');
        $note2->setContent('Content 2');
        $this->setEntityCreatedAt($note2, new \Carbon\Carbon('2023-01-02 12:00:00'));

        $notesArray = [$note1, $note2];
        $this->noteRepository
            ->expects($this->once())
            ->method('findByIdAndOrUser')
            ->with($user)
            ->willReturn($notesArray);

        $expectedResults = [
            new NoteDto(
                1,
                'Note 1',
                'Content 1',
                new \Carbon\Carbon('2023-01-01 12:00:00'),
                null
            ),
            new NoteDto(
                2,
                'Note 2',
                'Content 2',
                new \Carbon\Carbon('2023-01-02 12:00:00'),
                null
            ),
        ];

        $result = $this->noteService->getNotes($user);

        $this->assertCount(2, $result);

        $this->assertEquals($expectedResults, $result);
    }


    /**
     * Uses reflection to set a private/protected ID on an entity.
     * @throws ReflectionException
     */
    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setValue($entity, $id); // Set the ID
    }

    private function setEntityCreatedAt(Note $note, \Carbon\Carbon $createdAt): void
    {
        $reflection = new \ReflectionClass($note);
        $property = $reflection->getProperty('createdAt');
        $property->setValue($note, $createdAt);
    }




    public function testCreateNote(): void
    {
        $user = $this->createMock(User::class);
        $createNoteDto = new CreateNoteDto('Title', 'Some content');
        $note = new Note();
        $note->setTitle($createNoteDto->getTitle());
        $note->setContent($createNoteDto->getContent());

        $this->noteRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn(Note $noteArg) => $noteArg->getTitle() === 'Title' &&
                $noteArg->getContent() === 'Some content'
            ))
            ->willReturn($note);

        $result = $this->noteService->createNote($createNoteDto, $user);
        $this->assertInstanceOf(Note::class, $result);
        $this->assertEquals('Title', $result->getTitle());
        $this->assertEquals('Some content', $result->getContent());
    }

    public function testCreateNoteFails(): void
    {
        $user = $this->createMock(User::class);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not save note.');

        $createNoteDto = new CreateNoteDto('Invalid Title', 'Some content');

        $this->noteRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new \RuntimeException('Could not save note.'));

        $this->noteService->createNote($createNoteDto, $user);
    }

    public function testUpdateNote(): void
    {
        $existingNote = new Note();
        $existingNote->setTitle('Old Title');
        $existingNote->setContent('Old Content');

        $updateNoteDto = new UpdateNoteDto('New Title', 'New Content');

        $this->noteRepository
            ->expects($this->once())
            ->method('save')
            ->with($existingNote)
            ->willReturnArgument(0);

        $result = $this->noteService->updateNote($existingNote, $updateNoteDto);
        $this->assertEquals('New Title', $result->getTitle());
        $this->assertEquals('New Content', $result->getContent());
    }

    public function testUpdateNoteFails(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to update note.');

        $existingNote = new Note();
        $existingNote->setTitle('Existing Title');
        $existingNote->setContent('Existing Content');

        $updateNoteDto = new UpdateNoteDto('Title Updated', 'Content Updated');

        $this->noteRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new \RuntimeException('Unable to update note.'));

        $this->noteService->updateNote($existingNote, $updateNoteDto);
    }


    public function testDeleteNote(): void
    {
        $note = new Note();

        $this->noteRepository
            ->expects($this->once())
            ->method('delete')
            ->with($note)
            ->willReturn(true);

        $result = $this->noteService->deleteNote($note);
        $this->assertTrue($result);
    }

    public function testDeleteNoteFails(): void
    {
        $note = new Note();

        $this->noteRepository
            ->expects($this->once())
            ->method('delete')
            ->with($note)
            ->willReturn(false);

        $result = $this->noteService->deleteNote($note);
        $this->assertFalse($result);
    }

}