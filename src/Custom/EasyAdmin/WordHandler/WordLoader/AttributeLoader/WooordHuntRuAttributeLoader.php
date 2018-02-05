<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 19.01.2018
 * Time: 17:50
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader;

use Custom\EasyAdmin\WordHandler\Exception\LoadingException;
use Custom\EasyAdmin\WordHandler\WordLoader\WordLoaderInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\File;

abstract class WooordHuntRuAttributeLoader implements WordAttributeLoaderInterface {
    private $context;
    private $client;

    public function __construct( AttributeLoaderContext $context = null){
        if(is_null($context)){
            $context = new AttributeLoaderContext();
        }
        $this->context = $context;
        $this->client = new \Goutte\Client();
    }

    protected function getCrawler($spelling){
        if($this->context->getSpelling()==$spelling) {
            return $this->context->getData();
        }
        $this->context->setSpelling($spelling);
        $this->context->takeRequestRateDelay();

        $crawler = $this->client->request('GET', 'http://wooordhunt.ru/word/'.urlencode($spelling));

        if($this->client->getResponse()->getStatus()!=200){
            $crawler =  null;
        }
        $this->context->setData($crawler);
        return $crawler;
    }
} 