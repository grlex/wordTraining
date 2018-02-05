<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 22.01.2018
 * Time: 12:30
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader;


abstract class YandexSimpleAttributeLoader implements WordAttributeLoaderInterface {

    private $context;

    public function __construct( AttributeLoaderContext $context = null){
        if(is_null($context)){
            $context = new AttributeLoaderContext();
        }
        $this->context = $context;
    }

    protected function getResponse($spelling){
        if($this->context->getSpelling() == $spelling){
            return $this->context->getData();
        }
        $this->context->takeRequestRateDelay();
        $url = 'https://dictionary.yandex.net/dicservice.json/lookupMultiple?ui=ru&srv=tr-text&text=__text__&dict=en-ru.regular&flags=103';
        $url = str_replace('__text__', urlencode($spelling), $url);
        $responseData = file_get_contents($url);
        if($responseData !== false){
            $responseData = json_decode($responseData, true);
        }
        $this->context->setData($responseData);
        return $responseData;
    }

} 