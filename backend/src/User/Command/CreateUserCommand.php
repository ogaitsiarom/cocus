<?php

namespace App\User\Command;

use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'app:add-user',
    description: 'Creates a new user.',
    aliases: ['app:add-user'],
    hidden: false
)]
class CreateUserCommand extends Command
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $firstName = $helper->ask($input, $output, new Question("The user first name\n"));
        $lastName = $helper->ask($input, $output, new Question("The user last name\n"));
        $username = $helper->ask($input, $output, new Question("The user username\n"));
        $password = $helper->ask($input, $output, new Question("The user password\n"));
        $question = new ChoiceQuestion("Chose a role for the user", ['ROLE_USER', 'ROLE_ADMIN']);
        $role = $helper->ask($input, $output, $question);

        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setUsername($username);
        $user->setPlainPassword($password);
        $user->setRoles([$role]);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}