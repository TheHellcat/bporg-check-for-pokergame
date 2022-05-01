<?php
declare(strict_types=1);

namespace App\Service\BpOrgCheck;

use App\Entity\BpOrgCheck\Config;
use App\Entity\BpOrgCheck\Message;
use App\Entity\BpOrgCheck\PlayerInventory;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client as HttpClient;
use App\Repository\ConfigRepository;
use App\Repository\MessageRepository;

/**
 * Main service class that handles all the work for checking the BetterPlace API for poker chips balance to add to our players
 */
class BpOrgHandler
{
    const BPORG_EVENT_TYPE_PROJECT = 'projects';
    const BPORG_EVENT_TYPE_FUNDRAISER = 'fundraising_events';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Class contructor - you might've guessed it xD
     */
    public function __construct(EntityManagerInterface $entityManager, ConfigRepository $configRepository, MessageRepository $messageRepository)
    {
        $this->entityManager = $entityManager;
        $this->configRepository = $configRepository;
        $this->messageRepository = $messageRepository;
        $this->httpClient = new HttpClient;
    }

    /**
     * Call the BetterPlace API to fetch all donation details and process the messages for determining if and how much
     * balance to add to which of our players
     *
     * @param string $bpOrgType
     * @param string $bpOrgId
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function fetchDonationMessages(string $bpOrgType, string $bpOrgId)
    {
        $currentUpdateTime = time();

        // check for when we did the last update run
        $lastUpdate = $this->configRepository->find($bpOrgType . '-' . $bpOrgId . '-lastUpdate');
        if( !$lastUpdate )
        {
            // seems like we never fetched messages for this, so far, create a new last-updated entry
            $lastUpdate = new Config();
            $lastUpdate->setKey($bpOrgType . '-' . $bpOrgId . '-lastUpdate');
            $lastUpdate->setValue('0');
        }

        // URL for calling the BetterPlace API, constructed with the event type and ID, but still lacking the pagination page as that one will be appended when actually performing the call
        // TODO: sanitize type and ID parameters before slamming them into the query URL!
        $apiUrl = 'https://api.betterplace.org/de/api_v4/' . $bpOrgType . '/' . $bpOrgId . '/opinions.json?facets=has_message%3Atrue&order=confirmed_at%3ADESC&per_page=100&page=';

        $httpHeaders = [
//            'Content-Type' => 'application/json'
        ];

        $httpOptions = [
//            'body' => $data,
            'headers' => $httpHeaders
        ];

        // we're now calling the BetterPlace API in a loop, incrementing the "page" of the pagination with every call,
        // to be able to fetch more messages than the API caps per one call (200 as of this writing)
        $i = 0;
        $keepFetching = true;
        while( $keepFetching )
        {
            $i++;

            // emergency exit, in case there's too much of a backlog
            if( $i > 20 )
            {
                $keepFetching = false;
            }

            // Mr. Worf, FIRE! (this is where we make the actual API call to BetterPlace)
            $responseBody = '';
            $httpResponse = $this->httpClient->get(
                // here we append the current "page" (that we count in $i) we're on, from the pagination, to the call URL
                $apiUrl . $i,
                $httpOptions
            );
            if (null !== $httpResponse) {
                $responseBody = $httpResponse->getBody()->getContents();
                // TODO: proper error/failure handling!
            }

            // TODO: this would be WAY better to deserialize this via JMS serializer into a proper and neat data object,
            // but since we're a) super lazy and b) this is only a quick-and-dirty one-time-use app,
            // we just use this. It's not nice, but for now simply does the job w/o over-engineering it too much.
            $responseData = json_decode($responseBody);

            if( count( $responseData->data ) < 2 )
            {
                // we didn't get any (more) data in the data array of the BP API response.
                // this means there are no (more) messages left to be fetched, we got them all.
                $keepFetching = false;
            }

            foreach( $responseData->data as $opinionData )
            {
                $t = new \DateTime($opinionData->confirmed_at);
                $ts = $t->getTimestamp();
                if( $ts < (int)$lastUpdate->getValue() )
                {
                    // The timestamp of the donation, this message belongs to, is older than when we last updated.
                    // This means we can stop here, because everything from this point on we should already have processed
                    $keepFetching = false;
                }

                $messageEntry = $this->messageRepository->findOneBy(['bporgId' => (string)($opinionData->id)]);
                // ^ v  Here we check if we already got/processed this message (using the unique donation ID from BetterPlace)
                //      to prevent dupes, i.e. processing (and crediting to a player) a message multiple times.
                //      (Oh, and we also check if the donation message contains any text at all, 'cause if not we can skip this as well.)
                if( (!$messageEntry) && (strlen($opinionData->message) > 1) )
                {
                    $newMessageEntry = new Message();
                    $newMessageEntry->setCentValue( isset($opinionData->donated_amount_in_cents) ? $opinionData->donated_amount_in_cents : 0 );
                    $newMessageEntry->setMessage( $opinionData->message );
                    $newMessageEntry->setBporgId( (string)$opinionData->id );
                    $newMessageEntry->setTimestamp( $currentUpdateTime );

                    $this->entityManager->persist($newMessageEntry);

                    $player = $this->determinePlayer($newMessageEntry->getMessage());
                    if( $player )
                    {
                        // this is the inventory entry to keep track of the current balance that can be given out to the player,
                        // there will be negative amount entries added for this when poker chips are given out
                        $newInventoryEntry = new PlayerInventory();
                        $newInventoryEntry->setPlayer( $player );
                        $newInventoryEntry->setItemId( $bpOrgType . '-' . $bpOrgId . '-funds' );
                        $newInventoryEntry->setAmount( $newMessageEntry->getCentValue() );
                        $newInventoryEntry->setTimestamp( $currentUpdateTime );

                        // this is the inventory entry to easily keep track of the "total all time" amount ever accredited to a player,
                        // there will never be a reducing entry for this, so this is basically the sum of everything the player ever got.
                        $newInventoryAllTimeEntry = new PlayerInventory();
                        $newInventoryAllTimeEntry->setPlayer( $player );
                        $newInventoryAllTimeEntry->setItemId( $bpOrgType . '-' . $bpOrgId . '-funds-alltime' );
                        $newInventoryAllTimeEntry->setAmount( $newMessageEntry->getCentValue() );
                        $newInventoryAllTimeEntry->setTimestamp( $currentUpdateTime );

                        $this->entityManager->persist($newInventoryEntry);
                        $this->entityManager->persist($newInventoryAllTimeEntry);
                    }
                }
            }
        }

        $lastUpdate->setValue((string)$currentUpdateTime);
        $this->entityManager->persist($lastUpdate);

        $this->entityManager->flush();
    }

    /**
     * Accredits a donation amount to the given player, i.e. add it to their poker chips inventory.
     * Negative amounts reduce the available balance again, e.g. when chips have been given out.
     * If the amount is positive it will also be added to the "total all time" amount.
     *
     * @param string $player
     * @param string $bpOrgType
     * @param string $bpOrgId
     * @param int $amount
     * @return void
     */
    public function changePlayerFunds(string $player, string $bpOrgType, string $bpOrgId, int $amount)
    {
        $newInventoryEntry = new PlayerInventory();
        $newInventoryEntry->setPlayer( $player );
        $newInventoryEntry->setItemId( $bpOrgType . '-' . $bpOrgId . '-funds' );
        $newInventoryEntry->setAmount( $amount );
        $newInventoryEntry->setTimestamp( time() );
        $this->entityManager->persist($newInventoryEntry);

        if( $amount > 0 )
        {
            $newInventoryAllTimeEntry = new PlayerInventory();
            $newInventoryAllTimeEntry->setPlayer( $player );
            $newInventoryAllTimeEntry->setItemId( $bpOrgType . '-' . $bpOrgId . '-funds-alltime' );
            $newInventoryAllTimeEntry->setAmount( $amount );
            $newInventoryAllTimeEntry->setTimestamp( time() );
            $this->entityManager->persist($newInventoryAllTimeEntry);
        }

        $this->entityManager->flush();
    }

    /**
     * Quite fugly, quick and dirty function to determine for which player a donation message is meant to be counted for
     *
     * @param string $message
     * @return string|null
     */
    protected function determinePlayer(string $message): ?string
    {
        $player = null;

        if(
            (stripos($message, "Reichart") !== false) ||
            (stripos($message, "Stefan") !== false) ||
            (stripos($message, "Steffan") !== false) ||
            (stripos($message, "Reichert") !== false) ||
            (stripos($message, "Stephan") !== false)
        )
        {
            $player = "DerReichart";
        }

        if(
            (stripos($message, "Fuchsia") !== false) ||
            (stripos($message, "Manu") !== false)
        )
        {
            $player = "TheFuchsia";
        }

        if(
            (stripos($message, "Carla") !== false) ||
            (stripos($message, "Raya") !== false)
        )
        {
            $player = "Raya";
        }

        if(
            (stripos($message, "Nancy") !== false) ||
            (stripos($message, "Nency") !== false)
        )
        {
            $player = "Nancy";
        }

        if(
            (stripos($message, "Seb") !== false) ||
            (stripos($message, "Sep") !== false) ||
            (stripos($message, "PietSmiet") !== false)
        )
        {
            $player = "Sep";
        }

        if(
            (stripos($message, "#Hellcat") !== false) ||
            (stripos($message, "#TheRealHellcat") !== false) ||
            (stripos($message, "Katze") !== false) ||
            (stripos($message, "Micha") !== false) ||
            (stripos($message, "Hellcat") !== false) ||
            (stripos($message, "Kadse") !== false)
        )
        {
            $player = "TheRealHellcat";
        }

        if(
            (stripos($message, "#Test") !== false)
        )
        {
            $player = "Test";
        }

        return $player;
    }

    /**
     * Global factor for in how many chips a given donation amount (in cents) results
     *
     * @return float
     */
    public function getCentToChipFactor(): float
    {
        $cents = 100;
        $factor = 1;
        return (1/$cents) * $factor;
    }

    /**
     * Possible/optional individual poker chips factors for eah player.
     *
     * @param string $player
     * @return float
     */
    public function getPlayerCentToChipFactor(string $player): float
    {
        $playerChipFactor = 1;
        switch( $player )
        {
            case "DerReichart":
                $playerChipFactor = 1;
                break;

            case "TheRealHellcat":
                $playerChipFactor = 1;
                break;

            case "Test":
                $playerChipFactor = 0.5;
                break;

            default:
                $playerChipFactor = 1;
                break;
        }

        return $playerChipFactor;
    }
}
