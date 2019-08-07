<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 03/04/2018
 * Time: 19:44
 */

namespace Nononsense\HomeBundle\Controller;


use Nononsense\HomeBundle\Entity\InstanciasSteps;
use Nononsense\HomeBundle\Entity\InstanciasWorkflows;
use Nononsense\HomeBundle\Entity\MetaData;
use Nononsense\HomeBundle\Entity\ReconciliacionRegistro;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nononsense\HomeBundle\Form\Type as FormProveedor;

use Nononsense\UtilsBundle\Classes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NuevoRegistroController extends Controller
{

    public function pasounoAction(Request $request)
    {
        /*
         * Interfaz que muestra las opciones de búsqueda de los formularios
         */

        $search = $request->query->get('search');
        $categoriasSelect = $request->query->get('categoriasSelect');
        $subCategoriaSelect = $request->query->get('subcategoriaSelected');

        $categorias = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:Categories')
            ->findBy(array("padre" => 0));

        /*
         * En el futuro hacer por ajax
         */

        $allCategorias = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:Categories')
            ->findAll();

        /*
         * Modificar algoritmo para que permita ambos.
         */

        $templates = array();

        if (isset($categoriasSelect)) {
            /*
             * Existe un fitro, aunque sea vacía.
             * Si $categoriasSelect == 0. Filtro por todas y nombre, si existe
             * Si $categoriasSelect != 0. Pero $subCategoriaSelect == 0. Obtener todas las categorías hijo y hacer un "in". Filtro por nombre si existe.
             * Si $categoriasSelect != 0 y $subCategoriaSelect != 0. Hacer un "in" sólo con esa y filtrar por nombre si existe.
             */
            $categories = array();

            if ($categoriasSelect != 0) {
                if ($subCategoriaSelect == 0) {
                    // todas
                    $categoriasHijo = $this->getDoctrine()
                        ->getRepository('NononsenseHomeBundle:Categories')
                        ->findBy(array("padre" => $categoriasSelect));

                    foreach ($categoriasHijo as $hijo) {
                        $categories[] = $hijo->getId();

                    }

                    unset($categoriasHijo);

                } else {
                    $categories = array($subCategoriaSelect);
                }
            }

            $templates = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:MasterWorkflows')
                ->listDocumentosByNameAndCategory($search, $categories);
        } else {
            /*
            $templates = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:MasterWorkflows')
                ->listDocumentosByNameAndCategory("", array());
            */
        }

        $categoriaPadre = null;

        foreach ($templates as &$oneTemplate) {
            $padre = $oneTemplate['padre'];
            $encontrado = false;
            $it = 0;

            if ($categoriaPadre != null) {
                if ($categoriaPadre->getId() == $padre) {
                    $encontrado = true;
                }
            }

            while (!$encontrado && $it < sizeof($categorias)) {
                if ($categorias[$it]->getId() == $padre) {
                    $encontrado = true;
                    $categoriaPadre = $categorias[$it];
                }
                $it++;
            }

            $oneTemplate['category'] = $categoriaPadre->getName();
        }

        return $this->render('NononsenseHomeBundle:Contratos:nuevo_registro_paso_uno.html.twig', array(
            "templates" => $templates,
            "termino" => $search,
            "categorias" => $categorias,
            "todasCat" => $allCategorias,
        ));
    }

    public function createAction($templateid)
    {

        $MasterWorkflow = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:MasterWorkflows')
            ->find($templateid);

        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $instanciaWorkflow = new InstanciasWorkflows();
        $instanciaWorkflow->setIsActive(true);
        $instanciaWorkflow->setStatus(-2); // Estado especial de creación
        $instanciaWorkflow->setDescription("");
        $instanciaWorkflow->setMasterDataValues("");
        $instanciaWorkflow->setObservaciones("");
        $instanciaWorkflow->setYear(2019);

        $instanciaWorkflow->setUserCreatedEntiy($user);
        $instanciaWorkflow->setMasterWorkflowEntity($MasterWorkflow);
        $instanciaWorkflow->setSignvalues("");
        $instanciaWorkflow->setFiles("");

        $em->persist($instanciaWorkflow);
        $em->flush();

        $MasterSteps = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:MasterSteps')
            ->findBy(array("workflow_id" => $templateid));

        foreach ($MasterSteps as $stepM) {
            $instancias_step = new InstanciasSteps();
            $instancias_step->setInstanciaWorkflow($instanciaWorkflow);

            $instancias_step->setMasterStep($stepM);
            $instancias_step->setDependsOn(0);
            $instancias_step->setRules("");
            $instancias_step->setStatusId(0);
            $instancias_step->setUsageId(0);
            $instancias_step->setToken("");
            $instancias_step->setIsActive(1);
            $instancias_step->setStepDataValue("");
            $instancias_step->setAuxvalues("");

            $em->persist($instancias_step);
        }

        $em->flush();

        // asignar los depends on

        foreach ($MasterSteps as $stepM) {
            $instancias_stepMultiple = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                ->findBy(array(
                    'master_step_id' => $stepM->getId(),
                    'workflow_id' => $instanciaWorkflow->getId()));

            foreach ($instancias_stepMultiple as $instancias_step) {
                if ($stepM->getDependsOn() != 0) {

                    $Master_step_dependsOn = $this->getDoctrine()
                        ->getRepository('NononsenseHomeBundle:MasterSteps')
                        ->find($stepM->getDependsOn());

                    $instancias_step_dependON = $this->getDoctrine()
                        ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                        ->findOneBy(array(
                            'master_step_id' => $Master_step_dependsOn->getId(),
                            'workflow_id' => $instanciaWorkflow->getId()));

                    $instancias_step->setDependsOn($instancias_step_dependON->getId());

                } else {
                    $firststep = $instancias_step->getId();
                }
            }
        }


        $em->flush();
        /*
         * MetaData fecha y hora de creación
         */
        $metaData = new MetaData();
        $metaData->setInstanciaWorkflow($instanciaWorkflow);
        $now = new \DateTime();
        $now->modify("+2 hour"); // Ver tema de horarios usos
        $metaData->setFechainicio($now);

        $em->persist($metaData);

        $em->persist($instanciaWorkflow);
        $em->flush();

        $precreation = $MasterWorkflow->getPrecreation();
        switch ($precreation) {
            case "default":
                $instanciaWorkflow->setStatus(-1); // estado sin actividad
                $em->persist($instanciaWorkflow);
                $em->flush();
                $route = $this->container->get('router')->generate('nononsense_prevalidation_creation', array("registroid"=>$instanciaWorkflow->getId(),"stepid" => $firststep));
                break;
            case "codigolote":
                $route = $this->container->get('router')->generate('nononsense_precreation_codigo_lote_interface', array("registroid" => $instanciaWorkflow->getId()));
                break;
            case "equipoqr":
                $route = $this->container->get('router')->generate('nononsense_precreation_equipo_qr_interface', array("registroid" => $instanciaWorkflow->getId()));
                break;
            case "equipopesaqr":
                $route = $this->container->get('router')->generate('nononsense_precreation_equipo_pesa_qr_interface', array("registroid" => $instanciaWorkflow->getId()));
                break;
            default:
                $instanciaWorkflow->setStatus(-1); // estado sin actividad
                $em->persist($instanciaWorkflow);
                $em->flush();
                $route = $this->container->get('router')->generate('nononsense_prevalidation_creation', array("registroid"=>$instanciaWorkflow->getId(),"stepid" => $firststep));
                break;
        }


        return $this->redirect($route);
    }

    public function checkPreValidationRegistroAction($registroid,$stepid)
    {
        // Si todo OK, registro válido
        $em = $this->getDoctrine()->getManager();

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($registroid);

        /*
         * Realizar aquí las comprobaciones, si fuera erróneo no cambiar el status del registro y mostrar el mensaje de error:
         */

        $prevalidationcreate = $registro->getMasterWorkflowEntity()->getPrevalidation();
        $causa = "desconocida";
        $reconciliacion = false;
        $posibleReconciliado = false;
        $registroViejoReconciliado = 0;
        switch ($prevalidationcreate){
            case "intervencion":
                $metaData = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:MetaData')
                    ->findOneBy(array("workflow_id"=>$registroid));

                $equipo = $metaData->getEquipo();

                $bloqueo = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:BloqueoMasterWorkflow')
                    ->findOneBy(array("master_workflow_id"=>$registro->getMasterWorkflowEntity()->getId(), "status"=>0,"equipo"=>$equipo));

                if($bloqueo == null){
                    $valido = true;
                }else{
                    $valido = false;
                    $causa = "Recepción del equipo tras intervención pendiente de verificación, la plantilla esta bloqueada hasta que se verifique.";
                }

                break;
            case "codigoloteunico":
                /*
                 * Sólo puede haber un registro con ese par codigo / lote
                 */
                $metaData = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:MetaData')
                    ->findOneBy(array("workflow_id"=>$registroid));

                $lote = $metaData->getLote();
                $material = $metaData->getMaterial();

                $metaDataArray = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:MetaData')
                    ->findBy(array("lote"=>$lote,"material"=>$material));

                $it = 0;
                $registroViejoReconciliacionId = 0;
                foreach ($metaDataArray as $metaData){
                    $registro = $metaData->getInstanciaWorkflow();
                    //echo $registro->getId();

                    if($registro->getStatus() < 20 && $registro->getStatus() >= 0 ){
                        // registro válido.
                        if($registro->getStatus() != 9){
                            $it++;
                            $registroViejoReconciliacionId = $registro->getId();

                        }else{
                            // Posible Reconciliado
                            $posibleReconciliado = true;
                            $registroViejoReconciliado = $registro->getId();


                        }

                  //      echo " registro activo";
                    }
                }
                //var_dump($it);
                if($it == 0){
                    $valido = true;

                }else{
                    $valido = false;
                    $causa = "Ya existe un registro (".$registroViejoReconciliacionId.") para el Código Material: ".$material." Lote: ".$lote ;
                    $reconciliacion = true;
                }

                break;
            default:
                $valido = true;
                break;
        }

        if($valido){
            $registro->setStatus(0);
            $em->persist($registro);
            $em->flush();

            if($posibleReconciliado){
                // Actualizar:
                $reconciliacionRegistro = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
                    ->findOneBy(array("registro_viejo_id" => $registroViejoReconciliado));
                $reconciliacionRegistro->setRegistroNuevoId($registro->getId());
                $em->persist($reconciliacionRegistro);
                $em->flush();
            }

            $route = $this->container->get('router')->generate('nononsense_registro_concreto_link', array("stepid" => $stepid, "form" => 0));
        }else{
            if($reconciliacion){
                /*
                 * Flujo especial
                 */
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $causa
                );
                $route = $this->container->get('router')->generate('nononsense_solicitud_reconciliacion',array("registroid"=>$registroViejoReconciliacionId));

            }else{
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'No se puede crear el registro debido a: '.$causa
                );
                $route = $this->container->get('router')->generate('nononsense_home_homepage');
            }

        }
        return $this->redirect($route);
    }

    public function solicitudReconciliacionAction($registroid){
        return $this->render('NononsenseHomeBundle:Contratos:registro_reconciliacionInterface.html.twig', array(
            "registroid" => $registroid,
        ));
    }

    public function solicitudReconciliacionFormularioAction($registroid){
        /*
         * Crear nuevo registro especial de reconciliación.
         * Crear una reconciliacionRegistro en status 0
         * En validación debe ir al FLL.
         */
        $MasterWorkflow = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:MasterWorkflows')
            ->find(16);

        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $instanciaWorkflow = new InstanciasWorkflows();
        $instanciaWorkflow->setIsActive(true);
        $instanciaWorkflow->setStatus(0);
        $instanciaWorkflow->setDescription("");
        $instanciaWorkflow->setMasterDataValues("");
        $instanciaWorkflow->setObservaciones("");
        $instanciaWorkflow->setYear(2019);

        $instanciaWorkflow->setUserCreatedEntiy($user);
        $instanciaWorkflow->setMasterWorkflowEntity($MasterWorkflow);
        $instanciaWorkflow->setSignvalues("");
        $instanciaWorkflow->setFiles("");

        $em->persist($instanciaWorkflow);
        $em->flush();

        $MasterSteps = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:MasterSteps')
            ->findBy(array("workflow_id" => 16));

        foreach ($MasterSteps as $stepM) {
            $instancias_step = new InstanciasSteps();
            $instancias_step->setInstanciaWorkflow($instanciaWorkflow);

            $instancias_step->setMasterStep($stepM);
            $instancias_step->setDependsOn(0);
            $instancias_step->setRules("");
            $instancias_step->setStatusId(0);
            $instancias_step->setUsageId(0);
            $instancias_step->setToken("");
            $instancias_step->setIsActive(1);
            $instancias_step->setStepDataValue("");
            $instancias_step->setAuxvalues("");

            $em->persist($instancias_step);
        }

        $em->flush();

        // asignar los depends on

        foreach ($MasterSteps as $stepM) {
            $instancias_stepMultiple = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                ->findBy(array(
                    'master_step_id' => $stepM->getId(),
                    'workflow_id' => $instanciaWorkflow->getId()));

            foreach ($instancias_stepMultiple as $instancias_step) {
                if ($stepM->getDependsOn() != 0) {

                    $Master_step_dependsOn = $this->getDoctrine()
                        ->getRepository('NononsenseHomeBundle:MasterSteps')
                        ->find($stepM->getDependsOn());

                    $instancias_step_dependON = $this->getDoctrine()
                        ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                        ->findOneBy(array(
                            'master_step_id' => $Master_step_dependsOn->getId(),
                            'workflow_id' => $instanciaWorkflow->getId()));

                    $instancias_step->setDependsOn($instancias_step_dependON->getId());

                } else {
                    $firststep = $instancias_step->getId();
                }
            }
        }


        $em->flush();
        /*
         * MetaData fecha y hora de creación
         */
        $metaData = new MetaData();
        $metaData->setInstanciaWorkflow($instanciaWorkflow);
        $now = new \DateTime();
        $now->modify("+2 hour"); // Ver tema de horarios usos
        $metaData->setFechainicio($now);

        $em->persist($metaData);

        $em->persist($instanciaWorkflow);
        $em->flush();

        /*
         * Creacion de la entidad de control reconciliacionRegistro
         */
        $reconciliacionRegistro = new ReconciliacionRegistro();
        $reconciliacionRegistro->setStatus(0);
        $reconciliacionRegistro->setRegistroViejoId($registroid);
        $reconciliacionRegistro->setRegistroNuevoId(0);
        $reconciliacionRegistro->setSolicitudId($instanciaWorkflow->getId());
        $reconciliacionRegistro->setDescription("");

        $em->persist($reconciliacionRegistro);
        $em->flush();

        $route = $this->container->get('router')->generate('nononsense_registro_concreto_link', array("stepid" => $firststep, "form" => 0));
        return $this->redirect($route);

    }

    public function testLinkAction($templateid)
    {
        $options = array();

        $options['template'] = "33";

        $request = Request::createFromGlobals();
        $currentUrl = $request->getUri();
        $baseUrl = $this->getParameter("cm_installation");

        $indexToRemove = strpos($currentUrl, "/contratos_concreto_link");
        $currentUrl = substr($currentUrl, 0, $indexToRemove);

        $url_resp = $currentUrl;
        $options['responseURL'] = 'http://gsk.docxpresso.org';
        //$options['responseURL'] = 'http://testgit.pre.docxpresso.com/app_dev.php/';


        $url_edit_documento = $this->get('app.sdk')->previewDocAction($options);
        return $this->redirect($url_edit_documento);
    }

    public function codigoLoteInterfaceAction($registroid)
    {
        /*
         * Interfaz para asignar un codigo lote al workflow
         */
        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($registroid);

        $documentName = $registro->getMasterWorkflowEntity()->getName();
        $subCat = $registro->getMasterWorkflowEntity()->getCategory()->getName();

        return $this->render('NononsenseHomeBundle:Contratos:registro_creacion_codigolote.html.twig', array(
            "registroid" => $registroid,
            "documentName" => $documentName,
            "subCat" => $subCat
        ));
    }

    public function equipoQRInterfaceAction($registroid)
    {
        /*
        * Interfaz para asignar un equipo al workflow a través de un QR.
        */
        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($registroid);

        $documentName = $registro->getMasterWorkflowEntity()->getName();
        $subCat = $registro->getMasterWorkflowEntity()->getCategory()->getName();

        $master = $registro->getMasterWorkflowEntity()->getId();

        return $this->render('NononsenseHomeBundle:Contratos:registro_creacion_equipo_qr.html.twig', array(
            "registroid" => $registroid,
            "documentName" => $documentName,
            "subCat" => $subCat,
            "master" => $master
        ));
    }

    public function equipoPesaQRInterfaceAction($registroid)
    {
        /*
        * Interfaz para asignar un equipo al workflow a través de un QR.
        */
        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($registroid);

        $documentName = $registro->getMasterWorkflowEntity()->getName();
        $subCat = $registro->getMasterWorkflowEntity()->getCategory()->getName();

        $master = $registro->getMasterWorkflowEntity()->getId();

        return $this->render('NononsenseHomeBundle:Contratos:registro_creacion_equipo_pesa_qr.html.twig', array(
            "registroid" => $registroid,
            "documentName" => $documentName,
            "subCat" => $subCat,
            "master" => $master
        ));
    }

    public function codigoLoteSaveAction($registroid, Request $request)
    {

        /*
         * Pre-condition
         */
        /*
         * creationConditions
         */
        $em = $this->getDoctrine()->getManager();

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($registroid);

        /*
         * Guardar el master data values
         */
        $codigo = $request->query->get('material');
        $lote = $request->query->get('lote');

        $masterData = new \stdClass();
        $masterData->u_codigo_material = new \stdClass();
        $masterData->u_codigo_material->nameVar = "u_codigo_material";
        $masterData->u_codigo_material->valueVar = array($codigo);
        $masterData->u_codigo_material->step = "";

        $masterData->u_batch = new \stdClass();
        $masterData->u_batch->nameVar = "u_batch";
        $masterData->u_batch->valueVar = array($lote);
        $masterData->u_batch->step = "";

        $registro->setMasterDataValues(json_encode($masterData));

        $metaData = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:MetaData')
            ->findOneBy(array("workflow_id" => $registroid));
        /*
         * MetaData lote y codigo
         */

        $metaData->setLote($lote);
        $metaData->setMaterial($codigo);
        $em->persist($metaData);

        $em->persist($registro);
        $em->flush();


        $firststep = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array(
                'dependsOn' => 0,
                'workflow_id' => $registroid));

        $route = $this->container->get('router')->generate('nononsense_prevalidation_creation', array("registroid"=>$registro->getId(),"stepid" => $firststep->getId()));
        return $this->redirect($route);
    }

    public function equipoQRSaveAction($registroid, Request $request)
    {

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($registroid);

        $em = $this->getDoctrine()->getManager();

        /*
         * Guardar el master data values
         */

        $equipo = $request->query->get('equipo');

        $masterData = new \stdClass();
        $masterData->u_equipo = new \stdClass();
        $masterData->u_equipo->nameVar = "u_equipo";
        $masterData->u_equipo->valueVar = array($equipo);
        $masterData->u_equipo->step = "";

        $registro->setMasterDataValues(json_encode($masterData));


        /*
         * MetaData $equipo
         */
        $metaData = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:MetaData')
            ->findOneBy(array("workflow_id" => $registroid));

        $metaData->setEquipo($equipo);
        $em->persist($metaData);

        $em->persist($registro);
        $em->flush();


        $firststep = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array(
                'dependsOn' => 0,
                'workflow_id' => $registroid));

        $route = $this->container->get('router')->generate('nononsense_prevalidation_creation', array("registroid"=>$registro->getId(),"stepid" => $firststep->getId()));
        return $this->redirect($route);
    }

    public function equipoPesaQRSaveAction($registroid, Request $request)
    {

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($registroid);

        $em = $this->getDoctrine()->getManager();

        /*
         * Guardar el master data values
         */

        $equipo = $request->query->get('equipo');
        $pesa = $request->query->get('pesa');
        $control = $request->query->get('control');
        $aviso = $request->query->get('aviso');

        $masterData = new \stdClass();
        $masterData->u_pesa = new \stdClass();
        $masterData->u_pesa->nameVar = "u_pesa";
        $masterData->u_pesa->valueVar = array($pesa);
        $masterData->u_pesa->step = "";

        $masterData->u_equipo = new \stdClass();
        $masterData->u_equipo->nameVar = "u_equipo";
        $masterData->u_equipo->valueVar = array($equipo);
        $masterData->u_equipo->step = "";

        $masterData->u_limite_control = new \stdClass();
        $masterData->u_limite_control->nameVar = "u_limite_control";
        $masterData->u_limite_control->valueVar = array($control);
        $masterData->u_limite_control->step = "";

        $masterData->u_limite_aviso = new \stdClass();
        $masterData->u_limite_aviso->nameVar = "u_limite_aviso";
        $masterData->u_limite_aviso->valueVar = array($aviso);
        $masterData->u_limite_aviso->step = "";

        $registro->setMasterDataValues(json_encode($masterData));


        /*
         * MetaData $equipo
         */
        $metaData = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:MetaData')
            ->findOneBy(array("workflow_id" => $registroid));

        $metaData->setEquipo($equipo);
        $em->persist($metaData);

        $em->persist($registro);
        $em->flush();


        $firststep = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array(
                'dependsOn' => 0,
                'workflow_id' => $registroid));

        $route = $this->container->get('router')->generate('nononsense_prevalidation_creation', array("registroid"=>$registro->getId(),"stepid" => $firststep->getId()));
        return $this->redirect($route);
    }

    private function checkValid($tipoContrato, $mse_sms, &$sections, $cif, $cma, $arrayTipoProductos, $codigo_proveedor)
    {
        $valido = true;


        return $valido;
    }

}