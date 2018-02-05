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

abstract class AbortableEvent extends Event{
    protected $word;
    protected $attribute;
    protected $aborted;
    public function __constuct(Word $word, WordAttribute $attribute){
        $this->word = $word;
        $this->attribute = $attribute;
        $this->aborted = false;
    }
    public function getWord(){
        return $this->word;
    }
    public function getAttribute(){
        return $this->attribute;
    }
    public function isAborted(){
        return $this->aborted;
    }
    public function abort(){
        $this->aborted = true;
    }
} 