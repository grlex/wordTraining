<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 08.12.2017
 * Time: 22:38
 */

namespace Custom\FOSUser;


use Symfony\Component\HttpKernel\Bundle\Bundle;

class CustomFOSUserBundle extends Bundle {
    public function getParent(){
        return 'FOSUserBundle';
    }
} 