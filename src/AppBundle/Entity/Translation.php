<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 29.11.2017
 * Time: 13:53
 */

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
/**
 * Class Word
 * @package AppBundle\Model
 * @ORM\Entity
 * @ORM\Table(name="translation")
 */
class Translation {
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var string Название категории
     * @ORM\Column(type="string", length=50)
     */
    private $meaning;

    /**
     * @var Word
     * @ORM\ManyToOne(targetEntity="Word", inversedBy="translations")
     */
    private $word;

    /*public function __toString(){
        return $this->getTranslation();
    }*/

    /* ==========================   =============== */



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
     * Set meaning
     *
     * @param string $meaning
     *
     * @return Translation
     */
    public function setMeaning($meaning)
    {
        $this->meaning = $meaning;

        return $this;
    }

    /**
     * Get meaning
     *
     * @return string
     */
    public function getMeaning()
    {
        return $this->meaning;
    }

    /**
     * Set word
     *
     * @param \AppBundle\Entity\Word $word
     *
     * @return Translation
     */
    public function setWord(\AppBundle\Entity\Word $word = null)
    {
        $this->word = $word;

        return $this;
    }

    /**
     * Get word
     *
     * @return \AppBundle\Entity\Word
     */
    public function getWord()
    {
        return $this->word;
    }
}
