<?php
namespace Nononsense\HomeBundle\Controller;

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
use Nononsense\HomeBundle\Entity\TMTestResults;
use Nononsense\HomeBundle\Entity\TMTestAprob;
use Nononsense\HomeBundle\Entity\QrsTypes;
use Nononsense\HomeBundle\Entity\TMNestTemplates;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class TemplateConfigTemplatesController extends Controller
{
    public function detailAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $is_valid = $this->get('app.security')->permissionSeccion('admin_gp');
        if(!$is_valid){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos suficientes'
            );
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $array_item["template"] = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($array_item["template"]->getTmState()->getId()!=5 && $array_item["template"]->getTmState()->getId()!=11  && $array_item["template"]->getTmState()->getId()!=6){
            return $this->returnToHomePage("La plantilla indicada no se encuentra en fase de configuración");
        }


        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 5));
        $workflow_admin = $this->getDoctrine()->getRepository(TMWorkflow::class)->findOneBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));

        $find=0;
        if($workflow_admin->getUserEntiy() && $workflow_admin->getUserEntiy()==$user){
            $find=1;
        }
        if($find==0){
            if($workflow_admin->getGroupEntiy()){
                $in_group=0;
                foreach($user->getGroups() as $uniq_group){
                    if($uniq_group->getGroup()==$workflow_admin->getGroupEntiy()){
                        $find=1;
                        break;
                    }
                }
            }
        }

        if(!$find){
            return $this->returnToHomePage("No tiene permisos para configurar esta plantilla");
        }

        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 2));
        $array_item["elab"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 3));
        $array_item["test"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 4));
        $array_item["aprob"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 5));
        $array_item["admin"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));

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

        $array_item["users"] = $this->getDoctrine()->getRepository(Users::class)->findBy(array(),array("name" => "ASC"));
        $array_item["qrs"] = $this->getDoctrine()->getRepository(QrsTypes::class)->findBy(array(),array("name" => "ASC"));


        return $this->render('NononsenseHomeBundle:TemplateManagement:config_detail.html.twig',$array_item);
    }


    public function updateAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $is_valid = $this->get('app.security')->permissionSeccion('admin_gp');
        if(!$is_valid){
            return $this->returnToHomePage('No tiene permisos suficientes');
        }

        $password =  $request->get('password');
        if(!$password || !$this->get('utilities')->checkUser($password)){
            return $this->returnToHomePage("No se pudo firmar el registro, la contraseña es incorrecta");
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($template->getTmState()->getId()!=5 && $template->getTmState()->getId()!=11  && $template->getTmState()->getId()!=6){
            return $this->returnToHomePage('La plantilla indicada no se encuentra en fase de configuración');
        }

        if(!$template->getOpenedBy() || $template->getOpenedBy()!=$user){
            return $this->returnToHomePage('No se puede efectuar la operación');
        }
        
        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id"=>"5"));

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

        $next_state = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id"=>"11"));

        if($template->getTmState()->getId()!=6){
            if($request->get("logbook")){
               $template->setLogbook(1); 
            }
            else{
                $template->setLogbook(0); 
            }

            if($request->get("unique")){
               $template->setUniqid(1); 
            }
            else{
                $template->setUniqid(0); 
            }

            if($request->get("correlative")){
               $template->setCorrelative(1); 
            }
            else{
                $template->setCorrelative(0); 
            }

            if($request->get("not_fillable_it_self")){
               $template->setNotFillableItSelf(1); 
            }
            else{
                $template->setNotFillableItSelf(NULL); 
            }

            if($request->get("minutes_verification")){
               $template->setMinutesVerification($request->get("minutes_verification")); 
            }
            else{
                $template->setMinutesVerification(NULL); 
            }

            if($request->get("qr")){
               $qr = $this->getDoctrine()->getRepository(QrsTypes::class)->findOneBy(array("id" => $request->get("qr")));
               $template->setQRType($qr);
            }

            $description="Esta firma significa la puesta en vigor de la plantilla en la fecha indicada y habiendo sido aprobada, marcando la efectividad de la misma desde dicho momento. Declarando que las actividades asociados a la gestión de la plantilla se han realizado de manera satisfactoria";
          
            if($request->get("public_date") && $request->get("action")=="1"){
                $date_public=\DateTime::createFromFormat('Y-m-d', $request->get("public_date"));
                if(date("Y-m-d")>=$date_public->format("Y-m-d")){
                    $next_state = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id"=>"6"));
                    $template->setDateReview(new \DateTime('+3 year'));
                    
                    if($template->getTemplateId()){
                        $obsolete = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id"=>"7"));
                        $last_edition = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $template->getTemplateId()));
                        $last_edition->setTmState($obsolete);
                        $em->persist($last_edition);

                        /* Cambiamos la versión de las anidaciones para plantillas que no estén en un estado final */
                        $nests = $this->getDoctrine()->getRepository(TMNestTemplates::class)->findBy(array("nestTemplate" => $last_edition));
                        foreach($nests as $nest){
                            // Si la plantilla no está en un estado final
                            if (in_array($nest->getTemplate()->getTmState()->getId(), array(0,1,2,3,4,5,6,9,11))) {
                                $nest->setNestTemplate($template);
                                $em->persist($nest);
                            }
                        }

                        /* Cambiamos las categorías de retención de TODAS las versiones anteriores, aplicandoles las de la nueva versión */
                        while($last_edition){
                            foreach($last_edition->getRetentions() as $old_retention){
                                $last_edition->removeRetention($old_retention);
                            }

                            foreach($template->getRetentions() as $new_retention){
                                $last_edition->addRetention($new_retention);
                            }

                            $em->persist($last_edition);

                            if($last_edition->getTemplateId()){
                                $last_edition = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $last_edition->getTemplateId()));
                            }
                            else{
                                $last_edition=NULL;
                            }
                        }
                    }

                }

                $template->setEffectiveDate($date_public); 
            }
            else{
                $next_state = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id"=>"5"));
                $template->setEffectiveDate(NULL);
                $description="Esta firma significa la cancelación de la puesta en vigor de la plantilla en la fecha indicada pasado de nuevo a estado aprobada";
            }

            $template->setTmState($next_state);
            

            $nests = $this->getDoctrine()->getRepository(TMNestTemplates::class)->findBy(array("template" => $template));
            foreach($nests as $nest){
                $em->remove($nest);
            }
            if($request->get("templates")){
                $number=1;
                foreach($request->get("templates") as $key => $ctemplate){
                    $nest = new TMNestTemplates();
                    $nest->setTemplate($template);
                    $item_ctemplate = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $ctemplate));
                    $nest->setNestTemplate($item_ctemplate);
                    $nest->setNestNumber($number);
                    $em->persist($nest);
                    $number++;
                }
            }
            
        }
        else{
            $description="Esta firma significa la posible edición del dueño o backup del documento con la plantilla ya en vigor";
        }

        $owner = $this->getDoctrine()->getRepository(Users::class)->findOneBy(array("id" => $request->get("owner")));
        $backup = $this->getDoctrine()->getRepository(Users::class)->findOneBy(array("id" => $request->get("backup")));

        $template->setOwner($owner);
        $template->setBackup($backup);

        

        $signature = new TMSignatures();
        $signature->setTemplate($template);
        $signature->setAction($action);
        $signature->setUserEntiy($user);
        $signature->setCreated(new \DateTime());
        $signature->setModified(new \DateTime());
        $signature->setSignature("-");

        $signature->setVersion($response["version"]["id"]);
        $signature->setConfiguration($response["version"]["configuration"]["id"]);
        $signature->setDescription($description);
        $em->persist($signature);


        $em->persist($template);
        $em->flush();

        
        if($next_state->getId()!=6){
            $message = "La configuración de la plantilla se ha realizado correctamente";
        }
        else{
            $message = "La plantilla se ha puesto en vigor";
        }

        return $this->returnToHomePage($message, "message");
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