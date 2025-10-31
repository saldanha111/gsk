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
use TCPDF;

class ArchiveAZController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('archive_agent');
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
        $is_valid = $this->get('app.security')->permissionSeccion('archive_agent');
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
                        $changes="";
                        $oldlocation=$az->getLocation();
                        if($action!=5 && $location!=$oldlocation){
                            $old=$oldlocation->getBuilding()." - ".$oldlocation->getPassage()." - ".$oldlocation->getCabinet()." - ".$oldlocation->getShelf()." - ".$oldlocation->getOthers();
                            $new=$location->getBuilding()." - ".$location->getPassage()." - ".$location->getCabinet()." - ".$location->getShelf()." - ".$location->getOthers();
                            $changes="\n<table class='table'><tr><td>Campo</td><td>Anterior</td><td>Nuevo</td></tr><tr><td>Localización</td><td>".$old."</td><td>".$new."</td></tr></table>";
                        }
                        $az->setLocation($location);
                        $em->persist($az);
                        $saves[]=array("code" => $code,"action" => $action, "changes" => $changes);
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
                    $this->get('utilities')->saveLogArchive($this->getUser(),$save["action"],$comment,"az",$az->getId(),NULL,NULL,$save["changes"]);
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

        if ($request->get("f_prints")) {
            foreach ($request->get("f_prints") as $print) {
                $filters["prints"][] = $print;
            }
        }

        $archiveCategoriesRepository = $em->getRepository(ArchiveAZ::class);
        $items = $archiveCategoriesRepository->list($filters, false);

        if (empty($items)) {
            $this->get('session')->getFlashBag()->add('error', "No hay códigos disponibles para imprimir.");
            return $this->redirect($this->generateUrl('nononsense_archive_az_list'));
        }

        $codes = [];
        foreach ($items as $item) {
            $codes[] = $item->getCode();
        }

        // --- Rejilla: 3 columnas × 10 filas (30 por página)
        $colsPerRow  = 3;
        $rowsPerPage = 10;
        $perPage     = $colsPerRow * $rowsPerPage;

        // --- Estilo del código de barras (vector, sin imagen)
        //     IMPORTANTE: sin 'stretch' ni 'fitwidth' para controlar el ancho exacto
        $barcodeStyle = [
            'position'      => '',
            'align'         => 'C',
            'stretch'       => false,
            'fitwidth'      => false,
            'cellfitalign'  => '',
            'border'        => false,
            'hpadding'      => 0,
            'vpadding'      => 0,
            'fgcolor'       => [0, 0, 0],
            'bgcolor'       => false,
            'text'          => false,
            'font'          => 'helvetica',
            'fontsize'      => 11,
            'stretchtext'   => 0,
        ];

        // --- Crear PDF en A4 vertical (P)
        $pdf = new \Nononsense\HomeBundle\Utils\GskPdf('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, false, []);
        $pdf->setName("Códigos AZ - " . $this->getUser()->getUsername());
        $pdf->SetAuthor('GSK');
        $pdf->SetTitle('Códigos AZ');
        $pdf->SetSubject('Códigos AZ');
        $pdf->setFontSubsetting(true);
        $pdf->SetHeaderData(null, null, date("d/m/Y H:i:s"), null, [0,0,0], [0,0,0]);
        $pdf->SetPrintHeader(true);
        $pdf->SetPrintFooter(true);

        // Márgenes y tipografía base
        $left = 12; $top = 18; $right = 12; $bottom = 18;
        $pdf->SetMargins($left, $top, $right);
        $pdf->SetAutoPageBreak(true, $bottom);
        $pdf->SetFont('helvetica', '', 11);

        // Cálculo de celda
        $usableW = $pdf->getPageWidth()  - $left - $right;
        $usableH = $pdf->getPageHeight() - $top  - $bottom;
        $cellW   = $usableW / $colsPerRow;
        $cellH   = $usableH / $rowsPerPage;

        // Padding y proporciones internas
        $innerPadX = 4; // mm
        $innerPadY = 3; // mm

        $innerW   = $cellW - (2 * $innerPadX);
        $innerH   = $cellH - (2 * $innerPadY);

        // Alturas pensadas para que no se corte el texto
        $barcodeH = max(14.0, $innerH * 0.68);
        $gapH     = max(1.8,  $innerH * 0.08);
        $labelH   = max(6.0,  $innerH * 0.24);

        // Módulo base (grosor de barra). Ajustaremos si no cabe.
        $moduleBase = 0.55; // 0.50–0.60 según impresora/lector

        $chunks = array_chunk($codes, $perPage);
        foreach ($chunks as $pageCodes) {
            $pdf->AddPage('P', 'A4');

            // --- GRID visible (bordes de celdas)
            $pdf->SetDrawColor(80, 80, 80);
            $pdf->SetLineWidth(0.2);
            for ($r = 0; $r < $rowsPerPage; $r++) {
                for ($c = 0; $c < $colsPerRow; $c++) {
                    $x = $left + ($c * $cellW);
                    $y = $top  + ($r * $cellH);
                    $pdf->Rect($x, $y, $cellW, $cellH);
                }
            }

            // --- Pintar códigos centrados (H y V) con cálculo de ancho REAL por código
            foreach ($pageCodes as $i => $code) {
                $row = intdiv($i, $colsPerRow);
                $col = $i % $colsPerRow;

                $cellX = $left + ($col * $cellW);
                $cellY = $top  + ($row * $cellH);

                // Área interna
                $innerX = $cellX + $innerPadX;
                $innerY = $cellY + $innerPadY;

                // Altura total del bloque (barras + gap + texto)
                $blockH = $barcodeH + $gapH + $labelH;

                // Centrado vertical
                $yStart = $innerY + max(0, ($innerH - $blockH) / 2);

                // ---- Cálculo de ANCHO EXACTO según nº de módulos del C128
                // Usamos TCPDFBarcode para obtener 'maxw' (número de módulos).
                $modules = null;
                try {
                    $bc = new \TCPDFBarcode($code, 'C128B');
                    $bca = $bc->getBarcodeArray();
                    // 'maxw' = número de columnas (módulos) que ocupa el código (incluye zonas de calma)
                    if (is_array($bca) && isset($bca['maxw'])) {
                        $modules = (int) $bca['maxw'];
                    }
                } catch (\Exception $e) {
                    $modules = null;
                }

                // Fallback si no pudimos calcular módulos
                if (!$modules || $modules <= 0) {
                    $modules = 220; // aproximación conservadora para C128 medio
                }

                // Calcula el módulo para que quepa en el ancho interno, pero intenta mantener el grosor base
                $moduleW  = $moduleBase;
                $barcodeW = $modules * $moduleW;

                if ($barcodeW > $innerW) {
                    // Reducimos módulo para encajar exactamente en el ancho disponible
                    $moduleW  = $innerW / $modules;
                    $barcodeW = $innerW;
                }

                // Centrado horizontal EXACTO
                $xBarcode = $innerX + (($innerW - $barcodeW) / 2);

                // Dibuja el código de barras (vector) - Code 128
                $pdf->write1DBarcode(
                    $code,
                    'C128B',
                    $xBarcode,
                    $yStart,
                    $barcodeW,
                    $barcodeH,
                    $moduleW,
                    $barcodeStyle,
                    ''
                );

                // Etiqueta legible (centrada)
                $pdf->SetFont('helvetica', '', 11);
                $pdf->SetXY($cellX, $yStart + $barcodeH + $gapH);
                $pdf->Cell($cellW, $labelH, $code, 0, 0, 'C', false, '', 0, false, 'T', 'M');
            }
        }

        // Salida directa al navegador
        $pdf->Output("codigos-az.pdf", 'I');
        return new Response(); // TCPDF ya envía la salida
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
                'C128,B',
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