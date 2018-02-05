<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 04.09.2017
 * Time: 17:27
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Dictionary
 * @package AppBundle\Model
 * @ORM\Entity(repositoryClass = "DictionaryRepository")
 * @UniqueEntity("name")
 * @ORM\HasLifecycleCallbacks
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
     * @var Word[]
     * @ORM\OneToMany(targetEntity="Word", mappedBy="dictionary", cascade={"persist", "remove", "merge", "detach"}, orphanRemoval=true)
     */
    private $words;

    /**
     * @var DictionaryProcessing
     * @ORM\OneToOne(targetEntity="DictionaryProcessing", mappedBy="dictionary", cascade={"persist", "remove"})
     */
    private $processingInfo;



    public function __toString(){
        return $this->getName();
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->processingInfo = new DictionaryProcessing();
        $this->processingInfo->setDictionary($this);
        $this->words = new \Doctrine\Common\Collections\ArrayCollection();
        $this->packages = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set processingInfo
     *
     * @param \AppBundle\Entity\DictionaryProcessing $processingInfo
     *
     * @return Dictionary
     */
    public function setProcessingInfo(\AppBundle\Entity\DictionaryProcessing $processingInfo = null)
    {
        $processingInfo->setDictionary($this);
        $this->processingInfo = $processingInfo;

        return $this;
    }

    /**
     * Get processingInfo
     *
     * @return \AppBundle\Entity\DictionaryProcessing
     */
    public function getProcessingInfo()
    {
        return $this->processingInfo;
    }


    /**
     * Add dictionary word
     *
     * @param Word $word
     *
     * @return Dictionary
     */
    public function addWord(Word $word)
    {
        $word->setDictionary($this);
        $this->words->add($word);
        $total = $this->processingInfo->getTotal();
        $this->processingInfo->setTotal(++$total);
        $this->processingInfo->setProcessed(0);
        $this->processingInfo->setStatus(DictionaryProcessing::STATUS_PENDING);
        return $this;
    }

    /**
     * Remove  word
     *
     * @param Word $word
     */
    public function removeWord(Word $word)
    {
        $word->setDictionary(null);
        $this->words->removeElement($word);
        $total = $this->processingInfo->getTotal();
        $this->processingInfo->setTotal(--$total);
        $this->processingInfo->setProcessed(0);
        $this->processingInfo->setStatus(DictionaryProcessing::STATUS_PENDING);
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
