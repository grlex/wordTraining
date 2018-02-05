<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 09.01.2018
 * Time: 15:08
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

abstract class WordAttribute {
    const STATUS_DONE = 1;
    const STATUS_UNAVAILABLE = 2;
    const STATUS_AUTO = 3;
    const STATUS_AUTO_LOADING = 4;
    const STATUS_LINK = 5;         // WordPronounce
    const STATUS_LINK_LOADING = 6; // WordPronounce
    const STATUS_MIC = 7;          // Word Pronounce

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    protected $status;


    public function getId()
    {
        return $this->id;
    }

    public function isNew(){
        return is_null($this->id);
    }

    public function getStatus(){
        return $this->status;
    }

    public function setStatus($status){
        $this->status = $status;
        return $this;
    }

    public function __construct(){
        $this->status = self::STATUS_DONE;
    }

    abstract public  function getText();
    abstract public  function setText($text);
} 