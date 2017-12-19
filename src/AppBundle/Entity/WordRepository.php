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
    public function createQueryBuilder($alias, $indexBy = null)
    {

        $qb = parent::createQueryBuilder($alias, $indexBy);
        $qb->addOrderBy("e.spelling", "ASC");
        //dump($qb->getDQL());die();
        return $qb;
    }


}

