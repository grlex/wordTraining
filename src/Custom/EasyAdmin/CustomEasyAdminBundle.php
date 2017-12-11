<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 08.12.2017
 * Time: 17:21
 */

namespace Custom\EasyAdmin;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CustomEasyAdminBundle extends Bundle{

    public function getParent(){
        return 'EasyAdminBundle';
    }
} 