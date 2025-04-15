<?php

namespace App\Note\Controller;

use App\Note\Dto\CreateNoteDto;
use App\Note\Dto\UpdateNoteDto;
use App\Note\Mapper\NoteMapper;
use App\Note\Service\NoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller to manage notes.
 */
#[Route('/api')]
class NoteController extends AbstractController
{
    public function __construct(private readonly NoteService $service)
    {
    }

    /**
     * Get a note
     * @param Request $request
     * @return Response
     */
    #[Route('/note/{id}', name: 'get_note', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $note = $this->service->getNote($request->get('id'), $this->getUser());
        if (!$note) {
            return new JsonResponse(['message' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json(NoteMapper::toDto($note[0]), Response::HTTP_OK);
    }

    /**
     * Get a list note of notes
     * @return JsonResponse
     */
    #[Route('/notes', name: 'get_notes', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $notes = $this->service->getNotes($this->getUser());
        return $this->json($notes, Response::HTTP_OK);
    }

    /**
     * Create a note
     * @param CreateNoteDto $createNoteDto
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/note', name: 'store_note', methods: ['POST'])]
    public function store(
        #[MapRequestPayload] CreateNoteDto $createNoteDto, Request $request
    ): JsonResponse
    {
        $note = $this->service->createNote($createNoteDto, $this->getUser());
        return new JsonResponse(NoteMapper::toDto($note), Response::HTTP_OK);
    }

    /**
     * Update a note
     * @param UpdateNoteDto $updateNoteDto
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/note/{id}', name: 'edit_note', methods: ['PUT'])]
    public function update(#[MapRequestPayload] UpdateNoteDto $updateNoteDto, Request $request): JsonResponse
    {
        $note = $this->service->getNote($request->get('id'), $this->getUser());
        if (!$note) {
            return new JsonResponse(['message' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }
        $note = $this->service->updateNote($note[0], $updateNoteDto);
        return new JsonResponse(NoteMapper::toDto($note), Response::HTTP_OK);
    }

    /**
     * Delete a note
     * @param Request $request
     * @return Response
     */
    #[Route('/note/{id}', name: 'delete_note', methods: ['DELETE'])]
    public function delete(Request $request): Response
    {
        $note = $this->service->getNote($request->get('id'), $this->getUser());
        if (!$note) {
            return new JsonResponse(['message' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }
        if (!$this->service->deleteNote($note[0])) {
            return new JsonResponse(['message' => 'Note deleted'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse('Note deleted', Response::HTTP_OK);
    }
}