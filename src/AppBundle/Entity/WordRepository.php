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

class WordRepository extends EntityRepository {
    public function createListQuery($dictionaryId=null, $limit=0, array $orders=array()){
        $qb = $this->createQueryBuilder('w');
        $qb->select('w');
        $qb->join('w.spelling', 'spelling')
            ->leftJoin('w.translation', 'translation')
            ->leftJoin('w.transcription', 'transcription')
            ->leftJoin('w.pronounce', 'pronounce');

        if(!is_null($dictionaryId)) {
            $qb->join('w.dictionary', 'd')
                ->where('d.id = :id')
                ->setParameter(':id', $dictionaryId);
        }
        if($limit>0) {
            $qb->setMaxResults($limit);
        }
        foreach($orders as $field => $order){
            $qb->orderBy($field, $order);
        }
        return $qb->getQuery();
    }
}

