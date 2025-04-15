<?php

namespace App\Tests\Repository;

use App\Tests\TestCase;
use App\User\Entity\User;

class UserRepositoryTest extends TestCase
{
    public function testCreateUser(): void
    {
        $repository = $this->entityManager->getRepository(User::class);

        $user = new User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setUsername('usertest');
        $user->setPlainPassword('password');
        $user->setRoles(['ROLE_USER']);

        $returnedUser = $repository->create($user);

        $this->assertInstanceOf(User::class, $returnedUser);

        $this->assertNotNull($returnedUser->getId());
        $this->assertEquals('Test', $returnedUser->getFirstName());
        $this->assertEquals('User', $returnedUser->getLastName());
        $this->assertEquals('usertest', $returnedUser->getUsername());
        $this->assertContains('ROLE_USER', $returnedUser->getRoles());

        $savedUser = $repository->find($returnedUser->getId());
        $this->assertNotNull($savedUser);
        $this->assertEquals('Test', $savedUser->getFirstName());
        $this->assertEquals('User', $savedUser->getLastName());

        self::assertNotNull($savedUser->getCreatedAt(), 'created_at should not be null');
        self::assertNotNull($savedUser->getUpdatedAt(), 'updated_at should not be null');
    }

    public function testCreateUserPersistsCorrectly(): void
    {
        $repository = $this->entityManager->getRepository(User::class);

        $userCountBefore = count($repository->findAll());

        $user = new User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setUsername('usertest');
        $user->setPlainPassword('password');
        $user->setRoles(['ROLE_ADMIN']);

        $repository->create($user);

        $userCountAfter = count($repository->findAll());

        $this->assertEquals($userCountBefore + 1, $userCountAfter);

        $savedUser = $repository->findOneBy(['username' => 'usertest']);
        $this->assertNotNull($savedUser);
        $this->assertEquals('Test', $savedUser->getFirstName());
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $savedUser->getRoles());
    }

    public function testCreateUserThrowsOnInvalidUser(): void
    {
        $this->expectException(\Exception::class);

        $repository = $this->entityManager->getRepository(User::class);

        $invalidUser = new User();

        $repository->create($invalidUser);

        $this->entityManager->flush();
    }
}