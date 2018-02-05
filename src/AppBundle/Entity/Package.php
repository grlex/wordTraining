<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 27.12.2017
 * Time: 17:23
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Package
 * @package AppBundle\Entity
 * @ORM\Entity(repositoryClass="PackageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Package {
    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $local;

    /**
     * @var Word[]
     * @ORM\ManyToMany(targetEntity="Word", indexBy="id", cascade={"persist", "merge", "detach"})
     */
    private $words;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var Dictionary
     * @ORM\ManyToOne(targetEntity="Dictionary")
     * @ORM\JoinColumn(nullable=true)
     */
    private $dictionary;


    /**
     * Constructor
     */
    public function __construct($fakeId = null)
    {
        if($fakeId) $this->id = $fakeId;
        $this->words = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function isLocal(){
        return (bool)$this->local or $this->id == 'all-local';
    }

    public function isGlobal(){
        return !(bool)$this->local or $this->id == 'all-global';
    }

    public function isPredefined(){
        return $this->id == 'all-local' or $this->id == 'all-global';
    }

    public function hasWord(Word $word){
        return $this->words->containsKey($word->getId());
    }

    public function setWords( $words){
        $this->words = $words;
        return $this;
    }

    /**
     * the same could be achieved via foreign key trigger ON DELETE,
     * but by default in sqlite those triggers are disabled
     * @ORM\PreRemove
     */
    public function preRemove(){
        $this->words->clear();
    }
    /** ==================== ========================== */


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
     * @return Package
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
     * @return Package
     */
    public function addWord(\AppBundle\Entity\Word $word)
    {
        if(is_null($word->getId())) $this->words[] = $word;
        else $this->words[$word->getId()] = $word;

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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Package
     */
    public function setUser(\AppBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set dictionary
     *
     * @param \AppBundle\Entity\Dictionary $dictionary
     *
     * @return Package
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
     * Set local
     *
     * @param boolean $local
     *
     * @return Package
     */
    public function setLocal($local)
    {
        $this->local = $local;

        return $this;
    }

    /**
     * Get local
     *
     * @return boolean
     */
    public function getLocal()
    {
        return $this->local;
    }
}
