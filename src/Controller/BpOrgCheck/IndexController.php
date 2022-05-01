<?php
declare(strict_types=1);

namespace App\Controller\BpOrgCheck;

use App\Repository\PlayerInventoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use OpenApi\Annotations as SWG;
use App\Service\BpOrgCheck\BpOrgHandler;

/**
 * Controller for the web frontend of this application
 */
class IndexController extends AbstractController
{
    /**
     * Authenticate/login to be able to access the bank overview page
     *
     * @Route("/bporg/log-me-in/{user}/{pass}", methods={"GET"}, name="bporg_fe_login")
     * @Template()
     */
    public function loginAction(string $user, string $pass, Request $request, BpOrgHandler $bpOrgHandler, RequestStack $requestStack, PlayerInventoryRepository $playerInventoryRepository)
    {
        $status = '-';

        if( ($user == 'bank') && (password_verify($pass, '$2y$10$25dwk9uPlTbqeBFvo8l/7.736sBjA8BAGwJ2qQ8osK6pjtNPAOCJO')) )
        {
            $requestStack->getSession()->set('login', time());
            $status = 'ok';
        }

        return [
            'status' => $status
        ];
    }

    /**
     * The bank overview page
     *
     * @Route("/bporg/overview/{bpEventType}/{bpEventId}", methods={"GET"}, name="bporg_fe_index")
     * @Template()
     */
    public function indexAction(string $bpEventType, string $bpEventId, Request $request, RequestStack $requestStack, BpOrgHandler $bpOrgHandler, PlayerInventoryRepository $playerInventoryRepository): array
    {
        $login = $requestStack->getSession()->get('login', 0);
        if( !($login > time()-(60*60*12)) )
        {
            // HAHAA! Cheap man's access prevention, and by cheat I mean super, mega lazy xD
            // Instead of a halfway decent "access denied" page or message, we just make the whole thing crash and cause a 500 server error.
            // In case you haven't guessed it already: THIS IS NOT THE WAY TO HANDLE/DO THIS!
            $n = null;
            $n->goCrash();
            return [];
        }

        $playChipFactor = $bpOrgHandler->getCentToChipFactor();

        $bporgType = $bpEventType == 'project' ? BpOrgHandler::BPORG_EVENT_TYPE_PROJECT : BpOrgHandler::BPORG_EVENT_TYPE_FUNDRAISER;
        $inventoryItemId = $bporgType . '-' . $bpEventId . '-funds';
        $inventoryAllTimeItemId = $bporgType . '-' . $bpEventId . '-funds-alltime';

        $playerData = $playerInventoryRepository->getTotalAmountForAllPlayers($inventoryItemId);
        $playerDataAllTime = $playerInventoryRepository->getTotalAmountForAllPlayers($inventoryAllTimeItemId);

        // fetch all all-time amounts for all players and put them into the player balance array as well
        foreach( $playerData as $index => $player )
        {
            foreach( $playerDataAllTime as $allTimeData )
            {
                if( $allTimeData['player'] == $player['player'] )
                {
                    $playerData[$index]['total_alltime'] = $allTimeData['total_amount'];
                    breaK;
                }
            }

            $playerChipFactor = $bpOrgHandler->getPlayerCentToChipFactor($player['player']);

            $playerData[$index]['playChips'] = $player['total_amount'] * $playChipFactor * $playerChipFactor;
        }

        return [
            'playerData' => $playerData,
            'eventType' => $bpEventType,
            'eventId' => $bpEventId
        ];
    }
}
