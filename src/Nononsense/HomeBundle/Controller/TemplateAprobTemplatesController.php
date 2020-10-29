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
use Nononsense\HomeBundle\Entity\TMTestResults;
use Nononsense\HomeBundle\Entity\TMTestAprob;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class TemplateAprobTemplatesController extends Controller
{
    public function detailAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $user = $this->container->get('security.context')->getToken()->getUser();

        $array_item["template"] = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($array_item["template"]->getTmState()->getId()!=4){
        	$this->get('session')->getFlashBag()->add(
                'error',
                'La plantilla indicada no se encuentra en fase de aprobación'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        $action_test = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 3));
        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 4));
        $aprobs = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));

        $find=0;
        foreach($aprobs as $aprob){
            if($aprob->getUserEntiy() && $aprob->getUserEntiy()==$user  && !$aprob->getSigned()){
                $find=1;
                $array_item["my_id"]=$aprob->getId();
            }
        }
        
        if($find==0){
            foreach($aprobs as $aprob){
                if($aprob->getGroupEntiy() && !$aprob->getSigned()){
                    $in_group=0;
                    foreach($user->getGroups() as $uniq_group){
                        if($uniq_group->getGroup()==$aprob->getGroupEntiy()){
                            $in_group=1;
                            $array_item["my_id"]=$aprob->getId();
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
                'No tiene permisos para aprobar esta plantilla'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        $all_signatures = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template" => $array_item["template"]),array("id" => "ASC"));
        foreach($all_signatures as $key_all => $item_all){
            if($item_all->getAction()->getId()<3){
                $array_item["max_id_no_test"]=$item_all->getId();
            }
        }

        $signatures = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template" => $array_item["template"], "action" => $action_test),array("id" => "ASC"));
        $array_item["tests"] = $this->getDoctrine()->getRepository(TMTests::class)->findBy(array("signature" => $signatures, "test_id" => NULL),array("id" => "DESC"));

        $array_item["tests_results_aprobs"] = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("tmTest" => $array_item["tests"], "action" => $action),array("id" => "ASC"));

        foreach($array_item["tests"] as $key_test => $item_test){
        	$array_item["subtests"][$item_test->getId()] = $this->getDoctrine()->getRepository(TMTests::class)->findBy(array("signature" => $signatures, "test_id" => $item_test->getId()),array("id" => "DESC"));

            $array_item["subtests_results_aprobs"][$item_test->getId()] = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("tmTest" => $array_item["subtests"][$item_test->getId()], "action" => $action),array("id" => "ASC"));
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

        $array_item["aprobs"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $array_item["results"] = $this->getDoctrine()->getRepository(TMTestAprob::class)->findBy(array(),array("id" => "ASC"));


        return $this->render('NononsenseHomeBundle:TemplateManagement:aprob_detail.html.twig',$array_item);
    }


    public function updateAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $user = $this->container->get('security.context')->getToken()->getUser();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($template->getTmState()->getId()!=4){
            $this->get('session')->getFlashBag()->add(
                'error',
                'La plantilla indicada no se encuentra en fase de aprobación'
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

        if($request->get("test")){
        	$test_to_approve = $this->getDoctrine()->getRepository(TMTests::class)->findOneBy(array("id" => $request->get("test")));
        	if(!$test_to_approve || $test_to_approve->getSignature()->getTemplate()!=$template){
        		$this->get('session')->getFlashBag()->add(
	                'error',
	                'No se puede efectuar la operación'
	            );
	            $route=$this->container->get('router')->generate('nononsense_tm_templates');
	            return $this->redirect($route);
        	}
        }
        else{
        	$this->get('session')->getFlashBag()->add(
                'error',
                'No se puede efectuar la operación'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }
        
        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 4));
        
    	$aprobs = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $action),array("id" => "ASC"));
        $find=0;
        foreach($aprobs as $aprob){
            if($aprob->getUserEntiy() && $aprob->getUserEntiy()==$user  && !$aprob->getSigned()){
                $wich_workflow=$aprob;
                $find=1;
            }
        }
        
        if($find==0){
            foreach($aprobs as $aprob){
                if($aprob->getGroupEntiy() && !$aprob->getSigned()){
                    $in_group=0;
                    foreach($user->getGroups() as $uniq_group){
                        if($uniq_group->getGroup()==$aprob->getGroupEntiy()){
                            $in_group=1;
                            $wich_workflow=$aprob;
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
                'No tiene permisos para aprobar este documento'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        $action_test = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 3));
        

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
        $signature->setAction($action);
        $signature->setUserEntiy($user);
        $signature->setCreated(new \DateTime());
        $signature->setModified(new \DateTime());
        $signature->setSignature($request->get("signature"));
        $signature->setVersion($response["version"]["id"]);
        $signature->setTmTest($test_to_approve);
        $signature->setTmWhoAprobFromWorkflow($wich_workflow);
        $signature->setConfiguration($response["version"]["configuration"]["id"]);

        if($request->get("description")){
        	$signature->setDescription($request->get("description"));
        }

        $result = $this->getDoctrine()->getRepository(TMTestAprob::class)->findOneBy(array("id" => $request->get("result")));
        $signature->setTmAprobAction($result);
        $em->persist($signature);

        //Seleccionamos solo los últimas firmas de tipo test de entre todas las firmas de esa plantilla
        $all_signatures = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template" => $template),array("id" => "ASC"));
        $last_signatures_tests=array();
        foreach($all_signatures as $key_all => $item_all){
            if($item_all->getAction()->getId()<3){
                unset($last_signatures_tests);
            }
            else{
                $last_signatures_tests[]=$item_all;
            }
        }

        //Sacamos los últimos tests a través de las firmas que hemos seleccionado
        $last_tests = $this->getDoctrine()->getRepository(TMTests::class)->findBy(array("signature" => $last_signatures_tests),array("id" => "DESC"));
        $user_workflow_finish=1;
        foreach($last_tests as $item_test){
            //Miramos si esos tests tienen alguna aprobación
            $exist_aprob = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template" => $template, "tmTest" => $item_test, "tmWhoAprobFromWorkflow" => $wich_workflow));
            //Miramos si exceptuando la aprobacion que vamos a hacer, queda aún pendiente alguna para este usuario
            if(!$exist_aprob && $item_test!=$test_to_approve){
                $user_workflow_finish=0;
            }
        }
        

        $next_step=0;
        if($user_workflow_finish){
            $wich_workflow->setSigned(TRUE);
            $em->persist($wich_workflow);

	        $template->setOpenedBy(NULL);
    	    $template->setToken(NULL);
    	
	        $next_step=1;

            $aprobs = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $action),array("id" => "ASC"));
	        foreach($aprobs as $aprob){
	            if(!$aprob->getSigned()){
	                $next_step=0;
	            }
	        }

	        if($next_step==1){
                
	        	//Vaciamos el control de los que ya han firmado puesto que la plantilla cambia de estado
	        	foreach($aprobs as $aprob){
	        		$aprob->setSigned(0);
	        		$em->persist($aprob);
	        	}

	        	$next_state=5;
	        	$users_notifications=array();
                $users_elaborations=array();
                $users_tests=array();
	        	$userssignatures = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template" => $template),array("id" => "ASC"));
	        	//Comprobamos los test realizados para saber a que estado debemos pasar la plantilla
	        	foreach($userssignatures as $us){
	        		//Tenemos en cuenta solo las pruebas desde la última firma que no sea de aprobación
	        		if($us->getAction()->getId()!=4){
	        			$next_state=5;
	        			//Metemos aquellos usuarios que sean elaboradores para que estos sean notificados en caso de que la plantilla vuelva hacia atrás
	        			if($us->getAction()->getId()==2){
	        				$users_elaborations[]=$us->getUserEntiy()->getEmail();
	        			}

                        if($us->getAction()->getId()==3){
                            $users_tests[]=$us->getUserEntiy()->getEmail();
                        }
	        		}
	        		else{
	        			//Si hay un error en la prueba la plantilla vuelve hacia atrás
	        			if($us->getTmAprobAction()->getId()>1){
                            if($us->getTmAprobAction()->getId()==2 && $next_state>2){
	        				   $next_state=3;
                            }
                            else{
                                $next_state=2;
                            }
	        			}
	        		}
	        	}

	        	//Si el resultado es que la plantilla pasa a aprobación, se vacía los usuarios a notificar (elaboradores) y metemos los aprobadores
                switch($next_state){
                    case 2: $users_notifications=$users_elaborations;
                        break;
                    case 3: $users_notifications=$users_tests;
                        break;
    	        	case 5:
    	        		$users_notifications=array();
    	        		$action_admin = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 5));
    	        		$admins = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $action_admin));
    	        		foreach($admins as $admin){
    	        			if($admin->getUserEntiy()){
    	        				$users_notifications[]=$admin->getUserEntiy()->getEmail();
    	        			}
    	        			else{
    	        				foreach($aprob->getGroupEntiy()->getUsers() as $user_group){
                                    $users_notifications[]=$user_group->getUser()->getEmail();
                                }
    	        			}
    	        		}
                        break;
	        	}

	            $state = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id"=> $next_state));
	            $template->setTmState($state);

                switch($next_state){
                    case 5:
                        $subject="Plantilla aprobada";
                        $mensaje='La plantilla con ID '.$id.' está pendiente de configuración por su parte. Para poder revisarlo puede acceder a "Gestión de plantillas -> Pdt. configuración", buscar la plantilla correspondiente y pulsar en Configurar';
                        $baseURL=$this->container->get('router')->generate('nononsense_tm_config_detail', array("id" => $id),TRUE);
                        break;
                    case 3:
                        $subject="Test rechazados";
                        $mensaje='La plantilla con ID '.$id.' está pendiente de realizar nuevos tests por su parte. Para poder revisarlos puede acceder a "Gestión de plantillas -> En test", buscar la plantilla correspondiente y pulsar en Testear';
                        $baseURL=$this->container->get('router')->generate('nononsense_tm_test_detail', array("id" => $id),TRUE);
                        break;
                    case 2:
                        $subject="Plantilla rechazada/cancelada";
                        $mensaje='La plantilla con ID '.$id.' está pendiente de revisión por su parte. Para poder revisarlo puede acceder a "Gestión de plantillas -> En elaboración", buscar la plantilla correspondiente y pulsar en Elaborar';
                        $baseURL=$this->container->get('router')->generate('nononsense_tm_elaborate_detail', array("id" => $id),TRUE);
                        break;
                }

	            foreach($users_notifications as $email){
                    $this->get('utilities')->sendNotification($email, $baseURL, "", "", $subject, $mensaje);
                }

                $em->persist($template);
	        }
        }

        $em->flush();

        $this->get('session')->getFlashBag()->add('message', "La aprobación se ha realizado correctamente");
        if($user_workflow_finish){
        	$route = $this->container->get('router')->generate('nononsense_tm_templates');
        }
        else{
        	$route = $this->container->get('router')->generate('nononsense_tm_aprob_detail', array("id" => $template->getId()),TRUE);
        }

        return $this->redirect($route);
    }
}