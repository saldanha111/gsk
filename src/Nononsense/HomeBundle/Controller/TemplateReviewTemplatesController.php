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

class TemplateReviewTemplatesController extends Controller
{
    /* Se crea una solicitud de revisión de una plantilla */
    public function sendReviewAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array=array();

        $user = $this->container->get('security.context')->getToken()->getUser();
        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));

        if(!$this->get('app.security')->permissionSeccion('dueno_gp') && !$this->get('app.security')->permissionSeccion('elaborador_gp')){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos suficientes'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 2));
        $elaborators = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $action),array("id" => "ASC"));
        $find=0;
        foreach($elaborators as $elaborator){
            if($elaborator->getUserEntiy() && $elaborator->getUserEntiy()==$user){
                $find=1;
            }
        }

        if($user!=$template->getOwner() && $find==0){
            $this->get('session')->getFlashBag()->add(
                'error',
                'Solo el dueño o elaborador de esta plantilla puede crear una solicitud de revisión'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        if($template->getRequestReview()){
            $this->get('session')->getFlashBag()->add(
                'error',
                'Ya existe una solicitud de revisión abierta para esta plantilla'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        if(!empty($template->getDateReview()) && $template->getDateReview()>date("Y-m-d")){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No se puede realizar una solicitud de esta plantilla puesto que aún ha llegado la fecha de su revisión periódica'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        if($request->get("signature")){
            if($template){
                $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 12));
                if($action){
                    $this->get('session')->getFlashBag()->add('message','La solicitud de revisión ha sido tramitada');
                    $previous_signature = $this->getDoctrine()->getRepository(TMSignatures::class)->findOneBy(array("template"=>$template),array("id" => "ASC"));
                    if($template->getTmState()->getId()==6){
                        

                        $signature = new TMSignatures();
                        $signature->setTemplate($template);
                        $signature->setAction($action);
                        $signature->setUserEntiy($user);
                        $signature->setCreated(new \DateTime());
                        $signature->setModified(new \DateTime());
                        $signature->setSignature($request->get("signature"));
                        $signature->setVersion($previous_signature->getVersion());
                        $signature->setConfiguration($previous_signature->getConfiguration());
                        if($request->get("description")){
                            $signature->setDescription($request->get("description"));
                        }
                        $em->persist($signature);
                        $template->setRequestReview($signature);
                        $em->persist($template);

                        $users_notifications=array();
                        $action_elab = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 2));
                        $elabs = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $action_elab));
                        foreach($elabs as $elab){
                            if($elab->getUserEntiy()){
                                $users_notifications[]=$elab->getUserEntiy()->getEmail();
                            }
                            else{
                                foreach($elab->getGroupEntiy()->getUsers() as $user_group){
                                    $users_notifications[]=$user_group->getUser()->getEmail();
                                }
                            }
                        }

                        $subject="Solicitud de revisión";
                        $mensaje='Se ha tramitado la solicitud de revisión para la plantilla con ID '.$id.'. Para poder revisar dicha soliciutd puede acceder a "Gestión de plantillas -> Solicitudes de revisiones", buscar la plantilla correspondiente y pulsar en Tramitar';
                        $baseURL=$this->container->get('router')->generate('nononsense_tm_template_detail_review', array("id" => $id),TRUE);
                        foreach($users_notifications as $email){
                            $this->get('utilities')->sendNotification($email, $baseURL, "", "", $subject, $mensaje);
                        }

                        $em->flush();

                        $route=$this->container->get('router')->generate('nononsense_tm_templates');
                        return $this->redirect($route);
                    }
                    
                }
            }
        }

        $this->get('session')->getFlashBag()->add(
            'error',
            'No se ha podido efectuar la operación sobre la plantilla especificada. Es posible que ya se haya realizado una acción sobre ella o que la plantilla ya no exista'
        );
        $route=$this->container->get('router')->generate('nononsense_tm_templates');
        return $this->redirect($route);
    }

    public function detailAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        if(!$this->get('app.security')->permissionSeccion('aprobador_gp') && $this->get('app.security')->permissionSeccion('elaborador_gp')){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos suficientes'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $array_item["template"] = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($array_item["template"]->getTmState()->getId()!=6){
        	$this->get('session')->getFlashBag()->add(
                'error',
                'La plantilla indicada no se encuentra en vigor para poder realizar una revisión'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        $actions = $this->getDoctrine()->getRepository(TMActions::class)->findBy(array("id" => array(2,4)));
        $reviewers = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $actions),array("id" => "ASC"));
        $find=0;
        foreach($reviewers as $reviewer){
            if($reviewer->getUserEntiy() && $reviewer->getUserEntiy()==$user){
                $find=1;
            }
        }
        
        if($find==0){
            foreach($reviewers as $reviewer){
                if($reviewer->getGroupEntiy() && !$reviewer->getSigned()){
                    $in_group=0;
                    foreach($user->getGroups() as $uniq_group){
                        if($uniq_group->getGroup()==$reviewer->getGroupEntiy()){
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
                'No tiene permisos para revisar este documento'
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

        /* Para el listado de tests */
        $action_test = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 3));
        $action_aprob = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 4));
        $signatures = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template" => $array_item["template"], "action" => $action_test),array("id" => "ASC"));
        $array_item["tests"] = $this->getDoctrine()->getRepository(TMTests::class)->findBy(array("signature" => $signatures, "test_id" => NULL),array("id" => "DESC"));

        $array_item["tests_results_aprobs"] = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("tmTest" => $array_item["tests"], "action" => $action_aprob),array("id" => "ASC"));

        foreach($array_item["tests"] as $key_test => $item_test){
            $array_item["subtests"][$item_test->getId()] = $this->getDoctrine()->getRepository(TMTests::class)->findBy(array("signature" => $signatures, "test_id" => $item_test->getId()),array("id" => "DESC"));

            $array_item["subtests_results_aprobs"][$item_test->getId()] = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("tmTest" => $array_item["subtests"][$item_test->getId()], "action" => $action_aprob),array("id" => "ASC"));
        }
        /* Fin listado de tests */


        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 2));
        $array_item["elabs"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 4));
        $array_item["aprobs"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $array_item["cumplimentations"] = $this->getDoctrine()->getRepository(TMSecondWorkflow::class)->findBy(array("template" => $array_item["template"]),array("id" => "ASC"));
        $array_item["results"] = $this->getDoctrine()->getRepository(TMActions::class)->findBy(array("id" => array(13,14,15)),array("id" => "ASC"));


        return $this->render('NononsenseHomeBundle:TemplateManagement:review_detail.html.twig',$array_item);
    }

    public function updateAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $is_valid = $this->get('app.security')->permissionSeccion('elaborador_gp');
        if(!$is_valid){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos suficientes'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($template->getTmState()->getId()!=2){
            $this->get('session')->getFlashBag()->add(
                'error',
                'La plantilla indicada no se encuentra en estado de elaboración'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        if(!$template->getOpenedBy() || $template->getOpenedBy()!=$user){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No se puede efectuar la operación'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 2));
        $action_test = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 3));
        $elaborators = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $action),array("id" => "ASC"));
        $testers = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $action_test),array("id" => "ASC"));
        $find=0;
        foreach($elaborators as $elaborator){
            if($elaborator->getUserEntiy() && $elaborator->getUserEntiy()==$user){
                $elaborator->setSigned(TRUE);
                $em->persist($elaborator);
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
                        $elaborator->setSigned(TRUE);
                        $em->persist($elaborator);
                        break;
                    }
                }
            }
        }

        if($find==0){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos para elaborar este documento'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        if($request->files->get('template')){
            $base_url=$this->getParameter('api_docoaro')."/documents/".$template->getPlantillaId();
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
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        'Hubo un problema al firmar la configuración realizada. Es posible que la plantilla haya cambiado desde entonces'
                    );
                    $route=$this->container->get('router')->generate('nononsense_tm_templates');
                    return $this->redirect($route);
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

        if(!$response["version"]){
            $this->get('session')->getFlashBag()->add(
                'error',
                'Hubo un problema al subir el documento de la nueva plantilla'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
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
        $signature->setSignature($request->get("signature"));
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
        }

        $em->persist($template);
        
        $em->flush();

        $this->get('session')->getFlashBag()->add('message', "La operación se ha ejecutado con éxito");
        $route = $this->container->get('router')->generate('nononsense_tm_templates');
        return $this->redirect($route);
    }

    public function configurationAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $is_valid = $this->get('app.security')->permissionSeccion('elaborador_gp');
        if(!$is_valid){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos suficientes'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($template->getTmState()->getId()!=2){
            $this->get('session')->getFlashBag()->add(
                'error',
                'La plantilla indicada no se encuentra en estado de elaboración'
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
}