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
 * @ORM\Entity(repositoryClass="WordAttributeRepository")
 */
class WordTranscription extends WordAttribute{

    /**
     * @var string
     * @ORM\Column(type="string", length=200, unique=true, nullable=true)
     */
    private $text;

    /**
     * @var Word
     * @ORM\OneToMany(targetEntity="Word", mappedBy="transcription", cascade={"persist", "merge", "detach"})
     */
    private $words;


    public function __toString(){
        return $this->text;
    }

    /**
     * Constructor
     */
    public function __construct($transcriptionText = null)
    {
        parent::__construct();
        $this->words = new \Doctrine\Common\Collections\ArrayCollection();
        $this->text = $transcriptionText;
    }

    /* ==========================   =============== */



    /**
     * Set text
     *
     * @param string $text
     *
     * @return WordTranscription
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
     * @return WordTranscription
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
