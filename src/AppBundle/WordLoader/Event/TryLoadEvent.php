<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 22.12.2017
 * Time: 14:49
 */

namespace AppBundle\WordLoader\Event;


use Symfony\Component\EventDispatcher\Event;

class TryLoadEvent extends Event{
    const NAME = 'word_loader.try_load';
    private $attempts;
    private $word;
    private $aborted;
    public function __constuct($word, $attempts){
        $this->word = $word;
        $this->attempts = $attempts;
        $this->aborted = false;
    }
    public  function getAttemptCount(){
        return $this->attempts;
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