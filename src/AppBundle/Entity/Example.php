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
 * @ORM\Table(name="example")
 */
class Example {
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var string
     * @ORM\Column(type="string", length=400)
     */
    private $english;

    /**
     * @var string
     * @ORM\Column(type="string", length=400)
     */
    private $russian;

    /**
     * @var Word
     * @ORM\ManyToOne(targetEntity="Word", inversedBy="examples")
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
     * Set english
     *
     * @param string $english
     *
     * @return Example
     */
    public function setEnglish($english)
    {
        $this->english = $english;

        return $this;
    }

    /**
     * Get english
     *
     * @return string
     */
    public function getEnglish()
    {
        return $this->english;
    }

    /**
     * Set russian
     *
     * @param string $russian
     *
     * @return Example
     */
    public function setRussian($russian)
    {
        $this->russian = $russian;

        return $this;
    }

    /**
     * Get russian
     *
     * @return string
     */
    public function getRussian()
    {
        return $this->russian;
    }

    /**
     * Set word
     *
     * @param \AppBundle\Entity\Word $word
     *
     * @return Example
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
