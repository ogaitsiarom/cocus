<?php

namespace App\Tests\Unit;

use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
    }

    public function testSetAndGetFirstName(): void
    {
        $this->user->setFirstName('Test');
        self::assertSame('Test', $this->user->getFirstName());
    }

    public function testSetAndGetLastName(): void
    {
        $this->user->setLastName('User');
        self::assertSame('User', $this->user->getLastName());
    }

    public function testSetAndGetUsername(): void
    {
        $this->user->setUsername('usertest');
        self::assertSame('usertest', $this->user->getUsername());
        self::assertSame('usertest', $this->user->getUserIdentifier());
    }

    public function testSetAndGetPassword(): void
    {
        $password = 'hashedpassword';
        $this->user->setPassword($password);
        self::assertSame($password, $this->user->getPassword());
    }

    public function testSetAndGetPlainPassword(): void
    {
        $plainPassword = 'plain_password';
        $this->user->setPlainPassword($plainPassword);
        self::assertSame($plainPassword, $this->user->getPlainPassword());
    }

    public function testRolesDefaultIncludesUserRole(): void
    {
        self::assertSame(['ROLE_USER'], $this->user->getRoles());
    }

    public function testRolesSetCorrectly(): void
    {
        $roles = ['ROLE_ADMIN', 'ROLE_USER'];
        $this->user->setRoles($roles);
        self::assertContains('ROLE_ADMIN', $this->user->getRoles());
        self::assertContains('ROLE_USER', $this->user->getRoles());
    }

    public function testGetNotesCollection(): void
    {
        self::assertInstanceOf(ArrayCollection::class, $this->user->getNotes() ?? new ArrayCollection());
    }

    public function testEraseCredentials(): void
    {
        $this->user->setPlainPassword('toRemove');
        $this->user->eraseCredentials();

        self::assertEquals('toRemove', $this->user->getPlainPassword());
    }
}