<?php

namespace Nononsense\HomeBundle\Controller;


use Nononsense\HomeBundle\Entity\Contracts;
use Nononsense\HomeBundle\Entity\RecordsContracts;
use Nononsense\HomeBundle\Entity\RecordsContractsSignatures;
use Nononsense\HomeBundle\Entity\ContractsSignatures;
use Nononsense\HomeBundle\Entity\ContractsTypes;
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

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RecordsContractsController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('contratos_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

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
        $array_item["types"] = $this->getDoctrine()->getRepository(ContractsTypes::class)->findAll();
        $array_item["items"] = $this->getDoctrine()->getRepository(RecordsContracts::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(RecordsContracts::class)->count($filters2,$types);

        $group_direccion_rrhh = $this->getDoctrine()->getRepository('NononsenseGroupBundle:Groups')->find(18);
        $array_item['group_direccion_rrhh'] = $group_direccion_rrhh;

        $group_comite_rrhh = $this->getDoctrine()->getRepository('NononsenseGroupBundle:Groups')->find(19);
        $array_item['group_comite_rrhh'] = $group_comite_rrhh;

        $url=$this->container->get('router')->generate('nononsense_records_contracts');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        return $this->render('NononsenseHomeBundle:Contratos:records_contracts.html.twig',$array_item);
    }


    public function createAction($id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('contratos_crear_registro');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $contract = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:Contracts')
            ->find($id);

        /* Para testear */

        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $record = new RecordsContracts();
        $record->setIsActive(true);
        $record->setStatus(0); // Estado especial de creación
        $record->setDescription("");
        $record->setMasterDataValues("");
        $record->setCreated(new \DateTime());
        $record->setObservaciones("");
        $record->setYear(date("Y"));
        $record->setUserCreatedEntiy($user);
        $record->setContract($contract);
        $record->setDependsOn(0);
        $record->setToken("");
        $record->setStepDataValue("");
        $record->setFiles("");

        $record->setType($contract->getType());
        $em->persist($record);
        $em->flush();
        
        $route = $this->container->get('router')->generate('nononsense_records_contracts_link', array("id" => $record->getId()));
        
        return $this->redirect($route);
    }

    /* Donde Generamos el link que llama a docxpresso */
    public function linkAction(Request $request, $id)
    {
        $baseUrl = $this->getParameter("cm_installation");
        $baseUrlAux = $this->getParameter("cm_installation_aux");
        
        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
            ->find($id);


        $redirectUrl = $baseUrl . "recordsContracts/redirectFromData/" . $id;
        if ($record->getStatus()==0) {    
            $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_creation.js?v=".uniqid();
        }
        if ($record->getStatus()==1) {//firma por parte del director de rrhh    
            $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_sign_director_rrhh.js?v=".uniqid();
        }
        if ($record->getStatus()==2) {//firma por parte del comite de rrhh
            $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_sign_comite_rrhh.js?v=".uniqid();
        }

        $token_get_data = $this->get('utilities')->generateToken();

        $getDataUrl=$baseUrlAux."dataRecordsContracts/requestData/".$id."/".$token_get_data;
        $callbackUrl=$baseUrlAux."dataRecordsContracts/returnData/".$id;

        $id_plantilla = $record->getContract()->getPlantillaId();

        $base_url=$this->getParameter('api_docoaro')."/documents/".$id_plantilla."?getDataUrl=".$getDataUrl."&redirectUrl=".$redirectUrl."&callbackUrl=".$callbackUrl."&scriptUrl=".$scriptUrl;
        
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

        switch($request->get("mode")){
            case "pdf": $url_edit_documento=$response["pdfUrl"];break;
            default: $url_edit_documento=$response["fillInUrl"];break;
        }
     
        return $this->redirect($url_edit_documento);
    }

    /* Función a la que llama docxpresso antes de abrir la vista previa para saber si necesita cargar datos en las plantillas */
    public function RequestDataAction($id, $token)
    {
        $em = $this->getDoctrine()->getManager();

        $expired_token = $this->get('utilities')->tokenExpired($token);
        if($expired_token==1){
            $data["expired_token"] = 1;    
        }
        else{
            // get the InstanciasSteps entity
            $record = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsContracts')
                ->find($id);

            $contract = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:Contracts')
                ->find($record->getContract());
            
            /* Prefill contract */
            if($record->getStepDataValue()){
                $data["data"] = json_decode($record->getStepDataValue(),TRUE)["data"];
            }
            $data["data"]["numero_solicitud"]=$record->getId();

            /*
            else {

                // Data Ingegrity other usage
                $firmas = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                    ->findBy(array("record" => $record));

                if (!empty($firmas)) {
                    $data["data"]["dxo_gsk_audit_trail_bloque"] = 0;
                    $data["data"]["dxo_gsk_firmas_bloque"] = 1;
                    $data["data"]["dxo_gsk_firmas"] = $this->_construirFirmas($firmas);
                }

                if($record->getStatus()==3){
                    $data["configuration"]["cancel_button"]=0;
                    $data["configuration"]["cancel_button"]=0;
                    $data["configuration"]["partial_save_button"]=0;
                    $data["configuration"]["form_readonly"]=1;
                }

                if($record->getType()->getId()==1){
                    if($record->getStatus()==2 || $record->getStatus()==3){
                        $data["configuration"]["prefix_edit"]="resp_alm_SAP"; 
                        
                        $signatures = $this->getDoctrine()
                            ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                            ->findBy(array("record"=> $record->getId(), "firma" => NULL));
                        if(count($signatures)<=1){
                            $data["data"]["require_sap"]="1";  
                        }
                        else{
                            $data["data"]["require_sap"]=""; 
                        }
                    }
                    else{
                        $data["data"]["require_sap"]=""; 
                    }
                }
            }
            */
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
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
            ->find($id);

        $contract = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:Contracts')
            ->find($record->getContract());

        $request = Request::createFromGlobals();
        $params = array();
        $content = $request->getContent();

        if (!empty($content))
        {
            $params = json_decode($content, true); // 2nd param to get as array
        }

        $em = $this->getDoctrine()->getManager();

        $record->setStepDataValue(json_encode(array("data" => $params["data"], "action" => $params["action"]), JSON_FORCE_OBJECT));

        $status = $record->getStatus();

        if($status==0 && $params["action"]=='save'){//esta pasando a firma direccion rrhh
            $new_status = 1;
            $record->setStatus($new_status);
        }
        if($status==1 && $params["action"]=='save'){//esta pasando a comite
            $new_status = 2;
            $record->setStatus($new_status);
        }
        if($status==2 && $params["action"]=='save'){//esta pasando a 'para enviar'
            $new_status = 3;
            $record->setStatus($new_status);
        }


        $em->persist($record);
        $em->flush();

        $responseAction = new Response();
        $responseAction->setStatusCode(200);
        $responseAction->setContent("OK");
        return $responseAction;
    }

    /* Pagina a la que vamos tras volver de docxpresso */
    public function redirectFromDataAction($id)
    {
        
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
            ->find($id);

        if(!$record){
            $this->get('session')->getFlashBag()->add('error',"Error desconocido al intentar guardar los datos del contrato");
        }
        else{
            $stepData = $record->getStepDataValue();
            $stepDataJSON = json_decode($stepData, TRUE);
            $status = $record->getStatus();
            $action = $stepDataJSON["action"];

            if($status==0 && $action=='save_partial'){//el contrato ha sido rellenando parcialmente
                $this->get('session')->getFlashBag()->add('message', "El contrato se ha guardado correctamente");
            }
            if($status==1 && $action=='save'){//el contrato ha pasado a firma direccion rrhh
                $this->get('session')->getFlashBag()->add('message', "El contrato se ha enviado para firma de dirección de RRHH");
            } 
            if($status==2 && $action=='save'){//el contrato ha pasado a comite
                $this->get('session')->getFlashBag()->add('message', "El contrato se ha enviado al comité de RRHH");
            }
            if($status==3 && $action=='save'){//el contrato ha pasado a comite
                $this->get('session')->getFlashBag()->add('message', "El contrato está listo para enviar");
            }    
        }
        

        return $this->redirect($this->container->get('router')->generate('nononsense_records_contracts'));
    }

    /* Proceso donde firmamos los documentos */
    public function saveAndSendAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $comentario = $request->query->get('comment');

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
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
                ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
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
                ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
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
                ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
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
                ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
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
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
            ->findOneBy(array("id" => $id, "status" => 2));

        if(!$record){
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }

        $signature = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
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
            ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
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
                ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
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

        $remove_signatures = $em->getRepository(RecordsContractsSignatures::class)->findBy(["record" => $record, "firma" => NULL]);
        
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
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
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
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
            ->find($id);

        $route = $this->container->get('router')->generate('nononsense_records_link', array("id" => $record->getId()));
        return $this->redirect($route);
    }

    public function fileAction($id)
    {
        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
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
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
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
        $options['format'] = 'pdf';
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
            ->getRepository('NononsenseHomeBundle:ContractsSignatures')
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
            $sign = new RecordsContractsSignatures();
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
                $sign = new RecordsContractsSignatures();
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

                $fullText .= "<i>Documento securizado mediante registro en Blockchain</i><br>" . $nombre . " " . $fecha . "<br><img src='" . $firma . "' /><br><br><br>";
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

    /* Enviamos un email al trabajador para firma de contrato */
    public function sendEmailAction(Request $request)
    {
        $id = $request->get('record_contract_id_dialog_email');
        $email_email = $request->get('email_email');

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
            ->find($id);

        if(!$record){
            $this->get('session')->getFlashBag()->add('error',"El contrato no existe");    
        }
        else{
            if ($record->getStatus()!=3) {    
                $this->get('session')->getFlashBag()->add('error',"El contrato no se puede enviar. Su estado no es 'Para enviar'");
            }    
            else{
                try{
                    $em = $this->getDoctrine()->getManager();

                    $token = uniqid().rand(10000,90000);
                    $pin = rand(100000, 900000);

                    $link = $this->container->get('router')->generate('nononsense_records_contracts_public_sign_contract', array("token" => $token), UrlGeneratorInterface::ABSOLUTE_URL);

                    $record->setTokenPublicSignature($token);
                    $record->setPin($pin);
                    $em->persist($record);

                    $email = \Swift_Message::newInstance()
                        ->setSubject('Firma Contrato')
                        ->setFrom($this->container->getParameter('mailer_username'))
                        ->setTo($email_email)
                        ->setBody(
                            $this->renderView(
                                'NononsenseHomeBundle:Email:requestSignContract.html.twig', array(
                                'link' => $link,
                                'pin' => $pin,
                            )),
                            'text/html'
                        );
                    if($this->get('mailer')->send($email)) {
                        $em->flush();
                        $this->get('session')->getFlashBag()->add('error',"El contrato ha sido enviado para su firma correctamente");
                    } 
                    else {
                        $this->get('session')->getFlashBag()->add('error',"El contrato no se ha podido enviar para su firma");    
                    }
                }
                catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->add('error',"El contrato no se ha podido enviar para su firma");    
                }
            }
        }

        return $this->redirect($this->container->get('router')->generate('nononsense_records_contracts'));
    }

    public function signContractAction(Request $request, $token){
        
        $array_data = array();

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
            ->findOneByTokenPublicSignature($token);

        if($record){

            $firma = $request->get('firma');
            $result = 0;

            $em = $this->getDoctrine()->getManager();

            $recordSignature = $this->getDoctrine()->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')->findOneBy(
                array('record'=>$record, 'number'=>3)
            );

            if(!$recordSignature){
                if ($request->getMethod() == "POST") {
                
                    if(!$recordSignature){

                        $pin = $request->get('pin');
                        
                        if($record->getPin()==$pin){
                            $sign = new RecordsContractsSignatures();
                            $sign->setUserEntiy($record->getUserCreatedEntiy());
                            $sign->setRecord($record);
                            $sign->setNumber(3);
                            $sign->setCreated(new \DateTime());
                            $sign->setFirma($request->get('firma'));
                            $em->persist($sign); 

                            $record->setStatus(4);
                            $em->persist($record); 

                            $em->flush();

                            $result = 1;
                        }
                        else{
                            $result = 2;
                        }    
                    }
                    else{
                        $firma = $recordSignature->getFirma();
                    }
                    
                }
            }
            else{
                $firma = $recordSignature->getFirma();
            }


            $array_data['token'] = $token;
            $array_data['result'] = $result;
            $array_data['firma'] = $firma;
            $array_data['time'] = time();
            return $this->render('NononsenseHomeBundle:Contratos:sign_contract.html.twig',$array_data);
        }

        throw new \Exception("Contrato no existente", 1);
    }

    public function viewContractAction(Request $request, $token){

        $em = $this->getDoctrine()->getManager();
        
        $ruta_archivo = $this->get('kernel')->getRootDir() . "/../files/el_pdf.pdf";
         
        if(file_exists($ruta_archivo)){
            $content = file_get_contents($ruta_archivo);    
            $response = new Response($content);
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', 'inline');
            return $response;
        }

    }

    private function _sendNotification($mailTo, $link, $logo, $accion, $subject, $message)
    {
        $email = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->container->getParameter('mailer_username'))
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
            return true;
        } else {
            return false;
        }

    }
}