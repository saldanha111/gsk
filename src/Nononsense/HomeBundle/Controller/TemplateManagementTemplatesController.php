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
    public function listActiveJsonAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
        $array=array();

        $filters["limit_from"]=0;
        $filters["limit_many"]=10;

        if($request->get("no_request_in_proccess")){
            $filters["no_request_in_proccess"]=1;
        }

        if($request->get("nest")){
            $filters["nest"]=1;
        }

        if($request->get("name")){
            $filters["name"]=$request->get("name");
        }


        $items=$em->getRepository('NononsenseHomeBundle:TMTemplates')->listActiveForRequest($filters);
        $serializer = $this->get('serializer');
        $array_items = json_decode($serializer->serialize($items,'json',array('groups' => array('json'))),true);
        foreach($array_items as $key => $item){
            $array["items"][$key]["id"]=$item["id"];
            $array["items"][$key]["text"]=$item["name"]." - ".$item["prefix"];
            $array["items"][$key]["area"]=$item["area"]["id"];
        }

        $response = new Response(json_encode($array), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function listAction(Request $request)
    {
        $filters=Array();
        $filters2=Array();
        $types=array();

        $array_item["areas"] = $this->getDoctrine()->getRepository(Areas::class)->findBy(array(),array("name" => "ASC"));
        $array_item["groups"] = $this->getDoctrine()->getRepository(Groups::class)->findBy(array(),array("name" => "ASC"));
        $array_item["users"] = $this->getDoctrine()->getRepository(Users::class)->findBy(array(),array("name" => "ASC"));
        $array_item["states"] = $this->getDoctrine()->getRepository(TMStates::class)->findBy(array(),array("number" => "ASC"));
        
        

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
            $filters2["name"]=$request->get("name");
        }

        if($request->get("number")){
            $filters["number"]=$request->get("number");
            $filters2["number"]=$request->get("number");
        }

        if($request->get("area")){
            $filters["area"]=$request->get("area");
            $filters2["area"]=$request->get("area");
        }

        if($request->get("state")){
            $filters["state"]=$request->get("state");
            $filters2["state"]=$request->get("state");
        }

        if($request->get("applicant")){
            $filters["applicant"]=$request->get("applicant");
            $filters2["applicant"]=$request->get("applicant");
        }

        if($request->get("owner")){
            $filters["owner"]=$request->get("owner");
            $filters2["owner"]=$request->get("owner");
        }

        if($request->get("backup")){
            $filters["backup"]=$request->get("backup");
            $filters2["backup"]=$request->get("backup");
        }

        if($request->get("draft")){
            $filters["draft"]=$request->get("draft");
            $filters2["draft"]=$request->get("draft");
        }



        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(TMTemplates::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(TMTemplates::class)->count($filters2,$types);

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
        
        return $this->render('NononsenseHomeBundle:TemplateManagement:templates.html.twig',$array_item);
    }

    public function detailAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $array_item["template"] = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));

        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 2));
        $array_item["elab"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 3));
        $array_item["test"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 4));
        $array_item["aprob"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));
        $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 5));
        $array_item["admin"] = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $array_item["template"], "action" => $action),array("id" => "ASC"));


        return $this->render('NononsenseHomeBundle:TemplateManagement:template_detail.html.twig',$array_item);
    }

    public function actionRequestAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array=array();

        $is_valid = $this->get('app.security')->permissionSeccion('dueno_gp');
        if(!$is_valid){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos suficientes'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates');
            return $this->redirect($route);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();
        if($request->get("action") && $request->get("signature")){
            $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
            if($template){
                $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => $request->get("action")));
                if($action){
                    if($user!=$template->getOwner() && $user!=$template->getBackup()){
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            'Solo el dueño o backup puede aceptar o rechazar la solicitud'
                        );
                        $route=$this->container->get('router')->generate('nononsense_tm_templates');
                        return $this->redirect($route);
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
                            $signature->setSignature($request->get("signature"));
                            $signature->setVersion($previous_signature->getVersion());
                            $signature->setConfiguration($previous_signature->getConfiguration());
                            $em->persist($signature);

                            $em->flush();

                            $route=$this->container->get('router')->generate('nononsense_tm_templates');
                            return $this->redirect($route);
                        }
                    }
                }
            }
        }

        $this->get('session')->getFlashBag()->add(
            'error',
            'No se ha podido efectuar la operación sobre la plantilla especifiada. Es posible que ya se haya realizado una acción sobre ella o que la plantilla ya no exista'
        );
        $route=$this->container->get('router')->generate('nononsense_tm_templates');
        return $this->redirect($route);
    }

    public function changesHistoryAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $array_item=array();

        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $id));
        $serializer = $this->get('serializer');
        $array_item["item"] = json_decode($serializer->serialize($template,'json',array('groups' => array('detail'))),true);
        if($template->getFirstEdition()){
            $filters["changes_history"]=$template->getFirstEdition();
        }
        else{
            $filters["changes_history"]=$template->getId();
        }

        $array_item["templates"] = $this->getDoctrine()->getRepository(TMTemplates::class)->list($filters);
        foreach($array_item["templates"] as $key => $item){
            $signatures = $this->getDoctrine()->getRepository(TMSignatures::class)->findBy(array("template"=>$item["id"]),array("id" => "ASC"));
            $array_item["templates"][$key]["elab"]="";
            $array_item["templates"][$key]["test"]="";
            $array_item["templates"][$key]["aprob"]="";
            $array_item["templates"][$key]["admin"]="";
            foreach($signatures as $signature){
                switch($signature->getAction()->getId()){
                    case 2:$array_item["templates"][$key]["elab"].=$signature->getUserEntiy()->getName().",";break;
                    case 3:$array_item["templates"][$key]["test"].=$signature->getUserEntiy()->getName().",";break;
                    case 4:$array_item["templates"][$key]["aprob"].=$signature->getUserEntiy()->getName().",";break;
                    case 5:$array_item["templates"][$key]["admin"].=$signature->getUserEntiy()->getName().",";break;
                }
            }
        }

        return $this->render('NononsenseHomeBundle:TemplateManagement:template_history.html.twig',$array_item);
    }

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
}