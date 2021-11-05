<?php
/**
 * Nodalblock
 * User: Sergio
 * Date: 28/08/2019
 * Time: 09:07
 */
namespace Nononsense\HomeBundle\Controller;


use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\Activityuser;
use Nononsense\HomeBundle\Entity\CVSignatures;
use Nononsense\HomeBundle\Entity\TMCumplimentationsType;
use Nononsense\HomeBundle\Entity\Areas;
use Nononsense\HomeBundle\Entity\TMTemplates;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UtilsBundle\Classes;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Constraints\DateTime;
use Doctrine\ORM\Query\ResultSetMapping;

class ActivityController extends Controller
{
    public function listAction(Request $request){

        $is_valid = $this->get('app.security')->permissionSeccion('graphics_cv');
        if(!$is_valid){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No tiene permisos suficientes'
            );
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $filters=Array();
        $filters2=Array();
        $types=array();

        $filters=array_filter($request->query->all());
        $filters2=array_filter($request->query->all());

        if(isset($filters["action"]) && $filters["action"]==6){
            if(isset($filters["group"]) && ($filters["group"]==1 || $filters["group"]==4 || $filters["group"]==5 || $filters["group"]==7)){
                unset($filters["action"]);
                unset($filters2["action"]);
            }
            else{
                if(!isset($filters["group"])){
                    $filters["group"]=2;
                    $filters2["group"]=2;
                }
            }
        }

        $filters["user"]=$user;
        $filters2["user"]=$user;


        $array_item["suser"]["id"]=$user->getId();

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
            if($request->get("page")){
                $filters["limit_from"]=$request->get("page")-1;
            }
            else{
                $filters["limit_from"]=0;
            }

            if($request->get("limit_many")){
                $filters["limit_many"]=$request->get("limit_many");
            }
            else{
               $filters["limit_many"]=15; 
            }
        }
        else{
            $filters["limit_from"]=0;
            $filters["limit_many"]=99999999999999;
        }


        $array_item["suser"]["id"]=$user->getId();
        $array_item["filters"]=$filters;
        $array_item["items"] = $em->getRepository(CVSignatures::class)->activity("list",$filters);
        foreach($array_item["items"] as $key => $item){
            $array_item["items"][$key]["formatDuration"]=$this->convert_seconds($item["duration"]);
        }
        
        $array_item["count"] = $em->getRepository(CVSignatures::class)->activity("count",$filters2);

        $url=$this->container->get('router')->generate('nononsense_activity');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        $array_item["actions"] = $em->getRepository(TMCumplimentationsType::class)->search("list",array());
        $array_item["users"] = $em->getRepository(Users::class)->findAll();
        $array_item["areas"] = $em->getRepository(Areas::class)->findBy(array(),array("name" => "ASC"));

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
            switch($request->get("group")){
                default: return $this->render('NononsenseHomeBundle:Activity:activity_general.html.twig',$array_item);
            }
            
        }
    }


    public function templatesAction(Request $request){

        $is_valid = $this->get('app.security')->permissionSeccion('graphics_templates');
        if(!$is_valid){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No tiene permisos suficientes'
            );
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $filters=Array();
        $filters2=Array();
        $types=array();

        $filters=array_filter($request->query->all());
        $filters2=array_filter($request->query->all());


        $array_item["suser"]["id"]=$user->getId();

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
            if($request->get("page")){
                $filters["limit_from"]=$request->get("page")-1;
            }
            else{
                $filters["limit_from"]=0;
            }

            if($request->get("limit_many")){
                $filters["limit_many"]=$request->get("limit_many");
            }
            else{
               $filters["limit_many"]=15; 
            }
        }
        else{
            $filters["limit_from"]=0;
            $filters["limit_many"]=99999999999999;
        }


        $array_item["suser"]["id"]=$user->getId();
        $array_item["filters"]=$filters;

        /*$rsm = new ResultSetMapping();
        
        $query = $em->createNativeQuery("SELECT t.name,COUNT(t.id) AS conta FROM tm_templates t LEFT JOIN areas a3 ON t.area_id=a3.id WHERE t.first_edition IS NOT NULL GROUP BY t.first_edition,t.name ORDER BY conta DESC OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY ",$rsm);
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('conta', 'conta');

        $tests=$query->getResult();

        var_dump($tests);die();*/

        if($filters["group"]==1){
            $array_item["items"] = $em->getRepository(TMTemplates::class)->activity("list",$filters);
            $array_item["count"] = $em->getRepository(TMTemplates::class)->activity("count",$filters2);
        }
        else{
            $array_item["items"] = $em->getRepository(TMTemplates::class)->activityAux("list",$filters);
            $array_item["count"] = $em->getRepository(TMTemplates::class)->activityAux("count",$filters2);
            //var_dump($array_item["count"]);die();
        }

        $url=$this->container->get('router')->generate('nononsense_activity_templates');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        $array_item["areas"] = $em->getRepository(Areas::class)->findBy(array(),array("name" => "ASC"));

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
            switch($request->get("group")){
                default: return $this->render('NononsenseHomeBundle:Activity:templates.html.twig',$array_item);
            }
            
        }
    }

    private function convert_seconds($seconds)
    {
        $ret = "";

        /*** get the days ***/
        $days = intval(intval($seconds) / (3600*24));
        if($days> 0)
        {
            $ret .= "$days d, ";
        }

        /*** get the hours ***/
        $hours = (intval($seconds) / 3600) % 24;
        if($hours > 0)
        {
            $ret .= "$hours h, ";
        }

        /*** get the minutes ***/
        $minutes = (intval($seconds) / 60) % 60;
        if($minutes > 0)
        {
            $ret .= "$minutes m, ";
        }

        /*** get the seconds ***/
        $seconds = intval($seconds) % 60;
        if ($seconds > 0) {
            $ret .= "$seconds s";
        }

        return $ret;
    }

}