<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 24.12.2017
 * Time: 20:42
 */

namespace Custom\EasyAdmin\Form;


use AppBundle\Entity\WordAttribute;
use AppBundle\Entity\WordPronounce;
use AppBundle\Entity\WordPronounceAudioData;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type;
use Vich\UploaderBundle\Form\Type\VichFileType;

class WordPronounceType extends AbstractType {
    const PRONOUNCE_TYPE_UNAVAILABLE = 1;
    const PRONOUNCE_TYPE_NO = 2;
    const PRONOUNCE_TYPE_INITIAL = 3;
    const PRONOUNCE_TYPE_FILE = 4;
    const PRONOUNCE_TYPE_LINK = 5;
    const PRONOUNCE_TYPE_MICROPHONE = 6;
    const PRONOUNCE_TYPE_AUTO = 7;
    private $doctrine;
    private static $cachedEntitiesByUrl = [];
    public function __construct(Registry $doctrine){
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', Type\HiddenType::class);
        $builder->add('audioFile', Type\FileType::class);
        $builder->add('audioMic', Type\HiddenType::class);
        $builder->add('audioURL', Type\HiddenType::class);
        $builder->add('type', Type\HiddenType::class);

        $self = $this;
        $builder->addModelTransformer(new CallbackTransformer(
            function (WordPronounce $pronounce = null) use ($self){ return $self->transform($pronounce); },
            function (array $normData) use ($self){ return $self->reverseTransform($normData); }
        ));
    }
    private function transform (WordPronounce $pronounce=null){
        $sourceTypes = array(
            'word_pronounce.type.unavailable' => self::PRONOUNCE_TYPE_UNAVAILABLE,
            'word_pronounce.type.initial' => self::PRONOUNCE_TYPE_INITIAL,
            'word_pronounce.type.initial_loading' => self::PRONOUNCE_TYPE_INITIAL,
            'word_pronounce.type.no' => self::PRONOUNCE_TYPE_NO,
            'word_pronounce.type.file' => self::PRONOUNCE_TYPE_FILE,
            'word_pronounce.type.link' => self::PRONOUNCE_TYPE_LINK,
            'word_pronounce.type.microphone' => self::PRONOUNCE_TYPE_MICROPHONE,
            'word_pronounce.type.auto' => self::PRONOUNCE_TYPE_AUTO,
        );

        if(is_null($pronounce)) {
            $pronounce = new WordPronounce();
            unset($sourceTypes['word_pronounce.type.initial']);
            unset($sourceTypes['word_pronounce.type.initial_loading']);
            unset($sourceTypes['word_pronounce.type.unavailable']);
        }
        else if($pronounce->getStatus() == WordAttribute::STATUS_UNAVAILABLE){
            unset($sourceTypes['word_pronounce.type.initial']);
            unset($sourceTypes['word_pronounce.type.initial_loading']);
        }
        else{
            unset($sourceTypes['word_pronounce.type.unavailable']);
            if($pronounce->getStatus() == WordAttribute::STATUS_DONE){
                unset($sourceTypes['word_pronounce.type.initial_loading']);
            }
            else{
                unset($sourceTypes['word_pronounce.type.initial']);
            }
        }


        return array(
            'id' => $pronounce->getId(),
            'audioFilename' => $pronounce->getAudioFilename(),
            'types' => $sourceTypes,
            'status' => $pronounce->getStatus()
        );
    }
    private function reverseTransform (array $normData){
                $id = $normData['id'];
                $audioFile = $normData['audioFile'];
                $audioMic = $normData['audioMic'];
                $audioURL = $normData['audioURL'];
                $type = $normData['type'];

                $entity = null;
                switch($type){
                    case self::PRONOUNCE_TYPE_NO:
                        break;
                    case self::PRONOUNCE_TYPE_INITIAL:
                    case self::PRONOUNCE_TYPE_UNAVAILABLE:
                        if(!is_null($id)){
                            $entity = $this->doctrine->getRepository('AppBundle:WordPronounce')->find($id);
                        }
                    break;
                    case self::PRONOUNCE_TYPE_FILE:
                        $entity = new WordPronounce();
                        $entity->setAudioFile($audioFile);
                        $entity->setStatus(WordPronounce::STATUS_DONE);
                        break;
                    case self::PRONOUNCE_TYPE_MICROPHONE:
                        if($audioMic) {
                            $audioData = new WordPronounceAudioData($audioMic);
                            $entity = new WordPronounce();
                            $entity->setAudioData($audioData);
                            $entity->setStatus(WordPronounce::STATUS_MIC);
                        }
                        break;
                    case self::PRONOUNCE_TYPE_LINK:
                        if(array_key_exists($audioURL, self::$cachedEntitiesByUrl)){
                            //same http request with same audio URL
                            $entity = self::$cachedEntitiesByUrl[$audioURL];
                        }
                        else {
                            $url = parse_url($audioURL);
                            if ($url && $url['scheme'] && $url['host']) {
                                $entity = $this->doctrine->getRepository(WordPronounce::class)->findByAudioData($audioURL);
                                if (!$entity) {
                                    $audioData = new WordPronounceAudioData($audioURL);
                                    $entity = new WordPronounce();
                                    $entity->setAudioData($audioData);
                                    $entity->setStatus(WordPronounce::STATUS_LINK);
                                    self::$cachedEntitiesByUrl[$audioURL] = $entity;
                                }
                            }
                        }
                        break;
                    case self::PRONOUNCE_TYPE_AUTO:
                        $entity = new WordPronounce();
                        $entity->setStatus(WordAttribute::STATUS_AUTO);
                        break;
                }
                return $entity;
            }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                //'data_class' => WordPronounce::class
                'translation_domain' => 'CustomEasyAdminBundle'
            )
        );
    }
}
