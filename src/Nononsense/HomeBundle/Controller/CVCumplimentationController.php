<?php
namespace Nononsense\HomeBundle\Controller;

use Doctrine\ORM\EntityManager;
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
use Nononsense\HomeBundle\Entity\SpecificGroups;
use Nononsense\HomeBundle\Entity\TMCumplimentations;
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

class CVCumplimentationController extends Controller
{
    //Creamos nuevo registro
    public function newAction(Request $request, int $template)
    {   
        $em = $this->getDoctrine()->getManager();
    	$serializer = $this->get('serializer');
        $array=array();

        if(!$request->get("record")){
            $filter="init_cumplimentation";
        }
        else{
            $filter="nest_init_cumplimentation";
        }
       
        $items=$this->getDoctrine()->getRepository(TMTemplates::class)->list("list",array("id" => $template,$filter => 1));

        if(!$items){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'La plantilla indicada no puede cumplimentarse'
            );
            $route = $this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
            return $this->redirect($route);
        }

        $array["item"]=$items[0];

        if($request->get("record")){
            $record = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $request->get("record")));
            if(!$record || $record->getTemplate()->getId()!=$array["item"]["id"]){
                $this->get('session')->getFlashBag()->add(
                    'error',
                        'El registro indicado no puede cumplimentarse'
                );
                $route = $this->container->get('router')->generate('nononsense_cv_search');
                return $this->redirect($route);
            }
            $array["nest"]=$request->get("record");
        }

        $array["type_cumplimentations"] = $this->getDoctrine()->getRepository(TMCumplimentations::class)->findBy(array(),array("id" => "ASC"));
        $array["secondWf"]=$em->getRepository(TMSecondWorkflow::class)->findBy(array("template" => $template));
        $array["users"] = $em->getRepository(Users::class)->findBy(array(),array("name" => "ASC"));
        $array["groups"] = $em->getRepository(Groups::class)->findBy(array(),array("name" => "ASC"));
        
        return $this->render('NononsenseHomeBundle:CV:new_cumpli.html.twig',$array);
    }

    //Guardamos el nuevo registro (PRE CUMPLIMENTADO)
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
        $state=$em->getRepository(CVStates::class)->findOneBy(array("id" => 1));
        $user = $this->container->get('security.context')->getToken()->getUser();

        $wfs=$em->getRepository(TMSecondWorkflow::class)->findBy(array("template" => $item),array("id" => "ASC"));
        
        if(!$request->get("nest")){
            $record= new CVRecords();
            $record->setTemplate($item);
            $record->setCreated(new \DateTime());
            $record->setModified(new \DateTime());
            $record->setPending(FALSE);
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
                $aux_record->setPending(FALSE);
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

            //$array_unique["gsk_template_id"]=$item->getId();
        }

        $json_unique=json_encode($array_unique, JSON_FORCE_OBJECT);
        $record->setCodeUnique($json_unique);
        $em->persist($record);

        if($request->get("value_qr")){
            $value_qr=json_decode($request->get("value_qr"), true);
            $params["data"]=array_merge($params["data"],$value_qr);
        }

        if($request->get("unique") || $request->get("value_qr")){
            $params["data"]["gsk_init_prefill"]=1;
        }

        $json_value=json_encode(array("data" => $params["data"]), JSON_FORCE_OBJECT);
        $sign->setJson($json_value);
        $em->persist($sign);

        $key=0;

        $expected=array();
        $loaded=array();

        foreach($wfs as $wf){
            if (!array_key_exists($wf->getTmCumplimentation()->getId(), $expected)) {
                $expected[$wf->getTmCumplimentation()->getId()]=0;
            }
            $expected[$wf->getTmCumplimentation()->getId()]+=$wf->getSignaturesNumber();
        }

        foreach($request->get("types") as $item_type){
            if (!array_key_exists($item_type, $loaded)) {
                $loaded[$item_type]=0;
            }
            $loaded[$item_type]++;
        }

        foreach($expected as $key => $item_expected){
            if($item_expected>$loaded[$key]){
                $this->get('session')->getFlashBag()->add(
                    'error',
                        'Se esperaban más firmas en el workflow de las especificadas'
                );
                $route = $this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
                
                return $this->redirect($route);
            }
        }

        

        $last_mode=0;
        foreach($request->get("types") as $key => $item_type){
            $cvtype=$em->getRepository(TMCumplimentations::class)->findOneBy(array("id" => $item_type));
            if($cvtype->getTmType()->getId()<$last_mode){
                $this->get('session')->getFlashBag()->add(
                    'error',
                        'Error en el orden en el que se han introducido el workflow de firmantes'
                );
                $route = $this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
                return $this->redirect($route);
            }
            $cvwf= new CVWorkflow();
            $cvwf->setRecord($record);
            $cvwf->setType($cvtype);

            if($request->get("entities")[$key]=="1"){
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
            
            $last_mode=$cvtype->getTmType()->getId();
        }

        //Miramos si es una plantilla reconciliable
        $reconc=0;
        if($item->getUniqid()){
            //Miramos si se tiene que reconciliar
            $users_actions=$this->get('utilities')->get_users_actions($user,1);
            $reconciliation = $this->getDoctrine()->getRepository(CVRecords::class)->search("list",array("plantilla_id" => $item->getId(),"code_unique" => $array_unique, "limit_from" => 0,"limit_many" => 1,"users" => $users_actions));
            if($reconciliation && $reconciliation[0]){
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

                //Grabamos valores con etiqueta info para la busqueda por esta etiqueta
                if($record->getJson()){
                    $json_record=json_decode($record->getJson(),TRUE);
                    $json_info=array();
                    foreach($params["data"] as $key => $values){
                        if (array_key_exists($key,$json_record["configuration"]["variables"]) && $json_record["configuration"]["variables"][$key]["info"]!="" && $json_record["configuration"]["variables"][$key]["info"]!=$key){
                            $json_info["data"][$json_record["configuration"]["variables"][$key]["info"]]=$values;
                        }
                    }
                    if(!empty($json_info)){
                        $sign->getJsonInfo(json_encode($json_info, JSON_FORCE_OBJECT));
                        $em->persist($sign);
                    }
                }

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

    //Pedimos al usuario que firme la cumplimentación/verificación
    public function recordAction(Request $request, int $id)
    {   
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');
        $array=array();

        $array["item"] = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $id));

        if(!$array["item"]){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'El registro no existe'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        if($array["item"]->getRedirectSearch()){
            $array["item"]->setRedirectSearch(FALSE);
            $em->persist($array["item"]);
            $em->flush();
            $route = $this->container->get('router')->generate('nononsense_cv_search');
            return $this->redirect($route);
        }

        $array["signature"] = $this->getDoctrine()->getRepository(CVSignatures::class)->findOneBy(array("record" => $array["item"]),array("id" => "DESC"));

        if(!$array["signature"] || $array["signature"]->getSigned() || (!$array["signature"]->getVersion() && $array["signature"]->getAction()->getId()!=12)){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'El registro no se puede firmar'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        if($array["signature"]->getUser()!=$user){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'Usted no puede firmar esta evidencia'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $users_actions=$this->get('utilities')->get_users_actions($user,1);

        $can_sign = $this->getDoctrine()->getRepository(CVRecords::class)->search("count",array("id" => $array["item"]->getId(),"pending_for_me" => 1,"users" => $users_actions));

        if($can_sign==0){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No puede abrir esta plantilla debido al workflow definido'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        return $this->render('NononsenseHomeBundle:CV:sign.html.twig',$array);
    }

    //Firmamos la cumplimentación/verificación
    public function signAction(Request $request, int $id)
    {   
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');
        $array=array();
        $send_email=0;

        $record = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $id));

        if(!$record){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'El registro no existe'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $signature = $this->getDoctrine()->getRepository(CVSignatures::class)->findOneBy(array("record" => $record),array("id" => "DESC"));

        $all_signatures = $this->getDoctrine()->getRepository(CVSignatures::class)->search("list",array("record" => $record, "have_json" => 1,"not_this" => $signature->getId()));

        if(!$signature || $signature->getSigned() || (!$signature->getVersion() && $signature->getAction()->getId()!=12)){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'El registro no se puede firmar'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        if($signature->getUser()!=$user){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'Usted no puede firmar esta evidencia'
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

        if(!$request->get('justification') && ($signature->getJustification() || $signature->getAction()->getJustification() || ($record->getReconciliation() && count($record->getCvSignatures())==1))){
            $this->get('session')->getFlashBag()->add(
                'error',
                "El registro no se pudo firmar porque era necesaria una justificación"
            );
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }

        $users_actions=$this->get('utilities')->get_users_actions($user,1);

        $can_sign = $this->getDoctrine()->getRepository(CVRecords::class)->search("count",array("id" => $record->getId(),"pending_for_me" => 1,"users" => $users_actions));

        if($can_sign==0){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No puede abrir esta plantilla debido al workflow definido'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $wf=$this->get('utilities')->wich_wf($record,$user,1);

        if(!$wf && $signature->getAction()->getId()!=18){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No puede abrir esta plantilla debido al workflow definido'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        if($request->get('justification')){
            $signature->setDescription($request->get('justification'));
        }

        

        //Si estamos modificando una plantilla archivada guardamos en un json auxiliar porque se trata de una solicitud
        if($signature->getAction()->getId()==18){
            $signature->setJsonAux(str_replace("gsk_id_firm", $signature->getNumberSignature(), $signature->getJsonAux()));
            $specific = $em->getRepository(SpecificGroups::class)->findOneBy(array("name" => "ECO"));
            $other_group = $specific->getGroup();
            $record->setUserGxP($user);
            $typesw = $em->getRepository(CVSecondWorkflowStates::class)->findOneBy(array("id" => "1"));

            $sworkflow = new CVSecondWorkflow();
            $sworkflow->setRecord($record);
            $sworkflow->setGroup($other_group);
            $sworkflow->setNumberSignature(1);
            $sworkflow->setType($typesw);
            $sworkflow->setSigned(FALSE);
            $em->persist($sworkflow);

            $aux_users = $em->getRepository(GroupUsers::class)->findBy(["group" => $other_group]);
            foreach ($aux_users as $aux_user) {
                $subject="Modificaciones GxP";
                $mensaje='Se ha realizado una modificación GxP sobre el registro '.$record->getId().' - Código: '.$record->getTemplate()->getNumber().' - Título: '.$record->getTemplate()->getName().' - Edición: '.$record->getTemplate()->getNumEdition().' y está pendiente de aprobación por su parte. Para poder aprobarlo puede acceder a la sección "Modificaciones GxP", buscar el documento y pulsar en  "Aprobar modificación GxP"';
                $baseURL=$this->container->get('router')->generate('nononsense_cv_search',array(),true)."?gxp=1&id=".$record->getId();
                $this->get('utilities')->sendNotification($aux_user->getUser()->getEmail(), $baseURL, "", "", $subject, $mensaje);
            }
        }
        else{
            $signature->setJson(str_replace("gsk_id_firm", $signature->getNumberSignature(), $signature->getJson()));

            //Grabamos valores con etiqueta info para la busqueda por esta etiqueta
            if($record->getJson()){
                $json_record=json_decode($record->getJson(),TRUE);
                $json_info=array();
                $array_signature=json_decode($signature->getJson(),TRUE);
                foreach($array_signature["data"] as $key => $values){
                    if (array_key_exists($key,$json_record["configuration"]["variables"]) && $json_record["configuration"]["variables"][$key]["info"]!="" && $json_record["configuration"]["variables"][$key]["info"]!=$key){
                        $json_info["data"][$json_record["configuration"]["variables"][$key]["info"]]=$values;
                    }
                }
                if(!empty($json_info)){
                    $signature->setJsonInfo(json_encode($json_info, JSON_FORCE_OBJECT));
                }
            }
        }
        $signature->setSigned(TRUE);
        $signature->setSignDate(new \DateTime());
        
        $record->setModified(new \DateTime());
        $record->setPending(FALSE);

        if($signature->getAction()->getFinishUser()){
            $signature->setFinish(TRUE);
        }
        else{
            if(!$signature->getFinish()){
                $signature->setFinish(FALSE);
            }
        }

        if($wf){
            $wf->setSigned($signature->getFinish());
            $em->persist($wf);
        }
        
        $em->persist($signature);

        //Si no se trata de una modificación/reapertura de registro en estado final
        if($signature->getAction()->getId()!=18){
            //Miramos si termina el workflow por que no quedan wf de su mismo tipo de acción pendientes
            $finish_workflow=1;
            $exist_wfs=$this->getDoctrine()->getRepository(CVWorkflow::class)->findBy(array('record' => $record,"signed" => FALSE));
            foreach($exist_wfs as $exist_wf){
                if($exist_wf->getType()==$wf->getType() && $exist_wf!=$wf){
                    $finish_workflow=0;
                }
            }

            if($signature->getAction()->getFinishWorkflow() || $finish_workflow){
                //Capturamos el estado correspondiente a la última acción que se firma
                $next_state=$signature->getAction()->getNextState();

                //Caso excepcional, miramos si es un inicio de cumplimentación, si finaliza su cumplimentación y si tiene que saltar el workflow
                if($signature->getAction()->getId()==15 && $signature->getFinish()){
                    $next_state=$this->getDoctrine()->getRepository(CVStates::class)->findOneBy(array('id' => 4));
                }
                if($record->getState()!=$next_state){
                    //Si hay una firma de devolución en un workflow activo, tiene prioridad esta a la hora de setear el próximo estado
                    if($signature->getAction()->getId()!=9){ //Si no es un envió a cancelación desde verificación
                        $action=$this->getDoctrine()->getRepository(CVActions::class)->findOneBy(array('id' => 6));
                        $exist_return=$this->getDoctrine()->getRepository(CVSignatures::class)->findBy(array('record' => $record,"signed" => TRUE,"finish" => TRUE,'action' => $action),array("id" => "DESC"));
                        if(count($exist_return)>0 && $record->getState()->getType()==$exist_return[0]->getAction()->getType()){
                            $next_state=$exist_return[0]->getAction()->getNextState();
                        }
                    }

                    //Vaciamos próximo workflow activo
                    $find_next=0;
                    $clean_wfs=$this->getDoctrine()->getRepository(CVWorkflow::class)->findBy(array('record' => $record));
                    foreach($clean_wfs as $clean_wf){
                        if($clean_wf->getType()->getTmType()==$next_state->getType()){
                            $find_next=1;
                            $clean_wf->setSigned(FALSE);
                            $em->persist($clean_wf);
                        }
                    }
                    
                    //Sacamos los emails de los usuarios a los que tenemos que notificar de que tienen una verificación pendiente
                    if($next_state->getType() && $next_state->getType()->getId()==2){
                        $emails_wfs=$this->getDoctrine()->getRepository(CVWorkflow::class)->findBy(array('record' => $record));
                        foreach($emails_wfs as $email_wfs){
                            if($email_wfs->getType()->getTmType()==$next_state->getType()){
                                $send_email=1;
                                if($email_wfs->getUser()){
                                    $emails[]=$email_wfs->getUser()->getEmail();
                                }
                                else{
                                    $aux_users = $em->getRepository(GroupUsers::class)->findBy(["group" => $email_wfs->getGroup()]);
                                    foreach ($aux_users as $aux_user) {
                                        $emails[]=$aux_user->getUser()->getEmail();
                                    }
                                }
                            }
                        }
                    }

                    //Sacamos los emails de los usuarios a los que tenemos que notificar de que tienen una cumplimentación pendiente por haber sido rechazada la cancelación en verificación
                    if($next_state->getType() && $next_state->getType()->getId()==1 && $signature->getAction()->getId()==2){
                        $emails_wfs=$this->getDoctrine()->getRepository(CVWorkflow::class)->findBy(array('record' => $record));
                        foreach($emails_wfs as $email_wfs){
                            if($email_wfs->getType()->getTmType()==$next_state->getType()){
                                $send_email=2;
                                if($email_wfs->getUser()){
                                    $emails[]=$email_wfs->getUser()->getEmail();
                                }
                                else{
                                    $aux_users = $em->getRepository(GroupUsers::class)->findBy(["group" => $email_wfs->getGroup()]);
                                    foreach ($aux_users as $aux_user) {
                                        $emails[]=$aux_user->getUser()->getEmail();
                                    }
                                }
                            }
                        }
                    }

                    //Vaciamos próximo bloque de firmas activo
                    $other_signatures=$this->getDoctrine()->getRepository(CVSignatures::class)->findBy(array('record' => $record,"signed" => TRUE,"finish" => TRUE));
                    foreach($other_signatures as $other_signature){
                        if($other_signature->getAction()->getType()==$next_state->getType()){
                            $other_signature->setFinish(FALSE);
                            $em->persist($other_signature);
                        }
                    }

                    //Si no hay workflow siguiente y no es un estado final, saltamos al siguiente
                    if(!$find_next && !$next_state->getFinal()){
                        $next_state=$next_state->getJumpState();
                        
                    }
                    
                    $record->setState($next_state);
                }
            }

            //Guardamos las modificaciones de un usuario sobre algo previamente cumplimentado
            $obj1 = json_decode($signature->getJson())->data;
            if(count($all_signatures)>0){
                $obj2 = json_decode($all_signatures[0]->getJson())->data;
                $obj3 = json_decode($signature->getRecord()->getJson())->configuration;

                //Compares new signature with old step and instert differences
                $this->get('utilities')->multi_obj_diff_counter = 0;
                $this->get('utilities')->multi_obj_diff($obj1, $obj2, $obj3, '$obj2->$key', '/^(in_|gsk_|dxo_|delete_)|(name|extension\b)/', false, $signature, false, null, 'new');

                //Compares old signature with new step and check removed fields
                $this->get('utilities')->multi_obj_diff_counter = 0;
                $this->get('utilities')->multi_obj_diff($obj2, $obj1, $obj3, '$obj2->$key', '/^(in_|gsk_|dxo_|delete_)|(name|extension\b)/', false, $signature, false, null, 'old');
            }
            else{
                $obj2 = json_decode($signature->getRecord()->getJson())->configuration;
                //Compares with default values
                $this->get('utilities')->multi_obj_diff($obj1, $obj2, NULL, '$obj2->variables->$field->value', '/^(in_|gsk_|dxo_|delete_)|(name|extension\b)/', false, $signature, false, null, 'new');
            }
        
            //Certificamos la cumplimentación e una plantilla por pasar por un estado final
            if($record->getState()->getFinal()){
                $request->attributes->set("pdf", '1');
                $request->attributes->set("no-redirect", true);
                $slug="record";
                switch($record->getState()->getId()){
                    case 3: $slug.="-cancel-edition";break;
                    case 6: $slug.="-cancel-verification";break;
                    case 7: $slug.="-archive";
                            if($record->getReconciliation()){
                                $slug.="-reconciliation";
                            }
                        break;
                }
                $em->persist($record);
                $em->flush();
                $template = $record->getTemplate();
                if($template->getIsReactive())
                {
                    $this->makeReactivosActions($signature);
                }
                $file = Utils::api3($this->forward('NononsenseHomeBundle:CVDocoaro:link', ['request' => $request, 'id'  => $record->getId()])->getContent());
                $file = Utils::saveFile($file, $slug, $this->getParameter('crt.root_dir'));
                Utils::setCertification($this->container, $file, $slug, $record->getId());                
            }



            if($send_email){
                switch($send_email){
                    case 1:
                        $subject="Cumplimentación pendiente de verificación";
                        $mensaje='El registro con ID '.$record->getId().' - Código: '.$record->getTemplate()->getNumber().' - Título: '.$record->getTemplate()->getName().' - Edición: '.$record->getTemplate()->getNumEdition().' está pendiente de verificación por su parte. Para poder verificarlo puede acceder a la sección "Buscador" o "En proceso", buscar el documento y pulsar en Verificar';
                        $baseURL=$this->container->get('router')->generate('nononsense_cv_search',array(),true)."?id=".$record->getId();
                        break;
                    case 2:
                        $subject="Cancelación rechazada en verificación";
                        $mensaje='La cancelación del registro con ID '.$record->getId().' - Código: '.$record->getTemplate()->getNumber().' - Título: '.$record->getTemplate()->getName().' - Edición: '.$record->getTemplate()->getNumEdition().' ha sido rechazada en verificación. Para poder continuar con la cumplimentación puede acceder a la sección "Buscador" o "En proceso", buscar el documento y pulsar en Cumplimentar';
                        $baseURL=$this->container->get('router')->generate('nononsense_cv_search',array(),true)."?id=".$record->getId();
                        break;
                }

                foreach($emails as $email){
                    $this->get('utilities')->sendNotification($email, $baseURL, "", "", $subject, $mensaje);
                }
                
            }

        }

        $em->persist($record);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            "El documento se ha firmado correctamente"
        );
        return $this->redirect($this->container->get('router')->generate('nononsense_cv_search'));
    }

    //Listado de cumplimentaciones
    public function listAction(Request $request){

        $user = $this->container->get('security.context')->getToken()->getUser();
        $fll=false;
        foreach ($user->getGroups() as $groupMe) {
            $type = $groupMe->getGroup()->getTipo();
            if ($type == 'FLL') {
                $fll = true;
            }
        }

        $array_item["fll"]=$fll;

        $filters=Array();
        $filters2=Array();
        $types=array();

        $filters=array_filter($request->query->all());
        $filters2=array_filter($request->query->all());

        $type_delegation=1;
        if(isset($filters["gxp"])){
            $type_delegation=2;
        }
        if(isset($filters["blocked"])){
            //$type_delegation=3;
        }

        $desc_pdf="Listado de registros";

        if(isset($filters["pending_for_me"]) && isset($filters["gxp"])){
            unset($filters["pending_for_me"]);
            unset($filters2["pending_for_me"]);
            $filters["pending_gxp"]=1;
            $filters2["pending_gxp"]=1;
        }

        if(isset($filters["pending_for_me"]) && isset($filters["blocked"])){
            unset($filters["pending_for_me"]);
            unset($filters2["pending_for_me"]);
            $filters["pending_blocked"]=1;
            $filters2["pending_blocked"]=1;
        }

        if(isset($filters["gxp"])){
            $desc_pdf="Solicitudes GxP";
        }

        if(isset($filters["blocked"])){
            $desc_pdf="Registros en revisión";
        }

        $users_actions=$this->get('utilities')->get_users_actions($user,$type_delegation);
        $filters["users"]=$users_actions;
        $filters2["users"]=$users_actions;

        $filters["fll"]=$fll;
        $filters2["fll"]=$fll;

        $array_item["suser"]["id"]=$user->getId();

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
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


        $array_item["suser"]["id"]=$user->getId();
        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(CVRecords::class)->search("list",$filters);
        $array_item["states"]= $this->getDoctrine()->getRepository(CVStates::class)->findAll();
        $array_item["areas"] = $this->getDoctrine()->getRepository(Areas::class)->findBy(array(),array("name" => "ASC"));
        
        $array_item["count"] = $this->getDoctrine()->getRepository(CVRecords::class)->search("count",$filters2);

        $url=$this->container->get('router')->generate('nononsense_cv_search');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        if(isset($filters["pending_blocked"]) || isset($filters["pending_gxp"])){
             $array_item["filters"]["pending_for_me"]=1;
        }

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
            return $this->render('NononsenseHomeBundle:CV:search.html.twig',$array_item);
        }
        else{
            //Exportamos a Excel

            if($request->get("export_excel")){
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                $phpExcelObject->getProperties();
                $phpExcelObject->setActiveSheetIndex(0)
                 ->setCellValue('A1', $desc_pdf." - ".$user->getUsername()." - ".date("d/m/Y H:i:s"));
                $phpExcelObject->setActiveSheetIndex()
                 ->setCellValue('A2', 'Nº')
                 ->setCellValue('B2', 'Nombre')
                 ->setCellValue('C2', 'Nombre')
                 ->setCellValue('D2', 'Iniciado por')
                 ->setCellValue('E2', 'Fecha inicio')
                 ->setCellValue('F2', 'Ultima modificación')
                 ->setCellValue('G2', 'Estado');
            }

            if($request->get("export_pdf")){
                $html='<html><body style="font-size:8px;width:100%"><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%">
                        <th style="font-size:8px;width:6%">Nº</th>
                        <th style="font-size:8px;width:45%">Nombre</th>
                        <th style="font-size:8px;width:9%">Area</th>
                        <th style="font-size:8px;width:10%">Iniciado por</th>
                        <th style="font-size:8px;width:10%">F. inicio</th>
                        <th style="font-size:8px;width:10%">F. modific.</th>
                        <th style="font-size:8px;width:10%">Estado</th>
                    </tr>';
            }

            $i=3;
            foreach($array_item["items"] as $item){

                if($request->get("export_excel")){
                    $phpExcelObject->getActiveSheet()
                    ->setCellValue('A'.$i, $item["id"])
                    ->setCellValue('B'.$i, $item["name"])
                    ->setCellValue('C'.$i, $item["area"])
                    ->setCellValue('D'.$i, $item["creator"])
                    ->setCellValue('E'.$i, ($item["created"]) ? $item["created"]->format('d/m/Y H:i:s') : '')
                    ->setCellValue('F'.$i, ($item["modified"]) ? $item["modified"]->format('d/m/Y H:i:s') : '')
                    ->setCellValue('G'.$i, $item["state"]);
                }

                if($request->get("export_pdf")){
                    $html.='<tr style="font-size:8px">
                        <td>'.$item["id"].'</td>
                        <td>'.$item["name"].'</td>
                        <td>'.$item["area"].'</td>
                        <td>'.$item["creator"].'</td>
                        <td>'.(($item["created"]) ? $item["created"]->format('d/m/Y H:i:s') : '').'</td>
                        <td>'.(($item["modified"]) ? $item["modified"]->format('d/m/Y H:i:s') : '').'</td>
                        <td>'.$item["state"].'</td>
                    </tr>';
                }

                $i++;
            }

            if($request->get("export_excel")){
                $phpExcelObject->getActiveSheet()->setTitle('Listado de registros');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                  ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                  'list_records.xlsx'
                );
                $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'maxage=1');
                $response->headers->set('Content-Disposition', $dispositionHeader);

                return $response; 
            }

            if($request->get("export_pdf")){
                $html.='</table></body></html>';
                $this->get('utilities')->returnPDFResponseFromHTML($html,$desc_pdf);
            }
        }
    }

    public function listContentAction(Request $request){
        $filters = array_filter($request->query->all());

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
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

        $url=$this->container->get('router')->generate('nononsense_cv_search');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }

        
        $histories = $this->getDoctrine()->getRepository(CVRecordsHistory::class)->list("list",$filters);
        $count = $this->getDoctrine()->getRepository(CVRecordsHistory::class)->list("count",$filters);
        $pagination=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$count,"/", $parameters);

        return $this->render('NononsenseHomeBundle:CV:search_contain.html.twig', ['histories' => $histories, 'filters' => $filters, 'pagination' => $pagination, "count" => $count]);
    }

    public function downloadBase64Action(Request $request, $id){

        $hisotry = $this->getDoctrine()->getRepository(CVRecordsHistory::class)->findOneBy(['id' => $id]);

        $value = $hisotry->getValue();

        if ($request->get('type') !== null && $request->get('type') == 'prev') {
            $value = $hisotry->getPrevValue();
        }

        $file = file_get_contents($value);
        $mime = mime_content_type($value);

        $extension = preg_split('#(/|;)#', $hisotry->getValue())[1];
        $filename = $hisotry->getField().'.'.$extension;

        header('Content-Description: File Transfer');
        header('Content-Type: '.$mime);
        $type="attachments";
        if($request->get("serve")){
            $type=$request->get("serve");
        }
        header('Content-Disposition: '.$type.'; filename="'.$filename.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($file));
        echo $file;
        exit;
    }

    /**
     * @param CVSignatures $signature
     * @return void
     */
    private function makeReactivosActions(CVSignatures $signature)
    {
        $this->forward('NononsenseHomeBundle:ProductsDissolution:saveReactivoUse', ['signature'  => $signature]);
    }
}