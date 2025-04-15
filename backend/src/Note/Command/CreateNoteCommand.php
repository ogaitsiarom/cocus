<?php

namespace App\Note\Command;

use App\Note\Entity\Note;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'app:add-note',
    description: 'Creates a new note.',
    aliases: ['app:add-note'],
    hidden: false
)]
class CreateNoteCommand extends Command
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $title = $helper->ask($input, $output, new Question("The note title: \n"));
        $content = $helper->ask($input, $output, new Question("The note content: \n"));
        $userId = $helper->ask($input, $output, new Question("The user id: \n"));

        if (!$userId || !$user = $this->entityManager->getRepository(User::class)->find($userId)) {
            $output->writeln("User not found");
            return Command::FAILURE;
        }

        $note = new Note();
        $note->setTitle($title);
        $note->setContent($content);
        $note->setUser($user);
        $this->entityManager->persist($note);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}