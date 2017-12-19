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
 * @UniqueEntity("name")
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
     * @var Word[] dictionary words
     * @ORM\ManyToMany(targetEntity="Word", inversedBy="dictionaries", cascade={"persist","merge", "detach"})
     * @ORM\OrderBy({"spelling"="ASC"})
     */
    private $words;

    /**
     * @var DictionaryLoading
     * @ORM\OneToOne(targetEntity="DictionaryLoading", mappedBy="dictionary")
     */
    private $loadingInfo;



    public function __toString(){
        return $this->getName();
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->words = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set loadingInfo
     *
     * @param \AppBundle\Entity\DictionaryLoading $loadingInfo
     *
     * @return Dictionary
     */
    public function setLoadingInfo(\AppBundle\Entity\DictionaryLoading $loadingInfo = null)
    {
        $this->loadingInfo = $loadingInfo;

        return $this;
    }

    /**
     * Get loadingInfo
     *
     * @return \AppBundle\Entity\DictionaryLoading
     */
    public function getLoadingInfo()
    {
        return $this->loadingInfo;
    }
}
