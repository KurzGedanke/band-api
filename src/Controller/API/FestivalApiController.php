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
        $serializer = new SerializerService();
        $festivals = $festivalRepository->findAll();

        return JsonResponse::fromJsonString($serializer->serializeCircularReferenceJson($festivals));
    }
    
    #[Route('/api/festival/{festival}/{bandSlug}', name: 'festival-band')]
    public function listFestivalBand(BandRepository $bandRepository, string $bandSlug, string $festival, Request $request): JsonResponse
    {
        $serializer = new SerializerService();
        $festivalBand = $bandRepository->findOneBy(['slug' => $bandSlug]);
        $fesivalBandTimeSlot = $festivalBand->getTimeSlots() ?? [];
        
        $bandArray['band'] = [
          'id' => $festivalBand->getId(),
          'name' => $festivalBand->getName(),
          'gerne' => $festivalBand->getGenre(),
          'logo' => $request->getSchemeAndHttpHost() . '/images/band/logos/' . $festivalBand->getLogo(),
          'image' => $request->getSchemeAndHttpHost() . '/images/band/images/' . $festivalBand->getImage(),
          'instagram' => $festivalBand->getInstagram(),
          'spotify' => $festivalBand->getSpotify(),
          'appleMusic' => $festivalBand->getAppleMusic(),
          'bandcamp' => $festivalBand->getBandcamp(),
          'descroption' => $festivalBand->getDescription(),
        ];
        
        if(isset($fesivalBandTimeSlot)){
          foreach($fesivalBandTimeSlot as $timeslot) {
            if($timeslot->getFestival()->getName() === $festival)
              $bandTimeSlot = $timeslot;
          };
          
          $bandArray['startTime'] = $bandTimeSlot->getStartTime();
          $bandArray['endTime'] = $bandTimeSlot->getEndTime();
          $bandArray['stage'] = $bandTimeSlot->getStage()->getName();
        }

        return JsonResponse::fromJsonString($serializer->serializeCircularReferenceJson($bandArray));
    }

    #[Route('/api/festivals/{festival}/bands', name: 'festival-bands')]
    public function listFestivalBands(FestivalRepository $festivalRepository, string $festival): JsonResponse
    {
        $serializer = new SerializerService();
        $festivalBands = $festivalRepository->findOneBy(['name' => $festival]);

        return JsonResponse::fromJsonString($serializer->serializeCircularReferenceJson($festivalBands->getBands()));
    }
    
    #[Route('/api/festivals/{festival}/stages', name: 'festival-stages')]
    public function listFestivalStages(FestivalRepository $festivalRepository, string $festival): JsonResponse
    {
        $serializer = new SerializerService();
        $festivalStages = $festivalRepository->findOneBy(['name' => $festival]);
    
        return JsonResponse::fromJsonString($serializer->serializeCircularReferenceJson($festivalStages->getStages()));
    }
    
    #[Route('/api/festivals/{festival}/stages/{stageName}', name: 'festival-stages-info')]
    public function listFestivalStagesInfo(FestivalRepository $festivalRepository, StageRepository $stageRepository, string $festival, string $stageName): JsonResponse
    {
        $serializer = new SerializerService();
        $festivals = $festivalRepository->findOneBy(['name' => $festival]);

        $stage = $stageRepository->findBy(['name' => $stageName, 'festival' => $festivals]);
    
        return JsonResponse::fromJsonString($serializer->serializeCircularReferenceJson($stage));
    }
    
    #[Route('/api/festivals/{festival}/stages/{stageName}/timeslots', name: 'festival-stages-timeslots')]
    public function listFestivalStagesTimeSlots(FestivalRepository $festivalRepository, StageRepository $stageRepository, TimeSlotRepository $timeSlotRepository, string $festival, string $stageName): JsonResponse
    {
        $serializer = new SerializerService();
        $festivals = $festivalRepository->findOneBy(['name' => $festival]);
    
        $stage = $stageRepository->findBy(['name' => $stageName, 'festival' => $festivals]);
        $timeSlots = $timeSlotRepository->findBy(['stage' => $stage], ['startTime' => 'DESC']);
        
        $timeSlotArray = [];
        
        foreach($timeSlots as $timeslot) {
        $timeSlotArray[] = ['startTime' => $timeslot->getStartTime(),
        'endTime' => $timeslot->getEndTime(),
        'band' => $timeslot->getBand()->getName(),
        'bandSlug' => $timeslot->getBand()->getSlug(),
        'bandId' => $timeslot->getBand()->getId(),
        'stage' => $timeslot->getStage()->getName()];
        };
        
        return JsonResponse::fromJsonString($serializer->serializeCircularReferenceJson($timeSlotArray));
    }
}
