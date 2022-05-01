<?php
declare(strict_types=1);

namespace App\Controller\Api\BpOrgCheck;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Annotations as SWG;
use App\Service\BpOrgCheck\BpOrgHandler;

/**
 * Controller for this apps API
 */
class CheckingController extends AbstractController
{
    /**
     * Call this to trigger fetching and processing all (new) messages for the given BetterPlace event.
     *
     * @Route("/bporg/check/{type}/{id}", methods={"GET"}, name="bporg_check_docheck")
     *
     * @SWG\Tag(name="bp-org-check")
     *
     * @param string $type
     * @param string $id
     * @param BpOrgHandler $bpOrgHandler
     * @return JsonResponse
     */
    public function checkAction(string $type, string $id, BpOrgHandler $bpOrgHandler): JsonResponse
    {
        $bporgType = $type == 'project' ? BpOrgHandler::BPORG_EVENT_TYPE_PROJECT : BpOrgHandler::BPORG_EVENT_TYPE_FUNDRAISER;
        $bpOrgHandler->fetchDonationMessages($bporgType, $id);

        return new JsonResponse(['done' => true], 200, [], false);
    }
}
