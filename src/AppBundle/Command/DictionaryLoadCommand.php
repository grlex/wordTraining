<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 30.11.2017
 * Time: 22:13
 */

namespace AppBundle\Command;
use AppBundle\Service\Utils;
use AppBundle\WordLoader\CsvWordEnumSource;
use AppBundle\WordLoader\LoadingEvent;
use AppBundle\WordLoader\WordHuntRuLoader;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use AppBundle\Entity\Word;

class DictionaryLoadCommand extends ContainerAwareCommand
{

    private $progressFile;
    private $cancelled;
    protected function configure()
    {
        $this->setName('app:dictionary:load')
            ->setDescription('Loads dictionary words');
        $this->addOption('csv-separator',null, InputOption::VALUE_OPTIONAL, '', ', ');
        $this->addOption('source-dir',null, InputOption::VALUE_REQUIRED);
        $this->addOption('web-audio-dir',null, InputOption::VALUE_OPTIONAL, '', 'audio');
        $this->addOption('progress-file',null, InputOption::VALUE_OPTIONAL, '', true);
        $this->addArgument('dictionary-id', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $cancelled = false;
        $dictionaryId = $input->getArgument('dictionary-id');
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $dictionary = $em->getRepository('AppBundle:Dictionary')->find($dictionaryId);

        $dictionarySourcesDir = $input->getOption('source-dir');
        $csvFile = Utils::filesystemPath($dictionarySourcesDir).'/'.$dictionary->getSourceFilename();
        //$csvFile = Utils::filesystemPath($dictionarySourcesDir).'/'.$dictionary->getSourceFilename();
        $csvSeparator = $input->getOption('csv-separator');
        $wordsAudioDir = Utils::filesystemPath($container->getParameter('kernel.project_dir').'/web/'.$input->getOption('web-audio-dir'));
        if(!file_exists($wordsAudioDir)) mkdir($wordsAudioDir, 0777, true);

        $csvWordEnumSource = new CsvWordEnumSource($csvFile, $csvSeparator);
        $wordHuntLoader = new WordHuntRuLoader($csvWordEnumSource, $em->getRepository(Word::class), $wordsAudioDir);
        $eventDispatcher = new EventDispatcher();
        $wordHuntLoader->setEventDispatcher($eventDispatcher);

        $this->progressFile = $input->getOption('progress-file');
        $this->progressFile = $this->progressFile===true
            ? sprintf('%s/%s/%d.json',$container->getParameter('kernel.project_dir'), 'web/dictionaryLoading', $dictionaryId)
            : $this->progressFile;


        $eventDispatcher->addListener(LoadingEvent::NAME, [$this, 'onLoadProgress']);

        $words = $wordHuntLoader->loadWords();
        if($this->cancelled) {
            foreach($words as $word){
                $em->persist($word);
            }
            $em->remove($dictionary);
            $output->writeln('cancelled');
        }
        else{
            foreach($words as $word){
                $dictionary->addWord($word);
            }
            $dictionary->setLoaded(true);
            $dictionary->setWordCount(count($words));
            $output->writeln('done');
        }
        $em->flush();

    }

    public function onLoadProgress(LoadingEvent $event){
        if(file_exists($this->progressFile)){

            $progress = json_decode(file_get_contents($this->progressFile),true);
            if(array_key_exists('cancel', $progress) && $progress['cancel']==true){
                $event->cancel();
                $this->cancelled = true;
            }
            else{
                $progressFileHandler = fopen($this->progressFile,'w');
                flock($progressFileHandler, LOCK_EX);
                $progress['done'] = $event->getTotalWords()==$event->getLoadedWords();
                $progress['loaded'] = $event->getLoadedWords();
                $progress['total'] = $event->getTotalWords();

                fwrite($progressFileHandler, json_encode($progress));
                flock($progressFileHandler, LOCK_UN);
                fclose($progressFileHandler);
            }
        }
    }

}