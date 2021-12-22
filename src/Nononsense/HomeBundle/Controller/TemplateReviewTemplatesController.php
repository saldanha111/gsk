<?php
namespace Nononsense\HomeBundle\Controller;

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
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
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
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        if($template->getRequestReview()){
            $this->get('session')->getFlashBag()->add(
                'error',
                'Ya existe una solicitud de revisión abierta para esta plantilla'
            );
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        if(!empty($template->getDateReview()) && $template->getDateReview()>date("Y-m-d")){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No se puede realizar una solicitud de esta plantilla puesto que aún ha llegado la fecha de su revisión periódica'
            );
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
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
                        $mensaje='Se ha tramitado la solicitud de revisión para la plantilla con Código '.$template->getNumber().' - Título: '.$template->getName().' - Edición: '.$template->getNumEdition().'. Para poder revisar dicha soliciutd puede acceder a "Gestión de plantillas -> Solicitudes de revisiones", buscar la plantilla correspondiente y pulsar en Tramitar';
                        $baseURL=$this->container->get('router')->generate('nononsense_tm_template_detail_review', array("id" => $id),TRUE);
                        foreach($users_notifications as $email){
                            $this->get('utilities')->sendNotification($email, $baseURL, "", "", $subject, $mensaje);
                        }

                        $em->flush();

                        $route=$this->container->get('router')->generate('nononsense_home_homepage');
                        return $this->redirect($route);
                    }
                    
                }
            }
        }

        $this->get('session')->getFlashBag()->add(
            'error',
            'No se ha podido efectuar la operación sobre la plantilla especificada. Es posible que ya se haya realizado una acción sobre ella o que la plantilla ya no exista'
        );
        $route=$this->container->get('router')->generate('nononsense_home_homepage');
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
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $array_item["template"] = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($array_item["template"]->getTmState()->getId()!=6){
        	$this->get('session')->getFlashBag()->add(
                'error',
                'La plantilla indicada no se encuentra en vigor para poder realizar una revisión'
            );
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $actions = $this->getDoctrine()->getRepository(TMActions::class)->findBy(array("id" => array(2,4)));
        $reviewers = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $actions),array("id" => "ASC"));
        $find=0;
        foreach($reviewers as $reviewer){
            if($reviewer->getUserEntiy() && $reviewer->getUserEntiy()==$user && !$reviewer->getSigned()){
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
                'No tiene permisos para revisar este documento o ya lo ha firmado previamente'
            );
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
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

        if(!$this->get('app.security')->permissionSeccion('aprobador_gp') && $this->get('app.security')->permissionSeccion('elaborador_gp')){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos suficientes'
            );
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($template->getTmState()->getId()!=6){
            $this->get('session')->getFlashBag()->add(
                'error',
                'La plantilla indicada no se encuentra en vigor para poder realizar una revisión'
            );
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        

        if(!$template->getOpenedBy() || $template->getOpenedBy()!=$user){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No se puede efectuar la operación'
            );
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $actions = $this->getDoctrine()->getRepository(TMActions::class)->findBy(array("id" => array(2,4)));
        $reviewers = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $actions),array("id" => "ASC"));
        $find=0;
        foreach($reviewers as $reviewer){
            if($reviewer->getUserEntiy() && $reviewer->getUserEntiy()==$user && !$reviewer->getSigned()){
                $find=1;
                $wich_workflow=$reviewer;
            }
        }
        
        if($find==0){
            foreach($reviewers as $reviewer){
                if($reviewer->getGroupEntiy() && !$reviewer->getSigned()){
                    $in_group=0;
                    foreach($user->getGroups() as $uniq_group){
                        if($uniq_group->getGroup()==$reviewer->getGroupEntiy()){
                            $in_group=1;
                            $wich_workflow=$reviewer;
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
                'No tiene permisos para revisar este documento o ya lo ha firmado previamente'
            );
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        if($request->get("result") && in_array($request->get("result"), array(13,14,15))){
            $action_review = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => $request->get("result")));
            switch($request->get("result")){
                case 13: $description="La plantilla ha sido revisada. No es necesario realizar ningún cambio, la edición no se actualiza hasta la próxima revisión.";break;
                default: $description="";break;
            }
        }
        else{
            $this->get('session')->getFlashBag()->add(
                'error',
                'Hubo un problema al tramitar la firma de la solicitud de revisión'
            );
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
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
        $signature->setAction($action_review);
        $signature->setUserEntiy($user);
        $signature->setCreated(new \DateTime());
        $signature->setModified(new \DateTime());
        $signature->setSignature($request->get("signature"));
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
        if($request->get("result")!=14){
            foreach($reviewers as $reviewer){
                if($wich_workflow!=$reviewer && !$reviewer->getSigned()){
                    $user_workflow_finish=0;
                }
            }

            if($user_workflow_finish){
                $template->setDateReview(new \DateTime('+3 year'));
                $template->setNeedNewEdition(FALSE);
            }
        }
        else{
            $template->setNeedNewEdition(TRUE);
            $template->setDateReview(NULL);
        }

        if($user_workflow_finish){
            //Vaciamos el control de los que ya han firmado puesto que la solicitud se cierra
            foreach($reviewers as $reviewer){
                $reviewer->setSigned(0);
                $em->persist($reviewer);
            }

            $template->setRequestReview(NULL); 
        }
        
        $em->persist($template);
        $em->flush();

        $this->get('session')->getFlashBag()->add('message', "La revisión se ha realizado con éxito");
        $route = $this->container->get('router')->generate('nononsense_home_homepage');
       

        return $this->redirect($route);
    }
}