<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 11.12.2017
 * Time: 21:39
 */

namespace AppBundle\Entity;


use Doctrine\ORM\EntityRepository;

class WordRepository extends EntityRepository {
    public function findBySpelling($spelling){
        if(is_null($spelling) or !is_string($spelling)) return null;
        return $this->createQueryBuilder('w')
            ->select('w')
            ->where('w.spelling=:spelling')
            ->setMaxResults(1)
            ->setparameter(':spelling', $spelling)
            ->getQuery()
            ->getOneOrNullResult();
    }
} 