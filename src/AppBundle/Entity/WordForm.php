<?php
    /**
     * Created by PhpStorm.
     * User: Alexey Grigoriev
     * Date: 29.11.2017
     * Time: 13:53
     */

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Word
 * @package AppBundle\Model
 * @ORM\Entity
 * @ORM\Table(name="word_form")
 */
class WordForm {
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
    private $comment;

    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $formSpelling;


    /**
     * @var Word
     * @ORM\ManyToOne(targetEntity="Word", cascade={"persist"})
     */
    private $formWord;

    /**
     * @var Word
     * @ORM\ManyToOne(targetEntity="Word", inversedBy="forms")
     */
    private $word;


    public function __toString(){
        return $this->getFormSpelling();
    }

    /* ================================ ============== */

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
     * Set comment
     *
     * @param string $comment
     *
     * @return WordForm
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set word
     *
     * @param \AppBundle\Entity\Word $word
     *
     * @return WordForm
     */
    public function setWord(\AppBundle\Entity\Word $word = null)
    {
        $this->word = $word;

        return $this;
    }

    /**
     * Get word
     *
     * @return \AppBundle\Entity\Word
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * Set formWord
     *
     * @param \AppBundle\Entity\Word $formWord
     *
     * @return WordForm
     */
    public function setFormWord(\AppBundle\Entity\Word $formWord = null)
    {
        $this->formWord = $formWord;

        return $this;
    }

    /**
     * Get formWord
     *
     * @return \AppBundle\Entity\Word
     */
    public function getFormWord()
    {
        return $this->formWord;
    }

    /**
     * Set formSpelling
     *
     * @param string $formSpelling
     *
     * @return WordForm
     */
    public function setFormSpelling($formSpelling)
    {
        $this->formSpelling = $formSpelling;

        return $this;
    }

    /**
     * Get formSpelling
     *
     * @return string
     */
    public function getFormSpelling()
    {
        return $this->formSpelling;
    }

}
