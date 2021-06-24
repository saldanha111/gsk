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
use Nononsense\HomeBundle\Entity\CVStates;
use Nononsense\HomeBundle\Entity\CVRecordsHistory;
use Nononsense\HomeBundle\Entity\CVRequestTypes;
use Nononsense\GroupBundle\Entity\GroupUsers;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Nononsense\UtilsBundle\Classes\Auxiliar;
use Nononsense\UtilsBundle\Classes\Utils;

class CVModificationGxPController extends Controller
{
    //Preparamos la reapertura de una cumplimentación en estado final
    public function requestAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');
        $array=array();
        $error=0;

        $item = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $id));

        if(!$item){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'El registro no existe'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        if(!$item->getState()->getFinal()){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'El registro no se encuentra en un estado final y por tanto no se puede solicitar una modificación'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $signature = $this->getDoctrine()->getRepository(CVSignatures::class)->findOneBy(array("record" => $item),array("id" => "DESC"));

        if($signature->getAction()->getId()==18){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No se puede puede modificar este registro porque ya hay una solicitud de modificación'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $concat="?reupdate=1&";

        if($request->get("logbook")){
            $concat.="logbook=".$request->get("logbook")."&";
        }

        /*$action=$em->getRepository(CVActions::class)->findOneBy(array("id" => 18));
        $user = $this->container->get('security.context')->getToken()->getUser();


        $sign= new CVSignatures();
        $sign->setRecord($item);
        $sign->setUser($user);
        $sign->setAction($action);
        $sign->setCreated(new \DateTime());
        $sign->setModified(new \DateTime());
        $sign->setSigned(FALSE);
        $sign->setJustification(TRUE);
        $sign->setNumberSignature(($signature->getNumberSignature()+1));
        $sign->setJson($signature->getJson());
        $em->persist($sign);*/

        if($error==0){
            $em->flush();
            $route = $this->container->get('router')->generate('nononsense_cv_docoaro_new', array("id" => $item->getId())).$concat;
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