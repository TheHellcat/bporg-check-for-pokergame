<?php
declare(strict_types=1);

namespace App\Controller\Api\BpOrgCheck;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Annotations as SWG;
use App\Service\BpOrgCheck\BpOrgHandler;

/**
 * Controller for all player related calls of this applications API
 */
class PlayerController extends AbstractController
{
    /**
     * Call this to change the amount of balance for the given player.
     * (Used from the overview pages "give out chips" option.)
     *
     * @Route("/bporg/player/{bpEventType}/{bpEventId}/{player}/funds/{amount}/{fundsType}", methods={"POST"}, name="bporg_player_changefunds")
     *
     * @SWG\Tag(name="bp-org-player")
     *
     * @param string $bpEventType
     * @param string $bpEventId
     * @param string $player
     * @param int $amount
     * @param BpOrgHandler $bpOrgHandler
     * @return JsonResponse
     */
    public function fundsAction(string $bpEventType, string $bpEventId, string $player, int $amount, string $fundsType, BpOrgHandler $bpOrgHandler): JsonResponse
    {
        // TODO: should something like this be used in a public and/or even production environment, THIS NEEDS SOME KIND OF BEING SECURED!!!
        // everyone who knows the URL can add or subtract ANY amount of balance for ANY player, this was OK for when/where this was originally used,
        // as it only ran locally on the PC it was used on/from, not accessible from anywhere else.
        $bporgType = $bpEventType == 'project' ? BpOrgHandler::BPORG_EVENT_TYPE_PROJECT : BpOrgHandler::BPORG_EVENT_TYPE_FUNDRAISER;

        if( $fundsType == 'chips' )
        {
            $amount *= 1 / ($bpOrgHandler->getCentToChipFactor() * $bpOrgHandler->getPlayerCentToChipFactor($player));
        }

        $bpOrgHandler->changePlayerFunds($player, $bporgType, $bpEventId, (int)$amount);

        return new JsonResponse(['done' => true], 200, [], false);
    }
}
