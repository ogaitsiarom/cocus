<?php

namespace App\User\Controller;

use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/api')]
class UserController extends AbstractController
{

    #[Route('/user', name: 'create_user', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied: Admin privileges are required for this operation.')]
    public function store(Request $request, UserRepository $repository): JsonResponse
    {
        try {
            $user = new User();
            $user->setFirstName($request->getPayload()->get('firstName'));
            $user->setLastName($request->getPayload()->get('lastName'));
            $user->setUsername($request->getPayload()->get('userName'));
            $user->setPlainPassword($request->getPayload()->get('password'));
            $user->setRoles(["ROLE_USER"]);
            $repository->create($user);
            return $this->json($user, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }
    }
}