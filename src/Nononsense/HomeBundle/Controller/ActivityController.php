<?php
/**
 * Nodalblock
 * User: Sergio
 * Date: 28/08/2019
 * Time: 09:07
 */
namespace Nononsense\HomeBundle\Controller;


use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasWorkflows;
use Nononsense\HomeBundle\Entity\InstanciasSteps;
use Nononsense\HomeBundle\Entity\Activityuser;
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

class ActivityController extends Controller
{
    public function listAction(Request $request){

        var_dump(date());die();

        $user = $this->container->get('security.context')->getToken()->getUser();
        $can_be = false;

        foreach ($user->getGroups() as $groupMe) {
            $type = $groupMe->getGroup()->getTipo();
            if ($type == 'FLL') {
                $can_be = true;
            }
        }

        if (!$can_be) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos para acceder a esta sección'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $filters=Array();
        $filters2=Array();
        $types=array();

        $filters=array_filter($request->query->all());
        $filters2=array_filter($request->query->all());

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
               $filters["limit_many"]=99999999999999; 
            }
        }
        else{
            $filters["limit_from"]=0;
            $filters["limit_many"]=99999999999999;
        }


        $array_item["suser"]["id"]=$user->getId();
        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(ActivityUser::class)->search("list",$filters);
        foreach($array_item["items"] as $key => $item){
            $array_item["items"][$key]["formatDuration"]=$this->convert_seconds($item["duration"]);
        }
        $array_item["count"] = $this->getDoctrine()->getRepository(ActivityUser::class)->search("count",$filters2);

        $url=$this->container->get('router')->generate('nononsense_search');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
            switch($request->get("group")){
                default: return $this->render('NononsenseHomeBundle:Contratos:activity_general.html.twig',$array_item);
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