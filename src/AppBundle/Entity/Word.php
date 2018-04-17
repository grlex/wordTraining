<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 29.11.2017
 * Time: 13:53
 */

namespace AppBundle\Entity;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * Class Word
 * @package AppBundle\Model
 * @ORM\Entity(repositoryClass="WordRepository")
 * @ORM\HasLifecycleCallbacks()
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
     * @var WordTranslation
     * @ORM\ManyToOne(targetEntity="WordSpelling" , inversedBy="words", cascade={"persist"})
     */
    private $spelling;

    /**
     * @var WordTranslation
     * @ORM\ManyToOne(targetEntity="WordTranslation" , inversedBy="words", cascade={"persist"} )
     */
    private $translation;

    /**
     * @var WordTranscription
     * @ORM\ManyToOne(targetEntity="WordTranscription" , inversedBy="words", cascade={"persist"} )
     */
    private $transcription;

    /**
     * @var WordPronounce
     * @ORM\ManyToOne(targetEntity="WordPronounce", inversedBy="words", cascade={"persist"} )
     */
    private $pronounce;

    /**
     * @var WordPicture
     * @ORM\OneToMany(targetEntity="WordPicture", mappedBy="word", cascade={"persist", "remove"}, orphanRemoval=true )
     */
    private $pictures;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToOne(targetEntity="Dictionary", inversedBy="words", cascade={"persist"})
     */
    private $dictionary;


    public function __toString(){
        return (string)$this->spelling; // WordSpelling::__toString()
    }


    /**
     * @ORM\PreRemove
     */
    public function preRemove(LifecycleEventArgs $eventArgs){
        if($this->dictionary){
            $this->dictionary->removeWord($this);
        }
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
     * @param \AppBundle\Entity\WordSpelling $spelling
     *
     * @return Word
     */
    public function setSpelling(\AppBundle\Entity\WordSpelling $spelling = null)
    {
        if($this->spelling) $this->spelling->removeWord($this);
        if($spelling) $spelling->addWord($this);
        $this->spelling = $spelling;

        return $this;
    }

    /**
     * Get spelling
     *
     * @return \AppBundle\Entity\WordSpelling
     */
    public function getSpelling()
    {
        return $this->spelling;
    }

    /**
     * Set translation
     *
     * @param \AppBundle\Entity\WordTranslation $translation
     *
     * @return Word
     */
    public function setTranslation(\AppBundle\Entity\WordTranslation $translation = null)
    {
        if($this->translation) $this->translation->removeWord($this);
        if($translation) $translation->addWord($this);
        $this->translation = $translation;

        return $this;
    }

    /**
     * Get translation
     *
     * @return \AppBundle\Entity\WordTranslation
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * Set transcription
     *
     * @param \AppBundle\Entity\WordTranscription $transcription
     *
     * @return Word
     */
    public function setTranscription(\AppBundle\Entity\WordTranscription $transcription = null)
    {
        if($this->transcription) $this->transcription->removeWord($this);
        if($transcription) $transcription->addWord($this);
        $this->transcription = $transcription;

        return $this;
    }

    /**
     * Get transcription
     *
     * @return \AppBundle\Entity\WordTranscription
     */
    public function getTranscription()
    {
        return $this->transcription;
    }

    /**
     * Set pronounce
     *
     * @param \AppBundle\Entity\WordPronounce $pronounce
     *
     * @return Word
     */
    public function setPronounce(\AppBundle\Entity\WordPronounce $pronounce = null)
    {
        if($this->pronounce) $this->pronounce->removeWord($this);
        if($pronounce) $pronounce->addWord($this);
        $this->pronounce = $pronounce;

        return $this;
    }

    /**
     * Get pronounce
     *
     * @return \AppBundle\Entity\WordPronounce
     */
    public function getPronounce()
    {
        return $this->pronounce;
    }



    /**
     * Set dictionary
     *
     * @param \AppBundle\Entity\Dictionary $dictionary
     *
     * @return Word
     */
    public function setDictionary(\AppBundle\Entity\Dictionary $dictionary = null)
    {
        $this->dictionary = $dictionary;

        return $this;
    }

    /**
     * Get dictionary
     *
     * @return \AppBundle\Entity\Dictionary
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pictures = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add picture
     *
     * @param \AppBundle\Entity\WordPicture $picture
     *
     * @return Word
     */
    public function addPicture(\AppBundle\Entity\WordPicture $picture)
    {
        $this->pictures[] = $picture;

        return $this;
    }

    /**
     * Remove picture
     *
     * @param \AppBundle\Entity\WordPicture $picture
     */
    public function removePicture(\AppBundle\Entity\WordPicture $picture)
    {
        $this->pictures->removeElement($picture);
    }

    /**
     * Get pictures
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPictures()
    {
        return $this->pictures;
    }
}
