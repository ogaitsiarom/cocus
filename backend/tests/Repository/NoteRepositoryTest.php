<?php

namespace App\Tests\Repository;

use App\Note\Entity\Note;
use App\Note\Repository\NoteRepository;
use App\Tests\TestCase;
use App\User\Entity\User;
use Doctrine\ORM\EntityRepository;

class NoteRepositoryTest extends TestCase
{
    private NoteRepository|EntityRepository $noteRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->noteRepository = $this->entityManager->getRepository(Note::class);
    }

    public function testFindByIdAndOrUserReturnsResult(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'test']);

        $notes = $this->noteRepository->findByIdAndOrUser($user);

        $this->assertCount(2, $notes);

        foreach ($notes as $note) {
            $this->assertEquals('test', $note->getUser()->getUsername());
        }
    }

    public function testFindByIdAndOrUserReturnsEmpty(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'empty']);

        $notes = $this->noteRepository->findByIdAndOrUser($user);

        $this->assertEmpty($notes);
    }

    public function testFindNoteByIdAndUserReturnsSingle(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'admin']);

        $note = $this->noteRepository->findOneBy(['user' => $user, 'title' => 'test admin']);

        $notes = $this->noteRepository->findByIdAndOrUser($user, $note->getId());

        $this->assertCount(1, $notes);
        $this->assertEquals('test admin', $notes[0]->getTitle());
    }

    public function testSaveCreatesANewNote(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'test']);

        $note = new Note();
        $note->setTitle('New Note');
        $note->setContent('Content');
        $note->setUser($user);

        $savedNote = $this->noteRepository->save($note);

        $this->assertNotNull($savedNote->getId());
        $this->assertEquals('New Note', $savedNote->getTitle());
        $this->assertEquals($user->getId(), $savedNote->getUser()->getId());

        self::assertNotNull($note->getCreatedAt(), 'created_at should not be null');
        self::assertNotNull($note->getUpdatedAt(), 'updated_at should not be null');

    }

    public function testDeleteNote(): void
    {
        $note = new Note();
        $note->setTitle('To Delete');
        $note->setContent('Deletable Content');
        $note = $this->noteRepository->save($note);

        $noteId = $note->getId();

        $result = $this->noteRepository->delete($note);

        $this->assertTrue($result);

        $deletedNote = $this->noteRepository->find($noteId);
        $this->assertNull($deletedNote);
    }
}