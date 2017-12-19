<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 12.12.2017
 * Time: 19:33
 */

namespace Custom\EasyAdmin\Form;


use AppBundle\Entity\Word;
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
        $builder->add('spelling', Type\TextType::class, array(
            'label'=>'spell'
        ));

        $doctrine = $this->doctrine;
        $builder->addModelTransformer(new CallbackTransformer(
            function (Word $word=null){
                if(is_null($word)) $word = new Word();
                return array(
                    'spelling'=>$word->getSpelling()
                );
            },
            function (array $normData) use ($doctrine){
                $spelling = $normData['spelling'];
                if(!$spelling) return null;
                $word = $doctrine->getRepository('AppBundle:Word')->findOneBySpelling($spelling);
                if(is_null($word)) {
                    $word = new Word($spelling);
                }
                return $word;
            }
        ));
    }

}