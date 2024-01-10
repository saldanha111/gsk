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

use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchiveAZController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('archive_admin');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $defaultLimit = 44;
        $filters = Utils::getListFilters($request);
        $filters['limit_many'] = ($request->get('limit_many')) ?: $defaultLimit;

        $archiveCategoriesRepository = $em->getRepository(ArchiveAZ::class);
        $items = $archiveCategoriesRepository->list($filters);
        $totalItems = $archiveCategoriesRepository->count($filters);

        $data = [
            'filters' => $filters,
            'items' => $items,
            'count' => $totalItems,
            'pagination' => Utils::getPaginator($request, $filters['limit_many'], $totalItems)
        ];

        if($request->get("noprint")){
            $data['noprint']=1;
        }

        return $this->render('NononsenseHomeBundle:Archive:list_az.html.twig', $data);
    }

    public function editAction(Request $request, $code)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('archive_admin');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $relocate=null;
        if($request->get("relocate")){
            $relocate=1;
        }

        $az=NULL;
        $action = 2;
        $em = $this->getDoctrine()->getManager();
        $codes=array();
        if($code!=0){
            $codes[]=$code;
            $az = $em->getRepository('NononsenseHomeBundle:ArchiveAZ')->findOneBy(array("code" => $code));
            if(!$az && $request->getMethod()!='POST'){
                $this->get('session')->getFlashBag()->add('error', "El código AZ no se encuentra en el sistema y va a proceder a crearlo dentro de este");
            }
        }
        else{
            if(!$request->get("relocate")){
                if(!$request->get("number") || $request->get("number")<=0){
                    $codes[]=uniqid();
                }
                else{
                    for($count=0;$count<$request->get("number");$count++){
                        $codes[]=uniqid();
                    }
                }
            }
        }

        if($request->getMethod()=='POST'){
            try{
                if($request->get("comment")){
                    $comment=$request->get("comment");
                }

                $location = $em->getRepository(ArchiveLocations::class)->findOneBy(['id' => $request->get('location')]);

                if($request->get("codes")){
                    foreach($request->get("codes") as $code){
                        $az = $em->getRepository('NononsenseHomeBundle:ArchiveAZ')->findOneBy(array("code" => $code));
                        if(!$az){
                            $az = new ArchiveAZ();
                            $az->setCode($code);
                            $action = 5;
                        }
                        else{
                            $action = 2;
                        }
                        $az->setLocation($location);
                        $em->persist($az);
                        $saves[]=array("code" => $code,"action" => $action);
                    }
                }
                else{
                    $az->setLocation($location);
                    $em->persist($az);
                    $saves[]=array("code" => $az->getCode(),"action" => 2);
                    
                }

                $em->flush();

                foreach($saves as $save){
                    $az = $em->getRepository('NononsenseHomeBundle:ArchiveAZ')->findOneBy(array("code" => $save["code"]));
                    $this->get('utilities')->saveLogArchive($this->getUser(),$save["action"],$comment,"az",$az->getId());
                }

                $noprint="";
                if($saves>1){
                    if(!$request->get("isRelocate")){
                        $sentence="Los códigos AZ se han guardado correctamente. Ahora puede imprimirlos si lo desea";
                    }
                    else{
                        $sentence="Los códigos AZ se han reubicado correctamente.";
                        $noprint="&noprint=1";
                    }
                }
                else{
                    if(!$request->get("isRelocate")){
                        $sentence="El código AZ se ha guardado correctamente. Ahora puede imprimirlo si lo desea";
                    }
                    else{
                        $sentence="El código AZ se ha reubicado correctamente.";
                        $noprint="&noprint=1";
                    }
                }

                $list_codes="";
                foreach ($saves as $save) {
                    $list_codes.=$save["code"].",";
                }

                $this->get('session')->getFlashBag()->add('message',$sentence);
                return $this->redirect($this->generateUrl('nononsense_archive_az_list')."?f_codes=".$list_codes.$noprint);
            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar guardar los datos del AZ".$e->getMessage());
                return $this->redirect($this->generateUrl('nononsense_home_homepage'));
            }
        }
        
        $array_item = array();
        $array_item['az'] = $az;
        $array_item['codes'] = $codes;
        $array_item['time'] = time();
        $array_item['relocate']=$relocate;

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

    public function printAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $filters = Utils::getListFilters($request);
        if($request->get("f_prints")){
            foreach($request->get("f_prints") as $print){
                $filters["prints"][]=$print;
            }
        }
        $archiveCategoriesRepository = $em->getRepository(ArchiveAZ::class);
        $items = $archiveCategoriesRepository->list($filters,FALSE);
        foreach($items as $item){
            $codes[]=$item->getCode();
        }


        // Cargar la plantilla de Word
        $templateProcessor = new TemplateProcessor($this->container->get('kernel')->getRootDir().'/template_az.docx');

        $barcodesPerPage = 44;
        $barcodeChunks = array_chunk($codes, $barcodesPerPage);
        $totalPages = count($barcodeChunks);
        

        // Clonar las páginas necesarias
        //for ($i = 1; $i < $totalPages; $i++) {
            $templateProcessor->cloneBlock('PAGE', $totalPages, true, true);
        //}

        // Renombrar los marcadores de posición
        for ($i = 0; $i < $barcodesPerPage*$totalPages; $i++) {
            $section=floor($i/$barcodesPerPage)+1;
            $templateProcessor->setValue('barcode#'.$section, '${barcode' . ($i + 1).'}', 1);
        }

        // Reemplazar los marcadores de posición por imágenes de códigos de barras
        foreach ($codes as $index => $code) {

            $barcodeData = $this->getBarcodeImg($code);

            // Crea un nombre de archivo temporal para la imagen
            $tempImage = tempnam(sys_get_temp_dir(), 'barcode') . '.png';

            // Guarda los datos de la imagen en el archivo
            file_put_contents($tempImage, $barcodeData);

            $placeholder = 'barcode' . ($index + 1);
            $templateProcessor->setImageValue($placeholder, [
                'path' => $tempImage,
                'width' => 200,
                'height' => 50,
                'ratio' => false,
            ]);

            unlink($tempImage);
        }

        // Limpiar marcadores de posición sobrantes en la última página
        $lastChunkSize = count($barcodeChunks[$totalPages - 1]);
        if ($lastChunkSize < $barcodesPerPage) {
            for ($i = $lastChunkSize; $i < $barcodesPerPage; $i++) {
                $placeholder = 'barcode' . ($i + 1 + ($totalPages - 1) * $barcodesPerPage);
                $templateProcessor->setValue($placeholder, '');
            }
        }

        // Preparar y enviar la respuesta al navegador
        $response = new StreamedResponse(function () use ($templateProcessor) {
            // Guardar el documento en php://output que es un flujo directo al navegador
            $templateProcessor->saveAs('php://output');
        });

        // Configurar los encabezados HTTP para la descarga
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $response->headers->set('Content-Disposition', 'attachment; filename="codigos-de-barras.docx"');
        $response->headers->set('Cache-Control', 'max-age=0');

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