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


        
        $group_direccion_rrhh = $this->getDoctrine()->getRepository('NononsenseGroupBundle:Groups')->find($this->getParameter("group_id_direccion_rrhh"));
        $array_item['group_direccion_rrhh'] = $group_direccion_rrhh;

        $group_comite_rrhh = $this->getDoctrine()->getRepository('NononsenseGroupBundle:Groups')->find($this->getParameter("group_id_comite_rrhh"));
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

        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
            ->find($id);


        $redirectUrl = $baseUrl . "recordsContracts/redirectFromData/" . $id;
        if ($record->getStatus()==0) {    
            $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_creation.js?v=".uniqid();
        }
        if ($record->getStatus()==1) {//firma por parte del director de rrhh    
            $isGroup_direccion_rrhh = $this->getDoctrine()->getRepository('NononsenseGroupBundle:GroupUsers')->isMemberOfAnyGroup($user->getId(), array($this->getParameter("group_id_direccion_rrhh")));
            if($isGroup_direccion_rrhh){
                $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_sign_director_rrhh.js?v=".uniqid();
            }
            else{
                $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_without_perms.js?v=".uniqid();
            }
        }
        if ($record->getStatus()==2) {//firma por parte del comite de rrhh
            $isGroup_comite_rrhh = $this->getDoctrine()->getRepository('NononsenseGroupBundle:GroupUsers')->isMemberOfAnyGroup($user->getId(), array($this->getParameter("group_id_comite_rrhh")));
            if($isGroup_comite_rrhh){
                $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_sign_comite_rrhh.js?v=".uniqid();
            }
            else{
                $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_without_perms.js?v=".uniqid();
            }
        }
        if ($record->getStatus()==3) {//para enviar

            $isGroup_contratos_rrhh_or_direccion_rrhh = $this->getDoctrine()->getRepository('NononsenseGroupBundle:GroupUsers')->isMemberOfAnyGroup($user->getId(), array($this->getParameter("group_id_contratos_rrhh"), $this->getParameter("group_id_direccion_rrhh")));            
            if($isGroup_contratos_rrhh_or_direccion_rrhh){
                $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_for_send.js?v=".uniqid();
            }
            else{
                $isGroup_comite_rrhh = $this->getDoctrine()->getRepository('NononsenseGroupBundle:GroupUsers')->isMemberOfAnyGroup($user->getId(), array($this->getParameter("group_id_comite_rrhh")));

                if($isGroup_comite_rrhh){
                    $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_for_send_comite.js?v=".uniqid();
                }
                else{
                    $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_without_perms.js?v=".uniqid();
                }       
                
            }
        }

        $token_get_data = $this->get('utilities')->generateToken();

        $getDataUrl=$baseUrlAux."dataRecordsContracts/requestData/".$id."/".$token_get_data;
        $callbackUrl=$baseUrlAux."dataRecordsContracts/returnData/".$id;

        $id_plantilla = $record->getContract()->getPlantillaId();

        $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_sign_public_1.js?v=".uniqid();

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

        $mode = $request->get("mode");
        //$mode = 'pdf';

        switch($mode){
            case "pdf": $url_edit_documento=$response["pdfUrl"];break;
            default: $url_edit_documento=$response["fillInUrl"];break;
        }
     
        return $this->redirect($url_edit_documento);
    }

    /* Función a la que llama docxpresso antes de abrir la vista previa para saber si necesita cargar datos en las plantillas */
    public function RequestDataAction($id, $token, $sign_public_type)
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

            //sign_public_type es un tercer param que se pasa para generar la url de firma publica que se envia al firmante final, el empleado
            if($record->getStatus()==2 || $sign_public_type==1 || $sign_public_type==2){//ya ha firmado direccion rrhh
                $recordsContractsSignatures = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
                    ->findOneBy(array('record'=>$record, 'number'=>'1'));

                if($recordsContractsSignatures){
                    $firma = $recordsContractsSignatures->getFirma();
                    $data["data"]["firma_direccion_rrhh"] = "<img src='" . $firma . "' />";    
                }
            }
            if($record->getStatus()==3 || $sign_public_type==2){//ya ha firmado comite rrhh
                $recordsContractsSignatures = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
                    ->findOneBy(array('record'=>$record, 'number'=>'2'));

                if($recordsContractsSignatures){
                    $firma = $recordsContractsSignatures->getFirma();
                    $data["data"]["firma_comite"] = "<img src='" . $firma . "' />";    
                }
            }


            $data["configuration"]["form_readonly"]=1;
            $data["data"]["numero_solicitud"]=$record->getId();
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

        unset($params["data"]['firma_direccion_rrhh']);
        unset($params["data"]['firma_comite']);
        $record->setStepDataValue(json_encode(array("data" => $params["data"], "action" => $params["action"]), JSON_FORCE_OBJECT));

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

            $contractName = $record->getContract()->getName();

            $stepData = $record->getStepDataValue();
            $stepDataJSON = json_decode($stepData, TRUE);
            $status = $record->getStatus();
            $action = $stepDataJSON["action"];

            if($status==0 && $action=='save_partial'){//el contrato ha sido rellenando parcialmente
                $this->get('session')->getFlashBag()->add('message', "El contrato se ha guardado correctamente");
            }
            if($status==0 && $action=='save'){//esta pasando a firma direccion rrhh
                $new_status = 1;
                $record->setStatus($new_status);
                $this->get('session')->getFlashBag()->add('message', "El contrato se ha enviado a firmar a Dirección RRHH");

                $users_direccion_rrhh = $em->getRepository(GroupUsers::class)->findBy(["group" => $this->getParameter("group_id_direccion_rrhh")]);
                $subject="Contrato pendiente de firma";
                $mensaje='El Contrato con ID '.$record->getId().' está pendiente de firma por su parte';
                $link=$this->container->get('router')->generate('nononsense_records_contracts_link', array("id" => $record->getId()),TRUE);

                foreach ($users_direccion_rrhh as $user_dir_rrhh) {
                    $this->_sendNotification($user_dir_rrhh->getUser()->getEmail(), $link, "", "", $subject, $mensaje);
                }

            }
            if($status==1 && $action=='save'){//el contrato va a firmarse por direccion rrhh
                $can_sign=0;
                $isGroup_direccion_rrhh = $this->getDoctrine()->getRepository('NononsenseGroupBundle:GroupUsers')->isMemberOfAnyGroup($user->getId(), array($this->getParameter("group_id_direccion_rrhh")));
                if($isGroup_direccion_rrhh){
                    $can_sign=1;
                }
                if($can_sign==1){
                    return $this->render('NononsenseHomeBundle:Contratos:record_contract_sign.html.twig', array(
                        "contractName" => $contractName,
                        "id" => $id
                    ));    
                }
                else{
                    $this->get('session')->getFlashBag()->add('error',"No tienes permisos para firmar. Solo pueden firmar miembros de Dirección RRHH");
                }
            }
            if($status==2 && $action=='save'){//el contrato va a firmarse por comite rrhh
                $can_sign=0;
                $isGroup_comite_rrhh = $this->getDoctrine()->getRepository('NononsenseGroupBundle:GroupUsers')->isMemberOfAnyGroup($user->getId(), array($this->getParameter("group_id_comite_rrhh")));
                if($isGroup_comite_rrhh){
                    $can_sign=1;
                }
                if($can_sign==1){
                    return $this->render('NononsenseHomeBundle:Contratos:record_contract_sign.html.twig', array(
                        "contractName" => $contractName,
                        "id" => $id
                    ));    
                }
                else{
                    $this->get('session')->getFlashBag()->add('error',"No tienes permisos para firmar. Solo pueden firmar miembros del Comité de RRHH");
                }
            } 


            $em->persist($record);
            $em->flush();    
        }
        

        return $this->redirect($this->container->get('router')->generate('nononsense_records_contracts'));
    }


    /* Proceso donde firmamos los documentos */
    public function signAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $record = $this->getDoctrine()->getRepository('NononsenseHomeBundle:RecordsContracts')->find($id);

        if(!$record){
            $this->get('session')->getFlashBag()->add('error',"Error desconocido al intentar guardar los datos del contrato");
        }
        else{
            $status = $record->getStatus();
            if($status==1){//la firma es del director de rrhh
                $signature = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
                ->findOneBy(array("record"=>$record,"number"=>1));

                if(!$signature){

                    $can_sign=0;

                    $isGroup_direccion_rrhh = $this->getDoctrine()->getRepository('NononsenseGroupBundle:GroupUsers')->isMemberOfAnyGroup($user->getId(), array($this->getParameter("group_id_direccion_rrhh")));
                    if($isGroup_direccion_rrhh){
                        $can_sign=1;
                    }

                    if($can_sign==1){
                        $signature = new RecordsContractsSignatures();
                        $signature->setFirma($request->get('firma'));
                        $signature->setNumber(1);
                        $signature->setUserEntiy($user);
                        $signature->setRecord($record);
                        $em->persist($signature);

                        $record->setStatus(2);
                        $em->persist($record);              
                        $this->get('session')->getFlashBag()->add('error',"La firma se ha grabado correctamente");      

                        $users_comite_rrhh = $em->getRepository(GroupUsers::class)->findBy(["group" => $this->getParameter("group_id_comite_rrhh")]);
                        $subject="Contrato pendiente de firma";
                        $mensaje='El Contrato con ID '.$record->getId().' está pendiente de firma por su parte';
                        $link=$this->container->get('router')->generate('nononsense_records_contracts_link', array("id" => $record->getId()),TRUE);

                        foreach ($users_comite_rrhh as $user_comite_rrhh) {
                            $this->_sendNotification($user_comite_rrhh->getUser()->getEmail(), $link, "", "", $subject, $mensaje);
                        }

                    }
                    else{
                        $this->get('session')->getFlashBag()->add('error',"La firma no se grabó porque no tienes permisos suficientes");
                    }
                }
                else{
                    $this->get('session')->getFlashBag()->add('error',"La firma no se grabó porque este contrato ya había sido firmado por el director de RRHH");
                }
            }
            if($status==2){//la firma es del comite de rrhh
                $signature = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
                    ->findOneBy(array("record"=>$record,"number"=>2));

                if(!$signature){

                    $can_sign=0;

                    $isGroup_comite_rrhh = $this->getDoctrine()->getRepository('NononsenseGroupBundle:GroupUsers')->isMemberOfAnyGroup($user->getId(), array($this->getParameter("group_id_comite_rrhh")));
                    if($isGroup_comite_rrhh){
                        $can_sign=1;
                    }

                    if($can_sign==1){
                        $signature = new RecordsContractsSignatures();
                        $signature->setFirma($request->get('firma'));
                        $signature->setNumber(2);
                        $signature->setUserEntiy($user);
                        $signature->setRecord($record);
                        $em->persist($signature);

                        $record->setStatus(3);
                        $em->persist($record);              
                        $this->get('session')->getFlashBag()->add('error',"La firma se ha grabado correctamente");      
                    }
                    else{
                        $this->get('session')->getFlashBag()->add('error',"La firma no se grabó porque no tienes permisos suficientes");
                    }
                }
                else{
                    $this->get('session')->getFlashBag()->add('error',"La firma no se grabó porque este contrato ya había sido firmado por el comité de RRHH");
                }
            }
        }

        $em->flush();

        return $this->redirect($this->container->get('router')->generate('nononsense_records_contracts'));
    }

    public function editAction($id)
    {

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
            ->find($id);

        $route = $this->container->get('router')->generate('nononsense_records_link', array("id" => $record->getId()));
        return $this->redirect($route);
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

                    $baseUrl = $this->getParameter("cm_installation");
                    $baseUrlAux = $this->getParameter("cm_installation_aux");

                    $token = uniqid().rand(10000,90000);
                    $pin = rand(100000, 900000);

                    $id_plantilla = $record->getContract()->getPlantillaId();

                    $links = array();

                    for($i=1;$i<=2;$i++){
                        $token_get_data = $this->get('utilities')->generateToken();
                        $getDataUrl=$baseUrlAux."dataRecordsContracts/requestData/".$id."/".$token_get_data."/".$i;
                        $redirectUrl = $this->container->get('router')->generate('nononsense_records_contracts_public_sign_contract', array("token" => $record->getId()));

                        $scriptUrl_link = $baseUrl . "../js/js_oarodoc/contracts_sign_public_".$i.".js?v=".uniqid();
                        $base_url=$this->getParameter('api_docoaro')."/documents/".$id_plantilla."?getDataUrl=".$getDataUrl."&redirectUrl=".$redirectUrl."&scriptUrl=".$scriptUrl_link;
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
                        $links[$i] = $response["pdfUrl"];
                    }

                    echo "<pre>";
                    print_r($links);
                    echo "</pre>";

                    die();


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
                                'link1' => $link1,
                                'link2' => $link2,
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
                    echo $e->getMessage();
                    die();
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