<?php
/**
 * Nodalblock
 * User: Sergio
 * Date: 31/07/2019
 * Time: 11:01
 */

namespace Nononsense\HomeBundle\Controller;


use Nononsense\HomeBundle\Entity\Documents;
use Nononsense\HomeBundle\Entity\RecordsDocuments;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nononsense\HomeBundle\Form\Type as FormProveedor;

use Nononsense\UtilsBundle\Classes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RecordsController extends Controller
{

    public function createAction($id)
    {

        $document = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:Documents')
            ->find($id);

        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $record = new RecordsDocuments();
        $record->setIsActive(true);
        $record->setStatus(0); // Estado especial de creación
        $record->setDescription("");
        $record->setMasterDataValues("");
        $record->setObservaciones("");
        $record->setYear(date("Y"));
        $record->setUserCreatedEntiy($user);
        $record->setDocument($document);
        $record->setDependsOn(0);
        $record->setToken("");
        $record->setStepDataValue("");
        $record->setFiles("");

        $record->setType($document->getType());
        $em->persist($record);
        $em->flush();
        
        $route = $this->container->get('router')->generate('nononsense_records_link', array("id" => $record->getId()));
        
        return $this->redirect($route);
    }

    public function linkAction($id)
    {
        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->find($id);

        $baseUrl = $this->getParameter("cm_installation");

        $options = array();

        $options['template'] = $record->getDocument()->getPlantillaId();
        
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

        $options['custom'] = json_encode($customObject);

        if ($record->getStatus() == 4 || $record->getStatus() == 5) {
            // Abrir para validar
            $options['responseURL'] = $baseUrl . "control_validacion/" . $id . "/";
            $options['prefix'] = 'v';

            $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/validacion.js");
            $validacionURL1 = $baseUrl . "js/js_templates/validacion.js?v=" . $versionJS;

        } else if ($record->getStatus() == -1 || $record->getStatus() == 0) {
            // abrir para editar
            $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/activity.js");
            $validacionURL1 = $baseUrl . "js/js_templates/activity.js?v=" . $versionJS;

            $options['prefix'] = 'u';
            $options['responseURL'] = $baseUrl . "dataRecords/redirectFromData/" . $id . "/";
        }


        if ($validacionURL2 != "") {
            $options['requestExternalJS'] = $validacionURL1 . ";" . $validacionURL2 . "?v=" . time();
        } else {
            $options['requestExternalJS'] = $validacionURL1;
        }


        $options['requestExternalJS'] = $validacionURL1;
        $url_resp_data_uri = $baseUrl . 'dataRecords/returnData/' . $id;
        $url_requesetData = $baseUrl . 'dataRecords/requestData/' . $record->getId();
        $options['responseDataURI'] = $url_resp_data_uri;
        $options['requestDataURI'] = $url_requesetData;

        $options['enduserid'] = 'pruebadeusuario: ' . $this->getUser()->getName();

        $url_edit_documento = $this->get('app.sdk')->previewDocument($options);

        /*
         * Bloquear el registro
         */
        /*$record->setInEdition(1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($registro);
        $em->flush();*/


        return $this->redirect($url_edit_documento);
    }

    public function RequestDataAction($id)
    {
        /*
         * Get Value from JSON to put into document
         */

        // get the InstanciasSteps entity
        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->find($id);

        $document = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:Documents')
            ->find($record->getDocument());



        $recordMasterData = $record->getMasterDataValues();
        $recordMasterDataJSON = json_decode($recordMasterData);
        $em = $this->getDoctrine()->getManager();

        $data = new \stdClass();
        $varValues = new \stdClass();
        $data->varValues = $varValues;

        if ($record->getStatus() == 0) {
            // first usage
            $data = new \stdClass();
            $varValues = new \stdClass();
            $varValues->u_id_cumplimentacion = array($record->getId());

            if (isset($recordMasterDataJSON)) {
                foreach ($recordMasterDataJSON as $variable) {
                    $varName = $variable->nameVar;
                    $varValue = $variable->valueVar;

                    $varValues->{$varName} = $varValue;
                }
            }

            $varValues->historico_steps = array("     ");
            $data->varValues = $varValues;


        } else {


        }
        /*
        var_dump(json_encode($data));
                exit;
        */
        if(isset($data->custom)){
            unset($data->custom);
        }

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent(json_encode($data));

        return $response;
    }

    public function returnDataAction($id)
    {

        // get the InstanciasSteps entity
        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->find($id);

        $document = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:Documents')
            ->find($record->getDocument());

        $request = Request::createFromGlobals();
        $postData = $request->request->all();

        // WARGING SECURITY MISSING DONT FORGET GUS

        $data = "{}";
        foreach ($postData as $key => $value) {
            ${$key} = $value;
        }
        $dataJSON = json_decode($data);

        $em = $this->getDoctrine()->getManager();


        /*
         * Si el status ya es 1 es que lo que se está haciendo es validar. Ya que no se permite un segundo rellenado.
         * Se pisan los valores y ahora el status es 2 y el workflow pasa a validado 3 . Y su validación a "validada" 2
         */

        $now = new \DateTime();
        $now->modify("+2 hour"); // Ver tema de horarios usos

        /*
         * Actualizar metaData según variables
         */
        $varValues = $dataJSON->varValues;

        $data = json_encode($dataJSON);
        $record->setStepDataValue($data);
        $record->setStatus(1);
        $record->setToken($dataJSON->token);

        $em->persist($record);

        $em->flush();

        //return $this->render('NononsenseDataDocumentBundle:Default:index.html.twig', array('name' => $instancia_step_id));
        $responseAction = new Response();
        $responseAction->setStatusCode(200);
        $responseAction->setContent("OK");
        return $responseAction;
    }

    public function redirectFromDataAction($id, $action, $urlaux)
    {
        /*
         * cerrar
         * cancelar
         * parcial
         * enviar
         */
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->find($id);

        
        $record->setInEdition(0);
        $documentName = $record->getDocument()->getName();
        //var_dump($action);
        $route = $this->container->get('router')->generate('nononsense_home_homepage');

        if ($action == 'cancelar') {
            $record->setStatus(3);
            $em->persist($record);
            $em->flush();

            return $this->render('NononsenseHomeBundle:Contratos:record_cancel_interface.html.twig', array(
                "documentName" => $documentName,
                "stepid" => $id
            ));

        } elseif ($action == 'parcial') {
            if($record->getStatus()==0 || $record->getStatus()==1 || $record->getUserCreatedEntiy()==$user){
                $record->setStatus(1);
                $em->persist($record);
                $em->flush();
                

                $stepData = $record->getStepDataValue();
                $stepDataJSON = json_decode($stepData);

                $validations = $stepDataJSON->validations;
                $percentageCompleted = $validations->percentage;
                $validated = $validations->validated;

                /*
                 * Revisar si ha habido algún cambio en las variables para que muestre el campo de texto.
                 */
                $devolucion = 0;


                return $this->render('NononsenseHomeBundle:Contratos:record_completed.html.twig', array(
                    "documentName" => $documentName,
                    "percentageCompleted" => $percentageCompleted,
                    "validated" => $validated,
                    "id" => $id,
                    "devolucion" => $devolucion
                ));
            }

        } elseif ($action == 'enviar') {
            $stepData = $record->getStepDataValue();
            $stepDataJSON = json_decode($stepData);

            $validations = $stepDataJSON->validations;
            $percentageCompleted = $validations->percentage;
            $validated = $validations->validated;

            if($validations->percentage==100){
                $record->setStatus(2);
                $em->persist($record);
                $em->flush();
            

                /*
                 * Revisar si ha habido algún cambio en las variables para que muestre el campo de texto.
                 */
                $devolucion = 0;


                return $this->render('NononsenseHomeBundle:Contratos:record_completed.html.twig', array(
                    "documentName" => $documentName,
                    "percentageCompleted" => $percentageCompleted,
                    "validated" => $validated,
                    "id" => $id,
                    "devolucion" => $devolucion
                ));
            }

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

    public function saveAndSendAction($id, Request $request)
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

}