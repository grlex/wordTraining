<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 29.11.2017
 * Time: 13:53
 */

namespace AppBundle\Entity;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * Class Word
 * @package AppBundle\Model
 * @ORM\Entity(repositoryClass="WordRepository")
 * @ORM\Table(name="word")
 * @UniqueEntity("spelling")
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable
 */
class Word {
    const STATUS_PENDING = 1;
    const STATUS_LOADING = 2;
    const STATUS_INCORRECT = 3;
    const STATUS_TRANSLATED = 4;
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
     * word is mapped side because it allows simpty add word to dictionary words's ArrayCollection
     * and do nothing else due to dictionary is inversed (owning) side
     * @ORM\ManyToMany(targetEntity="Dictionary", mappedBy="words")
     */
    private $dictionaries;

    /**
     * @var string
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $soundFilename;

    /**
     * @var File
     * @Vich\UploadableField(mapping="word_sound", fileNameProperty="soundFilename")
     */
    private $soundFile;

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

    /**
     * @var WordForm[]
     * @ORM\OneToMany(targetEntity="WordForm", mappedBy="word", cascade={"persist", "remove"})
     */
    private $forms;



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
        $this->forms = new \Doctrine\Common\Collections\ArrayCollection();
        $this->status = self::STATUS_PENDING;
        $this->updatedAt = new \DateTime('now');
        $this->setSpelling($spelling);
    }

    /**
     * @ORM\PreRemove
     */
    public function preRemove(LifecycleEventArgs $eventArgs){
        $dictionaries = $this->getDictionaries();
        foreach($dictionaries as $dictionary){
            $this->removeFromDictionary($dictionary);
            // dictionary is attached to object manager when requested via Word::getDictionaries() method,
            // so it will be updated automatically within the same transaction as the word removal will
        }
    }

    public function setSoundFile(File $soundFile = null)
    {
        $this->soundFile = $soundFile;

        if (null !== $soundFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }
    public function getSoundFile(){
        return $this->soundFile;
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
     * Set soundFilename
     *
     * @param string $soundFilename
     *
     * @return Word
     */
    public function setSoundFilename($soundFilename)
    {
        $this->soundFilename = $soundFilename;

        return $this;
    }

    /**
     * @return string
     */
    public function getSoundFilename()
    {
        return $this->soundFilename;
    }


    /**
     * Add form
     *
     * @param \AppBundle\Entity\WordForm $form
     *
     * @return Word
     */
    public function addForm(\AppBundle\Entity\WordForm $form)
    {
        $form->setWord($this);
        $this->forms[] = $form;

        return $this;
    }

    /**
     * Remove form
     *
     * @param \AppBundle\Entity\WordForm $form
     */
    public function removeForm(\AppBundle\Entity\WordForm $form)
    {
        $form->setWord(null);
        $this->forms->removeElement($form);
    }

    /**
     * Get forms
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getForms()
    {
        return $this->forms;
    }
}
