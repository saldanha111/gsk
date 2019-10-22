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
use Nononsense\HomeBundle\Entity\DocumentsSignatures;
use Nononsense\HomeBundle\Entity\RecordsSignatures;
use Nononsense\HomeBundle\Entity\Types;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nononsense\HomeBundle\Form\Type as FormProveedor;

use Nononsense\UtilsBundle\Classes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Nononsense\UtilsBundle\Classes\Auxiliar;
use Nononsense\UtilsBundle\Classes\Utils;

class RecordsController extends Controller
{
    public function listAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $Egroups = $this->getDoctrine()
            ->getRepository('NononsenseGroupBundle:GroupUsers')
            ->findBy(array("user"=>$user));
        
        $filters=array();
        $filters2=array();
        $types=array();

        $filters["user"]=$user;
        $filters2["user"]=$user;

        $array_item["suser"]["id"]=$user->getId();
        foreach($Egroups as $group){
            $groups[]=$group->getGroup()->getId();
        }
        
        if($groups){
            $filters["groups"]=$groups;
            $filters2["groups"]=$groups;
        }

        if($request->get("pending_for_me")){
            $filters["pending_for_me"]=$request->get("pending_for_me");
            $filters2["pending_for_me"]=$request->get("pending_for_me");
        }

        if(!$request->get("export_excel")){
            if($request->get("page")){
                $filters["limit_from"]=$request->get("page")-1;
            }
            else{
                $filters["limit_from"]=0;
            }
            $filters["limit_many"]=15;
        }
        else{
            $filters["limit_from"]=0;
            $filters["limit_many"]=99999999999;
        }

        if($request->get("content")){
            $filters["content"]=$request->get("content");
            $filters2["content"]=$request->get("content");
        }

        if($request->get("name")){
            $filters["name"]=$request->get("name");
            $filters2["name"]=$request->get("name");
        }

        if($request->get("type")){
            $filters["type"]=$request->get("type");
            $filters2["type"]=$request->get("type");
        }

        if($request->get("status")){
            $filters["status"]=$request->get("status");
            $filters2["status"]=$request->get("status");
        }

        if($request->get("from")){
            $filters["from"]=$request->get("from");
            $filters2["from"]=$request->get("from");
        }

        if($request->get("until")){
            $filters["until"]=$request->get("until");
            $filters2["until"]=$request->get("until");
        }

        $array_item["states"][1]="Pendiente de completar";
        $array_item["states"][2]="Pendiente de firma";
        $array_item["states"][3]="Completado";
        $array_item["states"][4]="Cancelado";
        $array_item["suser"]["groups"]=$groups;
        $array_item["filters"]=$filters;
        $array_item["types"] = $this->getDoctrine()->getRepository(Types::class)->findAll();
        $array_item["items"] = $this->getDoctrine()->getRepository(RecordsDocuments::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(RecordsDocuments::class)->count($filters2,$types);

        $url=$this->container->get('router')->generate('nononsense_records');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        return $this->render('NononsenseHomeBundle:Contratos:records.html.twig',$array_item);
    }


    public function createAction($id)
    {

        $document = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:Documents')
            ->find($id);


        /* Para testear */

        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $record = new RecordsDocuments();
        $record->setIsActive(true);
        $record->setStatus(0); // Estado especial de creación
        $record->setDescription("");
        $record->setMasterDataValues("");
        $record->setCreated(new \DateTime());
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

    /* Donde Generamos el link que llama a docxpresso */
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

        if ($record->getStatus() == 2 || $record->getStatus() == 3) {
            // Abrir para validar
            $options['responseURL'] = $baseUrl . "records/redirectFromData/" . $id . "/";
            
            if ($record->getStatus() == 2){
                if($record->getType()->getId()==1){
                    $options['prefix'] = 'resp_alm';
                }
                else{
                    $options['prefix'] = 'def_v';
                }

                $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/documentValidacion.js");
                $validacionURL1 = $baseUrl . "js/js_templates/documentValidacion.js?v=" . $versionJS;   
            }
            else{
                $options['prefix'] = 'closed_';
                $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/documentClosed.js");
                $validacionURL1 = $baseUrl . "js/js_templates/documentClosed.js?v=" . $versionJS; 
            }

        } else if ($record->getStatus() == -1 || $record->getStatus() == 0  || $record->getStatus() == 1   || $record->getStatus() == 5) {
            // abrir para editar
            $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/document.js");
            $validacionURL1 = $baseUrl . "js/js_templates/document.js?v=" . $versionJS;

            //$options['prefix'] = 'u';
            $options['responseURL'] = $baseUrl . "records/redirectFromData/" . $id . "/";
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

    /* Función a la que llama docxpresso antes de abrir la vista previa para saber si necesita cargar datos en las plantillas */
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
        


        $stepMasterData = $record->getStepDataValue();
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
            $varValues->numero_solicitud = array($record->getId());

            if (isset($recordMasterDataJSON)) {
                foreach ($recordMasterDataJSON as $variable) {
                    $varName = $variable->nameVar;
                    $varValue = $variable->valueVar;

                    $varValues->{$varName} = $varValue;
                }
            }

            $varValues->historico_steps = array("     ");
            $data->varValues = $varValues;

            $data->varValues->require_sap=""; 
        } 
        else {
            // Data Ingegrity other usage

            $stepDataValue = $record->getStepDataValue();
            $data = json_decode($stepDataValue);

            $firmas = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                ->findBy(array("record" => $record));

            if (!empty($firmas)) {
                $data->varValues->dxo_gsk_audit_trail_bloque = array("No");
                $data->varValues->dxo_gsk_firmas_bloque = array("Si");
                $data->varValues->dxo_gsk_firmas = array($this->_construirFirmas($firmas));
            }

            if($record->getType()->getId()==1){
                if($record->getStatus()==2 || $record->getStatus()==3){
                    $signatures = $this->getDoctrine()
                        ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                        ->findBy(array("record"=> $record->getId(), "firma" => NULL));
                    if(count($signatures)<=1){
                        $data->varValues->require_sap="1"; 
                    }
                    else{
                        $data->varValues->require_sap=""; 
                    }
                }
                else{
                    $data->varValues->require_sap=""; 
                }
            }

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

    /* Función a la que se conecta doxpresso para mandar los datos - Webhook*/
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

        /*
         * Actualizar metaData según variables
         */
        $varValues = $dataJSON->varValues;

        $data = json_encode($dataJSON);
        $record->setStepDataValue($data);
        $record->setToken($dataJSON->token);

        $stepData = $record->getStepDataValue();
        $stepDataJSON = json_decode($stepData);

        $validations = $stepDataJSON->validations;
        $percentageCompleted = $validations->percentage;
        $validated = $validations->validated;


        $signatures = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsSignatures')
            ->findBy(array("record"=> $record->getId()));

        /* Si no está metido el workflow de las firmas lo metemos */
        if(!$signatures || $record->getComments()!=NULL){
            $record->setComments(NULL);
            if($validated && $percentageCompleted==100){
                if($signatures){
                    $number_signatures=count($signatures);
                }
                else{
                    $number_signatures=0;
                }
                switch($record->getType()->getId()){
                    //Caso especial para los registros de tipo Albarán Almacén
                    case "1": $this->albaranAlmacen($record,$number_signatures);
                            break;
                    default:
                            $record->setModified(date("Y-m-d"));
                            $baseSignatures = $this->getDoctrine()
                                ->getRepository('NononsenseHomeBundle:DocumentsSignatures')
                                ->findBy(array("document"=> $record->getDocument()));

                            $next=1;
                            if($record->getDocument()->getSignCreator()){
                                $sign = new RecordsSignatures();
                                $sign->setUserEntiy($record->getUserCreatedEntiy());
                                $sign->setRecord($record);
                                $sign->setNumber(0+$number_signatures);
                                $sign->setAttachment($record->getDocument()->getAttachment());
                                $sign->setNext($next);
                                $sign->setCreated(new \DateTime());
                                $em->persist($sign); 
                                $next=0;
                            }
                            foreach ($baseSignatures as $baseSignaturre) {
                                $sign = new RecordsSignatures();
                                $sign->setUserEntiy($baseSignaturre->getUserEntiy());
                                $sign->setGroupEntiy($baseSignaturre->getGroupEntiy());
                                $sign->setRecord($record);
                                $sign->setNumber($baseSignaturre->getNumber()+$number_signatures);
                                $sign->setAttachment($baseSignaturre->getAttachment());
                                $sign->setEmail($baseSignaturre->getEmail());
                                $sign->setNext($next);
                                $sign->setCreated(new \DateTime());
                                $em->persist($sign);
                                $next=0;
                            }
                            break;
                }
            }
        }

        $em->persist($record);

        $em->flush();

        //return $this->render('NononsenseDataDocumentBundle:Default:index.html.twig', array('name' => $instancia_step_id));
        $responseAction = new Response();
        $responseAction->setStatusCode(200);
        $responseAction->setContent("OK");
        return $responseAction;
    }

    /* Pagina a la que vamos tras volver de docxpresso */
    public function redirectFromDataAction($id, $action, $urlaux)
    {
        /*
         * cerrar
         * cancelar
         * parcial
         * enviar
         */
        $urlaux=str_replace("--", "/", $urlaux);
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->find($id);

        if(!$record){
            $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error desconocido al intentar guardar los datos del documento"
            );
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }

        
        $record->setInEdition(0);
        $documentName = $record->getDocument()->getName();
        //var_dump($action);
        $route = $this->container->get('router')->generate('nononsense_home_homepage');

        if ($action == 'cancelar') {
            $record->setStatus(4);
            $em->persist($record);
            $em->flush();

            return $this->render('NononsenseHomeBundle:Contratos:record_cancel_interface.html.twig', array(
                "documentName" => $documentName,
                "stepid" => $id
            ));
        } elseif ($action == 'devolver') {
            $signature = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                ->findOneBy(array("record"=>$record,"next"=>1));

            if(!$signature){
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "No puede firmar este documento. Es posible que el documento ya haya sido firmado por otro usuario"
                );
                return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
            }

            $can_sign=0;
            if($signature->getGroupEntiy()){
                $isGroup = $this->getDoctrine()
                ->getRepository('NononsenseGroupBundle:GroupUsers')
                ->findOneBy(array("group"=>$signature->getGroupEntiy(),"user"=>$user));
                if($isGroup){
                    $can_sign=1;
                }
            }
            else{
                if($signature->getUserEntiy()->getId()==$user->getId()){
                    $can_sign=1;
                }  
            }

            if(!$can_sign){
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "No puede firmar este documento. Es posible que el documento ya haya sido firmado por otro usuario"
                );
                return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
            }

            if($record->getStatus()!=2){
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "No puede firmar este documento. Es posible que el documento ya haya sido firmado por otro usuario"
                );
                return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
            }

            return $this->render('NononsenseHomeBundle:Contratos:record_return_interface.html.twig', array(
                "documentName" => $documentName,
                "id" => $id
            ));
            
        } elseif ($action == 'parcial') {
            if(($record->getStatus()==0 || $record->getStatus()==1) && $record->getUserCreatedEntiy()==$user){
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

        } elseif ($action == 'enviar' || $action == 'verificar') {
            $stepData = $record->getStepDataValue();
            $stepDataJSON = json_decode($stepData);

            $validations = $stepDataJSON->validations;
            $percentageCompleted = $validations->percentage;
            $validated = $validations->validated;

            if($validations->percentage==100){
                $total_signatures = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                    ->findOneBy(array("record"=>$record));

                if($total_signatures){
                    if(!$record->getDocument()->getSignCreator() && ($record->getStatus()==0 || $record->getStatus()==1 || $record->getStatus()==5)){
                        $can_sign=0;
                        if($record->getDocument()->getAttachment()){
                            $anexo=1;
                            $validated=1;
                            $record->setStatus(5);
                        }
                        else{
                            $anexo=0;
                            $record->setStatus(2);
                        }
                    }
                    else{
                        $signature = $this->getDoctrine()
                            ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                            ->findOneBy(array("record"=>$record,"next"=>1));

                        if(!$signature){
                            $this->get('session')->getFlashBag()->add(
                                'error',
                                "No puede firmar este documento. Es posible que el documento ya haya sido firmado por otro usuario"
                            );
                            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
                        }

                        $can_sign=0;
                        if($signature->getGroupEntiy()){
                            $isGroup = $this->getDoctrine()
                            ->getRepository('NononsenseGroupBundle:GroupUsers')
                            ->findOneBy(array("group"=>$signature->getGroupEntiy(),"user"=>$user));
                            if($isGroup){
                                $can_sign=1;
                            }
                        }
                        else{
                            if($signature->getUserEntiy()->getId()==$user->getId()){
                                $can_sign=1;
                            }  
                        }

                        if(!$can_sign){
                            if($record->getStatus()==2){
                                $this->get('session')->getFlashBag()->add(
                                    'error',
                                    "No puede firmar este documento. Es posible que el documento ya haya sido firmado por otro usuario"
                                );
                                return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
                            }
                            else{
                                $can_sign=0;
                            }
                        }

                        $signature = $this->getDoctrine()
                        ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                        ->findOneBy(array("record"=>$record,"next"=>1));
                        
                        if($signature->getAttachment()){
                            $anexo=1;
                        }
                        else{
                            $anexo=0;
                        }
                        $record->setStatus(2);
                    }
 
                }
                else{
                    // Para aquellos documentos donde no hay workflow y el primer firmante no firma
                    if(!$record->getDocument()->getSignCreator() && ($record->getStatus()==0 || $record->getStatus()==1 || $record->getStatus()==5)){
                        $can_sign=0;
                        if($record->getDocument()->getAttachment()){
                            $anexo=1;
                            $validated=1;
                            $record->setStatus(5);
                        }
                        else{
                            $anexo=0;
                            $record->setStatus(3);
                        }
                        $can_sign=0;

                    }
                    else{
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            "No puede firmar este documento. Es posible que el documento ya haya sido firmado por otro usuario"
                        );
                        return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
                    }
                }

                
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
                    "devolucion" => $devolucion,
                    "anexo" => $anexo,
                    "can_sign" => $can_sign
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

    /* Proceso donde firmamos los documentos */
    public function saveAndSendAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $comentario = $request->query->get('comment');

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->findOneBy(array("id" => $id));

        if($record->getStatus()!=2 && $record->getStatus()!=5){
            $this->get('session')->getFlashBag()->add(
                'error',
                "No puede firmar este documento. Es posible que el documento ya haya sido firmado por otro usuario"
            );
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }

        if($record->getStatus()==2){
            $signature = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                ->findOneBy(array("record"=>$record,"next"=>1));

            if(!$signature){
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "No puede firmar este documento. Es posible que el documento ya haya sido firmado por otro usuario"
                );
                return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
            }

            $can_sign=0;
            if($signature->getGroupEntiy()){
                $isGroup = $this->getDoctrine()
                ->getRepository('NononsenseGroupBundle:GroupUsers')
                ->findOneBy(array("group"=>$signature->getGroupEntiy(),"user"=>$user));
                if($isGroup){
                    $can_sign=1;
                }
            }
            else{
                if($signature->getUserEntiy()->getId()==$user->getId()){
                    $can_sign=1;
                }  
            }

            if(!$can_sign){
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "No puede firmar este documento. Es posible que el documento ya haya sido firmado por otro usuario"
                );
                return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
            }

            if($signature->getAttachment()){
                if(!$request->files->get('anexo')){
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        "Es necesario adjuntar un anexo para poder firmar este documento"
                    );
                    return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
                }
                else{
                    $file = $this->uploadFile($request, $record->getId());
                    $record->setFiles($file["name"]);
                }
            }

            $signature->setFirma($request->get('firma'));
            $signature->setNext(0);
            $signature->setUserEntiy($user);
            $signature->setModified(new \DateTime());

        

            $signature2 = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                ->findOneBy(array("record"=>$record,"firma"=>null,"next" => 0), array('number' => 'ASC'));
        }
        else{
            if(!$request->files->get('anexo')){
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "Es necesario adjuntar un anexo para poder firmar este documento"
                );
                return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
            }
            else{
                $file = $this->uploadFile($request, $record->getId());
                $record->setFiles($file["name"]);
            }

            $record->setStatus(2);
            $signature = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                ->findOneBy(array("record"=>$record,"firma"=>null,"next" => 1), array('number' => 'ASC'));
            $signature2=$signature;
        }

        if($signature2){
            $signature2->setNext(1);
            $em->persist($signature2);
            $send_email=0;
            if($signature2->getUserEntiy()){
                $send_email=1;
                $emails[]=$signature2->getUserEntiy()->getEmail();
            }
            else{
                $send_email=1;
                if($signature2->getEmail()){
                   $emails[]=$signature2->getEmail();
                }
                else{
                    $aux_users = $em->getRepository(GroupUsers::class)->findBy(["group" => $signature2->getGroupEntiy()]);
                    foreach ($aux_users as $aux_user) {
                        $emails[]=$aux_user->getUser()->getEmail();
                    }
                }
            }

            if($send_email==1){
                foreach($emails as $email){
                    $subject="Documento pendiente de firma";
                    $mensaje='El Documento con ID '.$record->getId().' está pendiente de revisión por su parte. Para poder revisarlo puede acceder a "Mis documentos pendientes", buscar el documento y pulsar en Firmar';
                    $baseURL=$this->container->get('router')->generate('nononsense_records_edit', array("id" => $record->getId()),TRUE);
                    
                    $this->_sendNotification($email, $baseURL, "", "", $subject, $mensaje);
                }
            }
        }
        else{
            if($signature){
                $record->setLastSign($signature->getId());
            }
            $record->setStatus(3);
        }
        
        


        $firmas = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                ->findBy(array("record" => $record));

        $stepDataValues = $record->getStepDataValue();
        $stepDataValuesJSON = json_decode($stepDataValues);

        $stepDataValuesJSON->varValues->dxo_gsk_audit_trail_bloque = array("No");
        $stepDataValuesJSON->varValues->dxo_gsk_firmas_bloque = array("Si");


        $stepDataValuesJSON->varValues->dxo_gsk_firmas[0] = $this->_construirFirmas($firmas);


        $data = json_encode($stepDataValuesJSON);

        $record->setStepDataValue($data);
        $em->persist($record);
        if($signature){
            $em->persist($signature);
        }
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_record_sent', array("id" => $record->getId()));
        
        return $this->redirect($route);

        
    }

    /* Proceso donde firmamos los documentos */
    public function returnAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $comentario = $request->get('comment');

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->findOneBy(array("id" => $id, "status" => 2));

        if(!$record){
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }

        $signature = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsSignatures')
            ->findOneBy(array("record"=>$record,"next"=>1));

        if(!$signature){
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }

        $can_sign=0;
        if($signature->getGroupEntiy()){
            $isGroup = $this->getDoctrine()
            ->getRepository('NononsenseGroupBundle:GroupUsers')
            ->findOneBy(array("group"=>$signature->getGroupEntiy(),"user"=>$user));
            if($isGroup){
                $can_sign=1;
            }
        }
        else{
            if($signature->getUserEntiy()->getId()==$user->getId()){
                $can_sign=1;
            }  
        }

        if(!$can_sign){
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }

        if($record->getStatus()!=2){
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }


        $signature->setFirma($request->get('firma'));
        $signature->setComments($request->get('comment'));
        $signature->setNext(0);
        $signature->setUserEntiy($user);
        $signature->setModified(new \DateTime());

        $record->setStatus(1);
        $record->setComments($request->get('comment'));

        $signature2 = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsSignatures')
            ->findOneBy(array("record"=>$record), array('number' => 'ASC'));

        if($signature2){
            
            $email=$signature2->getUserEntiy()->getEmail();
            

            $subject="Documento devuelto para revisión";
            $mensaje='El Documento con ID '.$record->getId().' ha sido devuelto por el usuario '.$user->getName().' y está pendiente de revisión.<br>La razón por la que se ha devuelto el documento es la siguiente: '.$request->get('comment').'.<br><br> Para poder revisar el documento puede acceder a la sección de "Mis documentos pendientes", buscar el documento y pulsar en Completar Documento';
            $baseURL=$this->container->get('router')->generate('nononsense_records_edit', array("id" => $record->getId()),TRUE);
                
            $this->_sendNotification($email, $baseURL, "", "", $subject, $mensaje);

        }
        else{
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }
        
        
        

        $firmas = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                ->findBy(array("record" => $record));

        $stepDataValues = $record->getStepDataValue();
        $stepDataValuesJSON = json_decode($stepDataValues);

        $stepDataValuesJSON->varValues->dxo_gsk_audit_trail_bloque = array("No");
        $stepDataValuesJSON->varValues->dxo_gsk_firmas_bloque = array("Si");


        $stepDataValuesJSON->varValues->dxo_gsk_firmas[0] = $this->_construirFirmas($firmas);


        $data = json_encode($stepDataValuesJSON);

        $record->setStepDataValue($data);
        $em->persist($record);
        $em->persist($signature);

        $remove_signatures = $em->getRepository(RecordsSignatures::class)->findBy(["record" => $record, "firma" => NULL]);
        
        foreach ($remove_signatures as $remove_signature) {
            if($signature->getId()!=$remove_signature->getId()){
                $em->remove($remove_signature);
            }
        }

        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_record_sent', array("id" => $record->getId()));
        
        return $this->redirect($route);

        
    }

    public function sentAction($id, Request $request)
    {
        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->findOneBy(array("id" => $id));
            
        $stepData = $record->getStepDataValue();
        $stepDataJSON = json_decode($stepData);

        $documentName = $record->getDocument()->getName();
        $validations = $stepDataJSON->validations;
        $percentageCompleted = $validations->percentage;

        return $this->render('NononsenseHomeBundle:Contratos:record_sent.html.twig', array(
                    "documentName" => $documentName,
                    "percentageCompleted" => $percentageCompleted,
                    "id" => $id,
        ));
    }

    public function editAction($id)
    {

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->find($id);

        $route = $this->container->get('router')->generate('nononsense_records_link', array("id" => $record->getId()));
        return $this->redirect($route);
    }

    public function fileAction($id)
    {
        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->find($id);

        return new BinaryFileResponse($this->get('kernel')->getRootDir().$record->getFiles());
    }

    public function downloadPdfAction($id)
    {
        /*
         *
         */
        
        $dataResponse = new \stdClass();
        $dataResponse->id = $id;

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->find($id);


        $name = $record->getDocument()->getName();

        

        $template = $record->getDocument()->getPlantillaId();


        $stepDataValues = $record->getStepDataValue();
        $stepDataValuesJSON = json_decode($stepDataValues);

        $rootdir = $this->get('kernel')->getRootDir();
        $rootdirFiles = $rootdir . "/files/documents/".$id;

        $fs = new Filesystem();
        if(!$fs->exists($rootdirFiles))
        {
            $fs->mkdir($rootdirFiles);
        }

        $filenamedownloadpdf = $rootdirFiles . "/" . $name . $id .".pdf";
        $filenamepdf = $name . $id;
        
        $aux = new Auxiliar();
        $utils = new Utils();
        
        $options = array();
        $options['template'] = (int)$template;
        $options['documentName'] = $filenamepdf;
        $options['response'] = 'json';

        $dataDXO = json_encode($stepDataValuesJSON);

        $options['data'] = $dataDXO;
        $options['docFormat'] = 'pdf';
        $options['name'] = $filenamepdf . '.pdf';
        $options['reference'] = $filenamepdf;

        $opt = $this->get('app.sdk')->base64_encode_url_safe(json_encode($options));
        //generate security info
        $uniqid = uniqid() . rand(99999, 9999999);
        $timestamp = time();
        $control = $template . '-';
        $control .= $timestamp . '-' . $uniqid;
        $control .= '-' . $opt;

        $dataKey = sha1($control, true);
        $masterKey = $this->getParameter('apikey');
        $APIKEY = bin2hex($utils->sha1_hmac($masterKey, $dataKey));

        //we should now redirect to Docxpresso
        $url = $this->getParameter('docxpresso_installation') . '/documents/requestDocument/' . $template;
        $addr = $url . '?';
        $addr .= 'uniqid=' . $uniqid . '&';
        $addr .= 'timestamp=' . $timestamp . '&';
        $addr .= 'APIKEY=' . $APIKEY;

        $curlResponse = $aux->curlRequest($addr, $opt);

        if ($curlResponse['status'] != 'OK') {
            //handle the error
            //exit('error');
            echo "</br>Error</br>";
            print_r("</br>" . $curlResponse['externalData']);

            $responseAction = new Response();
            $responseAction->setStatusCode(500);
            $dataResponse->feedback = "Error en la creación del fichero";
            $responseAction->setContent(json_encode($dataResponse));
        } else {
            //print_r($curlResponse);

            $response = json_decode($curlResponse['externalData']);
            //print_r($response);

            $usageId = $response->usageId;
            $token = $response->token;
            $name = $response->name;

            $dataDonwload = array();
            $dataDonwload['id'] = $template;
            $dataDonwload['token'] = $token;
            $documentLink = $this->get('app.sdk')->downloadDocument($dataDonwload);

            if (file_exists($filenamedownloadpdf)) {
                unlink($filenamedownloadpdf);
            }

            if (file_put_contents($filenamedownloadpdf, fopen($documentLink, 'r')) === FALSE) {
                $responseAction = new Response();
                $responseAction->setStatusCode(500);
                $dataResponse->feedback = "Error en la descarga del fichero";
                $responseAction->setContent(json_encode($dataResponse));

            } else {
                return new BinaryFileResponse($filenamedownloadpdf);
            }
        }

        return $responseAction;
    }

    private function albaranAlmacen($record,$number_signatures){

        $em = $this->getDoctrine()->getManager();
        $baseSignatures = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:DocumentsSignatures')
            ->findBy(array("document"=> $record->getDocument()));

        $data = $record->getStepDataValue();
        $dataJson = json_decode($data);

        $logistica=0;
        $calidad=0;
        $almacen=1;
        $responsable_almacen=1;
        $anexo=0;

        $step3=0;
        $step4=0;
        
        foreach($dataJson->varValues->u_tipo_material as $key => $material_value){
            if(urldecode($dataJson->varValues->u_check3[1])!="" && urldecode($dataJson->varValues->u_check4[1])==""){
                $anexo=1;
            }

            if(urldecode($dataJson->varValues->u_check4[2])!="" && (urldecode($dataJson->varValues->u_check3[3])!="" || urldecode($dataJson->varValues->u_check3[4])!="") && urldecode($dataJson->varValues->u_tipo_material[$key])!="ZINT REG" && urldecode($dataJson->varValues->u_tipo_material[$key])!="ZCOM NO Impreso"){
                $anexo=1;
            }

            switch(urldecode($dataJson->varValues->u_tipo_material[$key])){
                case "ZINT REG":
                case "ZINT PRU":
                    $step4=1;
                    break;
                case "ZNBW":
                    $almacen=1;
                    $responsable_almacen=1;
                    break;
                case "ZCOM Impreso":
                    if(urldecode($dataJson->varValues->u_check2[$key])=="Si"){
                        $step4=1;
                    }
                    else{
                        $logistica=1;
                        $step4=1;
                    }
                    break;
                default:
                    $step3=1;
                    break;
            }

            if($step3){
                if(urldecode($dataJson->varValues->u_check3[2])!="" && urldecode($dataJson->varValues->u_check4[0])==""){
                    $almacen=1;
                    $responsable_almacen=1;
                }
                else{
                    $logistica=1;
                    $step4=1;
                }
            }

            /* Miramos Logística */
            if($step4){
                if(urldecode($dataJson->varValues->u_check4[0])!="" && urldecode($dataJson->varValues->u_check3[2])!=""){
                    $calidad=1;
                }
                else{
                    $almacen=1;
                    $responsable_almacen=1;
                }
            }
        }

        $next=1;
        if($record->getDocument()->getSignCreator()){
            $sign = new RecordsSignatures();
            $sign->setUserEntiy($record->getUserCreatedEntiy());
            $sign->setRecord($record);
            $sign->setNumber(0+$number_signatures);
            $sign->setAttachment($anexo);
            $sign->setNext($next);
            $sign->setCreated(new \DateTime());
            $em->persist($sign); 
            $next=0;
        }

        foreach ($baseSignatures as $key => $baseSignaturre) {
            if(($key==0 && $logistica) || ($key==1 && $calidad) || ($key==2 && $almacen) || ($key==3 && $responsable_almacen)){
                $sign = new RecordsSignatures();
                $sign->setUserEntiy($baseSignaturre->getUserEntiy());
                $sign->setGroupEntiy($baseSignaturre->getGroupEntiy());
                $sign->setRecord($record);
                $sign->setNumber($baseSignaturre->getNumber()+$number_signatures);
                $sign->setAttachment($baseSignaturre->getAttachment());
                $sign->setNext($next);
                $sign->setEmail($baseSignaturre->getEmail());
                $sign->setCreated(new \DateTime());
                $em->persist($sign);
                $next=0;
            }
        }

        $em->flush();
    }

    private function _construirFirmas($firmas)
    {
        //$firmas => array entidad firmas
        $fullText="";
        foreach ($firmas as $firma) {
            if($firma->getFirma() && $firma->getUserEntiy()){
                
                $user = $this->getDoctrine()
                ->getRepository('NononsenseUserBundle:Users')
                ->find($firma->getUserEntiy());
                if($user->getName()){
                    $name=$user->getName();
                }
                else{
                    $name="";
       
                }
                
                $id = $firma->getId();
                $nombre = $name;
                $fecha = $firma->getModified()->format('d-m-Y H:i:s');
                $firma = $firma->getFirma();

                $fullText .= "<b>FIRMA: " . $id . "</b><br>" . $nombre . " " . $fecha . "<br><img src='" . $firma . "' /><br><br><br>";
            }
        }
        
        return $fullText;

    }

    private function uploadFile($request, $record_id)
    {
        //====================
        // GUARDAR DOCUMENTOS
        //====================

        //--------------------
        // url carpeta usuario
        //--------------------
        $ruta='/files/documents/'.$record_id.'/anexos/';
        $full_path = $this->get('kernel')->getRootDir() . $ruta;

        //---------------------------
        // ayudante archivos Symfony
        //---------------------------
        $fs = new Filesystem();

        //----------------------------
        // crear carpeta si no existe
        //----------------------------
        if(!$fs->exists($full_path))
        {
            $fs->mkdir($full_path);
        }

        //----------------------
        // nombre del documento
        //----------------------
        $file = $request->files->get('anexo');
        $file_name = $file->getClientOriginalName();
        $file_name_ = $file_name;

        //--------------------------------------------------
        // si existe documento mismo nombre, cambiar nombre
        //--------------------------------------------------
        if(file_exists($full_path.$file_name))
        {
            $i = 0;

            do
            {
                $i++;
                $file_name = $i.$file_name_;
            }

            while (file_exists($full_path.$file_name));
        }

        //-------------------
        // guardar documento
        //-------------------
        $file->move($full_path, $file_name);

        /**
         * @return [ nombre documento, tamaño documento ]
         */
        return [
            'name' => $ruta.$file_name,
            'size' => $file->getClientSize()
        ];
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