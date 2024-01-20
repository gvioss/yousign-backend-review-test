<?php

namespace App\GitHubStatistics\Infrastructure\Controller\Search;

use App\GitHubStatistics\Application\Dto\SearchInput;
use App\GitHubStatistics\Domain\Repository\ReadEventRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class SearchController
{
    public function __construct(
        private readonly ReadEventRepository $repository,
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route(path: '/api/search', name: 'api_search', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $searchInput = $this->serializer->denormalize($request->query->all(), SearchInput::class);

        $countByType = $this->repository->countByType($searchInput);

        $data = [
            'meta' => [
                'totalEvents' => $this->repository->countAll($searchInput),
                'totalPullRequests' => $countByType['pullRequest'] ?? 0,
                'totalCommits' => $countByType['commit'] ?? 0,
                'totalComments' => $countByType['comment'] ?? 0,
            ],
            'data' => [
                'events' => $this->repository->getLatest($searchInput),
                'stats' => $this->repository->statsByTypePerHour($searchInput)
            ]
        ];

        return new JsonResponse($data);
    }
}
