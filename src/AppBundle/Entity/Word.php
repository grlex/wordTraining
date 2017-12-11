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
 * @ORM\Entity(repositoryClass="WordRepository")
 * @ORM\Table(name="word")
 */
class Word {
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var string
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $spelling;
    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    private $transcription;
    /**
     * @var @var Translation[]
     * @ORM\OneToMany(targetEntity="Translation", mappedBy="word", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $translations;
    /**
     * @var Example[]
     * @ORM\OneToMany(targetEntity="Example", mappedBy="word", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $examples;

    /**
     * @var Dictionary
     * @ORM\ManyToMany(targetEntity="Dictionary", inversedBy="words")
     */
    private $dictionaries;

    public function __toString(){
        return $this->getSpelling();
    }


    /* ==========================   =============== */



    /**
     * Constructor
     */
    public function __construct($spelling = null)
    {
        $this->dictionaries = new \Doctrine\Common\Collections\ArrayCollection();
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->examples = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setSpelling($spelling);
    }

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
     * Set spelling
     *
     * @param string $spelling
     *
     * @return Word
     */
    public function setSpelling($spelling)
    {
        $this->spelling = $spelling;

        return $this;
    }

    /**
     * Get spelling
     *
     * @return string
     */
    public function getSpelling()
    {
        return $this->spelling;
    }

    /**
     * Set transcription
     *
     * @param string $transcription
     *
     * @return Word
     */
    public function setTranscription($transcription)
    {
        $this->transcription = $transcription;

        return $this;
    }

    /**
     * Get transcription
     *
     * @return string
     */
    public function getTranscription()
    {
        return $this->transcription;
    }

    /**
     * Add translation
     *
     * @param \AppBundle\Entity\Translation $translation
     *
     * @return Word
     */
    public function addTranslation(\AppBundle\Entity\Translation $translation)
    {
        $translation->setWord($this);
        $this->translations[] = $translation;

        return $this;
    }

    /**
     * Remove translation
     *
     * @param \AppBundle\Entity\Translation $translation
     */
    public function removeTranslation(\AppBundle\Entity\Translation $translation)
    {
        $translation->setWord(null);
        $this->translations->removeElement($translation);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Add example
     *
     * @param \AppBundle\Entity\Example $example
     *
     * @return Word
     */
    public function addExample(\AppBundle\Entity\Example $example)
    {
        $example->setWord($this);
        $this->examples[] = $example;

        return $this;
    }

    /**
     * Remove example
     *
     * @param \AppBundle\Entity\Example $example
     */
    public function removeExample(\AppBundle\Entity\Example $example)
    {
        $example->setWord(null);
        $this->examples->removeElement($example);
    }

    /**
     * Get examples
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExamples()
    {
        return $this->examples;
    }

    /**
     * Add dictionary
     *
     * @param \AppBundle\Entity\Dictionary $dictionary
     *
     * @return Word
     */
    public function addToDictionary(\AppBundle\Entity\Dictionary $dictionary)
    {
        $this->dictionaries[] = $dictionary;
        $dictionary->addWord($this);
        return $this;
    }

    /**
     * Remove dictionary
     *
     * @param \AppBundle\Entity\Dictionary $dictionary
     */
    public function removeFromDictionary(\AppBundle\Entity\Dictionary $dictionary)
    {
        $this->dictionaries->removeElement($dictionary);
        $dictionary->removeWord($this);
    }

    /**
     * Get dictionaries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDictionaries()
    {
        return $this->dictionaries;
    }

    /**
     * Add dictionary
     *
     * @param \AppBundle\Entity\Dictionary $dictionary
     *
     * @return Word
     */
    public function addDictionary(\AppBundle\Entity\Dictionary $dictionary)
    {
        $this->dictionaries[] = $dictionary;

        return $this;
    }

    /**
     * Remove dictionary
     *
     * @param \AppBundle\Entity\Dictionary $dictionary
     */
    public function removeDictionary(\AppBundle\Entity\Dictionary $dictionary)
    {
        $this->dictionaries->removeElement($dictionary);
    }
}
