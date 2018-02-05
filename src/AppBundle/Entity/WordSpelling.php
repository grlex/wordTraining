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
class WordSpelling extends WordAttribute {

    /**
     * @var string
     * @ORM\Column(type="string", length=200, unique=true, nullable=true)
     */
    private $text;

    /**
     * @var Word
     * @ORM\OneToMany(targetEntity="Word", mappedBy="spelling", cascade={"persist", "merge", "detach"})
     */
    private $words;

    /**
     * @var WordTranslation
     * @ORM\ManyToOne(targetEntity="WordTranslation")
     * @ORM\JoinColumn(nullable=true);
     */
    private $autoTranslation;

    /**
     * @var WordTranscription
     * @ORM\ManyToOne(targetEntity="WordTranscription")
     * @ORM\JoinColumn(nullable=true);
     */
    private $autoTranscription;

    /**
     * @var WordTranscription
     * @ORM\ManyToOne(targetEntity="WordPronounce")
     * @ORM\JoinColumn(nullable=true);
     */
    private $autoPronounce;


    public function __toString(){
        return $this->text;
    }

    /**
     * Constructor
     */
    public function __construct($spellingText='')
    {
        parent::__construct();
        $this->words = new \Doctrine\Common\Collections\ArrayCollection();
        $this->text = $spellingText;
    }

    /* ==========================   =============== */


    /**
     * Set text
     *
     * @param string $text
     *
     * @return WordSpelling
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
     * @return WordSpelling
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

    /**
     * Set autoTranslation
     *
     * @param \AppBundle\Entity\WordTranslation $autoTranslation
     *
     * @return WordSpelling
     */
    public function setAutoTranslation(\AppBundle\Entity\WordTranslation $autoTranslation = null)
    {
        $this->autoTranslation = $autoTranslation;

        return $this;
    }

    /**
     * Get autoTranslation
     *
     * @return \AppBundle\Entity\WordTranslation
     */
    public function getAutoTranslation()
    {
        return $this->autoTranslation;
    }

    /**
     * Set autoTranscription
     *
     * @param \AppBundle\Entity\WordTranscription $autoTranscription
     *
     * @return WordSpelling
     */
    public function setAutoTranscription(\AppBundle\Entity\WordTranscription $autoTranscription = null)
    {
        $this->autoTranscription = $autoTranscription;

        return $this;
    }

    /**
     * Get autoTranscription
     *
     * @return \AppBundle\Entity\WordTranscription
     */
    public function getAutoTranscription()
    {
        return $this->autoTranscription;
    }

    /**
     * Set autoPronounce
     *
     * @param \AppBundle\Entity\WordPronounce $autoPronounce
     *
     * @return WordSpelling
     */
    public function setAutoPronounce(\AppBundle\Entity\WordPronounce $autoPronounce = null)
    {
        $this->autoPronounce = $autoPronounce;

        return $this;
    }

    /**
     * Get autoPronounce
     *
     * @return \AppBundle\Entity\WordPronounce
     */
    public function getAutoPronounce()
    {
        return $this->autoPronounce;
    }
}
