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
use Doctrine\ORM\Query\Expr;

class WordAttributeRepository extends EntityRepository {
    public function getStatus($id){
        return $this->getEntityManager()
            ->createQuery(sprintf('select wa.status from %s wa where wa.id=:id', $this->getClassName()))
            ->setParameter(':id', $id)
            ->getSingleScalarResult();
    }
    public function setStatus($id, $status){
        return $this->getEntityManager()
            ->createQuery(sprintf('update %s wa set wa.status=:status where wa.id=:id', $this->getClassName()))
            ->setParameter(':id', $id)
            ->setParameter(':status', $status)
            ->execute();
    }

    public function findByAudioData($data){
        return $this->createQueryBuilder('wa')
            ->select('wa')
            ->join('wa.audioData', 'ad')
            ->where('ad.data = :data')
            ->setParameter(':data', $data)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

