<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 03/04/2018
 * Time: 19:44
 */

namespace Nononsense\HomeBundle\Controller;


use Nononsense\HomeBundle\Entity\CancelacionStep;
use Nononsense\HomeBundle\Entity\FirmasStep;
use Nononsense\HomeBundle\Entity\MetaData;
use Nononsense\HomeBundle\Entity\MetaFirmantes;
use Nononsense\HomeBundle\Entity\Revision;
use Nononsense\HomeBundle\Entity\RevisionInstanciaWorkflow;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\HomeBundle\Entity\InstanciasWorkflows;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasSteps;
use Nononsense\HomeBundle\Entity\EvidenciasStep;

use Nononsense\UtilsBundle\Classes;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class RegistroValidationController extends Controller
{
    public function listAction($page, $query)
    {
        $max = 10;
        $user = $this->container->get('security.context')->getToken()->getUser();
        $user_logged = $user->getId();

        $arrayGroupsUser = array();

        foreach ($user->getGroups() as $groupMe) {
            $type = $groupMe->getGroup()->getId();
            $arrayGroupsUser[] = $type;
        }

        //var_dump($arrayTypes);
        $idregistro = "";

        $idcodigomaterial = "";
        $idlote = "";
        $idcodigoequipo = "";
        $idworkordersap = "";
        $idcodigodocumento = "";

        /*
        $documents = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RevisionInstanciaWorkflow')
            ->listValidacionesPendientes($user_logged, $arrayTypes);
        */
        $documents = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->listValidacionesPendientes($user_logged, $arrayGroupsUser);

        if ($query != 'q') {
            $querySplitted = explode("_", $query);
            $idregistro = rawurldecode($querySplitted[0]);
            $idcodigomaterial = rawurldecode($querySplitted[1]);
            $idlote = rawurldecode($querySplitted[2]);
            $idcodigoequipo = rawurldecode($querySplitted[3]);
            $idworkordersap = rawurldecode($querySplitted[4]);
            $idcodigodocumento = rawurldecode($querySplitted[5]);

            $documentosInProcessParcial = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                ->listProcessParcial($page, $max, $idregistro, $idcodigomaterial, $idlote, $idcodigoequipo, $idworkordersap, $idcodigodocumento);


        } else {
            $documentosInProcessParcial = array();
        }


        $documentosInProcess = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->listProcess($user_logged);

        foreach ($documents as &$element) {
            $idRegistro = $element['registroid'];
            //var_dump($element['fecha']);
            /*
                        $revisionWorkflow = $this->getDoctrine()
                            ->getRepository('NononsenseHomeBundle:RevisionInstanciaWorkflow')
                            ->findBy(array('status' => 3, "instanciaworkflowid" => $idRegistro));

                        if (!empty($revisionWorkflow)) {
                            $texto = "";
                            foreach ($revisionWorkflow as $revision) {
                                $texto = $texto . $revision->getRevisiontext() . ".";
                            }
                            $element['comentarios'] = $texto;
                        } else {
                            $element['comentarios'] = "";
                        }
            */
            if ($element['checklist'] == 1) {
                $masterStepCheckList = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:MasterSteps')
                    ->findOneBy(array('workflow_id' => $element['masterworkflowid'], 'checklist' => 1));

                $element['checklistName'] = $masterStepCheckList->getName();

            } else {
                $element['checklistName'] = '';
            }

            // Reconciliacion
            $reconciliacionElement = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
                ->findOneBy(array("registro_nuevo_id" => $idRegistro));
            if(isset($reconciliacionElement)){
                $registroReconciliadoId = $reconciliacionElement->getRegistroViejoId();

                $registroReconciliado = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                    ->find($registroReconciliadoId);

                $element['reconciliacion'] = $registroReconciliado->getId();

            }else{
                $element['reconciliacion'] = 'NO';
            }


            /*
                * Posible firma pendiente
                */
            $status = $element['status'];

            if(in_array($status,array(7,12,13,15))){
                // Pendiente de firma o documento o checklist probar con ambos a ver que pasa.
                $pendingStep = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                    ->findOneBy(array("workflow_id" => $idRegistro, "dependsOn" => 0));

                $firma = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:FirmasStep')
                    ->findOneBy(array("step_id" => $pendingStep->getId(), "status" => 0, "userEntiy"=>$user));

                if(isset($firma)){
                    $stepid = $pendingStep->getId();
                    $comment = 1;

                    if($status == 7){
                        $route = $this->container->get('router')->generate('nononsense_registro_verificar', array('stepid' => $stepid, 'comment' => $comment));
                    }elseif ($status == 12){
                        $route = $this->container->get('router')->generate('nononsense_registro_cancelar_verficiacion', array('stepid' => $stepid));
                    }elseif ($status == 13){
                        $route = $this->container->get('router')->generate('nononsense_registro_devolver_edicion', array('stepid' => $stepid));
                    }elseif ($status == 15){
                        $route = $this->container->get('router')->generate('nononsense_registro_verificar_parcial', array('stepid' => $stepid, 'comment' => $comment));
                    }

                    $element['firma'] = $route;
                }else{
                    $element['firma'] = '';
                }

                // Probar con checklist por si acaso
                $pendingchecklistStep = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                    ->findOneBy(array("workflow_id" => $idRegistro, "dependsOn" =>$pendingStep->getId()));

                if(isset($pendingchecklistStep)){
                    // checkear si es checklist
                    $firmacheklist = $this->getDoctrine()
                        ->getRepository('NononsenseHomeBundle:FirmasStep')
                        ->findOneBy(array("step_id" => $pendingchecklistStep->getId(), "status" => 0, "userEntiy"=>$user));
                    if(isset( $firmacheklist )){
                        $stepid = $pendingchecklistStep->getId();
                        $comment = 1;

                        if($status == 7){
                            $route = $this->container->get('router')->generate('nononsense_registro_verificar', array('stepid' => $stepid, 'comment' => $comment));
                        }elseif ($status == 12){
                            $route = $this->container->get('router')->generate('nononsense_registro_cancelar_verficiacion', array('stepid' => $stepid));
                        }elseif ($status == 13){
                            $route = $this->container->get('router')->generate('nononsense_registro_devolver_edicion', array('stepid' => $stepid));
                        }elseif ($status == 15){
                            $route = $this->container->get('router')->generate('nononsense_registro_verificar_parcial', array('stepid' => $stepid, 'comment' => $comment));
                        }

                        $element['firmachecklist'] = $route;
                    }else{
                        $element['firmachecklist'] = '';
                    }


                }else{
                    $element['firmachecklist'] = '';
                }

            }else{
                $element['firma'] = '';
                $element['firmachecklist'] = '';
            }

        }

        foreach ($documentosInProcess as &$element2) {
            $idRegistro = $element2['id'];

            if ($element2['status'] == 6) {
                $revisionWorkflow = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:RevisionInstanciaWorkflow')
                    ->findBy(array('status' => 3, "instanciaworkflowid" => $idRegistro));

                $texto = "";
                foreach ($revisionWorkflow as $revision) {
                    $texto = $texto . $revision->getRevisiontext() . ".";
                }

                $element2['motivo'] = $texto;
            }

            $reconciliacionElement = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
                ->findOneBy(array("registro_nuevo_id" => $idRegistro));

            if(isset($reconciliacionElement)){
                $registroReconciliadoId = $reconciliacionElement->getRegistroViejoId();

                $registroReconciliado = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                    ->find($registroReconciliadoId);

                $element2['reconciliacion'] = $registroReconciliado->getId();

            }else{
                $element2['reconciliacion'] = 'NO';
            }

            /*
             * Posible firma pendiente
             */
            $status = $element2['status'];

            if(in_array($status,array(1,2,3))){
                // Pendiente de firma
                $pendingStep = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                    ->findOneBy(array("workflow_id" => $idRegistro, "dependsOn" => 0));

                $firma = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:FirmasStep')
                    ->findOneBy(array("step_id" => $pendingStep->getId(), "status" => 0, "userEntiy"=>$user));

                if(isset($firma)){
                    $stepid = $pendingStep->getId();
                    $comment = 1;
                    if($status == 1){
                        $route = $this->container->get('router')->generate('nononsense_contrato_registro_completado', array('stepid' => $stepid, 'comment' => $comment));
                    }elseif ($status == 2){
                        $route = $this->container->get('router')->generate('nononsense_contrato_registro_completado', array('stepid' => $stepid, 'comment' => $comment));
                    }elseif ($status == 3){
                        $route = $this->container->get('router')->generate('nononsense_registro_cancelar', array('stepid' => $stepid));
                    }

                    $element2['firma'] = $route;
                }else{
                    $element2['firma'] = '';
                }
            }else{
                $element2['firma'] = '';
            }

        }

        //var_dump($documentosInProcessParcial);
        foreach ($documentosInProcessParcial as &$element3) {
            //echo 'problem';
            $idRegistro = $element3['id'];
            //echo 'problem 2';
            $reconciliacionElement = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
                ->findOneBy(array("registro_nuevo_id" => $idRegistro));

            if(isset($reconciliacionElement)){
                $registroReconciliadoId = $reconciliacionElement->getRegistroViejoId();

                $registroReconciliado = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                    ->find($registroReconciliadoId);

                $element3['reconciliacion'] = $registroReconciliado->getId();

            }else{
                $element3['reconciliacion'] = 'NO';
            }

            /*
 * Posible firma pendiente
 */
            $status = $element3['status'];

            if(in_array($status,array(1,2,3))){
                // Pendiente de firma
                $pendingStep = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                    ->findOneBy(array("workflow_id" => $idRegistro, "dependsOn" => 0));

                $firma = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:FirmasStep')
                    ->findOneBy(array("step_id" => $pendingStep->getId(), "status" => 0, "userEntiy"=>$user));

                if(isset($firma)){
                    $stepid = $pendingStep->getId();
                    $comment = 1;
                    if($status == 1){
                        $route = $this->container->get('router')->generate('nononsense_contrato_registro_completado', array('stepid' => $stepid, 'comment' => $comment));
                    }elseif ($status == 2){
                        $route = $this->container->get('router')->generate('nononsense_contrato_registro_completado', array('stepid' => $stepid, 'comment' => $comment));
                    }elseif ($status == 3){
                        $route = $this->container->get('router')->generate('nononsense_registro_cancelar', array('stepid' => $stepid));
                    }

                    $element3['firma'] = $route;
                }else{
                    $element3['firma'] = '';
                }
            }else{
                $element3['firma'] = '';
            }

        }

        // $documentosInProcessParcial = array();

        $paging = array(
            'page' => $page,
            'path' => 'nononsense_registro_enproceso',
            'count' => max(ceil(sizeof($documentosInProcessParcial) / 10), 1),
            'results' => sizeof($documentosInProcessParcial)
        );
        if ($idregistro == "-1") {
            $idregistro = "";
        }
        if ($idcodigomaterial == "-1") {
            $idcodigomaterial = "";
        }
        if ($idlote == "-1") {
            $idlote = "";
        }
        if ($idcodigoequipo == "-1") {
            $idcodigoequipo = "";
        }
        if ($idworkordersap == "-1") {
            $idworkordersap = "";
        }
        if ($idcodigodocumento == "-1") {
            $idcodigodocumento = "";
        }

        return $this->render('NononsenseHomeBundle:Contratos:registro_procesed.html.twig', array(
            "documents" => $documents,
            "documentsProcess" => $documentosInProcess,
            "documentsProcessParcial" => $documentosInProcessParcial,
            "query" => $query,
            'paging' => $paging,
            "idregistro" => $idregistro,
            "codigomaterial" => $idcodigomaterial,
            "lote" => $idlote,
            "codigoequipo" => $idcodigoequipo,
            "workordersap" => $idworkordersap,
            "codigodocumento" => $idcodigodocumento
        ));
    }

    public function validarAction($revisionid)
    {
        $revisionWorkflow = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RevisionInstanciaWorkflow')
            ->find($revisionid);

        $registro = $revisionWorkflow->getInstanciaWorkflowEntity();

        $firstStep = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registro->getId(), "dependsOn" => 0));

        $validation = $firstStep->getMasterStep()->getValidation();
        if ($validation != "self") {
            $firstStep = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                ->findOneBy(array("workflow_id" => $registro->getId(), "master_step_id" => $validation));
        }

        $route = $this->container->get('router')->generate('nononsense_registro_concreto_link', array("stepid" => $firstStep->getId(), "form" => 0, "revisionid" => $revisionid));
        return $this->redirect($route);
    }

    public function verAction($revisionid)
    {
        /*
         * Usar link pero modificado para que no se pueda modificar nada y con un "functions" diferente.
         */

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($revisionid);

        //$registro = $revisionWorkflow->getInstanciaWorkflowEntity();

        $firstStep = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registro->getId(), "dependsOn" => 0));

        //$baseUrl = $this->getParameter("cm_installation");

        //$options = array();

        //$options['template'] = $firstStep->getMasterStep()->getPlantillaId();

        //$url_requesetData = $baseUrl . 'data/requestData/' . $firstStep->getId();

        //$options['requestDataURI'] = $url_requesetData;
        //$options['token'] = $firstStep->getToken();

        //$url_edit_documento = $this->get('app.sdk')->viewDocument($options);
        $route = $this->container->get('router')->generate('nononsense_registro_concreto_link', array("stepid" => $firstStep->getId(), "form" => 0, "revisionid" => $revisionid));
        return $this->redirect($route);
    }

    public function verChecklistAction($revisionid)
    {
        /*
         * Usar link pero modificado para que no se pueda modificar nada y con un "functions" diferente.
         * Cómo es el checklist debería abrirse incluso con un "functions" diferente sólo con las opciones de:
         * cerrar, guardar, guardar y enviar.
         */

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($revisionid);

        $stepsList = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findBy(array("workflow_id" => $registro->getId()));

        $stepCheckList = null;

        foreach ($stepsList as $oneStep) {
            if ($oneStep->getMasterStep()->getChecklist() == 1) {
                $stepCheckList = $oneStep;
            }
        }

        if ($stepCheckList != null) {
            $route = $this->container->get('router')->generate('nononsense_registro_concreto_link', array("stepid" => $stepCheckList->getId(), "form" => 0, "revisionid" => $revisionid));
            return $this->redirect($route);
        } else {
            echo 'Se ha producido un error inesperado';
            exit;
        }

    }

    /*
     * Creo que este método no se usa, debería borrarle
     * Le dejo comentado para saber si debo borrarlo o no
     */
    /*
    public function validarOkAction($stepid)
    {
        $em = $this->getDoctrine()->getManager();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        /*
         * Si tiene una checklist, no se debería marcar cómo válido el registro. Aún.
         *
        $registro = $step->getInstanciaWorkflow();
        if($registro->getMasterWorkflowEntity()->getChecklist() == 0){
            $registro->setStatus(9); // Estado archivado.
        }

/*
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(1);
        $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
        $evidencia->setToken($step->getToken());
*/
    /*
     * Desbloquear en caso de que este registro generase bloqueo
     *

    $bloqueo = $this->getDoctrine()
        ->getRepository('NononsenseHomeBundle:BloqueoMasterWorkflow')
        ->findOneBy(array("master_workflow_id" => $registro->getMasterWorkflowEntity()->getId(), "status" => 0, "registro_id" => $registro->getId()));

    if ($bloqueo != null) {
        $bloqueo->setStatus(1);
        $em->persist($bloqueo);
    }


    //$em->persist($evidencia);
    $em->flush();

    return $this->render('NononsenseHomeBundle:Contratos:registro_validado.html.twig', array());
}
*/

    public function devolverInterfaceAction($revisionid)
    {
        /*
         * Modificación del flujo, ponerle un valor especial y hacer el link.
         */

        $revisionWorkflow = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RevisionInstanciaWorkflow')
            ->find($revisionid);

        $documentName = $revisionWorkflow->getInstanciaWorkflowEntity()->getMasterWorkflowEntity()->getName();

        $revisionWorkflow = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RevisionInstanciaWorkflow')
            ->find($revisionid);

        $registro = $revisionWorkflow->getInstanciaWorkflowEntity();

        $firstStep = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registro->getId(), "dependsOn" => 0));

        //      $firstStep->setStatusId(4); // Devolver

        $em = $this->getDoctrine()->getManager();
        $em->persist($firstStep);
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_registro_concreto_link', array("stepid" => $firstStep->getId(), "form" => 0, 'revisionid' => $revisionid));
        return $this->redirect($route);
        /*
                return $this->render('NononsenseHomeBundle:Contratos:registro_devolver_edicion_interface.html.twig', array(
                    "documentName" => $documentName,
                    "revisionid" => $revisionid));
        */
    }

    public function devolverAction($revisionid, request $request)
    {
        $revisionWorkflow = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RevisionInstanciaWorkflow')
            ->find($revisionid);

        //$comentario = $request->query->get('comment');
        $comentario = 'ya está en la interfaz';
        $revisionWorkflow->setStatus(3);
        $revisionWorkflow->setRevisiontext($comentario);

        $registro = $revisionWorkflow->getInstanciaWorkflowEntity();

        $registro->setStatus(6);
        /*
         * Modificar el metadacompletado por devolución
         */
        /*
        $metaData = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:MetaData')
            ->findOneBy(array("workflow_id" => $registro->getId(), "dataname" => 'Fecha Registro Completado'));

        $metaData->setDataname('Fecha registro completado devuelto para edicion');
*/
        $em = $this->getDoctrine()->getManager();
        $em->persist($revisionWorkflow);
        $em->persist($registro);
        //      $em->persist($metaData);
        $em->flush();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registro->getId(), "dependsOn" => 0));
        /*
                $evidencia = new EvidenciasStep();
                $evidencia->setStepEntity($step);
                $evidencia->setStatus(3);
                $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
                $evidencia->setToken($step->getToken());
        */
        //      $em->persist($evidencia);
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_home_homepage');
        return $this->redirect($route);
    }

    public function cancelarRevisionInterfaceAction($revisionid)
    {

        /*
      * Modificación del flujo, ponerle un valor especial y hacer el link.
      */

        $revisionWorkflow = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RevisionInstanciaWorkflow')
            ->find($revisionid);

        $registro = $revisionWorkflow->getInstanciaWorkflowEntity();


        $firstStep = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registro->getId(), "dependsOn" => 0));

        //$firstStep->setStatusId(5); // Cancelar

        $em = $this->getDoctrine()->getManager();
        $em->persist($revisionWorkflow);
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_registro_concreto_link', array("stepid" => $firstStep->getId(), "form" => 0, 'revisionid' => $revisionid));
        return $this->redirect($route);
        /*
        $revisionWorkflow = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RevisionInstanciaWorkflow')
            ->find($revisionid);

        $documentName = $revisionWorkflow->getInstanciaWorkflowEntity()->getMasterWorkflowEntity()->getName();

        return $this->render('NononsenseHomeBundle:Contratos:registro_cancelar_edicion_interface.html.twig', array("documentName" => $documentName,
            "revisionid" => $revisionid));
        */
    }

    public function cancelarRevisionAction($revisionid, request $request)
    {


        $revisionWorkflow = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RevisionInstanciaWorkflow')
            ->find($revisionid);

        //$comentario = $request->query->get('comment');
        $comentario = "esta en la interfaz";
        $revisionWorkflow->setStatus(2);
        $revisionWorkflow->setRevisiontext($comentario);

        $registro = $revisionWorkflow->getInstanciaWorkflowEntity();

        $registro->setStatus(5);

        $em = $this->getDoctrine()->getManager();
        $em->persist($revisionWorkflow);
        $em->persist($registro);
        $em->flush();

        /*
         * Crear validación de FLL o responsable de área.
         * Asociada a un grupo específico.
         */
        $user = $registro->getUserCreatedEntiy();
        $type = 5;

        //Detectar grupo BASE del creador y asignar la revision a su superior.
        foreach ($user->getGroups() as $groupMe) {
            if ($groupMe->getGroup()->getTipo() == 'base') {
                $type = $groupMe->getGroup()->getSuperior();
            }
        }
        $revisionInstanciaWorkflowEntityAux = new RevisionInstanciaWorkflow();
        $revisionInstanciaWorkflowEntityAux->setStatus(0);

        $revisionInstanciaWorkflowEntityAux->setRevisiontext("Esta es la validacion de FLL especial");
        $revisionInstanciaWorkflowEntityAux->setInstanciaWorkflowEntity($registro);

        $revisionInstanciaWorkflowEntityAux->setType($type);
        $em->persist($revisionInstanciaWorkflowEntityAux);

        $em->flush();
        $route = $this->container->get('router')->generate('nononsense_home_homepage');
        $baseURL = $this->container->getParameter('cm_installation');
        /*
         * Enviar email avisando
         */
        $groups = $this->getDoctrine()
            ->getRepository('NononsenseGroupBundle:Groups')
            ->findOneBy(array("id" => $type));

        $groupUsers = $groups->getUsers();
        $enviosUser = array();
        foreach ($groupUsers as $element) {
            $users = $element->getUser();

            //var_dump($users->getId());
            $enviosUser[] = $users->getEmail();

        }
        $subject = "Pendiente revision FLL";
        $mensaje = "Tiene un documento pendiente de verificar cancelación por parte de FLL";
        $arrayEnviosHechos = array();
        foreach ($enviosUser as $email) {
            if ($this->_sendNotification($email, $baseURL, "", "", $subject, $mensaje)) {
                $arrayEnviosHechos[] = 'envio correcto de ' . $email;
            } else {
                $arrayEnviosHechos[] = 'envio erroneo de ' . $email;
            }
        }

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registro->getId(), "dependsOn" => 0));
        /*
                $evidencia = new EvidenciasStep();
                $evidencia->setStepEntity($step);
                $evidencia->setStatus(6);
                $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
                $evidencia->setToken($step->getToken());
        */
//        $em->persist($evidencia);
        $em->flush();

        return $this->redirect($route);

    }

    public function completarAction($registroid, Request $request)
    {
        /*
         * Localizar el step completado.
         * Asignar el estado 3. Temporal. Pero si no se ha abierto nunca (estado 0) no poner en 3.
         * Abrir con link como siempre
         */
        if ($registroid == 0) {
            $logbook = $request->query->get('logbook');
            $registroid = $request->query->get('registroidForm');
        }


        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($registroid);

        $pendingStep = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registro->getId(), "status_id" => array(1, 3, 0), "dependsOn" => 0));
        /*
                if ($pendingStep->getStatusId() == 0) {

                } else {
                    $pendingStep->setStatusId(3);
                }
        */

        $em = $this->getDoctrine()->getManager();
        $em->persist($pendingStep);
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_registro_concreto_link', array("stepid" => $pendingStep->getId(), "form" => 0, "revisionid" => 0, "logbook" => $logbook));
        return $this->redirect($route);
    }

    public function cancelarInterfaceAction($stepid)
    {

        /*
         * Interfaz de cancelación
         */
        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $documentName = $step->getMasterStep()->getName();

        return $this->render('NononsenseHomeBundle:Contratos:registro_cancelar_interface.html.twig', array(
            "documentName" => $documentName,
            "stepid" => $stepid
        ));
    }

    public function devolverEdicionInterfaceAction($stepid)
    {
        /*
         * Interfaz de verificacion
         */
        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $documentName = $step->getMasterStep()->getName();

        return $this->render('NononsenseHomeBundle:Contratos:registro_verificar_devolver_edicion.html.twig', array(
            "documentName" => $documentName,
            "stepid" => $stepid
        ));
    }

    public function cancelarVerificarInterfaceAction($stepid)
    {
        /*
         * Interfaz de verificacion
         */
        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $documentName = $step->getMasterStep()->getName();

        return $this->render('NononsenseHomeBundle:Contratos:registro_verificar_ko_interface.html.twig', array(
            "documentName" => $documentName,
            "stepid" => $stepid,
        ));
    }


    public function verificarInterfaceAction($stepid, $comment)
    {

        /*
         * Interfaz de verificacion
         */
        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $documentName = $step->getMasterStep()->getName();


        return $this->render('NononsenseHomeBundle:Contratos:registro_verificar_ok_interface.html.twig', array(
            "documentName" => $documentName,
            "stepid" => $stepid,
            "comment" => $comment
        ));
    }

    public function verificarParcialInterfaceAction($stepid, $comment)
    {

        /*
         * Interfaz de verificacion
         */
        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $documentName = $step->getMasterStep()->getName();

        return $this->render('NononsenseHomeBundle:Contratos:registro_verificar_parcial_interface.html.twig', array(
            "documentName" => $documentName,
            "stepid" => $stepid,
            "comment" => $comment
        ));
    }

    public function verificarOkStepAction($stepid, Request $request)
    {

        $user = $this->container->get('security.context')->getToken()->getUser();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $comentario = $request->query->get('comment');
        $firmaImagen = $request->query->get('firma');

        $documentName = $step->getMasterStep()->getName();

        $registro = $step->getInstanciaWorkflow();
        if ($registro->getMasterWorkflowEntity()->getChecklist() == 0) {
            $registro->setStatus(9); // Estado archivado.

        } else {
            if ($step->getMasterStep()->getChecklist() == 1) {
                // Yo soy la checklist
                $registro->setStatus(9); // Estado archivado.
            } else {
                $registro->setStatus(4); // Sigue en estado en validación
            }
        }

        //$step->setStatusId(4);

        $em = $this->getDoctrine()->getManager();
        $em->persist($step);
        $em->persist($registro);
        $em->flush();

        /*
         * Guardar evidencia de registro verificado
         *
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(1);
        $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());
*/

        /*
         * Obtener firma pendiente y firmar
         */
        $firma = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findOneBy(array("step_id" => $step->getId(), "status" => 0, "userEntiy" => $user));

        if (isset($firma)) {
            $firma->setFirma($firmaImagen);
            $firma->setAccion("Verificación positiva: " . $comentario);
            $firma->setStatus(1); // Firmado

        } else {
            // No debería estar aquí
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene firma pendiente para este paso.'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }
/*
        $counter = count($firmas) + 1;

        $firma = new FirmasStep();
        $firma->setAccion("Verificación positiva: " . $comentario);
        $firma->setStepEntity($step);
        $firma->setUserEntiy($user);
        $firma->setFirma($firmaImagen);
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);
*/
        $em->persist($firma);
//        $em->persist($evidencia);
        $em->flush();

        return $this->render('NononsenseHomeBundle:Contratos:registro_validado.html.twig', array(
            "documentName" => $documentName,
            "stepid" => $stepid
        ));

    }

    public function verificarParcialStepAction($stepid, Request $request)
    {

        $user = $this->container->get('security.context')->getToken()->getUser();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $comentario = $request->query->get('comment');
        $firmaImagen = $request->query->get('firma');

        $documentName = $step->getMasterStep()->getName();

        $registro = $step->getInstanciaWorkflow();
        $registro->setStatus(4);

        //$step->setStatusId(4);

        $em = $this->getDoctrine()->getManager();
        $em->persist($step);
        $em->persist($registro);
        $em->flush();

        /*
         * Guardar evidencia de registro verificado
         *
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(1);
        $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());
*/

        /*
         * Obtener firma pendiente y firmar
         */
        $firma = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findOneBy(array("step_id" => $step->getId(), "status" => 0, "userEntiy" => $user));

        if (isset($firma)) {
            $firma->setFirma($firmaImagen);
            $firma->setAccion("Verificación parcial: " . $comentario);
            $firma->setStatus(1); // Firmado

        } else {
            // No debería estar aquí
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene firma pendiente para este paso.'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }
/*
        $counter = count($firmas) + 1;

        $firma = new FirmasStep();
        $firma->setAccion("Verificación parcial: " . $comentario);
        $firma->setStepEntity($step);
        $firma->setUserEntiy($user);
        $firma->setFirma($firmaImagen);
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);
*/
        $em->persist($firma);
//        $em->persist($evidencia);
        $em->flush();

        return $this->render('NononsenseHomeBundle:Contratos:registro_validado_parcial.html.twig', array(
            "documentName" => $documentName,
            "stepid" => $stepid
        ));

    }

    public function verificarKoStepAction($stepid, Request $request)
    {

        $user = $this->container->get('security.context')->getToken()->getUser();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $comentario = $request->query->get('comment');
        $firmaImagen = $request->query->get('firma');

        $documentName = $step->getMasterStep()->getName();

        $registro = $step->getInstanciaWorkflow();
        $registro->setStatus(14);

        //$step->setStatusId(4);

        $em = $this->getDoctrine()->getManager();
        $em->persist($step);
        $em->persist($registro);
        $em->flush();

        /*
         * Guardar evidencia de registro verificado
         *
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(1);
        $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());
        */
        /*
         * Guardar firma
         */
        /*
         * Obtener firma pendiente y firmar
         */
        $firma = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findOneBy(array("step_id" => $step->getId(), "status" => 0, "userEntiy" => $user));

        if (isset($firma)) {
            $firma->setFirma($firmaImagen);
            $firma->setAccion("Verificación negativa: " . $comentario);
            $firma->setStatus(1); // Firmado

        } else {
            // No debería estar aquí
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene firma pendiente para este paso.'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }
/*
        $counter = count($firmas) + 1;

        $firma = new FirmasStep();
        $firma->setAccion("Verificación negativa: " . $comentario);
        $firma->setStepEntity($step);
        $firma->setUserEntiy($user);
        $firma->setFirma($firmaImagen);
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);
*/
        $em->persist($firma);
//        $em->persist($evidencia);
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_registro_enproceso');

        return $this->redirect($route);

    }

    public function verificarDevolverEdicionAction($stepid, Request $request)
    {

        $user = $this->container->get('security.context')->getToken()->getUser();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $comentario = $request->query->get('comment');
        $firmaImagen = $request->query->get('firma');

        $documentName = $step->getMasterStep()->getName();

        $registro = $step->getInstanciaWorkflow();
        $registro->setStatus(0);

        //$step->setStatusId(4);

        $em = $this->getDoctrine()->getManager();
        $em->persist($step);
        $em->persist($registro);
        $em->flush();

        /*
         * Guardar evidencia de registro verificado
         *
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(1);
        $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());
*/
        /*
         * Guardar firma
         */
        /*
         * Obtener firma pendiente y firmar
         */
        $firma = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findOneBy(array("step_id" => $step->getId(), "status" => 0, "userEntiy" => $user));

        if (isset($firma)) {
            $firma->setFirma($firmaImagen);
            $firma->setAccion("Devuelto para edición: " . $comentario);
            $firma->setStatus(1); // Firmado

        } else {
            // No debería estar aquí
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene firma pendiente para este paso.'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }


/*
        $firma = new FirmasStep();
        $firma->setAccion("Devuelto para edición: " . $comentario);
        $firma->setStepEntity($step);
        $firma->setUserEntiy($user);
        $firma->setFirma($firmaImagen);
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);
*/
        $em->persist($firma);
  //      $em->persist($evidencia);
        $em->flush();

        /*
         * Envío por email a creador del documento
         */
        $emailTo = $registro->getUserCreatedEntiy()->getEmail();
        $baseUrl = $this->getParameter("cm_installation");
        $linkToEnProcess = $baseUrl . "registro_process";
        $logo = "";
        $accion = "devolverEdicion";
        $subject = "Registro devuelto para edición";
        $message = "El registro con Id: " . $registro->getId() . " ha sido devuelto para edición, por favor revíselo o notifique a otro usuario para que revise dicha cumplimentación";


        $this->_sendNotification($emailTo, $linkToEnProcess, $logo, $accion, $subject, $message);


        $route = $this->container->get('router')->generate('nononsense_registro_enproceso');

        return $this->redirect($route);

    }

    public function notificacionSemanalPendienteVerificarAction()
    {

        $documents = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->findBy(array("status" => 4));

        $arrayIdPendientes = array();
        foreach ($documents as $oneDocument) {
            $arrayIdPendientes[] = $oneDocument->getId();
        }
        $idPendientesString = implode(",", $arrayIdPendientes);

        /*
         * En producción habría que hacerlo por areas ahora sólo va a haber un grupo
         */
        $groups = $this->getDoctrine()
            ->getRepository('NononsenseGroupBundle:Groups')
            ->findOneBy(array("id" => 11));

        $groupUsers = $groups->getUsers();
        $enviosUser = array();
        foreach ($groupUsers as $element) {
            $users = $element->getUser();
            $enviosUser[] = $users->getEmail();
        }

        $subject = "Documento pendiente de verificar";
        $mensaje = "Los siguientes registros: " . $idPendientesString . " están pendientes de verificar en el sistema";
        $arrayEnviosHechos = array();

        $baseUrl = $this->getParameter("cm_installation");
        $linkToEnProcess = $baseUrl . "registro_process";

        foreach ($enviosUser as $email) {
            if ($this->_sendNotification($email, $linkToEnProcess, "", "", $subject, $mensaje)) {
                $arrayEnviosHechos[] = 'envio correcto de ' . $email;
            } else {
                $arrayEnviosHechos[] = 'envio erroneo de ' . $email;
            }
        }


        $responseAction = new Response();
        $responseAction->setStatusCode(200);
        $responseAction->setContent("OK");
        return $responseAction;

    }

    /*
     * Muestra la interfaz de firma para una rechazar una cancelación
     */
    public function rechazarCancelacionFirmaAction($stepid)
    {
        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $documentName = $step->getMasterStep()->getName();

        return $this->render('NononsenseHomeBundle:Contratos:registro_rechazar_cancelacion_firma.html.twig', array(
            "documentName" => $documentName,
            "stepid" => $stepid
        ));

    }

    /*
     * Interfaz de cancelacion rechazada y método que registra firma y datos
     */
    public function cancelacionRechazadaAction($stepid, Request $request)
    {

        $user = $this->container->get('security.context')->getToken()->getUser();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $comentario = $request->query->get('comment');
        $firmaImagen = $request->query->get('firma');

        $documentName = $step->getMasterStep()->getName();

        $registro = $step->getInstanciaWorkflow();

        if ($registro->getStatus() == 5) {
            $registro->setStatus(0);

        } elseif ($registro->getStatus() == 14) {
            $registro->setStatus(4);

        }


        $em = $this->getDoctrine()->getManager();
        $em->persist($step);
        $em->persist($registro);
        $em->flush();

        /*
         * Guardar evidencia de cabcekacuib rechazada
         *
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(1);
        $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());
*/
        /*
         * Guardar firma
         */
        /*
         * Obtener firma pendiente y firmar
         */
        $firma = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findOneBy(array("step_id" => $step->getId(), "status" => 0, "userEntiy" => $user));

        if (isset($firma)) {
            $firma->setFirma($firmaImagen);
            $firma->setAccion("Rechazada la cancelación: " . $comentario);
            $firma->setStatus(1); // Firmado

        } else {
            // No debería estar aquí
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene firma pendiente para este paso.'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }
/*
        $counter = count($firmas) + 1;

        $firma = new FirmasStep();
        $firma->setAccion("Rechazada la cancelación: " . $comentario);
        $firma->setStepEntity($step);
        $firma->setUserEntiy($user);
        $firma->setFirma($firmaImagen);
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);
*/
        $em->persist($firma);
//        $em->persist($evidencia);
        $em->flush();

        return $this->render('NononsenseHomeBundle:Contratos:registro_cancelacion_rechazada.html.twig', array(
            "documentName" => $documentName,
            "stepid" => $stepid
        ));

    }

    /*
     * Muestra la interfaz de firma para aprobar una cancelación
     */
    public function aprobarCancelacionFirmaAction($stepid)
    {
        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $documentName = $step->getMasterStep()->getName();

        return $this->render('NononsenseHomeBundle:Contratos:registro_aprobar_cancelacion_firma.html.twig', array(
            "documentName" => $documentName,
            "stepid" => $stepid
        ));
    }

    /*
     * Interfaz de cancelación aprobada y registro de datos y firma
     */
    public function cancelacionAprobadaAction($stepid, Request $request)
    {

        $user = $this->container->get('security.context')->getToken()->getUser();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $comentario = $request->query->get('comment');
        $firmaImagen = $request->query->get('firma');

        $documentName = $step->getMasterStep()->getName();

        $registro = $step->getInstanciaWorkflow();

        if ($registro->getStatus() == 5) {
            $registro->setStatus(6);

        } elseif ($registro->getStatus() == 14) {
            $registro->setStatus(8);

        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($step);
        $em->persist($registro);
        $em->flush();

        /*
         * Guardar evidencia de cabcekacuib rechazada
         *
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(1);
        $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());
*/

        /*
         * Obtener firma pendiente y firmar
         */
        $firma = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findOneBy(array("step_id" => $step->getId(), "status" => 0, "userEntiy" => $user));

        if (isset($firma)) {
            $firma->setFirma($firmaImagen);
            $firma->setAccion("Aprobada la cancelación: " . $comentario);
            $firma->setStatus(1); // Firmado

        } else {
            // No debería estar aquí
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene firma pendiente para este paso.'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }
/*
        $counter = count($firmas) + 1;

        $firma = new FirmasStep();
        $firma->setAccion("Aprobada la cancelación: " . $comentario);
        $firma->setStepEntity($step);
        $firma->setUserEntiy($user);
        $firma->setFirma($firmaImagen);
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);
*/
        $em->persist($firma);
//        $em->persist($evidencia);
        $em->flush();

        return $this->render('NononsenseHomeBundle:Contratos:registro_cancelacion_aprobada.html.twig', array(
            "documentName" => $documentName,
            "stepid" => $stepid
        ));
    }

    public function cancelarStepAction($stepid, Request $request)
    {
        /*
         * Crear un objeto cancelarRegistro con el texto correspondiente.
         * El workflow y el step id poner en modo cancelado (workflow 5 y step 4)
         */
        $user = $this->container->get('security.context')->getToken()->getUser();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        //$cancelacionStep = new CancelacionStep();
        //$cancelacionStep->setStatus(0);
        //$cancelacionStep->setStep($step);

        $comentario = $request->query->get('comment');
        $firmaImagen = $request->query->get('firma');
        //$cancelacionStep->setRevisiontext($comentario);

        $documentName = $step->getMasterStep()->getName();

        $registro = $step->getInstanciaWorkflow();
        $registro->setStatus(5);

//        $step->setStatusId(4);

        $em = $this->getDoctrine()->getManager();
        $em->persist($step);
        $em->persist($registro);
        //$em->persist($cancelacionStep);
        $em->flush();

        /*
         * Guardar evidencia de registro completado pero cancelado en edicion
         *
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(5);
        $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());
*/
        /*
         * Guardar firma
         */
        $firma = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findOneBy(array("step_id" => $step->getId(), "status" => 0, "userEntiy"=>$user));

        if(isset($firma)){
            $firma->setFirma($firmaImagen);
            $firma->setAccion("Solicitud cancelacion: " . $comentario);
            $firma->setStatus(1); // Firmado

        }else{
            // No debería estar aquí
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene firma pendiente para este paso.'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }
/*
        $counter = count($firmas) + 1;

        $firma = new FirmasStep();
        $firma->setAccion("Solicitud cancelacion: " . $comentario);
        $firma->setStepEntity($step);
        $firma->setUserEntiy($user);
        $firma->setFirma($firmaImagen);
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);
*/
        $em->persist($firma);
        //$em->persist($evidencia);
        $em->flush();

        return $this->render('NononsenseHomeBundle:Contratos:registro_canceladoenviado.html.twig', array(
            "documentName" => $documentName,
            "stepid" => $stepid
        ));

    }

    private function _sendNotification($mailTo, $link, $logo, $accion, $subject, $message)
    {
        $mailLogger = new \Swift_Plugins_Loggers_ArrayLogger();
        $this->get('mailer')->registerPlugin(new \Swift_Plugins_LoggerPlugin($mailLogger));
        $email = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->container->getParameter('mailer_user'))
            ->setTo($mailTo)
            ->setBody(
                $this->renderView(
                    'NononsenseHomeBundle:Email:notificationUser.html.twig', array(
                    'logo' => $logo,
                    'accion' => $accion,
                    'message' => $message,
                    'link' => $link
                )),
                'text/html'
            );
        if ($this->get('mailer')->send($email)) {
            //echo '[SWIFTMAILER] sent email to ' . $mailTo;
            //echo 'LOG: ' . $mailLogger->dump();
            return true;
        } else {
            //echo '[SWIFTMAILER] not sending email: ' . $mailLogger->dump();
            return false;
        }

    }

}