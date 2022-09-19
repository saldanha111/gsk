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
use Nononsense\HomeBundle\Entity\SpecificGroups;
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

class CVStandByController extends Controller
{
    //Visualizamos el registro bloqueado
    public function viewAction(Request $request, int $id)
    {   
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');
        $array=array();

        $user = $this->container->get('security.context')->getToken()->getUser();
        $users_actions=$this->get('utilities')->get_users_actions($user,3);

        $items=$this->getDoctrine()->getRepository(CVRecords::class)->search("list",array("id" => $id,"blocked" => 1,"pending_blocked" => 1,"users" => $users_actions));

        if(!$items){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No se puede desbloquear este registro'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $record = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $items[0]["id"]));

        $wf=$this->get('utilities')->wich_second_wf($record,$user,2);
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
        $array["groups"] = $em->getRepository(Groups::class)->findBy(array(),array("name" => "ASC"));
        $array["signature_request"] = $em->getRepository(CVSignatures::class)->findOneBy(array("record" => $items[0]["id"],"action" => 18),array("id" => "DESC"));
        $array["secondWf"] = $em->getRepository(CVSecondWorkflow::class)->findBy(array("record" => $items[0]["id"]),array("id" => "ASC"));
        $array["currentWf"] = $wf;
        $specific = $em->getRepository(SpecificGroups::class)->findOneBy(array("name" => "ECO"));
        $array["eco"]=$specific->getGroup();
        
        return $this->render('NononsenseHomeBundle:CV:view_standby.html.twig',$array);
    }

    //Aprobamos o rechazamos la modificación GxP
    public function saveAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');
        $array=array();
        $error=0;

        $user = $this->container->get('security.context')->getToken()->getUser();
        $users_actions=$this->get('utilities')->get_users_actions($user,3);

        $items=$this->getDoctrine()->getRepository(CVRecords::class)->search("list",array("id" => $id,"blocked" => 1,"pending_blocked" => 1,"users" => $users_actions));

        if(!$items){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No se puede desbloquear este registro'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $record = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $items[0]["id"]));

        $wf=$this->get('utilities')->wich_second_wf($record,$user,2);
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
                "No se ha detectado ninguna acción sobre el desbloqueo del registro en Stand By"
            );
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }


        if(!$request->get('observations')){
            $this->get('session')->getFlashBag()->add(
                'error',
                "El campo observaciones es obligatorio"
            );
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }

        $specific = $em->getRepository(SpecificGroups::class)->findOneBy(array("name" => "ECO"));
        $eco=$specific->getGroup();
        
        if($wf->getGroup() && $wf->getGroup()==$eco){
            switch($request->get('action')){
                case 1: $id_action=28;break;
                case 2: $id_action=30;break;
            }
        }
        else{
            switch($request->get('action')){
                case 1: $id_action=28;break;
                case 2: $id_action=30;break;
                case 3: $id_action=29;break;
            }
        }

        if(!$id_action){
            $this->get('session')->getFlashBag()->add(
                'error',
                "No se ha detectado ninguna acción sobre el desbloqueo del registro en Stand By"
            );
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }

        $action=$em->getRepository(CVActions::class)->findOneBy(array("id" => $id_action));
        $typesw = $em->getRepository(CVSecondWorkflowStates::class)->findOneBy(array("id" => "2"));


        $wf->setSigned(TRUE);
        $wf->setUser($user);
        $em->persist($wf);

        $all_signatures = $this->getDoctrine()->getRepository(CVSignatures::class)->findBy(array("record" => $record)); 
        $last_signature = $this->getDoctrine()->getRepository(CVSignatures::class)->findOneBy(array("record" => $record),array("id" => "DESC"));

        $signature = new CVSignatures();
        $signature->setUser($user);
        $signature->setRecord($record);
        $signature->setNumberSignature((count($all_signatures)+1));
        $signature->setJustification(TRUE);
        $signature->setAction($action);
        $signature->setSigned(TRUE);
        $signature->setCreated(new \DateTime());
        $signature->setModified(new \DateTime());
        $signature->setSignDate(new \DateTime());
        $signature->setJson($last_signature->getJson());
        $signature->setJsonInfo($last_signature->getJsonInfo());
        $signature->setVersion($last_signature->getVersion());
        $signature->setConfiguration($last_signature->getConfiguration());
        $signature->setFinish(TRUE);
        $signature->setDescription($request->get('observations'));

        $em->persist($signature);
        

        if($id_action==28 || $id_action==30){
            $record->setBlocked(FALSE);
            $record->setInEdition(FALSE);

            foreach($record->getCvSecondWorkflows() as $item_wf){
                $em->remove($item_wf);
            }

            $em->persist($record);
        }

        if($id_action==28){
            $subject="Registro desbloqueado";
            $mensaje='Se ha aprobado el desbloqueo del registro '.$record->getId().' - Código: '.$record->getTemplate()->getNumber().' - Título: '.$record->getTemplate()->getName().' - Edición: '.$record->getTemplate()->getNumEdition().'. Para poder continuar con su trabajo, acceda a la sección "Buscador" o "En proceso" y busca el documento o bien puede pinchar en el siguiente link"';
            $baseURL=$this->container->get('router')->generate('nononsense_cv_search',array(),true)."?id=".$record->getId();
            $this->get('utilities')->sendNotification($record->getOpenedBy()->getEmail(), $baseURL, "", "", $subject, $mensaje);
        }

        if($id_action==29){
            $subject="Se solicita confirmación de ECO para el desbloqueo";
            $mensaje='Se ha solicitado por parte del FLL la confirmación para el desbloqueo del registro '.$record->getId().' - Código: '.$record->getTemplate()->getNumber().' - Título: '.$record->getTemplate()->getName().' - Edición: '.$record->getTemplate()->getNumEdition().'. Para proceder con el desbloqueo, acceda a la sección "Documentos en Stand By" y busque el documento o bien puede pinchar en el siguiente link"';
            $baseURL=$this->container->get('router')->generate('nononsense_request_view_standby', ["id" => $record->getId()],array(),true);
            $eco_users = $em->getRepository(GroupUsers::class)->findBy(["group" => $eco]);
            foreach ($eco_users as $eco_user) {
                $this->get('utilities')->sendNotification($eco_user->getUser()->getEmail(), $baseURL, "", "", $subject, $mensaje);
            }
            $record->setEcoNextOnStandBy(TRUE);
        }
        

        if($id_action==30){
            //Certificamos tras rechazar la reapertura del registro
            $request->attributes->set("pdf", '1');
            $request->attributes->set("no-redirect", true);
            $slug="record-cancel-standby";

            $record->setState($action->getNextState());
            $this->get('utilities')->checkModelNotification($record->getTemplate(),$action->getNextState());
            $em->persist($record);

            $em->flush();

            $file = Utils::api3($this->forward('NononsenseHomeBundle:CVDocoaro:link', ['request' => $request, 'id'  => $record->getId()])->getContent());
            $file = Utils::saveFile($file, $slug, $this->getParameter('crt.root_dir'));
            Utils::setCertification($this->container, $file, $slug, $record->getId()); 
        }

        switch($request->get('action')){
            case 1: $message_alert = "Se ha aprobado el desbloqueo de la plantilla correctamente";
                break;
            case 2: $message_alert = "Se ha rechazado el desbloqueo de la plantilla";
                break;
            case 3: $message_alert = "Se ha solicitado la confirmación por parte de ECO para el desbloqueo de la plantilla";
                break;
        }

        $this->get('session')->getFlashBag()->add(
            'success',
            $message_alert
        );

        $em->flush();
        $route = $this->container->get('router')->generate('nononsense_cv_search')."?blocked=1";
        
        return $this->redirect($route);
    }
}