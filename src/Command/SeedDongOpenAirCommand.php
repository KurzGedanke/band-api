<?php

namespace App\Command;

use App\Entity\Band;
use App\Entity\Festival;
use App\Entity\Stage;
use App\Entity\TimeSlot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:dong-open-air',
    description: 'Seed the Dong Open Air 2026 festival, stage, bands and time slots (scraped from dongopenair.de)',
)]
class SeedDongOpenAirCommand extends Command
{
    private const FESTIVAL_NAME = 'Dong Open Air 2026';
    private const STAGE_NAME = 'Hauptbühne';
    private const IMG_BASE = 'https://www.dongopenair.de/wp-content/plugins/dong_magic/files/doa_2026/bands/';

    // day => calendar date (16.-18. July 2026, Thu/Fri/Sat)
    private const DATES = [
        'Donnerstag' => '2026-07-16',
        'Freitag'    => '2026-07-17',
        'Samstag'    => '2026-07-18',
    ];

    /** Lineup scraped from https://www.dongopenair.de/bands/ : [day, time, band, imageFile] */
    private const LINEUP = [
        ['Donnerstag', '12:25', 'Aereum', 'aereum.jpg'],
        ['Donnerstag', '13:30', 'Onyxsin', 'onyxsin.jpg'],
        ['Donnerstag', '14:35', 'Opus Maxima', 'opus_maxima.jpg'],
        ['Donnerstag', '15:40', 'Homecoming', 'homecoming.jpg'],
        ['Donnerstag', '16:45', 'Octoploid', 'octoploid.jpg'],
        ['Donnerstag', '17:50', 'Non Est Deus', 'non_est_deux.jpg'],
        ['Donnerstag', '19:20', 'Subway to Sally', 'subway_to_sally.jpg'],
        ['Donnerstag', '21:00', 'Amorphis', 'amorphis.jpg'],

        ['Freitag', '11:15', 'Hold Your Ground', 'hold_your_ground.jpg'],
        ['Freitag', '12:20', 'VORGA', 'vorga.jpg'],
        ['Freitag', '13:25', 'Vansind', 'vansind.jpg'],
        ['Freitag', '14:30', 'SubMasq', 'subterranean_masquerade.jpg'],
        ['Freitag', '15:35', 'Night in Gales', 'night_in_gales.jpg'],
        ['Freitag', '16:40', 'Goldsmith', 'goldsmith.jpg'],
        ['Freitag', '17:45', 'Grailknights', 'grailknights.jpg'],
        ['Freitag', '19:05', 'Creeper', 'creeper.jpg'],
        ['Freitag', '20:35', 'Satyricon', 'satyricon.jpg'],
        ['Freitag', '22:15', 'Skindred', 'skindred.jpg'],

        ['Samstag', '11:15', 'Goatfather', 'goatfather.jpg'],
        ['Samstag', '12:20', 'Snakebite', 'snakebite.jpg'],
        ['Samstag', '13:25', 'Gangrena Gasosa', 'gangrena_gasosa.jpg'],
        ['Samstag', '14:30', 'Synsnake', 'sysnake.jpg'],
        ['Samstag', '15:35', 'Impureza', 'impureza.jpg'],
        ['Samstag', '16:40', 'Detartrated', 'detartrated.jpg'],
        ['Samstag', '17:45', 'Bonded', 'bonded.jpg'],
        ['Samstag', '19:05', 'Gaerea', 'gaerea.jpg'],
        ['Samstag', '20:35', 'Overkill', 'overkill.jpg'],
        ['Samstag', '22:15', 'Alestorm', 'alestorm.jpg'],
    ];

    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('no-images', null, InputOption::VALUE_NONE, 'Skip downloading band images');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $downloadImages = !$input->getOption('no-images');
        $imageDir = \dirname(__DIR__, 2) . '/public/images/band/images';

        $bandRepo = $this->em->getRepository(Band::class);

        // Festival (idempotent by name)
        $festival = $this->em->getRepository(Festival::class)->findOneBy(['name' => self::FESTIVAL_NAME]) ?? new Festival();
        $festival->setName(self::FESTIVAL_NAME);
        $festival->setStartDate(new \DateTime(self::DATES['Donnerstag']));
        $festival->setEndDate(new \DateTime(self::DATES['Samstag']));
        $this->em->persist($festival);

        // Single stage (idempotent by name + festival)
        $stage = $this->em->getRepository(Stage::class)->findOneBy(['name' => self::STAGE_NAME, 'festival' => $festival]) ?? new Stage();
        $stage->setName(self::STAGE_NAME);
        $stage->setLocation('Dong, Neukirchen-Vluyn');
        $stage->setFestival($festival);
        $this->em->persist($stage);

        // Pre-compute end times: next slot start on the same day, last slot + 75 min
        $endTimes = $this->computeEndTimes();

        $created = 0;
        $updated = 0;
        $imagesOk = 0;

        foreach (self::LINEUP as $i => [$day, $time, $name, $imageFile]) {
            $slug = $this->slugify($name);
            $band = $bandRepo->findOneBy(['slug' => $slug]);
            if ($band === null) {
                $band = new Band();
                $created++;
            } else {
                $updated++;
            }
            $band->setName($name);
            $band->setSlug($slug);
            $band->setImage($imageFile);
            $festival->addBand($band);
            $this->em->persist($band);

            if ($downloadImages && $this->downloadImage($imageFile, $imageDir)) {
                $imagesOk++;
            }

            $start = new \DateTime(self::DATES[$day] . ' ' . $time);
            $end = new \DateTime(self::DATES[$day] . ' ' . $endTimes[$i]);

            $slot = new TimeSlot();
            $slot->setBand($band);
            $slot->setStage($stage);
            $slot->setStartTime($start);
            $slot->setEndTime($end);
            $this->em->persist($slot);
        }

        $this->em->flush();

        $io->success(sprintf(
            '%s: %d bands created, %d updated, %d time slots, %d images downloaded.',
            self::FESTIVAL_NAME,
            $created,
            $updated,
            \count(self::LINEUP),
            $imagesOk
        ));

        return Command::SUCCESS;
    }

    /** @return array<int,string> index in LINEUP => end time "HH:MM" */
    private function computeEndTimes(): array
    {
        $byDay = [];
        foreach (self::LINEUP as $i => [$day, $time]) {
            $byDay[$day][] = [$i, $time];
        }
        $ends = [];
        foreach ($byDay as $slots) {
            $count = \count($slots);
            foreach ($slots as $pos => [$i, $time]) {
                if ($pos + 1 < $count) {
                    $ends[$i] = $slots[$pos + 1][1];
                } else {
                    $ends[$i] = (new \DateTime($time))->modify('+75 minutes')->format('H:i');
                }
            }
        }
        return $ends;
    }

    private function slugify(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }

    private function downloadImage(string $file, string $dir): bool
    {
        $target = $dir . '/' . $file;
        if (is_file($target)) {
            return true;
        }
        $data = @file_get_contents(self::IMG_BASE . $file);
        if ($data === false) {
            return false;
        }
        return @file_put_contents($target, $data) !== false;
    }
}
