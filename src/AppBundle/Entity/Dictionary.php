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
 * @ORM\Entity(repositoryClass = "DictionaryRepository")
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
     * @ORM\JoinTable(joinColumns={@ORM\JoinColumn(name="dictionary_id", referencedColumnName="id", onDelete="CASCADE", unique=false)},
     *         inverseJoinColumns={@ORM\JoinColumn(name="word_id", referencedColumnName="id", onDelete="CASCADE", unique=false)})
     */
    private $words;

    /**
     * @var DictionaryLoading
     * @ORM\OneToOne(targetEntity="DictionaryLoading", mappedBy="dictionary", cascade={"persist", "remove"})
     */
    private $loadingInfo;

    /**
     * @var Package
     * @ORM\OneToMany(targetEntity="Package", mappedBy="dictionary", cascade={"persist", "remove"})
     */
    private $packages;



    public function __toString(){
        return $this->getName();
    }


    /**
     * Constructor
     */
    public function __construct()
    {
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
     * Add word
     *
     * @param \AppBundle\Entity\Word $word
     *
     * @return Dictionary
     */
    public function addWord(\AppBundle\Entity\Word $word)
    {
        $this->words[] = $word;
        $total = $this->loadingInfo->getTotal();
        $this->loadingInfo->setTotal(++$total);
        $this->loadingInfo->setStatus(DictionaryLoading::STATUS_PENDING);
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
        $total = $this->loadingInfo->getTotal();
        $this->loadingInfo->setTotal(--$total);
        $loaded = $this->loadingInfo->getLoaded();
        $this->loadingInfo->setLoaded($loaded>$total ? $total : $loaded );
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
        $loadingInfo->setDictionary($this);
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

    /**
     * Add package
     *
     * @param Package $package
     *
     * @return Dictionary
     */
    public function addPackage(Package $package)
    {
        $this->packages[] = $package;
        return $this;
    }

    /**
     * Remove package
     *
     * @param Package $package
     *
     * @return Dictionary
     */
    public function removePackage(Package $package)
    {
        $this->packages->removeElement($package);
        return $this;
    }

    /**
     * Get packages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPackages()
    {
        return $this->packages;
    }
}
