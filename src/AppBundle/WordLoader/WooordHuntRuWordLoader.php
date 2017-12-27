<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 30.11.2017
 * Time: 13:54
 */

namespace AppBundle\WordLoader;


use AppBundle\Entity\Example;
use AppBundle\Entity\Translation;
use AppBundle\Entity\Word;
use AppBundle\Entity\WordForm;

use AppBundle\WordLoader\Exception\AbortedException;
use AppBundle\WordLoader\Exception\WordFormLoadingException;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\PessimisticLockException;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Validator\Constraints\File;
use AppBundle\WordLoader\Event\WaitingEvent;
use AppBundle\WordLoader\Event\TryLoadEvent;



class WooordHuntRuWordLoader implements WordLoaderInterface{


    private $sleepRequestInterval;
    private $soundDir;
    private $timeCache;
    private $doctrine;
    private $formsLoadingDiveLevel;
    private $eventDispatcher;
    public function __construct( $soundDir, Registry $doctrine, $requestInterval = 2, EventDispatcherInterface $eventDispatcher = null, CacheInterface $timeCache = null){
        $this->sleepRequestInterval = $requestInterval*1000000;
        $this->soundDir  = $soundDir;
        $this->timeCache = $timeCache;
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrine = $doctrine;
        $this->formsLoadingDiveLevel = 0;
        if(!file_exists($soundDir)) mkdir($soundDir,0777, true);
    }

    public function loadWord(Word $word){

        if($this->distributeLoading($word)===true){
            return true;
        }

        $loadingAttempts = 0;
        try {
            do {
                $event = $this->dispatchTryLoadEvent($word, $loadingAttempts++);
                if ($event->isAborted()) {
                    $this->updateWordStatus($word, Word::STATUS_PENDING);
                    throw new AbortedException();
                }
                $this->takeRequestRateDelay();
            } while (!$this->doLoadWord($word));
        } catch(AbortedException $ex){
            $this->updateWordStatus($word, Word::STATUS_PENDING);
            throw $ex;
        }
        return true;
    }

    private function distributeLoading($word){
        if(!$word->getSpelling()) {
            $this->updateWordStatus($word, Word::STATUS_INCORRECT);
            return true;
        }
        switch($word->getStatus()){
            case Word::STATUS_INCORRECT:
            case Word::STATUS_TRANSLATED:
                return true;
            case Word::STATUS_LOADING:
                $time = time();
                do{
                    usleep($this->sleepRequestInterval);
                    $event = $this->dispatchWaitingEvent($word, time()-$time);
                    if($event->isAborted()) {
                        throw new AbortedException();
                    }
                    $this->refreshWordStatus($word);
                    $status = $word->getStatus();
                } while ($status == Word::STATUS_LOADING);
                return $this->distributeLoading($word);
            case Word::STATUS_PENDING:
                try {
                    $em = $this->doctrine->getManager();
                    $word = $em->find(Word::class, $word->getId(), LockMode::PESSIMISTIC_WRITE); // lock word entry
                    if ($word->getStatus() == Word::STATUS_PENDING) {
                        $word->setStatus(Word::STATUS_LOADING);
                        $em->flush($word); // unlock word entry
                        break;
                    }
                    // else => 2 or more processes try to lock same entry ( only one will load, others will be waiting )
                    $em->flush($word); // unlock word entry
                }
                catch(PessimisticLockException $ex){
                    if ($this->formsLoadingDiveLevel > 0) {
                        // if we are loading form word, we go back to "root" word and try to load forms again
                        // ( see  parseForms() method )
                        throw new WordFormLoadingException();
                    }
                }
                return $this->distributeLoading($word); // go to  "case Word::STATUS_LOADING" and wait
        };

        return 'load';
    }

    private function updateWordStatus(Word $word, $status){
        $this->doctrine->getManager()->getRepository(Word::class)->setStatus($word->getId(), $status);
        $word->setStatus($status);
    }
    private function refreshWordStatus(Word $word){
        $status = $this->doctrine->getManager()->getRepository(Word::class)->getStatus($word->getId());
        $word->setStatus($status);
    }


    private function doLoadWord(Word $word){

        $client = new \Goutte\Client();
        $crawler = $client->request('GET', 'http://wooordhunt.ru/word/'.$word->getSpelling());

        if($client->getResponse()->getStatus()!=200){
            return false;
        }

        if(!$this->checkWordIsCorrect($crawler)){
            $this->updateWordStatus($word, Word::STATUS_INCORRECT);
            return true;
        }

        $this->parseTranscription($crawler, $word);
        $this->parseTranslations($crawler, $word);
        $this->parseExamples($crawler, $word);
        $this->parseAndLoadSound($crawler, $word);
        $word = $this->parseForms($crawler, $word);
        $word->setStatus(Word::STATUS_TRANSLATED);

        $em =  $this->doctrine->getManager();
        $em->flush($word);
        return true;
    }

    private function checkWordIsCorrect(Crawler $crawler){
        return $crawler->filter('#wd_title > .trans_sound')->count() > 0;
    }
    private function parseTranslations(Crawler $crawler, Word $word){
        $crawler = $crawler->filter('#wd_content > .t_inline_en');
        if($crawler->count() == 0 ) return;
        $translations = $crawler->text();
        $translations = array_unique(mb_split(', ', $translations));
        foreach($translations as $translation){
            $translationEntity = new Translation();
            $translationEntity->setMeaning($translation);
            $word->addTranslation($translationEntity);
        }
    }
    private function parseTranscription(Crawler $crawler, Word $word){
        $crawler = $crawler->filter('#us_tr_sound > .transcription');
        if($crawler->count() == 0 ) return;
        $transcription = $crawler->text();
        $transcription = sprintf('%s', trim($transcription,'| ') );
        $word->setTranscription($transcription);
    }
    private function parseExamples(Crawler $crawler, Word $word){
        $exampleNodes = $crawler->filter('#wd_content > .block > .ex_t');
        $exampleNodes->each(function(Crawler $nodeCrawler) use ($word){
            $exampleEntity = new Example();
            $exampleEntity->setEnglish($nodeCrawler->previousAll()->first()->text());
            $exampleEntity->setRussian(trim($nodeCrawler->text(), ' \n\r\t\0☰'));
            $word->addExample($exampleEntity);
        });
    }
    private function parseAndLoadSound(Crawler $crawler, Word $word){
        $crawler = $crawler->filter('#us_tr_sound > audio > source');
        if($crawler->count() == 0 ) return;
        $soundUri = 'http://wooordhunt.ru'.$crawler->first()->attr('src');
        $soundFileName = sprintf('%s.mp3', $word->getSpelling());
        $soundFilePath = sprintf('%s/%s', $this->soundDir, $soundFileName);
        file_put_contents($soundFilePath, file_get_contents($soundUri));
        $word->setSoundFilename($soundFileName);
    }

    private function parseForms(Crawler $crawler, Word $word){
        $crawler = $crawler->filter('#wd_title #word_forms');
        if($crawler->count()==0) return $word;
        $links = $crawler->filter('a');

        for( $i=0 ;$i< $links->count(); $i++){

            $link = $links->eq($i);
            $originalSpelling = substr(strrchr($link->attr('href'),'/'),1);
            $formSpelling = $link->text();
            $formComment = $link->previousAll()->first()->text();


            $em = $this->doctrine->getManager();
            $formWord = $em->getRepository(Word::class)->findOneBySpelling($originalSpelling);
            if(is_null($formWord)){

                try {
                    $formWord = new Word($originalSpelling);
                    $em = $this->doctrine->getManager();
                    $em->persist($formWord);
                    // here is exception may occur if word entry would be inserted by another process
                    // in the moment before current process will have persisted current instance of new word entry
                    $em->flush($formWord);
                }catch(\Exception $ex){
                    if ($this->formsLoadingDiveLevel > 0) {
                        // if we are loading form word, we go back to "root" word and try to load forms again
                        // ( see next try-catch block )
                        throw new WordFormLoadingException();
                    }
                    // we are in the "root" word now, so
                    // reattach changes of "root" word to the reset manager
                    // and try to load form words again
                    $word = $this->mergeManagerWordEntry($word);
                    return $this->parseForms($crawler, $word);

                }
            }

            try{
                $this->formsLoadingDiveLevel++;
                $this->loadWord($formWord);
                $this->formsLoadingDiveLevel--;
            } catch(WordFormLoadingException $ex) {
                // Each time exception have been thrown in the doctrine manager, manager is closed and
                // we need to attach all changes in the entities made before to manager again, so
                // if we are loading form word, we go back to "root" word and try to load forms again.
                // By achieving root word, "formsLoadingDiveLevel" property here will be decremented to "1"
                if ($this->formsLoadingDiveLevel > 1) {
                    $this->formsLoadingDiveLevel--;
                    throw $ex;
                }
                $this->formsLoadingDiveLevel--;
                // we are in the "root" word now, so
                // reattach changes of "root" word to the reset manager
                // and try to load form words again
                $word = $this->mergeManagerWordEntry($word);
                return $this->parseForms($crawler, $word);
            } catch(AbortedException $ex){
                $this->formsLoadingDiveLevel--;
                throw $ex;
            }

            $form = new WordForm();
            $form->setComment($formComment)
                ->setFormSpelling($formSpelling)
                ->setFormWord($formWord)
                ->setWord($word);
            $word->addForm($form);
        }
        return $word;
    }

    private function mergeManagerWordEntry($word){
        $em =  $this->doctrine->getManager();
        $tmpWord = $word;
        if(!$em->contains($word)) {
            $word = $em->find(Word::class, $word->getId());
            $em->merge($tmpWord);
        }
        return $word;
    }

    private function takeRequestRateDelay(){
        if(!$this->timeCache) {
            usleep($this->sleepRequestInterval);
            return;
        }
        $cachedTime = $this->timeCache->get('wordLoader.timestamp',0);
        if(microtime()-$cachedTime < $this->sleepRequestInterval){
            $this->timeCache->set('wordLoader.timestamp', microtime());
            usleep($this->sleepRequestInterval+rand(1000,1000000));
        }
    }


    private function dispatchTryLoadEvent($word, $attempts){
        $event = new TryLoadEvent($word, $attempts);
        if($this->eventDispatcher){
            $this->eventDispatcher->dispatch(TryLoadEvent::NAME, $event);
        }
        return $event;
    }
    private function dispatchWaitingEvent($word, $waitingSeconds){
        $event = new WaitingEvent($word, $waitingSeconds);
        if($this->eventDispatcher){
            $this->eventDispatcher->dispatch(WaitingEvent::NAME, $event);
        }
        return $event;
    }

}