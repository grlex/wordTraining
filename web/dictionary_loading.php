<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 10.12.2017
 * Time: 15:14
 */

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

define('PROJECT_DIR', __DIR__.'/..');

function sendErrorResponse($error){
    $response = new JsonResponse(array(
        'error'=>$error
    ));
    $response->send();
    die();
}


$dictionaryId = @$_REQUEST['id'];
if(is_null($dictionaryId)) {
    sendErrorResponse('"id" query parameter with dictionary id required');
}
$parameters = Yaml::parse(file_get_contents(__DIR__.'/../app/config/parameters.yml'));
$parameters = $parameters['parameters'];
$phpInterpreter = @$parameters['php_interpreter'] ?: null;
if(is_null($phpInterpreter))
    sendErrorResponse('"php_enterpreter parameter in the parameters.yml config is not set');
$dictionarySourcesDir = @$parameters['app.dictionary_sources_dir'] ?: null;
if(is_null($dictionarySourcesDir))
    sendErrorResponse('"app.dictionary_sources_dir parameter in the parameters.yml config is not set');
$dictionarySourcesDir = PROJECT_DIR.'/'.$dictionarySourcesDir;

$webAudioDir = @$parameters['app.web_words_audio_dir'] ?: null;
if(is_null($webAudioDir))
    sendErrorResponse('"app.web_words_audio_dir parameter in the parameters.yml config is not set');
//




$progressFile = sprintf('%s/%s/%d.json', PROJECT_DIR, 'var/dictionaryLoading', $dictionaryId);
if(file_exists($progressFile)){
    $progress = json_decode(file_get_contents($progressFile),true);
    if($progress['done']==false && array_key_exists('cancel', $_POST)){
        $progressFileHandler = fopen($progressFile,'w');
        flock($progressFileHandler, LOCK_EX);
        $progress['cancel'] = true;
        fwrite($progressFileHandler, json_encode($progress));
        flock($progressFileHandler, LOCK_UN);
        fclose($progressFileHandler);
    }
    if(array_key_exists('cancelled', $progress)){
        unlink($progressFile);
    }
    $response = new JsonResponse($progress);
    $response->send();

}else if(array_key_exists('start', $_POST)){
    file_put_contents($progressFile, json_encode(array(
        'done'=>false,
        'loaded'=> 0,
        'total'=>0
    )));


    $command = sprintf('"%s" "%s" %s %s %s %s %d',
        $phpInterpreter,
        __DIR__.'/../bin/console',
        'app:dictionary:load',
        sprintf('--source-dir="%s"', $dictionarySourcesDir),
        sprintf('--web-audio-dir="%s"', $webAudioDir),
        sprintf('--progress-file="%s"', $progressFile),
        $dictionaryId
    );

    $process = new Process($command);
    $process->start();
    $process->wait(function ($type, $buffer) use ($progressFile) {
        if (Process::ERR === $type) {
            header('Content-Type: text/html;charset=IBM866');
            echo 'ERR > '.$buffer;
        } else {
            $progress = json_decode(file_get_contents($progressFile),true);
            if(array_key_exists('cancel', $progress) && $progress['cancel']){
                $progress['cancelled'] = true;
                file_put_contents($progressFile, json_encode($progress));
            }
            echo 'OUT > '.$buffer;
        }
    });
}