<?php

namespace App\Command;

use App\Entity\Festival;
use App\Entity\Stage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:backfill-slugs',
    description: 'Generate slugs for existing festivals and stages',
)]
class BackfillSlugsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = 0;

        foreach ($this->em->getRepository(Festival::class)->findAll() as $festival) {
            // Re-setting the name regenerates the slug via the entity setter.
            $festival->setName($festival->getName());
            $io->writeln(sprintf('Festival "%s" → %s', $festival->getName(), $festival->getSlug()));
            ++$count;
        }

        foreach ($this->em->getRepository(Stage::class)->findAll() as $stage) {
            $stage->setName($stage->getName());
            $io->writeln(sprintf('Stage "%s" → %s', $stage->getName(), $stage->getSlug()));
            ++$count;
        }

        $this->em->flush();

        $io->success(sprintf('Backfilled %d slug(s).', $count));

        return Command::SUCCESS;
    }
}
