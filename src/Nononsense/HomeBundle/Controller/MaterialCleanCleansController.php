<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\HomeBundle\Entity\MaterialCleanCenters;
use Nononsense\HomeBundle\Entity\MaterialCleanCleans;
use Nononsense\HomeBundle\Entity\MaterialCleanCodes;
use Nononsense\HomeBundle\Entity\MaterialCleanMaterials;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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
        $materialCleanCode = null;

        $materialCleanCode = $em->getRepository(MaterialCleanCodes::class)->findOneBy(['code' => $barcode]);

        if (!$materialCleanCode) {
            $materialCleanCode = new MaterialCleanCodes();
        }

        $cleanDate = new DateTime();
        $expirationDate = $this->getCleanDate($materialCleanCode->getIdMaterial()->getExpirationDays());

        $array_item = array();
        $array_item["materials"] = $this->getDoctrine()->getRepository(MaterialCleanMaterials::class)->findBy(
            [],
            ['name' => 'ASC']
        );
        $array_item["centers"] = $this->getDoctrine()->getRepository(MaterialCleanCenters::class)->findBy(
            [],
            ['name' => 'ASC']
        );
        $array_item['code'] = $barcode;
        $array_item['materialCleanCode'] = $materialCleanCode;
        $array_item['cleanDate'] = $cleanDate->format('d-m-Y');
        $array_item['expirationDate'] = ($expirationDate instanceof DateTime) ? $expirationDate->format('d-m-Y') : '';

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

            if (!($request->get("firma")) || !(strpos($request->get("firma"), 'data:image/png;base64') === 0)) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "No se ha podido procesar la firma, por favor vuelva a intentarlo"
                );
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

            if (!$material || !$center || ($material->getOtherName() === true && $request->get(
                        'materialOther'
                    ) == '')) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "Tienes que seleccionar un centro de trabajo y un material"
                );
                $error = 1;
            }

            if ($error == 0) {
                $materialClean = new MaterialCleanCleans();
                $cleanDate = new DateTime();
                $expirationDate = $this->getCleanDate($material->getExpirationDays());
                $materialClean
                    ->setMaterial($material)
                    ->setCenter($center)
                    ->setCleanDate($cleanDate)
                    ->setCleanExpiredDate($expirationDate)
                    ->setCode($request->get('code'))
                    ->setCleanUser($this->getUser())
                    ->setSignature($request->get('firma'))
                    ->setMaterialOther($request->get('materialOther'))
                    ->setStatus(1);
                $em->persist($materialClean);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'message',
                    "La limpieza de material se ha guardado correctamente"
                );
                return $this->redirect($this->generateUrl('nononsense_mclean_cleans_scan'));
            }
        } catch (Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error al intentar guardar los datos de la limpieza: " . $e->getMessage()
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
                "No se ha podido recuperar el tiempo de caducidad de la limpieza: " . $e->getMessage()
            );
        }
        return $expirationDate;
    }

}
