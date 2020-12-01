<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 03/04/2018
 * Time: 19:44
 */

namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Nononsense\HomeBundle\Entity\EvidenciasStep;
use Nononsense\HomeBundle\Entity\FirmasStep;
use Nononsense\HomeBundle\Entity\InstanciasSteps;
use Nononsense\HomeBundle\Entity\InstanciasWorkflows;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\GroupBundle\Entity\GroupUsers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Nononsense\UtilsBundle\Classes\Utils;

class BackOfficeController extends Controller
{
    public function indexAction()
    {
        /*
         * Cambiar los permisos para que sea por grupo admin
         */
        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($user->getId() != 1) {
            exit;
        }


        echo "hola admin Gus!";
        exit;
    }

    public function standByDocumentsListAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $grupos = array('FLL', 'ECO');

        if (!$this->_grantUser($user, $grupos)) {

            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos para entrar aquí'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $status = array();
        foreach ($user->getGroups() as $groupMe) {
            $type = $groupMe->getGroup()->getTipo();

            if ($type == 'FLL') {
                $status[] = 11;
            }
            if ($type == 'ECO') {
                $status[] = 17;
            }
        }

        $filters['page']        = (!$request->get('page')) ? 1 : $request->get('page');
        $filters['status']      = $status;
        $limit                  = 15;

        $documentsProcess = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->listStandBy($filters, $limit);

        $documents = $documentsProcess->getIterator();

        foreach ($documents as &$element2) {
            $idRegistro = $element2['id'];

            $step = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                ->findOneBy(array("workflow_id" => $idRegistro, "dependsOn" => 0));

            $element2['idcumplimentacion'] = $step->getId();


            $reconciliacionElement = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
                ->findOneBy(array("registro_nuevo_id" => $idRegistro));

            if (isset($reconciliacionElement)) {
                $registroReconciliadoId = $reconciliacionElement->getRegistroViejoId();
                /*
                                $registroReconciliado = $this->getDoctrine()
                                    ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                                    ->find($registroReconciliadoId);
                  */
                $registroReconciliado = $reconciliacionElement->getRegistroViejoEntity();

                $element2['reconciliacion'] = $registroReconciliado->getId();

            } else {
                $element2['reconciliacion'] = 'NO';
            }

        }

        $params    = $request->query->all();
        unset($params["page"]);
        $parameters = (!empty($params)) ? true : false;

        $pagination = Utils::paginador($limit, $request, false, $documentsProcess->count(), "/", $parameters);

        return $this->render('NononsenseHomeBundle:Backoffice:stand_by_documents_list.html.twig', array(
            "documentsProcess" => $documents, 'pagination' => $pagination
        ));
    }

    public function standByDocumentAction($idRegistro)
    {
        /*
    FLL
    */

        $user = $this->container->get('security.context')->getToken()->getUser();
        $grupos = array('FLL');

        if (!$this->_grantUser($user, $grupos)) {

            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos para entrar aquí'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }
        $documents = array();

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->findOneBy(array("id" => $idRegistro, "status" => 11));

        if (!$registro) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Este registro ha sido gestionado por otro usuario de FLL'
            );
            $route = $this->container->get('router')->generate('nononsense_backoffice_standby_documents_list');
            return $this->redirect($route);
        }

        $subcat = $registro->getMasterWorkflowEntity()->getCategory()->getName();
        $name = $registro->getMasterWorkflowEntity()->getName();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registro->getId(), "dependsOn" => 0));

        $element = array(
            "id" => $step->getId(),
            "subcat" => $subcat,
            "name" => $name,
            "status" => $registro->getStatus(),
            "fecha" => $registro->getModified(),
        );

        $documents[] = $element;


        return $this->render('NononsenseHomeBundle:Backoffice:stand_by_document.html.twig', array(
            "documents" => $documents,
            "idregistro" => $idRegistro));
    }

    public function standByDocumentECOAction($idRegistro)
    {

        $user = $this->container->get('security.context')->getToken()->getUser();
        $grupos = array('ECO');

        if (!$this->_grantUser($user, $grupos)) {

            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos para entrar aquí'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $documents = array();

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->findOneBy(array("id" => $idRegistro, "status" => 17));
        
        if (!$registro) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Este registro ha sido gestionado por otro usuario de ECO'
            );
            $route = $this->container->get('router')->generate('nononsense_backoffice_standby_documents_list');
            return $this->redirect($route);
        }

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $idRegistro, "dependsOn" => 0));

        $subcat = $registro->getMasterWorkflowEntity()->getCategory()->getName();
        $name = $registro->getMasterWorkflowEntity()->getName();

        /*
         * Habría que obtener el comentario y el nombre del FLL que lo ha autorizado
         */


        $element = array(
            "id" => $step->getId(),
            "subcat" => $subcat,
            "name" => $name,
            "status" => $registro->getStatus(),
            "fecha" => $registro->getModified()
        );

        $documents[] = $element;

        $nombreFLL = '';
        $comentarioFLL = '';

        $firmas = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findBy(array("step_id" => $step->getId()));

        $counter = count($firmas);

        foreach ($firmas as $firma) {
            $number = $firma->getNumber();
            if ($counter == $number) {
                $comentarioFLL = $firma->getAccion();
                $nombreFLL = $firma->getUserEntiy()->getName();
            }
        }


        return $this->render('NononsenseHomeBundle:Backoffice:stand_by_document_ECO.html.twig', array(
            "documents" => $documents,
            "idregistro" => $idRegistro,
            "comentarioFLL" => $comentarioFLL,
            "nombreFLL" => $nombreFLL));
    }

    public function standByDocumentFLLAccionAction($idRegistro, Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $grupos = array('FLL');

        if (!$this->_grantUser($user, $grupos)) {

            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos para entrar aquí'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->findOneBy(array("id" => $idRegistro, "status" => 11));
        
        if (!$registro) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Este registro ha sido gestionado por otro usuario de FLL'
            );
            $route = $this->container->get('router')->generate('nononsense_backoffice_standby_documents_list');
            return $this->redirect($route);
        }

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $idRegistro, "dependsOn" => 0));
        /*
       * Guardar firma
       */

        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        /*
      * Guardar evidencia un paso "mas" auxiliar
      */
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(0);
        $evidencia->setUserEntiy($user);
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());


        $firmaImagen = $request->get('firma');
        $comentario = $request->get('comment');
        $accion = $request->get('accion');

        $registro->setInEdition(0);

        if ($accion == 'autorizar') {
            $descp = "Registro en StandBy Liberado por FLL: " . $comentario;
            $registro->setStatus(0);


        } else if ($accion == 'cancelar') {
            $descp = "Registro en StandBy Cancelado por FLL: " . $comentario;
            $registro->setStatus(8);

        } else {
            $descp = "Enviada petición a ECO sobre registro en StandBy: " . $comentario;
            $registro->setStatus(17);

            $groups = $em->getRepository(Groups::class)->findBy(["tipo" => "ECO"]);
            foreach($groups as $group){
                $aux_users = $em->getRepository(GroupUsers::class)->findBy(["group" => $group]);
                foreach ($aux_users as $aux_user) {
                    $emails[]=$aux_user->getUser()->getEmail();
                }
            }

            $unique_emails = array_unique($emails);

            foreach($unique_emails as $email){
                $subject="Registros bloqueados";
                $mensaje='El registro '.$step->getId().' ha sido derivado a usted (o cualquier otro miembro del grupo ECO) por parte de un FLL y requiere de su gestión.<br>Justificación: '.$comentario ;
                $baseURL=$this->container->get('router')->generate('nononsense_backoffice_standby_document_eco',array("idRegistro" => $idRegistro),TRUE);
                
                $this->get('utilities')->sendNotification($email, $baseURL, "", "", $subject, $mensaje);
            }
        }


        /*
         * Guardar firma
         */
        $firmas = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findBy(array("step_id" => $step->getId()));

        $counter = count($firmas) + 1;

        $firma = new FirmasStep();
        $firma->setAccion($descp);
        $firma->setStepEntity($step);
        $firma->setUserEntiy($user);
        $firma->setFirma($firmaImagen);
        $firma->setStatus(1);
        $firma->setElaboracion(0);
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);


        $em->persist($evidencia);
        $em->persist($registro);
        $em->persist($firma);
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_backoffice_standby_documents_list');
        return $this->redirect($route);
    }

    public function standByDocumentECOAccionAction($idRegistro, Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $grupos = array('ECO');

        if (!$this->_grantUser($user, $grupos)) {

            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos para entrar aquí'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->findOneBy(array("id" => $idRegistro, "status" => 17));
        
        if (!$registro) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Este registro ha sido gestionado por otro usuario de ECO'
            );
            $route = $this->container->get('router')->generate('nononsense_backoffice_standby_documents_list');
            return $this->redirect($route);
        }


        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $idRegistro, "dependsOn" => 0));
        /*
       * Guardar firma
       */

        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        /*
      * Guardar evidencia un paso "mas" auxiliar
      */
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step);
        $evidencia->setStatus(0);
        $evidencia->setUserEntiy($user);
        $evidencia->setToken($step->getToken());
        $evidencia->setStepDataValue($step->getStepDataValue());

        $firmaImagen = $request->get('firma');
        $comentario = $request->get('comment');
        $accion = $request->get('accion');

        if ($accion == 'autorizar') {
            $descp = "Registro en StandBy Liberado por ECO: " . $comentario;
            $registro->setStatus(0);


        } else if ($accion == 'cancelar') {
            $descp = "Registro en StandBy Cancelado por ECO: " . $comentario;
            $registro->setStatus(8);

        }

        /*
         * Guardar firma
         */
        $firmas = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findBy(array("step_id" => $step->getId()));

        $counter = count($firmas) + 1;

        $firma = new FirmasStep();
        $firma->setAccion($descp);
        $firma->setStepEntity($step);
        $firma->setUserEntiy($user);
        $firma->setFirma($firmaImagen);
        $firma->setStatus(1);
        $firma->setElaboracion(0);
        $firma->setNumber($counter);

        $evidencia->setFirmaEntity($firma);


        $em->persist($evidencia);
        $em->persist($registro);
        $em->persist($firma);
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_backoffice_standby_documents_list');
        return $this->redirect($route);
    }

    private function _grantUser($user, $arrayGrupos)
    {
        //Detectar si el type del grupo está en array grupos
        $autorizado = false;

        foreach ($user->getGroups() as $groupMe) {
            $type = $groupMe->getGroup()->getTipo();
            if (in_array($type, $arrayGrupos)) {
                $autorizado = true;
            }
        }
        return $autorizado;
    }

    public function bloquearRegistroAction(){
        /*
         * Bloquear aquellos registros que estén en edition desde hace +10 horas.
         */
        $em = $this->getDoctrine()->getManager();

        $posiblesRegistrosArray = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->findBy(array("in_edition"=>1));

        $now = new \DateTime();
        $now->modify("-8 hour"); // Intervalo

        $block_records=array();

        foreach ($posiblesRegistrosArray as $registro){
            $fechaModificacion = $registro->getModified();
            if($fechaModificacion < $now){
                $step = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                ->findOneBy(array("workflow_id" => $registro->getId(), "dependsOn" => 0));

                $block_records[]=$step->getId();

                $registro->setInEdition(0);
                $registro->setStatus(11);

                $em->persist($registro);
            }

        }

        if (!empty($block_records)) {

            $groups = $em->getRepository(Groups::class)->findBy(["tipo" => "FLL"]);
            foreach($groups as $group){
                $aux_users = $em->getRepository(GroupUsers::class)->findBy(["group" => $group]);
                foreach ($aux_users as $aux_user) {
                    $emails[]=$aux_user->getUser()->getEmail();
                }
            }

            $unique_emails = array_unique($emails);
            $log_records_stand_by = implode("<br>", $block_records);

            foreach($unique_emails as $email){
                $subject="Registros bloqueados";
                $mensaje='Los siguientes registros han sido bloqueados y necesitan ser gestionados por su parte o algún otro FLL. Acceda al siguiente  Link para gestionar los bloqueos.<br><br>'.$log_records_stand_by;
                $baseURL=$this->container->get('router')->generate('nononsense_backoffice_standby_documents_list',array(),TRUE);
                
                $this->get('utilities')->sendNotification($email, $baseURL, "", "", $subject, $mensaje);
            }
        }

        $em->flush();

        $responseAction = new Response();
        $responseAction->setStatusCode(200);
        $responseAction->setContent("OK");
        return $responseAction;
    }
}