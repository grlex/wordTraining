<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 30.11.2017
 * Time: 13:54
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader;


use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\AttributeLoaderContext;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\WooordHuntRuAudioFileLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\WooordHuntRuTranscriptionLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\WooordHuntRuTranslationLoader;

use Psr\SimpleCache\CacheInterface;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\SplitTranscriptionLoader;




class WooordHuntRuWordLoader extends BaseWordLoader{

    public function __construct( $audioDir, $requestRateDelay = 2, CacheInterface $timeCache = null){

        $context = new AttributeLoaderContext($requestRateDelay, $timeCache);
        $this->translationLoader = new WooordHuntRuTranslationLoader($context);
        $this->transcriptionLoader = new SplitTranscriptionLoader(new WooordHuntRuTranscriptionLoader($context));
        $this->audioFileLoader = new WooordHuntRuAudioFileLoader($audioDir, $context);
    }
}