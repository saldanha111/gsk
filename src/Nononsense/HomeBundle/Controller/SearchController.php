<?php
/**
 * Nodalblock
 * User: Sergio
 * Date: 09/08/2019
 * Time: 10:32
 */
namespace Nononsense\HomeBundle\Controller;


use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasWorkflows;
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

class SearchController extends Controller
{
    public function listAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $filters=Array();
        $filters2=Array();
        $types=array();

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

        if($request->get("id")){
            $filters["id"]=$request->get("id");
            $filters2["id"]=$request->get("id");
        }

        if($request->get("plantilla_id")){
            $filters["plantilla_id"]=$request->get("plantilla_id");
            $filters2["plantilla_id"]=$request->get("plantilla_id");
        }

        if($request->get("status")){
            $filters["status"]=$request->get("status");
            $filters2["status"]=$request->get("status");
        }

        if($request->get("from")){
            $filters["from"]=$request->get("from");
            $filters2["from"]=$request->get("from");
        }

        if($request->get("until")){
            $filters["until"]=$request->get("until");
            $filters2["until"]=$request->get("until");
        }

        if($request->get("lot")){
            $filters["lot"]=$request->get("lot");
            $filters2["lot"]=$request->get("lot");
        }

        if($request->get("material")){
            $filters["material"]=$request->get("material");
            $filters2["material"]=$request->get("material");
        }

        if($request->get("sap")){
            $filters["sap"]=$request->get("sap");
            $filters2["sap"]=$request->get("sap");
        }

        if($request->get("equipment_number")){
            $filters["equipment_number"]=$request->get("equipment_number");
            $filters2["equipment_number"]=$request->get("equipment_number");
        }

        if($request->get("creator")){
            $filters["creator"]=$request->get("creator");
            $filters2["creator"]=$request->get("creator");
        }

        
        $array_item["suser"]["id"]=$user->getId();
        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(InstanciasWorkflows::class)->search("list",$filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(InstanciasWorkflows::class)->search("count",$filters2);

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
        
        return $this->render('NononsenseHomeBundle:Contratos:search.html.twig',$array_item);
    }
}