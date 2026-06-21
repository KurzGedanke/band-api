<?php

namespace App\Controller\API;

use App\Repository\FestivalRepository;
use App\Repository\StageRepository;
use App\Repository\TimeSlotRepository;
use App\Repository\BandRepository;
use App\Services\SerializerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class FestivalApiController extends AbstractController
{
    #[Route('/api/festivals', name: 'festival-list', methods: ['GET'])]
    public function listFestivals(FestivalRepository $festivalRepository): JsonResponse
    {
        $festivals = [];
        foreach ($festivalRepository->findAll() as $festival) {
            $festivals[] = [
                'id' => $festival->getId(),
                'name' => $festival->getName(),
                'slug' => $festival->getSlug(),
                'startDate' => $festival->getStartDate()?->format('Y-m-d'),
                'endDate' => $festival->getEndDate()?->format('Y-m-d'),
            ];
        }

        return new JsonResponse($festivals);
    }

    #[Route('/api/festivals/{festivalSlug}/{bandSlug}', name: 'festival-band')]
    public function listFestivalBand(BandRepository $bandRepository, string $bandSlug, string $festivalSlug, Request $request): JsonResponse
    {
        $serializer = new SerializerService();
        $festivalBand = $bandRepository->findOneBy(['slug' => $bandSlug]);
        $fesivalBandTimeSlot = $festivalBand->getTimeSlots() ?? [];

        $bandArray['band'] = [
          'id' => $festivalBand->getId(),
          'name' => $festivalBand->getName(),
          'genre' => $festivalBand->getGenre(),
          'logo' => $request->getSchemeAndHttpHost() . '/images/band/logos/' . $festivalBand->getLogo(),
          'image' => $request->getSchemeAndHttpHost() . '/images/band/images/' . $festivalBand->getImage(),
          'instagram' => $festivalBand->getInstagram(),
          'spotify' => $festivalBand->getSpotify(),
          'appleMusic' => $festivalBand->getAppleMusic(),
          'bandcamp' => $festivalBand->getBandcamp(),
          'description' => $festivalBand->getDescription(),
        ];

        if(isset($fesivalBandTimeSlot)){
          foreach($fesivalBandTimeSlot as $timeslot) {
            if($timeslot->getFestival()->getSlug() === $festivalSlug)
              $bandTimeSlot = $timeslot;
          };

          $bandArray['startTime'] = $bandTimeSlot->getStartTime();
          $bandArray['endTime'] = $bandTimeSlot->getEndTime();
          $bandArray['stage'] = $bandTimeSlot->getStage()->getName();
        }

        return JsonResponse::fromJsonString($serializer->serializeCircularReferenceJson($bandArray));
    }

    #[Route('/api/festivals/{festivalSlug}/bands', name: 'festival-bands', priority: 1)]
    public function listFestivalBands(FestivalRepository $festivalRepository, string $festivalSlug, Request $request): JsonResponse
    {
        $serializer = new SerializerService();
        $festivalEntity = $festivalRepository->findOneBy(['slug' => $festivalSlug]);

        $bands = [];
        foreach ($festivalEntity->getBands() as $band) {
            $bands[] = [
                'id' => $band->getId(),
                'name' => $band->getName(),
                'slug' => $band->getSlug(),
                'genre' => $band->getGenre(),
                'logo' => $request->getSchemeAndHttpHost() . '/images/band/logos/' . $band->getLogo(),
                'image' => $request->getSchemeAndHttpHost() . '/images/band/images/' . $band->getImage(),
                'instagram' => $band->getInstagram(),
                'spotify' => $band->getSpotify(),
                'appleMusic' => $band->getAppleMusic(),
                'bandcamp' => $band->getBandcamp(),
                'description' => $band->getDescription(),
            ];
        }

        return JsonResponse::fromJsonString($serializer->serializeCircularReferenceJson($bands));
    }

    #[Route('/api/festivals/{festivalSlug}/stages', name: 'festival-stages', priority: 1)]
    public function listFestivalStages(FestivalRepository $festivalRepository, string $festivalSlug): JsonResponse
    {
        $festivalEntity = $festivalRepository->findOneBy(['slug' => $festivalSlug]);

        $stages = [];
        foreach ($festivalEntity?->getStages() ?? [] as $stage) {
            $stages[] = [
                'id' => $stage->getId(),
                'name' => $stage->getName(),
                'slug' => $stage->getSlug(),
                'location' => $stage->getLocation(),
            ];
        }

        return new JsonResponse($stages);
    }

    #[Route('/api/festivals/{festivalSlug}/stages/{stageSlug}', name: 'festival-stages-info')]
    public function listFestivalStagesInfo(FestivalRepository $festivalRepository, StageRepository $stageRepository, string $festivalSlug, string $stageSlug): JsonResponse
    {
        $festivalEntity = $festivalRepository->findOneBy(['slug' => $festivalSlug]);

        $stages = [];
        foreach ($stageRepository->findBy(['slug' => $stageSlug, 'festival' => $festivalEntity]) as $stage) {
            $stages[] = [
                'id' => $stage->getId(),
                'name' => $stage->getName(),
                'slug' => $stage->getSlug(),
                'location' => $stage->getLocation(),
            ];
        }

        return new JsonResponse($stages);
    }

    #[Route('/api/festivals/{festivalSlug}/stages/{stageSlug}/timeslots', name: 'festival-stages-timeslots')]
    public function listFestivalStagesTimeSlots(FestivalRepository $festivalRepository, StageRepository $stageRepository, TimeSlotRepository $timeSlotRepository, string $festivalSlug, string $stageSlug): JsonResponse
    {
        $serializer = new SerializerService();
        $festivals = $festivalRepository->findOneBy(['slug' => $festivalSlug]);

        $stage = $stageRepository->findBy(['slug' => $stageSlug, 'festival' => $festivals]);
        $timeSlots = $timeSlotRepository->findBy(['stage' => $stage], ['startTime' => 'ASC']);

        $timeSlotArray = [];

        foreach($timeSlots as $timeslot) {
        $timeSlotArray[] = ['startTime' => $timeslot->getStartTime(),
        'endTime' => $timeslot->getEndTime(),
        'band' => $timeslot->getBand()->getName(),
        'bandSlug' => $timeslot->getBand()->getSlug(),
        'bandId' => $timeslot->getBand()->getId(),
        'stage' => $timeslot->getStage()->getName(),
        'stageSlug' => $timeslot->getStage()->getSlug()];
        };

        return JsonResponse::fromJsonString($serializer->serializeCircularReferenceJson($timeSlotArray));
    }

    #[Route('/api/festivals/{festivalSlug}/stages/{stageSlug}/upnext', name: 'festival-stages-upnext')]
    public function listFestivalStagesUpNext(FestivalRepository $festivalRepository, StageRepository $stageRepository, TimeSlotRepository $timeSlotRepository, string $festivalSlug, string $stageSlug): JsonResponse
    {
        $serializer = new SerializerService();
//        $festivals = $festivalRepository->findOneBy(['slug' => $festivalSlug]);

        date_default_timezone_set('Europe/Berlin');

        $dateNow = date('Y-m-d H:i:00', time());

//        $stage = $stageRepository->findBy(['slug' => $stageSlug, 'festival' => $festivals]);
        $timeSlot = $timeSlotRepository->findNextTimeSlots($dateNow);

        $upNext = [];

        $upNext[] = [
            'startTime' => $timeSlot->getStartTime()->format('H:i:s d.m.Y '),
            'band' => $timeSlot->getBand()->getName(),
            'stage' => $timeSlot->getStage()->getName(),
        ];

        return JsonResponse::fromJsonString($serializer->serializeCircularReferenceJson($upNext));
    }
}
