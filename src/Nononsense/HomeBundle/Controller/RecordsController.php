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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nononsense\HomeBundle\Form\Type as FormProveedor;

use Nononsense\UtilsBundle\Classes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

        $array_item["suser"]["groups"]=$groups;
        $array_item["items"] = $this->getDoctrine()->getRepository(RecordsDocuments::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(RecordsDocuments::class)->count($filters2,$types);

        return $this->render('NononsenseHomeBundle:Contratos:records.html.twig',$array_item);
    }


    public function createAction($id)
    {

        $document = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:Documents')
            ->find($id);


        /* Para testear */

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->find(55);

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
            $options['prefix'] = 'v';

            if ($record->getStatus() == 2){
                $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/documentValidacion.js");
                $validacionURL1 = $baseUrl . "js/js_templates/documentValidacion.js?v=" . $versionJS;   
            }
            else{
                $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/documentClosed.js");
                $validacionURL1 = $baseUrl . "js/js_templates/documentClosed.js?v=" . $versionJS; 
            }

        } else if ($record->getStatus() == -1 || $record->getStatus() == 0  || $record->getStatus() == 1) {
            // abrir para editar
            $versionJS = filemtime(__DIR__ . "/../../../../web/js/js_templates/activity.js");
            $validacionURL1 = $baseUrl . "js/js_templates/document.js?v=" . $versionJS;

            $options['prefix'] = 'u';
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
        $now->modify("+2 hour"); // Ver tema de horarios usos

        /*
         * Actualizar metaData según variables
         */
        $varValues = $dataJSON->varValues;

        $data = json_encode($dataJSON);
        $record->setStepDataValue($data);
        $record->setStatus(1);
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
        if(!$signatures){
            if($validated && $percentageCompleted==100){
                switch($record->getType()){
                    //Caso especial para los registros de tipo Albarán Almacén
                    case 1: $this->albaranAlmacen($record);
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
                                $sign->setNumber(0);
                                $sign->setAttachment(0);
                                $sign->setNext($next);
                                $em->persist($sign); 
                                $next=0;
                            }
                            foreach ($baseSignatures as $baseSignaturre) {
                                $sign = new RecordsSignatures();
                                $sign->setUserEntiy($baseSignaturre->getUserEntiy());
                                $sign->setGroupEntiy($baseSignaturre->getGroupEntiy());
                                $sign->setRecord($record);
                                $sign->setNumber($baseSignaturre->getNumber());
                                $sign->setAttachment($baseSignaturre->getAttachment());
                                $sign->setNext($next);
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
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->find($id);

        if(!$record){
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

                $record->setStatus(2);
                $em->persist($record);
                $em->flush();

                $signature = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                ->findOneBy(array("record"=>$record,"next"=>1));
                
                if($signature->getAttachment()){
                    $anexo=1;
                }
                else{
                    $anexo=0;
                }
            
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
                    "anexo" => $anexo
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

        if($signature->getAttachment()){
            if(!$request->files->get('anexo')){
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

        $signature2 = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsSignatures')
            ->findOneBy(array("record"=>$record,"firma"=>null,"next" => 0), array('number' => 'ASC'));

        if($signature2){
            $signature2->setNext(1);
            $em->persist($signature2);
            if($signature2->getUserEntiy()){
                $email=$signature2->getUserEntiy()->getEmail();
                $subject="Documento pendiente de firma";
                $mensaje='El Documento con ID '.$record->getId().' está pendiente de firmar por su parte. Para hacerlo puede acceder a "Mis documentos pendientes", buscar el documento y pulsar en Firmar';
                $baseURL=$this->container->get('router')->generate('nononsense_records_edit', array("id" => $record->getId()),TRUE);
                
                $this->_sendNotification($email, $baseURL, "", "", $subject, $mensaje);
            }
            else{
                //Como es un grupo no enviamos email
            }
        }
        else{
            $record->setStatus(3);
            $record->setLastSign($signature->getId());
            
        }

        $em->persist($record);
        $em->persist($signature);
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

    private function albaranAlmacen($record){

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
        

        if(urldecode($dataJson->varValues->u_check3[1])!="" && urldecode($dataJson->varValues->u_check4[1])==""){
            $anexo=1;
        }

        if(urldecode($dataJson->varValues->u_check4[2])!="" && (urldecode($dataJson->varValues->u_check3[3])!="" || urldecode($dataJson->varValues->u_check3[4])!="") && urldecode($dataJson->varValues->u_tipo_material[0])!="ZINT REG" && urldecode($dataJson->varValues->u_tipo_material[0])!="ZCOM NO Impreso"){
            $anexo=1;
        }

        switch(urldecode($dataJson->varValues->u_tipo_material[0])){
            case "ZINT REG":
            case "ZINT PRU":
                $step4=1;
                break;
            case "ZNBW":
                $almacen=1;
                $responsable_almacen=1;
                break;
            case "ZCOM Impreso":
                if(urldecode($dataJson->varValues->u_check2[0])!=""){
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

        $next=1;
        if($record->getDocument()->getSignCreator()){
            $sign = new RecordsSignatures();
            $sign->setUserEntiy($record->getUserCreatedEntiy());
            $sign->setRecord($record);
            $sign->setNumber(0);
            $sign->setAttachment($anexo);
            $sign->setNext($next);
            $em->persist($sign); 
            $next=0;
        }

        foreach ($baseSignatures as $key => $baseSignaturre) {
            if(($key==0 && $logistica) || ($key==1 && $calidad) || ($key==2 && $almacen) || ($key==3 && $responsable_almacen)){
                $sign = new RecordsSignatures();
                $sign->setUserEntiy($baseSignaturre->getUserEntiy());
                $sign->setGroupEntiy($baseSignaturre->getGroupEntiy());
                $sign->setRecord($record);
                $sign->setNumber($baseSignaturre->getNumber());
                $sign->setAttachment($baseSignaturre->getAttachment());
                $sign->setNext($next);
                $em->persist($sign);
                $next=0;
            }
        }

        $em->flush();
    }

    private function _construirFirmas($firmas)
    {
        //$firmas => array entidad firmas
        $fullText = "<table id='tablefirmas' class='table table-striped'>";


        foreach ($firmas as $firma) {
            if($firma->getFirma()!=NULL){
                $user = $this->getDoctrine()
                ->getRepository('NononsenseUserBundle:Users')
                ->find($firma->getuserid());
                if($user->getName()){
                    $name=$user->getName();
                }
                else{
                    $name="";
                }
                
                $id = $firma->getId();
                $nombre = $name;
                $fecha = $firma->getCreated()->format('d-m-Y H:i:s');
                $firma = $firma->getFirma();

                $fullText .= "<tr><td colspan='4'>Firma</td></tr><tr><td>" . $id . "</td><td>" . $nombre . " " . $fecha . "</td><td><img src='" . $firma . "' /></td></tr>";
            }
        }

        $fullText .= "</table>";
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