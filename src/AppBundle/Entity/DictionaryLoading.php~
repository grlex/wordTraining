<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 12.12.2017
 * Time: 12:37
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class DictionaryLoading
 * @package AppBundle\Entity
 * @ORM\Entity
 * @ORM\Table("dictionary_loading")
 */
class DictionaryLoading {

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $loaded;
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $total;
    /**
     * @var int
     * @ORM\Column(type="boolean")
     */
    protected $done;
    /**
     * @var int
     * @ORM\Column(type="boolean")
     */
    protected $cancelled;
    /**
     * @var Dictionary
     * @ORM\OneToOne(targetEntity="Dictionary", inversedBy="loading")
     */
    protected $dictionary;

    public function __construct(){
        $this->loaded=0;
        $this->total = 0;
        $this->done= false;
        $this->cancelled = false;
    }

    /* -====================================== */

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
     * Set loaded
     *
     * @param integer $loaded
     *
     * @return DictionaryLoading
     */
    public function setLoaded($loaded)
    {
        $this->loaded = $loaded;

        return $this;
    }

    /**
     * Get loaded
     *
     * @return integer
     */
    public function getLoaded()
    {
        return $this->loaded;
    }

    /**
     * Set total
     *
     * @param integer $total
     *
     * @return DictionaryLoading
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set done
     *
     * @param boolean $done
     *
     * @return DictionaryLoading
     */
    public function setDone($done)
    {
        $this->done = $done;

        return $this;
    }

    /**
     * Get done
     *
     * @return boolean
     */
    public function getDone()
    {
        return $this->done;
    }

    /**
     * Set cancelled
     *
     * @param boolean $cancelled
     *
     * @return DictionaryLoading
     */
    public function setCancelled($cancelled)
    {
        $this->cancelled = $cancelled;

        return $this;
    }

    /**
     * Get cancelled
     *
     * @return boolean
     */
    public function getCancelled()
    {
        return $this->cancelled;
    }

    /**
     * Set dictionary
     *
     * @param \AppBundle\Entity\Dictionary $dictionary
     *
     * @return DictionaryLoading
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
}
