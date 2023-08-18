<?php

namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Nononsense\HomeBundle\Entity\ArchiveAZ;
use Nononsense\HomeBundle\Entity\ArchiveLocations;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nononsense\HomeBundle\Form\Type as FormProveedor;

use Nononsense\UtilsBundle\Classes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Nononsense\UtilsBundle\Classes\Auxiliar;
use Nononsense\UtilsBundle\Classes\Utils;

use Endroid\QrCode\QrCode;
use Com\Tecnick\Barcode\Barcode;
use Com\Tecnick\Barcode\Exception as BCodeException;
use Com\Tecnick\Color\Exception as BColorException;


class ArchiveAZController extends Controller
{
    public function editAction(Request $request, $code)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('archive_admin');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $action = 2;
        $em = $this->getDoctrine()->getManager();
        if($code!=0){
            $az = $em->getRepository('NononsenseHomeBundle:ArchiveAZ')->findOneBy(array("code" => $code));
            if(!$az && $request->getMethod()!='POST'){
                $this->get('session')->getFlashBag()->add('error', "El código AZ no se encuentra en el sistema y va a proceder a crearlo dentro de este");
            }
        }
        else{
            $code=uniqid();
        }
        if(!isset($az) || !$az){
            $az = new ArchiveAZ();
            $az->setCode($code);
            $action = 5;
        }

        if($request->getMethod()=='POST'){
            try{
                $location = $em->getRepository(ArchiveLocations::class)->findOneBy(['id' => $request->get('location')]);
                $az->setLocation($location);

                if($request->get("comment")){
                    $comment=$request->get("comment");
                }

                $em->persist($az);
                $em->flush();
                $this->get('utilities')->saveLogArchive($this->getUser(),$action,$comment,"az",$az->getId());
                $this->get('session')->getFlashBag()->add('message',"El código AZ se ha guardado correctamente. Descargeselo para empezar a utilizarlo");
                return $this->redirect($this->generateUrl('nononsense_archive_az_edit', ['code' => $code]));
            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar guardar los datos del AZ");
                return $this->redirect($this->generateUrl('nononsense_home_homepage'));
            }
        }
        
        $array_item = array();
        $array_item['az'] = $az;
        $array_item['code'] = $code;
        $array_item['time'] = time();

        return $this->render('NononsenseHomeBundle:Archive:az.html.twig',$array_item);
    }

    public function viewBarcodeAction(Request $request, $code)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('archive_admin');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        if ($code) {
            $content = $this->getBarcodeImg($code);
            if ($content) {
                $response = new Response();
                $disposition = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_INLINE,
                    $code . '.png'
                );
                $response->headers->set('Content-Disposition', $disposition);
                $response->headers->set('Content-Type', 'image/png');
                $response->setContent($content);
                return $response;
            }
        }

        $this->get('session')->getFlashBag()->add(
            'error',
            "Se ha producido un error al intentar obtener el código de barras"
        );
        return $this->redirect($this->generateUrl('nononsense_mclean_codes_list'));
    }

    public function checkAZAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$request->get("az")){
            $response = new Response(json_encode(array("check" => false)), 400);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $az = $em->getRepository(ArchiveAZ::class)->findOneBy(['code' => $request->get("az")]);

        if(!$az){
            $response = new Response(json_encode(array("check" => false)), 400);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $response = new Response(json_encode(array("check" => TRUE,"az" => $az->getId())), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @param $mcCode
     * @return string
     */
    private function getBarcodeImg($code)
    {
        $result = '';
        $code = $code;
        $barcode = new Barcode();
        try {
            $bobj = $barcode->getBarcodeObj(
                'C128,C',
                $code,
                500,         // bar width (use absolute or negative value as multiplication factor)
                100,        // bar height (use absolute or negative value as multiplication factor)
                'black',     // foreground color
                [20, 20, 20, 20]  // padding (use absolute or negative values as multiplication factors)
            )->setBackgroundColor('white');
            $result = $bobj->getPngData();
        } catch (BCodeException | BColorException $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Se ha producido un error al intentar obtener el código de barras "
            );
        }
        $barcodeImg = self::addBarcodeInfoData($result, 540, 140, $code);

        return $barcodeImg;
    }
    /**
     * @param $qrPath
     * @param $qrWidth
     * @param $text
     */
    private function addBarcodeInfoData($barCodeBase, $barCodeWidth, $barCodeHeight, $text)
    {
        $spacedText = implode(' ',str_split($text));
        $squareHeight = 40;
        $rectangle = imagecreatetruecolor($barCodeWidth, $squareHeight);
        $white = imagecolorallocate($rectangle, 255, 255, 255);
        imagefilledrectangle($rectangle, 0, 0, $barCodeWidth, $squareHeight, $white);

        $barCodeImage = imagecreatefromstring($barCodeBase);
        $black = imagecolorallocate($rectangle, 0, 0, 0);
        $rootdir = $this->get('kernel')->getRootDir();
        $font_path = $rootdir . '/Resources/font/opensans.ttf';

        imagettftext($rectangle, 22, 0, 120, 22, $black, $font_path, $spacedText);

        ob_start();
        $new = imagecreate($barCodeWidth, $barCodeHeight+$squareHeight);
        imagecopy($new, $barCodeImage, 0, 0, 0, 0, $barCodeWidth, $barCodeHeight);
        imagecopy($new, $rectangle, 0, $barCodeHeight-4, 0, 0, $barCodeWidth, $squareHeight);
        imagepng($new);
        $content = ob_get_clean();

        // Clear Memory
        imagedestroy($barCodeImage);
        imagedestroy($rectangle);
        imagedestroy($new);

        return $content;
    }

}