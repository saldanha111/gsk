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
use Nononsense\HomeBundle\Entity\TMSecondWorkflow;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CVCumplimentationController extends Controller
{
    public function newAction(Request $request, int $template, int $id)
    {
    	$serializer = $this->get('serializer');
        $array=array();

        $items=$this->getDoctrine()->getRepository(TMTemplates::class)->list("list",array("id" => $template,"init_cumplimentation" => 1));

        if(!$items){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'La plantilla indicada no puede cumplimentarse'
            );
            $route = $this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
            return $this->redirect($route);
        }

        $array["item"]=$items[0];
        $array["secondWf"]=$this->getDoctrine()->getRepository(TMSecondWorkflow::class)->findBy(array("template" => $template));
        $array["users"] = $this->getDoctrine()->getRepository(Users::class)->findAll();
        $array["groups"] = $this->getDoctrine()->getRepository(Groups::class)->findAll();

        if($id!=0){
            $array["id"]=$id;
        }
        else{
            $array["id"]=0;
        }
        
        return $this->render('NononsenseHomeBundle:CV:new_cumpli.html.twig',$array);
    }
}