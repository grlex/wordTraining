<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 19.01.2018
 * Time: 13:32
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader;

use Custom\EasyAdmin\WordHandler\Exception\LoadingException;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\WordAttributeLoaderInterface;
use Custom\EasyAdmin\WordHandler\WordLoader\WordLoaderInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\File;

abstract class BaseWordLoader implements WordLoaderInterface {
    protected $translationLoader;
    protected $transcriptionLoader;
    protected $audioFileLoader;

    public function getTranslationLoader(){
        return $this->translationLoader;
    }

    public function getTranscriptionLoader(){
        return $this->transcriptionLoader;
    }

    public function getAudioFileLoader(){
        return $this->audioFileLoader;
    }

    public function loadTranslation($spelling){
        return is_null($this->translationLoader)
            ? false
            :  $this->translationLoader->load($spelling);
    }

    public function loadTranscription($spelling = null, $dialect = self::DIALECT_UK){
        return is_null($this->transcriptionLoader)
            ? false
            :  $this->transcriptionLoader->load($spelling, $dialect);
    }

    public function loadAudioFile($spelling = null, $dialect = self::DIALECT_UK){
        return is_null($this->audioFileLoader)
            ? false
            :  $this->audioFileLoader->load($spelling, $dialect);
    }
} 