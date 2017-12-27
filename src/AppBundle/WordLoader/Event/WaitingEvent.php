<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 22.12.2017
 * Time: 14:49
 */

namespace AppBundle\WordLoader\Event;


use Symfony\Component\EventDispatcher\Event;

class WaitingEvent extends Event{
    const NAME = 'word_loader.waiting_event';
    private $secondsWaiting;
    private $word;
    private $aborted;
    public function __constuct($word, $secondsWaiting){
        $this->word = $word;
        $this->secondsWaiting = $secondsWaiting;
        $this->aborted = false;
    }
    public  function getSecondsWaiting(){
        return $this->secondsWaiting;
    }
    public function getWord(){
        return $this->word;
    }
    public function isAborted(){
        return $this->aborted;
    }
    public function abort(){
        $this->aborted = true;
    }
} 