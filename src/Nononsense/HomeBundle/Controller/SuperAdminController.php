<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\HomeBundle\Entity\ArchiveSignatures;
use Nononsense\HomeBundle\Entity\ArchiveTypes;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SuperAdminController extends Controller
{
    public function editAction(Request $request)
    {
        if (!$this->getUser()->getSuperAdmin()) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "No tiene permisos para entrar en esta sección"
            );
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $sections=$em->getRepository('NononsenseHomeBundle:SAInProduction')->findAll();

        if ($request->getMethod() == 'POST' && $this->saveData($request)) {
            return $this->redirect($this->generateUrl('nononsense_superadmin'));
        }

        $data = [
            'sections' => $sections
        ];

        return $this->render('NononsenseHomeBundle:Default:supeardmin.html.twig', $data);
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function saveData(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $save=false;
        try {
            foreach($request->get("sections") as $keySection => $sectionValue){
                $section=$em->getRepository('NononsenseHomeBundle:SAInProduction')->findOneBy(array("id" => $keySection));
                if($sectionValue==1){
                    $value=true;
                }
                else{
                    $value=false;
                }

                $section->setProduction($value);
                $em->persist($section);
            }

            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'message',
                "Las secciónes en producción han cambiado"
            );
            $saved = true;
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error al intentar cambiar las secciones en producción"
            );
        }
        return $saved;
    }
}
