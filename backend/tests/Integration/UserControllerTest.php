<?php

namespace App\Tests\Integration;

use App\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserControllerTest extends TestCase
{

    protected function logInAs(string $username, string $password): void
    {
        $this->client->request(Request::METHOD_POST, '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'username' => $username,
            'password' => $password,
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));
    }

    public function testCreateUserWithAdminPrivileges(): void
    {
        $this->logInAs('admin', 'password');

        $this->client->request(
            Request::METHOD_POST,
            '/api/user',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'firstName' => 'Test',
                'lastName'  => 'User',
                'userName'  => 'usertest',
                'password'  => 'password',
            ])
        );

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);

        self::assertEquals('Test', $responseData['firstName']);
        self::assertEquals('User', $responseData['lastName']);
        self::assertEquals('usertest', $responseData['username']);
        self::assertContains('ROLE_USER', $responseData['roles']);
    }

    public function testCreateUserWithoutAdminPrivilegesFails(): void
    {
        $this->logInAs('test', 'password');

        $this->client->request(
            Request::METHOD_POST,
            '/api/user',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'firstName' => 'Unauthorized',
                'lastName'  => 'User',
                'userName'  => 'unauthorizeduser',
                'password'  => 'securepassword',
            ])
        );

        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        self::assertStringContainsString('Access denied', $response->getContent());
    }

    public function testCreateUserWithoutLoginFails(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/api/user',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'firstName' => 'Guest',
                'lastName'  => 'User',
                'userName'  => 'guestuser',
                'password'  => 'securepassword',
            ])
        );

        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testCreateUserMissingDataFailsGracefully(): void
    {
        $this->logInAs('admin', 'password');

        $this->client->request(
            Request::METHOD_POST,
            '/api/user',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([])
        );

        $response = $this->client->getResponse();
        self::assertNotEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }
}