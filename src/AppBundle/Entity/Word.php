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
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
/**
 * Class Word
 * @package AppBundle\Model
 * @ORM\Entity(repositoryClass="WordRepository")
 * @ORM\Table(name="word")
 * @Vich\Uploadable
 * @UniqueEntity("spelling")
 */
class Word {
    const STATUS_PENDING = 1;
    const STATUS_INCORRECT = 2;
    const STATUS_TRANSLATED = 3;
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
     * @ORM\Column(type="string", length=50, nullable=true)
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
     * @ORM\ManyToMany(targetEntity="Dictionary", mappedBy="words")
     */
    private $dictionaries;

    /**
     * @var string
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $audioFilename;
    /**
     * @var File
     * @Vich\UploadableField(mapping="word_audio", fileNameProperty="audioFilename")
     */
    private $audioFile;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    private $status;



    public function __toString(){
        return $this->getSpelling();
    }



    /**
     * Constructor
     */
    public function __construct($spelling = null)
    {
        $this->dictionaries = new \Doctrine\Common\Collections\ArrayCollection();
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->examples = new \Doctrine\Common\Collections\ArrayCollection();
        $this->status = self::STATUS_PENDING;
        $this->setSpelling($spelling);
        $this->updatedAt = new \DateTime('now');
    }

    public function setAudioFile(File $audioFile = null)
    {
        $this->audioFile = $audioFile;

        if ($audioFile) {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    public function getAudioFilename()
    {
        return $this->audioFilename;
    }

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
        $dictionary->addWord($this);
        $this->dictionaries[] = $dictionary;
        return $this;
    }

    /**
     * Remove dictionary
     *
     * @param \AppBundle\Entity\Dictionary $dictionary
     */
    public function removeFromDictionary(\AppBundle\Entity\Dictionary $dictionary)
    {
        $dictionary->removeWord($this);
        $this->dictionaries->removeElement($dictionary);
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
     * Set status
     *
     * @param integer $status
     *
     * @return Word
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set audioFilename
     *
     * @param string $audioFilename
     *
     * @return Word
     */
    public function setAudioFilename($audioFilename)
    {
        $this->audioFilename = $audioFilename;

        return $this;
    }


    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Word
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
