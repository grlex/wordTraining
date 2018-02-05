<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 08.12.2017
 * Time: 17:27
 */
namespace Custom\EasyAdmin\Controller;

use AppBundle\Entity\Settings;
use Custom\EasyAdmin\Form\SettingsBackgroundImageType;
use Custom\EasyAdmin\Form\SettingsType;
use AppBundle\Entity\Word;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Form\Util\LegacyFormHelper;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SettingsAdminController extends Controller {


    /**
     * @Route("/settings", name="admin_settings")
     */
    public function adminSettingsAction(Request $request){

        $repository = $this->getDoctrine()->getRepository(Settings::class);
        $em  = $this->getDoctrine()->getManager();

        $underMaintenanceSetting  = $repository->findOneBySetting(Settings::SETTING_UNDER_MAINTENANCE) ?: new Settings(Settings::SETTING_UNDER_MAINTENANCE);
        $showBackgroundImagesSetting  = $repository->findOneBySetting(Settings::SETTING_SHOW_BACKGROUND_IMAGES) ?: new Settings(Settings::SETTING_SHOW_BACKGROUND_IMAGES);
        $backgroundImagesSetting  = $repository->findBySetting(Settings::SETTING_BACKGROUND_IMAGES) ?: array();

        $settingsData = array(
            'id' => null,
            'underMaintenance' => (bool)$underMaintenanceSetting->getValue(),
            'showBackgroundImages' => (bool)$showBackgroundImagesSetting->getValue(),
            'backgroundImages' => $backgroundImagesSetting,
        );


        $editForm = $this->createForm(SettingsType::class, $settingsData);

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $settingsData = $editForm->getData();

            $underMaintenanceSetting->setValue($settingsData['underMaintenance']);
            if(is_null($underMaintenanceSetting->getId())) $em->persist($underMaintenanceSetting);

            $showBackgroundImagesSetting->setValue($settingsData['showBackgroundImages']);
            if(is_null($showBackgroundImagesSetting->getId())) $em->persist($showBackgroundImagesSetting);


            $this->saveBackgroundImages($settingsData['backgroundImages']);

            $em->flush();

            return $this->redirectToRoute('admin_settings');
        }


        return $this->render('@CustomEasyAdminBundle/Resources/views/admin/settings.html.twig', array(
            'form' => $editForm->createView(),
        ));

    }

    public function saveBackgroundImages($backgroundImages){
        $repository = $this->getDoctrine()->getRepository(Settings::class);
        $em  = $this->getDoctrine()->getManager();

        $backgroundsPath = $this->getParameter('kernel.project_dir').'/web/images/backgrounds';

        foreach($backgroundImages as $image){
            if(is_null($image['id'])){
                $removedIndexes = $image['remove'] ?: [];
                foreach($image['file'] as $index => $file){
                    if(in_array($index, $removedIndexes)) continue;
                    $filename = $this->saveImageFile($file, $backgroundsPath);
                    $setting = new Settings(Settings::SETTING_BACKGROUND_IMAGES);
                    $inactive = array_key_exists($index, ($image['inactive']) ?: []) ? '1' : '0';
                    $repeated = array_key_exists($index, ($image['repeat']) ?: []) ? '1' : '0';
                    $value = implode('|', [$inactive, $repeated, $filename]);
                    $setting->setValue($value);
                    $em->persist($setting);
                }
            }
            else{
                if($image['remove']['single']== "single"){
                    $setting = $repository->find($image['id']);
                    list($inactive, $repeated, $filename) = explode('|', $setting->getValue());
                    foreach(['orig', 'small'] as $prefix){
                        $filepath = sprintf('%s/%s.%s',$backgroundsPath, $prefix, $filename);
                        if(file_exists($filepath)) unlink($filepath);
                    }
                    $em->remove($setting);

                }
                else {
                    $setting = $repository->find($image['id']);
                    list($inactive, $repeated, $filename) = explode('|', $setting->getValue());
                    $inactive = $image['inactive']['single'] == 'single' ? '1' : '0';
                    $repeated = $image['repeat']['single'] == 'single' ? '1' : '0';
                    $setting->setValue(implode('|',[$inactive, $repeated, $filename]));
                }
            }
        }
    }

    public function saveImageFile(UploadedFile $file, $path){

        $sideLengthLimitOrig = false;
        $sideLengthLimitSmall = 150;

        $imageFunction = 'image';
        $srcResource = null;
        $guessedExtension = $file->guessExtension();
        switch($guessedExtension){
            case 'png':
                $srcResource = imageCreateFromPng($file->getPathname());
                $imageFunction .= 'png';
                break;
            case 'jpg':
            case 'jpeg':
                $srcResource = imagecreatefromjpeg($file->getPathname());
                $imageFunction .= 'jpeg';
                break;
            case 'bmp':
                $srcResource = imagecreatefromwbmp($file->getPathname());
                $imageFunction .= 'wbmp';
                break;
            case 'gif':
                $srcResource = imagecreatefromgif($file->getPathname());
                $imageFunction .= 'gif';
                break;
            default:
                throw new \Exception('file extension is not supported');

        }



        $srcSize = getimagesize($file->getPathname());
        $longestSideLength = max(width($srcSize), height($srcSize));

        $dstSizeOrig = $longestSideLength <= $sideLengthLimitOrig || $sideLengthLimitOrig === false
            ? $srcSize
            : [
                $sideLengthLimitOrig * width($srcSize)/$longestSideLength,
                $sideLengthLimitOrig * height($srcSize)/$longestSideLength
            ];
        $dstSizeSmall = $longestSideLength <= $sideLengthLimitSmall
            ? $srcSize
            : [
                $sideLengthLimitSmall * width($srcSize)/$longestSideLength,
                $sideLengthLimitSmall * height($srcSize)/$longestSideLength
            ];

        $dstResourceOrig = imagecreatetruecolor(width($dstSizeOrig), height($dstSizeOrig));
        $dstResourceSmall = imagecreatetruecolor(width($dstSizeSmall), height($dstSizeSmall));


        imagecopyresized($dstResourceOrig, $srcResource,0,0,0,0,width($dstSizeOrig),height($dstSizeOrig),width($srcSize),height($srcSize));
        imagecopyresized($dstResourceSmall, $srcResource,0,0,0,0,width($dstSizeSmall),height($dstSizeSmall),width($srcSize),height($srcSize));

        $basename = sprintf('%s.%s', md5(uniqid("",true)), $guessedExtension);

        $imageFunction($dstResourceSmall, sprintf('%s/%s.%s', $path, 'small', $basename));
        $imageFunction($dstResourceOrig, sprintf('%s/%s.%s', $path, 'orig', $basename));
        return $basename;
    }
}

function width($size){
    return $size[0];
}
function height($size){
    return $size[1];
}