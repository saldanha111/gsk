<?php

namespace Nononsense\HomeBundle\Controller;

use Com\Tecnick\Barcode\Barcode;
use Com\Tecnick\Barcode\Exception as BCodeException;
use Com\Tecnick\Color\Exception as BColorException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Nononsense\HomeBundle\Entity\MaterialCleanCenters;
use Nononsense\HomeBundle\Entity\MaterialCleanCodes;
use Nononsense\HomeBundle\Entity\MaterialCleanCodesRepository;
use Nononsense\HomeBundle\Entity\MaterialCleanMaterials;
use Nononsense\HomeBundle\Entity\MaterialCleanMaterialsRepository;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class MaterialCleanCodesController extends Controller
{
    private const codePrefix = 'ML';

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_codes_list');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters = [];
        $filters2 = [];

        if ($request->get("page")) {
            $filters["limit_from"] = $request->get("page") - 1;
        } else {
            $filters["limit_from"] = 0;
        }
        $filters["limit_many"] = 15;

        if ($request->get("code")) {
            $filters["code"] = $request->get("code");
            $filters2["code"] = $request->get("code");
        }

        if ($request->get("material")) {
            $filters["material"] = $request->get("material");
            $filters2["material"] = $request->get("material");
        }

        if ($request->get("center")) {
            $filters["center"] = $request->get("center");
            $filters2["center"] = $request->get("center");
        }

        $array_item["filters"] = $filters;

        /** @var MaterialCleanCodesRepository $materialCleanCodes */
        $materialCleanCodes = $this->getDoctrine()->getRepository(MaterialCleanCodes::class);
        $array_item["materials"] = $this->getDoctrine()->getRepository(MaterialCleanMaterials::class)->findBy([], ['name' => 'ASC']);
        $array_item["centers"] = $this->getDoctrine()->getRepository(MaterialCleanCenters::class)->findBy([], ['name' => 'ASC']);
        $array_item["items"] = $materialCleanCodes->list($filters);
        $array_item["count"] = $materialCleanCodes->count($filters2);

        $url = $this->container->get('router')->generate('nononsense_mclean_codes_list');
        $params = $request->query->all();
        unset($params["page"]);
        if (!empty($params)) {
            $parameters = true;
        } else {
            $parameters = false;
        }
        $array_item["pagination"] = Utils::paginador(
            $filters["limit_many"],
            $request,
            $url,
            $array_item["count"],
            "/",
            $parameters
        );

        return $this->render('NononsenseHomeBundle:MaterialClean:code_index.html.twig', $array_item);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_codes_edit');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $code = $em->getRepository('NononsenseHomeBundle:MaterialCleanCodes')->find($id);

        if (!$code) {
            $code = new MaterialCleanCodes();
        }

        if ($request->getMethod() == 'POST') {
            try {
                $error = 0;
                if (!($request->get("center") > 0) || !($request->get("material") > 0)) {
                    $this->get('session')->getFlashBag()->add('error', "Tienes que seleccionar un centro de trabajo y un material");
                    $error = 1;
                }

                if ($error == 0) {
                    $center = $this->getDoctrine()->getRepository('NononsenseHomeBundle:MaterialCleanCenters')->find($request->get("center"));
                    $material = $this->getDoctrine()->getRepository('NononsenseHomeBundle:MaterialCleanMaterials')->find($request->get("material"));
                    if($material->getCenter()->getId() === $center->getId()){
                        $code->setIdCenter($center);
                        $code->setIdMaterial($material);
                        $em->persist($code);
                        $em->flush();
                        if($em->contains($code)){
                            $barCode = self::codePrefix . str_pad($code->getId(), 10, "0", STR_PAD_LEFT);
                            $code->setCode($barCode);
                            $em->persist($code);
                            $em->flush();
                        }
                        $this->get('session')->getFlashBag()->add('message', "El código se ha guardado correctamente");
                        return $this->redirect($this->generateUrl('nononsense_mclean_codes_list'));
                    }else{
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            "El material seleccionado no pertenece al centro."
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error al intentar guardar los datos del material: " . $e->getMessage()
                );
            }
        }

        $array_item = array();
        $array_item["centers"] = $this->getDoctrine()->getRepository(MaterialCleanCenters::class)->findBy(['active' => true], ['name' => 'ASC']);
        $array_item['code'] = $code;
        $array_item['materialsUrl'] = $this->generateUrl('nononsense_mclean_get_material_by_center_json', ['id' => 'xxx']);

        return $this->render('NononsenseHomeBundle:MaterialClean:code_edit.html.twig', $array_item);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|Response
     */
    public function viewBarcodeAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_codes_list');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        if ($id) {
            $mcBarcode = $this->getDoctrine()->getRepository('NononsenseHomeBundle:MaterialCleanCodes')->find($id);
            if ($mcBarcode) {
                $content = $this->getBarcodeImg($mcBarcode);
                if ($content) {
                    $response = new Response();
                    $disposition = $response->headers->makeDisposition(
                        ResponseHeaderBag::DISPOSITION_INLINE,
                        $mcBarcode->getCode() . '.png'
                    );
                    $response->headers->set('Content-Disposition', $disposition);
                    $response->headers->set('Content-Type', 'image/png');
                    $response->setContent($content);
                    return $response;
                }
            }
        }

        $this->get('session')->getFlashBag()->add(
            'error',
            "Se ha producido un error al intentar obtener el código de barras"
        );
        return $this->redirect($this->generateUrl('nononsense_mclean_codes_list'));
    }

    public function ajaxGetMaterialDataAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_return = array();
        $data = array();
        $status = 500;
        try {
            /** @var MaterialCleanMaterialsRepository $materialRepository */
            $materialRepository = $em->getRepository(MaterialCleanMaterials::class);
            $materialInput = $materialRepository->findBy(['center' =>$id, 'active' => true]);
            $data = $this->renderView('NononsenseHomeBundle:MaterialClean:material_select.html.twig', ['materials' => $materialInput]);
            $status = 200;

        } catch (Exception $e) {

        }

        $array_return['data'] = $data;
        $array_return['status'] = $status;

        return new JsonResponse(json_encode($array_return));
    }

    /**
     * @param $mcCode
     * @return string
     */
    private function getBarcodeImg($mcCode)
    {
        $result = '';
        $code = $mcCode->getCode();
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
                "Se ha producido un error al intentar obtener el código de barras: " . $e->getMessage()
            );
        }

        return $result;
    }

}
