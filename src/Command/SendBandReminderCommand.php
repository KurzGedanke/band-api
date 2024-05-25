<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\TimeSlotRepository;

#[AsCommand(
    name: 'send:band-reminder',
    description: 'Add a short description for your command',
)]
class SendBandReminderCommand extends Command
{
    private TimeSlotRepository $timeSlotRepository;

    public function __construct(TimeSlotRepository $timeSlotRepository)
    {
        $this->timeSlotRepository = $timeSlotRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        date_default_timezone_set('Europe/Berlin');

        $dateNow = date('Y-m-d H:i:00', time());
        $dateLater = date('Y-m-d H:i:00', (time() + 5 * 60 ));

        $timeSlots = $this->timeSlotRepository->findNextTimeSlotsBasedOn5Minutes($dateNow, $dateLater);

        foreach ($timeSlots as $timeSlot) {
            $io->comment($timeSlot->getBand()->getName());
        }

        $io->success($timeSlots);
        $io->success($dateNow);
        $io->success($dateLater);

        return Command::SUCCESS;
    }
}
