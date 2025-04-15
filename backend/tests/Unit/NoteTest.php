<?php

namespace App\Tests\Unit;

use App\Note\Entity\Note;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

class NoteTest extends TestCase
{
    private Note $note;

    protected function setUp(): void
    {
        $this->note = new Note();
    }

    public function testSetAndGetTitle(): void
    {
        $title = 'Test Note Title';
        $this->note->setTitle($title);

        self::assertSame($title, $this->note->getTitle());
    }

    public function testSetAndGetContent(): void
    {
        $content = 'This is the content of the note.';
        $this->note->setContent($content);

        self::assertSame($content, $this->note->getContent());
    }

    public function testSetAndGetUser(): void
    {
        $user = new User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setUsername('usertest');

        $this->note->setUser($user);

        self::assertSame($user, $this->note->getUser());
        self::assertEquals('Test', $this->note->getUser()->getFirstName());
    }

    public function testPropertiesInitialization(): void
    {
        self::assertNull($this->note->getId());

        $this->note->setTitle('Init title');
        $this->note->setContent('Init content');

        self::assertEquals('Init title', $this->note->getTitle());
        self::assertEquals('Init content', $this->note->getContent());
    }
}