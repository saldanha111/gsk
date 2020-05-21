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
use Nononsense\HomeBundle\Entity\ActivityUser;

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

        $percentageCompleted = $stepDataJSON->data->gsk_percent;


        //var_dump($this->_checkModifyVariables($step));die();
        
        $devolucion = 0;
        $validated=0;

        /*
         * Revisar si ha habido algún cambio en las variables para que muestre el campo de texto.
         */
        if(property_exists($stepDataJSON->data,"gsk_comment")){
            $devolucion = 1;
        }

        if($stepDataJSON->action=="save"){
            $validated = 1;
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

    public function linkAction($stepid, $form, $revisionid, $logbook,$modo)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if(isset($_REQUEST["readonly"])){
            $readonly=TRUE;
        }
        else{
            $readonly=FALSE;
        }

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $registro = $step->getInstanciaWorkflow();

        if ($registro->getInEdition() == 1 && !$readonly) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Este registro ha sido abierto por otro usuario'
            );
            $route = $this->container->get('router')->generate('nononsense_search');
            return $this->redirect($route);
        }

        if ($registro->getStatus() == 0 && !$readonly) {
            // En verficiación, comprobar que puede verificar
            if (!$this->puedeCumplimentar($step)) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'No puede cumplimentar este registro porque ha participado en la verificación'
                );
                $route = $this->container->get('router')->generate('nononsense_search');
                return $this->redirect($route);
            }
        }

        if (($registro->getStatus() == 4 || $registro->getStatus() == 5) && !$readonly) {
            // En verficiación, comprobar que puede verificar
            if (!$this->puedeValidar($step)) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'No puede validar este registro porque ha participado en la elaboración'
                );
                $route = $this->container->get('router')->generate('nononsense_search');
                return $this->redirect($route);
            }
        }

        if ($registro->getStatus() == 14 && !$readonly) {
            // En verficiación, comprobar que puede verificar si está en cancelación
            $firma = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:FirmasStep')
                ->findOneBy(array("step_id" => $step->getId()),array('number' => 'DESC'));

            if ($user==$firma->getUserEntiy()) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'No puede validar este registro porque ha participado en la cancelación en verificación'
                );
                $route = $this->container->get('router')->generate('nononsense_search');
                return $this->redirect($route);
            }
        }


        $baseUrl = $this->getParameter("cm_installation");
        $baseUrlAux = $this->getParameter("cm_installation_aux");

        $options = array();

        $scriptUrl="";
        $styleUrl="";

        /*
         * Saber si hay algún precreation
         */
        $precreationValue = $registro->getMasterWorkflowEntity()->getPrecreation();
        if ($precreationValue != "default") {
            /*
             * Si ya tuviera una firma asignada no haría falta hacer esto
             */

            $firma = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:FirmasStep')
                ->findOneBy(array("step_id" => $step->getId()));

        }
        /*$options['custom'] = json_encode($customObject);

        $accionText = '';*/

        $styleUrl = $baseUrl . "../css/css_oarodoc/standard.css?v=".uniqid();

        if ($step->getMasterStep()->getChecklist() == 1 && $registro->getStatus() == 4  && !$readonly) {

            $scriptUrl = $baseUrl . "../js/js_oarodoc/activity.js?v=".uniqid();

            $redirect_url = $baseUrl . "control_check_list/" . $stepid . "/";

            $accionText = 'Completar check list';
            $actionId = 4;

        } else {


            if ($registro->getStatus() == 4  && !$readonly) {
                // Abrir para validar
                $redirect_url = $baseUrl . "control_validacion/" . $stepid;

                $scriptUrl = $baseUrl . "../js/js_oarodoc/validation.js?v=".uniqid();
                $registro->setInEdition(1);

                $accionText = 'Validar registro';
                $actionId = 2;

            } else if (($registro->getStatus() == -1 || $registro->getStatus() == 0) && !$readonly) {

                $registro->setInEdition(1);
                // abrir para editar
                $scriptUrl = $baseUrl . "../js/js_oarodoc/activity.js?v=".uniqid();
                
                $redirect_url = $baseUrl . "control_elaboracion/" . $stepid;

                $accionText = 'Elaborar registro';
                $actionId = 1;

            } else if (($registro->getStatus() == 5 || $registro->getStatus() == 14) && !$readonly) {
                $registro->setInEdition(1);
                // Flujos de cancelación
                $redirect_url = $baseUrl . "control_cancelacion/" . $stepid;

                $scriptUrl = $baseUrl . "../js/js_oarodoc/validation_cancel.js?v=".uniqid();

                $accionText = 'verificar cancelacion';
                $actionId = 5;

            } else {

                // No abrir para editar ... usar el método show
                $redirect_url = $baseUrl . "control_elaboracion/" . $stepid;
                $scriptUrl = $baseUrl . "../js/js_oarodoc/show.js?v=".uniqid();

                $accionText = 'Ver registro';
                $actionId = 3;

            }
        }


        /*
         * Caso especial de firma FLL
         */
        $revisionWorkflow = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RevisionInstanciaWorkflow')
            ->find($revisionid);

        /*if($step->getMasterStep()->getId() == 7){
            $validacionURL2 = $baseUrl . "js/js_templates/pesos.js";
        }*/
        


        //$options['requestExternalJS'] = $validacionURL1;
        $callback_url = $baseUrlAux . 'data/get_data_from_document/' . $stepid;
        $get_data_url = $baseUrlAux . 'data/requestData/' . $step->getId() . '/' . $logbook.'/'.$modo;
        
        $base_url=$this->getParameter('api_docoaro')."/documents/".$step->getMasterStep()->getPlantillaId()."?getDataUrl=".$get_data_url."&redirectUrl=".$redirect_url."&callbackUrl=".$callback_url."&scriptUrl=".$scriptUrl."&styleUrl=".$styleUrl;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"GET");
        

        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Api-Key: ".$this->getParameter('api_key_docoaro')));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array());    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $raw_response = curl_exec($ch);
        $response = json_decode($raw_response, true);

        $url_edit_documento=$response["fillInUrl"];

        /*
         * Crear activity registro
         */
        $now = new \DateTime();

        $activity = new ActivityUser();
        $activity->setEntrada($now);
        $activity->setStatus(0);
        $activity->setUserEntiy($user);
        $activity->setStepEntity($step);
        $activity->setAccion($accionText);
        $activity->setActionID($actionId);


        $em = $this->getDoctrine()->getManager();
        $em->persist($activity);
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
        *
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(0);
        $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());
        */

        $firmaImagen = $request->get('firma');
        $comentario = $request->get('comment');

        if (!empty($comentario)) {
            $descp = "Guardado parcial. " . $comentario;

        } else {
            $descp = "Guardado parcial";
        }
        /*
         * Obtener firma pendiente y firmar
         */
        $firma = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findOneBy(array("step_id" => $step->getId(), "status" => 0, "userEntiy" => $user));

        if (isset($firma)) {
            $firma->setFirma($firmaImagen);
            $firma->setAccion($descp);
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
                $firma->setAccion($descp);
                $firma->setStepEntity($step);
                $firma->setUserEntiy($user);
                $firma->setFirma($firmaImagen);
                $firma->setNumber($counter);

                $evidencia->setFirmaEntity($firma);


                $em->persist($evidencia);
        */

        /*
         * Si soy la checklist debo ir al estado 4
         */
        if($step->getMasterStep()->getChecklist() == 1){
            $registro->setStatus(4);
            $em->persist($registro);
        }

        $em->persist($firma);
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_search');
        return $this->redirect($route);

    }

    public function saveAndSendAction($stepid, Request $request)
    {
        $comentario = $request->get('comment');

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $em = $this->getDoctrine()->getManager();

        $registro = $step->getInstanciaWorkflow();
        $registro->setStatus(4);

        /*if (isset($comentario)) {
            // Devolución
            $revisionInstanciaWorkflowEntityAux = new RevisionInstanciaWorkflow();
            $revisionInstanciaWorkflowEntityAux->setStatus(3);

            $revisionInstanciaWorkflowEntityAux->setRevisiontext($comentario);
            $revisionInstanciaWorkflowEntityAux->setInstanciaWorkflowEntity($registro);

            $revisionInstanciaWorkflowEntityAux->setType(1);
            $em->persist($revisionInstanciaWorkflowEntityAux);
        }*/
        /*
         * Crea la validación, asignar grupo si es específico.
         */
        $validationType = $registro->getMasterWorkflowEntity()->getValidation();
        // En type va el valor del grupo del usuario de creación.

        $user = $this->container->get('security.context')->getToken()->getUser();

        $type=1;
        // Sólo debería tener uno ...
        foreach ($user->getGroups() as $groupMe) {
            $type = $groupMe->getGroup()->getId();
        }

        /*
         * Obsoleto
         *
                switch ($validationType) {
                    case "FLL":
                        $type = 5;
                        $registro->setStatus(8);
                        break;
                    case "mantenimiento":
                        /*
                         * Si valor mantenimineto en el datos del step el type es 2, sino es 1
                         * varValues
                         *
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
                         *
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
        */

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
                $metaData->setFecha($now);

                $em->persist($metaData);
        */
        $em->flush();

        /*
         * Guardar evidencia de registro completado
         *
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(0);
        $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());
*/
        $documentName = $step->getMasterStep()->getName();

        /*
         * Guardar firma
         */


        $firma = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findOneBy(array("step_id" => $step->getId(), "status" => 0, "userEntiy" => $user));

        $firmaImagen = $request->get('firma');

        if (isset($firma)) {
            $firma->setFirma($firmaImagen);
            $firma->setAccion("Guardado y enviado a verificación. " . $comentario);
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
                $firma->setAccion("Guardado y enviado a validación");
                $firma->setStepEntity($step);
                $firma->setUserEntiy($user);
                $firma->setFirma($firmaImagen);
                $firma->setNumber($counter);

                $evidencia->setFirmaEntity($firma);

                $em->persist($evidencia);
                */
        $em->persist($firma);
        $em->flush();

        /*
         * Enviar email si sólo hay un usuario en un grupo.
         */

        $grupoVerificacion = $registro->getMasterWorkflowEntity()->getGrupoVerificacion();
        $userGroupVerificacion = $grupoVerificacion->getUsers();

        if (count($userGroupVerificacion) == 1) {
            // Enviar notificación al único usuario del grupo
            // {{path('nononsense_ver_registro', {'revisionid': plantilla.registroid})}}
            $route = $this->container->get('router')->generate('nononsense_ver_registro', array('revisionid' => $registro->getId()));
            $route = 'registro_ver/' . $registro->getId();

            $groupUsers = $userGroupVerificacion[0];
            $userVerificacion = $groupUsers->getUser();

            $mailTo = $userVerificacion->getEmail();
            $baseURL = $this->container->getParameter('cm_installation');
            $link = $baseURL . $route;

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
        $urlaux=str_replace("--", "/", $urlaux);
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $registro = $step->getInstanciaWorkflow();
        $registro->setInEdition(0);

        $debeFirmar = true;

        if ($action == 'cancelar') {
            $registro->setStatus(3);
            $route = $this->container->get('router')->generate('nononsense_registro_cancelar', array('stepid' => $stepid));
            $descp = 'Pendiente firma cancelación checklist';

        } elseif ($action == 'parcial') {
            $registro->setStatus(15);

            $route = $this->container->get('router')->generate('nononsense_contrato_registro_completado', array('stepid' => $stepid, 'comment' => $comment));
            $descp = 'Pendiente firma completado parcial checklist';

        } elseif ($action == 'enviar') {
            /*
             * Para llegar a este punto REAL el usuario debe haber verificado el ES-MA. Si no lo ha hecho mostrar un mensaje de error
             */
            $descp = 'Pendiente firma envio verificación completo checklist';
            $registro->setStatus(7);
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
                $registro->setStatus(15);
                $route = $this->container->get('router')->generate('nononsense_esma_no_validado', array('workflowid' => $registro->getId()));
            }


        } else if ($action == 'cerrar') {
            $debeFirmar = false;
            $route = base64_decode($urlaux);


        } else {
            // Error... go inbox
            $debeFirmar = false;
            echo 'No deberías haber llegado aquí. Error desconocido';
            var_dump($action);
            exit;

        }

        if ($debeFirmar) {
            $evidencia = new EvidenciasStep();
            $evidencia->setStepEntity($step);
            $evidencia->setStatus(0);
            $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
            $evidencia->setToken($step->getToken());
            $evidencia->setStepDataValue($step->getStepDataValue());

            $firmas = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:FirmasStep')
                ->findBy(array("step_id" => $step->getId()));

            $counter = count($firmas) + 1;

            $firma = new FirmasStep();
            $firma->setAccion($descp);
            $firma->setStepEntity($step);
            $firma->setUserEntiy($user);
            $firma->setFirma("");
            $firma->setStatus(0); //Pendiente
            $firma->setElaboracion(0);
            $firma->setNumber($counter);

            $evidencia->setFirmaEntity($firma);


            $em->persist($evidencia);
            $em->persist($firma);
        }


        $em->persist($step);
        $em->persist($registro);
        $em->flush();

        $this->_registrarFinActividad($user, $step);

        return $this->redirect($route);
    }

    public function ESMANoValidadoAction($workflowid)
    {

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($workflowid);

        $documentName = $registro->getMasterWorkflowEntity()->getName();

        $stepsList = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findBy(array("workflow_id" => $registro->getId()));

        $stepCheckList = null;

        foreach ($stepsList as $oneStep) {
            if ($oneStep->getMasterStep()->getChecklist() == 1) {
                $stepCheckList = $oneStep;
            }
        }


        return $this->render('NononsenseHomeBundle:Contratos:registro_esma_no_validado.html.twig', array(
            "documentName" => $documentName,
            "stepid" => $stepCheckList->getId()
        ));
    }

    public function ESMANoValidadoFirmaAction($stepid, Request $request)
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
        $registro->setStatus(4);

        $em->persist($registro);
        $em->flush();

        $firmaImagen = $request->get('firma');
        $comentario = $request->get('comment');

        if (!empty($comentario)) {
            $descp = "Guardado parcial. " . $comentario;

        } else {
            $descp = "Guardado parcial";
        }
        /*
         * Obtener firma pendiente y firmar
         */
        $firma = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findOneBy(array("step_id" => $step->getId(), "status" => 0, "userEntiy" => $user));

        if (isset($firma)) {
            $firma->setFirma($firmaImagen);
            $firma->setAccion($descp);
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

        $em->persist($firma);
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_search');
        return $this->redirect($route);
    }

    public function controlElaboracionAction($stepid)
    {
        /*
         * Crear aquí la entidad de la firma, y luego en la parte de recoger la firma simplemente actualizar.
         * En la interfaz se puede poner "firmar" si el usuario en cuestión es el mismo.
         *
         */
        
        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $registro = $step->getInstanciaWorkflow();
        $registro->setInEdition(0);

        $debeFirmar = true;

        
        $dataJson = json_decode($step->getStepDataValue());

        if(property_exists($dataJson,"gsk_comment")){
            $comment=1;
        }
        else{
            $comment=0;
        }

        
        if ($dataJson->action == 'cancel') {
            $registro->setStatus(3);
            $descp = 'Solicitud cancelacion: ';
            $route = $this->container->get('router')->generate('nononsense_registro_cancelar', array('stepid' => $stepid));

        } elseif ($dataJson->action == 'save_partial') {
            $registro->setStatus(1);
            $descp = 'Guardado parcial';
            $route = $this->container->get('router')->generate('nononsense_contrato_registro_completado', array('stepid' => $stepid, 'comment' => $comment));

        } elseif ($dataJson->action == 'save') {
            $registro->setStatus(2);
            $descp = 'Guardado y enviado a verificación';
            $route = $this->container->get('router')->generate('nononsense_contrato_registro_completado', array('stepid' => $stepid, 'comment' => $comment));

        } else if ($dataJson->action == 'close') {
            $firmas = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findBy(array("stepEntity" => $step));
            if(empty($firmas) && $registro->getStatus()<=0){
                $registro->setStatus(-1);
            }
            $debeFirmar = false;
            $route = $this->container->get('router')->generate('nononsense_search');

        } else {
            // Error... go inbox
            echo 'No deberías haber llegado aquí. Error desconocido';
            exit;

        }


        if ($debeFirmar) {
            $evidencia = new EvidenciasStep();
            $evidencia->setStepEntity($step);
            $evidencia->setStatus(0);
            $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
            $evidencia->setToken($step->getToken());
            $evidencia->setStepDataValue($step->getStepDataValue());

            $firmas = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:FirmasStep')
                ->findBy(array("step_id" => $step->getId()));

            $counter = count($firmas) + 1;

            $firma = new FirmasStep();
            $firma->setAccion($descp);
            $firma->setStepEntity($step);
            $firma->setUserEntiy($user);
            $firma->setFirma("");
            $firma->setStatus(0); //Pendiente
            $firma->setElaboracion(1);
            $firma->setNumber($counter);

            $evidencia->setFirmaEntity($firma);

            $step->setStepDataValue(str_replace("gsk_id_firm", $counter, $step->getStepDataValue()));
            $evidencia->setStepDataValue(str_replace("gsk_id_firm", $counter, $evidencia->getStepDataValue()));

            $em->persist($step);

            $em->persist($evidencia);
            $em->persist($firma);
        }

        /*
         * Registrar fin actividad
         */
        $this->_registrarFinActividad($user, $step);


        $em->persist($registro);
        $em->flush();

        return $this->redirect($route);
    }

    public function controlValidacionAction($stepid)
    {
        
        $em = $this->getDoctrine()->getManager();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $registro = $step->getInstanciaWorkflow();
        $registro->setInEdition(0);

        $em->persist($registro);
        $em->flush();

        $dataJson = json_decode($step->getStepDataValue());

        if(property_exists($dataJson,"gsk_comment")){
            $comment=1;
        }
        else{
            $comment=0;
        }


        $debeFirmar = true;
        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($dataJson->action == 'cancel') {
            $registro->setStatus(12);
            $route = $this->container->get('router')->generate('nononsense_registro_cancelar_verficiacion', array('stepid' => $stepid));
            $descp = 'Pendiente firma de solicitar cancelación en verificación';

        } elseif ($dataJson->action == 'save') {
            $step->setStatusId(2); // verificado
            $registro->setStatus(7);
            $route = $this->container->get('router')->generate('nononsense_registro_verificar', array('stepid' => $stepid, 'comment' => $comment));
            $descp = 'Pendiente firma de verificación total';

        } elseif ($dataJson->action == 'return') {
            $registro->setStatus(13);
            $route = $this->container->get('router')->generate('nononsense_registro_devolver_edicion', array('stepid' => $stepid));
            $descp = 'Pendiente firma para enviar a devolución';

        } else if ($dataJson->action == 'close') {
            $debeFirmar = false;
            $route = $this->container->get('router')->generate('nononsense_search');


        } else if ($dataJson->action == 'save_partial') {
            $registro->setStatus(15);
            $route = $this->container->get('router')->generate('nononsense_registro_verificar_parcial', array('stepid' => $stepid, 'comment' => $comment));
            $descp = 'Pendiente firma verificación parical';

        } else {
            // Error... go inbox
            $debeFirmar = false;
            echo 'No deberías haber llegado aquí. Error desconocido';
            var_dump($action);
            exit;

        }

        if ($debeFirmar) {
            $evidencia = new EvidenciasStep();
            $evidencia->setStepEntity($step);
            $evidencia->setStatus(0);
            $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
            $evidencia->setToken($step->getToken());
            $evidencia->setStepDataValue($step->getStepDataValue());

            $firmas = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:FirmasStep')
                ->findBy(array("step_id" => $step->getId()));

            $counter = count($firmas) + 1;

            $firma = new FirmasStep();
            $firma->setAccion($descp);
            $firma->setStepEntity($step);
            $firma->setUserEntiy($user);
            $firma->setFirma("");
            $firma->setStatus(0); //Pendiente
            $firma->setElaboracion(0);
            $firma->setNumber($counter);

            $evidencia->setFirmaEntity($firma);

            $step->setStepDataValue(str_replace("gsk_id_firm", $counter, $step->getStepDataValue()));
            $evidencia->setStepDataValue(str_replace("gsk_id_firm", $counter, $evidencia->getStepDataValue()));


            $em->persist($evidencia);
            $em->persist($firma);
        }

        $em->persist($step);
        $em->persist($registro);
        $em->flush();

        $this->_registrarFinActividad($user, $step);

        return $this->redirect($route);
    }

    public function controlCancelacionAction($stepid)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

        $registro = $step->getInstanciaWorkflow();
        $registro->setInEdition(0);

        $dataJson = json_decode($step->getStepDataValue());

        $em->persist($registro);
        $em->flush();
        $debeFirmar = true;
        if ($dataJson->action == 'cancel') {

            $route = $this->container->get('router')->generate('nononsense_registro_rechazar_cancelacion_firma', array('stepid' => $stepid));
            $descp = 'Pendiente firma rechazar cancelación';

        } elseif ($dataJson->action == 'save_partial') {

            $route = $this->container->get('router')->generate('nononsense_registro_aprobar_cancelacion_firma', array('stepid' => $stepid));
            $descp = 'Pendiente firmar aprobar cancelación';

        } else if ($dataJson->action == 'close') {
            $debeFirmar = false;
            $route = $this->container->get('router')->generate('nononsense_search');


        } else {
            // Error... go inbox
            $debeFirmar = false;
            echo 'No deberías haber llegado aquí. Error desconocido';
            var_dump($action);
            exit;

        }

        if ($debeFirmar) {
            $evidencia = new EvidenciasStep();
            $evidencia->setStepEntity($step);
            $evidencia->setStatus(0);
            $evidencia->setUserEntiy($registro->getUserCreatedEntiy());
            $evidencia->setToken($step->getToken());
            $evidencia->setStepDataValue($step->getStepDataValue());

            $firmas = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:FirmasStep')
                ->findBy(array("step_id" => $step->getId()));

            $counter = count($firmas) + 1;

            $firma = new FirmasStep();
            $firma->setAccion($descp);
            $firma->setStepEntity($step);
            $firma->setUserEntiy($user);
            $firma->setFirma("");
            $firma->setStatus(0); //Pendiente
            $firma->setElaboracion(0);
            $firma->setNumber($counter);

            $evidencia->setFirmaEntity($firma);


            $em->persist($evidencia);
            $em->persist($firma);
        }

        $em->persist($step);
        $em->persist($registro);
        $em->flush();

        $this->_registrarFinActividad($user, $step);

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
            /*
                        $registroViejo = $this->getDoctrine()
                            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                            ->find($registroViejoId);
                        */
            $registroViejo = $peticion->getRegistroViejoEntity();
            /*
                        $userSolicitud = $this->getDoctrine()
                            ->getRepository('NononsenseUserBundle:Users')
                            ->find($peticion->getUserId());
                        */
            $userSolicitud = $peticion->getUserEntiy();

            $subcat = $registroViejo->getMasterWorkflowEntity()->getCategory()->getName();
            $nombre_usuario = $userSolicitud->getName();
            $name = $registroViejo->getMasterWorkflowEntity()->getName();
            $fecha = $peticion->getCreated();

            $step = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                ->findOneBy(array("workflow_id" => $registroViejoId, "dependsOn" => 0));

            $element = array(
                "id" => $peticion->getId(),
                "idafectado" => $step->getId(),
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

        $user = $this->container->get('security.context')->getToken()->getUser();

        if ($user->getId() == $peticionEntity->getUserId()) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'No puede gestionar una solicitud que ha solicitado usted mismo'
            );
            $route = $this->container->get('router')->generate('nononsense_registro_autorizar_list');
            return $this->redirect($route);
        }

        $registroViejoId = $peticionEntity->getRegistroViejoId();
        /*
                $registroViejo = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                    ->find($registroViejoId);
          */
        $registroViejo = $peticionEntity->getRegistroViejoEntity();
        /*
                $userSolicitud = $this->getDoctrine()
                    ->getRepository('NononsenseUserBundle:Users')
                    ->find($peticionEntity->getUserId());
          */
        $userSolicitud = $peticionEntity->getUserEntiy();

        $subcat = $registroViejo->getMasterWorkflowEntity()->getCategory()->getName();
        $nombre_usuario = $userSolicitud->getName();
        $name = $registroViejo->getMasterWorkflowEntity()->getName();
        $fecha = $peticionEntity->getCreated();

        $stepViejo = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registroViejoId, "dependsOn" => 0));

        $peticion = array(
            "id" => $peticionEntity->getId(),
            "idafectado" => $stepViejo->getId(),
            "subcat" => $subcat,
            "name" => $name,
            "fecha" => $fecha,
            "nameUser" => $nombre_usuario,
            "description" => $peticionEntity->getDescription()
        );

        $documentsReconciliacion = array();
        $procesarReconciliaciones = true;

        while ($procesarReconciliaciones) {
            if ($registroViejo != null) {

                $subcat = $registroViejo->getMasterWorkflowEntity()->getCategory()->getName();
                $name = $registroViejo->getMasterWorkflowEntity()->getName();

                $stepViejo = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                    ->findOneBy(array("workflow_id" => $registroViejo->getId(), "dependsOn" => 0));

                $element = array(
                    "id" => $stepViejo->getId(),
                    "subcat" => $subcat,
                    "name" => $name,
                    "status" => $registroViejo->getStatus(),
                    "fecha" => $registroViejo->getModified(),
                    "workflow" => $stepViejo->getWorkflowId()
                );
                $documentsReconciliacion[] = $element;
            } else {
                $procesarReconciliaciones = false;
            }

            // Ver una posible reconciliación del registro viejo
            $peticionReconciliacionAntigua = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
                ->findOneBy(array("registro_nuevo_id" => $registroViejo->getId()));

            if (isset($peticionReconciliacionAntigua)) {

                $registroViejoId = $peticionReconciliacionAntigua->getRegistroViejoId();
                /*
                $registroViejo = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                    ->find($registroViejoId);
                */
                $registroViejo = $peticionReconciliacionAntigua->getRegistroViejoEntity();

            } else {
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

        $user = $this->container->get('security.context')->getToken()->getUser();

        if ($user->getId() == $peticionEntity->getUserId()) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'No puede gestionar una solicitud que ha solicitado usted mismo'
            );
            $route = $this->container->get('router')->generate('nononsense_registro_autorizar_list');
            return $this->redirect($route);
        }

        $registroViejoId = $peticionEntity->getRegistroViejoId();
        $registroNuevoId = $peticionEntity->getRegistroNuevoId();
        /*
                $registroViejo = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                    ->find($registroViejoId);
        */
        $registroViejo = $peticionEntity->getRegistroViejoEntity();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registroViejoId, "dependsOn" => 0));

        $new_step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registroNuevoId, "dependsOn" => 0));

        /*
         * Guardar firma
         */

        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        /*
        * Guardar evidencia un paso "mas" auxiliar
        */
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($new_step);
        $evidencia->setStatus(0);
        $evidencia->setUserEntiy($user);
        $evidencia->setToken($new_step->getToken());
        $evidencia->setStepDataValue($new_step->getStepDataValue());


        $firmaImagen = $request->get('firma');
        $comentario = $request->get('comment');
        $accion = $request->get('accion');

        $registroNuevo = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($registroNuevoId);

        if ($accion == 'autorizar') {
            $descp = "Petición de reconciliación autorizada. " . $comentario;
            //$registroViejo->setStatus(10);
            $peticionEntity->setStatus(1);

            $registroNuevo->setStatus(0);
            $em->persist($registroNuevo);
            $desc_action="autorizada";
            $si_no="";

        } else {
            $descp = "Petición de reconciliación no autorizada. " . $comentario;
            $peticionEntity->setStatus(2);
            $registroNuevo->setStatus(8);

            $desc_action="no autorizada";
            $si_no="no";
        }

        $peticionEntity->setDescValidation($comentario);
        $peticionEntity->setUserValidationEntiy($user);
        /*
         * Guardar firma
         */
        $firmas = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findBy(array("step_id" => $step->getId()));

        $counter = count($firmas) + 1;

        $firma = new FirmasStep();
        $firma->setAccion($descp);
        $firma->setStepEntity($new_step);
        $firma->setUserEntiy($user);
        $firma->setFirma($firmaImagen);
        $firma->setStatus(1);
        $firma->setElaboracion(0);
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);


        $em->persist($evidencia);
        $em->persist($peticionEntity);
        $em->persist($firma);
        $em->persist($registroViejo);

        $email=$peticionEntity->getUserEntiy()->getEmail();
        $subject="Reconciliación ".$desc_action;
        $mensaje='La reconciliación para el documento '.$new_step->getId().' '.$si_no.' ha sido autorizada.';
        $baseURL=$this->container->get('router')->generate('nononsense_search', array(),TRUE)."?id=".$new_step->getId();


        /* ENVIAMOS LA RECONCILIACION A BLOCKCHAIN */

        $api_terceros = $this->getParameter('url_api_3');
        $params = array();

        $info=array(
            "id_usuario_solicitante"=>$peticionEntity->getUserId(),
            "id_viejo"=>$step->getId(),
            "id_nuevo"=>$new_step->getId(),
            "texto_solicitud"=>$peticionEntity->getDescription(),
            "respuesta"=>$desc_action,
            "texto_respuesta"=>$request->get('comment'),
            "id_usuario_autorizador"=>$user->getId(),
            "fecha"=>date("d/m/Y H:i:s")
        );

        $params['json'] = json_encode($info);
        

        $url = $api_terceros.'/json';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("apiKey: ".$this->getParameter('api_key_api_3')));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpcode = strval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        curl_close($ch);

        if($httpcode[0]==2){
            $array_response = json_decode($response, true);
            $peticionEntity->setTxhash($array_response["tx_hash"]);
            $em->persist($peticionEntity);
        }
        else{
            $this->get('session')->getFlashBag()->add(
                'error',
                'La solicitud de reconciliación no se ha podido gestionar. Error al enviar los datos a blockchain'
            );
            $route = $this->container->get('router')->generate('nononsense_registro_autorizar_list');
            return $this->redirect($route);
        }
        /* FIN ENVIO RECONCILIACION A BLOCKCHAIN */

        $em->flush();

        $this->_sendNotification($email, $baseURL, "", "", $subject, $mensaje);

        $route = $this->container->get('router')->generate('nononsense_search');
        return $this->redirect($route);

    }

    private function _registrarFinActividad($user, $step)
    {
        $activity = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:ActivityUser')
            ->findOneBy(array("stepEntity" => $step, "userEntiy" => $user, "status" => 0));

        if($activity){
            $activity->setStatus(1);
            $now = new \DateTime();
            $activity->setSalida($now);

            $em = $this->getDoctrine()->getManager();
            $em->persist($activity);
            $em->flush();
        }

    }

    private function _checkModifyVariables($step)
    {
        /*
         * Comprueba si ha habido alguna modificación en los valores imputados
         * Como se modificó lo de las evidencias, ahora lastEvidencia no es "last" sino dos anteriores.
         */
        $resultado = false;

        $dataString = $step->getStepDataValue();

        $evidencias = $evidencias = $step->getEvidenciasStep();

        if (!empty($evidencias)) {
            $lastEvidencia = null;
            $lastSecondEvidencia = null;

            $currentId = 0;

            foreach ($evidencias as $evidenciaElement) {
                if ($currentId < $evidenciaElement->getId()) {
                    $lastSecondEvidencia = $lastEvidencia;
                    $lastEvidencia = $evidenciaElement;
                    $currentId = $evidenciaElement->getId();
                }
            }

            if ($currentId != 0) {
                // Comparar varValues not empties
                if($lastSecondEvidencia != null){
                    $lastEvidencia = $lastSecondEvidencia;
                }

                $currentDataJson = json_decode($dataString);
                $lastDataJson = json_decode($lastEvidencia->getStepDataValue());

                $currentVarValues = $currentDataJson->varValues;
                $lastVarValues = $lastDataJson->varValues;

                foreach ($currentVarValues as $prop => $values) {
                    foreach($values as $key => $value){
                        if (array_key_exists($key, $lastVarValues->{$prop})) {
                            $lastValues = $lastVarValues->{$prop}[$key];
                            $position = strpos($prop, "u_");

                            if ($position === 0) {
                                // variable válida
                                // variable válida.
                                $lastValue = $lastVarValues->{$prop}[$key]; // Para que funcione en los "checboxes" y "radioButton" habría que hacer un implode + trim
                                // if lastValue es un valor vacío no haría falta hacer un "modificado"
                                
                                $currentValue = $value;

                                $default_value="";
                                foreach($currentDataJson->data as $element){
                                    if($element->name==$prop){
                                        $default_value=$element->label;
                                        break;
                                    }
                                }

                                if ($lastValue != "") {
                                    if (urldecode($lastValue) != urldecode($currentValue) && urldecode($default_value)!=urldecode($lastValue)) {
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

    private function puedeValidar($step)
    {
        $resultado = true;

        $registro = $step->getInstanciaWorkflow();

        $user = $this->container->get('security.context')->getToken()->getUser();
        $userCreated = $registro->getUserCreatedEntiy();

        if ($user == $userCreated) {
            $resultado = false;
        }

        if ($resultado) {

            // Obtener todas las firmas de este usuario
            $firmas = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:FirmasStep')
                ->findBy(array("step_id" => $step->getId(), "userEntiy" => $user));

            foreach ($firmas as $firma) {
                if ($firma->getElaboracion()) {
                    // Este usuario ha firmado y además una firma de elaboración
                    $resultado = false;
                }
            }
        }
        return $resultado;
    }

    private function puedeCumplimentar($step)
    {
        $resultado = true;

        $registro = $step->getInstanciaWorkflow();

        $user = $this->container->get('security.context')->getToken()->getUser();

        if ($resultado) {
            // Obtener todas las firmas de este usuario
            $firmas = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:FirmasStep')
                ->findBy(array("step_id" => $step->getId(), "userEntiy" => $user));

            foreach ($firmas as $firma) {

                $elaboracion = $firma->getElaboracion();
                if (!$elaboracion  && strpos($firma->getAccion(), 'Petición de reconciliación') === false  && strpos($firma->getAccion(), 'Registro en StandBy Liberado') === false) {
                    // Este usuario ha firmado y además una firma de elaboración
                    $resultado = false;
                }
            }
        }

        return $resultado;
    }
}