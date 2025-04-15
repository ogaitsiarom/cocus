<?php

namespace App\Tests\Integration;

use App\Note\Entity\Note;
use App\Tests\TestCase;
use App\User\Entity\User;
use Carbon\Carbon;
use Exception;


class NoteControllerTest extends TestCase
{

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        Carbon::setTestNow('2024-05-16');
        parent::setUp();
    }

    public function testAccessDenied(): void
    {
        $this->client->request('GET', '/api/note/1');
        $this->assertResponseStatusCodeSame(401);

        $this->client->request('GET', '/api/notes');
        $this->assertResponseStatusCodeSame(401);

        $this->client->request('POST', '/api/note');
        $this->assertResponseStatusCodeSame(401);

        $this->client->request('PUT', '/api/note/1');
        $this->assertResponseStatusCodeSame(401);

        $this->client->request('DELETE', '/api/note/1');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testIndex(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'test']);

        $this->client->loginUser($user);
        $this->client->request('GET', '/api/note/1');

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $expectedData = [
            'id' => 1,
            'title' => 'test',
            'content' => 'test',
            'createdAt' => '2024-05-16T00:00:00+00:00',
            'updatedAt' => '2024-05-16T00:00:00+00:00',
        ];

        $this->assertEquals($expectedData, $data);
    }

    public function testIndexWithWrongUser(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'admin']);

        $this->client->loginUser($user);
        $this->client->request('GET', '/api/note/1');
        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(['message' => 'Note not found'], $data);
    }

    public function testIndexWithNoNote(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'test']);

        $this->client->loginUser($user);
        $this->client->request('GET', '/api/note/99');
        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(['message' => 'Note not found'], $data);
    }

    public function testList(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'test']);

        $this->client->loginUser($user);
        $this->client->request('GET', '/api/notes');

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $data);

        $expectedData = [[
            'id' => 1,
            'title' => 'test',
            'content' => 'test',
            'createdAt' => '2024-05-16T00:00:00+00:00',
            'updatedAt' => '2024-05-16T00:00:00+00:00',
        ], [
            'id' => 2,
            'title' => 'test',
            'content' => 'test',
            'createdAt' => '2024-05-16T00:00:00+00:00',
            'updatedAt' => '2024-05-16T00:00:00+00:00',
        ]];

        $this->assertEquals($expectedData, $data);
    }

    public function testListWithNoNotes(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'empty']);

        $this->client->loginUser($user);
        $this->client->request('GET', '/api/notes');

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(0, $data);

        $expectedData = [];

        $this->assertEquals($expectedData, $data);
    }

    public function testCreateNote(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'test']);

        $this->client->loginUser($user);
        $this->client->request('POST', '/api/note', [
            'title' => 'Shopping list',
            'content' => 'Banana, Apple, Orange, Toothbrush, Milk, Soap, Shampoo',
        ]);
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals([
            "id" => 4,
            "title" => "Shopping list",
            "content" => "Banana, Apple, Orange, Toothbrush, Milk, Soap, Shampoo",
            "createdAt" => "2024-05-16T00:00:00.000000Z",
            "updatedAt" => "2024-05-16T00:00:00.000000Z",
        ], $data);
    }

    public function testCreateNoteWithInvalidTitle(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'test']);

        $this->client->loginUser($user);

        $this->client->request(
            method: 'POST',
            uri: '/api/note',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode([
                'title' => 'smal',
                'content' => 'A valid content here',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(422, $response->getStatusCode());
        $actualResponse = json_decode($response->getContent(), true);
        $expectedResponse = 'Your title must be at least 5 characters long';
        $this->assertEquals($expectedResponse, $actualResponse['violations'][0]['title']);
    }

    public function testCreateNoteWithInvalidContent(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'test']);

        $this->client->loginUser($user);

        $this->client->request(
            method: 'POST',
            uri: '/api/note',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode([
                'title' => 'This is a valid title',
                'content' => 'smal',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(422, $response->getStatusCode());
        $actualResponse = json_decode($response->getContent(), true);
        $expectedResponse = 'Your content must be at least 5 characters long';
        $this->assertEquals($expectedResponse, $actualResponse['violations'][0]['title']);
    }

    public function testCreateNoteWithInvalidTitleAndContent(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'test']);

        $this->client->loginUser($user);

        $this->client->request(
            method: 'POST',
            uri: '/api/note',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode([
                'title' => 'smal',
                'content' => 'smal',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(422, $response->getStatusCode());
        $actualResponse = json_decode($response->getContent(), true);
        $expectedResponseTitle = 'Your title must be at least 5 characters long';
        $expectedResponseContent = 'Your content must be at least 5 characters long';
        $this->assertEquals($expectedResponseTitle, $actualResponse['violations'][0]['title']);
        $this->assertEquals($expectedResponseContent, $actualResponse['violations'][1]['title']);
    }

    public function testUpdateNote(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'test']);

        $this->client->loginUser($user);

        $note = $this->entityManager->getRepository(Note::class)->findOneBy(['id' => 1]);
        $oldTitle = $note->getTitle();
        $oldContent = $note->getContent();

        $this->client->request(
            method: 'PUT',
            uri: '/api/note/1',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode([
                'title' => 'This is a new title',
                'content' => 'This is a new content',
            ])
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $updatedNote = $this->entityManager->getRepository(Note::class)->findOneBy(['id' => 1]);
        $this->assertEquals('This is a new title', $updatedNote->getTitle());
        $this->assertEquals('This is a new content', $updatedNote->getContent());
        $this->assertNotEquals($oldTitle, $updatedNote->getTitle());
        $this->assertNotEquals($oldContent, $updatedNote->getContent());
        $this->assertEquals([
            "id" => 1,
            "title" => "This is a new title",
            "content" => "This is a new content",
            "createdAt" => "2024-05-16T00:00:00.000000Z",
            "updatedAt" => "2024-05-16T00:00:00.000000Z",
        ], $data);
    }

    public function testUpdateNoteWithInvalidTitle(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'test']);

        $this->client->loginUser($user);

        $note = $this->entityManager->getRepository(Note::class)->findOneBy(['id' => 1]);

        $this->client->request(
            method: 'PUT',
            uri: '/api/note/1',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode([
                'title' => 'smal',
                'content' => 'This is a new content',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(422, $response->getStatusCode());
        $actualResponse = json_decode($response->getContent(), true);
        $expectedResponseTitle = 'Your title must be at least 5 characters long';
        $this->assertEquals($expectedResponseTitle, $actualResponse['violations'][0]['title']);
    }

    public function testUpdateNoteWithInvalidContent(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'test']);

        $this->client->loginUser($user);

        $note = $this->entityManager->getRepository(Note::class)->findOneBy(['id' => 1]);

        $this->client->request(
            method: 'PUT',
            uri: '/api/note/1',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode([
                'title' => 'This is a new title',
                'content' => 'smal',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(422, $response->getStatusCode());
        $actualResponse = json_decode($response->getContent(), true);
        $expectedResponseContent = 'Your content must be at least 5 characters long';
        $this->assertEquals($expectedResponseContent, $actualResponse['violations'][0]['title']);
    }

    public function testUpdateNoteWithInvalidTitleAndContent(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'test']);

        $this->client->loginUser($user);

        $note = $this->entityManager->getRepository(Note::class)->findOneBy(['id' => 1]);

        $this->client->request(
            method: 'PUT',
            uri: '/api/note/1',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode([
                'title' => 'smal',
                'content' => 'smal',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(422, $response->getStatusCode());
        $actualResponse = json_decode($response->getContent(), true);
        $expectedResponseTitle = 'Your title must be at least 5 characters long';
        $expectedResponseContent = 'Your content must be at least 5 characters long';
        $this->assertEquals($expectedResponseTitle, $actualResponse['violations'][0]['title']);
        $this->assertEquals($expectedResponseContent, $actualResponse['violations'][1]['title']);
    }

    public function testUpdateNoteWithWrongUser(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'admin']);

        $this->client->loginUser($user);
        $this->client->request('PUT', '/api/note/1', [
            'title' => 'Shopping list',
            'content' => 'Banana, Apple, Orange, Toothbrush, Milk, Soap, Shampoo',
        ]);
        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(['message' => 'Note not found'], $data);
    }

    public function testUpdateNoteWithNonexistentID(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'admin']);

        $this->client->loginUser($user);
        $this->client->request('PUT', '/api/note/99', [
            'title' => 'Shopping list',
            'content' => 'Banana, Apple, Orange, Toothbrush, Milk, Soap, Shampoo',
        ]);
        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(['message' => 'Note not found'], $data);
    }

    public function testDeleteNote(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'test']);

        $this->client->loginUser($user);
        $this->client->request('DELETE', '/api/note/1');
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Note deleted', $data);
    }

    public function testDeleteNonexistentNote(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'test']);

        $this->client->loginUser($user);
        $this->client->request('DELETE', '/api/note/99');
        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(['message' => 'Note not found'], $data);
    }
}