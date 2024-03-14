<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\User;
use App\Entity\UsersCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 *
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    public function add(Card $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Card $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getCardsUsers(EntityManagerInterface $entityManager, $client_id): array{

        $qb = $entityManager->createQueryBuilder();

        $qbActiveUsers = $entityManager->createQueryBuilder();
        $qbInactiveUsers = $entityManager->createQueryBuilder();

        $qb->select('c')
            ->from(Card::class, 'c')
            ->leftJoin(UsersCard::class, 'uc', 'WITH', 'uc.card = c.id AND uc.user = :user_id')
            ->where('c.active = 1')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull('uc.id'),
                $qb->expr()->andX(
                    $qb->expr()->eq('c.type', ':card_type')
                )
            ))
            ->setParameter('user_id', $client_id)
            ->setParameter('card_type', 'Giftcard');

        $query = $qb->getQuery();

        return $query->getResult();

    }

//    /**
//     * @return Card[] Returns an array of Card objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Card
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
