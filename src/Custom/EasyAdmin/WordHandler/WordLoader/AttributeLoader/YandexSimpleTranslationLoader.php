<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 19.01.2018
 * Time: 18:41
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader;


use Custom\EasyAdmin\WordHandler\WordLoader\WordLoaderInterface;

class YandexSimpleTranslationLoader extends YandexSimpleAttributeLoader {

    public function load($spelling, $dialect=WordLoaderInterface::DIALECT_UK){
        if($dialect!= WordLoaderInterface::DIALECT_UK) return false;
        $responseData = $this->getResponse($spelling);
        if($responseData === false) return false;
        $translationResults = $responseData['en-ru']['regular'];
        if(count($translationResults)==0) return false;
        $allTranslations = [];
        foreach($translationResults as $result){
            if(array_key_exists('tr', $result )) {
                $translations = $result['tr'];
                foreach($translations as $translation){
                    $allTranslations[] = $translation['text'];
                }
            }
        }
        return count($allTranslations) == 0 ? false : implode(', ', $allTranslations);
    }
}