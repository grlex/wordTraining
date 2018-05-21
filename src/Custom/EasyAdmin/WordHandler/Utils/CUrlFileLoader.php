<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 29.04.2018
 * Time: 17:36
 */

namespace Custom\EasyAdmin\WordHandler\Utils;


use Symfony\Component\Config\Definition\Exception\Exception;

class CUrlFileLoader {
    public static function download($url, $saveFilePath, $guessExtension = true){
        //$url = 'http://www.sibnet.ru/uploads/content/20180430/d/d/dd62c89c79835578bf36ed654e85b3a9.jpg';
        $tmpFilePath = $saveFilePath.'.tmp';

        $fp = fopen ($tmpFilePath, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        if($guessExtension) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        //if(curl_error($ch)) throw new \Exception(curl_error($ch));
        fclose($fp);
        $guessedExtension = "";
        if($guessExtension){
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $fs = fopen($tmpFilePath, 'r');
            $responseHeadersStr = fread($fs, $headerSize);
            $contentType = self::getContentTypeHeader($responseHeadersStr);
            if($contentType) {
                $guessedExtension =  self::getExtension($contentType);
                $saveFilePath .= '.' . $guessedExtension;
            }
            $fd = fopen($saveFilePath, 'w');
            stream_copy_to_stream($fs, $fd);
            fclose($fs);
            fclose($fd);
            unlink($tmpFilePath);
        }
        else{
            rename($tmpFilePath, $saveFilePath);
        }
        curl_close($ch);
        return $guessedExtension;
    }

    private static function getContentTypeHeader($headersStr){
        $matches = array();
        preg_match('/Content-Type:([^\n\r;]+)/', $headersStr, $matches);
        return trim(array_pop($matches));
    }


    private static function getExtension($contentType){
        switch($contentType){
            /*
             * images
             */
            case 'image/jpeg':
                return 'jpg';
            case 'image/bmp':
            case 'image/x-windows-bmp':
                return 'bmp';
            case 'image/png':
                return 'png';
            case 'image/gif':
                return 'gif';
            /*
             * audios
             */
            case 'audio/x-wav':
            case 'audio/wav':
                return 'wav';
            case 'audio/mpeg':
                return 'mp3';
            default:
                return '';
        }
    }
} 