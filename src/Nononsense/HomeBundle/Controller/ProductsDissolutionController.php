<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\HomeBundle\Entity\ProductsDissolution;
use Nononsense\HomeBundle\Entity\ProductsDissolutionRepository;
use Nononsense\HomeBundle\Entity\ProductsInputs;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Nononsense\UtilsBundle\Classes\Utils;
use Endroid\QrCode\QrCode;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductsDissolutionController extends Controller
{

    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('reactivos_disolucion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();

        $filters = Utils::getListFilters($request);
        $filters["limit_many"] = 15;

        /** @var ProductsDissolutionRepository $productInputRepository */
        $productsDissolutionRepository = $em->getRepository(ProductsDissolution::class);
        $totalItems = $productsDissolutionRepository->count($filters);

        $array_item = [
            "filters" => $filters,
            "items" => $productsDissolutionRepository->list($filters,1),
            "count" => $totalItems,
            "pagination" => Utils::getPaginator($request, $filters['limit_many'], $totalItems)
        ];

        return $this->render('NononsenseHomeBundle:Products:dissolution_list.html.twig', $array_item);
    }

    public function editAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('reactivos_disolucion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }
        $em = $this->getDoctrine()->getManager();

        /** @var ProductsDissolutionRepository $productsDissolutionRepository */
        $productsDissolutionRepository = $em->getRepository(ProductsDissolution::class);
        /** @var ProductsDissolution $dissolution */
        $dissolution = $productsDissolutionRepository->find($id);

        if (!$dissolution) {
            $dissolution = new ProductsDissolution();
        }

        if ($request->getMethod() == 'POST') {
            $name = $request->get('name');
            $newLines = $request->get('line');
            if(empty($name)){
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "El nombre no puede estar vacío"
                );
                return $this->redirect($this->generateUrl('nononsense_products_dissolution_edit', ['id' => $id]));
            }
            if(empty($newLines)){
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "Tienes que seleccionar al menos un reactivo para la disolución"
                );
                return $this->redirect($this->generateUrl('nononsense_products_dissolution_edit', ['id' => $id]));
            }
            try{
                if(!$dissolution->getId()){
                    $dissolution->setName($name);
                    $dissolution->setQrCode('DSSF' . uniqid());
                }else{
                    $lines = $dissolution->getLines();
                    foreach($lines as $item){
                        $dissolution->removeLine($item);
                    }
                }

                foreach($newLines as $item){
                    $inputLine = $em->getRepository(ProductsInputs::class)->find($item);
                    $dissolution->addLine($inputLine);
                }

                $em->persist($dissolution);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'message',
                    "La disolución se ha guardado correctamente."
                );
                return $this->redirect($this->generateUrl('nononsense_products_dissolution'));
            }catch(Exception $e){
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "La disolución no se ha guardado correctamente"
                );
                return $this->redirect($this->generateUrl('nononsense_products_dissolution_edit', ['id' => $id]));
            }
        }

        $array_item = array();
        $array_item['dissolution'] = $dissolution;

        return $this->render('NononsenseHomeBundle:Products:dissolution_detail.html.twig', $array_item);
    }

    /**
     * @param InstanciasSteps $step
     * @return bool
     * @throws Exception
     */
    public function saveReactivoUseAction(InstanciasSteps $step)
    {
        $em = $this->getDoctrine()->getManager();
        $data = json_decode($step->getStepDataValue(), true);
        $products = [];
        if(count($data['data'])){
            foreach($data['data'] as $key => $item){
                if(is_array($item)){
                    foreach($item as $idx => $prod){
                        $products[$idx][$key] = $prod;
                    }
                }
            }
            $disoluciones = [];
            foreach($products as $prod){
                $nrDisolucion = $prod['u_ndis'];
                if($nrDisolucion > 0){
                    $disoluciones[$nrDisolucion]['name'] = $prod['u_tipo'] . ' ' . $prod['u_tipo2'];
                    $disoluciones[$nrDisolucion]['method'] = $prod['u_met1'] . ' ' . $prod['u_met'];
                    $disoluciones[$nrDisolucion]['caducidad'] = $prod['u_cad_prep'];
                }
            }

            foreach($disoluciones as $key => $disolucion){
                $dis = new ProductsDissolution();
                $dis->setName($disolucion['name']);
                $dis->setMethod($disolucion['method']);
                $dis->setExpiryDate(new DateTime($disolucion['caducidad']));
                $dis->setQrCode($this->generateDisolucionQrCode());
                $em->persist($dis);
                $em->flush();
                foreach($data['data']['u_ndis'] as $idx => $val){
                    if($val == $key){
                        $imgUrl = $this->generateUrl('nononsense_products_dissolution_view_qr', ['id' => $dis->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
                        $data['data']['u_qr_disfin'][$idx] = '<a href="' . $imgUrl . '" target="_blank"><img src="' . $imgUrl . '"/></a>';
                        $data['data']['u_qr_disfin_text'][$idx] = $dis->getQrCode();
                    }
                }
            }
            $jsonData = json_encode($data);
            $step->setStepDataValue($jsonData);
            $em->persist($step);
            $em->flush();
        }

        return true;
    }

    public function dissolutionQrAction($id)
    {
        $productInput = null;
        if ($id) {
            $productsDissolution = $this->getDoctrine()->getRepository(ProductsDissolution::class)->find($id);

            $filename = "qr_disolution_" . $productsDissolution->getId() . ".png";
            $rootdir = $this->get('kernel')->getRootDir();
            $ruta_img_qr = $rootdir . "/files/material_dissolution_qr/";
            $qrWidth = 154;
            $qrPadding = 2;

            $text = [
                $productsDissolution->getName(),
                $productsDissolution->getMethod(),
                'F.Preparacion: ' . $productsDissolution->getCreated()->format('Y-m-d'),
                'F.Caducidad: ' . $productsDissolution->getExpiryDate()->format('Y-m-d')
            ];

            $qrCode = new QrCode();
            $qrCode
                ->setText($productsDissolution->getqrCode())
                ->setSize($qrWidth)
                ->setPadding($qrPadding)
                ->setErrorCorrection('high')
                ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
                ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
                ->setImageType(QrCode::IMAGE_TYPE_PNG);

            $qrCode->save($ruta_img_qr . $filename);
            $content = $this->addQrInfoData($ruta_img_qr . $filename, ($qrWidth + $qrPadding * 2), $text);

            $response = new Response();
            $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, 'image.png');
            $response->headers->set('Content-Disposition', $disposition);
            $response->headers->set('Content-Type', 'image/png');
            $response->setContent($content);
            return $response;
        }
        echo "Error al generar el QR";
        exit();
    }

    /**
     * @param $qrPath
     * @param $qrWidth
     * @param $text
     */
    private function addQrInfoData($qrPath, $qrWidth, $text)
    {
        $lines = count($text);
        $squareHeight = 15*$lines;
        $rectangle = imagecreatetruecolor($qrWidth, $squareHeight);
        $white = imagecolorallocate($rectangle, 255, 255, 255);
        imagefilledrectangle($rectangle, 1, 1, $qrWidth-2, $squareHeight-3, $white);

        $qrImage = imagecreatefrompng($qrPath);
        $black = imagecolorallocate($rectangle, 0, 0, 0);
        $rootdir = $this->get('kernel')->getRootDir();
        $font_path = $rootdir . '/Resources/font/opensans.ttf';

        $space = 12;
        foreach($text as $line){
            imagettftext($rectangle, 7, 0, 4, $space, $black, $font_path, $line);
            $space += 12;
        }

        ob_start();
        $new = imagecreate($qrWidth, $qrWidth+$squareHeight);
        imagecopy($new, $qrImage, 0, 0, 0, 0, $qrWidth, $qrWidth);
        imagecopy($new, $rectangle, 0, $qrWidth+1, 0, 0, $qrWidth, $squareHeight);
        imagepng($new);
        $content = ob_get_clean();

        // Clear Memory
        imagedestroy($qrImage);
        imagedestroy($rectangle);
        imagedestroy($new);

        return $content;
    }

    /**
     * @return string
     */
    private function generateDisolucionQrCode()
    {
        $prefix = 'DISOL';
        /** @var ProductsDissolutionRepository $prodcutsDissolutionRepository */
        $prodcutsDissolutionRepository = $this->getDoctrine()->getRepository(ProductsDissolution::class);
        /** @var ProductsDissolution $lastInput */
        $lastInput = $prodcutsDissolutionRepository->findBy([],['id'=>'DESC'],1,0);
        if(!$lastInput){
            $lastId = 0;
        }else{
            $lastId = (int)$lastInput[0]->getId();
        }
        $inputNumber = str_pad(($lastId+1), 8, '0', STR_PAD_LEFT);
        return $prefix.$inputNumber;
    }

}