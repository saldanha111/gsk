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
        
        return $this->render('NononsenseHomeBundle:TemplateManagement:request.html.twig',$array_item);
    }

    public function listAction(Request $request)
    {
    	$serializer = $this->get('serializer');
        $array_item=array("count" => 0);
        $array_item["areas"] = $this->getDoctrine()->getRepository(Areas::class)->findBy(array(),array("name" => "ASC"));
        $array_item["groups"] = $this->getDoctrine()->getRepository(Groups::class)->findBy(array(),array("name" => "ASC"));
        $array_item["users"] = $this->getDoctrine()->getRepository(Users::class)->findBy(array(),array("name" => "ASC"));
        $array_item["states"] = $this->getDoctrine()->getRepository(TMStates::class)->findBy(array(),array("name" => "ASC"));

        return $this->render('NononsenseHomeBundle:TemplateManagement:requests.html.twig',$array_item);
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

        if(!$request->get("request_type")){
            $this->get('session')->getFlashBag()->add(
                'error',
                'El campo "Tipo de solicitud" es obligatorio'
            );
            $route = $this->container->get('router')->generate('nononsense_tm_request');
            return $this->redirect($route);
        }

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

        if(!$request->get("signature")){
            $array_error[]="Firma";
        }

        if(!empty($array_error)) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Los siguientes campos son obligatorios: '.implode(",", $array_error)
            );
            $route = $this->container->get('router')->generate('nononsense_tm_request');
            //return $this->redirect($route);
            var_dump($array_error);die();
        }

        switch($request->get("request_type")){
            case 1:
                $area = $this->getDoctrine()->getRepository(Areas::class)->findOneById($request->get("area"));
                if(!$area){
                    $this->get('session')->getFlashBag()->add('error','El área indicado no existe');
                    $route = $this->container->get('router')->generate('nononsense_tm_request');
                    //return $this->redirect($route);
                    var_dump("error");die();
                }

                $prefix = $this->getDoctrine()->getRepository(AreaPrefixes::class)->findOneBy(array("id"=> $request->get("prefix"),"area" => $area));
                if(!$prefix){
                    $this->get('session')->getFlashBag()->add('error','La numeración indicada no existe');
                    $route = $this->container->get('router')->generate('nononsense_tm_request');
                    //return $this->redirect($route);
                    var_dump("error");die();
                }
                $desc_prefix=$prefix->getName();
                $retentions = $this->getDoctrine()->getRepository(RetentionCategories::class)->findBy(array("id"=> $request->get("retention")));
                $name=$request->get("name");
                $plantilla=$area->getTemplate()->getPlantillaId();
                $itemplate=NULL;
                $num_edition=1;
                $first_edition=NULL;
                break;
            case 2:
                $templates=$em->getRepository('NononsenseHomeBundle:TMTemplates')->listActiveForRequest(array("id"=>$request->get("template"),"no_request_in_proccess" => 1));
                if($templates){
                    $this->get('session')->getFlashBag()->add('error','El template indicado no está disponible');
                    $route = $this->container->get('router')->generate('nononsense_tm_request');
                    //return $this->redirect($route);
                    var_dump("error");die();
                }
                $desc_prefix=$templates[0]->getPrefix();
                $area=$templates[0]->getArea();
                $retentions=$templates[0]->getRetentions();
                $name=$templates[0]->getName();
                $plantilla=$templates[0]->getPlantillaId();
                $itemplate=$templates[0]->getTemplateId();
                $num_edition=$templates[0]->getNumEdition()+1;
                if(!$templates[0]->getFirstEdition()){
                    $first_edition=$templates[0]->getId();
                }
                else{
                    $first_edition=$templates[0]->getFirstEdition();
                }
                break;
        }

        $number=$desc_prefix;
        $number= str_replace("#X#", str_pad($id, 6, "0", STR_PAD_LEFT), $number);
        $number= str_replace("#E#", str_pad($num_edition, 2, "0", STR_PAD_LEFT), $number);
        $number= str_replace("#YY#", date("YY"), $number);
        $number= str_replace("#YYYY#", date("YYYY"), $number);
        if(($request->get("area")==10 || $request->get("area")==11)){
            $number= str_replace("#NUMPROY#", $request->get("num_project"), $number);
        }
        

        $number=str_pad($num_edition, 10, "0", STR_PAD_LEFT);
        
        $state = $this->getDoctrine()->getRepository(TMStates::class)->findOneBy(array("id"=> 1));

        $template = new TMTemplates();
        $template->setState($state);
        $template->setName($name);
        $template->setArea($area);
        $template->setPrefix($desc_prefix);
        $template->setNumber($number);
        $template->setPlantillaId($plantilla);
        $template->setDescription($request->get("description"));
        $template->setHistoryChange($request->get("history_change"));
        $template->setLogbook(0);
        $template->setCreated(new \DateTime());
        $template->setModified(new \DateTime());
        $template->setTemplateId($itemplate);
        $template->setNumEdition($num_edition);
        $template->setFirstEdition($first_edition);

        $em->persist($template);
        $em->flush();

        echo "Pasamos!!";die();
        
    }
}