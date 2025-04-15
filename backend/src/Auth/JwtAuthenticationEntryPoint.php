<?php

namespace App\Auth;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Implements the AuthenticationEntryPointInterface to provide a mechanism for starting the authentication process.
 * @param Request $request The current HTTP request object.
 * @param AuthenticationException|null $authException Optional exception object containing details about why authentication failed.
 * @return JsonResponse The response object containing the authentication requirement message and HTTP status code.
 */
class JwtAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Authentication Required',
        ], Response::HTTP_UNAUTHORIZED);
    }
}