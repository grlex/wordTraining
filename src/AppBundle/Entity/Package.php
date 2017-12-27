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
class Package {
    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var PackageWord[]
     * @ORM\OneToMany(targetEntity="PackageWord", mappedBy="package")
     */
    private $words;

    /**
     * @var Dictionary
     * @ORM\ManyToOne(targetEntity="Dictionary", inversedBy="packages")
     */
    private $dictionary;


} 