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

class WordRepository extends EntityRepository {
    public function setStatus($id, $status){
        return $this->createQueryBuilder('w')
            ->update(Word::class, 'w')
            ->set('w.status', $status)
            ->where('w.id = :id')
            ->setParameter(':id', $id)
            ->getQuery()
            ->execute();
    }
    public function getStatus($id){
        return $this->createQueryBuilder('w')
            ->select('w.status')
            ->where('w.id = :id')
            ->setParameter(':id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

