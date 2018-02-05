<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 24.12.2017
 * Time: 20:42
 */

namespace Custom\EasyAdmin\Form;


use AppBundle\Entity\Settings;
use AppBundle\Entity\WordAttribute;
use AppBundle\Entity\WordTranslation;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type;

class SettingsBackgroundImageType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add('id', Type\HiddenType::class);
        $builder->add('filename', Type\HiddenType::class);
        $builder->add('remove', Type\HiddenType::class);
        $builder->add('repeat', Type\HiddenType::class);
        $builder->add('inactive', Type\HiddenType::class);
        $builder->add('file', Type\FileType::class, array( 'multiple' => true));
        $builder->addModelTransformer(new CallbackTransformer(
            function ($setting = null){
                if($setting == null) $setting = new Settings(Settings::SETTING_BACKGROUND_IMAGES);
                list($inactive, $repeated, $filename) = explode('|', $setting->getValue() ?: '0|0|none');
                return array(
                    'id' => $setting->getId(),
                    'filename' => $filename,
                    'remove' => null,
                    'repeat' => $repeated,
                    'inactive' => $inactive,
                    'file' => null
                );
            },
            function (array $data){ return $data; })
        );
    }
}