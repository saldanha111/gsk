<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\HomeBundle\Entity\MaterialCleanCenters;
use Nononsense\HomeBundle\Entity\MaterialCleanCleans;
use Nononsense\HomeBundle\Entity\MaterialCleanCodes;
use Nononsense\HomeBundle\Entity\MaterialCleanMaterials;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Nononsense\UtilsBundle\Classes\Utils;

class MaterialCleanCleansController extends Controller
{

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function scanAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_cleans_scan');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        return $this->render('NononsenseHomeBundle:MaterialClean:cleans_index.html.twig');
    }

    /**
     * @param Request $request
     * @param string $barcode
     * @return RedirectResponse|Response
     */
    public function viewAction(Request $request, $barcode)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_cleans_scan');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $materialCleanCode = $em->getRepository(MaterialCleanCodes::class)->findOneBy(['code' => $barcode]);

        if (!$materialCleanCode || !$materialCleanCode->getIdMaterial()->getActive()) {
            $materialCleanCode = new MaterialCleanCodes();
        }

        $cleanDate = new DateTime();
        $expirationDate = $this->getCleanDate($materialCleanCode->getIdMaterial()->getExpirationDays());

        $array_item = array();
        $array_item["materials"] = $this->getDoctrine()->getRepository(MaterialCleanMaterials::class)->findBy(
            ['active' => true],
            ['name' => 'ASC']
        );
        $array_item["centers"] = $this->getDoctrine()->getRepository(MaterialCleanCenters::class)->findBy(
            ['active' => true],
            ['name' => 'ASC']
        );
        $array_item['code'] = $barcode;
        $array_item['materialCleanCode'] = $materialCleanCode;
        $array_item['cleanDate'] = $cleanDate->format('d-m-Y');
        $array_item['expirationDate'] = ($expirationDate instanceof DateTime) ? $expirationDate->format('d-m-Y') : '';
        $array_item['materialsUrl'] = $this->generateUrl('nononsense_mclean_get_material_by_center_json', ['id' => 'xxx']);

        return $this->render('NononsenseHomeBundle:MaterialClean:cleans_view.html.twig', $array_item);
    }

    public function saveAction(Request $request, string $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_cleans_scan');
        if (!$is_valid || $request->getMethod() != 'POST') {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        try {
            $error = 0;

            $password = $request->get('password');
            if(!$this->get('utilities')->checkUser($password)){
                $this->get('session')->getFlashBag()->add('error', "La contraseña no es correcta.");
                $error = 1;
            }

            if (urldecode($id) !== $request->get('code')) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "Ha ocurrido un error al procesar el código, por favor vuelva a intentarlo"
                );
                $error = 1;
            }

            $em = $this->getDoctrine()->getManager();
            $materialCleanCode = $em->getRepository('NononsenseHomeBundle:MaterialCleanCodes')->findOneByCode(
                urldecode($id)
            );

            if ($materialCleanCode) {
                $material = $materialCleanCode->getIdMaterial();
                $center = $materialCleanCode->getIdCenter();
            } else {
                $material = $em->getRepository('NononsenseHomeBundle:MaterialCleanMaterials')->find(
                    $request->get("material")
                );
                $center = $em->getRepository('NononsenseHomeBundle:MaterialCleanCenters')->find(
                    $request->get("center")
                );
            }

            if (
                !$material || !$center
                || ($material->getOtherName() === true && $request->get('materialOther') == '')
                || ($material->getAdditionalInfo() === true && $request->get('additionalInfo') == '')
            ) {
                $this->get('session')->getFlashBag()->add('error', "Todos los datos son obligatorios");
                $error = 1;
            }

            if ($error == 0) {
                $now = new DateTime();
                $firma = 'Limpieza registrada con contraseña de usuario el día ' . $now->format('d-m-Y H:i:s');
                $materialClean = new MaterialCleanCleans();
                $cleanDate = new DateTime();
                $expirationDate = $this->getCleanDate($material->getExpirationDays());
                if($material->getCenter()->getId() !== $center->getId()){
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        "Material seleccionado no pertenece al centro"
                    );
                }else{

                    $html = '
                        <p>Limpieza de material</p>
                        <ul>
                            <li>Material: '.$material->getName().'</li>
                            <li>Código: '.$request->get('code').'</li>
                            <li>Centro: '.$center->getName().'</li>
                            <li>Usuario: '.$this->getUser()->getUsername().'</li>
                            <li>Fecha: '.$now->format('d-m-Y H:i:s').'</li>
                        </ul>';

                    $file = Utils::generatePdf($this->container, 'GSK - Material limpio', 'Limpieza de material', $html, 'material', $this->getParameter('crt.root_dir'));
                    Utils::setCertification($this->container, $file, 'material', $material->getId());

                    $materialClean
                        ->setMaterial($material)
                        ->setCenter($center)
                        ->setCleanDate($cleanDate)
                        ->setCleanExpiredDate($expirationDate)
                        ->setCode($request->get('code'))
                        ->setCleanUser($this->getUser())
                        ->setSignature($firma)
                        ->setMaterialOther($request->get('materialOther'))
                        ->setAdditionalInfo($request->get('additionalInfo'))
                        ->setStatus(1);
                    $em->persist($materialClean);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add(
                        'message',
                        "La limpieza de material se ha guardado correctamente"
                    );
                    return $this->redirect($this->generateUrl('nononsense_mclean_cleans_scan'));
                }
            }
        } catch (Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error al intentar guardar los datos de la limpieza "
            );
        }
        return $this->redirect($this->generateUrl('nononsense_mclean_cleans_view', ['barcode' => $id]));
    }

    private function getCleanDate($expirationDays)
    {
        try {
            $expirationInterval = new \DateInterval('P' . $expirationDays . 'D');
            $expirationDate = (new DateTime())->add($expirationInterval);
        } catch (Exception $e) {
            $expirationDate = null;
            $this->get('session')->getFlashBag()->add(
                'error',
                "No se ha podido recuperar el tiempo de caducidad de la limpieza "
            );
        }
        return $expirationDate;
    }

    public function checkPassAction(Request $request)
    {
        $password = $request->get('password');

        $isValidPassword = ($this->get('utilities')->checkUser($password));

        return new JsonResponse(['valid' => $isValidPassword]);
    }
}
