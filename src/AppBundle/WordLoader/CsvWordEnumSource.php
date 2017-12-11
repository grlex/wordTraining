<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 30.11.2017
 * Time: 13:48
 */

namespace AppBundle\WordLoader;


class CsvWordEnumSource implements WordEnumSourceInterface{

    private $wordEnum;
    public function __construct($csvFilePath, $wordSeparator=", "){
        if(!file_exists($csvFilePath))
            throw new \Exception(sprintf('%s file does not exists',$csvFilePath));
        $this->wordEnum = explode($wordSeparator, file_get_contents($csvFilePath));
    }
    public function getWordEnum()
    {
        return $this->wordEnum;
    }
}