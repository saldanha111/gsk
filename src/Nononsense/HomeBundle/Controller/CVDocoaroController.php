<?php
namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Nononsense\UtilsBundle\Classes;

use Nononsense\UserBundle\Entity\Users;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\HomeBundle\Entity\Areas;
use Nononsense\HomeBundle\Entity\TMTemplates;
use Nononsense\HomeBundle\Entity\CVRecords;
use Nononsense\HomeBundle\Entity\TMActions;
use Nononsense\HomeBundle\Entity\CVActions;
use Nononsense\HomeBundle\Entity\TMSecondWorkflow;
use Nononsense\HomeBundle\Entity\CVSignatures;
use Nononsense\HomeBundle\Entity\CVWorkflow;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


class CVDocoaroController extends Controller
{
    public function linkAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $user = $this->container->get('security.context')->getToken()->getUser();

        $record = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $id));
        if(!$record){
            return false;
        }

        if(count($record->getCvSignatures())==0){
            $route = $this->container->get('router')->generate('nononsense_cv_new',array("template" => $record->getTemplate()->getId()))."?record=".$record->getId();
            return $this->redirect($route);
        }

        $baseUrl = $this->getParameter("cm_installation");
        $baseUrlAux = $this->getParameter("cm_installation_aux");

        if($record->getState() && !$record->getState()->getCanBeOpened() && !$request->get("reupdate")){
           $this->get('session')->getFlashBag()->add(
                'error',
                    'La plantilla indicada no se puede abrir por su estado actual'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        if($record->getInEdition() && !$request->get("in_edition")){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'La plantilla se encuentra en edición'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $signature = $this->getDoctrine()->getRepository(CVSignatures::class)->findOneBy(array("record" => $record),array("id" => "DESC"));


        if(!$request->get("in_edition")){
            if($signature && !$signature->getSigned() && $signature->getVersion()!=NULL && $signature->getConfiguration()!=NULL){
                $this->get('session')->getFlashBag()->add(
                    'error',
                        'El registro se encuentra pendiente de firma'
                );
                $route = $this->container->get('router')->generate('nononsense_home_homepage');
                return $this->redirect($route);
            }

            if($request->get("reupdate") && !$record->getState()->getFinal()){
                $this->get('session')->getFlashBag()->add(
                    'error',
                        'El registro no se encuentra en un estado final y por tanto no se puede solicitar una modificación'
                );
                $route = $this->container->get('router')->generate('nononsense_home_homepage');
                return $this->redirect($route);
            }

            if($request->get("reupdate") && $signature->getAction()->getId()==18){
                $this->get('session')->getFlashBag()->add(
                    'error',
                        'No se puede puede modificar este registro porque ya hay una solicitud de modificación'
                );
                $route = $this->container->get('router')->generate('nononsense_home_homepage');
                return $this->redirect($route);
            }
        }

        $custom_view="";

        if($request->get("audittrail")){
            $custom_view.="&audittrail=1";
        }

        if($request->get("reupdate")){
            $custom_view.="&reupdate=1";
        }

        if(($signature->getAction()->getId()==18 || $record->getUserGxP()) && $request->get("view_reupdate")){
            $custom_view.="&auxjson=1";
        }

        if($request->get("logbook")){
            $custom_view.="&logbook=".$request->get("logbook");
        }


        $token_get_data = $this->get('utilities')->generateToken();
        if($record->getTemplate()->getIsReactive()){
            $jsActivityName = '026template';
        }else{
            $jsActivityName = 'activity';
        }
        if(!$record->getState() || (!$record->getState()->getFinal() && !$request->get("pdf") && !$request->get("readonly") && !$request->get("in_edition")) || $request->get("reupdate")){ // Si no es un estado final,no queremos sacar un pdf, no es solo lectura, no está en edición y no es una modificación gxp
            $mode="c";
            if($record->getState()){
                if($record->getState()->getType()){
                    switch($record->getState()->getType()->getName()){
                        case "Cumplimentador": $mode="c";$scriptUrl = urlencode($baseUrl . "../js/js_oarodoc/".$jsActivityName.".js?v=".uniqid());break;
                        case "Verificador": $mode="v";$scriptUrl = urlencode($baseUrl . "../js/js_oarodoc/validation.js?v=".uniqid());break;

                    }
                }
                else{
                    if($record->getState()->getFinal() && $request->get("reupdate")){
                        $mode="c";$scriptUrl = urlencode($baseUrl . "../js/js_oarodoc/".$jsActivityName.".js?v=".uniqid());
                    }
                }
            }
            

            if($record->getState()->getId()==2 || $record->getState()->getId()==5){
                $scriptUrl = urlencode($baseUrl . "../js/js_oarodoc/validation_cancel.js?v=".uniqid());
            }


            $callback_url=urlencode($baseUrlAux."docoaro/".$id."/save?token=".$token_get_data.$custom_view);
            $get_data_url=urlencode($baseUrlAux."docoaro/".$id."/getdata?token=".$token_get_data."&mode=".$mode.$custom_view);
            //echo $baseUrlAux."docoaro/".$id."/getdata?token=".$token_get_data."&mode=".$mode.$custom_view;die();
            $redirectUrl = urlencode($this->container->get('router')->generate('nononsense_cv_record', array("id" => $id),TRUE));
            $styleUrl = urlencode($baseUrl . "../css/css_oarodoc/standard.css?v=".uniqid());

            $base_url=$this->getParameter('api_docoaro')."/documents/".$record->getTemplate()->getPlantillaId()."?scriptUrl=".$scriptUrl."&styleUrl=".$styleUrl."&callbackUrl=".$callback_url."&redirectUrl=".$redirectUrl."&getDataUrl=".$get_data_url;

            $record->setInEdition(TRUE);
            $record->setOpenDate(new \DateTime());
            $record->setOpenedBy($user);
        }
        else{
            $get_data_url=urlencode($baseUrlAux."docoaro/".$id."/getdata?token=".$token_get_data."&mode=pdf".$custom_view);
            //echo $baseUrlAux."docoaro/".$id."/getdata?token=".$token_get_data."&mode=pdf".$custom_view;die();
            $scriptUrl = urlencode($baseUrl . "../js/js_oarodoc/show.js?v=".uniqid());
            $styleUrl = urlencode($baseUrl . "../css/css_oarodoc/standard.css?v=".uniqid());

            $base_url=$this->getParameter('api_docoaro')."/documents/".$record->getTemplate()->getPlantillaId()."?getDataUrl=".$get_data_url."&scriptUrl=".$scriptUrl."&styleUrl=".$styleUrl;
        }

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
        
        $em->persist($record);
        $em->flush();

        if(!$request->get("pdf")){
            $url_edit_documento=$response["fillInUrl"];
        }
        else{
           $url_edit_documento=$response["pdfUrl"];
        }

        if ($request->get("no-redirect") !== null && $request->get("no-redirect")) {
            return new Response($url_edit_documento);

        }
        else{
            return $this->redirect($url_edit_documento);
        }
    }

    public function getDataAction(Request $request, int $id)
    {   
        $json_content["data"]["u_id_cumplimentacion"]=$id;

        $id_usuario = $this->get('utilities')->getUserByToken($_REQUEST["token"]);
        if(!$id_usuario){
            return false;
        }
        
        $record = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $id));
        if(!$record){
            return false;
        }

        if($request->get("audittrail")){
            $audittrail=1;
            $only_signatures=0;
            $json_content["data"]["dxo_gsk_audit_trail"] = $this->get_signatures($record,1);
        }
        else{
            $audittrail=0;
            $only_signatures=1;
            $json_content["data"]["dxo_gsk_firmas"] = $this->get_signatures($record,0);
        }

        $json_content["data"]["dxo_gsk_audit_trail_bloque"] = $audittrail;
        $json_content["data"]["dxo_gsk_firmas_bloque"] = $only_signatures;

        if($request->get("logbook") && $request->get("logbook")>0){
            $json_content["data"]["dxo_gsk_logbook"] = 1;
            $json_content["data"]["dxo_gsk_logbook_bloque"] = $this->get_logbook($record,$request->get("logbook"));
        }
        else{
            $json_content["data"]["dxo_gsk_logbook"] = 0;
            $json_content["data"]["dxo_gsk_logbook_bloque"] = "";
        }


        

        $signature = $this->getDoctrine()->getRepository(CVSignatures::class)->findOneBy(array("record" => $record),array("id" => "DESC"));
        $json2=$signature->getJson();

        if($request->get("auxjson")){
            $json2=$signature->getJsonAux();
        }

        $json_content2=json_decode($json2,TRUE);

        if (array_key_exists("data",$json_content2)){
            $json_content["data"]=array_merge($json_content2["data"],$json_content["data"]);
        }

        if (array_key_exists("gsk_is_manual_fill",$json_content["data"])){
            unset($json_content["data"]["gsk_is_manual_fill"]);
        }

        if (array_key_exists("gsk_comment",$json_content["data"])){
            unset($json_content["data"]["gsk_comment"]);
        }

        if($request->get("mode")){
            switch($request->get("mode")){
                case "c":   $json_content["configuration"]["prefix_view"]="u_;in_;dxo_";
                            $json_content["configuration"]["apply_required"]=1;
                            if($signature->getAction()->getId()==15 && !$signature->getSigned()){
                                $json_content["configuration"]["cancel_button"]=0;
                            }
                            $json_content["configuration"]["partial_save_button"]=1;
                            $json_content["configuration"]["cancel_button"]=1;
                            $json_content["configuration"]["close_button"]=1;
                    break;
                case "v":   $json_content["configuration"]["prefix_view"]="";
                            $json_content["configuration"]["prefix_edit"]="verchk_;";
                            $json_content["configuration"]["apply_required"]=1;
                            $json_content["configuration"]["partial_save_button"]=1;
                            $json_content["configuration"]["cancel_button"]=1;
                            $json_content["configuration"]["close_button"]=1;
                    break;
                case "pdf": $json_content["configuration"]["prefix_view"]="";
                            $json_content["configuration"]["form_readonly"]=1;
                            $json_content["configuration"]["apply_required"]=0;
                            $json_content["configuration"]["partial_save_button"]=0;
                            $json_content["configuration"]["cancel_button"]=0;
                            $json_content["configuration"]["close_button"]=1;
                    break;
            }
        }

        //Opciones activas para el estado "Solicitada cancelación en..."
        if($record->getState()->getId()==2 || $record->getState()->getId()==5){
            $json_content["configuration"]["form_readonly"]=1;
            $json_content["configuration"]["prefix_view"]="";
            $json_content["configuration"]["partial_save_button"]=1;
            $json_content["configuration"]["cancel_button"]=1;
            $json_content["configuration"]["close_button"]=1;
        }

        //Si es una modificación de la plantilla quitamos algunos botones para que tenga que cumplimentar entera la plantilla
        if($request->get("reupdate") && $signature->getSigned()){
            $json_content["configuration"]["prefix_view"]="u_;in_;dxo_;verchk_";
            $json_content["configuration"]["prefix_edit"]="u_;in_;dxo_";
            $json_content["configuration"]["apply_required"]=1;
            $json_content["configuration"]["partial_save_button"]=0;
            $json_content["configuration"]["cancel_button"]=0;
            $json_content["configuration"]["close_button"]=1;
        }

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent(json_encode($json_content));

        return $response;
    }

    public function saveAction(int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $expired_token = $this->get('utilities')->tokenExpired($_REQUEST["token"]);

        if(!$expired_token){
            $id_usuario = $this->get('utilities')->getUserByToken($_REQUEST["token"]);
            if(!$id_usuario){
                return false;
            }
            $this->get('utilities')->tokenRemove($_REQUEST["token"]);
            
            $record = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $id));
            if(!$record){
                return false;
            }

            $request = Request::createFromGlobals();
            $params = array();
            $content = $request->getContent();

            if (!empty($content))
            {
                $params = json_decode($content, true); // 2nd param to get as array
            }

            if($params["action"]=="close"){
                $record->setInEdition(FALSE);
                $record->setOpenedBy(NULL);
                $record->setRedirectSearch(TRUE);
                $em->persist($record);
                $em->flush();
                return false;
            }

            $json_value=json_encode(array("data" => $params["data"], "action" => $params["action"]), JSON_FORCE_OBJECT);
            $json_record=json_encode(array("configuration" => $params["configuration"]), JSON_FORCE_OBJECT);

            $user = $this->getDoctrine()->getRepository(Users::class)->findOneBy(array("id" => $id_usuario));

            $users_actions=$this->get('utilities')->get_users_actions($user,1);
            

            $can_sign = $this->getDoctrine()->getRepository(CVRecords::class)->search("count",array("id" => $record->getId(),"pending_for_me" => 1,"users" => $users_actions));

            if($can_sign==0 && !$request->get("reupdate")){
                return false;
            }

            //Miramos wf que le toca

            $wf=$this->get('utilities')->wich_wf($record,$user,1);
            if(!$wf && !$request->get("reupdate")){
                return false;
            }

            //Miramos si es el último firmante del workflow dentro de una misma fase
            if($wf){
                $last_wf = $this->getDoctrine()->getRepository(CVWorkflow::class)->search("count",array("record" => $record,"not_this" => $wf->getId(),"signed" => FALSE,"type"=>$wf->getType()->getTmType()));
                if($last_wf==0){
                    $finish_workflow=1;
                }
                else{
                    $finish_workflow=0;
                }
            }
            else{
                $finish_workflow=0;
            }

            $token=$_REQUEST["token"];
            
            $array_item=array();

            
            if(!$record->getState() || !$record->getState()->getFinal() || $request->get("reupdate")){
                $all_signatures = $this->getDoctrine()->getRepository(CVSignatures::class)->findBy(array("record" => $record)); 
                $last_signature = $this->getDoctrine()->getRepository(CVSignatures::class)->findOneBy(array("record" => $record),array("id" => "DESC"));

                if($last_signature->getSigned()){
                    $signature = new CVSignatures();
                    $signature->setUser($user);
                    $signature->setRecord($record);
                    $signature->setNumberSignature((count($all_signatures)+1));
                    $signature->setJustification(FALSE);
                    $signature->setCreated($record->getOpenDate());

                    //Miramos si es una firma delegada o no
                    $delegation=FALSE;

                    if($wf && $wf->getUser()!=$user){
                        $delegation=TRUE;
                        foreach($user->getGroups() as $uniq_group){
                            if($uniq_group->getGroup()==$wf->getGroup()){
                                $delegation=FALSE;
                                break;
                            }
                        }
                    }
                    $signature->setDelegation($delegation);
                }
                else{

                    if(!$last_signature->getSigned() && $last_signature->getUser()==$user){
                        $signature=$last_signature;
                    }
                    else{
                        return false;
                    }

                    if($signature->getAction()->getId()==15){
                        switch($params["action"]){
                            case "save_partial": 
                                $signature->setFinish(FALSE);
                                break;
                            case "save": 
                                $signature->setFinish(TRUE);
                                break;
                            case "cancel":
                                $signature->setFinish(TRUE);
                                $fix_action = $this->getDoctrine()->getRepository(CVActions::class)->findOneBy(array("id" => 1));
                                $signature->setAction($fix_action);
                                break;
                        }
                    }
                }

                $state_id="1";
                if($record->getState()){
                    $state_id=$record->getState()->getId();
                }
                
                switch($state_id){
                    case "1":
                        switch($params["action"]){
                            case "save_partial": 
                                $action_id=5;
                                break;
                            case "save": 
                                if($finish_workflow){
                                    $action_id=4;
                                }
                                else{
                                    $action_id=16;
                                }
                                break;
                            case "cancel":
                                $action_id=1;
                                break;
                        }
                        break;
                    case "2":
                        switch($params["action"]){
                            case "save_partial": 
                                $action_id=3;
                                break;
                            case "cancel":
                                $action_id=2;
                                break;
                        }
                        break;
                    case "4":
                        if($wf && $wf->getType()->getId()==3){
                            switch($params["action"]){
                                case "save_partial": 
                                    $action_id=32;
                                    break;
                                case "save": 
                                    if($finish_workflow){
                                        $action_id=33;
                                    }
                                    else{
                                        $action_id=35;
                                    }
                                    break;
                                case "cancel":
                                    $action_id=34;
                                    break;
                                case "return":
                                    $action_id=31;
                                    break;
                            }
                        }
                        else{
                            switch($params["action"]){
                                case "save_partial": 
                                    $action_id=7;
                                    break;
                                case "save": 
                                    if($finish_workflow){
                                        $action_id=8;
                                    }
                                    else{
                                        $action_id=17;
                                    }
                                    break;
                                case "cancel":
                                    $action_id=9;
                                    break;
                                case "return":
                                    $action_id=6;
                                    break;
                            }
                        }
                        break;
                    case "5":
                        switch($params["action"]){
                            case "save_partial": 
                                $action_id=11;
                                break;
                            case "cancel":
                                $action_id=10;
                                break;
                        }
                        break;
                }

                if(!isset($action_id) && $record->getState()->getFinal()){
                    $action_id=18;
                    
                }
                
                if(!$signature->getAction()){ 
                    $action = $this->getDoctrine()->getRepository(CVActions::class)->findOneBy(array("id" => $action_id));
                }
                else{
                    $action=$signature->getAction();
                }

                $base_url=$this->getParameter('api_docoaro')."/documents/".$record->getTemplate()->getPlantillaId();
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

                $signature->setAction($action);
                $signature->setSigned(FALSE);
                $signature->setModified(new \DateTime());
                if($action_id!=18){
                    $signature->setJson($json_value);
                }
                else{
                    $signature->setJson($last_signature->getJson());
                    $signature->setJsonAux($json_value);
                }
                $signature->setVersion($response["version"]["id"]);
                $signature->setConfiguration($response["version"]["configuration"]["id"]);
                if(array_key_exists("gsk_comment",$params["data"]) && $params["data"]["gsk_comment"]){
                   $signature->setJustification(TRUE); 
                }

                if(array_key_exists("gsk_is_manual_fill",$params["data"]) && $params["data"]["gsk_is_manual_fill"]){
                   $signature->setManualFill(TRUE); 
                }

                $em->persist($signature);
                $record->setInEdition(FALSE);
                $record->setOpenedBy(NULL);
                $record->setModified(new \DateTime());
                $record->setPending(FALSE);
                $record->setOpenDate(NULL);
                $record->setJson($json_record);
                $em->persist($record);
                $em->flush();
            }

            $responseAction = new Response();
            $responseAction->setStatusCode(200);
            $responseAction->setContent("OK");
            return $responseAction;

        }
    }

    public function auditTrailAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $user = $this->container->get('security.context')->getToken()->getUser();

        $record = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $id));
        if(!$record){
            return false;
        }

        $html='<html><body style="font-size:8px;width:100%">Documento: <b>'.$record->getTemplate()->getName().'</b><br>Registro:<b>'.$record->getId().'</b><br><br>'.$this->get_signatures($record,1).'</body></html>';

        $this->get('utilities')->returnPDFResponseFromHTML($html);
    }

    private function get_signatures($record,$audittrail)
    {
        $fullText = "";
        $signatures = $this->getDoctrine()->getRepository(CVSignatures::class)->findBy(array("record" => $record, "signed" => TRUE),array("id" => "ASC"));
        if($signatures){
            $fullText = '<table id="tablefirmas" class="table" style="max-width:none!important"><tr><td colspan="7" width="100%"><b>Firmas</b></td></tr>';
            foreach ($signatures as $key => $signature) {
                $id = $signature->getNumberSignature();
                $name = $signature->getUser()->getName();
                $date = $signature->getModified()->format('d-m-Y H:i:s');
                $comment="";
                if($signature->getDescription()){
                    $comment = "Comentarios: ".$signature->getDescription()."<br>";
                }

                if($signature->getDelegation()){
                    $comment .= "Delegación de firma por ausencia<br>";
                }

                if($signature->getRecord()->getReconciliation()){
                    $action = $signature->getAction()->getNameReconc();
                    $comment .= '"'.$signature->getAction()->getDescriptionReconc().'"';
                }
                else{
                    $action = $signature->getAction()->getName();
                    $comment .= '"'.$signature->getAction()->getDescription().'"';
                }

                $fullText .= '<tr><td width="5%">' . $id . '</td><td colspan="6">'.$action.'</td></tr><tr><td width="5%"></td><td width="15%">' . $name . '<br>' . $date . '</td><td width="80%" colspan="4">'.$comment .'</td></tr>';
                if($audittrail){
                    $first=1;
                    foreach($signature->getChanges() as $change){
                        if($change->getLineOptions()!=1){
                            if($first){
                                $fullText .= '<tr><td></td><td>Linea</td><td>Campo</td><td>Valor actual</td><td>Valor anterior</td><td>Acción</td></tr>';
                                $first=0;
                            }
                            if($change->getInfo()){
                                $field=$change->getInfo();
                            }
                            else{
                                $field=$change->getField();
                            }

                            if(is_numeric($change->getIndex())){
                                $index=$change->getIndex();
                            }
                            else{
                                $index=-1;
                            }

                            $fullText .= '<tr><td></td><td>Linea '.($index+1).'</td><td>'.$field.'</td>';
                            if(!is_null($change->getLineOptions())){
                                $fullText .= '<td></td><td>'.$change->getValue().'</td><td>Eliminado</td>';
                            }
                            else{
                                $fullText .= '<td>'.$change->getValue().'</td><td>'.$change->getPrevValue().'</td><td>Modificado</td>';
                            }
                            $fullText .= '</tr>';
                        }
                    }
                }
                $fullText .= '<tr><td colspan="7" width="100%"></td></tr>';
            }
            $fullText .= '</table>';
        }
        return $fullText;
    }

    private function get_logbook($current,$num)
    {
        $fullText = "";

        $users_actions=$this->get('utilities')->get_users_actions($current->getUser(),1);

        $records = $this->getDoctrine()->getRepository(CVRecords::class)->search("list",array("users" => $users_actions,"not_this"=>$current->getId(),"plantilla_id"=>$current->getTemplate()->getId(),"limit_from" => 0, "limit_many" => $num,"have_json" => 1,"have_signature" => 1));
        
        $array_records=array();
        $array_fields=array();
        if($records){
            $fullText = "<table id='tablelogbook' class='table' style='max-width:none!important'><tr><td><b>Id</b></td><td><b>Estado</b></td>";
            $array_records=array();
            foreach($records as $key => $row_record){
                $config_json = json_decode($row_record["json"],TRUE);
                $signature = $this->getDoctrine()->getRepository(CVSignatures::class)->search("list",array("record" => $row_record["id"],"signed" => TRUE,"have_json" => 1),array("id" => "DESC"));

                $data = json_decode($signature[0]->getJson(),TRUE);
                foreach($data["data"] as $field => $obj){

                    if (!preg_match("/^(in_|gsk_|dxo_|delete_|verchk_)|(name|extension\b)/", $field)){
                        if(array_key_exists($field, $config_json["configuration"]["variables"]) && $config_json["configuration"]["variables"][$field]["info"]!=""){
                            $array_fields[$field]=$config_json["configuration"]["variables"][$field]["info"];
                        }
                        else{
                            if(array_key_exists($field, $array_fields)){
                                $array_fields[$field]=$array_fields[$field];
                            }
                            else{
                                $array_fields[$field]=$field;
                            }
                        }

                        if(is_array($obj)){
                            foreach($obj as $key => $value){
                                $array_records[$row_record["id"]]["values"][$field][$key]=$value;
                            }
                        }
                        else{
                            $array_records[$row_record["id"]]["values"][$field][0]=$obj;
                        }

                    }
                    $array_records[$row_record["id"]]["state"]=$row_record["state"];
                    $array_records[$row_record["id"]]["finalState"]=$row_record["finalState"];

                }
            }

            foreach($array_fields as $show_field){
                $fullText .= "<td><b>".$show_field."</b></td>";
            }

            $fullText .= "</tr>";

            foreach($array_records as $id => $row){
                $fullText .= "<tr><td>".$id."</td><td>".$row["state"]."</td>";
                foreach(array_keys($array_fields) as $field){
                   $fullText .= "<td>";
                   if (array_key_exists($field, $row["values"])) {
                        if(is_array($row["values"][$field])){
                            foreach($row["values"][$field] as $key => $value){
                                if($value=="" && $row["finalState"]){
                                    $value="N/A";
                                }
                                if(is_array($value)){
                                    $fullText .= "<img src='".$value["value"]."' style='width:100px'><br>";
                                }
                                else{
                                    $fullText .= $value."<br>";
                                }
                                
                            }
                        }
                        else{
                            $fullText .= $row["values"][$field]."<br>";
                        }
                    }
                    $fullText .= "</td>";
                }
                $fullText .= "</tr>";
            }
            $fullText .= "</table>";
        }
        return $fullText;
    }
}