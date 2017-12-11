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
use AppBundle\Entity\WordRepository;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WordHuntRuLoader implements WordLoaderInterface{

    private $wordEnumSource;
    private $pronounceAudioBaseDir;
    private $sleepRequestInterval;
    private $eventDispatcher;
    private $wordRepository;
    public function __construct(WordEnumSourceInterface $wordEnumSource, WordRepository $wordRepository, $pronounceAudioBaseDir, $sleepRequestInterval = 2){
        $this->wordEnumSource = $wordEnumSource;
        $this->pronounceAudioBaseDir = $pronounceAudioBaseDir;
        $this->sleepRequestInterval = $sleepRequestInterval;
        $this->wordRepository = $wordRepository;

    }

    /**
     * @return Word[]
     */
    public function loadWords()
    {
        $words = $this->wordEnumSource->getWordEnum();

        $wordEntities = [];
        $start = 0;
        $count = 3;

        for($i=$start;$i<$start+$count; $i++){
            $word = $words[$i];
            $wordEntity = $this->wordRepository->findBySpelling($word);
            if(!is_null($wordEntity)){
                $wordEntities[] = $wordEntity;
                continue;
            }
            $wordEntities[] = $this->loadWord($word, $this->pronounceAudioBaseDir);
            sleep($this->sleepRequestInterval);
            $event = $this->dispatchLoadingEvent($i-$start+1, $count);
            if($event && $event->isCanceled()){
                return false;
            }
        }
        return $wordEntities;
    }
    private function loadWord($word, $soundDir){
        $client = new \Goutte\Client();
        $crawler = $client->request('GET', 'http://wooordhunt.ru/word/'.$word);
        $wordEntity = new Word($word);
        if($client->getResponse()->getStatus()!=200){
            return $wordEntity;
        }
        $transcription = $crawler->filter('#us_tr_sound > .transcription')->text();
        $transcription = sprintf('%s', trim($transcription,'| ') );
        $wordEntity->setTranscription($transcription);
        $translations = $crawler->filter('#wd_content > .t_inline_en')->text();
        $translations = mb_split(', ', $translations);
        foreach($translations as $translation){
            $translationEntity = new Translation();
            $translationEntity->setMeaning($translation);
            $wordEntity->addTranslation($translationEntity);
        }
        $soundUri = 'http://wooordhunt.ru'.$crawler->filter('#us_tr_sound > audio > source')->first()->attr('src');
        file_put_contents(sprintf('%s/%s.mp3',$soundDir,$word), file_get_contents($soundUri));

        $exampleNodes = $crawler->filter('#wd_content > .block > .ex_t');
        $exampleNodes->each(function(\Symfony\Component\DomCrawler\Crawler $nodeCrawler) use ($wordEntity){
            $exampleEntity = new Example();
            $exampleEntity->setEnglish($nodeCrawler->previousAll()->first()->text());
            $exampleEntity->setRussian($nodeCrawler->text());
            $wordEntity->addExample($exampleEntity);
        });
        return $wordEntity;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher){
        $this->eventDispatcher = $eventDispatcher;
    }
    public function getEventDispatcher(){
        return $this->eventDispatcher;
    }
    private function dispatchLoadingEvent($loadedWords, $totalWords){
        if(is_null($this->eventDispatcher)) return null;
        $event = new LoadingEvent($loadedWords, $totalWords);
        return $this->eventDispatcher->dispatch(LoadingEvent::NAME, $event);
    }
}