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

class VoiceRssOrgPronounceLoader implements WordAttributeLoaderInterface{
    private $audioDir;
    private $queryParams;
    public function __construct( $audioDir, $apiKey){
        $this->audioDir = $audioDir;
        $this->queryParams = array(
            'key'=> $apiKey,//'f66cc21a4a85459a93cf255b89d6b787',
            'hl'=>'en-us',
            'f'=>'48khz_16bit_stereo',
            'src'=>''
        );

    }
    public function load($spelling, $dialect=WordLoaderInterface::DIALECT_UK){

        $this->queryParams['src'] = $spelling;
        $url = 'http://api.voicerss.org/?'.http_build_query($this->queryParams);
        $data = file_get_contents($url);
        $name = str_replace(array(' ','\''), '_', $spelling);
        $filepath = sprintf('%s/%s.mp3', $this->audioDir, $name );
        file_put_contents($filepath, $data);
        return new File($filepath);
    }
} 