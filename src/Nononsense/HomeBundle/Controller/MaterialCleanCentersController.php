<?php

namespace Nononsense\HomeBundle\Controller;

use Exception;
use Nononsense\HomeBundle\Entity\MaterialCleanCenters;
use Nononsense\HomeBundle\Entity\MaterialCleanDepartments;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MaterialCleanCentersController extends Controller
{
    public function listAction(Request $request)
    {
        if (!$this->get('app.security')->permissionSeccion('mc_centers_list')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters = [];
        if ($request->get("page")) {
            $filters["limit_from"] = $request->get("page") - 1;
        } else {
            $filters["limit_from"] = 0;
        }
        $filters["limit_many"] = 15;

        if ($request->get("name")) {
            $filters["name"] = $request->get("name");
        }

        $array_item["canCreate"] = $this->get('app.security')->permissionSeccion('mc_centers_new');
        $array_item["filters"] = $filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(MaterialCleanCenters::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(MaterialCleanCenters::class)->count($filters);
        $array_item["pagination"] = Utils::getPaginator($request, $filters["limit_many"], $array_item["count"]);

        return $this->render('NononsenseHomeBundle:MaterialClean:center_index.html.twig', $array_item);
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response|null
     */
    public function editAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_centers_edit');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $center = $em->getRepository(MaterialCleanCenters::class)->find($id);

        if (!$center) {
            $is_valid = $this->get('app.security')->permissionSeccion('mc_centers_new');
            if (!$is_valid) {
                return $this->redirect($this->generateUrl('nononsense_home_homepage'));
            }
            $center = new MaterialCleanCenters();
        }else{
            $is_valid = $this->get('app.security')->permissionSeccion('mc_centers_edit');
            if (!$is_valid) {
                return $this->redirect($this->generateUrl('nononsense_home_homepage'));
            }
        }

        $departments = $em->getRepository(MaterialCleanDepartments::class)->findBy(['active' => true]);

        if ($request->getMethod() == 'POST') {
            try {
                if(!$center->getName()) {
                    $center->setName($request->get("name"));
                }
                $center->setDescription($request->get("description"));
                $center->setActive($request->get("active"));

                $error = 0;
                $centerName = $em->getRepository(MaterialCleanCenters::class)->findOneBy(
                    ['name' => $request->get("name")]
                );
                if ($centerName && $centerName->getId() != $center->getId()) {
                    $this->get('session')->getFlashBag()->add('error', "Ese centro ya está registrado.");
                    $error = 1;
                }
                if(!$center->getDepartment()) {
                    $department = $em->getRepository(MaterialCleanDepartments::class)->find($request->get("department"));
                    if(!$department){
                        $this->get('session')->getFlashBag()->add('error', "El departamento no es correcto.");
                        $error = 1;
                    }else{
                        $center->setDepartment($department);
                    }
                }
                if ($request->get("depar") && $centerName->getId() != $center->getId()) {
                    $this->get('session')->getFlashBag()->add('error', "Ese centro ya está registrado.");
                    $error = 1;
                }

                if ($error == 0) {
                    $em->persist($center);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('message', "El centro se ha guardado correctamente");
                    return $this->redirect($this->generateUrl('nononsense_mclean_centers_list'));
                }
            } catch (Exception $e) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error al intentar guardar los datos del material: "
                );
            }
        }

        $array_item = array();
        $array_item['center'] = $center;
        $array_item['departments'] = $departments;

        return $this->render('NononsenseHomeBundle:MaterialClean:center_edit.html.twig', $array_item);
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_centers_edit');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $center = $em->getRepository('NononsenseHomeBundle:MaterialCleanCenters')->find($id);

            if ($center) {
                $center->setActive(false);
                $em->persist($center);
                $em->flush();
                $this->get('session')->getFlashBag()->add('message', "El centro se ha inactivado correctamente");
            } else {
                $this->get('session')->getFlashBag()->add('message', "El centro no existe");
            }
        } catch (Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error al intentar inactivar el centro: "
            );
        }

        return $this->redirect($this->generateUrl('nononsense_mclean_centers_list'));
    }
}
