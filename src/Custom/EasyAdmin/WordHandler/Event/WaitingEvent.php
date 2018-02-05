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

class WaitingEvent extends AbortableEvent{
    const NAME = 'word_handler.waiting_event';
    private $secondsWaiting;

    public function __constuct(Word $word, WordAttribute $attribute, $secondsWaiting){
        parent::__constuct($word, $attribute);
        $this->secondsWaiting = $secondsWaiting;
    }
    public  function getSecondsWaiting(){
        return $this->secondsWaiting;
    }
} 