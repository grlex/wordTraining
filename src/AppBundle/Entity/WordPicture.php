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
 * Class WordPicture
 * @package AppBundle\Model
 * @ORM\Entity(repositoryClass="WordAttributeRepository")
 * @Vich\Uploadable
 */
class WordPicture extends WordAttribute
{
    /**
     * @var string
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $filename;

    /**
     * @var string
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $url;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $chosen;


    /**
     * @var Word
     * @ORM\ManyToOne(targetEntity="Word", inversedBy="pictures")
     */
    private $word;

    /**
     * @var File
     * @Vich\UploadableField(mapping="word_picture", fileNameProperty="filename")
     */
    private $file;


    public function __toString(){
        return '[ picture for word ]';
    }


    public function getText(){
        return $this->getFilename();
    }
    public function setText($filename){
        $this->filename = $filename;
    }

    public function getFile(){
        return $this->file;
    }
    public function setFile(){
        throw new \Exception('Not implemented');
    }

    /* ==========================   =============== */



    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return WordPicture
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }



    /**
     * Set word
     *
     * @param \AppBundle\Entity\Word $word
     *
     * @return WordPicture
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
     * Set url
     *
     * @param string $url
     *
     * @return WordPicture
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return WordPicture
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
     * Set chosen
     *
     * @param boolean $chosen
     *
     * @return WordPicture
     */
    public function setChosen($chosen)
    {
        $this->chosen = $chosen;

        return $this;
    }

    /**
     * Get chosen
     *
     * @return boolean
     */
    public function getChosen()
    {
        return $this->chosen;
    }
}
