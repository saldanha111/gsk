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

class TemplateManagementRequestController extends Controller
{
    public function createAction(Request $request)
    {
    	$serializer = $this->get('serializer');
        $array_item=array();
        $array_item["areas"] = $this->getDoctrine()->getRepository(Areas::class)->findBy(array(),array("name" => "ASC"));
        $array_item["groups"] = $this->getDoctrine()->getRepository(Groups::class)->findBy(array(),array("name" => "ASC"));
        $array_item["users"] = $this->getDoctrine()->getRepository(Users::class)->findBy(array(),array("name" => "ASC"));
        
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
}