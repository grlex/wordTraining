<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 30.01.2018
 * Time: 17:04
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Settings
 * @package AppBundle\Entity
 * @ORM\Entity(repositoryClass="SettingsRepository")
 */
class Settings {
    const SETTING_UNDEFINED = 0;
    const SETTING_UNDER_MAINTENANCE = 1;
    const SETTING_SHOW_BACKGROUND_IMAGES = 2;
    const SETTING_BACKGROUND_IMAGES = 3;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="integer")
     */
    private $setting;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $value;


    public function __construct($setting = self::SETTING_UNDEFINED){
        $this->setting = $setting;
    }

    /** ========================  =============== **/


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
     * Set setting
     *
     * @param integer $setting
     *
     * @return Settings
     */
    public function setSetting($setting)
    {
        $this->setting = $setting;

        return $this;
    }

    /**
     * Get setting
     *
     * @return integer
     */
    public function getSetting()
    {
        return $this->setting;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return Settings
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
