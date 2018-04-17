<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 19.01.2018
 * Time: 22:34
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader;

use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\AttributeLoaderContext;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\ChainedLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\SplitTranscriptionLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\Text2SpeechOrgPronounceLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\WooordHuntRuPronounceLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\WooordHuntRuTranscriptionLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\WooordHuntRuTranslationLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\YandexApiTranslationLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\YandexSimpleTranscriptionLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\YandexSimpleTranslationLoader;
use Psr\SimpleCache\CacheInterface;

class AppWordLoader extends BaseWordLoader {
    public function __construct( $audioDir,
                                 $apiKey = null,
                                 $requestRateDelay = 2,
                                 CacheInterface $yandexLoaderTimestampCache = null,
                                 CacheInterface $wordHuntLoaderTimestampCache = null)
    {
        $wooordHuntRuContext = new AttributeLoaderContext($requestRateDelay, $wordHuntLoaderTimestampCache);
        $yandexSimpleContext = new AttributeLoaderContext($requestRateDelay, $yandexLoaderTimestampCache);
        $translationLoaders = array();
        $translationLoaders[] = new YandexSimpleTranslationLoader($yandexSimpleContext);
        if($apiKey) $translationLoaders[] = new YandexApiTranslationLoader($apiKey);
        $this->translationLoader = new ChainedLoader($translationLoaders);
        $this->transcriptionLoader = new ChainedLoader(array(
            new SplitTranscriptionLoader(new YandexSimpleTranscriptionLoader($yandexSimpleContext)),
            new SplitTranscriptionLoader(new WooordHuntRuTranscriptionLoader($wooordHuntRuContext))
        ));

        $this->pronounceLoader = new ChainedLoader(array(
            new WooordHuntRuPronounceLoader($audioDir, $wooordHuntRuContext),
            new Text2SpeechOrgPronounceLoader($audioDir)
        ));




    }
} 