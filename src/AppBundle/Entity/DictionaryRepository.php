<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 11.12.2017
 * Time: 21:39
 */

namespace AppBundle\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use AppBundle\Entity\Dictionary;
use AppBundle\Entity\DictionaryLoading;
use Doctrine\ORM\Query\Expr;

class DictionaryRepository extends EntityRepository {
    public function findWithProcessingInfo($id){
         return  $this->createQueryBuilder('d')
            ->select('d, dp')
            ->join('d.processingInfo', 'dp')
            ->where('d.id = :id')
            ->setParameter(':id', $id)
            ->getQuery()
            //->getSQL()
            ->getOneOrNullResult();
    }

    public function createListQuery($limit=0){
        $qb = $this->createQueryBuilder('d')
            ->select('d.id, d.name as name');
        if($limit>0){
            $qb->setMaxResults($limit);
        }
        return $qb->getQuery();
    }

    public function createWordListQuery($id = null){
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->from(Word::class, 'w')
            ->select('w')
            ->join('w.dictionary', 'd')
            ->join('w.spelling', 'spelling');
        if(!is_null($id)) {
            $qb->where('d.id = :id')->setParameter(':id', $id);
        }

        return $qb->getQuery();
    }
}

