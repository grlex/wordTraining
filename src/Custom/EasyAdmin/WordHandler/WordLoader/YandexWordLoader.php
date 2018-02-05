<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 19.01.2018
 * Time: 13:28
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader;

use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\AttributeLoaderContext;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\YandexSimpleTranscriptionLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\YandexSimpleTranslationLoader;

class YandexWordLoader extends BaseWordLoader{

    public function __construct( $requestRateDelay=2, $timestampCache = null){
        $context = new AttributeLoaderContext($requestRateDelay, $timestampCache);
        $this->translationLoader = new YandexSimpleTranslationLoader($context);
        $this->transcriptionLoader = new YandexSimpleTranscriptionLoader($context);
    }

    /*
    public function loadAudioFile($spelling = null, $dialect = self::DIALECT_UK){
        throw new \Exception('Not supported');
    }*/

}