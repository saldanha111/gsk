<?php

namespace Nononsense\HomeBundle\Controller;

use DateInterval;
use DateTime;
use Exception;
use Nononsense\HomeBundle\Entity\MaterialCleanCenters;
use Nononsense\HomeBundle\Entity\MaterialCleanCentersRepository;
use Nononsense\HomeBundle\Entity\MaterialCleanMaterials;
use Nononsense\HomeBundle\Entity\MaterialCleanMaterialsRepository;
use Nononsense\HomeBundle\Entity\MaterialCleanProducts;
use Nononsense\HomeBundle\Entity\MaterialCleanProductsRepository;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MaterialCleanMaterialsController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_materials_list');
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

        if ($request->get("name")) {
            $filters["name"] = $request->get("name");
            $filters2["name"] = $request->get("name");
        }

        if ($request->get("product")) {
            $filters["product"] = $request->get("product");
            $filters2["product"] = $request->get("product");
        }

        if ($request->get("center")) {
            $filters["center"] = $request->get("center");
            $filters2["center"] = $request->get("center");
        }

        $array_item["filters"] = $filters;

        $em = $this->getDoctrine()->getManager();
        /** @var MaterialCleanMaterialsRepository $materialRepository */
        $materialRepository = $em->getRepository(MaterialCleanMaterials::class);
        $array_item["items"] = $materialRepository->list($filters);
        $array_item["count"] = $materialRepository->count($filters2);
        /** @var MaterialCleanProductsRepository $productsRepository */
        $productsRepository = $em->getRepository(MaterialCleanProducts::class);
        $array_item['products'] = $productsRepository->findBy(['active' => true]);
        /** @var MaterialCleanCentersRepository $centersRepository */
        $centersRepository = $em->getRepository(MaterialCleanCenters::class);
        $array_item['centers'] = $centersRepository->findAll();
        $array_item["filters"] = $filters;

        $url = $this->container->get('router')->generate('nononsense_mclean_materials_list');
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

        return $this->render('NononsenseHomeBundle:MaterialClean:material_index.html.twig', $array_item);
    }

    public function editAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_materials_edit');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();

        /** @var MaterialCleanMaterialsRepository $materialRepository */
        $materialRepository = $em->getRepository(materialCleanMaterials::class);
        $material = $materialRepository->find($id);

        /** @var MaterialCleanProductsRepository $productsRepository */
        $productsRepository = $em->getRepository(MaterialCleanProducts::class);

        /** @var MaterialCleanCentersRepository $centersRepository */
        $centersRepository = $em->getRepository(MaterialCleanCenters::class);

        if (!$material) {
            $material = new MaterialCleanMaterials();
        }

        if ($request->getMethod() == 'POST') {
            try {
                $error = 0;
                if(!$material->getId()){
                    $product = $productsRepository->find($request->get("product"));
                    $material->setProduct($product);
                    $center = $centersRepository->find($request->get("center"));
                    $material->setCenter($center);
                    $material->setName($request->get("name"));
                    $material->setotherName(($request->get("otherName")) == 1);
                    $material->setAdditionalInfo($request->get('additionalInfo'));
                }
                $material->setActive($request->get("active"));
                $material->setExpirationDays($request->get("expiration_days"));
                if ($error == 0) {
                    $em->persist($material);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('message', "El material se ha guardado correctamente");
                    return $this->redirect($this->generateUrl('nononsense_mclean_materials_list'));
                }
            } catch (Exception $e) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error al intentar guardar los datos del material "
                );
            }
        }

        $array_item = array();
        $array_item['material'] = $material;
        $array_item['products'] = $productsRepository->findBy(['active' => true]);
        $array_item['centers'] = $centersRepository->findBy(['active' => true]);

        return $this->render('NononsenseHomeBundle:MaterialClean:material_edit.html.twig', $array_item);
    }

    public function ajaxDataAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_return = array();
        $data = array();
        $status = 500;
        try {
            /** @var MaterialCleanMaterialsRepository $materialRepository */
            $materialRepository = $em->getRepository(MaterialCleanMaterials::class);
            $materialInput = $materialRepository->findOneBy(['id' =>$id, 'active' => true]);
            if ($materialInput) {
                $expirationDays = $materialInput->getExpirationDays();
                $expirationInterval = new DateInterval('P' . $expirationDays . 'D');
                $expirationDate = (new DateTime())->add($expirationInterval);
                $otherName = $materialInput->getOtherName();
                $additionalInfo = $materialInput->getAdditionalInfo();

                $data['expirationDays'] = $expirationDays;
                $data['expirationDate'] = $expirationDate->format('d-m-Y');
                $data['otherName'] = $otherName;
                $data['additionalInfo'] = $additionalInfo;
                $status = 200;
            }
        } catch (Exception $e) {
        }

        $array_return['data'] = $data;
        $array_return['status'] = $status;

        return new JsonResponse($array_return);
    }

    public function deleteAction($id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_materials_edit');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }
        $em = $this->getDoctrine()->getManager();
        try {
            /** @var MaterialCleanMaterialsRepository $materialRepository */
            $materialRepository = $em->getRepository(MaterialCleanMaterials::class);
            $material = $materialRepository->find($id);

            if ($material) {
                $material->setActive(false);
                $em->persist($material);
                $em->flush();
                $this->get('session')->getFlashBag()->add('message', "El material se ha inactivado correctamente");
            } else {
                $this->get('session')->getFlashBag()->add('message', "El material no existe");
            }
        } catch (Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error al intentar inactivar el material"
            );
        }

        return $this->redirect($this->generateUrl('nononsense_mclean_materials_list'));
    }
}
