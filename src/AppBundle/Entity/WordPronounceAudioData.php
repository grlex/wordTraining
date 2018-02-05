<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 09.01.2018
 * Time: 12:19
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Word
 * @package AppBundle\Model
 * @ORM\Entity
 */
class WordPronounceAudioData
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="text", unique=true)
     */
    private $data;

    public function __construct($data = null){
        $this->data = $data;
    }

    /** ======================== ======== */

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return WordPronounceAudioData
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

}
