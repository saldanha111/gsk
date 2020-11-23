<?php

namespace Nononsense\HomeBundle\Controller;

use Exception;
use Nononsense\HomeBundle\Entity\ProductsDissolution;
use Nononsense\HomeBundle\Entity\ProductsDissolutionRepository;
use Nononsense\HomeBundle\Entity\ProductsInputsRepository;
use Nononsense\HomeBundle\Entity\ProductsInputStatus;
use Nononsense\HomeBundle\Entity\ProductsInputStatusRepository;
use Nononsense\HomeBundle\Entity\ProductsRepository;
use Nononsense\HomeBundle\Entity\ProductsTypes;
use Nononsense\HomeBundle\Entity\ProductsTypesRepository;
use Nononsense\HomeBundle\Entity\Products;
use Nononsense\HomeBundle\Entity\ProductsInputs;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Nononsense\UtilsBundle\Classes\Utils;
use Endroid\QrCode\QrCode;

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

    private function generateQrProductDissolution(ProductsDissolution $productDissolution)
    {
        $filename = "qr_material_dissolution_" . $productDissolution->getId() . ".png";
        $rootdir = $this->get('kernel')->getRootDir();
        $ruta_img_qr = $rootdir . "/files/material_dissolution_qr/";

        $qrCode = new QrCode();
        $qrCode
            ->setText($productDissolution->getqrCode())
            ->setSize(500)
            ->setPadding(5)
            ->setErrorCorrection('high')
            ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
            ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
            ->setImageType(QrCode::IMAGE_TYPE_PNG);

        $qrCode->save($ruta_img_qr . $filename);

        return $filename;
    }

    public function dissolutionQrAction($id)
    {
        $productInput = null;
        if ($id) {
            $productInput = $this->getDoctrine()->getRepository(ProductsDissolution::class)->find($id);

            if ($productInput) {
                $filename = self::generateQrProductDissolution($productInput);

                $rootdir = $this->get('kernel')->getRootDir();
                $ruta_img_qr = $rootdir . "/files/material_dissolution_qr/";

                $content = file_get_contents($ruta_img_qr . $filename);

                $response = new Response();
                $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, 'image.png');
                $response->headers->set('Content-Disposition', $disposition);
                $response->headers->set('Content-Type', 'image/png');
                $response->setContent($content);
                return $response;
            }
        }

        echo "Error al generar el QR";
        exit();
    }

}