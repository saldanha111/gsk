<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 03/04/2018
 * Time: 19:44
 */

namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BackOfficeController extends Controller
{
    public function indexAction()
    {
        /*
         * Cambiar los permisos para que sea por grupo admin
         */
        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($user->getId() != 1) {
            exit;
        }


        echo "hola admin Gus!";
        exit;
    }

    public function grupoVerificadorIndexAction(){
        /*
         * Cambiar los permisos para que sea por grupo admin
         */
        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($user->getId() != 1) {
            echo "no tienes permisos para estar aquí";
            exit;
        }

        // Obtener todos los masterworkflow

        $MasterWorkflowList = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:MasterWorkflows')
            ->findBy(array("isActive"=>1));

        $GroupsList =
            $this->getDoctrine()
                ->getRepository('NononsenseGroupBundle:Groups')
                ->findBy(array("isActive"=>1));


        return $this->render('NononsenseHomeBundle:Backoffice:index.html.twig',array(
            "listadoPlantillas" => $MasterWorkflowList,
            "listadoGrupos" => $GroupsList
        ));
    }

    public function setGrupoVerificadorAction(Request $request){
        $grupo = $request->query->get('gruposDisponiblesSelect');
        $master = $request->query->get('plantillaSeleccionada');

        $MasterWorkflowEntity = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:MasterWorkflows')
            ->find($master);

        $GroupEntity = $this->getDoctrine()
            ->getRepository('NononsenseGroupBundle:Groups')
            ->find($grupo);

        $MasterWorkflowEntity->setGrupoVerificacion($GroupEntity);

        $em = $this->getDoctrine()->getManager();
        $em->persist($MasterWorkflowEntity);
        $em->flush();

        //Poner flash mensaje
        $this->get('session')->getFlashBag()->add(
            'success',
            'Se ha modificado correctamente el grupo verificador ('.$GroupEntity->getName().') de la plantilla: ' . $MasterWorkflowEntity->getName()
        );

        $route = $this->container->get('router')->generate('nononsense_backoffice_verificator_groups');
        return $this->redirect($route);

    }

    private function _grantUser($user){
        // isAdmimn
    }
}