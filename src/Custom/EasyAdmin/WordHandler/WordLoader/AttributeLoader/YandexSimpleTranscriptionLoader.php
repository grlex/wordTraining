<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 19.01.2018
 * Time: 18:41
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader;


use Custom\EasyAdmin\WordHandler\WordLoader\WordLoaderInterface;

class YandexSimpleTranscriptionLoader extends YandexSimpleAttributeLoader {
    public function load($spelling, $dialect=WordLoaderInterface::DIALECT_UK){
        if($dialect!= WordLoaderInterface::DIALECT_UK) return false;
        $responseData = $this->getResponse($spelling);
        if($responseData === false) return false;
        $translationResults = $responseData['en-ru']['regular'];
        if(count($translationResults)==0) return false;
        foreach($translationResults as $result){
            if(array_key_exists('ts', $result )) return $result['ts'];
        }
        return false;
    }
}