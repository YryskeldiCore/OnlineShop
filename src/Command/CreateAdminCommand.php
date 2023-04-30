<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateAdminCommand extends Command
{
    protected static $defaultName = 'make:admin';
    protected static $defaultDescription = 'creates an admin!';

    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userAdmin = new User();

        $userAdmin->setEmail('admin@gmail.com');
        $userAdmin->setUsername('admin');
        $userAdmin->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($userAdmin, '123123123');
        $userAdmin->setPassword($hashedPassword);

        $this->entityManager->persist($userAdmin);
        $this->entityManager->flush();

        $io->writeln('successfully created!');

        return Command::SUCCESS;
    }
}