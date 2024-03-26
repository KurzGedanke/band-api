<?php

namespace App\Controller\API;

use App\Repository\FestivalRepository;
use App\Repository\StageRepository;
use App\Services\SerializerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class FestivalApiController extends AbstractController
{
    #[Route('/api/festivals', name: 'festival-list', methods: ['GET'])]
    public function listFestivals(FestivalRepository $festivalRepository): JsonResponse
    {
        $serializer = new SerializerService();
        $festivals = $festivalRepository->findAll();

        return JsonResponse::fromJsonString($serializer->serializeCircularReferenceJson($festivals));
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
    
    #[Route('/api/festivals/{festival}/stages/{stageName}', name: 'festival-stages-timeslots')]
    public function listFestivalStagesTimeSlots(FestivalRepository $festivalRepository, StageRepository $stageRepository, string $festival, string $stageName): JsonResponse
    {
        $serializer = new SerializerService();
        $festivals = $festivalRepository->findOneBy(['name' => $festival]);

        $stage = $stageRepository->findBy(['name' => $stageName, 'festival' => $festivals]);
    
        return JsonResponse::fromJsonString($serializer->serializeCircularReferenceJson($stage));
    }
}
