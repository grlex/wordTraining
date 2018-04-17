<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 10.04.2018
 * Time: 10:05
 */

namespace AppBundle\Service;


class BingImageSearcher {
    private $accessKey;
    private $endpointUrl;

    public function __construct($accessKey){
        $this->accessKey = $accessKey;
        $this->endpointUrl = 'https://api.cognitive.microsoft.com/bing/v7.0/images/search';
    }

    public function search($term){
        if (strlen($this->accessKey) == 32) {

            $jsonResult =  json_decode($this->doSearch($term), true);
            $value =  $jsonResult['value'];
            $images = [];
            foreach($value as $item){
                $images[] = array(
                    'name'=> $item['name'],
                    'url' => $item['thumbnailUrl']
                );
            }
            return $images;

        } else {
            throw new \Exception('Invalid bing API key format');
        }
    }

    private function doSearch ($query) {
        // Prepare HTTP request
        // NOTE: Use the key 'http' even if you are making an HTTPS request. See:
        // http://php.net/manual/en/function.stream-context-create.php
        $headers = "Ocp-Apim-Subscription-Key: {$this->accessKey}\r\n";
        $options = array ( 'http' => array (
            'header' => $headers,
            'method' => 'GET' ));

        // Perform the Web request and get the JSON response
        $context = stream_context_create($options);
        $result = file_get_contents($this->endpointUrl . "?q=" . urlencode($query), false, $context);

        return $result;
    }

} 