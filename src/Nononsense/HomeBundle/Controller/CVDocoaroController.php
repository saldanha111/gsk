<?php
namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasSteps;
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

        $baseUrl = $this->getParameter("cm_installation");
        $baseUrlAux = $this->getParameter("cm_installation_aux");

        if($record->getState() && !$record->getState()->getCanBeOpened()){
           $this->get('session')->getFlashBag()->add(
                'error',
                    'La plantilla indicada no se puede abrir por su estado actual'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        if($record->getInEdition()){
           $this->get('session')->getFlashBag()->add(
                'error',
                    'La plantilla se encuentra en edición'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $custom_view="";

        if($request->get("audittrail")){
            $custom_view.="&audittrail=1";
        }

        if($request->get("logbook")){
            $custom_view.="&logbook=".$request->get("logbook");
        }


        if(!$record->getState() || (!$record->getState()->getFinal() && !$request->get("pdf"))){ // Si no es un estado final y no queremos sacar un pdf
            $mode="c";
            if($record->getState()){
                switch($record->getState()->getType()->getName()){
                    case "Cumplimentador": $mode="c";break;
                    case "Verificador": $mode="v";break;
                }
            }

            $token_get_data = $this->get('utilities')->generateToken();

            
            $callback_url=urlencode($baseUrlAux."docoaro/".$id."/save?token=".$token_get_data);
            $get_data_url=urlencode($baseUrlAux."docoaro/".$id."/getdata?token=".$token_get_data."&mode=".$mode.$custom_view);

            $redirectUrl = urlencode($this->container->get('router')->generate('nononsense_cv_record', array("id" => $id),TRUE));
            $scriptUrl = urlencode($baseUrl . "../js/js_oarodoc/activity.js?v=".uniqid());
            $styleUrl = urlencode($baseUrl . "../css/css_oarodoc/standard.css?v=".uniqid());

            $base_url=$this->getParameter('api_docoaro')."/documents/".$record->getTemplate()->getPlantillaId()."?scriptUrl=".$scriptUrl."&styleUrl=".$styleUrl."&callbackUrl=".$callback_url."&redirectUrl=".$redirectUrl."&getDataUrl=".$get_data_url;
        }
        else{
            $get_data_url=urlencode($baseUrlAux."docoaro/".$id."/getdata?token=".$token_get_data."&mode=pdf".$custom_view);
            $scriptUrl = urlencode($baseUrl . "../js/js_oarodoc/show.js?v=".uniqid());

            $base_url=$this->getParameter('api_docoaro')."/documents/".$record->getTemplate()->getPlantillaId()."?getDataUrl=".$get_data_url."&scriptUrl=".$scriptUrl;
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

        $record->setInEdition(TRUE);
        $em->persist($record);
        $em->flush();

        if(!$request->get("pdf")){
            return $this->redirect($response["fillInUrl"]);
        }
        else{
            return $this->redirect($response["pdfUrl"]);
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

        $signature = $this->getDoctrine()->getRepository(CVSignatures::class)->findOneBy(array("record" => $record),array("id" => "DESC"));
        $json2=$signature->getJson();

        $json_content2=json_decode($json2,TRUE);

        if (array_key_exists("data",$json_content2)){
            $json_content["data"]=array_merge($json_content["data"],$json_content2["data"]);
        }

        if($request->get("mode")){
            switch($request->get("mode")){
                case "c": $json_content["configuration"]["prefix_view"]="u_;in_;dxo_";break;
                case "v": $json_content["configuration"]["prefix_view"]="";$json_content["configuration"]["prefix_edit"]="verchk_;";break;
                case "pdf": $json_content["configuration"]["prefix_view"]="";$json_content["configuration"]["form_readonly"]=1;break;
            }
        }

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent(json_encode($json_content));

        return $response;
    }

    public function saveAction(int $id)
    {
        $expired_token = $this->get('utilities')->tokenExpired($_REQUEST["token"]);

        if(!$expired_token){
            $id_usuario = $this->get('utilities')->getUserByToken($_REQUEST["token"]);
            if(!$id_usuario){
                return false;
            }
            
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

            $json_value=json_encode(array("data" => $params["data"], "action" => $params["action"]), JSON_FORCE_OBJECT);

            $user = $this->getDoctrine()->getRepository(Users::class)->findOneBy(array("id" => $id_usuario));

            $token=$_REQUEST["token"];
            $em = $this->getDoctrine()->getManager();
            $array_item=array();

            
            if(!$record->getState() || !$record->getState()->getFinal()){
                $all_signatures = $this->getDoctrine()->getRepository(CVSignatures::class)->findBy(array("record" => $record)); 
                $last_signature = $this->getDoctrine()->getRepository(CVSignatures::class)->findOneBy(array("record" => $record),array("id" => "DESC"));

                if($last_signature->getSigned()){
                    $signature = new CVSignatures();
                    $signature->setUser($user);
                    $signature->setRecord($record);
                    $signature->setNumber((count($all_signatures)+1));
                    $signature->setJustification(FALSE); 
                }
                else{

                    if(!$last_signature->getSigned() && $last_signature->getUser()==$user){
                        $signature=$last_signature;
                    }
                    else{
                        return false;
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
                                $finish_user=0;
                                $action_id=5;
                                break;
                            case "save": 
                                $finish_user=1;
                                if($finish_workflow){
                                    $action_id=4;
                                }
                                else{
                                    $action_id=16;
                                }
                                break;
                            case "cancel":
                                $finish_user=1;
                                $action_id=1;
                                break;

                        }
                        break;
                    case "2":
                        switch($params["action"]){
                            case "save_partial": 
                                $finish_user=1;
                                $action_id=3;
                                break;
                            case "cancel":
                                $finish_user=1;
                                $action_id=2;
                                break;

                        }
                        break;
                    case "4":
                        switch($params["action"]){
                            case "save_partial": 
                                $finish_user=0;
                                $action_id=7;
                                break;
                            case "save": 
                                $finish_user=1;
                                if($finish_workflow){
                                    $action_id=8;
                                }
                                else{
                                    $action_id=17;
                                }
                                break;
                            case "cancel":
                                $finish_user=1;
                                $action_id=9;
                                break;
                            case "return":
                                $finish_user=1;
                                $action_id=6;
                                break;
                        }
                        break;

                    case "5":
                        switch($params["action"]){
                            case "save_partial": 
                                $finish_user=1;
                                $action_id=11;
                                break;
                            case "cancel":
                                $finish_user=1;
                                $action_id=10;
                                break;
                        }
                        break;
                }
                

                $action = $this->getDoctrine()->getRepository(CVActions::class)->findOneBy(array("id" => $action_id));

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
                $signature->setCreated(new \DateTime());
                $signature->setModified(new \DateTime());
                $signature->setJson($json_value);
                $signature->setVersion($response["version"]["id"]);
                $signature->setConfiguration($response["version"]["configuration"]["id"]);
                if(array_key_exists("gsk_comment",$params["data"]) && $params["data"]["gsk_comment"]){
                   $signature->setJustification(TRUE); 
                }
                $em->persist($signature);
                $record->setInEdition(FALSE);
                $em->persist($record);
                $em->flush();
            }

            $responseAction = new Response();
            $responseAction->setStatusCode(200);
            $responseAction->setContent("OK");
            return $responseAction;

        }
    }

    private function get_signatures($record,$audittrail)
    {
        $fullText = "";
        $signatures = $this->getDoctrine()->getRepository(CVSignatures::class)->findBy(array("record" => $record, "signed" => TRUE),array("id" => "DESC"));
        if($signatures){
            $fullText = "<table id='tablefirmas' class='table table-striped'>";
            foreach ($signatures as $key => $signature) {
                $id = $signature->getNumber();
                $name = $signature->getUser()->getName();
                $date = $signature->getModified()->format('d-m-Y H:i:s');
                $comment = $signature->getAction()->getName();

                $fullText .= "<tr><td colspan='4'>Firma</td></tr><tr><td width='5%'>" . $id . "</td><td width='20%'>" . $name . " " . $date . "</td><td>Comentarios: " . $comment . "</td></tr>";
            }
            $fullText .= "</table>";
        }

        return $fullText;
    }
}