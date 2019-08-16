<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 03/04/2018
 * Time: 19:44
 */

namespace Nononsense\HomeBundle\Controller;


use Nononsense\HomeBundle\Entity\BloqueoMasterWorkflow;
use Nononsense\HomeBundle\Entity\EvidenciasStep;
use Nononsense\HomeBundle\Entity\FirmasStep;
use Nononsense\HomeBundle\Entity\MetaData;
use Nononsense\HomeBundle\Entity\MetaFirmantes;
use Nononsense\HomeBundle\Entity\Revision;
use Nononsense\HomeBundle\Entity\RevisionInstanciaWorkflow;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\HomeBundle\Entity\InstanciasWorkflows;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasSteps;

use Nononsense\UtilsBundle\Classes;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class RegistroConcretoController extends Controller
{
    public function registroCompletadoAction($stepid, $comment)
    {
        // si el registro se ha validado no mostrar esta interfaz sino otra.

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $documentName = $step->getMasterStep()->getName();

        $stepData = $step->getStepDataValue();
        $stepDataJSON = json_decode($stepData);

        $validations = $stepDataJSON->validations;
        $percentageCompleted = $validations->percentage;
        $validated = $validations->validated;

        /*
         * Revisar si ha habido algún cambio en las variables para que muestre el campo de texto.
         */
        $devolucion = 0;
        if ($this->_checkModifyVariables($step)) {
            $devolucion = 1;
        }
        if ($comment == 1) {
            $devolucion = 1;
        }


        return $this->render('NononsenseHomeBundle:Contratos:registro_completado.html.twig', array(
            "documentName" => $documentName,
            "percentageCompleted" => $percentageCompleted,
            "validated" => $validated,
            "stepid" => $stepid,
            "devolucion" => $devolucion
        ));
    }

    public function verStepAction($stepid)
    {
        //$dataResponse = new \stdClass();
        //$dataResponse->workflow_id = $registroid;


        $firststep = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $options = array();

        $options['template'] = $firststep->getMasterStep()->getPlantillaId();
        $options['token'] = $firststep->getToken();

        $url_edit_documento = $this->get('app.sdk')->viewDocument($options);
        return $this->redirect($url_edit_documento);
    }

    public function linkAction($stepid, $form, $revisionid, $logbook)
    {
        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $registro = $step->getInstanciaWorkflow();
        /*
        if ($step->getStatusId() == 2 && $registro->getStatus() != 10 ) {
            // ya validado, en realidad es como algo raro esto porque en la instalación de gus no funciona pero seguro que en las otras si

            $route = $this->container->get('router')->generate('nononsense_ver_step', array("stepid" => $stepid));
            return $this->redirect($route);
            /*
            $this->get('session')->getFlashBag()->add(
                'error',
                'No puede ver este registro aquí porque ya ha sido validado'
            );
            $route = $this->container->get('router')->generate('nononsense_registro_enproceso');
            return $this->redirect($route);
            *

        }
        */
        $baseUrl = $this->getParameter("cm_installation");

        $options = array();

        $options['template'] = $step->getMasterStep()->getPlantillaId();
        /*
        $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/activity.js");
        $validacionURL1 = $baseUrl . "js/js_templates/activity.js?v=" . $versionJS;
        */

        $validacionURL2 = $baseUrl . "js/js_templates/pesos.js";

        $validacionURL1 = '';
        $validacionURL2 = '';

        /*
         * Custom variable:
         */
        $customObject = new \stdClass();
        $customObject->activate = 'deactivate'; // default En caso de haber precarga de datos poner en activate (gestionar según el status...)
        $customObject->sessionTime = '1200'; // In seconds
        $customObject->sessionLocation = 'http://gsk.docxpresso.org/';// Dónde redirigir para el logout


        /*
         * Saber si hay algún precreation
         */
        $precreationValue = $registro->getMasterWorkflowEntity()->getPrecreation();
        if ($precreationValue != "default") {
            $customObject->activate = 'activate';
        }
        $options['custom'] = json_encode($customObject);

        if ($step->getMasterStep()->getChecklist() == 1) {

            $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/activity.js");
            $validacionURL1 = $baseUrl . "js/js_templates/activity.js?v=" . $versionJS;

            $options['prefix'] = 'u';
            $options['responseURL'] = $baseUrl . "control_check_list/" . $stepid . "/";


        } else {


            if ($registro->getStatus() == 4) {
                // Abrir para validar
                $options['responseURL'] = $baseUrl . "control_validacion/" . $stepid . "/";
                $options['prefix'] = 'verchk';

                $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/validacion.js");
                $validacionURL1 = $baseUrl . "js/js_templates/validacion.js?v=" . $versionJS;

            } else if ($registro->getStatus() == -1 ||
                $registro->getStatus() == 0) {
                // abrir para editar
                $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/activity.js");
                $validacionURL1 = $baseUrl . "js/js_templates/activity.js?v=" . $versionJS;

                $options['prefix'] = 'u';
                $options['responseURL'] = $baseUrl . "control_elaboracion/" . $stepid . "/";


            } else if ($registro->getStatus() == 5 || $registro->getStatus() == 14) {
                // Flujos de cancelación
                $options['prefix'] = 'show';
                $options['responseURL'] = $baseUrl . "control_cancelacion/" . $stepid . "/";


                $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/cancelacion.js");
                $validacionURL1 = $baseUrl . "js/js_templates/cancelacion.js?v=" . $versionJS;

            } else {

                // No abrir para editar ... usar el método show
                $options['prefix'] = 'show';
                $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/show.js");
                $validacionURL1 = $baseUrl . "js/js_templates/show.js?v=" . $versionJS;
            }
        }


        /*
         * Caso especial de firma FLL
         */
        $revisionWorkflow = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RevisionInstanciaWorkflow')
            ->find($revisionid);


        if ($validacionURL2 != "") {
            $options['requestExternalJS'] = $validacionURL1 . ";" . $validacionURL2 . "?v=" . time();
        } else {
            $options['requestExternalJS'] = $validacionURL1;
        }


        $options['requestExternalJS'] = $validacionURL1;
        $url_resp_data_uri = $baseUrl . 'data/get_data_from_document/' . $stepid;
        $url_requesetData = $baseUrl . 'data/requestData/' . $step->getId() . '/' . $logbook;

        $options['responseDataURI'] = $url_resp_data_uri;
        $options['requestDataURI'] = $url_requesetData;


        $options['enduserid'] = 'pruebadeusuario: ' . $this->getUser()->getName();

        $url_edit_documento = $this->get('app.sdk')->previewDocument($options);

        /*
         * Bloquear el registro
         */
        //$registro->setInEdition(1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($registro);
        $em->flush();


        return $this->redirect($url_edit_documento);
    }

    public function saveAction($stepid, Request $request)
    {
        /*
         * Guardar firma
         */

        $user = $this->container->get('security.context')->getToken()->getUser();
        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $em = $this->getDoctrine()->getManager();

        $registro = $step->getInstanciaWorkflow();

        if ($step->getMasterStep()->getChecklist() == 0) {
            $registro->setStatus(0);
        }

        $em->persist($registro);
        $em->flush();

        /*
        * Guardar evidencia de registro completado
        */
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(0);
        $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());


        $firmaImagen = $request->query->get('firma');
        $comentario = $request->query->get('comment');

        if (!empty($comentario)) {
            $descp = "Guardado parcial. " . $comentario;

        } else {
            $descp = "Guardado parcial";
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
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);


        $em->persist($evidencia);
        $em->persist($firma);
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_registro_enproceso');
        return $this->redirect($route);

    }

    public
    function saveAndSendAction($stepid, Request $request)
    {
        $comentario = $request->query->get('comment');

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $em = $this->getDoctrine()->getManager();

        $registro = $step->getInstanciaWorkflow();
        $registro->setStatus(4);

        if (isset($comentario)) {
            // Devolución
            $revisionInstanciaWorkflowEntityAux = new RevisionInstanciaWorkflow();
            $revisionInstanciaWorkflowEntityAux->setStatus(3);

            $revisionInstanciaWorkflowEntityAux->setRevisiontext($comentario);
            $revisionInstanciaWorkflowEntityAux->setInstanciaWorkflowEntity($registro);

            $revisionInstanciaWorkflowEntityAux->setType(1);
            $em->persist($revisionInstanciaWorkflowEntityAux);
        }
        /*
         * Crea la validación, asignar grupo si es específico.
         */
        $validationType = $registro->getMasterWorkflowEntity()->getValidation();
        // En type va el valor del grupo del usuario de creación.

        $user = $this->container->get('security.context')->getToken()->getUser();

        // Sólo debería tener uno ...
        foreach ($user->getGroups() as $groupMe) {
            $type = $groupMe->getGroup()->getId();
        }


        switch ($validationType) {
            case "FLL":
                $type = 5;
                $registro->setStatus(8);
                break;
            case "mantenimiento":
                /*
                 * Si valor mantenimineto en el datos del step el type es 2, sino es 1
                 * varValues
                 */
                $data = $step->getStepDataValue();
                $dataJson = json_decode($data);

                $u_accion = $dataJson->varValues->u_accion;
                if (in_array("Mantenimiento", $u_accion)) {
                    $type = 5;
                }
                break;
            case "intervencion":
                /*
                 * Si u_accion es intervención.
                 * Bloquear la plantilla.
                 *
                 */
                $data = $step->getStepDataValue();
                $dataJson = json_decode($data);

                $u_accion = $dataJson->varValues->u_accion;
                //var_dump($u_accion);
                //exit;
                if (in_array("Intervenci%C3%B3n", $u_accion)) {
                    $metaData = $this->getDoctrine()
                        ->getRepository('NononsenseHomeBundle:MetaData')
                        ->findOneBy(array("workflow_id" => $registro->getId()));

                    $equipo = $metaData->getEquipo();
                    $type = 5;

                    $now = new \DateTime();
                    $now->modify("+2 hour"); // Ver tema de horarios usos

                    $bloqueoMasterWorkflow = new BloqueoMasterWorkflow();
                    $bloqueoMasterWorkflow->setStatus(0);
                    $bloqueoMasterWorkflow->setEquipo($equipo);
                    $bloqueoMasterWorkflow->setMasterWorkflowId($registro->getMasterWorkflowEntity()->getId());
                    $bloqueoMasterWorkflow->setFechaInicioBloqueo($now);
                    $bloqueoMasterWorkflow->setRegistroId($registro->getId());

                    $em->persist($bloqueoMasterWorkflow);

                }
                break;
        }


        $revisionInstanciaWorkflowEntity = new RevisionInstanciaWorkflow();
        $revisionInstanciaWorkflowEntity->setStatus(0);

        $revisionInstanciaWorkflowEntity->setRevisiontext("Escriba aquí los comentarios que sean necesarios");
        $revisionInstanciaWorkflowEntity->setInstanciaWorkflowEntity($registro);

        $revisionInstanciaWorkflowEntity->setType($type);

        $em->persist($revisionInstanciaWorkflowEntity);
        $em->persist($registro);
        /*
                $metaData = new MetaData();
                $metaData->setInstanciaWorkflow($registro);
                $metaData->setDataname("Fecha Registro Completado");
                $now = new \DateTime();
                $now->modify("+2 hour"); // Ver tema de horarios usos
                $metaData->setFecha($now);

                $em->persist($metaData);
        */
        $em->flush();

        /*
         * Guardar evidencia de registro completado
         */
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(0);
        $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());

        $documentName = $step->getMasterStep()->getName();

        /*
         * Guardar firma
         */
        $firmas = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findBy(array("step_id" => $step->getId()));

        $counter = count($firmas) + 1;

        $firmaImagen = $request->query->get('firma');
        $firma = new FirmasStep();
        $firma->setAccion("Guardado y enviado a validación");
        $firma->setStepEntity($step);
        $firma->setUserEntiy($user);
        $firma->setFirma($firmaImagen);
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);

        $em->persist($evidencia);
        $em->persist($firma);
        $em->flush();

        /*
         * Enviar email si sólo hay un usuario en un grupo.
         */

        $grupoVerificacion = $registro->getMasterWorkflowEntity()->getGrupoVerificacion();
        $userGroupVerificacion = $grupoVerificacion->getUsers();

        if (count($userGroupVerificacion) == 1) {
            // Enviar notificación al único usuario del grupo

            $groupUsers = $userGroupVerificacion[0];
            $userVerificacion = $groupUsers->getUser();

            $mailTo = $userVerificacion->getEmail();
            $baseURL = $this->container->getParameter('cm_installation');
            $link = $baseURL . "registro_process";
            $logo = '';
            $accion = 'notificarVerficiacion';
            $subject = 'Tiene un registro pendiente de verificar';
            $message = 'Tiene pendiente el siguiente registro: ' . $registro->getId() . ' pendiente de verificar';

            $this->_sendNotification($mailTo, $link, $logo, $accion, $subject, $message);
        }

        return $this->render('NononsenseHomeBundle:Contratos:registro_guardadoenviado.html.twig', array(
            "documentName" => $documentName,
        ));

    }

    public function controlCheckListAction($stepid, $action, $comment, $urlaux)
    {
        /*
                * cerrar
                * cancelar
                * parcial
                * enviar
                */
        $em = $this->getDoctrine()->getManager();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $registro = $step->getInstanciaWorkflow();
        $registro->setInEdition(0);

        if ($action == 'cancelar') {
            $registro->setStatus(3);
            $route = $this->container->get('router')->generate('nononsense_registro_cancelar', array('stepid' => $stepid));

        } elseif ($action == 'parcial') {
            //$registro->setStatus(1);

            $route = $this->container->get('router')->generate('nononsense_contrato_registro_completado', array('stepid' => $stepid, 'comment' => $comment));

        } elseif ($action == 'enviar') {
            /*
             * Para llegar a este punto REAL el usuario debe haber verificado el ES-MA. Si no lo ha hecho mostrar un mensaje de error
             */
            $stepList = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                ->findBy(array("workflow_id" => $registro->getId()));

            $esmavalidado = false;

            foreach ($stepList as $oneStep) {
                if ($oneStep->getMasterStep()->getChecklist() == 0) {
                    // El ES-MA debe estar validado
                    if ($oneStep->getStatusId() == 2) {
                        $esmavalidado = true;
                    }
                }
            }

            if ($esmavalidado) {
                $step->setStatusId(2); // verificado
                $route = $this->container->get('router')->generate('nononsense_registro_verificar', array('stepid' => $stepid, 'comment' => $comment));
            } else {
                $route = $this->container->get('router')->generate('nononsense_esma_no_validado', array('workflowid' => $registro->getId()));
            }


        } else if ($action == 'cerrar') {

            $route = base64_decode($urlaux);


        } else {
            // Error... go inbox
            echo 'No deberías haber llegado aquí. Error desconocido';
            var_dump($action);
            exit;

        }

        $em->persist($step);
        $em->persist($registro);
        $em->flush();

        return $this->redirect($route);
    }

    public function ESMANoValidadoAction($workflowid)
    {

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($workflowid);

        $documentName = $registro->getMasterWorkflowEntity()->getName();

        return $this->render('NononsenseHomeBundle:Contratos:registro_esma_no_validado.html.twig', array(
            "documentName" => $documentName
        ));
    }

    public function controlElaboracionAction($stepid, $action, $comment, $urlaux)
    {
        /*
         * cerrar
         * cancelar
         * parcial
         * enviar
         */
        $em = $this->getDoctrine()->getManager();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $registro = $step->getInstanciaWorkflow();
        $registro->setInEdition(0);
        //var_dump($action);

        if ($action == 'cancelar') {
            $registro->setStatus(3);
            $route = $this->container->get('router')->generate('nononsense_registro_cancelar', array('stepid' => $stepid));

        } elseif ($action == 'parcial') {
            $registro->setStatus(1);

            $route = $this->container->get('router')->generate('nononsense_contrato_registro_completado', array('stepid' => $stepid, 'comment' => $comment));

        } elseif ($action == 'enviar') {
            $registro->setStatus(2);
            $route = $this->container->get('router')->generate('nononsense_contrato_registro_completado', array('stepid' => $stepid, 'comment' => $comment));

        } else if ($action == 'cerrar') {

            $route = base64_decode($urlaux);


        } else {
            // Error... go inbox
            echo 'No deberías haber llegado aquí. Error desconocido';
            var_dump($action);
            exit;

        }

        $em->persist($registro);
        $em->flush();

        return $this->redirect($route);
    }

    public function controlValidacionAction($stepid, $action, $comment, $urlaux)
    {
        $em = $this->getDoctrine()->getManager();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $registro = $step->getInstanciaWorkflow();
        $registro->setInEdition(0);

        $em->persist($registro);
        $em->flush();

        if ($action == 'cancelar') {
            $registro->setStatus(12);
            $route = $this->container->get('router')->generate('nononsense_registro_cancelar_verficiacion', array('stepid' => $stepid));

        } elseif ($action == 'verificar') {
            $step->setStatusId(2); // verificado
            $registro->setStatus(7);
            $route = $this->container->get('router')->generate('nononsense_registro_verificar', array('stepid' => $stepid, 'comment' => $comment));


        } elseif ($action == 'devolver') {
            $registro->setStatus(13);
            $route = $this->container->get('router')->generate('nononsense_registro_devolver_edicion', array('stepid' => $stepid));


        } else if ($action == 'cerrar') {

            $route = base64_decode($urlaux);


        } else if ($action == 'verificarparcial') {
            $registro->setStatus(15);
            $route = $this->container->get('router')->generate('nononsense_registro_verificar_parcial', array('stepid' => $stepid, 'comment' => $comment));

        } else {
            // Error... go inbox
            echo 'No deberías haber llegado aquí. Error desconocido';
            var_dump($action);
            exit;

        }

        $em->persist($step);
        $em->persist($registro);
        $em->flush();

        return $this->redirect($route);
    }

    public function controlCancelacionAction($stepid, $action, $urlaux)
    {
        $em = $this->getDoctrine()->getManager();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $registro = $step->getInstanciaWorkflow();
        $registro->setInEdition(0);

        $em->persist($registro);
        $em->flush();

        if ($action == 'rechazar') {

            $route = $this->container->get('router')->generate('nononsense_registro_rechazar_cancelacion_firma', array('stepid' => $stepid));

        } elseif ($action == 'aprobar') {

            $route = $this->container->get('router')->generate('nononsense_registro_aprobar_cancelacion_firma', array('stepid' => $stepid));


        } else if ($action == 'cerrar') {

            $route = base64_decode($urlaux);


        } else {
            // Error... go inbox
            echo 'No deberías haber llegado aquí. Error desconocido';
            var_dump($action);
            exit;

        }

        $em->persist($step);
        $em->persist($registro);
        $em->flush();

        return $this->redirect($route);
    }

    public function listadoAutorizacionesAction()
    {
        // Sólo si FLL
        $user = $this->container->get('security.context')->getToken()->getUser();
        $FLL = false;

        foreach ($user->getGroups() as $groupMe) {
            $type = $groupMe->getGroup()->getTipo();
            if ($type == 'FLL') {
                $FLL = true;
            }
        }

        if (!$FLL) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos para estar aqui'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);

        }

        $documentsProcess = array();
        /*
         * id
         * idafectado
         * subcat => companyName
         * name
         * fecha
         * nameSol
         *
         */

        $peticionesReconciliacion = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
            ->findBy(array('status' => 0));

        foreach ($peticionesReconciliacion as $peticion) {
            $registroViejoId = $peticion->getRegistroViejoId();

            $registroViejo = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                ->find($registroViejoId);

            $userSolicitud = $this->getDoctrine()
                ->getRepository('NononsenseUserBundle:Users')
                ->find($peticion->getUserId());

            $subcat = $registroViejo->getMasterWorkflowEntity()->getCategory()->getName();
            $nombre_usuario = $userSolicitud->getName();
            $name = $registroViejo->getMasterWorkflowEntity()->getName();
            $fecha = $peticion->getCreated();

            $element = array(
                "id" => $peticion->getId(),
                "idafectado" => $registroViejoId,
                "subcat" => $subcat,
                "name" => $name,
                "fecha" => $fecha,
                "nameUser" => $nombre_usuario
            );
            $documentsProcess[] = $element;
        }

        return $this->render('NononsenseHomeBundle:Contratos:reconcialiacion_list.html.twig', array(
            "documentsProcess" => $documentsProcess,
        ));
    }

    public function autorizarPeticionInterfaceAction($peticionid)
    {
        $peticionEntity = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
            ->find($peticionid);

        $registroViejoId = $peticionEntity->getRegistroViejoId();

        $registroViejo = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($registroViejoId);

        $userSolicitud = $this->getDoctrine()
            ->getRepository('NononsenseUserBundle:Users')
            ->find($peticionEntity->getUserId());

        $subcat = $registroViejo->getMasterWorkflowEntity()->getCategory()->getName();
        $nombre_usuario = $userSolicitud->getName();
        $name = $registroViejo->getMasterWorkflowEntity()->getName();
        $fecha = $peticionEntity->getCreated();

        $peticion = array(
            "id" => $peticionEntity->getId(),
            "idafectado" => $registroViejoId,
            "subcat" => $subcat,
            "name" => $name,
            "fecha" => $fecha,
            "nameUser" => $nombre_usuario
        );

        $documentsReconciliacion = array();
        $procesarReconciliaciones = true;

        while($procesarReconciliaciones){
            if($registroViejo != null){

                $subcat = $registroViejo->getMasterWorkflowEntity()->getCategory()->getName();
                $name = $registroViejo->getMasterWorkflowEntity()->getName();

                $element = array(
                    "id"=> $registroViejo->getId(),
                    "subcat" => $subcat,
                    "name" => $name,
                    "status" => $registroViejo->getStatus(),
                    "fecha" =>$registroViejo->getModified()
                );
                $documentsReconciliacion[] = $element;
            }else{
                $procesarReconciliaciones = false;
            }

            // Ver una posible reconciliación del registro viejo
            $peticionReconciliacionAntigua = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
                ->findOneBy(array("registro_nuevo_id"=>$registroViejo->getId()));

            if(isset($peticionReconciliacionAntigua)){

                $registroViejoId = $peticionReconciliacionAntigua->getRegistroViejoId();
                $registroViejo = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                    ->find($registroViejoId);

            }else{
                $registroViejo = null;
                $procesarReconciliaciones = false;
            }

        }

        return $this->render('NononsenseHomeBundle:Contratos:gestionar_peticion_reconciliacion.html.twig', array(
            "documentsReconciliacion" => $documentsReconciliacion,
            "peticion" => $peticion));
    }

    public function procesarPeticionAction($peticionid, Request $request)
    {
        /*
         * Se genera una evidencia a mayores para el step del registro original
         * Se le pega la firma.
         * En caso correcto, se marca como registro reconciliado y el nuevo se habilita y se pone a 0.
         */
        $peticionEntity = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
            ->find($peticionid);

        $registroViejoId = $peticionEntity->getRegistroViejoId();
        $registroNuevoId = $peticionEntity->getRegistroNuevoId();

        $registroViejo = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($registroViejoId);

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registroViejoId, "dependsOn"=>0));

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

        if($accion == 'autorizar'){
            $descp = "Petición de reconciliación autorizada. " . $comentario;
            $registroViejo->setStatus(10);
            $peticionEntity->setStatus(1);

            $registroNuevo = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                ->find($registroNuevoId);
            $registroNuevo->setStatus(0);
            $em->persist($registroNuevo);

        }else{
            $descp = "Petición de reconciliación no autorizada. " . $comentario;
            $peticionEntity->setStatus(2);
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
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);


        $em->persist($evidencia);
        $em->persist($peticionEntity);
        $em->persist($firma);
        $em->persist($registroViejo);
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_registro_enproceso');
        return $this->redirect($route);

    }

    private function _checkModifyVariables($step)
    {
        /*
         * Comprueba si ha habido alguna modificación en los valores imputados
         */
        $resultado = false;

        $dataString = $step->getStepDataValue();

        $evidencias = $evidencias = $step->getEvidenciasStep();

        if (!empty($evidencias)) {
            $lastEvidencia = null;
            $currentId = 0;

            foreach ($evidencias as $evidenciaElement) {
                if ($currentId < $evidenciaElement->getId()) {
                    $lastEvidencia = $evidenciaElement;
                    $currentId = $evidenciaElement->getId();
                }
            }

            if ($currentId != 0) {
                // Comparar varValues not empties
                $currentDataJson = json_decode($dataString);
                $lastDataJson = json_decode($lastEvidencia->getStepDataValue());

                $currentVarValues = $currentDataJson->varValues;
                $lastVarValues = $lastDataJson->varValues;

                foreach ($currentVarValues as $prop => $value) {
                    $lastValues = $lastVarValues->{$prop};
                    $position = strpos($prop, "u_");

                    if ($position === 0) {
                        // variable válida
                        // variable válida.
                        $lastValue = trim(implode("", $lastVarValues->{$prop})); // Para que funcione en los "checboxes" y "radioButton" habría que hacer un implode + trim
                        // if lastValue es un valor vacío no haría falta hacer un "modificado"
                        $currentValue = trim(implode("", $value));

                        if ($lastValue != "") {
                            if ($lastValue != $currentValue) {
                                // Modificado
                                $resultado = true;

                            }
                        }
                        /*
                        if ($value !== $lastValues) {
                            //   echo 'Te pillé: ';
                            //  var_dump($value);
                            //var_dump($lastValues);
                            $resultado = true;
                        }
                        */
                    }

                }
            }


        } else {
            // first usage do nothing
        }

        return $resultado;
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