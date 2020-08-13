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

        if($request->get("no_request_in_proccess")){
            $in_proccess=1;
        }
        else{
            $in_proccess=0;
        }

        $items=$em->getRepository('NononsenseHomeBundle:TMTemplates')->listActiveForRequest(array("name"=>$request->get("name"),"no_request_in_proccess" => $in_proccess,"limit_from" => 0, "limit_many" => 10));
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
}