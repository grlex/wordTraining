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
 * @ORM\Entity(repositoryClass="WordAttributeRepository")
 */
class WordTranslation extends WordAttribute {

    /**
     * @var string
     * @ORM\Column(type="string", length=200, unique=true, nullable=true)
     */
    private $text;

    /**
     * @var Word
     * @ORM\OneToMany(targetEntity="Word", mappedBy="translation", cascade={"persist", "merge", "detach"})
     */
    private $words;


    public function __toString(){
        return $this->text;
    }

    /**
     * Constructor
     */
    public function __construct($translationText=null)
    {
        parent::__construct();
        $this->words = new \Doctrine\Common\Collections\ArrayCollection();
        $this->text = $translationText;
    }

    /* ==========================   =============== */



    /**
     * Set text
     *
     * @param string $text
     *
     * @return WordTranslation
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Add word
     *
     * @param \AppBundle\Entity\Word $word
     *
     * @return WordTranslation
     */
    public function addWord(\AppBundle\Entity\Word $word)
    {
        $this->words[] = $word;

        return $this;
    }

    /**
     * Remove word
     *
     * @param \AppBundle\Entity\Word $word
     */
    public function removeWord(\AppBundle\Entity\Word $word)
    {
        $this->words->removeElement($word);
    }

    /**
     * Get words
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWords()
    {
        return $this->words;
    }
}
