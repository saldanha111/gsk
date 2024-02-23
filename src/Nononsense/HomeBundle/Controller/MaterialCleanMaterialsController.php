<?php

namespace Nononsense\HomeBundle\Controller;

use DateInterval;
use DateTime;
use Exception;
use Nononsense\HomeBundle\Entity\MaterialCleanCenters;
use Nononsense\HomeBundle\Entity\MaterialCleanCentersRepository;
use Nononsense\HomeBundle\Entity\MaterialCleanMaterials;
use Nononsense\HomeBundle\Entity\MaterialCleanMaterialsLog;
use Nononsense\HomeBundle\Entity\MaterialCleanMaterialsRepository;
use Nononsense\HomeBundle\Entity\MaterialCleanProducts;
use Nononsense\HomeBundle\Entity\MaterialCleanProductsRepository;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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

        $array_item["canCreate"] = $this->get('app.security')->permissionSeccion('mc_materials_new');

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

        $em = $this->getDoctrine()->getManager();
        /** @var MaterialCleanMaterialsRepository $materialRepository */
        $materialRepository = $em->getRepository(MaterialCleanMaterials::class);
        $array_item["items"] = $materialRepository->list($filters);
        $array_item["count"] = $materialRepository->count($filters2);
        /** @var MaterialCleanProductsRepository $productsRepository */
        $productsRepository = $em->getRepository(MaterialCleanProducts::class);
        $array_item['products'] = $productsRepository->findBy(['active' => true, 'validated' => true]);
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
        $em = $this->getDoctrine()->getManager();

        /** @var MaterialCleanMaterialsRepository $materialRepository */
        $materialRepository = $em->getRepository(materialCleanMaterials::class);
        $material = $materialRepository->find($id);

        if (!$material) {
            $is_valid = $this->get('app.security')->permissionSeccion('mc_materials_new');
            if (!$is_valid) {
                return $this->redirect($this->generateUrl('nononsense_home_homepage'));
            }
            $material = new MaterialCleanMaterials();
        }else{
            $is_valid = $this->get('app.security')->permissionSeccion('mc_materials_edit');
            if (!$is_valid) {
                return $this->redirect($this->generateUrl('nononsense_home_homepage'));
            }
        }

        /** @var MaterialCleanProductsRepository $productsRepository */
        $productsRepository = $em->getRepository(MaterialCleanProducts::class);

        /** @var MaterialCleanCentersRepository $centersRepository */
        $centersRepository = $em->getRepository(MaterialCleanCenters::class);

        if ($request->getMethod() == 'POST') {
            try {
                if($material->getId()){
                    $log = new MaterialCleanMaterialsLog();
                    $log->setUpdated($material->getUpdated())
                        ->setCreated(new DateTime())
                        ->setMaterial($material)
                        ->setProduct($material->getProduct())
                        ->setCenter($material->getCenter())
                        ->setExpirationDays($material->getExpirationDays())
                        ->setExpirationHours($material->getExpirationHours())
                        ->setAdditionalInfo($material->getAdditionalInfo())
                        ->setOtherName($material->getOtherName())
                        ->setValidated($material->getValidated())
                        ->setUpdateUser($material->getUpdateUser())
                        ->setValidateUser($material->getValidateUser())
                        ->setActive($material->getActive())
                        ->setName($material->getName())
                        ->setUpdateComment($material->getUpdateComment());
                    $material->setUpdateComment($request->get('update_comment'));
                }

                $password = $request->get('password');
                if(!$this->get('utilities')->checkUser($password)){
                    $this->get('session')->getFlashBag()->add('error', "La contraseña no es correcta.");
                    return $this->redirect($this->generateUrl('nononsense_mclean_materials_list'));
                }
                $error = 0;
                if(!$material->getId()){
                    $product = $productsRepository->find($request->get("product"));
                    $material->setProduct($product);
                    $center = $centersRepository->find($request->get("center"));
                    $material->setCenter($center);
                    $material->setName($request->get("name"));
                    $material->setotherName(($request->get("otherName")) == 1);
                    $material->setAdditionalInfo(($request->get('additionalInfo')) == 1);
                }
                $material->setActive(($request->get("active")) == 1);
                $material->setExpirationDays($request->get("expiration_days"));
                $material->setExpirationHours($request->get("expiration_hours"));
                if ($error == 0) {
                    $material->setUpdateUser($this->getUser());
                    $material->setValidated(false);
                    $material->setValidateUser(null);
                    $material->setUpdated(new DateTime());
                    $em->persist($material);
                    if(isset($log) && $log){
                        $em->persist($log);
                    }
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

        $logRepository = $em->getRepository(MaterialCleanMaterialsLog::class);
        $logs = $logRepository->findBy(['material' => $material], ['id' => 'DESC']);

        $array_item = array();
        $array_item['material'] = $material;
        $array_item['products'] = $productsRepository->findBy(['active' => true, 'validated' => true]);
        $array_item['centers'] = $centersRepository->findBy(['active' => true, 'validated' => true]);
        $array_item['currentUser'] = $this->getUser();
        $array_item['log'] = $logs;

        return $this->render('NononsenseHomeBundle:MaterialClean:material_edit.html.twig', $array_item);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|Response
     */
    public function validateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var MaterialCleanMaterialsRepository $materialsRepository */
        $marterialsRepository = $em->getRepository('NononsenseHomeBundle:MaterialCleanMaterials');
        $marterial = $marterialsRepository->find($id);

        $is_valid = $this->get('app.security')->permissionSeccion('mc_materials_edit');
        if (!$is_valid) {
            $this->get('session')->getFlashBag()->add('error', "No tienes permisos para validar materiales.");
            return $this->redirect($this->generateUrl('nononsense_mclean_materials_list'));
        }

        try {
            $password = $request->get('valPassword');
            if(!$this->get('utilities')->checkUser($password)){
                $this->get('session')->getFlashBag()->add('error', "La contraseña no es correcta.");
                return $this->redirect($this->generateUrl('nononsense_mclean_materials_list'));
            }

            $updatedMaterialUser = $marterial->getUpdateUser();
            if(!$updatedMaterialUser || $updatedMaterialUser === $this->getUser()){
                $this->get('session')->getFlashBag()->add('error', "El usuario que editó el material no puede validarlo");
                return $this->redirect($this->generateUrl('nononsense_mclean_materials_list'));
            }

            $marterial->setValidateUser($this->getUser());
            $marterial->setValidated(true);
            $marterial->setUpdated(new DateTime());
            $em->persist($marterial);
            $em->flush();
            $this->get('session')->getFlashBag()->add('message', "El material se ha validado correctamente");
        } catch (Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error al intentar validar el material "
            );
        }
        return $this->redirect($this->generateUrl('nononsense_mclean_materials_list'));
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
            $materialInput = $materialRepository->findOneBy(['id' =>$id, 'active' => true, 'validated' => true]);
            if ($materialInput) {
                $expirationDays = $materialInput->getExpirationDays() ?? 0;
                $expirationHours = $materialInput->getExpirationHours() ?? 0;
                $daysInterval = new DateInterval('P' . $expirationDays . 'D');
                $hoursInterval = new DateInterval('PT' . $expirationHours . 'H');
                $expirationDate = (new DateTime())->add($daysInterval)->add($hoursInterval);
                $otherName = $materialInput->getOtherName();
                $additionalInfo = $materialInput->getAdditionalInfo();

                $data['cleanDate'] = (new DateTime())->format('d-m-Y H:i:s');
                $data['expirationDays'] = $expirationDays;
                $data['expirationHours'] = $expirationHours;
                $data['expirationDate'] = $expirationDate->format('d-m-Y H:i:s');
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
