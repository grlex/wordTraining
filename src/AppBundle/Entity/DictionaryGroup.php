<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 05.05.2018
 * Time: 19:07
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class DictionaryGroup
 * @package AppBundle\Entity
 * @ORM\Entity
 */
class DictionaryGroup {

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=200)
     */
    private $title;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false, options={"default":1})
     */
    private $maxColumns;

    /**
     * @var int
     * @ORm\Column(type="integer", nullable=true)
     */
    private $sort;

    /**
     * @var DictionaryGroup
     * @ORM\ManyToOne(targetEntity="DictionaryGroup", inversedBy="children")
     */
    private $parent;

    /**
     * @var DictionaryGroup[]
     * @ORM\OneToMany(targetEntity="DictionaryGroup", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"sort" = "ASC"})
     */
    private $children;

    /**
     * @var Dictionary[]
     * @ORM\OneToMany(targetEntity="Dictionary", mappedBy="group", cascade={"persist", "remove"})
     */
    private $dictionaries;

    /* --------------------------------------------- */

    public function __toString(){
        return $this->title;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dictionaries = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     *
     * @return DictionaryGroup
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set maxColumns
     *
     * @param integer $maxColumns
     *
     * @return DictionaryGroup
     */
    public function setMaxColumns($maxColumns)
    {
        $this->maxColumns = $maxColumns;

        return $this;
    }

    /**
     * Get maxColumns
     *
     * @return integer
     */
    public function getMaxColumns()
    {
        return $this->maxColumns;
    }

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\DictionaryGroup $parent
     *
     * @return DictionaryGroup
     */
    public function setParent(\AppBundle\Entity\DictionaryGroup $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\DictionaryGroup
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add child
     *
     * @param \AppBundle\Entity\DictionaryGroup $child
     *
     * @return DictionaryGroup
     */
    public function addChild(\AppBundle\Entity\DictionaryGroup $child)
    {
        $this->children[] = $child;
        $child->setParent($this);
        return $this;
    }

    /**
     * Remove child
     *
     * @param \AppBundle\Entity\DictionaryGroup $child
     */
    public function removeChild(\AppBundle\Entity\DictionaryGroup $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add dictionary
     *
     * @param \AppBundle\Entity\Dictionary $dictionary
     *
     * @return DictionaryGroup
     */
    public function addDictionary(\AppBundle\Entity\Dictionary $dictionary)
    {
        $this->dictionaries[] = $dictionary;
        $dictionary->setGroup($this);
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
     * Set sort
     *
     * @param integer $sort
     *
     * @return DictionaryGroup
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get sort
     *
     * @return integer
     */
    public function getSort()
    {
        return $this->sort;
    }
}
