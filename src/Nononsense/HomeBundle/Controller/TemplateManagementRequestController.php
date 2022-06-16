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


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class TemplateManagementRequestController extends Controller
{
    public function createAction(Request $request)
    {
    	$serializer = $this->get('serializer');
        $array_item=array();

        $array_item["areas"] = $this->getDoctrine()->getRepository(Areas::class)->findBy(array(),array("name" => "ASC"));
        $array_item["groups"] = $this->getDoctrine()->getRepository(Groups::class)->findBy(array(),array("name" => "ASC"));
        $array_item["users"] = $this->getDoctrine()->getRepository(Users::class)->findBy(array(),array("name" => "ASC"));
        $array_item["retention_categories"] = $this->getDoctrine()->getRepository(RetentionCategories::class)->findBy(array(),array("name" => "ASC"));

        if($request->get("id")){
           $item=$this->getDoctrine()->getRepository('NononsenseHomeBundle:TMTemplates')->listActiveForRequest(array("id"=>$request->get("id"),"no_request_in_proccess" => 1));
           if(count($item)>0){
                $array_item["item"]=$item[0];
           }
           else{
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'La plantilla indicada ya tiene una nueva edición en proceso'
                );
                $route = $this->container->get('router')->generate('nononsense_home_homepage');
                return $this->redirect($route);
           }
        }
        
        return $this->render('NononsenseHomeBundle:TemplateManagement:request.html.twig',$array_item);
    }

    public function detailAction(Request $request)
    {
        $serializer = $this->get('serializer');
        $array_item=array("count" => 0);
        
        return $this->render('NononsenseHomeBundle:TemplateManagement:request_detail.html.twig',$array_item);
    }

    public function listUsersAndGroupsJsonAction(Request $request, int $area)
    {
        $em = $this->getDoctrine()->getManager();
        $array=array();

        $array["users"]["admin"]=$em->getRepository('NononsenseUserBundle:Users')->listUsersByAreaAndPermission($area,"admin_gp");
        $array["users"]["elab"]=$em->getRepository('NononsenseUserBundle:Users')->listUsersByAreaAndPermission($area,"elaborador_gp");
        $array["users"]["test"]=$em->getRepository('NononsenseUserBundle:Users')->listUsersByAreaAndPermission($area,"tester_gp");
        $array["users"]["aprob"]=$em->getRepository('NononsenseUserBundle:Users')->listUsersByAreaAndPermission($area,"aprobador_gp");
        $array["users"]["dueno"]=$em->getRepository('NononsenseUserBundle:Users')->listUsersByAreaAndPermission($area,"dueno_gp");

        $array["groups"]["admin"]=$em->getRepository('NononsenseGroupBundle:Groups')->listGroupsByAreaAndPermission($area,"admin_gp");
        $array["groups"]["elab"]=$em->getRepository('NononsenseGroupBundle:Groups')->listGroupsByAreaAndPermission($area,"elaborador_gp");
        $array["groups"]["test"]=$em->getRepository('NononsenseGroupBundle:Groups')->listGroupsByAreaAndPermission($area,"tester_gp");
        $array["groups"]["aprob"]=$em->getRepository('NononsenseGroupBundle:Groups')->listGroupsByAreaAndPermission($area,"aprobador_gp");
        $array["groups"]["dueno"]=$em->getRepository('NononsenseGroupBundle:Groups')->listGroupsByAreaAndPermission($area,"dueno_gp");

        $response = new Response(json_encode($array), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function saveCreateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $array=array();

        $array_error=array();

        $password =  $request->get('password');
        if(!$password || !$this->get('utilities')->checkUser($password)){
            return $this->returnToTmRequest("No se pudo firmar el registro, la contraseña es incorrecta");
        }

        if(!$request->get("request_type")){
            return $this->returnToTmRequest("El campo 'Tipo de solicitud' es obligatorio");
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        switch($request->get("request_type")){
            case 1:
                if(!$request->get("area")){
                    $array_error[]="Área";
                }
                if(!$request->get("name")){
                    $array_error[]="Nombre";
                }
                if(!$request->get("prefix")){
                    $array_error[]="Numeración";
                }
                if(!$request->get("retention")){
                    $array_error[]="Categorías de retención";
                }
                if(!$request->get("password")){
                    $array_error[]="Firma";
                }
                if(($request->get("area")==10 || $request->get("area")==11)){
                    if(!$request->get("num_project")){
                        $array_error[]="Num.Proyecto";
                    }
                }
                else{
                    if(!$request->get("relationals_test")){
                        $array_error[]="Testers";
                    }
                }
                break;
            case 2:
                if(!$request->get("template")){
                    $array_error[]="Plantilla";
                }
                break;
        }

        if(!$request->get("description")){
            $array_error[]="Descripción";
        }

        if(!$request->get("history_change")){
            $array_error[]="Historial de cambios";
        }

        if(!$request->get("reference")){
            $array_error[]="Referencia";
        }

        if(!$request->get("owner")){
            $array_error[]="Dueño";
        }

        if(!$request->get("backup")){
            $array_error[]="Backup";
        }

        if(!$request->get("public_date")){
            $array_error[]="fecha de publicación";
        }

        if(!$request->get("relationals_elab")){
            $array_error[]="Elaboradores";
        }

        if(!$request->get("relationals_aprob")){
            $array_error[]="Aprobadores";
        }

        if(!$request->get("relationals_admin")){
            $array_error[]="Administrador";
        }

        if(!$request->get("password")){
            $array_error[]="Firma";
        }

        if(!empty($array_error)) {
            return $this->returnToTmRequest("Los siguientes campos son obligatorios: ".implode(",", $array_error));
        }

        $array_workflow=array(2=>"relationals_elab",3=>"relationals_test",4=>"relationals_aprob",5=>"relationals_admin");
        $array_type_users=array();
        foreach($array_workflow as $itemw){
            unset($array_users);
            $array_users=array();
            foreach($request->get($itemw) as $key => $wid){
                if($request->get("type_".$itemw)[$key]==2){
                    if (in_array($wid, $array_users)) {
                        return $this->returnToTmRequest("No puede añadir el mismo usuario dentro del mismo paso en el workflow");
                    }

                    switch($itemw){
                        case "relationals_aprob":
                            if (in_array($wid, $array_type_users["relationals_elab"])) {
                                return $this->returnToTmRequest("Un mismo usuario no puede estar como aprobador si ya está como  elaborador");
                            }

                            if (in_array($wid, $array_type_users["relationals_test"])) {
                                return $this->returnToTmRequest("Un mismo usuario no puede estar como aprobador si ya está como tester");
                            }

                            if($user->getId()==$wid && $request->get("area")!=10 && $request->get("area")!=11){
                                return $this->returnToTmRequest("No puede añadirse como aprobador en su propia solicitud");
                            }
                            break;
                        case "relationals_admin":
                            if($user->getId()==$wid && $request->get("area")!=10 && $request->get("area")!=11){
                                return $this->returnToTmRequest("No puede añadirse como administrador en su propia solicitud");
                            }
                            break;
                    }

                    $array_users[]=$wid;
                    $array_type_users[$itemw][]=$wid;
                }
            }

            if(empty($request->get($itemw)) && ($itemw!="relationals_test" || ($request->get("area")!=10 && $request->get("area")!=11))){
                return $this->returnToTmRequest("No puede haber un workflow vacío");
            }
        }

        if (!in_array($request->get("owner"),$request->get("relationals_elab")) && !in_array($request->get("owner"),$request->get("relationals_aprob")) && !in_array($request->get("backup"),$request->get("relationals_elab")) && !in_array($request->get("backup"),$request->get("relationals_aprob"))) {
            return $this->returnToTmRequest("El dueño o backup debe estar dentro del workflow de elaboración o aprobación");
        }

        /** @var TMActions $action */
        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id"=>"6"));

        switch($request->get("request_type")){
            case 1:
                $area = $this->getDoctrine()->getRepository(Areas::class)->findOneById($request->get("area"));

                if(!$area){
                    return $this->returnToTmRequest("El área indicado no existe");
                }

                $prefix = $this->getDoctrine()->getRepository(AreaPrefixes::class)->findOneBy(array("id"=> $request->get("prefix"),"area" => $area));
                if(!$prefix){
                    return $this->returnToTmRequest("La numeración indicada no existe");
                }
                $desc_prefix=$prefix->getName();
                $retentions = $this->getDoctrine()->getRepository(RetentionCategories::class)->findBy(array("id"=> $request->get("retention")));

                $name=$request->get("name");
                if($area->getTemplate()){
                    $plantilla=$area->getTemplate()->getPlantillaId();
                }
                else{
                    $plantilla=NULL;
                }

                $itemplate=NULL;
                $num_edition=1;
                $first_edition=NULL;
                $num_days=NULL;

                $last_record = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("prefix" => $desc_prefix,"firstEdition" => NULL),array('id' => 'DESC'));
                if(!$last_record){
                    $number_id=1;
                }
                else{
                    $number_id=$last_record->getNumberId()+1;
                }

                break;
            case 2:
                $templates=$em->getRepository('NononsenseHomeBundle:TMTemplates')->listActiveForRequest(array("id"=>$request->get("template"),"no_request_in_proccess" => 1));
                if(!$templates){
                    return $this->returnToTmRequest("El template indicado no está disponible");
                }
                $retentions = $templates[0]->getRetentions();
                $desc_prefix=$templates[0]->getPrefix();
                $area=$templates[0]->getArea();
                $retentions=$templates[0]->getRetentions();
                $name=$templates[0]->getName();
                $plantilla=$templates[0]->getPlantillaId();
                $itemplate=$templates[0]->getId();
                $num_edition=$templates[0]->getNumEdition()+1;
                $version=$templates[0]->getNumEdition();
                $configuracion="1.1";
                if(!$templates[0]->getFirstEdition()){
                    $first_edition=$templates[0]->getId();
                }
                else{
                    $first_edition=$templates[0]->getFirstEdition();
                }

                $last_date = $templates[0]->getCreated();
                $now_date = new \DateTime();
                $num_days = $last_date->diff($now_date)->format("%a");

                $number_id=$templates[0]->getNumberId();
                
                break;
        }



        $number=$desc_prefix;
        $number= str_replace("#X#", str_pad($number_id, 6, "0", STR_PAD_LEFT), $number);
        $number= str_replace("#E#", str_pad($num_edition, 2, "0", STR_PAD_LEFT), $number);
        $number= str_replace("#YY#", date("YY"), $number);
        $number= str_replace("#YYYY#", date("YYYY"), $number);
        if(($request->get("area")==10 || $request->get("area")==11)){
            $number= str_replace("#NUMPROY#", $request->get("num_project"), $number);
        }

        /** @var TMStates $state */
        $state = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id"=> 1));

        $base_url=$this->getParameter('api_docoaro')."/documents/".$plantilla."/clone";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Api-Key: ".$this->getParameter('api_key_docoaro')));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array("name" => $number.": ".$name)); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $raw_response = curl_exec($ch);
        $response = json_decode($raw_response, true);

        if(!array_key_exists('version', $response)){
            $response["id"]=NULL;
            $response["version"]["id"]=NULL;
            $response["version"]["configuration"]["id"]=NULL;
        }
        
        $template = new TMTemplates();
        $template->setTmState($state);
        $template->setName($name);
        $template->setArea($area);
        $template->setPrefix($desc_prefix);
        $template->setNumber($number);
        $template->setNumberId($number_id);
        $template->setPlantillaId($response["id"]);
        $template->setReference($request->get("reference"));
        $template->setDescription($request->get("description"));
        $template->setHistoryChange($request->get("history_change"));
        if($request->get("is_simple")){
           $template->setIsSimple(1); 
        }
        else{
            $template->setIsSimple(0); 
        }

        $date_public=\DateTime::createFromFormat('Y-m-d', $request->get("public_date"));

        if($request->get("public_date")){
           $template->setEstimatedEffectiveDate($date_public); 
        }

        $template->setLogbook(0);
        $template->setUniqid(0);
        $template->setCorrelative(0);
        $template->setCreated(new \DateTime());
        $template->setModified(new \DateTime());
        $template->setTemplateId($itemplate);
        $template->setNumEdition($num_edition);
        $template->setFirstEdition($first_edition);
        $template->setDiffEditionDays($num_days);

        /** @var Users $owner */
        $owner = $this->getDoctrine()->getRepository(Users::class)->findOneBy(array("id" => $request->get("owner")));
        /** @var Users $backup */
        $backup = $this->getDoctrine()->getRepository(Users::class)->findOneBy(array("id" => $request->get("backup")));

        $template->setOwner($owner);
        $template->setBackup($backup);
        $template->setApplicant($user);

        foreach($retentions as $retention){
            $template->addRetention($retention);   
        }

        $em->persist($template);

        
        $signature = new TMSignatures();
        $signature->setTemplate($template);
        $signature->setAction($action);
        $signature->setUserEntiy($user);
        $signature->setCreated(new \DateTime());
        $signature->setModified(new \DateTime());
        $signature->setSignature("-");
        $signature->setVersion($response["version"]["id"]);
        $signature->setConfiguration($response["version"]["configuration"]["id"]);
        $em->persist($signature);



        $array_workflow=array(2=>"relationals_elab",3=>"relationals_test",4=>"relationals_aprob",5=>"relationals_admin");
        $array_type_users=array();
        $num=1;
        foreach($array_workflow as $key_action => $itemw){
            foreach($request->get($itemw) as $key => $wid){
                $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id"=>$key_action));
                $workflow = new TMWorkflow();

                $relational=$request->get("type_".$itemw)[$key];
                if($relational==1){
                    $group = $this->getDoctrine()->getRepository(Groups::class)->find($wid);
                    $workflow->setGroupEntiy($group);
                }
                else{
                    $user = $this->getDoctrine()->getRepository(Users::class)->find($wid);
                    $workflow->setUserEntiy($user);
                }
                $workflow->setTemplate($template);
                $workflow->setAction($action);
                $workflow->setNumber($num);
                $workflow->setSigned(0);
                $em->persist($workflow);
            }
            $num++;
        }

        $subject="Solicitud de nueva plantilla";
        $mensaje='Se ha iniciado una solicitud de una nueva plantilla con Código '.$template->getNumber().' - Título: '.$template->getName().' - Edición: '.$template->getNumEdition().'. Para poder aceptarla o canclearla puede acceder a "Solicitudes pendientes"';
        $baseURL=$this->container->get('router')->generate('nononsense_tm_template_detail', array("id" => $template->getId()),TRUE);
        $this->get('utilities')->sendNotification($owner->getEmail(), $baseURL, "", "", $subject, $mensaje);
        $this->get('utilities')->sendNotification($backup->getEmail(), $baseURL, "", "", $subject, $mensaje);

        
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('message', "Solicitud creada correctamente");
        $route = $this->container->get('router')->generate('nononsense_home_homepage');
        return $this->redirect($route);
    }

    public function retrieveRetentionsByTemplateIdAction(Request $request, int $templateId): Response
    {
        $em = $this->getDoctrine()->getManager();

        /** @var TMTemplates $template */
        $template=$em->getRepository('NononsenseHomeBundle:TMTemplates')->listRetentionsByTemplateId($templateId);
        $template["selected"] =$template[0]["retentions"];
        $template["allCategories"]=$em->getRepository('NononsenseHomeBundle:RetentionCategories')->listToArray();

        $response = new Response(json_encode($template), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    private function returnToTmRequest(string $msgError,string $type = "error"): RedirectResponse
    {
        $this->get('session')->getFlashBag()->add(
            $type,
            $msgError
        );
        $route=$this->container->get('router')->generate('nononsense_tm_request');
        return $this->redirect($route);
    }
}