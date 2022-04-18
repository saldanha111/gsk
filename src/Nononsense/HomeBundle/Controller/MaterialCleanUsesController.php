<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\HomeBundle\Entity\MaterialCleanCleans;
use Nononsense\HomeBundle\Entity\MaterialCleanCleansRepository;
use Nononsense\HomeBundle\Entity\MaterialCleanMaterials;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Nononsense\UtilsBundle\Classes\Utils;

class MaterialCleanUsesController extends Controller
{

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function scanAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_uses_scan');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $data = [];

        if($request->getMethod() == 'POST') {
            if($request->get('po')){
                $data['po'] = $request->get('po');
                return $this->render('NononsenseHomeBundle:MaterialClean:uses_material_index.html.twig',$data);
            }
            $this->get('session')->getFlashBag()->add(
                'error',
                "Ha ocurrido un error al intentar obtener el process order. Vuelve a intentarlo."
            );

        }
        return $this->render('NononsenseHomeBundle:MaterialClean:uses_po_index.html.twig');
    }

    /**
     * @param Request $request
     * @param string $barcode
     * @param string $po
     * @return RedirectResponse|Response
     */
    public function viewAction(Request $request, $barcode, $po)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_uses_scan');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $materialCleanCode = null;

        /** @var MaterialCleanCleansRepository $materialCleanRepository */
        $materialCleanRepository = $em->getRepository('NononsenseHomeBundle:MaterialCleanCleans');
        /** @var MaterialCleanCleans $materialCleanClean */
        $materialCleanClean = $materialCleanRepository->findBy(['code' => $barcode],['cleanDate' => 'DESC'],1);

        if (!$materialCleanClean) {
            $this->get('session')->getFlashBag()->add('error', "No se ha encontrado el material asociado a ese código.");
            return $this->redirect($this->generateUrl('nononsense_mclean_uses_scan'));
        }else{
            $materialCleanClean = reset($materialCleanClean);
        }

        // Miramos si el material cumple los requisitos para ser utilizado.
        $error = $this->checkIfCanCleanUse($materialCleanClean, $materialCleanRepository, $po);
        if(!$error){
            $this->get('session')->getFlashBag()->add('message', "Fecha de caducidad no alcanzada. Se puede usar el material o elemento.");
        }

        $array_item = [
            'materialCleanClean' => $materialCleanClean,
            'code' => $materialCleanClean->getCode(),
            'center' => $materialCleanClean->getCenter(),
            'material' => $materialCleanClean->getMaterial(),
            'lotCode' => $po,
            'cleanDate' => ($materialCleanClean->getCleanDate())->format('d-m-Y H:i:s'),
            'cleanExpirationDate' => ($materialCleanClean->getCleanExpiredDate())->format('d-m-Y H:i:s'),
            'cleanUser' => $materialCleanClean->getCleanUser(),
            'usesDate' => (new DateTime())->format('d-m-Y H:i:s'),
            'error' => $error
        ];

        return $this->render('NononsenseHomeBundle:MaterialClean:uses_view.html.twig', $array_item);
    }

    public function saveAction(Request $request, string $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_uses_scan');
        if (!$is_valid || $request->getMethod() != 'POST') {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $error = false;

        try {
            $po = $request->get('lot-code');
            $password = $request->get('password');
            if(!$this->get('utilities')->checkUser($password)){
                $this->get('session')->getFlashBag()->add('error', "La contraseña no es correcta.");
                $error = 1;
            }

            if(!$request->get('lot-code')){
                $this->get('session')->getFlashBag()->add('error', "Ha ocurrido un error al procesar el process order, por favor vuelva a intentarlo");
                $error = true;
            }

            $em = $this->getDoctrine()->getManager();
            /** @var MaterialCleanCleansRepository $materialCleanRepository */
            $materialCleanRepository = $em->getRepository('NononsenseHomeBundle:MaterialCleanCleans');
            /** @var MaterialCleanCleans $materialCleanClean */
            $materialCleanClean = $materialCleanRepository->findBy(['code' => urldecode($id)],['cleanDate' => 'DESC'],1);

            if (!$materialCleanClean) {
                $this->get('session')->getFlashBag()->add('error', "No se ha encontrado el material asociado a ese código.");
                $error = true;
            }else{
                $materialCleanClean = reset($materialCleanClean);
            }

            $error = ($error || $this->checkIfCanCleanUse($materialCleanClean, $materialCleanRepository, $po));

            if (!$error) {
                $now = new DateTime();
                $firma = 'Utilización de material registrada con contraseña de usuario el día ' . $now->format('d-m-Y H:i:s');

                $html = '
                    <p>Utilización del material</p>
                    <ul>
                        <li>Material:'.$materialCleanClean->getMaterial()->getName().'</li>
                        <li>Código:'.$materialCleanClean->getCode().'</li>
                        <li>Centro:'.$materialCleanClean->getCenter()->getName().'</li>
                        <li>Usuario:'.$this->getUser()->getUsername().'</li>
                    </ul>';

                    $file = Utils::generatePdf($this->container, 'GSK - Material limpio', 'Utilización del material', $html, 'material', $this->getParameter('crt.root_dir'));
                    Utils::setCertification($this->container, $file, 'material-utilización', $materialCleanClean->getId());

                $materialCleanClean
                    ->setVerificationDate(new DateTime())
                    ->setVerificationUser($this->getUser())
                    ->setVerificationSignature($firma)
                    ->setLotNumber($po)
                    ->setUseInformation($request->get('additionalInfo'))
                    ->setStatus(2);

                $em->persist($materialCleanClean);
                $em->flush();

                $this->get('session')->getFlashBag()->add('message', "La verificación se ha registrado con éxito");
                return $this->redirect($this->generateUrl('nononsense_mclean_uses_scan'));
            }
        } catch (Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error al intentar registrar la verificación"
            );
            return $this->redirect($this->generateUrl('nononsense_mclean_uses_view',['barcode' => $id]));
        }
    }

    /**
     * @param MaterialCleanCleans $materialCleanClean
     * @return bool
     */
    private function isClearOutOfDate($materialCleanClean)
    {
        $now = new DateTime();
        $expirationDate = $materialCleanClean->getCleanExpiredDate();
        return ($expirationDate < $now);
    }

    /**
     * @param MaterialCleanCleans $materialCleanClean
     * @param MaterialCleanCleansRepository $materialCleanRepository
     * @param string $po
     * @return bool
     */
    private function checkIfCanCleanUse($materialCleanClean, $materialCleanRepository, $po)
    {
        $error = false;
        // Si el material no está limpio
        if($materialCleanClean->getStatus() != 1){
            $statusName = $materialCleanRepository->getStatusName($materialCleanClean->getStatus());
            $this->get('session')->getFlashBag()->add('error', "El estado del material es: {$statusName}");
            $error = true;
        }

        $outOfDate = $this->isClearOutOfDate($materialCleanClean);
        if($outOfDate){
            $this->get('session')->getFlashBag()->add('error', "La fecha de caducidad de la limpieza ha pasado.");
            // Marcamos el material como sucio y lo asignamos al po
            $this->markMaterialAsDirty($materialCleanClean, $po);
            $error = true;
        }

        $sameUser = ($this->getUser()->getId() == ($materialCleanClean->getCleanUser())->getId());
        if($sameUser){
            $this->get('session')->getFlashBag()->add('error', "El usuario que ha realizado la limpieza no puede ser el mismo que el usuario que la valida.");
            $error = true;
        }

        return $error;
    }

    /**
     * @param MaterialCleanCleans $materialCleanClean
     * @param string $po
     * @return void
     */
    private function markMaterialAsDirty(MaterialCleanCleans $materialCleanClean, string $po)
    {
        if($materialCleanClean->getStatus() == 1){
            $em = $this->getDoctrine()->getManager();
            $materialCleanClean
                ->setLotNumber($po)
                ->setDirtyMaterialUser($this->getUser())
                ->setDirtyMaterialDate(new DateTime())
                ->setStatus(3);
            $em->persist($materialCleanClean);
            $em->flush();
        }
    }
}
