<?php
namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
use Nononsense\HomeBundle\Entity\CVSecondWorkflowStates;
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

        /*$is_valid = $this->get('app.security')->permissionSeccion('aprobacion_gxp');
        if(!$is_valid){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos suficientes'
            );
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }*/

        $user = $this->container->get('security.context')->getToken()->getUser();
        $users_actions=$this->get('utilities')->get_users_actions($user,2);

        $items=$this->getDoctrine()->getRepository(CVRecords::class)->search("list",array("id" => $id,"gxp" => 1,"pending_gxp" => 1,"users" => $users_actions));

        if(!$items){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No se puede aprobar la modificación GxP de este registro'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $record = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $items[0]["id"]));

        $wf=$this->get('utilities')->wich_second_wf($record,$user,1,2);
        if(!$wf){
             $this->get('session')->getFlashBag()->add(
                'error',
                    'No tiene permisos suficientes'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }
    
        $array["item"]=$items[0];

        $array["users"] = $em->getRepository(Users::class)->findBy(array(),array("name" => "ASC"));
        $array["groups"] = $em->getRepository(Groups::class)->findBy(array("isActive" => TRUE),array("name" => "ASC"));
        $array["signature_request"] = $em->getRepository(CVSignatures::class)->findOneBy(array("record" => $items[0]["id"],"action" => 18),array("id" => "DESC"));
        $array["secondWf"] = $em->getRepository(CVSecondWorkflow::class)->findBy(array("record" => $items[0]["id"]),array("id" => "ASC"));
        $array["currentWf"] = $wf;

        
        return $this->render('NononsenseHomeBundle:CV:view_gxp.html.twig',$array);
    }

    //Aprobamos o rechazamos la modificación GxP
    public function saveAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');
        $array=array();
        $error=0;

        /*$is_valid = $this->get('app.security')->permissionSeccion('aprobacion_gxp');
        if(!$is_valid){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos suficientes'
            );
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }*/

        $user = $this->container->get('security.context')->getToken()->getUser();
        $users_actions=$this->get('utilities')->get_users_actions($user,2);

        $items=$this->getDoctrine()->getRepository(CVRecords::class)->search("list",array("id" => $id,"gxp" => 1,"pending_gxp" => 1,"users" => $users_actions));

        if(!$items){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No se puede aprobar la modificación GxP de este registro'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $record = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $items[0]["id"]));

        $wf=$this->get('utilities')->wich_second_wf($record,$user,1,2);

        if(!$wf){
             $this->get('session')->getFlashBag()->add(
                'error',
                    'No tiene permisos suficientes'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        if(!$request->get('password') || !$this->get('utilities')->checkUser($request->get('password'))){
            $this->get('session')->getFlashBag()->add(
                'error',
                "No se pudo firmar el registro, la contraseña es incorrecta"
            );
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }


        if(!$request->get('action')){
            $this->get('session')->getFlashBag()->add(
                'error',
                "No se ha detectado ninguna acción sobre la modificación GxP"
            );
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }

        //Observaciones obligatorias si se trata de un rechazo
        if($request->get('action')!=1){
            if(!$request->get('observations')){
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "El campo observaciones es obligatorio"
                );
                return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
            }
        }
        
        switch($request->get('action')){
            case 1: $id_action=26;break;
            default:$id_action=27;break;
        }

        $action=$em->getRepository(CVActions::class)->findOneBy(array("id" => $id_action));
        $typesw = $em->getRepository(CVSecondWorkflowStates::class)->findOneBy(array("id" => "1"));

        //Si es el ECO puede añadir firmas adicionales
        if($wf->getNumberSignature()==1 && $request->get('action')==1){
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

                    $aux_wf->setType($typesw);
                    $aux_wf->setRecord($record);
                    $aux_wf->setNumberSignature($count);
                    $aux_wf->setSigned(FALSE);
                    $em->persist($aux_wf);

                    $record->addCvSecondWorkflow($aux_wf);
                    $count++;
                }
            } 
        }

        $wf->setSigned(TRUE);
        /*if(!$wf->getUser()){
            $wf->setUser($user);
        }*/
        $em->persist($wf);

        $all_signatures = $this->getDoctrine()->getRepository(CVSignatures::class)->findBy(array("record" => $record)); 
        $last_signature = $this->getDoctrine()->getRepository(CVSignatures::class)->findOneBy(array("record" => $record),array("id" => "DESC"));

        $signature = new CVSignatures();
        $signature->setUser($user);
        $signature->setRecord($record);
        $signature->setNumberSignature((count($all_signatures)+1));
        $signature->setJustification(FALSE);
        $signature->setAction($action);
        $signature->setSigned(TRUE);
        $signature->setCreated(new \DateTime());
        $signature->setModified(new \DateTime());
        $signature->setSignDate(new \DateTime());
        $signature->setJson($last_signature->getJson());
        $signature->setJsonAux($last_signature->getJsonAux());
        $signature->setJsonInfo($last_signature->getJsonInfo());
        $signature->setVersion($last_signature->getVersion());
        $signature->setConfiguration($last_signature->getConfiguration());
        $signature->setFinish(TRUE);

        


        //Miramos si es una firma delegada o no
        $delegation=FALSE;
        if($wf && $wf->getUser()!=$user){
            $delegation=TRUE;
            foreach($user->getGroups() as $uniq_group){
                if($uniq_group->getGroup()==$wf->getGroup()){
                    $delegation=FALSE;
                    break;
                }
            }
        }
        $signature->setDelegation($delegation);

        if($request->get('observations')){
            $signature->setJustification(TRUE);
            $signature->setDescription($request->get('observations'));
        }

        $em->persist($signature);
        
        $pending_wf=0;
        foreach($record->getCvSecondWorkflows() as $pending){
            if(!$pending->getSigned()){
                $pending_wf++;
            }
        }
        
        $message_ok="Ha firmado la aprobación de la modificación. El cambio solo tendrá efecto cuando firmen todos los firmantes";

        //Si ya no hay registros pendientes en el workflow o es un rechazo
        if($pending_wf==0 || $request->get('action')!=1){

            foreach($record->getCvSecondWorkflows() as $item_wf){
                $em->remove($item_wf);
            }

            $record->setUserGxP(NULL);
            $em->persist($record);

            // Es la última aprobación
            if($request->get('action')==1){
                $signature_request = $em->getRepository(CVSignatures::class)->findOneBy(array("record" => $record->getId(),"action" => 18),array("id" => "DESC"));

                $obj1 = json_decode($signature->getJsonAux())->data;
                $obj2 = json_decode($signature->getJson())->data;
                $obj3 = json_decode($signature->getRecord()->getJson())->configuration;

                //Compares new signature with old step and instert differences
                $this->get('utilities')->multi_obj_diff_counter = 0;
                $this->get('utilities')->multi_obj_diff($obj1, $obj2, $obj3, '$obj2->$key', '/^(in_|gsk_|dxo_|delete_)|(name|extension\b)/', false, $signature_request, false, null, 'new');

                //Compares old signature with new step and check removed fields
                $this->get('utilities')->multi_obj_diff_counter = 0;
                $this->get('utilities')->multi_obj_diff($obj2, $obj1, $obj3, '$obj2->$key', '/^(in_|gsk_|dxo_|delete_)|(name|extension\b)/', false, $signature_request, false, null, 'old');

                $signature->setJson($signature->getJsonAux());

                //Grabamos valores con etiqueta info para la busqueda por esta etiqueta
                if($record->getJson()){
                    $json_record_pointer=json_decode($record->getJson(),TRUE);
                    $json_info=array();
                    $array_signature=json_decode($signature->getJson(),TRUE);
                    foreach($array_signature["data"] as $key => $values){
                        if (array_key_exists($key,$json_record_pointer["configuration"]["variables"]) && $json_record_pointer["configuration"]["variables"][$key]["info"]!="" && $json_record_pointer["configuration"]["variables"][$key]["info"]!=$key){
                            $json_info["data"][$json_record_pointer["configuration"]["variables"][$key]["info"]]=$values;
                        }
                    }
                    if(!empty($json_info)){
                        $signature->setJsonInfo(json_encode($json_info, JSON_FORCE_OBJECT));
                    }
                }

                $em->persist($signature);
            }
            
            $em->flush();

            //Certificamos solo tras la última acción sobre el flujo de modificación gxp
            $request->attributes->set("pdf", '1');
            $request->attributes->set("no-redirect", true);
            $slug="record-modificacion-gxp";

            $file = Utils::api3($this->forward('NononsenseHomeBundle:CVDocoaro:link', ['request' => $request, 'id'  => $record->getId()])->getContent());
            $file = Utils::saveFile($file, $slug, $this->getParameter('crt.root_dir'));
            Utils::setCertification($this->container, $file, $slug, $record->getId());  

            $message_ok="Se ha aprobado la modificación de la plantilla correctamente";              
        }

        if($request->get('action')==1){
            $this->get('session')->getFlashBag()->add(
                'success',
                $message_ok
            );
        }
        else{
            $this->get('session')->getFlashBag()->add(
                'success',
                "Se ha rechazado la modificación de la plantilla correctamente"
            );
        }

        $em->flush();
        $route = $this->container->get('router')->generate('nononsense_cv_search')."?gxp=1";
        
        return $this->redirect($route);
    }
}