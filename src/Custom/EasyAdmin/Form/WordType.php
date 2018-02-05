<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 12.12.2017
 * Time: 19:33
 */

namespace Custom\EasyAdmin\Form;


use AppBundle\Entity\Word;
use AppBundle\Entity\WordPronounce;
use AppBundle\Entity\WordTranscription;
use AppBundle\Entity\WordTranslation;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WordType extends AbstractType {

    private $doctrine;
    public function __construct(Registry $doctrine){
        $this->doctrine = $doctrine;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', Type\HiddenType::class);
        $builder->add('spelling', WordSpellingType::class, array(
            'required' => true
        ));
        $builder->add('translation', WordTranslationType::class, array());
        $builder->add('transcription', WordTranscriptionType::class, array());
        $builder->add('pronounce', WordPronounceType::class, array());

        $doctrine = $this->doctrine;
        $builder->addModelTransformer(new CallbackTransformer(
            function (Word $word=null)  {
                if(is_null($word)) $word = new Word();
                return array(
                    'id' => $word->getId(),
                    'spelling'=>$word->getSpelling(),
                    'translation' => $word->getTranslation(),
                    'transcription' => $word->getTranscription(),
                    'pronounce' => $word->getPronounce()
                );
            },
            function (array $normData) use ($doctrine){

                $id = $normData['id'];
                $spelling = $normData['spelling'];
                $translation = $normData['translation'];
                $transcription = $normData['transcription'];
                $pronounce = $normData['pronounce'];


                $word = null;
                if($id){
                    $word = $doctrine->getRepository('AppBundle:Word')->find($id);
                }
                if(!$word) $word = new Word();
                $word->setSpelling($spelling);
                $word->setTranslation($translation);
                $word->setTranscription($transcription);
                $word->setPronounce($pronounce);

                return $word;
            }
        ));
    }

}