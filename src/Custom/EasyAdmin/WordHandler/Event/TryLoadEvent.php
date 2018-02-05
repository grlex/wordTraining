<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 22.12.2017
 * Time: 14:49
 */

namespace Custom\EasyAdmin\WordHandler\Event;


use AppBundle\Entity\Word;
use AppBundle\Entity\WordAttribute;
use Symfony\Component\EventDispatcher\Event;

class TryLoadEvent extends AbortableEvent{
    const NAME = 'word_loader.try_load';
    private $attempts;

    public function __constuct(Word $word, WordAttribute $attribute, $attempts){
        parent::__constuct($word, $attribute);
        $this->attempts = $attempts;
    }
    public  function getAttemptCount(){
        return $this->attempts;
    }
} 