<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 19.01.2018
 * Time: 18:41
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader;


use Custom\EasyAdmin\WordHandler\WordLoader\WordLoaderInterface;

class YandexApiTranslationLoader implements WordAttributeLoaderInterface {
    private $apiKey;
    public function __construct($apiKey, AttributeLoaderContext $context = null){
        if(is_null($context)){
            $context = new AttributeLoaderContext();
        }
        $this->context = $context;
        $this->apiKey = $apiKey;
    }
    public function load($spelling, $dialect=WordLoaderInterface::DIALECT_UK){
        if($dialect!= WordLoaderInterface::DIALECT_UK) return false;
        /* https://translate.yandex.net/api/v1.5/tr.json/translate
             ? [key=<API-ключ>]
             & [text=<переводимый текст>]
             & [lang=<направление перевода>]
             & [format=<формат текста>]
             & [options=<опции перевода>]
             & [callback=<имя callback-функции>]
         */
        $httpQuery = http_build_query(array(
            'key' => $this->apiKey,
            'text' => urlencode($spelling),
            'lang' => 'en-ru',
        ));
        $url = 'https://translate.yandex.net/api/v1.5/tr.json/translate?'.$httpQuery;
        $responseData = file_get_contents($url);
        if($responseData===false) return false;
        $responseData = json_decode($responseData, true);
        if($responseData['code'] == 200 ){
            return str_replace('+', ' ', implode(', ', $responseData['text']));
        }
        return false;
    }
}