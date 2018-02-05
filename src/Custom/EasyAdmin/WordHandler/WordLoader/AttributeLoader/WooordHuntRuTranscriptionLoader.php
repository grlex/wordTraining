<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 19.01.2018
 * Time: 18:17
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader;


use Custom\EasyAdmin\WordHandler\WordLoader\WordLoaderInterface;

class WooordHuntRuTranscriptionLoader extends WooordHuntRuAttributeLoader {
    public function load($spelling, $dialect = WordLoaderInterface::DIALECT_UK){
        $crawler = $this->getCrawler($spelling);
        if(is_null($crawler)) return false;
        $crawlerTrans = null;
        switch($dialect){
            case WordLoaderInterface::DIALECT_US:
                $crawlerTrans = $crawler->filter('#us_tr_sound > .transcription');
                break;
            case WordLoaderInterface::DIALECT_UK:
            default:
                $crawlerTrans = $crawler->filter('#uk_tr_sound > .transcription');
                break;
        }
        if($crawlerTrans->count()==0) return false;
        $transcription = $crawlerTrans->text();
        $transcription = sprintf('%s', trim($transcription,'| ') );

        return $transcription;
    }
} 