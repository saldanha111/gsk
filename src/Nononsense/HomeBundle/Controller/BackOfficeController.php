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

use Nononsense\HomeBundle\Entity\EvidenciasStep;
use Nononsense\HomeBundle\Entity\FirmasStep;
use Nononsense\HomeBundle\Entity\InstanciasSteps;
use Nononsense\HomeBundle\Entity\InstanciasWorkflows;

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

    public function grupoVerificadorIndexAction()
    {
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
            ->findBy(array("isActive" => 1));

        $GroupsList =
            $this->getDoctrine()
                ->getRepository('NononsenseGroupBundle:Groups')
                ->findBy(array("isActive" => 1));


        return $this->render('NononsenseHomeBundle:Backoffice:index.html.twig', array(
            "listadoPlantillas" => $MasterWorkflowList,
            "listadoGrupos" => $GroupsList
        ));
    }

    public function setGrupoVerificadorAction(Request $request)
    {
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
            'Se ha modificado correctamente el grupo verificador (' . $GroupEntity->getName() . ') de la plantilla: ' . $MasterWorkflowEntity->getName()
        );

        $route = $this->container->get('router')->generate('nononsense_backoffice_verificator_groups');
        return $this->redirect($route);

    }

    public function standByDocumentsListAction()
    {
        $documentsProcess = array();

        $documentsProcess = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->listStandBy();

        foreach ($documentsProcess as &$element2) {
            $idRegistro = $element2['id'];


            $reconciliacionElement = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
                ->findOneBy(array("registro_nuevo_id" => $idRegistro));

            if (isset($reconciliacionElement)) {
                $registroReconciliadoId = $reconciliacionElement->getRegistroViejoId();
/*
                $registroReconciliado = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                    ->find($registroReconciliadoId);
  */
                $registroReconciliado = $reconciliacionElement->getRegistroViejoEntity();

                $element2['reconciliacion'] = $registroReconciliado->getId();

            } else {
                $element2['reconciliacion'] = 'NO';
            }

        }


        return $this->render('NononsenseHomeBundle:Backoffice:stand_by_documents_list.html.twig', array(
            "documentsProcess" => $documentsProcess,
        ));
    }

    public function standByDocumentAction($idRegistro)
    {
        $documents = array();

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($idRegistro);

        $subcat = $registro->getMasterWorkflowEntity()->getCategory()->getName();
        $name = $registro->getMasterWorkflowEntity()->getName();

        $element = array(
            "id" => $registro->getId(),
            "subcat" => $subcat,
            "name" => $name,
            "status" => $registro->getStatus(),
            "fecha" => $registro->getModified()
        );

        $documents[] = $element;


        return $this->render('NononsenseHomeBundle:Backoffice:stand_by_document.html.twig', array(
            "documents" => $documents,
            "idregistro" => $idRegistro));
    }

    public function standByDocumentECOAction($idRegistro)
    {
        $documents = array();

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($idRegistro);

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $idRegistro, "dependsOn" => 0));

        $subcat = $registro->getMasterWorkflowEntity()->getCategory()->getName();
        $name = $registro->getMasterWorkflowEntity()->getName();

        /*
         * Habría que obtener el comentario y el nombre del FLL que lo ha autorizado
         */

        $element = array(
            "id" => $registro->getId(),
            "subcat" => $subcat,
            "name" => $name,
            "status" => $registro->getStatus(),
            "fecha" => $registro->getModified()
        );

        $documents[] = $element;

        $nombreFLL = '';
        $comentarioFLL = '';

        $firmas = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findBy(array("step_id" => $step->getId()));

        $counter = count($firmas);

        foreach ($firmas as $firma){
            $number = $firma->getNumber();
            if($counter == $number){
                $comentarioFLL = $firma->getAccion();
                $nombreFLL = $firma->getUserEntiy()->getName();
            }
        }


        return $this->render('NononsenseHomeBundle:Backoffice:stand_by_document_ECO.html.twig', array(
            "documents" => $documents,
            "idregistro" => $idRegistro,
            "comentarioFLL" => $comentarioFLL,
            "nombreFLL" => $nombreFLL));
    }

    public function standByDocumentFLLAccionAction($idRegistro, Request $request)
    {

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($idRegistro);

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $idRegistro, "dependsOn" => 0));
        /*
       * Guardar firma
       */

        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        /*
      * Guardar evidencia un paso "mas" auxiliar
      */
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(0);
        $evidencia->setUserEntiy($user);
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());


        $firmaImagen = $request->query->get('firma');
        $comentario = $request->query->get('comment');
        $accion = $request->query->get('accion');

        $registro->setInEdition(0);

        if ($accion == 'autorizar') {
            $descp = "Registro en StandBy Liberado por FLL: " . $comentario;
            $registro->setStatus(0);


        } else if ($accion == 'cancelar') {
            $descp = "Registro en StandBy Cancelado por FLL: " . $comentario;
            $registro->setStatus(8);

        } else {
            $descp = "Enviada petición a ECO sobre registro en StandBy: " . $comentario;
            $registro->setStatus(17);
        }


        /*
         * Guardar firma
         */
        $firmas = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findBy(array("step_id" => $step->getId()));

        $counter = count($firmas) + 1;

        $firma = new FirmasStep();
        $firma->setAccion($descp);
        $firma->setStepEntity($step);
        $firma->setUserEntiy($user);
        $firma->setFirma($firmaImagen);
        $firma->setStatus(1);
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);


        $em->persist($evidencia);
        $em->persist($registro);
        $em->persist($firma);
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_backoffice_standby_documents_list');
        return $this->redirect($route);
    }

    public function standByDocumentECOAccionAction($idRegistro, Request $request)
    {

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($idRegistro);

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $idRegistro, "dependsOn" => 0));
        /*
       * Guardar firma
       */

        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        /*
      * Guardar evidencia un paso "mas" auxiliar
      */
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(0);
        $evidencia->setUserEntiy($user);
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());


        $firmaImagen = $request->query->get('firma');
        $comentario = $request->query->get('comment');
        $accion = $request->query->get('accion');

        if ($accion == 'autorizar') {
            $descp = "Registro en StandBy Liberado por ECO: " . $comentario;
            $registro->setStatus(0);


        } else if ($accion == 'cancelar') {
            $descp = "Registro en StandBy Cancelado por ECO: " . $comentario;
            $registro->setStatus(8);

        }

        /*
         * Guardar firma
         */
        $firmas = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findBy(array("step_id" => $step->getId()));

        $counter = count($firmas) + 1;

        $firma = new FirmasStep();
        $firma->setAccion($descp);
        $firma->setStepEntity($step);
        $firma->setUserEntiy($user);
        $firma->setFirma($firmaImagen);
        $firma->setStatus(1);
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);


        $em->persist($evidencia);
        $em->persist($registro);
        $em->persist($firma);
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_backoffice_standby_documents_list');
        return $this->redirect($route);
    }

    private function _grantUser($user)
    {
        // isAdmimn
    }
}