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
 * @ORM\Entity(repositoryClass="DictionaryProcessingRepository")
 */
class DictionaryProcessing {

    const STATUS_PENDING = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_DONE = 3;
    const STATUS_PAUSING = 4;
    const STATUS_PAUSED = 5;
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
    protected $processed;
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $total;
    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    protected $status;

    /**
     * @var Dictionary
     * @ORM\OneToOne(targetEntity="Dictionary", inversedBy="processingInfo")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $dictionary;

    public function __construct(){
        $this->loaded=0;
        $this->total = 0;
        $this->status = self::STATUS_PENDING;
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
     * Set processed
     *
     * @param integer $processed
     *
     * @return DictionaryProcessing
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;

        return $this;
    }

    /**
     * Get processed
     *
     * @return integer
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * Set total
     *
     * @param integer $total
     *
     * @return DictionaryProcessing
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
     * Set status
     *
     * @param boolean $status
     *
     * @return DictionaryProcessing
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set dictionary
     *
     * @param \AppBundle\Entity\Dictionary $dictionary
     *
     * @return DictionaryProcessing
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
