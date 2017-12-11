<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 09.12.2017
 * Time: 22:12
 */

namespace AppBundle\Service;


use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;
use AppBundle\Entity\Dictionary;

class VichUploaderNamer implements NamerInterface {

    /**
     * Creates a name for the file being uploaded.
     *
     * @param object $object The object the upload is attached to.
     * @param PropertyMapping $mapping The mapping to use to manipulate the given object.
     *
     * @return string The file name.
     */
    public function name($object, PropertyMapping $mapping)
    {
        switch(get_class($object)){
            case Dictionary::class:
                $dictionaryName = $object->getName();
                $dictionaryName = preg_replace('/\s/','_',$dictionaryName);
                $dictionaryName = Utils::filesystemPath($dictionaryName);
                $dictionaryName .= '.'.$object->getSourceFile()->getClientOriginalExtension();
                return $dictionaryName;
                break;
        }
    }
}