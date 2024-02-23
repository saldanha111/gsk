<?php
namespace Nononsense\HomeBundle\Controller;

use Exception;
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

class TemplateManagementTemplatesController extends Controller
{
    /* Listado de plantillas que están disponibles para crear una nueva edición */
    public function listActiveJsonAction(Request $request)
    {


        $em = $this->getDoctrine()->getManager();
        $array = array();

        $filters["limit_from"] = 0;
        $filters["limit_many"] = 10;

        if ($request->get("no_request_in_proccess")) {
            $filters["no_request_in_proccess"] = 1;
        }

        if ($request->get("nest")) {
            $filters["nest"] = 1;
            if ($request->get("parent")) {
                $filters["parent"] = $request->get("parent");
            }
        }

        if ($request->get("name")) {
            $filters["name"] = $request->get("name");
        }

        if ($request->get("operative")) {
            $filters["operative"] = $request->get("operative");
        }

        try {

            $items = $em->getRepository('NononsenseHomeBundle:TMTemplates')->listActiveForRequest($filters);

        } catch(Exception $exception)
        {
            $response = new Response(json_encode($exception->getMessage()), 500);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $serializer = $this->get('serializer');
        $array_items = json_decode($serializer->serialize($items,'json',array('groups' => array('json'))),true);
        foreach($array_items as $key => $item){
            $array["items"][$key]["id"]=$item["id"];
            if(!$request->get("cv")){
                $array["items"][$key]["text"]=$item["name"]." - ".$item["prefix"];
            }
            else{
                $array["items"][$key]["text"]=$item["id"]." - ".$item["name"];
            }
            $array["items"][$key]["area"]=$item["area"]["id"];
        }

        $response = new Response(json_encode($array), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /* Listado de plantillas o de solicitudes de baja */
    public function listAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $filters=Array("user" => $user->getId());
        $types=array();

        $array_item["areas"] = $this->getDoctrine()->getRepository(Areas::class)->findBy(array(),array("name" => "ASC"));
        $array_item["groups"] = $this->getDoctrine()->getRepository(Groups::class)->findBy(array("isActive" => TRUE),array("name" => "ASC"));
        $array_item["users"] = $this->getDoctrine()->getRepository(Users::class)->findBy(array(),array("name" => "ASC"));
        $array_item["states"] = $this->getDoctrine()->getRepository(TMStates::class)->findBy(array(),array("number" => "ASC"));
        
        if(!$this->get('app.security')->permissionSeccion('dueno_gp') && !$this->get('app.security')->permissionSeccion('elaborador_gp') && !$this->get('app.security')->permissionSeccion('tester_gp') && !$this->get('app.security')->permissionSeccion('aprobador_gp') && !$this->get('app.security')->permissionSeccion('admin_gp')){
            $array_item["extend"]=FALSE;
        }   
        else{
            if($request->get("cumpl") && $request->get("cumpl")!=""){
                $filters["cumpl"]=$request->get("cumpl");
                $array_item["extend"]=FALSE;
            }
            else{
                $array_item["extend"]=TRUE;
            }
            
        }

        if(!$request->get("export_excel")){
            if($request->get("page")){
                $filters["limit_from"]=$request->get("page")-1;
            }
            else{
                $filters["limit_from"]=0;
            }
            $filters["limit_many"]=15;
        }
        else{
            $filters["limit_from"]=0;
            $filters["limit_many"]=99999999999;
        }


        if($request->get("name")){
            $filters["name"]=$request->get("name");
        }

        if($request->get("number")){
            $filters["number"]=$request->get("number");
        }

        if($request->get("area")){
            $filters["area"]=$request->get("area");
        }

        if($request->get("state")){
            $filters["state"]=$request->get("state");

            $perm_state = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id" => $request->get("state")));

            if($perm_state){
                if($perm_state->getPem()){
                    $pems = explode(";", $perm_state->getPem());
                    $find=0;
                    foreach($pems as $pem){
                        if($this->get('app.security')->permissionSeccion($pem)){
                            $find=1;
                        }
                    }

                    if(!$find){
                        return $this->returnToHomePage("No tiene permisos suficientes");
                    }
                }
            }
        }
        else{
            if(!$request->get("request_drop") && !$request->get("request_review")){
                return $this->returnToHomePage("Tiene que filtrar por al menos un estado");
            }
        }

        if($request->get("applicant")){
            $filters["applicant"]=$request->get("applicant");
        }

        if($request->get("owner")){
            $filters["owner"]=$request->get("owner");
        }

        if($request->get("backup")){
            $filters["backup"]=$request->get("backup");
        }

        if($request->get("draft")){
            $filters["draft"]=$request->get("draft");
        }

        if($request->get("request_drop")){
            $filters["request_drop"]=$request->get("request_drop");
        }

        if($request->get("applicant_drop")){
            $filters["applicant_drop"]=$request->get("applicant_drop");
        }

        if($request->get("request_review")){
            $filters["request_review"]=$request->get("request_review");
        }

        if($request->get("pending_for_me")){
            $filters["pending_for_me"]=$request->get("pending_for_me");
        }

        if($request->get("order")){
            $filters["order"]=$request->get("order");
        }


        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(TMTemplates::class)->list("list",$filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(TMTemplates::class)->list("count",$filters);

        $url=$this->container->get('router')->generate('nononsense_tm_templates');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        if(!$request->get("request_drop")){
            if(!$request->get("request_review")){
                return $this->render('NononsenseHomeBundle:TemplateManagement:templates.html.twig',$array_item);  
            }
            else{
                if(!$this->get('app.security')->permissionSeccion('elaborador_gp') && !$this->get('app.security')->permissionSeccion('aprobador_gp')){
                    return $this->returnToHomePage("No tiene permisos suficientes");
                }
                return $this->render('NononsenseHomeBundle:TemplateManagement:requests_review_templates.html.twig',$array_item);
            }
        }
        else{
            $is_valid = $this->get('app.security')->permissionSeccion('admin_gp');
            if(!$is_valid){
                return $this->returnToHomePage("No tiene permisos suficientes");
            }
            return $this->render('NononsenseHomeBundle:TemplateManagement:request_drop_templates.html.twig',$array_item);            
        }
        
        
    }

    /* Detalle de una solicitud o una plantilla */
    public function detailAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $array_item["template"] = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));

        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 2));
        $array_item["elab"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 3));
        $array_item["test"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 4));
        $array_item["aprob"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 5));
        $array_item["admin"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));


        if($request->get("request_review") || $request->get("request_drop") || $request->get("request_drop_action")){
            if($array_item["template"]->getTmState()->getId()!=6){
                return $this->returnToHomePage("La plantilla indicada no se encuentra en vigor y por tanto no se puede realizar ninguna acción de solicitud sobre ella");
            }
        }

        /* Popup de solicitar baja de plantilla */
        if($request->get("request_drop")){
            $is_valid = $this->get('app.security')->permissionSeccion('dueno_gp');
            if(!$is_valid){
                return $this->returnToHomePage("No tiene permisos suficientes");
            }

            if($user!=$array_item["template"]->getOwner() && $user!=$array_item["template"]->getBackup()){
                return $this->returnToHomePage("Solo el dueño o backup de esta plantilla puede crear una solicitud de baja");
            }

            $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 8));
            $drop_request = $this->getDoctrine()->getRepository(TMSignatures::class)->findOneBy(array("template" => $array_item["template"], "action" => $action, "tmDropAction" => NULL));

            if($drop_request){
                return $this->returnToHomePage("Ya existe una solicitud de baja para esta plantilla");
            }
        }

        /* Popup de firma para aceptar o rechazar la baja de plantilla */
        if($request->get("request_drop_action")){
            $is_valid = $this->get('app.security')->permissionSeccion('admin_gp');
            if(!$is_valid){
                return $this->returnToHomePage("No tiene permisos suficientes");
            }

            $action_admin = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 5));
            $workflow_admin = $this->getDoctrine()->getRepository(TMWorkflow::class)->findOneBy(array("template" => $array_item["template"], "action" => $action_admin),array("id" => "ASC"));

            if(!$workflow_admin || $workflow_admin->getUserEntiy()!=$user){
                return $this->returnToHomePage("No esta denominado como administrador de esta plantilla y por tanto no puede tramitar aprobar o rechazar la solicitud de baja");
            }

            $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 8));
            $array_item["drop_request"] = $this->getDoctrine()->getRepository(TMSignatures::class)->findOneBy(array("template" => $array_item["template"], "action" => $action, "tmDropAction" => NULL));
            if(!$array_item["drop_request"]){
                return $this->returnToHomePage("No hay una solicitud de baja para esta plantilla");
            }
        }

        /* Popup de solicitar revisión de plantilla */
        if($request->get("request_review")){
            if(!$this->get('app.security')->permissionSeccion('dueno_gp') && !$this->get('app.security')->permissionSeccion('elaborador_gp')){
                return $this->returnToHomePage("No tiene permisos suficientes");
            }

            $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 2));
            $elaborators = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
            $find=0;
            foreach($elaborators as $elaborator){
                if($elaborator->getUserEntiy() && $elaborator->getUserEntiy()==$user){
                    $find=1;
                }
            }

            if($user!=$array_item["template"]->getOwner() && $find==0){
                return $this->returnToHomePage("Solo el dueño o elaborador de esta plantilla puede crear una solicitud de revisión");
            }

            if($array_item["template"]->getRequestReview()){
                return $this->returnToHomePage("Ya existe una solicitud de revisión abierta para esta plantilla");
            }

            if(!empty($array_item["template"]->getDateReview()) && $array_item["template"]->getDateReview()<=date("Y-m-d")){
                return $this->returnToHomePage("No se puede realizar una solicitud de esta plantilla puesto que aún ha llegado la fecha de su revisión periódica");
            }

            if($array_item["template"]->getNeedNewEdition()){
                return $this->returnToHomePage("Ya se ha realizado una revisión para esta plantilla previamente y se ha determinado que es necesario crear una edición de esta plantilla");
            }

            $array_item["can_review"]=1;
        }


        return $this->render('NononsenseHomeBundle:TemplateManagement:template_detail.html.twig',$array_item);
    }

    /* Se acepta o se rechaza la solicitud de creación de una plantilla */
    public function actionRequestAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array=array();

        $is_valid = $this->get('app.security')->permissionSeccion('dueno_gp');
        if(!$is_valid){
            return $this->returnToHomePage("No tiene permisos suficientes");
        }

        $user = $this->container->get('security.context')->getToken()->getUser();
        if($request->get("action") && $request->get("password")){
            $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
            if($template){
                $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => $request->get("action")));
                if($action){
                    if($user!=$template->getOwner() && $user!=$template->getBackup()){
                        return $this->returnToHomePage("Solo el dueño o backup puede aceptar o rechazar la solicitud");
                    }

                    switch($request->get("action")){
                        case 1: 
                            $state = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id" => 2));
                            $this->get('session')->getFlashBag()->add('message','La solicitud ha sido aceptada y ha pasado a elaboración');
                        break;
                        case 7: 
                            $state = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id" => 9));
                            $this->get('session')->getFlashBag()->add('message','La solicitud ha sido cancelada');
                        break;
                    }
                    if($state){
                        $previous_signature = $this->getDoctrine()->getRepository(TMSignatures::class)->findOneBy(array("template"=>$template),array("id" => "ASC"));

                        if($template->getTmState()->getId()==1){

                            $template->setTmState($state);
                            $em->persist($template);

                            $signature = new TMSignatures();
                            $signature->setTemplate($template);
                            $signature->setAction($action);
                            $signature->setUserEntiy($user);
                            $signature->setCreated(new \DateTime());
                            $signature->setModified(new \DateTime());
                            $signature->setSignature("-");
                            $signature->setVersion($previous_signature->getVersion());
                            $signature->setConfiguration($previous_signature->getConfiguration());
                            $em->persist($signature);

                            $em->flush();

                            $route=$this->container->get('router')->generate('nononsense_home_homepage');
                            return $this->redirect($route);
                        }
                    }
                }
            }
        }

        return $this->returnToHomePage("No se ha podido efectuar la operación sobre la plantilla especifiada. Es posible que ya se haya realizado una acción sobre ella o que la plantilla ya no exista");
    }

    /* Se crea una solicitud de baja de una plantilla */
    public function requestDropAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array=array();

        $is_valid = $this->get('app.security')->permissionSeccion('dueno_gp');
        if(!$is_valid){
            return $this->returnToHomePage("No tiene permisos suficientes");
        }

        $password =  $request->get('password');
        if(!$password || !$this->get('utilities')->checkUser($password)){
            return $this->returnToHomePage("No se pudo firmar el registro, la contraseña es incorrecta");
        }

        $user = $this->container->get('security.context')->getToken()->getUser();
        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        if($template){
            $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 8));
            if($action){
                $drop_request = $this->getDoctrine()->getRepository(TMSignatures::class)->findOneBy(array("template" => $template, "action" => $action, "tmDropAction" => NULL));
                if($drop_request){
                    return $this->returnToHomePage("Ya hay una solicitud de baja para esta plantilla");
                }

                if($user!=$template->getOwner() && $user!=$template->getBackup()){
                    return $this->returnToHomePage("Solo el dueño o backup puede crear una solicitud de baja");
                }


                $this->get('session')->getFlashBag()->add('message','La solicitud de baja ha sido tramitada');


                $previous_signature = $this->getDoctrine()->getRepository(TMSignatures::class)->findOneBy(array("template"=>$template),array("id" => "ASC"));

                if($template->getTmState()->getId()==6){
                    $signature = new TMSignatures();
                    $signature->setTemplate($template);
                    $signature->setAction($action);
                    $signature->setUserEntiy($user);
                    $signature->setCreated(new \DateTime());
                    $signature->setModified(new \DateTime());
                    $signature->setSignature("-");
                    $signature->setVersion($previous_signature->getVersion());
                    $signature->setConfiguration($previous_signature->getConfiguration());
                    if($request->get("description")){
                        $signature->setDescription($request->get("description"));
                    }
                    $em->persist($signature);


                    $users_notifications=array();
                    $action_admin = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 5));
                    $admins = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $action_admin));
                    foreach($admins as $admin){
                        if($admin->getUserEntiy()){
                            $users_notifications[]=$admin->getUserEntiy()->getEmail();
                        }
                        else{
                            foreach($admin->getGroupEntiy()->getUsers() as $user_group){
                                $users_notifications[]=$user_group->getUser()->getEmail();
                            }
                        }
                    }

                    $subject="Solicitud de baja";
                    $mensaje='Se ha tramitado la solicitud de baja para la plantilla con Código '.$template->getNumber().' - Título: '.$template->getName().' - Edición: '.$template->getNumEdition().'. Para poder revisar dicha soliciutd puede acceder a "Gestión de plantillas -> Solicitudes de baja", buscar la plantilla correspondiente y pulsar en Administrar';
                    $baseURL=$this->container->get('router')->generate('nononsense_tm_template_detail', array("id" => $id),TRUE)."?request_drop_action=1";
                    foreach($users_notifications as $email){
                        $this->get('utilities')->sendNotification($email, $baseURL, "", "", $subject, $mensaje);
                    }

                    $em->flush();

                    $route=$this->container->get('router')->generate('nononsense_home_homepage');
                    return $this->redirect($route);
                }

            }
        }
        return $this->returnToHomePage("No se ha podido efectuar la operación sobre la plantilla especificada. Es posible que ya se haya realizado una acción sobre ella o que la plantilla ya no exista");
    }

    /* Aceptar o rechazar solicitud de baja de una plantilla */
    public function responseRequestDropAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array=array();

        $is_valid = $this->get('app.security')->permissionSeccion('admin_gp');
        if(!$is_valid){
            return $this->returnToHomePage("No tiene permisos suficientes");
        }

        $password =  $request->get('password');
        if(!$password || !$this->get('utilities')->checkUser($password)){
            return $this->returnToHomePage("No se pudo firmar el registro, la contraseña es incorrecta");
        }

        $user = $this->container->get('security.context')->getToken()->getUser();
        if($request->get("action")){
            $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
            if($template){
                $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 8));
                if($action){

                    $drop_request = $this->getDoctrine()->getRepository(TMSignatures::class)->findOneBy(array("template" => $template, "action" => $action, "tmDropAction" => NULL));
                    if(!$drop_request){
                        return $this->returnToHomePage("No existe una solicitud de baja sobre esta plantilla");
                    }

                    $action_admin = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 5));
                    $workflow_admin = $this->getDoctrine()->getRepository(TMWorkflow::class)->findOneBy(array("template" => $template, "action" => $action_admin),array("id" => "ASC"));

                    if(!$workflow_admin || $workflow_admin->getUserEntiy()!=$user){
                        return $this->returnToHomePage("No tiene permisos para aceptar la solicitud de esta plantilla");
                    }

                    $previous_signature = $this->getDoctrine()->getRepository(TMSignatures::class)->findOneBy(array("template"=>$template),array("id" => "ASC"));

                    if($template->getTmState()->getId()==6){

                        $signature = new TMSignatures();
                        $signature->setTemplate($template);
                        switch($request->get("action")){
                            case 1: 
                                $drop_request->setTmDropAction(TRUE);
                                $em->persist($drop_request);
                                $action_response = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 9));
                                $description="Esta firma significa la autorización para la efectuar la baja de esta plantilla conforme a los procedimientos vigentes.";
                                if($request->get("description")){
                                    $description.=" ".$request->get("description");
                                }

                                $next_state = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id" => 8));
                                $template->setTmState($next_state);
                                $em->persist($template);

                                while($template->getTemplateId()!=NULL){
                                    $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $template->getTemplateId()));
                                    $template->setTmState($next_state);
                                    $em->persist($template);
                                }


                                $signature->setDescription($description);
                                $desc_error="La plantilla ha sido dada de baja con éxito";
                                break;
                            case 2: 
                                $drop_request->setTmDropAction(FALSE);
                                $em->persist($drop_request);
                                $action_response = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 10));
                                if($request->get("description")){
                                    $signature->setDescription($request->get("description"));
                                }
                                $desc_error="La solicitud de baja ha sido rechazada con éxito";
                                break;
                        }
                        
                        $signature->setAction($action_response);
                        $signature->setUserEntiy($user);
                        $signature->setCreated(new \DateTime());
                        $signature->setModified(new \DateTime());
                        $signature->setSignature("-");
                        $signature->setVersion($previous_signature->getVersion());
                        $signature->setConfiguration($previous_signature->getConfiguration());
                        $em->persist($signature);
                        
                        $em->flush();

                        return $this->returnToHomePage($desc_error, "message");
                    }
                    
                }
            }
        }

        return $this->returnToHomePage("No se ha podido efectuar la operación sobre la solicitud especificada. Es posible que ya se haya realizado una acción sobre ella");
    }

    /* Detalle historial de cambios de una plantilla */
    public function changesHistoryAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $filters=Array("user" => $user->getId());

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        $serializer = $this->get('serializer');
        $array_item["item"] = json_decode($serializer->serialize($template,'json',array('groups' => array('detail'))),true);
        if($template->getFirstEdition()){
            $filters["changes_history"]=$template->getFirstEdition();
        }
        else{
            $filters["changes_history"]=$template->getId();
        }

        $array_item["templates"] = $this->getDoctrine()->getRepository(TMTemplates::class)->list("list",$filters);
        foreach($array_item["templates"] as $key => $item){
            $signatures = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template"=>$item["id"]),array("id" => "ASC"));

            $array_item["templates"][$key]["elab"]="";
            $array_item["templates"][$key]["test"]="";
            $array_item["templates"][$key]["aprob"]="";
            $array_item["templates"][$key]["admin"]="";

            $elabs=array();
            $tests=array();
            $aprobs=array();
            $admins=array();

            foreach($signatures as $signature){
                switch($signature->getAction()->getId()){
                    case 2:$elabs[]=$signature->getUserEntiy()->getName();break;
                    case 3:$tests[]=$signature->getUserEntiy()->getName();break;
                    case 4:$aprobs[]=$signature->getUserEntiy()->getName();break;
                    case 5:$admins[]=$signature->getUserEntiy()->getName();break;
                }
            }

            $array_item["templates"][$key]["elab"]=implode(",", array_unique($elabs));
            $array_item["templates"][$key]["test"]=implode(",", array_unique($tests));
            $array_item["templates"][$key]["aprob"]=implode(",", array_unique($aprobs));
            $array_item["templates"][$key]["admin"]=implode(",", array_unique($admins));
        }

        return $this->render('NononsenseHomeBundle:TemplateManagement:template_history.html.twig',$array_item);
    }

    /* Auditrail de una plantilla */
    public function auditTrailAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        $serializer = $this->get('serializer');
        $array_item["item"] = json_decode($serializer->serialize($template,'json',array('groups' => array('detail'))),true);

        $array_item["signatures"] = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template" => $template),array("id" => "ASC"));
        return $this->render('NononsenseHomeBundle:TemplateManagement:template_audit_trail.html.twig',$array_item);
    }

    /* Página imprimible con la configuración base de la plantilla */
    public function coverPageAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        
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

        $url_edit_documento=$response["fillInUrl"];
        $html=file_get_contents($url_edit_documento);
        preg_match_all('/<div class="well" id="fill_html">(.*?)<\/div>.*?<\/form>/s',$html,$html_content);
        $array_item["html"]=$html_content[1][0];
        $array_item["template"]=$template;

        $array_item["signatures"] = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template" => $template),array("id" => "ASC"));

        return $this->render('NononsenseHomeBundle:TemplateManagement:template_cover_page.html.twig',$array_item);
    }

    public function configurationAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        
        if($request->get("configuration")){
            $configuration="?configuration=".$request->get("configuration");
        }
        else{
            $configuration="";
        }


        $base_url=$this->getParameter('api_docoaro')."/documents/".$template->getPlantillaId().$configuration;
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

        if(!isset($response["configurationUrl"])){
            return $this->returnToHomePage("No se puede cargar la configuración de esta plantilla");
        }
        
        return $this->redirect($response["configurationUrl"]);
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