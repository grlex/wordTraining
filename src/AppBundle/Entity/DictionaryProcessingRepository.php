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

class DictionaryProcessingRepository extends EntityRepository {
    public function updateProcessed($id, $processed){
        return $this->createQueryBuilder('dp')
            ->update(DictionaryProcessing::class, 'dp')
            ->set('dp.processed', $processed)
            ->where('dp.id = :id')
            ->setParameter(':id', $id)
            ->getQuery()
            ->execute();
    }
    public function setStatus($id, $status){
        return $this->createQueryBuilder('dp')
            ->update(DictionaryProcessing::class, 'dp')
            ->set('dp.status', $status)
            ->where('dp.id = :id')
            ->setParameter(':id', $id)
            ->getQuery()
            ->execute();
    }
    public function getStatus($id){
        return $this->createQueryBuilder('dp')
            ->select('dp.status')
            ->where('dp.id = :id')
            ->setParameter(':id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

