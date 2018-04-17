<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 19.01.2018
 * Time: 18:17
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader;

use Custom\EasyAdmin\WordHandler\WordLoader\WordLoaderInterface;
use Symfony\Component\HttpFoundation\File\File;

class WooordHuntRuPronounceLoader extends WooordHuntRuAttributeLoader {
    private $audioDir;
    public function __construct( $audioDir, AttributeLoaderContext $context = null){
        $this->audioDir = $audioDir;
        parent::__construct($context);
    }
    public function load($spelling, $dialect=WordLoaderInterface::DIALECT_UK){
        $crawler = $this->getCrawler($spelling);
        if(is_null($crawler)) return false;
        $crawlerAudio = null;
        switch($dialect){
            case WordLoaderInterface::DIALECT_US:
                $crawlerAudio = $crawler->filter('#audio_us > source');
                break;
            case WordLoaderInterface::DIALECT_UK:
            default:
                $crawlerAudio = $crawler->filter('#audio_uk > source');
                break;
        }

        if($crawlerAudio->count() == 0 ) return false;
        $audioUrl = 'http://wooordhunt.ru'.($crawlerAudio->first()->attr('src'));
        $audioFileData = file_get_contents($audioUrl);
        if($audioFileData === false) return false;

        $filename = $spelling.'_'.md5($audioUrl);
        $maybeURLFileame = array_pop(explode('/',parse_url($audioUrl, PHP_URL_PATH)));
        $extensionWithDot = strrchr($maybeURLFileame,'.');
        if($extensionWithDot) $filename.=$extensionWithDot;
        $filepath = sprintf('%s/%s', $this->audioDir, $filename);
        file_put_contents($filepath, $audioFileData);
        return new File($filepath);
    }
} 