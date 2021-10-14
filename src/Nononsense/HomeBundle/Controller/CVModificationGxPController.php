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
use Nononsense\HomeBundle\Entity\CVSecondWorkflow;
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

        if($signature && $signature->getAction()->getId()==18){
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

    //Visualizamos el GxP a modificar
    public function viewAction(Request $request, int $id)
    {   
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');
        $array=array();

        $is_valid = $this->get('app.security')->permissionSeccion('aprobacion_gxp');
        if(!$is_valid){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos suficientes'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
            return $this->redirect($route);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $items=$this->getDoctrine()->getRepository(CVRecords::class)->search("list",array("id" => $id,"gxp" => 1,"action_gxp" => 1,"user" => $user));

        if(!$items){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No se puede aprobar la modificación GxP de este registro'
            );
            $route = $this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
            return $this->redirect($route);
        }

        $record = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $items[0]["id"]));

        $wf=$this->get('utilities')->wich_second_wf($record,$user);
        if(!$wf){
             $this->get('session')->getFlashBag()->add(
                'error',
                    'No tiene permisos suficientes'
            );
            $route = $this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
            return $this->redirect($route);
        }
    
        $array["item"]=$items[0];

        $array["users"] = $em->getRepository(Users::class)->findAll();
        $array["groups"] = $em->getRepository(Groups::class)->findAll();
        $array["signature_request"] = $em->getRepository(CVSignatures::class)->findOneBy(array("record" => $items[0]["id"],"action" => 18),array("id" => "DESC"));
        $array["secondWf"] = $em->getRepository(CVSecondWorkflow::class)->findBy(array("record" => $items[0]["id"]),array("id" => "ASC"));
        $array["currentWf"] = $wf;

        
        return $this->render('NononsenseHomeBundle:CV:view_gxp.html.twig',$array);
    }

    //Aprobamos o rechazamos la modificación GxP
    public function saveAction(Request $request, int $template)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');
        $array=array();
        $error=0;

        $is_valid = $this->get('app.security')->permissionSeccion('aprobacion_gxp');
        if(!$is_valid){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos suficientes'
            );
            $route=$this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
            return $this->redirect($route);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $items=$this->getDoctrine()->getRepository(CVRecords::class)->search("list",array("id" => $id,"gxp" => 1,"action_gxp" => 1,"user" => $user));

        if(!$items){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No se puede aprobar la modificación GxP de este registro'
            );
            $route = $this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
            return $this->redirect($route);
        }

        $record = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $items[0]["id"]));

        $wf=$this->get('utilities')->wich_second_wf($record,$user);
        if(!$wf){
             $this->get('session')->getFlashBag()->add(
                'error',
                    'No tiene permisos suficientes'
            );
            $route = $this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
            return $this->redirect($route);
        }

        if(!$request->get('password') || !$this->get('utilities')->checkUser($request->get('password'))){
            $this->get('session')->getFlashBag()->add(
                'error',
                "No se pudo firmar el registro, la contraseña es incorrecta"
            );
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }

        if($wf->getNumberSignature()==1){
            if(!$request->get('observations')){
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "El campo observaciones es obligatorio"
                );
                return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
            }

            if($request->get('types')){
                $count=$wf->getNumberSignature()+1;
                foreach($request->get('types') as $key => $type){
                    $aux_wf = new CVSecondWorkflow();
                    if($type==1){
                        $aux_group = $group=$em->getRepository(Groups::class)->findOneBy(array("id" => $request->get('relationals')[$key]));
                        $aux_wf->setGroup($aux_group);
                    }
                    else{
                        $aux_user = $group=$em->getRepository(Users::class)->findOneBy(array("id" => $request->get('relationals')[$key]));
                        $aux_wf->setUser($aux_user);
                    }

                    $aux_wf->setRecord($record);
                    $aux_wf->setNumberSignature($count);
                    $aux_wf->setSigned(FALSE);
                    $em->persist($aux_wf);
                    $count++;
                }
            }
            
        }

        $wf->setSigned(TRUE);
        $wf->setUser($user);
        $em->persist($aux_wf);

        $all_signatures = $this->getDoctrine()->getRepository(CVSignatures::class)->findBy(array("record" => $record)); 
        $last_signature = $this->getDoctrine()->getRepository(CVSignatures::class)->findOneBy(array("record" => $record),array("id" => "DESC"));

        $signature = new CVSignatures();
        $signature->setUser($user);
        $signature->setRecord($record);
        $signature->setNumberSignature((count($all_signatures)+1));
        $signature->setJustification(FALSE);
        $signature->setCreated($record->getOpenDate());
        $signature->setAction($action);
        $signature->setSigned(TRUE);
        $signature->setCreated(new \DateTime());
        $signature->setModified(new \DateTime());
        $signature->setJson($last_signature->getJson());
        $signature->setJsonAux($last_signature->getJsonAux());














        



        $item = $em->getRepository(TMTemplates::class)->findOneBy(array("id" => $items[0]["id"]));
        $action=$em->getRepository(CVActions::class)->findOneBy(array("id" => 15));
        $state=$em->getRepository(CVStates::class)->findOneBy(array("id" => 1));
        $user = $this->container->get('security.context')->getToken()->getUser();

        $wfs=$em->getRepository(TMSecondWorkflow::class)->findBy(array("template" => $item),array("id" => "ASC"));
        
        if(!$request->get("nest")){
            $record= new CVRecords();
            $record->setTemplate($item);
            $record->setCreated(new \DateTime());
            $record->setModified(new \DateTime());
            $record->setInEdition(FALSE);
            $record->setEnabled(TRUE);
            $record->setState($state);
        }
        else{
            $record = $em->getRepository(CVRecords::class)->findOneBy(array("id" => $request->get("nest")));
        }

        $record->setUser($user);

        if(!$record->getNested()){
            $before_nested=$record;
            foreach($item->getTmNestMasterTemplates() as $subtemplate){
                $aux_record= new CVRecords();
                $aux_record->setTemplate($subtemplate->getNestTemplate());
                $aux_record->setCreated(new \DateTime());
                $aux_record->setModified(new \DateTime());
                $aux_record->setInEdition(FALSE);
                $aux_record->setEnabled(TRUE);
                $aux_record->setState($state);
                $aux_record->setNested($before_nested);
                $aux_record->setFirstNested($record);
                $em->persist($aux_record);
                $before_nested=$aux_record;
            }
        }

        $params["data"]=array();

        $sign= new CVSignatures();
        $sign->setRecord($record);
        $sign->setUser($user);
        $sign->setAction($action);
        $sign->setCreated(new \DateTime());
        $sign->setModified(new \DateTime());
        $sign->setSigned(FALSE);
        $sign->setJustification(FALSE);
        $sign->setNumberSignature(1);

        $array_unique=array();
        if($request->get("unique")){
            foreach($request->get("unique") as $unique){
                if($request->get($unique)){
                    $params["data"][$unique]=$request->get($unique);
                    $array_unique[$unique]=$request->get($unique);
                }
            }

            $array_unique["gsk_template_id"]=$item->getId();
        }

        $json_unique=json_encode($array_unique, JSON_FORCE_OBJECT);
        $record->setCodeUnique($json_unique);
        $em->persist($record);

        if($request->get("value_qr")){
            $value_qr=json_decode($request->get("value_qr"), true);
            $params["data"]=array_merge($params["data"],$value_qr);
        }

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

                    $cvwf->setNumberSignature($key);
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

        //Miramos si es una plantilla reconciliable
        $reconc=0;
        if($item->getUniqid()){
            //Miramos si se tiene que reconciliar
            $reconciliation = $this->getDoctrine()->getRepository(CVRecords::class)->search("list",array("user"=> $user, "plantilla_id" => $item->getId(),"code_unique" => $array_unique, "limit_from" => 0,"limit_many" => 1));
            if($reconciliation[0]){
                $recon=$em->getRepository(CVRecords::class)->findOneBy(array("id" => $reconciliation[0]["id"]));
                $record->setReconciliation($recon);
                $record->setJson($recon->getJson());
                if($recon->getFirstReconciliation()){
                    $record->setFirstReconciliation($recon->getFirstReconciliation());
                }
                else{
                    $record->setFirstReconciliation($recon);
                }
                $reconc=1;
                $em->persist($sign);
                $em->persist($record);
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