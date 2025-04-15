<?php

namespace App\Tests\Command;

use App\Note\Entity\Note;
use App\Tests\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CreateNoteCommandTest extends TestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();
        $application = new Application(self::$kernel);
        $command = $application->find('app:add-note');

        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteSuccessful(): void
    {
        $user = $this->entityManager->getRepository('App\User\Entity\User')->findOneBy(['username' => 'test']);
        $this->assertNotNull($user);

        $this->commandTester->setInputs([
            'Test Title',
            'Test Content',
            (string)$user->getId(),
        ]);

        $statusCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $statusCode);

        $note = $this->entityManager->getRepository(Note::class)->findOneBy([
            'title' => 'Test Title',
            'content' => 'Test Content',
            'user' => $user,
        ]);

        $this->assertNotNull($note);
    }

    public function testExecuteFailsWithUserNotFound(): void
    {
        $invalidUserId = 99999;

        $this->commandTester->setInputs([
            'Another Test Title',
            'Another Test Content',
            (string)$invalidUserId,
        ]);

        $statusCode = $this->commandTester->execute([]);

        $this->assertNotEquals(0, $statusCode);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('User not found', $output);

        $note = $this->entityManager->getRepository(Note::class)->findOneBy([
            'title' => 'Another Test Title',
            'content' => 'Another Test Content',
        ]);

        $this->assertNull($note);
    }
}