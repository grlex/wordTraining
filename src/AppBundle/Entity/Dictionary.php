<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 04.09.2017
 * Time: 17:27
 */

namespace AppBundle\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Dictionary
 * @package AppBundle\Model
 * @ORM\Entity
 * @ORM\Table(name="dictionary")
 * @Vich\Uploadable
 */
class Dictionary {
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
     * @Assert\NotBlank(message="entity.common.notBlank")
     * @Assert\Length( max = 200, maxMessage="model.common.strLength.{{limit}}" )
     */
    private $name;

    /**
     * @var
     * @ORM\Column(type="string", length=50)
     */
    private $sourceFilename;

    /**
     * @var File
     * @Vich\UploadableField(mapping="dictionary_source", fileNameProperty="sourceFilename")
     */
    private $sourceFile;

    /**
     * @var Word[] dictionary words
     * @ORM\ManyToMany(targetEntity="Word", mappedBy="dictionaries", cascade={"persist"})
     */
    private $words;

    /**
     * @var int word count
     * @ORM\Column(type="integer")
     */
    private $wordCount;

    /**
     * @var bool loaded
     * @ORM\Column(type="boolean")
     */
    private $loaded;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;




    public function __toString(){
       return $this->getName();
    }

    public function setSourceFile(File $sourceFile = null)
    {
        $this->sourceFile = $sourceFile;

        if ($sourceFile) {
            $this->updatedAt = new \DateTime('now');
            $this->wordCount = count(explode(',', file_get_contents($sourceFile->getRealPath())));
        }

        return $this;
    }

    /**
     * @return File|null
     */
    public function getSourceFile()
    {
        return $this->sourceFile;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->words = new \Doctrine\Common\Collections\ArrayCollection();
        $this->wordCount = 0;
        $this->loaded=false;
    }

    /* ========================  ================== */



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
     * Set name
     *
     * @param string $name
     *
     * @return Dictionary
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add word
     *
     * @param \AppBundle\Entity\Word $word
     *
     * @return Dictionary
     */
    public function addWord(\AppBundle\Entity\Word $word)
    {
        $word->addToDictionary($this);
        $this->words[] = $word;
        $this->wordCount++;

        return $this;
    }

    /**
     * Remove word
     *
     * @param \AppBundle\Entity\Word $word
     */
    public function removeWord(\AppBundle\Entity\Word $word)
    {
        $word->removeFromDictionary($this);
        $this->words->removeElement($word);
        $this->wordCount--;
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
     * Set sourceFilename
     *
     * @param string $sourceFilename
     *
     * @return Dictionary
     */
    public function setSourceFilename($sourceFilename)
    {
        $this->sourceFilename = $sourceFilename;

        return $this;
    }

    /**
     * Get sourceFilename
     *
     * @return string
     */
    public function getSourceFilename()
    {
        return $this->sourceFilename;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Dictionary
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
     * Set wordCount
     *
     * @param integer $wordCount
     *
     * @return Dictionary
     */
    public function setWordCount($wordCount)
    {
        $this->wordCount = $wordCount;

        return $this;
    }

    /**
     * Get wordCount
     *
     * @return integer
     */
    public function getWordCount()
    {
        return $this->wordCount;
    }

    /**
     * Set loaded
     *
     * @param boolean $loaded
     *
     * @return Dictionary
     */
    public function setLoaded($loaded)
    {
        $this->loaded = $loaded;

        return $this;
    }

    /**
     * Get loaded
     *
     * @return boolean
     */
    public function getLoaded()
    {
        return $this->loaded;
    }
}
