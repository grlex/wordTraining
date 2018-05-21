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
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\VoiceRssOrgPronounceLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\WooordHuntRuPronounceLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\WooordHuntRuTranscriptionLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\WooordHuntRuTranslationLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\YandexApiTranslationLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\YandexSimpleTranscriptionLoader;
use Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader\YandexSimpleTranslationLoader;
use Psr\SimpleCache\CacheInterface;

class AppWordLoader extends BaseWordLoader {
    public function __construct( $audioDir,
                                 $yandexApiKey = null,
                                 $voiceRssApiKey = null,
                                 $requestRateDelay = 2,
                                 CacheInterface $yandexLoaderTimestampCache = null,
                                 CacheInterface $wordHuntLoaderTimestampCache = null)
    {
        $wooordHuntRuContext = new AttributeLoaderContext($requestRateDelay, $wordHuntLoaderTimestampCache);
        $yandexSimpleContext = new AttributeLoaderContext($requestRateDelay, $yandexLoaderTimestampCache);
        $translationLoaders = array();
        $translationLoaders[] = new YandexSimpleTranslationLoader($yandexSimpleContext);
        if($yandexApiKey) $translationLoaders[] = new YandexApiTranslationLoader($yandexApiKey);
        $this->translationLoader = new ChainedLoader($translationLoaders);
        $this->transcriptionLoader = new ChainedLoader(array(
            new SplitTranscriptionLoader(new YandexSimpleTranscriptionLoader($yandexSimpleContext)),
            new SplitTranscriptionLoader(new WooordHuntRuTranscriptionLoader($wooordHuntRuContext))
        ));

        $pronounceLoaders = array();
        $pronounceLoaders[] = new WooordHuntRuPronounceLoader($audioDir, $wooordHuntRuContext);
        if($voiceRssApiKey) $pronounceLoaders[] = new VoiceRssOrgPronounceLoader($audioDir, $voiceRssApiKey);
        $this->pronounceLoader = new ChainedLoader($pronounceLoaders);
    }


} 