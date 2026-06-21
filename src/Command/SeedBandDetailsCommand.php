<?php

namespace App\Command;

use App\Entity\Band;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:band-details',
    description: 'Fill in genre, social links and descriptions for the Dong Open Air 2026 bands',
)]
class SeedBandDetailsCommand extends Command
{
    /**
     * Verified band metadata keyed by band slug. Links are official artist
     * profiles; a null field means no official profile was confirmed (left
     * untouched so manual data is never overwritten with null).
     *
     * @var array<string, array{genre:string, instagram:?string, spotify:?string, appleMusic:?string, bandcamp:?string, description:string}>
     */
    private const DETAILS = [
        'aereum' => [
            'genre' => 'Melodic Death / Folk Metal',
            'instagram' => 'https://www.instagram.com/aereumofficial/',
            'spotify' => 'https://open.spotify.com/artist/4InllsE71WEGbU1sM1nUtN',
            'appleMusic' => 'https://music.apple.com/us/artist/aereum/1492839659',
            'bandcamp' => 'https://aereum.bandcamp.com/',
            'description' => 'Melodic death and folk metal band from Duisburg, Germany, whose lyrics weave the mysticism of ancient Egypt with earthy themes. Debut album "Tempest of Time" (2020).',
        ],
        'onyxsin' => [
            'genre' => 'Alternative Metal / Metalcore',
            'instagram' => 'https://www.instagram.com/onyxsin_official/',
            'spotify' => 'https://open.spotify.com/artist/6JKpO4OLTJAkwdwa8yt2UF',
            'appleMusic' => 'https://music.apple.com/de/artist/onyxsin/1711274642',
            'bandcamp' => null,
            'description' => 'Metal trio from Duisburg, Germany, formed in late 2021, blending metalcore and hard rock with prog and melodic death metal under the motto "both hard and melodic".',
        ],
        'opus-maxima' => [
            'genre' => 'Djent / Progressive Deathcore',
            'instagram' => 'https://www.instagram.com/opusmaximaofficial/',
            'spotify' => 'https://open.spotify.com/artist/20XGECH8f1iYiduEfH9w7r',
            'appleMusic' => 'https://music.apple.com/us/artist/opus-maxima/1336067467',
            'bandcamp' => 'https://opusmaximaofficial.bandcamp.com/',
            'description' => 'Djent and progressive deathcore band from Mexico City, Mexico, known for 8-string riffs and heavy breakdowns. Album "El Renacer" (2021).',
        ],
        'homecoming' => [
            'genre' => 'Post-Metal / Progressive Metal',
            'instagram' => 'https://www.instagram.com/homecoming_paris/',
            'spotify' => 'https://open.spotify.com/artist/5qad2jhw9rXk2MlKO1khL1',
            'appleMusic' => null,
            'bandcamp' => 'https://wearehomecoming.bandcamp.com/',
            'description' => 'Post-metal and progressive metal quartet from Paris, France, fusing sludge, grunge, 90s alternative rock and prog. Albums "LP01" (2019) and "Those We Knew" (2024).',
        ],
        'octoploid' => [
            'genre' => 'Melodic Death / Progressive Metal',
            'instagram' => 'https://www.instagram.com/octoploidmusic/',
            'spotify' => 'https://open.spotify.com/artist/712moi8jSAse1lYXB6rU4R',
            'appleMusic' => 'https://music.apple.com/ca/artist/octoploid/1736843645',
            'bandcamp' => null,
            'description' => 'Solo project of Amorphis founding bassist Olli-Pekka Laine (Finland), blending 90s death/black metal with 70s prog. Debut album "Beyond the Aeons" (2024).',
        ],
        'non-est-deus' => [
            'genre' => 'Melodic Black Metal',
            'instagram' => 'https://www.instagram.com/non_est_deus/',
            'spotify' => 'https://open.spotify.com/artist/3CAMaX2bss4c0E7K4O0dTf',
            'appleMusic' => 'https://music.apple.com/us/artist/non-est-deus/1598304436',
            'bandcamp' => 'https://noisebringer-records.bandcamp.com/',
            'description' => 'Melodic black metal project from Bamberg, Germany, formed in 2017 by the anonymous musician "Noise" (also of Kanonenfieber), themed around the rejection of religious fanaticism.',
        ],
        'subway-to-sally' => [
            'genre' => 'Medieval Folk Metal',
            'instagram' => 'https://www.instagram.com/subwaytosallyofficial/',
            'spotify' => 'https://open.spotify.com/artist/544X9aDcwFDSon8HevRcqg',
            'appleMusic' => 'https://music.apple.com/us/artist/subway-to-sally/13498062',
            'bandcamp' => 'https://subwaytosally.bandcamp.com/',
            'description' => 'German medieval/folk metal band formed in Potsdam in 1990, blending metal with bagpipes, hurdy-gurdy, shawm and violin, mostly singing in German.',
        ],
        'amorphis' => [
            'genre' => 'Melodic Death / Heavy Metal',
            'instagram' => 'https://www.instagram.com/amorphisband/',
            'spotify' => 'https://open.spotify.com/artist/2UOVgpgiNTC6KK0vSC77aD',
            'appleMusic' => 'https://music.apple.com/fi/artist/amorphis/5545051',
            'bandcamp' => null,
            'description' => 'Metal band from Helsinki, Finland, formed in 1990, blending melodic death and heavy metal with progressive and folk elements and lyrics drawn from the Kalevala.',
        ],
        'hold-your-ground' => [
            'genre' => 'Hardcore Punk',
            'instagram' => 'https://www.instagram.com/holdyourground_official/',
            'spotify' => 'https://open.spotify.com/artist/5yhHa68oOzZLNIS9i8NAaT',
            'appleMusic' => null,
            'bandcamp' => 'https://holdyourground1.bandcamp.com/',
            'description' => 'Four-piece hardcore-punk band from Lünen, Germany, formed in 2021, playing short, direct, energetic songs.',
        ],
        'vorga' => [
            'genre' => 'Atmospheric Black Metal',
            'instagram' => 'https://www.instagram.com/vorga_band/',
            'spotify' => 'https://open.spotify.com/artist/4VeQnaE38lgZIDrYPUYOA8',
            'appleMusic' => 'https://music.apple.com/us/artist/vorga/1454170253',
            'bandcamp' => 'https://vorga.bandcamp.com/',
            'description' => 'Sci-fi-themed melodic/atmospheric black metal band founded in 2016, based in Karlsruhe, Germany. Album "Beyond the Palest Star" (2024) via Transcending Obscurity.',
        ],
        'vansind' => [
            'genre' => 'Folk Metal',
            'instagram' => 'https://www.instagram.com/vansindband/',
            'spotify' => 'https://open.spotify.com/artist/0Nykhf9j9EMdq4K8LeaDfe',
            'appleMusic' => 'https://music.apple.com/us/artist/vansind/1501668341',
            'bandcamp' => 'https://vansind.bandcamp.com/',
            'description' => 'Folk metal band formed in 2019 in Slagelse, Denmark, blending Viking/folk metal with lyrics drawn from Norse mythology and Scandinavian history.',
        ],
        'submasq' => [
            'genre' => 'Progressive Oriental Metal',
            'instagram' => 'https://www.instagram.com/subterranean_masquerade/',
            'spotify' => 'https://open.spotify.com/artist/06JYsafBUf9AGI0SUd4tY2',
            'appleMusic' => 'https://music.apple.com/us/artist/subterranean-masquerade/79486100',
            'bandcamp' => 'https://submasq.bandcamp.com/',
            'description' => 'Subterranean Masquerade (SubMasq) is a progressive oriental/psychedelic metal band from Israel, founded in 1997, blending avant-garde metal with Middle Eastern influences.',
        ],
        'night-in-gales' => [
            'genre' => 'Melodic Death Metal',
            'instagram' => 'https://www.instagram.com/night_in_gales_deathmetal/',
            'spotify' => 'https://open.spotify.com/artist/16l3GG2iL03FXfxJaNx1rC',
            'appleMusic' => null,
            'bandcamp' => 'https://nightingalesmerch.bandcamp.com/',
            'description' => 'German melodic death metal band from North Rhine-Westphalia, active since 1995. Ninth album "Shadowreaper" (2024).',
        ],
        'goldsmith' => [
            'genre' => 'Heavy Rock',
            'instagram' => 'https://www.instagram.com/goldsmithrocks/',
            'spotify' => 'https://open.spotify.com/artist/3cMgrUPLwJg5aky4Z4sszv',
            'appleMusic' => null,
            'bandcamp' => null,
            'description' => 'Heavy rock band from Freiburg, Germany, founded by guitarist/vocalist Michael Goldschmidt, blending traditional heavy metal, thrash, hard rock and punk.',
        ],
        'grailknights' => [
            'genre' => 'Power Metal',
            'instagram' => 'https://www.instagram.com/grailknights_official/',
            'spotify' => 'https://open.spotify.com/artist/3WKWdx78zcqsj2RkN5ldqR',
            'appleMusic' => null,
            'bandcamp' => null,
            'description' => 'German power metal band from Wunstorf known for theatrical superhero personas and fantasy-themed lyrics. Album "Forever" (2025).',
        ],
        'creeper' => [
            'genre' => 'Horror Punk / Gothic Rock',
            'instagram' => 'https://www.instagram.com/creepercult/',
            'spotify' => 'https://open.spotify.com/artist/0nV7SiEIVtPLTSJ6NwWDGj',
            'appleMusic' => null,
            'bandcamp' => null,
            'description' => 'British band from Southampton, England, formed in 2014, whose concept albums span horror punk, glam rock and gothic rock; fronted by Will Gould.',
        ],
        'satyricon' => [
            'genre' => 'Black Metal',
            'instagram' => 'https://www.instagram.com/satyriconofficial/',
            'spotify' => 'https://open.spotify.com/artist/221Rd0FvVxMx7eCbWqjiKd',
            'appleMusic' => null,
            'bandcamp' => 'https://satyricon.bandcamp.com/',
            'description' => 'Norwegian black metal band formed in Oslo in 1991, led by Satyr and Frost; one of the most prominent acts in Norwegian black metal.',
        ],
        'skindred' => [
            'genre' => 'Ragga Metal',
            'instagram' => 'https://www.instagram.com/skindredmusic/',
            'spotify' => 'https://open.spotify.com/artist/3jTlKw98Ql1jGRPYqhqHap',
            'appleMusic' => null,
            'bandcamp' => null,
            'description' => 'Welsh band from Newport formed in 1998 fusing heavy metal with reggae, a style they call ragga metal. 2026 album "You Got This" topped the UK Albums Chart.',
        ],
        'goatfather' => [
            'genre' => 'Stoner Rock',
            'instagram' => 'https://www.instagram.com/goatfather_stoner/',
            'spotify' => 'https://open.spotify.com/artist/6ZSawiJyYTvdaCzQ25QwyM',
            'appleMusic' => 'https://music.apple.com/us/artist/goatfather/1160685342',
            'bandcamp' => 'https://goatfather.bandcamp.com/',
            'description' => 'Stoner/Southern rock quartet formed in 2014 in Lyon, France. Third album "House of the Rising Smoke" (2025) via Argonauta Records.',
        ],
        'snakebite' => [
            'genre' => 'Hard Rock / Heavy Metal',
            'instagram' => 'https://www.instagram.com/snakebite_music/',
            'spotify' => 'https://open.spotify.com/artist/50WEDFpbZjnsOda59ai38R',
            'appleMusic' => 'https://music.apple.com/us/artist/snakebite/3992097',
            'bandcamp' => 'https://snakebite-music.bandcamp.com/',
            'description' => 'Hard rock/heavy metal band from Essen, Germany, drawing on 80s rock and metal influences. Album "Cobra Crew" (2024).',
        ],
        'gangrena-gasosa' => [
            'genre' => 'Saravá Metal / Crossover Thrash',
            'instagram' => 'https://www.instagram.com/gangrenagasosa/',
            'spotify' => 'https://open.spotify.com/artist/7bmlMF1tQCEqpaW0apBGEh',
            'appleMusic' => 'https://music.apple.com/us/artist/gangrena-gasosa/1018527747',
            'bandcamp' => null,
            'description' => 'Brazilian band from Rio de Janeiro that pioneered "saravá metal", a humorous fusion of crossover thrash, hardcore and Afro-Brazilian (Umbanda) elements. Album "Figa Of The Dark" (2024).',
        ],
        'synsnake' => [
            'genre' => 'Metalcore',
            'instagram' => 'https://www.instagram.com/synsnakeofficial/',
            'spotify' => 'https://open.spotify.com/artist/77gfMqeW2CqUi95Fwi1onA',
            'appleMusic' => 'https://music.apple.com/us/artist/synsnake/1121539493',
            'bandcamp' => 'https://synsnake.bandcamp.com/',
            'description' => 'South Korean metalcore band from Seoul, formed in 2015, known for coining "K-popcore". Second album "Nodes" (2025).',
        ],
        'impureza' => [
            'genre' => 'Death Metal (Hispanic Metal)',
            'instagram' => 'https://www.instagram.com/impurezaofficial/',
            'spotify' => 'https://open.spotify.com/artist/3YA9Uqs38ZD6Lg2q66PVw9',
            'appleMusic' => 'https://music.apple.com/us/artist/impureza/422671948',
            'bandcamp' => 'https://impureza.bandcamp.com/',
            'description' => 'Franco-Spanish band formed in 2004 fusing brutal death metal with flamenco guitar and Spanish vocals — "Hispanic Metal". Album "Alcázares" (2025) via Season of Mist.',
        ],
        'detartrated' => [
            'genre' => 'Deathcore',
            'instagram' => 'https://www.instagram.com/detartrated_official/',
            'spotify' => 'https://open.spotify.com/artist/03V1hykDrJWlq0kzEQkNBe',
            'appleMusic' => 'https://music.apple.com/de/artist/detartrated/1804598727',
            'bandcamp' => 'https://detartrated.bandcamp.com/',
            'description' => 'German orchestral deathcore band from the Castrop-Rauxel area, formed in 2024, combining crushing breakdowns with orchestral atmospheres.',
        ],
        'bonded' => [
            'genre' => 'Thrash Metal',
            'instagram' => 'https://www.instagram.com/bondedofficial/',
            'spotify' => 'https://open.spotify.com/artist/7IqK0RqRcaTDtVJtblZ0HQ',
            'appleMusic' => 'https://music.apple.com/us/artist/bonded/1487156987',
            'bandcamp' => 'https://bonded.bandcamp.com/',
            'description' => 'German thrash/death metal band formed in 2018, featuring former Sodom members Bernemann and Makka Freiwald. Debut "Rest In Violence" (2020) via Century Media.',
        ],
        'gaerea' => [
            'genre' => 'Black Metal',
            'instagram' => 'https://www.instagram.com/gaerea_/',
            'spotify' => 'https://open.spotify.com/artist/1wXoI3Ajpv4WwQ3LmcrSBw',
            'appleMusic' => 'https://music.apple.com/us/artist/gaerea/1383640506',
            'bandcamp' => 'https://gaerea.bandcamp.com/',
            'description' => 'Masked black metal band from Porto, Portugal, formed in 2016 and signed to Season of Mist, known for anonymity and an emotive, genre-bending sound.',
        ],
        'overkill' => [
            'genre' => 'Thrash Metal',
            'instagram' => 'https://www.instagram.com/overkillofficial/',
            'spotify' => 'https://open.spotify.com/artist/0NmYchKQ8JIR9QHYJA0FRe',
            'appleMusic' => 'https://music.apple.com/us/artist/overkill/263455457',
            'bandcamp' => 'https://overkillmetal.bandcamp.com/',
            'description' => 'American thrash metal band formed in 1980 in New Jersey, fronted by Bobby "Blitz" Ellsworth and bassist D.D. Verni. One of the pioneering East Coast thrash bands.',
        ],
        'alestorm' => [
            'genre' => 'Pirate Metal',
            'instagram' => 'https://www.instagram.com/alestormofficial/',
            'spotify' => 'https://open.spotify.com/artist/3OpqU68JpZlzvjAJj3B2Da',
            'appleMusic' => 'https://music.apple.com/us/artist/alestorm/272280214',
            'bandcamp' => 'https://alestorm.bandcamp.com/',
            'description' => 'Scottish heavy metal band formed in Perth in 2004, dubbed "pirate metal" for its pirate-themed folk and power metal sound. Signed to Napalm Records.',
        ],
    ];

    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite fields that already have a value (default: only fill empty fields)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $overwrite = (bool) $input->getOption('overwrite');
        $bandRepo = $this->em->getRepository(Band::class);

        $updated = 0;
        $missing = [];

        foreach (self::DETAILS as $slug => $d) {
            $band = $bandRepo->findOneBy(['slug' => $slug]);
            if ($band === null) {
                $missing[] = $slug;
                continue;
            }

            $this->fill($band->setGenre(...), $band->getGenre(), $d['genre'], $overwrite);
            $this->fill($band->setDescription(...), $band->getDescription(), $d['description'], $overwrite);
            $this->fill($band->setInstagram(...), $band->getInstagram(), $d['instagram'], $overwrite);
            $this->fill($band->setSpotify(...), $band->getSpotify(), $d['spotify'], $overwrite);
            $this->fill($band->setAppleMusic(...), $band->getAppleMusic(), $d['appleMusic'], $overwrite);
            $this->fill($band->setBandcamp(...), $band->getBandcamp(), $d['bandcamp'], $overwrite);

            ++$updated;
        }

        $this->em->flush();

        $io->success(sprintf('Enriched %d of %d bands.', $updated, \count(self::DETAILS)));

        if ($missing !== []) {
            $io->warning(sprintf(
                "%d band(s) not found in the database (run app:seed:dong-open-air first?): %s",
                \count($missing),
                implode(', ', $missing)
            ));
        }

        return Command::SUCCESS;
    }

    /**
     * Apply $value via $setter, skipping nulls and (unless overwriting) fields
     * that already hold a non-empty value.
     */
    private function fill(callable $setter, ?string $current, ?string $value, bool $overwrite): void
    {
        if ($value === null) {
            return; // no verified data — never clobber with null
        }
        if (!$overwrite && $current !== null && $current !== '') {
            return; // keep existing manual data
        }
        $setter($value);
    }
}
