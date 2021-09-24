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
        $groups=array();
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

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
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

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
            return $this->render('NononsenseHomeBundle:Contratos:records.html.twig',$array_item);
        }
        else{
            //Exportamos a Excel

            if($request->get("export_excel")){
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                $phpExcelObject->getProperties();
                $phpExcelObject->setActiveSheetIndex(0)
                 ->setCellValue('A1', 'Nº')
                 ->setCellValue('B1', 'Nombre')
                 ->setCellValue('C1', 'Creador')
                 ->setCellValue('D1', 'Próximo firmante')
                 ->setCellValue('E1', 'Tipo')
                 ->setCellValue('F1', 'Fecha')
                 ->setCellValue('G1', 'Estado');
            }

            if($request->get("export_pdf")){
                $html='<html><body style="font-size:8px;width:100%">';
                $sintax_head_f="<b>Filtros:</b><br>";

                if($request->get("content")){
                    $html.=$sintax_head_f."Contenido => ".$request->get("content")."<br>";
                    $sintax_head_f="";
                }

                if($request->get("name")){
                    $html.=$sintax_head_f."Nombre => ".$request->get("name")."<br>";
                    $sintax_head_f="";
                }

                if($request->get("type")){
                    $htype = $this->getDoctrine()->getRepository(Types::class)->findOneBy(array("id"=>$request->get("type")));
                    $html.=$sintax_head_f."Tipo => ".$htype->getName()."<br>";
                    $sintax_head_f="";
                }

                if($request->get("status")){
                    switch($request->get("status")){
                        case 1: $hstate="Pendiente de completar";break;
                        case 2: $hstate="Pendiente de firma";break;
                        case 3: $hstate="Completado";break;
                        case 4: $hstate="Cancelado";break;
                    }
                    $html.=$sintax_head_f."Estado => ".$hstate."<br>";
                    $sintax_head_f="";
                }

                if($request->get("from")){
                    $html.=$sintax_head_f."Fecha desde => ".$request->get("from")."<br>";
                    $sintax_head_f="";
                }

                if($request->get("until")){
                    $html.=$sintax_head_f."Fecha hasta => ".$request->get("until")."<br>";
                    $sintax_head_f="";
                }

                if($request->get("pending_for_me")){
                    $html.=$sintax_head_f."Pendientes por mí<br>";
                    $sintax_head_f="";
                }

                $html.='<br><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%">
                <th style="font-size:8px;width:6%">Nº</th>
                <th style="font-size:8px;width:44%">Nombre</th>
                <th style="font-size:8px;width:10%">Creador</th>
                <th style="font-size:8px;width:10%">Próximo firmante</th>
                <th style="font-size:8px;width:10%">Tipo</th>
                <th style="font-size:8px;width:10%">Fecha</th>
                <th style="font-size:8px;width:10%">Estado</th>
                </tr>';
            }

            $i=2;
            foreach($array_item["items"] as $item){
                switch($item["status"]){
                    case 1: $status="En proceso";break;
                    case 2: $status="Pendiente de firma";break;
                    case 3: $status="Completado";break;
                    case 4: $status="Cancelado";break;
                    case 5: $status="Pendiente anexo";break;
                    default: $status="Desconocido";
                }

                if($item["nameNextSigner"]){
                    $next_signer=$item["nameNextSigner"];
                }
                else{
                    $next_signer=$item["nameNextSignerGroup"];
                }


                if($request->get("export_excel")){
                    $phpExcelObject->getActiveSheet()
                    ->setCellValue('A'.$i, $item["id"])
                    ->setCellValue('B'.$i, $item["name"])
                    ->setCellValue('C'.$i, $item["usuario"])
                    ->setCellValue('D'.$i, $next_signer)
                    ->setCellValue('E'.$i, $item["nameType"])
                    ->setCellValue('F'.$i, ($item["created"]) ? $item["created"] : '')
                    ->setCellValue('G'.$i, $status);
                }

                if($request->get("export_pdf")){
                    $html.='<tr style="font-size:8px"><td>'.$item["id"].'</td><td>'.$item["name"].'</td><td>'.$item["usuario"].'</td><td>'.$next_signer.'</td><td>'.$item["nameType"].'</td><td>'.(($item["created"]) ? $item["created"]->format('Y-m-d H:i:s') : '').'</td><td>'.$status.'</td></tr>';
                }

                $i++;
            }

            if($request->get("export_excel")){
                $phpExcelObject->getActiveSheet()->setTitle('Listado de documentos');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                  ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                  'list_documents.xlsx'
                );
                $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'maxage=1');
                $response->headers->set('Content-Disposition', $dispositionHeader);

                return $response; 
            }

            if($request->get("export_pdf")){
                $html.='</table></body></html>';
                $this->returnPDFResponseFromHTML($html);
            }
        }
    }


    public function createAction($id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('albaran_use');
        if(!$is_valid){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos suficientes'
            );
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

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
    public function linkAction(Request $request, $id)
    {
        $baseUrl = $this->getParameter("cm_installation");
        $baseUrlAux = $this->getParameter("cm_installation_aux");
        

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->find($id);

        if ($record->getStatus() == 2 || $record->getStatus() == 3) {
            // Abrir para validar
             $redirectUrl = $baseUrl . "records/redirectFromData/" . $id;
             if($record->getStatus() == 2){
                 $scriptUrl = $baseUrl . "../js/js_oarodoc/documents_validations.js?v=".uniqid();
             }
             else{
                 $scriptUrl = $baseUrl . "../js/js_oarodoc/show.js?v=".uniqid();
             }

        } else if ($record->getStatus() == -1 || $record->getStatus() == 0  || $record->getStatus() == 1   || $record->getStatus() == 5) {

            $redirectUrl = $baseUrl . "records/redirectFromData/" . $id;
             $scriptUrl = $baseUrl . "../js/js_oarodoc/documents.js?v=".uniqid();
        }
        
        $token_get_data = $this->get('utilities')->generateToken();
       
        $getDataUrl=$baseUrlAux."dataRecords/requestData/".$id."?token=".$token_get_data;
        $callbackUrl=$baseUrlAux."dataRecords/returnData/".$id."?token=".$token_get_data;

        $base_url=$this->getParameter('api_docoaro')."/documents/".$record->getDocument()->getPlantillaId()."?getDataUrl=".$getDataUrl."&redirectUrl=".$redirectUrl."&callbackUrl=".$callbackUrl."&scriptUrl=".$scriptUrl;
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

        if ($request->get("no-redirect") !== null && $request->get("no-redirect")) {
            return $url_edit_documento;
        }
     
        return $this->redirect($url_edit_documento);
    }

    /* Función a la que llama docxpresso antes de abrir la vista previa para saber si necesita cargar datos en las plantillas */
    public function RequestDataAction($id)
    {
        $expired_token = $this->get('utilities')->tokenExpired($_REQUEST["token"]);
        if(!$expired_token){
            $em = $this->getDoctrine()->getManager();

            // get the InstanciasSteps entity
            $record = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsDocuments')
                ->find($id);

            $document = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:Documents')
                ->find($record->getDocument());
            
            /* Prefill document */
            if($record->getStepDataValue()){
                $data["data"] = json_decode($record->getStepDataValue(),TRUE)["data"];
            }
            $data["data"]["numero_solicitud"]=$record->getId();

            
            
            $em = $this->getDoctrine()->getManager();

            if ($record->getStatus() == 0) {

                $data["data"]["require_sap"]=""; 
            } 
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


            $response = new Response();
            $response->setStatusCode(200);
            $response->setContent(json_encode($data));

            return $response;
        }
    }

    /* Función a la que se conecta doxpresso para mandar los datos - Webhook*/
    public function returnDataAction($id)
    {

        $expired_token = $this->get('utilities')->tokenExpired($_REQUEST["token"]);
        if(!$expired_token){

            // get the InstanciasSteps entity
            $record = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsDocuments')
                ->find($id);

            $document = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:Documents')
                ->find($record->getDocument());

            $request = Request::createFromGlobals();
            $params = array();
            $content = $request->getContent();

            if (!empty($content))
            {
                $params = json_decode($content, true); // 2nd param to get as array
            }

            $em = $this->getDoctrine()->getManager();

            $now = new \DateTime();

            /*
             * Actualizar metaData según variables
             */


            $record->setStepDataValue(json_encode(array("data" => $params["data"], "action" => $params["action"]), JSON_FORCE_OBJECT));

            $stepData = $record->getStepDataValue();
            $stepDataJSON = json_decode($stepData);

            $signatures = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                ->findBy(array("record"=> $record->getId()));

            /* Si no está metido el workflow de las firmas lo metemos */
            if(!$signatures || $record->getComments()!=NULL){
                $record->setComments(NULL);
                if($params["action"]=="save"){
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
        else{
            return false;
        }
    }

    /* Pagina a la que vamos tras volver de docxpresso */
    public function redirectFromDataAction($id, Request $request)
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

        $stepData = $record->getStepDataValue();
        $stepDataJSON = json_decode($stepData, TRUE);

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

        if ($stepDataJSON["action"] == 'cancel') {
            $record->setStatus(4);
            $em->persist($record);
            $em->flush();

            return $this->render('NononsenseHomeBundle:Contratos:record_cancel_interface.html.twig', array(
                "documentName" => $documentName,
                "stepid" => $id
            ));
        } elseif ($stepDataJSON["action"]== 'return') {
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
            
        } elseif ($stepDataJSON["action"] == 'save_partial') {
            if(($record->getStatus()==0 || $record->getStatus()==1) && $record->getUserCreatedEntiy()==$user){
                $record->setStatus(1);
                $em->persist($record);
                $em->flush();
                

                $stepData = $record->getStepDataValue();
                $stepDataJSON = json_decode($stepData);

                /*
                 * Revisar si ha habido algún cambio en las variables para que muestre el campo de texto.
                 */
                $devolucion = 0;


                return $this->render('NononsenseHomeBundle:Contratos:record_completed.html.twig', array(
                    "documentName" => $documentName,
                    "id" => $id,
                    "validated" => 0,
                    "devolucion" => $devolucion
                ));
            }

        } elseif ($stepDataJSON["action"] == 'save') {
            $stepData = $record->getStepDataValue();
            $stepDataJSON = json_decode($stepData, TRUE);

            $percentageCompleted = 100;
            $validated = 1;

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
                            
                            try {
                                $record->setStatus(3);

                                $request->attributes->set("mode", 'pdf');
                                $request->attributes->set("no-redirect", true);

                                $file = Utils::api3($this->linkAction($request, $record->getId()));
                                $file = Utils::saveFile($file, 'plain_document', $this->getParameter('crt.root_dir'));
                                Utils::setCertification($this->container, $file, 'documento/albaran', $record->getId());
                            } catch (\Exception $e) {
                                $this->get('session')->getFlashBag()->add( 'error', "No se pudo certificar el doccumento");
                            }

                            //CERTIFICADO AQUÍ ALEX
                            //RUTA DEL PDF -> $ruta_pdf=$this->container->get('router')->generate('nononsense_records_link', array("id" => $record->getId()),TRUE)."?mode=pdf";
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
            

        } else if ($stepDataJSON["action"] == 'close') {

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

            $password = $request->get('password');
            if(!$this->get('utilities')->checkUser($password)){
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "No se pudo firmar el doccumento, la contraseña es incorrecta"
                );
                return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
            }

            $signature->setFirma(1);
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
                    $baseURL=$this->container->get('router')->generate('nononsense_records_link', array("id" => $record->getId()),TRUE);
                    
                    $this->get('utilities')->sendNotification($email, $baseURL, "", "", $subject, $mensaje);
                }
            }
        }
        else{
            if($signature){
                $record->setLastSign($signature->getId());
            }
            // $record->setStatus(3);

            try {
                $record->setStatus(3);

                $request->attributes->set("mode", 'pdf');
                $request->attributes->set("no-redirect", true);
                
                $file = Utils::api3($this->linkAction($request, $record->getId()));
                $file = Utils::saveFile($file, 'plain_document', $this->getParameter('crt.root_dir'));
                Utils::setCertification($this->container, $file, 'documento/albaran', $record->getId());
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add( 'error', "No se pudo certificar el doccumento");
            }

            //CERTIFICADO AQUÍ ALEX
            //RUTA DEL PDF -> $ruta_pdf=$this->container->get('router')->generate('nononsense_records_link', array("id" => $record->getId()),TRUE)."?mode=pdf";
        }
        
        


        $firmas = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                ->findBy(array("record" => $record));

        $stepDataValues = $record->getStepDataValue();
        $stepDataValuesJSON = json_decode($stepDataValues);

        $stepDataValuesJSON->data->dxo_gsk_audit_trail_bloque = 0;
        $stepDataValuesJSON->data->dxo_gsk_firmas_bloque = 1;


        $stepDataValuesJSON->data->dxo_gsk_firmas = $this->_construirFirmas($firmas);


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

        $password = $request->get('password');
        if(!$this->get('utilities')->checkUser($password)){
            $this->get('session')->getFlashBag()->add(
                'error',
                "No se pudo firmar el doccumento, la contraseña es incorrecta"
            );
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }


        $signature->setFirma(1);
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
            $baseURL=$this->container->get('router')->generate('nononsense_records_link', array("id" => $record->getId()),TRUE);
                
            $this->get('utilities')->sendNotification($email, $baseURL, "", "", $subject, $mensaje);

        }
        else{
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }
        
        
        

        $firmas = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsSignatures')
                ->findBy(array("record" => $record));

        $stepDataValues = $record->getStepDataValue();
        $stepDataValuesJSON = json_decode($stepDataValues);

        $stepDataValuesJSON->data->dxo_gsk_audit_trail_bloque = 0;
        $stepDataValuesJSON->data->dxo_gsk_firmas_bloque = 1;


        $stepDataValuesJSON->data->dxo_gsk_firmas = $this->_construirFirmas($firmas);


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

        $documentName = $record->getDocument()->getName();

        return $this->render('NononsenseHomeBundle:Contratos:record_sent.html.twig', array(
                    "documentName" => $documentName,
                    "id" => $id,
        ));
    }

    public function fileAction($id)
    {
        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsDocuments')
            ->find($id);

        return new BinaryFileResponse($this->get('kernel')->getRootDir().$record->getFiles());
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
        
        foreach($dataJson->data->u_tipo_material as $key => $material_value){
            if(urldecode($dataJson->data->u_check3)=="3" && urldecode($dataJson->data->u_check4)=="1" && urldecode($dataJson->data->u_tipo_material->{$key})!="ZNBW"){
                $anexo=1;
            }

            if(urldecode($dataJson->data->u_check3)=="2" && urldecode($dataJson->data->u_check4)!="2"){
                $anexo=1;
            }

            if(urldecode($dataJson->data->u_check4)=="3" && (urldecode($dataJson->data->u_check3)=="4" || urldecode($dataJson->data->u_check3)=="5") && urldecode($dataJson->data->u_tipo_material->{$key})!="ZINT REG" && urldecode($dataJson->data->u_tipo_material->{$key})!="ZCOM NO Impreso"){
                $anexo=1;
            }

            switch(urldecode($dataJson->data->u_tipo_material->{$key})){
                case "ZINT REG":
                case "ZINT PRU":
                    $step4=1;
                    break;
                case "ZNBW":
                    $almacen=1;
                    $responsable_almacen=1;
                    break;
                case "ZCOM Impreso":
                    if((isset($dataJson->data->u_check2->{$key}) && urldecode($dataJson->data->u_check2->{$key})=="Si") || urldecode($dataJson->data->u_check2)=="Si"){
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
                if(urldecode($dataJson->data->u_check3)=="3" && urldecode($dataJson->data->u_check4)!="1"){
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
                if(urldecode($dataJson->data->u_check4)=="1" && urldecode($dataJson->data->u_check3)=="3"){
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
            if($firma->getUserEntiy() && $firma->getFirma()){
                
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

                $fullText .= "<i>Documento securizado mediante registro en Blockchain</i>. <br>Firmado por " . $nombre . " " . $fecha . "<br><br>";
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

    private function returnPDFResponseFromHTML($html){
        //set_time_limit(30); uncomment this line according to your needs
        // If you are not in a controller, retrieve of some way the service container and then retrieve it
        //$pdf = $this->container->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //if you are in a controlller use :
        $pdf = $this->get("white_october.tcpdf")->create('horizontal', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAuthor('GSK');
        $pdf->SetTitle(('Registros GSK'));
        $pdf->SetSubject('Registros GSK');
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 9, '', true);
        //$pdf->SetMargins(20,20,40, true);
        $pdf->AddPage('L', 'A4');


        $filename = 'list_records';

        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        $pdf->Output($filename.".pdf",'I'); // This will output the PDF as a response directly
    }
}