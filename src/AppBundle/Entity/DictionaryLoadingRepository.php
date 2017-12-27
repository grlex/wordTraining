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

class DictionaryLoadingRepository extends EntityRepository {
    public function updateLoaded($id, $loaded){
        return $this->createQueryBuilder('dl')
            ->update(DictionaryLoading::class, 'dl')
            ->set('dl.loaded', $loaded)
            ->where('dl.id = :id')
            ->setParameter(':id', $id)
            ->getQuery()
            ->execute();
    }
    public function setStatus($id, $status){
        return $this->createQueryBuilder('dl')
            ->update(DictionaryLoading::class, 'dl')
            ->set('dl.status', $status)
            ->where('dl.id = :id')
            ->setParameter(':id', $id)
            ->getQuery()
            ->execute();
    }
    public function getStatus($id){
        return $this->createQueryBuilder('dl')
            ->select('dl.status')
            ->where('dl.id = :id')
            ->setParameter(':id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

