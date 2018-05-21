<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 06.05.2018
 * Time: 21:32
 */

namespace Custom\EasyAdmin\Form\Type\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\Configurator\TypeConfiguratorInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Form\FormConfigInterface;


class DisableByRefConfigurator implements TypeConfiguratorInterface  {

    /**
     * {@inheritdoc}
     */
    public function configure($name, array $options, array $metadata, FormConfigInterface $parentConfig)
    {

        if ($metadata['associationType'] & ClassMetadata::TO_MANY) {
            $options['by_reference'] = false;
        }


        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type, array $options, array $metadata)
    {
        $isEntityType = in_array($type, array('entity', 'Symfony\Bridge\Doctrine\Form\Type\EntityType'), true);
        $isCollectionType = in_array($type, array('entity', 'Symfony\Bridge\Doctrine\Form\Type\CollectionType'), true);

        return ($isEntityType or $isCollectionType) && 'association' === $metadata['dataType'];
    }
} 