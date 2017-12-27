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

class DictionaryRepository extends EntityRepository {
    public function findWithLoadingInfo($id){
         return  $this->createQueryBuilder('d')
            ->select('d, dl')
            ->join('d.loadingInfo', 'dl')
            ->where('d.id = :id')
            ->setParameter(':id', $id)
            ->getQuery()
            //->getSQL()
            ->getOneOrNullResult();
    }
}

