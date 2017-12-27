<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 27.12.2017
 * Time: 17:23
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Package
 * @package AppBundle\Entity
 * @ORM\Entity
 */
class PackageWord {
    const PRIORITY_HIGH = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_LOW = 3;
    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Word
     * @ORM\ManyToOne(targetEntity="Word")
     */
    private $word;
    /**
     * @var Word
     * @ORM\ManyToOne(targetEntity="Package", inversedBy="words")
     */
    private $package;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $learned;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    private $priority;

    /**
     * @var Translation[]
     * @ORM\ManyToMany
     */
    private $primaryTranslations;

} 