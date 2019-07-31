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
    public function registroCompletadoAction($stepid)
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


        return $this->render('NononsenseHomeBundle:Contratos:registro_completado.html.twig', array(
            "documentName" => $documentName,
            "percentageCompleted" => $percentageCompleted,
            "validated" => $validated,
            "stepid" => $stepid,
            "devolucion" => $devolucion
        ));
    }

    public function linkAction($stepid, $form, $revisionid)
    {
        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($stepid);

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

        $registro = $step->getInstanciaWorkflow();
        /*
         * Saber si hay algún precreation
         */
        $precreationValue = $registro->getMasterWorkflowEntity()->getPrecreation();
        if ($precreationValue != "default") {
            $customObject->activate = 'activate';
        }
        $options['custom'] = json_encode($customObject);

        if ($registro->getStatus() == 4 || $registro->getStatus() == 5) {
            // Abrir para validar
            $options['responseURL'] = $baseUrl . "control_validacion/" . $stepid . "/";
            $options['prefix'] = 'v';

            $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/validacion.js");
            $validacionURL1 = $baseUrl . "js/js_templates/validacion.js?v=" . $versionJS;

        } else if ($registro->getStatus() == -1 ||
            $registro->getStatus() == 0) {
            // abrir para editar
            $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/activity.js");
            $validacionURL1 = $baseUrl . "js/js_templates/activity.js?v=" . $versionJS;

            $options['prefix'] = 'u';
            $options['responseURL'] = $baseUrl . "control_elaboracion/" . $stepid . "/";


        } else {
            // No abrir para editar ... usar el método show
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
        $url_requesetData = $baseUrl . 'data/requestData/' . $step->getId();

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
        $registro->setStatus(0);

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

        return $this->render('NononsenseHomeBundle:Contratos:registro_guardadoenviado.html.twig', array(
            "documentName" => $documentName,
        ));

    }

    public function controlElaboracionAction($stepid, $action, $urlaux)
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

            $route = $this->container->get('router')->generate('nononsense_contrato_registro_completado', array('stepid' => $stepid));

        } elseif ($action == 'enviar') {
            $registro->setStatus(2);
            $route = $this->container->get('router')->generate('nononsense_contrato_registro_completado', array('stepid' => $stepid));

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

    public function controlValidacionAction($stepid, $action, $urlaux)
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
            $route = $this->container->get('router')->generate('nononsense_registro_cancelar_verficiacion', array('stepid' => $stepid));

        } elseif ($action == 'verificar') {
            $registro->setStatus(7);
            $route = $this->container->get('router')->generate('nononsense_registro_verificar', array('stepid' => $stepid));


        } elseif ($action == 'devolver') {

            $route = $this->container->get('router')->generate('nononsense_registro_devolver_edicion', array('stepid' => $stepid));


        } else if ($action == 'cerrar') {

            $route = base64_decode($urlaux);


        } else {
            // Error... go inbox
            echo 'No deberías haber llegado aquí. Error desconocido';
            var_dump($action);
            exit;

        }

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
                        $lastValue = trim(implode("",$lastVarValues->{$prop})); // Para que funcione en los "checboxes" y "radioButton" habría que hacer un implode + trim
                        // if lastValue es un valor vacío no haría falta hacer un "modificado"
                        $currentValue = trim(implode("",$value));

                        if($lastValue != ""){
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
}