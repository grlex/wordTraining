<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 22.01.2018
 * Time: 12:10
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader;


use Custom\EasyAdmin\WordHandler\WordLoader\WordLoaderInterface;

class ChainedLoader implements WordAttributeLoaderInterface {
    private $loadersChain;
    public function __construct(array $loaders){
        $this->loadersChain = $loaders;
    }

    public function load($spelling, $dialect = WordLoaderInterface::DIALECT_UK){
        foreach($this->loadersChain as $loader){
            $value = $loader->load($spelling, $dialect);
            if($value !== false) return $value;
        }
        return false;
    }
} 