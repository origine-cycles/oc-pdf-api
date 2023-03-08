<?php

namespace App\Repository;

use App\Entity\I18n;
use App\Entity\Langue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method I18n|null find($id, $lockMode = null, $lockVersion = null)
 * @method I18n|null findOneBy(array $criteria, array $orderBy = null)
 * @method I18n[]    findAll()
 * @method I18n[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class I18nRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, I18n::class);
    }


    public function findContenuLang($locale,$type)
    {
        $return = [];
        $result = $this->createQueryBuilder('i')
            ->andWhere('i.idLang = :locale')
            ->andWhere('i.page = :type')
            ->setParameter('locale', $locale)
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult()
            ;

        foreach($result as $key => $value){
            $return[$value->getCode()] = $value->getValeur();
        }

        return (object) $return;
    }

    public function findDistinc()
    {
        return $this->createQueryBuilder('i')
            ->select('i.page')
            ->where('i.idLang = 1')
            ->distinct()
            ->getQuery()
            ->getResult()
            ;
    }
    // /**
    //  * @return I18n[] Returns an array of I18n objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?I18n
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
