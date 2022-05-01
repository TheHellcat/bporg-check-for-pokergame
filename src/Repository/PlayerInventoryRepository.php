<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\BpOrgCheck\PlayerInventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlayerInventory|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerInventory|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerInventory[]    findAll()
 * @method PlayerInventory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerInventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerInventory::class);
    }

    /**
     * @param string $itemId
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTotalAmountForAllPlayers(string $itemId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT player, sum(amount) AS total_amount FROM player_inventory WHERE item_id = :itemId GROUP BY player ORDER BY player ASC;';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['itemId' => $itemId]);

        return $resultSet->fetchAllAssociative();
    }
}
