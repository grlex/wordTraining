<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 19.01.2018
 * Time: 18:01
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader;


use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\ArrayCache;


class AttributeLoaderContext {
    private  $requestRateDelay;
    private  $timeCache;
    private  $spelling;
    private  $data;

    public function __construct( $requestRateDelay = 2, CacheInterface $timeCache = null){
        $this->requestRateDelay = $requestRateDelay*1000000; // us
        $this->timeCache = $timeCache ?: new ArrayCache();
        $this->spelling = null;
    }

    public function takeRequestRateDelay(){

        $cachedTime = $this->timeCache->get('wordLoader.timestamp',0);
        if(microtime()-$cachedTime < $this->requestRateDelay){
            usleep($this->requestRateDelay+rand(1000,1000000));
        }
        $this->timeCache->set('wordLoader.timestamp', microtime());
    }

    public function getSpelling(){
        return $this->spelling;
    }

    public function setSpelling($spelling){
        $this->spelling = $spelling;
        return $this;
    }

    public function getData(){
        return $this->data;
    }

    public function setData($value){
        $this->data = $value;
        return $this;
    }
} 