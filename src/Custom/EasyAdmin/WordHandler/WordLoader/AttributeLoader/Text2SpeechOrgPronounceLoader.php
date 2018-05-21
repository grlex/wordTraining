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

class Text2SpeechOrgPronounceLoader implements WordAttributeLoaderInterface {
    const VOICE_US_MALE = 'rms';
    const VOICE_US_FEMALE = 'slt';
    private $audioDir;
    private $context;
    private $client;
    private $formParams;
    public function __construct( $audioDir, AttributeLoaderContext $context = null, $voice = self::VOICE_US_MALE){
        $this->audioDir = $audioDir;
        $this->context = $context ?: new AttributeLoaderContext(1);
        $this->client = new \Goutte\Client();
        $this->formParams = array(
            'voice' => $voice,
            'speed' => 1,
            'outname' => 'speech',
            'text' => 'text to pronounce'
        );
    }
    public function load($spelling, $dialect=WordLoaderInterface::DIALECT_UK){
        $this->context->takeRequestRateDelay();
        $this->formParams['text'] = $spelling;
        $this->client->request('post', 'https://www.text2speech.org/', $this->formParams);


        $response = $this->client->getResponse();
        if($response->getStatus() !== 200) return false;

        $matches = array();
        preg_match("/var url = '(\\/FW\\/result\\.php\\?name=[a-z0-9]+)';/", $response->getContent(), $matches);

        if(count($matches)==0) return false;

        sleep(3); // poll interval

        $crawler = $this->client->request('get', 'http://www.text2speech.org'.$matches[1]);
        if($response->getStatus() !== 200) return false;

        $crawler = $crawler->filter('#download-result a')->first();

        if($crawler->count()==0) return false;

        $audioUrl = 'http://www.text2speech.org'.$crawler->attr('href');


        $audioFileData = file_get_contents($audioUrl);
        if($audioFileData === false) return false;

        $filename = preg_replace('/[^a-z]/','', $spelling).'_'. md5($audioUrl);

        $maybeURLFileame = array_pop(explode('/',parse_url($audioUrl, PHP_URL_PATH)));
        $extensionWithDot = strrchr($crawler->text(),'.');
        $filename.=$extensionWithDot;
        $filepath = sprintf('%s/%s', $this->audioDir, $filename);
        file_put_contents($filepath, $audioFileData);
        return new File($filepath);
    }
} 