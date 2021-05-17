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
        

        $baseUrl = $this->getParameter("cm_installation");
        $baseUrlAux = $this->getParameter("cm_installation_aux");

        if(!$request->get("pdf")){
        
            if($request->get("mode")){
                $mode=$request->get("mode");
            }
            else{
                $mode="c";
            }
        

            $token_get_data = $this->get('utilities')->generateToken();

            
            $callback_url=urlencode($baseUrlAux."docoaro/".$id."/save?token=".$token_get_data);
            $get_data_url=urlencode($baseUrlAux."docoaro/".$id."/getdata?mode=".$mode);

            $redirectUrl = urlencode($this->container->get('router')->generate('nononsense_tm_test_detail', array("id" => $id),TRUE)."?token=".$token_get_data);
            $scriptUrl = urlencode($baseUrl . "../js/js_oarodoc/activity.js?v=".uniqid());
            $styleUrl = urlencode($baseUrl . "../css/css_oarodoc/standard.css?v=".uniqid());


            $base_url=$this->getParameter('api_docoaro')."/documents/".$record->getTemplate()->getPlantillaId()."?scriptUrl=".$scriptUrl."&styleUrl=".$styleUrl."&callbackUrl=".$callback_url."&redirectUrl=".$redirectUrl."&getDataUrl=".$get_data_url;

        }
        else{
            $get_data_url=urlencode($baseUrlAux."docoaro/".$id."/getdata?mode=pdf");
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

        if(!$request->get("pdf")){
            return $this->redirect($response["fillInUrl"]);
        }
        else{
            return $this->redirect($response["pdfUrl"]);
        }
    }

    public function getDataAction(Request $request, int $id)
    {
        $json=file_get_contents($this->getParameter("cm_installation_aux")."../bundles/nononsensehome/json-data-test.json");
        
        $json_content=json_decode($json,TRUE);

        if($request->get("test")){
            $test = $this->getDoctrine()->getRepository(TMTests::class)->findOneBy(array("id" => $request->get("test")));
            if(!$test){
                return false;
            }
            $json2=$test->getTest();
            $json2=str_replace("gsk_id_firm"," [<i class='fa fa-fw fa-check'></i>in.] ",$json2);

            $json_content2=json_decode($json2,TRUE);

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

            $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
            if($template->getTmState()->getId()!=3){
                return FALSE;
            }

            if($request->get("test")){
                $tm = $this->getDoctrine()->getRepository(TMTests::class)->findOneBy(array("id" => $request->get("test")));
                if(!$tm){
                    return FALSE;
                }
                $test_master=$request->get("test");
            }
            else{
                $test_master=NULL;
            }

            $test = new TMTests();
            $test->setUserEntiy($user);
            $test->setToken($token);
            $test->setTest($json_value);
            $test->setTestId($test_master);
            $test->setCreated(new \DateTime());
            $em->persist($test);
            $em->flush();

            $responseAction = new Response();
            $responseAction->setStatusCode(200);
            $responseAction->setContent("OK");
            return $responseAction;

        }
    }
}