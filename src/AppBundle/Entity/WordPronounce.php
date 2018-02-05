<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 09.01.2018
 * Time: 12:19
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Twig\Extension\UploaderExtension;

/**
 * Class Word
 * @package AppBundle\Model
 * @ORM\Entity(repositoryClass="WordAttributeRepository")
 * @Vich\Uploadable
 */
class WordPronounce extends WordAttribute
{
    /**
     * @var string
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $audioFilename;

    /**
     * @var File
     * @Vich\UploadableField(mapping="word_sound", fileNameProperty="audioFilename")
     */
    private $audioFile;

    /**
     * @var WordPronounceAudioData
     * @ORM\OneToOne(targetEntity="WordPronounceAudioData", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $audioData;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var Word
     * @ORM\OneToMany(targetEntity="Word", mappedBy="pronounce", cascade={"persist", "merge", "detach"})
     */
    private $words;


    public function __toString(){
        return '[ audio for word ]';
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->words = new \Doctrine\Common\Collections\ArrayCollection();
        $this->updatedAt = new \DateTime('now');
    }

    public function setAudioFile(File $audioFile = null)
    {
        $this->audioFile = $audioFile;

        if (null !== $audioFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }
    public function getAudioFile(){
        return $this->audioFile;
    }

    public function generateFilename(){
        if($this->audioFile instanceof UploadedFile){
            $filename = $this->getWords()->first()->getSpelling()->getText();
            $filename .= '_'.md5(uniqid('audio_file',true));
            $filename .= $this->audioFile->guessClientExtension();
            return $filename;
        }
        if($this->audioFile){
            return $this->audioFile->getExtension();
        }
        return false;
    }

    public function getText(){
        return $this->getAudioFilename();
    }
    public function setText($filename){
        $this->audioFilename = $filename;
    }

    /* ==========================   =============== */



    /**
     * Set audioFilename
     *
     * @param string $audioFilename
     *
     * @return WordPronounce
     */
    public function setAudioFilename($audioFilename)
    {
        $this->audioFilename = $audioFilename;

        return $this;
    }

    /**
     * Get audioFilename
     *
     * @return string
     */
    public function getAudioFilename()
    {
        return $this->audioFilename;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return WordPronounce
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

    /**
     * Add word
     *
     * @param \AppBundle\Entity\Word $word
     *
     * @return WordPronounce
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
     * Set audioData
     *
     * @param \AppBundle\Entity\WordPronounceAudioData $audioData
     *
     * @return WordPronounce
     */
    public function setAudioData(\AppBundle\Entity\WordPronounceAudioData $audioData = null)
    {
        $this->audioData = $audioData;

        return $this;
    }

    /**
     * Get audioData
     *
     * @return \AppBundle\Entity\WordPronounceAudioData
     */
    public function getAudioData()
    {
        return $this->audioData;
    }
}
