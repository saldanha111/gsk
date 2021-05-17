<?php
namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasSteps;
use Symfony\Component\Filesystem\Filesystem;
use Nononsense\UtilsBundle\Classes;

use Nononsense\UserBundle\Entity\Users;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\HomeBundle\Entity\Areas;
use Nononsense\HomeBundle\Entity\TMTemplates;
use Nononsense\HomeBundle\Entity\CVRecords;
use Nononsense\HomeBundle\Entity\CVActions;
use Nononsense\HomeBundle\Entity\TMSecondWorkflow;
use Nononsense\HomeBundle\Entity\CVSignatures;
use Nononsense\HomeBundle\Entity\CVWorkflow;

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
    public function newAction(Request $request, int $template)
    {   
        $em = $this->getDoctrine()->getManager();
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
        $array["secondWf"]=$em->getRepository(TMSecondWorkflow::class)->findBy(array("template" => $template));
        $array["users"] = $em->getRepository(Users::class)->findAll();
        $array["groups"] = $em->getRepository(Groups::class)->findAll();
        
        return $this->render('NononsenseHomeBundle:CV:new_cumpli.html.twig',$array);
    }

    public function newSaveAction(Request $request, int $template)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');
        $array=array();
        $error=0;

        $items=$em->getRepository(TMTemplates::class)->list("list",array("id" => $template,"init_cumplimentation" => 1));

        $concat="?";

        if($request->get("logbook")){
            $concat.="logbook=".$request->get("logbook")."&";
        }

        if(!$items){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'La plantilla indicada no puede cumplimentarse'
            );
            $route = $this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
            return $this->redirect($route);
        }

        $item = $em->getRepository(TMTemplates::class)->findOneBy(array("id" => $items[0]["id"]));
        $action=$em->getRepository(CVActions::class)->findOneBy(array("id" => 15));
        $user = $this->container->get('security.context')->getToken()->getUser();

        $wfs=$em->getRepository(TMSecondWorkflow::class)->findBy(array("template" => $item),array("id" => "ASC"));
        

        $record= new CVRecords();
        $record->setTemplate($item);
        $record->setCreated(new \DateTime());
        $record->setModified(new \DateTime());
        $record->setInEdition(FALSE);
        $record->setEnabled(TRUE);
        $record->setState(NULL);
        $record->setUser($user);
        $em->persist($record);

        $params["data"]=array();

        $sign= new CVSignatures();
        $sign->setRecord($record);
        $sign->setUser($user);
        $sign->setAction($action);
        $sign->setCreated(new \DateTime());
        $sign->setModified(new \DateTime());
        $sign->setSigned(FALSE);

        $json_value=json_encode(array("data" => $params["data"]), JSON_FORCE_OBJECT);
        $sign->setJson($json_value);
        $em->persist($sign);

        $key=0;
        foreach($wfs as $wf){
            for ($i = 1; $i <= $wf->getSignaturesNumber(); $i++) {
                if($request->get($wf->getTmCumplimentation()->getName()) && array_key_exists($key, $request->get($wf->getTmCumplimentation()->getName())) && $request->get("relationals") && array_key_exists($key, $request->get("relationals"))){

                    $cvwf= new CVWorkflow();
                    $cvwf->setRecord($record);
                    $cvwf->setType($wf->getTmCumplimentation());

                    if($request->get($wf->getTmCumplimentation()->getName())[$key]=="1"){
                        $group=$em->getRepository(Groups::class)->findOneBy(array("id" => $request->get("relationals")[$key]));
                        $cvwf->setGroup($group);
                    }
                    else{
                        $user_aux=$em->getRepository(Users::class)->findOneBy(array("id" => $request->get("relationals")[$key]));
                        $cvwf->setUser($user_aux);
                    }

                    $cvwf->setNumber($key);
                    $cvwf->setSigned(FALSE);
                    $em->persist($cvwf);
                }
                else{
                    $error=1;
                }
                if($error){
                   break 2; 
                }
                $key++;
            }
        }

        
        if($error==0){
            $em->flush();

             $route = $this->container->get('router')->generate('nononsense_cv_docoaro_new', array("id" => $record->getId())).$concat;
        }
        else{
            $this->get('session')->getFlashBag()->add(
                'error',
                    'Hubo un error al intentar iniciar la cumplimentación de la plantilla'
            );
            $route = $this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
        }

        return $this->redirect($route);
    }
}