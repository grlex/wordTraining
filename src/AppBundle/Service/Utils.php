<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 30.11.2017
 * Time: 15:17
 */

namespace AppBundle\Service;


class Utils {
    public static function rrmdir($path) {
        $dir = opendir($path);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $full = $path . '/' . $file;
                if ( is_dir($full) ) {
                    self::rrmdir($full);
                }
                else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($path);
    }
    public static function translit($str){
        $rusLower = self::mbStringToArray('абвгдеёжзийклмнопрстуфхцчшщъыьэюя');
        $rusUpper = self::mbStringToArray('АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ');
        $rusToEng = mb_split('\|','a|b|v|g|d|e|yo|g|z|i|y|k|l|m|n|o|p|r|s|t|u|f|h|c|ch|sh|shy||i||e|yu|ya');
        $strArr = self::mbStringToArray($str);
        $ret = '';
        foreach($strArr as $char){
            if(($key=array_search($char,$rusLower))!==false){
                $ret.=$rusToEng[$key];
            }
            else if(($key=array_search($char,$rusUpper))!==false){
                $ret.=$rusToEng[$key];
            }
            else{
                $ret.=$char;
            }
        }
        return $ret;
    }
    public static function mbStringToArray ($string) {
        $strlen = mb_strlen($string);
        $array = [];
        while ($strlen) {
            $array[] = mb_substr($string,0,1,"UTF-8");
            $string = mb_substr($string,1,$strlen,"UTF-8");
            $strlen = mb_strlen($string);
        }
        return $array;
    }

    public static function filesystemPath($path){
        $os = strtoupper(substr(php_uname('s'),0,3));
        switch($os){

            case 'WIN':
                return iconv('UTF-8', 'Windows-1251', $path);
                break;
            default:
                return $path;
        }
    }
} 