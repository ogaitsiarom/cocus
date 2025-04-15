<?php

namespace App\Tests;

use App\Note\Entity\Note;
use App\User\Entity\User;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestCase extends WebTestCase
{
    public EntityManagerInterface $entityManager;
    public ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->createSchema($metadata);

        $repo = $this->entityManager->getRepository(User::class);
        $user = new User();
        $user->setUsername('test');
        $user->setFirstName('test');
        $user->setLastName('test');
        $user->setPlainPassword('password');
        $user->setRoles(['ROLE_USER']);
        $user = $repo->create($user);

        $adminUser = new User();
        $adminUser->setUsername('admin');
        $adminUser->setFirstName('Admin');
        $adminUser->setLastName('Test');
        $adminUser->setPlainPassword('password');
        $adminUser->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $adminUser = $repo->create($adminUser);

        $emptyUser = new User();
        $emptyUser->setUsername('empty');
        $emptyUser->setFirstName('Empty');
        $emptyUser->setLastName('Test');
        $emptyUser->setPlainPassword('password');
        $emptyUser->setRoles(['ROLE_USER']);
        $emptyUser = $repo->create($emptyUser);

        $note = new Note();
        $note->setTitle('test');
        $note->setContent('test');
        $note->setUser($user);
        $note = $this->entityManager->getRepository(Note::class)->save($note);

        $note = new Note();
        $note->setTitle('test');
        $note->setContent('test');
        $note->setUser($user);
        $note = $this->entityManager->getRepository(Note::class)->save($note);

        $note = new Note();
        $note->setTitle('test admin');
        $note->setContent('test admin');
        $note->setUser($adminUser);
        $note = $this->entityManager->getRepository(Note::class)->save($note);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Carbon::setTestNow(null);

        if (isset($this->entityManager)) {
            $schemaTool = new SchemaTool($this->entityManager);
            $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

            $schemaTool->dropSchema($metadata);

            $this->entityManager->close();
            unset($this->entityManager);
        }

        if (isset($this->client)) {
            unset($this->client);
        }

    }
}