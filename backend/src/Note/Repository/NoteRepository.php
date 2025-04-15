<?php

namespace App\Note\Repository;

use App\Note\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Note>
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    /**
     * @param UserInterface $user
     * @param int|null $id
     * @return array
     */
    public function findByIdAndOrUser(UserInterface $user, ?int $id = null): array
    {
        if ($id) {
            return $this->findBy(['id' => $id, 'user' => $user]);
        }
        return $this->findBy(
            ['user' => $user]
        );
    }

    /**
     * Delete a note
     * @param Note $note
     * @return Note
     */
    public function save(Note $note): Note
    {
        $entityManager = $this->getEntityManager();
        $entityManager->wrapInTransaction(function () use ($entityManager, $note) {
            $entityManager->persist($note);
            $entityManager->flush();
        });
        return $note;
    }

    /**
     * Delete a note
     * @param Note $note
     * @return bool
     */
    public function delete(Note $note): bool
    {
        $entityManager = $this->getEntityManager();
        $entityManager->beginTransaction();
        try {
            $entityManager->remove($note);
            $entityManager->flush();
            $entityManager->commit();
            return true;
        } catch (\Exception $e) {
            $entityManager->rollback();
            return false;
        }
    }
}
