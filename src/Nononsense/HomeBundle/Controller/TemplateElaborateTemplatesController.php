<?php
namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
use Nononsense\HomeBundle\Entity\TMTests;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class TemplateElaborateTemplatesController extends Controller
{
    public function detailAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $is_valid = $this->get('app.security')->permissionSeccion('elaborador_gp');
        if(!$is_valid){
            return $this->returnToHomePage("No tiene permisos suficientes");
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $array_item["template"] = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($array_item["template"]->getTmState()->getId()!=2){
            return $this->returnToHomePage("La plantilla indicada no se encuentra en estado de elaboración");
        }

        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 2));
        $elaborators = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $find=0;
        foreach($elaborators as $elaborator){
            if($elaborator->getUserEntiy() && $elaborator->getUserEntiy()==$user){
                $find=1;
            }
        }
        
        if($find==0){
            foreach($elaborators as $elaborator){
                if($elaborator->getGroupEntiy() && !$elaborator->getSigned()){
                    $in_group=0;
                    foreach($user->getGroups() as $uniq_group){
                        if($uniq_group->getGroup()==$elaborator->getGroupEntiy()){
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
            return $this->returnToHomePage("No tiene permisos para elaborar este documento");
        }

        $array_item["type_cumplimentations"] = $this->getDoctrine()->getRepository(TMCumplimentations::class)->findBy(array(),array("id" => "ASC"));

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

        if($response["configurationUrl"]){
            $url_edit_documento=$response["configurationUrl"];
            $array_item["downloadUrl"]=$response["downloadUrl"];
        
            preg_match_all('/token=(.*?)$/s',$url_edit_documento,$var_token);
       	    $token=$var_token[1][0];
        }
        else{
            $token=NULL;
        }
       	
       	if(!$array_item["template"]->getOpenedBy() || $token!=$array_item["template"]->getToken() || $token==NULL){
       		$array_item["template"]->setOpenedBy($user);
       		$array_item["template"]->setToken($token);
       		$em->persist($array_item["template"]);
			$em->flush();
       	}

        /* Para el listado de tests */
        $approval_exists=0;
        $all_signatures = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template" => $array_item["template"]),array("id" => "ASC"));
        foreach($all_signatures as $key_all => $item_all){
            if($item_all->getAction()->getId()==2){
                $array_item["max_id_no_test"]=$item_all->getId();
            }

            if($item_all->getAction()->getId()==17){
                $array_item["max_id_re_approv"]=$item_all->getId();
            }

            if($item_all->getAction()->getId()==4){
                $approval_exists=1;
            }
        }
        $action_test = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 3));
        $action_aprob = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 4));
        $signatures = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template" => $array_item["template"], "action" => $action_test),array("id" => "ASC"));
        $array_item["tests"] = $this->getDoctrine()->getRepository(TMTests::class)->findBy(array("signature" => $signatures, "test_id" => NULL),array("id" => "DESC"));

        $array_item["tests_results_aprobs"] = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("tmTest" => $array_item["tests"], "action" => $action_aprob),array("id" => "ASC"));

        foreach($array_item["tests"] as $key_test => $item_test){
            $array_item["subtests"][$item_test->getId()] = $this->getDoctrine()->getRepository(TMTests::class)->findBy(array("signature" => $signatures, "test_id" => $item_test->getId()),array("id" => "DESC"));

            $array_item["subtests_results_aprobs"][$item_test->getId()] = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("tmTest" => $array_item["subtests"][$item_test->getId()], "action" => $action_aprob),array("id" => "ASC"));
        }
        if($approval_exists==1){
            $array_item["aprobs"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action_aprob),array("id" => "ASC"));
        }
        else{
            $array_item["aprobs"]=array();
        }
        /* Fin listado de tests */

        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 2));
        $array_item["elab"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $array_item["cumplimentations"] = $this->getDoctrine()->getRepository(TMSecondWorkflow::class)->findBy(array("template" => $array_item["template"]),array("id" => "ASC"));
        if(count($array_item["cumplimentations"])>0){
            $array_item["min_cumplimentations"]=1;
        }
        else{
            $array_item["min_cumplimentations"]=0;
        }


        return $this->render('NononsenseHomeBundle:TemplateManagement:elaboration_detail.html.twig',$array_item);
    }

    public function updateAction(Request $request, int $id)
    {

        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $is_valid = $this->get('app.security')->permissionSeccion('elaborador_gp');
        if(!$is_valid){
            return $this->returnToHomePage("No tiene permisos suficientes");
        }

        $password = $request->get('password');
        if(!$password || !$this->get('utilities')->checkUser($password)){
            return $this->returnToHomePage("No se pudo firmar el registro, la contraseña es incorrecta");
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($template->getTmState()->getId()!=2){
            return $this->returnToHomePage("La plantilla indicada no se encuentra en estado de elaboración");
        }

        if(!$template->getOpenedBy() || $template->getOpenedBy()!=$user){
            return $this->returnToHomePage("No se puede efectuar la operación");
        }

        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 2));
        $action_test = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 3));
        $elaborators = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $action),array("id" => "ASC"));
        $testers = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $action_test),array("id" => "ASC"));
        $find=0;
        foreach($elaborators as $elaborator){
            if($elaborator->getUserEntiy() && $elaborator->getUserEntiy()==$user){
                if($request->get("finish_elaboration")){
                    $elaborator->setSigned(TRUE);
                    $em->persist($elaborator);
                }
                $find=1;
            }
            else{
                if($request->get("description")){
                    $elaborator->setSigned(FALSE);
                    $em->persist($elaborator);
                }
            }
        }
        if($find==0){
            foreach($elaborators as $elaborator){

                if($elaborator->getGroupEntiy() && !$elaborator->getSigned()){
                    $in_group=0;
                    foreach($user->getGroups() as $uniq_group){

                        if($uniq_group->getGroup()==$elaborator->getGroupEntiy()){
                            $in_group=1;
                            break;
                        }
                    }
                    if($in_group==1){
                        $find=1;
                        if($request->get("finish_elaboration")){
                            $elaborator->setSigned(TRUE);
                            $em->persist($elaborator);
                        }
                        break;
                    }
                }
            }
        }

        if($find==0){
            return $this->returnToHomePage("No tiene permisos para elaborar este documento");
        }

        if($request->files->get('template')){
            if($template->getPlantillaId()){
                $base_url=$this->getParameter('api_docoaro')."/documents/".$template->getPlantillaId();
            }
            else{
                $base_url=$this->getParameter('api_docoaro')."/documents";
            }
            $fs = new Filesystem();
            $file = $request->files->get('template');
            $data_file = curl_file_create($file->getRealPath(), $file->getClientMimeType(), $file->getClientOriginalName());
            $post = array('name' => uniqid(),'file'=> $data_file);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
            curl_setopt($ch, CURLOPT_HTTPHEADER,array("Content-Type: multipart/form-data","Api-Key: ".$this->getParameter('api_key_docoaro')));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);    
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $raw_response = curl_exec($ch);
            $response = json_decode($raw_response, true);

            if(!$template->getPlantillaId() && isset($response["id"])){
                preg_match_all('/token=(.*?)$/s',$response["configurationUrl"],$var_token);
                $token=$var_token[1][0];

                $template->setToken($token);
                $template->setPlantillaId($response["id"]);
                $em->persist($template);
            }
            
        }
        else{
            if($request->get('activate_configuration')){

                $base_url=$this->getParameter('api_docoaro')."/configurations/".$template->getTmpConfiguration();
                $post = array();
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $base_url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"PATCH");
                curl_setopt($ch, CURLOPT_HTTPHEADER,array("Content-Type: multipart/form-data","Api-Key: ".$this->getParameter('api_key_docoaro')));
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);    
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $raw_response = curl_exec($ch);
                $response2 = json_decode($raw_response, true);

                if(!$response2["configuration"]){
                    return $this->returnToHomePage("Hubo un problema al firmar la configuración realizada. Es posible que la plantilla haya cambiado desde entonces");
                }

                $template->setTmpConfiguration(NULL);
            }
            
            $base_url=$this->getParameter('api_docoaro')."/documents/".$template->getPlantillaId();
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
            
        }

        if(!isset($response["version"])){
            return $this->returnToHomePage("Hubo un problema al subir el documento de la nueva plantilla");
        }

        if($request->get("cumplimentation")){
            $swfs = $this->getDoctrine()->getRepository(TMSecondWorkflow::class)->findBy(array("template" => $template));
            foreach($swfs as $swf){
                $em->remove($swf);
            }

            foreach($request->get("cumplimentation") as $key => $cumpl){
                $swf = new TMSecondWorkflow();
                $swf->setTemplate($template);
                $cumplimentation = $this->getDoctrine()->getRepository(TMCumplimentations::class)->findOneBy(array("id" => $cumpl));
                $swf->setTmCumplimentation($cumplimentation);
                $swf->setSignaturesNumber($request->get("signatures")[$key]);
                $em->persist($swf);
            }
        }

        $signature = new TMSignatures();
        $signature->setTemplate($template);
        $signature->setAction($action);
        $signature->setUserEntiy($user);
        $signature->setCreated(new \DateTime());
        $signature->setModified(new \DateTime());
        $signature->setSignature("-");
        $signature->setVersion($response["version"]["id"]);
        $signature->setConfiguration($response["version"]["configuration"]["id"]);
        if($request->get("description")){
            $signature->setDescription($request->get("description"));
        }
        $em->persist($signature);

        $template->setOpenedBy(NULL);
        $template->setToken(NULL);

        $next_step=1;
        foreach($elaborators as $elaborator){
            if(!$elaborator->getSigned()){
                $next_step=0;
            }
        }

        if($next_step==1){

            foreach($elaborators as $elaborator){
                $elaborator->setSigned(0);
                $em->persist($elaborator);
            }
                
            if(($template->getArea()->getId()==10 || $template->getArea()->getId()==11) && !$testers){
                $state = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id"=> 4));
            }
            else{
                $state = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id"=> 3));
            }
            $template->setTmState($state);

            $description=$signature->getDescription()." - Esta firma significa la declaración de que la elaboración se ha hecho siguiendo lo establecido en los procedimientos";
            $signature->setDescription($description);
            $em->persist($signature);

        }

        $em->persist($template);
        
        $em->flush();

        return $this->returnToHomePage("La operación se ha ejecutado con éxito", "message");
    }

    public function configurationAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $is_valid = $this->get('app.security')->permissionSeccion('elaborador_gp');
        if(!$is_valid){
            return $this->returnToHomePage("No tiene permisos suficientes");
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($template->getTmState()->getId()!=2){
            return $this->returnToHomePage("La plantilla indicada no se encuentra en estado de elaboración");
        }

        if(!$template->getOpenedBy() || $template->getOpenedBy()!=$user){
            return $this->returnToHomePage("No se puedo efectuar la operación");
        }

        $token_get_data = $this->get('utilities')->generateToken();


        $baseUrlAux = $this->getParameter("cm_installation_aux");
        $callback_url=$baseUrlAux."mt/elaborate/".$id."/restore?token=".$token_get_data;
        $redirectUrl = $this->container->get('router')->generate('nononsense_tm_elaborate_update', array("id" => $template->getId()),TRUE)."?sign=1";


        $base_url=$this->getParameter('api_docoaro')."/documents/".$template->getPlantillaId()."?callbackUrl=".$callback_url."&keyPrivated=".$this->getParameter('key_privated_config_docoaro')."&redirectUrl=".$redirectUrl;
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
        
        return $this->redirect($response["configurationUrl"]);
    }

    public function restoreLastConfigurationAction(Request $request, int $id)
    {
        $expired_token = $this->get('utilities')->tokenExpired($_REQUEST["token"]);

        if(!$expired_token){
            $id_usuario = $this->get('utilities')->getUserByToken($_REQUEST["token"]);
            $this->get('utilities')->tokenRemove($_REQUEST["token"]);
            
            $em = $this->getDoctrine()->getManager();
            $array_item=array();

            $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
            if($template->getTmState()->getId()!=2){
                return FALSE;
            }

            $last_signature = $this->getDoctrine()->getRepository(TMSignatures::class)->findOneBy(array("template" => $template),array("id" => "DESC"));

           
            $base_url=$this->getParameter('api_docoaro')."/configurations/".$last_signature->getConfiguration();
            $post = array();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"PATCH");
            curl_setopt($ch, CURLOPT_HTTPHEADER,array("Content-Type: multipart/form-data","Api-Key: ".$this->getParameter('api_key_docoaro')));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);    
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $raw_response = curl_exec($ch);
            $response = json_decode($raw_response, true);

            $content = $request->getContent();

            if (!empty($content))
            {
                $params = json_decode($content, true); // 2nd param to get as array
            }

            $template->setTmpConfiguration($params["configuration"]["id"]);
            $em->persist($template);
            $em->flush();

            $responseAction = new Response();
            $responseAction->setStatusCode(200);
            $responseAction->setContent("OK");
            return $responseAction;

        }
    }

    public function detailCancelAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        if(!$this->get('app.security')->permissionSeccion('elaborador_gp')){
            return $this->returnToHomePage("No tiene permisos suficientes");
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $array_item["template"] = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($array_item["template"]->getTmState()->getId()!=9){
            return $this->returnToHomePage("La plantilla indicada no se encuentra en solicitud de cancelación");
        }

        $actions = $this->getDoctrine()->getRepository(TMActions::class)->findBy(array("id" => array(2)));
        $elaborators = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $actions),array("id" => "ASC"));
        $find=0;
        foreach($elaborators as $elaborator){
            if($elaborator->getUserEntiy() && $elaborator->getUserEntiy()==$user && !$elaborator->getSigned()){
                $find=1;
            }
        }
        
        if($find==0){
            foreach($elaborators as $elaborator){
                if($elaborator->getGroupEntiy() && !$elaborator->getSigned()){
                    $in_group=0;
                    foreach($user->getGroups() as $uniq_group){
                        if($uniq_group->getGroup()==$elaborator->getGroupEntiy()){
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
            return $this->returnToHomePage("No tiene permisos para tramitar la cancelación de este documento o ya lo ha firmado previamente");
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

        $url_edit_documento=$response["configurationUrl"];
        $array_item["configurationUrl"]=$response["configurationUrl"];
        $array_item["downloadUrl"]=$response["downloadUrl"];

        
        preg_match_all('/token=(.*?)$/s',$url_edit_documento,$var_token);
        $token=$var_token[1][0];
        
        if(!$array_item["template"]->getOpenedBy() || $token!=$array_item["template"]->getToken()){
            $array_item["template"]->setOpenedBy($user);
            $array_item["template"]->setToken($token);
            $em->persist($array_item["template"]);
            $em->flush();
        }

        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 2));
        $array_item["elabs"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $array_item["cumplimentations"] = $this->getDoctrine()->getRepository(TMSecondWorkflow::class)->findBy(array("template" => $array_item["template"]),array("id" => "ASC"));
        $array_item["results"] = $this->getDoctrine()->getRepository(TMActions::class)->findBy(array("id" => array(16,17)),array("id" => "ASC"));


        return $this->render('NononsenseHomeBundle:TemplateManagement:elaboration_cancel.html.twig',$array_item);
    }

    public function updateCancelAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();
        $description="";

        if(!$this->get('app.security')->permissionSeccion('elaborador_gp')){
            return $this->returnToHomePage("No tiene permisos suficientes");
        }

        $password =  $request->get('password');
        if(!$password || !$this->get('utilities')->checkUser($password)){
            return $this->returnToHomePage("No se pudo firmar el registro, la contraseña es incorrecta");
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($template->getTmState()->getId()!=9){
            return $this->returnToHomePage("La plantilla indicada no se encuentra en solicitud de cancelación para poder realizar el trámite");
        }

        if(!$template->getOpenedBy() || $template->getOpenedBy()!=$user){
            return $this->returnToHomePage("No se puede efectuar la operación");
        }

        $actions = $this->getDoctrine()->getRepository(TMActions::class)->findBy(array("id" => array(2)));
        $elaborators = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $actions),array("id" => "ASC"));
        $find=0;
        foreach($elaborators as $elaborator){
            if($elaborator->getUserEntiy() && $elaborator->getUserEntiy()==$user && !$elaborator->getSigned()){
                $find=1;
                $wich_workflow=$elaborator;
            }
        }
        
        if($find==0){
            foreach($elaborators as $elaborator){
                if($elaborator->getGroupEntiy() && !$elaborator->getSigned()){
                    $in_group=0;
                    foreach($user->getGroups() as $uniq_group){
                        if($uniq_group->getGroup()==$elaborator->getGroupEntiy()){
                            $in_group=1;
                            $wich_workflow=$elaborator;
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
            return $this->returnToHomePage("No tiene permisos para tramitar la cancelación del documento o ya lo ha firmado previamente");
        }

        if($request->get("action") && in_array($request->get("action"), array(16,17))){
            $action_cancel = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => $request->get("action")));
        }
        else{
            return $this->returnToHomePage("Hubo un problema al tramitar la firma de solicitud de cancelación");
        }

        

        $base_url=$this->getParameter('api_docoaro')."/documents/".$template->getPlantillaId();
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

        $signature = new TMSignatures();
        $signature->setTemplate($template);
        $signature->setAction($action_cancel);
        $signature->setUserEntiy($user);
        $signature->setCreated(new \DateTime());
        $signature->setModified(new \DateTime());
        $signature->setSignature("-");
        $signature->setVersion($response["version"]["id"]);
        $signature->setConfiguration($response["version"]["configuration"]["id"]);

        if($request->get("description")){
            $description.=" ".$request->get("description");
        }

        $signature->setDescription($description);
        $em->persist($signature);

        $wich_workflow->setSigned(TRUE);
        $em->persist($wich_workflow);

        $template->setOpenedBy(NULL);
        $template->setToken(NULL);

        
        $user_workflow_finish=1;

        foreach($elaborators as $elaborator){
            if($wich_workflow!=$elaborator && !$elaborator->getSigned()){
                $user_workflow_finish=0;
            }
        }

        if($user_workflow_finish){
            //Vaciamos el control de los que ya han firmado puesto que la solicitud de cancelación cambia de estado
            foreach($elaborators as $elaborator){
                $elaborator->setSigned(0);
                $em->persist($elaborator);
            }

            $next_state=14;
            $userssignatures = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template" => $template),array("id" => "ASC"));
            $userssignatures[]=$signature;
            //Comprobamos las firmas realizadas para saber a que estado debemos pasar la plantilla
            foreach($userssignatures as $us){
                //Tenemos en cuenta solo las firmas correspondientes a la respuesta a la cancelación
                if($us->getAction()->getId()!=16 && $us->getAction()->getId()!=17){
                    $next_state=14;
                }
                else{
                    if($us->getAction()->getId()==17){
                        $next_state=4;
                    }
                }
                echo $us->getAction()->getId()."<br>";
            }

            $state = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id"=> $next_state));
            $template->setTmState($state);

            switch($next_state){
                case 14:
                    $this->get('session')->getFlashBag()->add('message', "La plantilla ha sido cancelada");
                    break;
                case 4:
                    $this->get('session')->getFlashBag()->add('message', "Se ha rechazado la cancelación de la plantilla debido a que una de las firmas no ha aprobado la cancelación");
                    break;
            }
        }
        else{
            $this->get('session')->getFlashBag()->add('message', "La firma correspondiente a la solicitud de cancelación se ha registrado correctamente");
        }
        
        $em->persist($template);
        $em->flush();


        $route = $this->container->get('router')->generate('nononsense_home_homepage');


        return $this->redirect($route);
    }

    private function returnToHomePage(string $msgError, string $type = "error"): RedirectResponse
    {
        $this->get('session')->getFlashBag()->add(
            $type,
            $msgError
        );
        $route=$this->container->get('router')->generate('nononsense_home_homepage');
        return $this->redirect($route);
    }
}