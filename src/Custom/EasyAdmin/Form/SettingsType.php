<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 24.12.2017
 * Time: 20:42
 */

namespace Custom\EasyAdmin\Form;


use AppBundle\Entity\Settings;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type;

class SettingsType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('underMaintenance', Type\CheckboxType::class, array(
            'required'=> false,
            'label' => 'settings.under_maintenance'
        ));
        $builder->add('showBackgroundImages', Type\Checkboxtype::class, array(
            'required'=> false,
            'label' => 'settings.show_background_images'
        ));
        $builder->add('backgroundImages', Type\CollectionType::class, array(
            'label' => 'settings.background_images',
            'required'=> false,
            'entry_type' => SettingsBackgroundImageType::class,
            'allow_add' => true,
            'allow_delete' => true

        ));
    }
}