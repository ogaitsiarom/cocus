<?php

namespace App\Tests\Command;

use App\Tests\TestCase;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends TestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $application = new Application(self::$kernel);
        $command = $application->find('app:add-user');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteSuccessfullyWithUserRole(): void
    {
        $this->commandTester->setInputs([
            'John',
            'Doe',
            'john_doe',
            'securePassword',
            'ROLE_USER',
        ]);

        $statusCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $statusCode);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'john_doe']);

        $this->assertNotNull($user, 'User should be created.');
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testExecuteSuccessfullyWithAdminRole(): void
    {
        $this->commandTester->setInputs([
            'Admin',
            'Tester',
            'admin_tester',
            'adminSecurePass',
            'ROLE_ADMIN',
        ]);

        $statusCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $statusCode);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'admin_tester']);

        $this->assertNotNull($user, 'Admin user should be created.');
        $this->assertEquals('Admin', $user->getFirstName());
        $this->assertEquals('Tester', $user->getLastName());
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());
    }
}