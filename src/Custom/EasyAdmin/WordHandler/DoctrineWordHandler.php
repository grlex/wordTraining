<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 16.01.2018
 * Time: 13:53
 */

namespace Custom\EasyAdmin\WordHandler;


use AppBundle\Entity\Word;
use AppBundle\Entity\WordAttribute;
use AppBundle\Entity\WordPronounce;
use AppBundle\Entity\WordSpelling;
use AppBundle\Entity\WordTranscription;
use AppBundle\Entity\WordTranslation;
use Custom\EasyAdmin\WordHandler\Event\TryLoadEvent;
use Custom\EasyAdmin\WordHandler\Event\WaitingEvent;
use Custom\EasyAdmin\WordHandler\Exception\AbortedException;
use Custom\EasyAdmin\WordHandler\Exception\LoadingException;
use Custom\EasyAdmin\WordHandler\WordLoader\WordLoaderInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;

class DoctrineWordHandler implements WordHandlerInterface {
    private $doctrine;
    private $loader;
    private $eventDispatcher;
    private $soundDir;
    private $pictureDir;
    private $waitingDelay;
    private $unavailableAttributeText;
    public function __construct(Registry $doctrine, WordLoaderInterface $loader, $soundDir, $pictureDir, EventDispatcherInterface $eventDispatcher=null, $waitingDelay = 2){
        $this->doctrine = $doctrine;
        $this->loader = $loader;
        $this->eventDispatcher = $eventDispatcher;
        $this->soundDir = $soundDir;
        $this->pictureDir = $pictureDir;
        $this->waitingDelay = $waitingDelay;
        $this->unavailableAttributeText = '---';
    }

    public function handleWordAttributes(Word $word){
        $this->handleWordAttribute($word, $word->getSpelling());
        $this->handleWordAttribute($word, $word->getTranslation());
        $this->handleWordAttribute($word, $word->getTranscription());
        $this->handleWordAttribute($word, $word->getPronounce());
        foreach($word->getPictures() as $picture){
            $this->handleWordAttribute($word, $picture);
        }
    }
    private function handleWordAttribute(Word $word, WordAttribute $attribute=null){
        if(is_null($attribute)) return null;
        switch($attribute->getStatus()){
            case WordAttribute::STATUS_DONE:
            case WordAttribute::STATUS_UNAVAILABLE:
                break;
            case WordAttribute::STATUS_AUTO:
                $this->handleWordAttributeAutoState($word, $attribute);
                break;
            case WordAttribute::STATUS_AUTO_LOADING:
                $this->handleWordAttributeAutoLoadingState($word, $attribute);
                break;
            case WordAttribute::STATUS_LINK:
                $this->handleWordAttributeLinkState($word, $attribute);
                break;
            case WordAttribute::STATUS_LINK_LOADING:
                $this->handleWordAttributeLinkLoadingState($word, $attribute);
                break;
            case WordAttribute::STATUS_MIC:
                $this->handleWordAttributeMicState($word, $attribute);
                break;
            case WordAttribute::STATUS_PICTURE_LINK:
                $this->handleWordAttributePictureLinkState($word, $attribute);
                break;
            case WordAttribute::STATUS_PICTURE_LINK_LOADING:
                $this->handleWordAttributePictureLinkLoadingState($word, $attribute);
                break;
        }
    }
    private function handleWordAttributeAutoState(Word $word, WordAttribute $attribute){
        $nowStatus = $this->doctrine->getManager()->getRepository(get_class($attribute))->getStatus($attribute->getId());
        if($nowStatus==WordAttribute::STATUS_AUTO){
            $this->doctrine->getManager()->getRepository(get_class($attribute))->setStatus($attribute->getId(), WordAttribute::STATUS_AUTO_LOADING);
        }
        else{
            $this->doctrine->getManager()->refresh($attribute);
            $this->handleWordAttribute($word, $attribute);
            return;
        }
        $loadingAttempts = 0;
        $success = true;
        do {
            $event = $this->dispatchTryLoadEvent($word, $attribute, ++$loadingAttempts);
            if ($event->isAborted()) {
                $this->doctrine->getManager()->getRepository(get_class($attribute))->setStatus($attribute->getId(), WordAttribute::STATUS_AUTO);
                throw new AbortedException();
            }
            try {
                $success = $this->autoloadWordAttribute($word, $attribute);
            }
            catch(LoadingException $ex){
                continue;
            }
            catch(\Exception $ex){
                $this->doctrine->getManager()->getRepository(get_class($attribute))->setStatus($attribute->getId(), WordAttribute::STATUS_AUTO);
                throw $ex;
            }
            break;
        }while(true);

        $attribute->setStatus($success ? WordAttribute::STATUS_DONE : WordAttribute::STATUS_UNAVAILABLE);
        $this->doctrine->getManager()->persist($attribute);
        $this->doctrine->getManager()->flush();
    }
    private function handleWordAttributeAutoLoadingState(Word $word, WordAttribute $attribute){
        $waitingStartTime = time();
        do{
            $event = $this->dispatchWaitingEvent($word, $attribute, time()-$waitingStartTime);
            if($event->isAborted()) {
                throw new AbortedException();
            }
            sleep($this->waitingDelay);
            $nowStatus = $this->doctrine->getManager()->getRepository(get_class($attribute))->getStatus($attribute->getId());
            if($nowStatus==WordAttribute::STATUS_AUTO_LOADING){
                continue;
            }
            $this->doctrine->getManager()->refresh($attribute);
            $this->handleWordAttribute($word, $attribute);
            break;
        }while(true);
    }
    private function handleWordAttributeLinkState(Word $word, WordAttribute $attribute){
        $nowStatus = $this->doctrine->getManager()->getRepository(get_class($attribute))->getStatus($attribute->getId());
        if($nowStatus==WordAttribute::STATUS_LINK){
            $this->doctrine->getManager()->getRepository(get_class($attribute))->setStatus($attribute->getId(), WordAttribute::STATUS_LINK_LOADING);
        }
        else{
            $this->doctrine->getManager()->refresh($attribute);
            $this->handleWordAttribute($word, $attribute);
            return;
        }
        $loadingAttempts = 0;
        $success = true;
        do {
            $event = $this->dispatchTryLoadEvent($word, $attribute, ++$loadingAttempts);
            if ($event->isAborted()) {
                $this->doctrine->getManager()->getRepository(get_class($attribute))->setStatus($attribute->getId(), WordAttribute::STATUS_LINK);
                throw new AbortedException();
            }
            try {
                $url = $attribute->getAudioData()->getData();
                $filename = $word->getSpelling()->getText().'_'.md5($url);
                $maybeFileame = array_pop(explode('/',parse_url($url, PHP_URL_PATH)));
                $extensionWithDot = strrchr($maybeFileame,'.');
                if($extensionWithDot) $filename.=$extensionWithDot;
                $audioFileData = file_get_contents($url);
                if($audioFileData === false) {
                    $success = false;
                }
                $filepath = $this->soundDir.'/'.$filename;
                file_put_contents($filepath, $audioFileData);
                $attribute->setAudioFilename($filename);
            }
            catch(\Exception $ex){
                throw $ex;
                continue;
            }
            break;
        }while(true);
        $attribute->setStatus($success ? WordAttribute::STATUS_DONE : WordAttribute::STATUS_UNAVAILABLE);
        $this->doctrine->getManager()->persist($attribute);
        $this->doctrine->getManager()->flush();
    }
    private function handleWordAttributeLinkLoadingState(Word $word, WordAttribute $attribute){
        $waitingStartTime = time();
        do{
            $event = $this->dispatchWaitingEvent($word, $attribute, time()-$waitingStartTime);
            if($event->isAborted()) {
                throw new AbortedException();
            }
            sleep($this->waitingDelay);
            $nowStatus = $this->doctrine->getManager()->getRepository(get_class($attribute))->getStatus($attribute->getId());
            if($nowStatus==WordAttribute::STATUS_LINK_LOADING){
                continue;
            }
            $this->doctrine->getManager()->refresh($attribute);
            $this->handleWordAttribute($word, $attribute);
            break;
        }while(true);
    }
    private function handleWordAttributeMicState(Word $word, WordAttribute $attribute){
        $audioData = $attribute->getAudioData();
        $fileData = $audioData->getData();
        $fileData = base64_decode($fileData);
        $filename = $word->getSpelling()->getText().'_'.md5(uniqid(rand(),true)).'.wav';
        $filepath = $this->soundDir.'/'.$filename;
        file_put_contents($filepath, $fileData);
        $attribute->setStatus(WordAttribute::STATUS_DONE);
        $attribute->setAudioFilename($filename);
        $attribute->setAudioData(null);
        $this->doctrine->getManager()->remove($audioData);
        $this->doctrine->getManager()->persist($attribute);
        $this->doctrine->getManager()->flush();
    }

    private function handleWordAttributePictureLinkState(Word $word, WordAttribute $attribute){
        $nowStatus = $this->doctrine->getManager()->getRepository(get_class($attribute))->getStatus($attribute->getId());
        if($nowStatus==WordAttribute::STATUS_PICTURE_LINK){
            $this->doctrine->getManager()->getRepository(get_class($attribute))->setStatus($attribute->getId(), WordAttribute::STATUS_PICTURE_LINK_LOADING);
        }
        else{
            $this->doctrine->getManager()->refresh($attribute);
            $this->handleWordAttribute($word, $attribute);
            return;
        }
        $loadingAttempts = 0;
        $success = true;
        do {
            $event = $this->dispatchTryLoadEvent($word, $attribute, ++$loadingAttempts);
            if ($event->isAborted()) {
                $this->doctrine->getManager()->getRepository(get_class($attribute))->setStatus($attribute->getId(), WordAttribute::STATUS_PICTURE_LINK);
                throw new AbortedException();
            }
            try {
                $url = $attribute->getUrl();
                $pictureData = file_get_contents($url);
                if($pictureData === false) {
                    $success = false;
                }

                $filename = $word->getSpelling()->getText().'_'.md5($url);
                $maybeFileame = array_pop(explode('/',parse_url($url, PHP_URL_PATH)));
                $extensionWithDot = strrchr($maybeFileame,'.');
                if(!$extensionWithDot) {
                    $contentType = preg_grep('/^Content-Type/', $http_response_header);
                    $contentType = trim(explode(':',array_pop($contentType))[1]);

                    switch($contentType){
                        case 'image/jpeg':
                            $extensionWithDot = '.jpg';
                            break;
                        case 'image/bmp':
                        case 'image/x-windows-bmp':
                            $extensionWithDot = '.bmp';
                            break;
                        case 'image/png':
                            $extensionWithDot = '.png';
                            break;
                        case 'image/gif':
                            $extensionWithDot = '.gif';
                            break;
                    }
                }
                $filename.=$extensionWithDot;
                $filepath = $this->pictureDir.'/'.$filename;
                file_put_contents($filepath, $pictureData);
                $attribute->setFilename($filename);
            }
            catch(\Exception $ex){
                throw $ex;
                continue;
            }
            break;
        }while(true);
        $attribute->setStatus($success ? WordAttribute::STATUS_DONE : WordAttribute::STATUS_UNAVAILABLE);
        $this->doctrine->getManager()->persist($attribute);
        $this->doctrine->getManager()->flush();
    }
    private function handleWordAttributePictureLinkLoadingState(Word $word, WordAttribute $attribute){
        $waitingStartTime = time();
        do{
            $event = $this->dispatchWaitingEvent($word, $attribute, time()-$waitingStartTime);
            if($event->isAborted()) {
                throw new AbortedException();
            }
            sleep($this->waitingDelay);
            $nowStatus = $this->doctrine->getManager()->getRepository(get_class($attribute))->getStatus($attribute->getId());
            if($nowStatus==WordAttribute::STATUS_PICTURE_LINK_LOADING){
                continue;
            }
            $this->doctrine->getManager()->refresh($attribute);
            $this->handleWordAttribute($word, $attribute);
            break;
        }while(true);
    }

    private function autoloadWordAttribute(Word $word, WordAttribute &$attribute){
        $wordSpelling = $word->getSpelling();
        $spellingText = $wordSpelling->getText();

        $attributeName = '';
        $loaderMethod = '';
        if($attribute instanceof WordTranslation){
            $attributeName = "Translation";
            $loaderMethod = "loadTranslation";
        }
        else if($attribute instanceof WordTranscription) {
            $attributeName = "Transcription";
            $loaderMethod = "loadTranscription";
        }
        else if($attribute instanceof WordPronounce) {
            $attributeName = "Pronounce";
            $loaderMethod = "loadPronounce";
        }

        /*$autoAttribute = call_user_func([ $wordSpelling, 'getAuto'.$attributeName]);
        if($autoAttribute) {
            $this->doctrine->getManager()->remove($attribute);
            $attribute = $autoAttribute;
            call_user_func([ $word, 'set'.$attributeName], $autoAttribute);
            return $autoAttribute->getStatus() != WordAttribute::STATUS_UNAVAILABLE;
        }*/

        $text = call_user_func([$this->loader, $loaderMethod], $spellingText);

        if($text===false) {
            $unavailableAttribute = $this->doctrine->getManager()->getRepository(get_class($attribute))->findOneByStatus(WordAttribute::STATUS_UNAVAILABLE);
            if($unavailableAttribute){
                $this->doctrine->getManager()->remove($attribute);
                $attribute = $unavailableAttribute;
                call_user_func([ $word, 'set'.$attributeName], $unavailableAttribute);
            }
            else {
                $attribute->setText($this->unavailableAttributeText);
            }
            call_user_func([ $wordSpelling, 'setAuto'.$attributeName], $attribute);
            return false;
        }

        $text = $text instanceof File ? $text->getFilename() : $text;
        $existedAttribute = null;
        if($attributeName!='Pronounce') {
            $existedAttribute = $this->doctrine->getManager()->getRepository(get_class($attribute))->findOneByText($text);
        }
        if($existedAttribute){
            $this->doctrine->getManager()->remove($attribute);
            $attribute = $existedAttribute;
            call_user_func([ $word, 'set'.$attributeName], $existedAttribute);
        }
        else{
            $attribute->setText($text);
        }

        call_user_func([ $wordSpelling, 'setAuto'.$attributeName], $attribute);

        return true;
    }

    private function dispatchTryLoadEvent(Word $word, WordAttribute $attribute, $attempts){
        $event = new TryLoadEvent($word, $attribute, $attempts);
        if($this->eventDispatcher){
            $this->eventDispatcher->dispatch(TryLoadEvent::NAME, $event);
        }
        return $event;
    }
    private function dispatchWaitingEvent(Word $word, WordAttribute $attribute, $waitingSeconds){
        $event = new WaitingEvent($word, $attribute, $waitingSeconds);
        if($this->eventDispatcher){
            $this->eventDispatcher->dispatch(WaitingEvent::NAME, $event);
        }
        return $event;
    }
}