<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 19.01.2018
 * Time: 18:17
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader;


use Custom\EasyAdmin\WordHandler\WordLoader\WordLoaderInterface;

class WooordHuntRuTranslationLoader extends WooordHuntRuAttributeLoader {
    public function load($spelling, $dialect=WordLoaderInterface::DIALECT_UK){
        $crawler = $this->getCrawler($spelling);
        if(is_null($crawler)) return false;
        $crawlerTrans = $crawler->filter('#wd_content > .t_inline_en');
        if($crawlerTrans->count() == 0 ) {
            $crawlerTrans = $crawler->filter('#wd_content > .light_tr');
        }
        if($crawlerTrans->count() == 0 ) {
            return false;
        }
        $translations = $crawlerTrans->text();
        return $translations;
        /*
        $translations = array_unique(preg_split('/, |, /', $translations));
        return $translations[0];
        */
    }
} 