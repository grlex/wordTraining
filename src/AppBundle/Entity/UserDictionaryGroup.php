<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 08.05.2018
 * Time: 22:14
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserDictionaryGroup
 * @package AppBundle\Entity
 * @ORM\Entity
 */
class UserDictionaryGroup {
    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;
    /**
     * @var DictionaryGroup
     * @ORM\ManyToOne(targetEntity="DictionaryGroup")
     */
    private $group;
    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $collapsed;

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
     * Set collapsed
     *
     * @param boolean $collapsed
     *
     * @return UserDictionaryGroup
     */
    public function setCollapsed($collapsed)
    {
        $this->collapsed = $collapsed;

        return $this;
    }

    /**
     * Get collapsed
     *
     * @return boolean
     */
    public function getCollapsed()
    {
        return $this->collapsed;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return UserDictionaryGroup
     */
    public function setUser(\AppBundle\Entity\User $user = null)
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
     * Set group
     *
     * @param \AppBundle\Entity\DictionaryGroup $group
     *
     * @return UserDictionaryGroup
     */
    public function setGroup(\AppBundle\Entity\DictionaryGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \AppBundle\Entity\DictionaryGroup
     */
    public function getGroup()
    {
        return $this->group;
    }
}
