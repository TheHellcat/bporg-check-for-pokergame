<?php
declare(strict_types=1);

namespace App\Entity\BpOrgCheck;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 * @ORM\Table(name="messages",
 *     indexes={
 *         @ORM\Index(name="idx_bporgid", columns={"bporg_id"})
 *     }
 * )
 */
class Message
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
     * @ORM\Column(name="bporg_id", type="string", length=64)
     *
     * @var string
     */
    protected $bporgId;

    /**
     * @ORM\Column(name="message", type="text")
     *
     * @var string
     */
    protected $message;

    /**
     * @ORM\Column(name="cent_value", type="integer")
     *
     * @var int
     */
    protected $centValue;

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
    public function getBporgId(): string
    {
        return $this->bporgId;
    }

    /**
     * @param string $bporgId
     */
    public function setBporgId(string $bporgId): void
    {
        $this->bporgId = $bporgId;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getCentValue(): int
    {
        return $this->centValue;
    }

    /**
     * @param int $centValue
     */
    public function setCentValue(int $centValue): void
    {
        $this->centValue = $centValue;
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
