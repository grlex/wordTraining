<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 30.11.2017
 * Time: 14:46
 */

namespace AppBundle\WordLoader;


use Symfony\Component\EventDispatcher\Event;

class LoadingEvent extends Event {
    const NAME = 'word_loader.loading';
    private $loadedWords;
    private $totalWords;
    private $canceled;
    public function __construct($loadedWords, $totalWords){
        $this->loadedWords = $loadedWords;
        $this->totalWords = $totalWords;
        $this->canceled = false;
    }
    public function getTotalWords(){
        return $this->totalWords;
    }
    public function getLoadedWords(){
        return $this->loadedWords;
    }
    public function isCanceled(){
        return $this->canceled;
    }
    public function cancel(){
        $this->canceled = true;
        return $this;
    }
} 