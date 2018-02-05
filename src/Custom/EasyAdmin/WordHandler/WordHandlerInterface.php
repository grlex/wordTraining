<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 16.01.2018
 * Time: 13:51
 */

namespace Custom\EasyAdmin\WordHandler;


use AppBundle\Entity\Word;

interface WordHandlerInterface {
    public function handleWordAttributes(Word $word);
}