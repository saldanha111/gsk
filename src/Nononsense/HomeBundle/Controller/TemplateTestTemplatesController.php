<?php
namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasSteps;
use Symfony\Component\Filesystem\Filesystem;
use Nononsense\UtilsBundle\Classes;

use Nononsense\UserBundle\Entity\Users;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\GroupBundle\Entity\GroupsUsers;
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

        if($request->get("token")){
        	$test = $this->getDoctrine()->getRepository(TMTests::class)->findOneBy(array("token" => $request->get("token"), "signature" => NULL, "userEntiy" => $user));
        	if($test){
        		$array_item["sign"]=1;
        	}
        }

        $approval_exists=0;
        $all_signatures = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template" => $array_item["template"]),array("id" => "ASC"));
        foreach($all_signatures as $key_all => $item_all){
            if($item_all->getAction()->getId()==2 || $item_all->getAction()->getId()==4){
                $array_item["max_id_no_test"]=$item_all->getId();
            }
            if($item_all->getAction()->getId()==4){
                $approval_exists=1;
            }
        }
        $action_aprob = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 4));
        $signatures = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
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
        $array_item["results"] = $this->getDoctrine()->getRepository(TMTestResults::class)->findBy(array(),array("id" => "ASC"));


        return $this->render('NononsenseHomeBundle:TemplateManagement:test_detail.html.twig',$array_item);
    }

    public function linkAction(Request $request, int $id)
    {
    	$em = $this->getDoctrine()->getManager();
        $array_item=array();

        $user = $this->container->get('security.context')->getToken()->getUser();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));

        if($request->get("test")){
        	$id_test=$request->get("test");
        }
        else{
        	$id_test=0;
        }

        $baseUrl = $this->getParameter("cm_installation");
	    $baseUrlAux = $this->getParameter("cm_installation_aux");

	    if($request->get("configuration")){
	    	$configuration="&configuration=".$request->get("configuration");
	    }
	    else{
	    	$configuration="";
	    }

        if(!$request->get("pdf")){
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
	    
	        if($request->get("mode")){
	        	$mode=$request->get("mode");
	        }
	        else{
	        	$mode="c";
	        }
        

	        $token_get_data = $this->get('utilities')->generateToken();

	        
	        $callback_url=urlencode($baseUrlAux."mt/test/".$id."/save?token=".$token_get_data."&test=".$id_test);
	        $get_data_url=urlencode($baseUrlAux."mt/test/".$id."/getdata?mode=".$mode."&test=".$id_test);

	        $redirectUrl = urlencode($this->container->get('router')->generate('nononsense_tm_test_detail', array("id" => $template->getId()),TRUE)."?token=".$token_get_data);
	        $scriptUrl = urlencode($baseUrl . "../js/js_oarodoc/activity.js?v=".uniqid());
	        $styleUrl = urlencode($baseUrl . "../css/css_oarodoc/standard.css?v=".uniqid());


	        $base_url=$this->getParameter('api_docoaro')."/documents/".$template->getPlantillaId()."?scriptUrl=".$scriptUrl."&styleUrl=".$styleUrl."&callbackUrl=".$callback_url."&redirectUrl=".$redirectUrl."&getDataUrl=".$get_data_url.$configuration;

        }
        else{
	        $get_data_url=urlencode($baseUrlAux."mt/test/".$id."/getdata?mode=pdf&test=".$id_test);
	        $scriptUrl = urlencode($baseUrl . "../js/js_oarodoc/show.js?v=".uniqid());

	        $base_url=$this->getParameter('api_docoaro')."/documents/".$template->getPlantillaId()."?getDataUrl=".$get_data_url."&scriptUrl=".$scriptUrl.$configuration;
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

    public function updateAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $user = $this->container->get('security.context')->getToken()->getUser();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($template->getTmState()->getId()!=3){
            $this->get('session')->getFlashBag()->add(
                'error',
                'La plantilla indicada no se encuentra en fase Test'
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

        if($request->get("token")){
        	$last_test = $this->getDoctrine()->getRepository(TMTests::class)->findOneBy(array("token" => $request->get("token")));
        	if(!$last_test){
        		$this->get('session')->getFlashBag()->add(
	                'error',
	                'No se puedo efectuar la operación'
	            );
	            $route=$this->container->get('router')->generate('nononsense_tm_templates');
	            return $this->redirect($route);
        	}
        }
        else{
        	$this->get('session')->getFlashBag()->add(
                'error',
                'No se puedo efectuar la operación'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 3));
        
        if($request->get("finish_tests")){
        	$testers = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $action),array("id" => "ASC"));
	        $find=0;
	        foreach($testers as $tester){
	            if($tester->getUserEntiy() && $tester->getUserEntiy()==$user){
	                $tester->setSigned(TRUE);
	                $em->persist($tester);
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
	                        $tester->setSigned(TRUE);
	                        $em->persist($tester);
	                        break;
	                    }
	                }
	            }
	        }
	    }
	    else{
	    	$testers = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $action),array("id" => "DESC"));
	        $find=0;

        	foreach($testers as $tester){
                if($tester->getGroupEntiy()){
                    $in_group=0;
                    foreach($user->getGroups() as $uniq_group){
                        if($uniq_group->getGroup()==$tester->getGroupEntiy()){
                            $in_group=1;
                            break;
                        }
                    }
                    if($in_group==1){
                        $find=1;
                        $tester->setSigned(FALSE);
                        $em->persist($tester);
                        break;
                    }
                }
            }
	        
	        if($find==0){
	        	foreach($testers as $tester){
		            if($tester->getUserEntiy() && $tester->getUserEntiy()==$user){
		                $tester->setSigned(FALSE);
		                $em->persist($tester);
		                $find=1;
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
        $signature->setConfiguration($response["version"]["configuration"]["id"]);
        if($request->get("finish_tests")){
        	if($request->get("description")){
        		$signature->setDescription($request->get("description")." - Finalizo tests");
        	}
        	else{
        		$signature->setDescription("Finalizo tests");
        	}
        	
        }
        else{
        	if($request->get("description")){
        		$signature->setDescription($request->get("description"));
        	}
        }
        $em->persist($signature);

        
        $result = $this->getDoctrine()->getRepository(TMTestResults::class)->findOneBy(array("id" => $request->get("result")));
        $last_test->setSignature($signature);
        $last_test->setResult($result);
        $em->persist($last_test);
        

        if($request->get("finish_tests")){
	        $template->setOpenedBy(NULL);
    	    $template->setToken(NULL);
    	
	        $next_step=1;

	        foreach($testers as $tester){
	            if(!$tester->getSigned()){
	                $next_step=0;
	            }
	        }

	        if($next_step==1){
	        	//Vaciamos el control de los que ya han firmado puesto que la plantilla cambia de estado
	        	foreach($testers as $tester){
	        		$tester->setSigned(0);
	        		$em->persist($tester);
	        	}

	        	$next_state=4;
	        	$users_notifications=array();
	        	$userssignatures = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template" => $template),array("id" => "ASC"));
	        	//Comprobamos los test realizados para saber a que estado debemos pasar la plantilla
	        	foreach($userssignatures as $us){
	        		//Tenemos en cuenta solo las pruebas desde la última firma que no sea de test
	        		if($us->getAction()->getId()!=3){
	        			$next_state=4;
	        			//Metemos aquellos usuarios que sean elaboradores para que estos sean notificados en caso de que la plantilla vuelva hacia atrás
	        			if($us->getAction()->getId()==2){
	        				$users_notifications[]=$us->getUserEntiy()->getEmail();
	        			}
	        		}
	        		else{
	        			//Si hay un error en la prueba la plantilla vuelve hacia atrás
	        			if($us->getTmTests()[0]->getResult()->getId()>1){
	        				$next_state=2;
	        			}
	        		}
	        	}

	        	//Si el resultado es que la plantilla pasa a aprobación, se vacía los usuarios a notificar (elaboradores) y metemos los aprobadores
	        	if($next_state==4){
	        		$users_notifications=array();
	        		$action_aprob = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 4));
	        		$aprobs = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $action_aprob));
	        		foreach($aprobs as $aprob){
	        			if($aprob->getUserEntiy()){
	        				$users_notifications[]=$aprob->getUserEntiy()->getEmail();
	        			}
	        			else{
	        				foreach($aprob->getGroupEntiy()->getUsers() as $user_group){
								$users_notifications[]=$user_group->getUser()->getEmail();
	        				}
	        			}
	        		}
	        	}

	            $state = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id"=> $next_state));
	            $template->setTmState($state);

                if($next_state==4){
                    $subject="Plantilla a aprobar";
                    $mensaje='La plantilla con ID '.$id.' está pendiente de aprobación por su parte. Para poder revisarlo puede acceder a "Gestión de plantillas -> En aprobación", buscar la plantilla correspondiente y pulsar en Aprobar';
                    $baseURL=$this->container->get('router')->generate('nononsense_tm_aprob_detail', array("id" => $id),TRUE);
                }
                else{
                    $subject="La plantilla no ha pasado los tests";
                    $mensaje='La plantilla con ID '.$id.' no ha pasado los tests realizados y require de nuevo de su elaboración. Para poder realizar las correcciones pertinentes, puede acceder a "Gestión de plantillas -> En elaboración", buscar la plantilla correspondiente y pulsar en Elaborar. Podrá ver los tests y comentarios de cada uno de llos pulsando en el Audit Trail';
                    $baseURL=$this->container->get('router')->generate('nononsense_tm_elaborate_detail', array("id" => $id),TRUE);
                }

                foreach($users_notifications as $email){
                    $this->get('utilities')->sendNotification($email, $baseURL, "", "", $subject, $mensaje);
                }
	        }

        }

        $em->persist($template);
        $em->flush();

       
        if($request->get("finish_tests")){
        	$this->get('session')->getFlashBag()->add('message', "La operación se ha ejecutado con éxito");
        	$route = $this->container->get('router')->generate('nononsense_tm_templates');
        }
        else{
        	 $this->get('session')->getFlashBag()->add('message', "El test se ha añadido correctamente");
        	$route = $this->container->get('router')->generate('nononsense_tm_test_detail', array("id" => $template->getId()),TRUE);
        }
        return $this->redirect($route);
    }
}