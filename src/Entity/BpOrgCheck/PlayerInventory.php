<?php
declare(strict_types=1);

namespace App\Entity\BpOrgCheck;

use App\Repository\PlayerInventoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass=PlayerInventoryRepository::class)
 * @ORM\Table(name="player_inventory")
 */
class PlayerInventory
{
    /**
     * @ORM\Id
     * @ORM\Column(name="entry_id", type="string", unique=true, length=64)
     * @ORM\GeneratedValue(strategy="UUID")
     *
     * @var string
     */
    protected $entryId;

    /**
     * @ORM\Column(name="player", type="string", length=128)
     *
     * @var string
     */
    protected $player;

    /**
     * @ORM\Column(name="item_id", type="string", length=64)
     *
     * @var string
     */
    protected $itemId;

    /**
     * @ORM\Column(name="amount", type="integer")
     *
     * @var int
     */
    protected $amount;

    /**
     * @ORM\Column(name="timestamp", type="integer")
     *
     * @var int
     */
    protected $timestamp;

    /**
     * @return string
     */
    public function getEntryId(): string
    {
        return $this->entryId;
    }

    /**
     * @param string $entryId
     */
    public function setEntryId(string $entryId): void
    {
        $this->entryId = $entryId;
    }

    /**
     * @return string
     */
    public function getPlayer(): string
    {
        return $this->player;
    }

    /**
     * @param string $player
     */
    public function setPlayer(string $player): void
    {
        $this->player = $player;
    }

    /**
     * @return string
     */
    public function getItemId(): string
    {
        return $this->itemId;
    }

    /**
     * @param string $itemId
     */
    public function setItemId(string $itemId): void
    {
        $this->itemId = $itemId;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }
}
