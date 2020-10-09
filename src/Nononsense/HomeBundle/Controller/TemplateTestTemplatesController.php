<?php
namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasSteps;
use Symfony\Component\Filesystem\Filesystem;
use Nononsense\UtilsBundle\Classes;

use Nononsense\UserBundle\Entity\Users;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\HomeBundle\Entity\Areas;
use Nononsense\HomeBundle\Entity\TMStates;
use Nononsense\HomeBundle\Entity\RetentionCategories;
use Nononsense\HomeBundle\Entity\AreaPrefixes;
use Nononsense\HomeBundle\Entity\TMTemplates;
use Nononsense\HomeBundle\Entity\TMActions;
use Nononsense\HomeBundle\Entity\TMSignatures;
use Nononsense\HomeBundle\Entity\TMWorkflow;
use Nononsense\HomeBundle\Entity\TMCumplimentations;
use Nononsense\HomeBundle\Entity\TMSecondWorkflow;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class TemplateTestTemplatesController extends Controller
{
    public function detailAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $user = $this->container->get('security.context')->getToken()->getUser();

        $array_item["template"] = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($array_item["template"]->getTmState()->getId()!=3){
        	$this->get('session')->getFlashBag()->add(
                'error',
                'La plantilla indicada no se encuentra en fase test'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 3));
        $testers = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $find=0;
        foreach($testers as $tester){
            if($tester->getUserEntiy() && $tester->getUserEntiy()==$user){
                $find=1;
            }
        }
        
        if($find==0){
            foreach($testers as $tester){
                if($tester->getGroupEntiy() && !$tester->getSigned()){
                    $in_group=0;
                    foreach($user->getGroups() as $uniq_group){
                        if($uniq_group->getGroup()==$tester->getGroupEntiy()){
                            $in_group=1;
                            break;
                        }
                    }
                    if($in_group==1){
                        $find=1;
                        break;
                    }
                }
            }
        }

        if($find==0){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos para testear este documento'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        $base_url=$this->getParameter('api_docoaro')."/documents/".$array_item["template"]->getPlantillaId();
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

        $url_fill_documento=$response["fillInUrl"];
        
        preg_match_all('/token=(.*?)$/s',$url_fill_documento,$var_token);
       	$token=$var_token[1][0];
       	
       	if(!$array_item["template"]->getOpenedBy() || $token!=$array_item["template"]->getToken()){
       		$array_item["template"]->setOpenedBy($user);
       		$array_item["template"]->setToken($token);
       		$em->persist($array_item["template"]);
			$em->flush();
       	}

        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 3));
        $array_item["testers"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));


        return $this->render('NononsenseHomeBundle:TemplateManagement:test_detail.html.twig',$array_item);
    }

    public function linkAction(Request $request, int $id)
    {
    	$em = $this->getDoctrine()->getManager();
        $array_item=array();

        $user = $this->container->get('security.context')->getToken()->getUser();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($template->getTmState()->getId()!=3){
            $this->get('session')->getFlashBag()->add(
                'error',
                'La plantilla indicada no se encuentra en fase test'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        if(!$template->getOpenedBy() || $template->getOpenedBy()!=$user){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No se puedo efectuar la operación'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        if($request->get("test")){
        	$id_test=$request->get("test");
        }
        else{
        	$id_test=0;
        }

        $token_get_data = $this->get('utilities')->generateToken();

        $baseUrl = $this->getParameter("cm_installation");
        $baseUrlAux = $this->getParameter("cm_installation_aux");
        $callback_url=urlencode($baseUrlAux."mt/test/".$id."/save?token=".$token_get_data."&test=".$id_test);
        $get_data_url=urlencode($baseUrlAux."mt/test/".$id."/getdata");

        $redirectUrl = urlencode($this->container->get('router')->generate('nononsense_tm_test_detail', array("id" => $template->getId()),TRUE)."?token=".$token_get_data);
        $scriptUrl = urlencode($baseUrl . "../js/js_oarodoc/activity.js?v=".uniqid());
        $styleUrl = urlencode($baseUrl . "../css/css_oarodoc/standard.css?v=".uniqid());


        $base_url=$this->getParameter('api_docoaro')."/documents/".$template->getPlantillaId()."?scriptUrl=".$scriptUrl."&styleUrl=".$styleUrl."&callbackUrl=".$callback_url."&redirectUrl=".$redirectUrl."&getDataUrl=".$get_data_url;
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
        
        return $this->redirect($response["fillInUrl"]);

    }

    public function getDataAction(Request $request, int $id)
    {
    	$json=file_get_contents($this->getParameter("cm_installation_aux")."../bundles/nononsensehome/json-data-test.json");

    	$json_content=json_decode($json);
    	$json_content->configuration->prefix_view="u_;in_;dxo_";

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

            $test = new TMTests();
            $test->setUserEntiy($user);
            $test->setToken($token);
            $test->setTest($json_value);
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