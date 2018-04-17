<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 10.04.2018
 * Time: 14:29
 */

namespace AppBundle\Service;


use Symfony\Component\HttpFoundation\JsonResponse;

class GoogleImageSearcher {
    private $urlTemplate;

    /**
     * @param $engineID string  Google custom search engine id
     * @param $apiKey string Key to use JSON/Atom api interface of the custom search engine
     */
    public function __construct($engineID, $apiKey){
        $this->urlTemplate = 'https://www.googleapis.com/customsearch/v1?searchType=image'
            ."&cx=$engineID"
            ."&key=$apiKey"
            .'&q=%s';
    }
    public function search($term){

        $url = sprintf($this->urlTemplate, urlencode($term));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $jsonResult = curl_exec($ch);
        $jsonResult = json_decode($jsonResult, true);
        $items =  $jsonResult['items'];
        $images = [];
        foreach($items as $item){
            $images[] = array(
                'title'=> $item['title'],
                'url' => $item['image']['thumbnailLink']
            );
        }
        return $images;
    }
} 