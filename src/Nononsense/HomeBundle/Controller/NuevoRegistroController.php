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
use Nononsense\HomeBundle\Entity\MasterWorkflows;
use Nononsense\HomeBundle\Entity\MasterSteps;
use Nononsense\HomeBundle\Entity\MetaData;
use Nononsense\HomeBundle\Entity\EvidenciasStep;
use Nononsense\HomeBundle\Entity\FirmasStep;
use Nononsense\HomeBundle\Entity\Categories;
use Nononsense\HomeBundle\Entity\ReconciliacionRegistro;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\GroupBundle\Entity\Groups;
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

    public function editAction(Request $request, string $id)
    {
        $serializer = $this->get('serializer');

        $array_item["categorias"] = $this->getDoctrine()->getRepository('NononsenseHomeBundle:Categories')->findBy(array("padre" => 0));
        $array_item["subcategorias"] = $this->getDoctrine()->getRepository('NononsenseHomeBundle:Categories')->findAll();

        if($id!=0){
            $array_item["item"]  = $this->getDoctrine()->getRepository(MasterWorkflows::class)->detail($id);
            if(empty($array_item["item"])){
                return $this->redirect($this->container->get('router')->generate('nononsense_nuevo_contrato'));
            }
            
            $records = $this->getDoctrine()->getRepository(InstanciasSteps::class)->search("count",array("master_step_id"=>$array_item["item"]["stepId"]));
            if($records>0){
                $array_item["not_update"]=1;
            }
        }

        return $this->render('NononsenseHomeBundle:Contratos:template.html.twig',$array_item);
    }

    public function updateAction(Request $request, string $id)
    {   
        $em = $this->getDoctrine()->getManager();

        try {
            $not_update=0;
            $category = $this->getDoctrine()->getRepository(Categories::class)->findOneById($request->get("category_id"));
            $group = $this->getDoctrine()->getRepository(Groups::class)->findOneById(1);

            if($id!=0){
                $mw = $this->getDoctrine()->getRepository(MasterWorkflows::class)->findOneById($id);
                $ms = $this->getDoctrine()->getRepository(MasterSteps::class)->findOneBy(array("workflow_id"=>$id,"dependsOn"=>0));
                $ms2 = $this->getDoctrine()->getRepository(MasterSteps::class)->findOneBy(array("workflow_id"=>$id,"dependsOn"=>$ms->getId()));

                $records = $this->getDoctrine()->getRepository(InstanciasSteps::class)->search("count",array("master_step_id"=>$ms->getId()));
                if($records>0){
                    $not_update=1;
                }
                else{
                    if(!empty($ms)){
                        $em->remove($ms);
                        $ms=NULL;
                    }
                    if(!empty($ms2)){
                        $em->remove($ms2);
                        $ms2=NULL;
                    }
                }
            }
            else{
                $mw = new MasterWorkflows();
                
                $mw->setMasterData("");
                $mw->setConfig("");
                $mw->setOrdersteps("");
                $mw->setCreated(new \DateTime());
                $mw->setValidation("default");
                $ms = new MasterSteps();
            }

            $mw->setGrupoVerificacion($group);

            if(!$not_update){
                $mw->setPrecreation($request->get("precreation"));

                if($request->get("precreation")=="codigolote"){
                    $mw->setPrevalidation("codigoloteunico");
                }
                else{
                    $mw->setPrevalidation("default");
                }
                
                

                $ms = new MasterSteps();
                $ms->setMasterWorkflow($mw);
                $ms->setGroups($group);
                $ms->setPlantillaId($request->get("plantilla_id"));
                $ms->setPosition(1);
                $ms->setBlock(1);
                $ms->setOptional(0);
                $ms->setDependsOn(0);
                $ms->setNotification(1);
                $ms->setCloneable(0);
                $ms->setName($request->get("name"));
                $ms->setStatusId(1);
                $ms->setCreated(new \DateTime());
                $ms->setModified(new \DateTime());
                $ms->setChecklist(0);
                $ms->setPlantillaPrefix("");
                $ms->setRules("");
                $ms->setStepData("");
                $ms->setValidation("self");

                if($request->get("tiene_checklist")){
                    $mw->setChecklist(1);
                    $ms2 = new MasterSteps();

                    $ms2->setMasterWorkflow($mw);
                    $ms2->setGroups($group);
                    $ms2->setPlantillaId(23);
                    $ms2->setPosition(1);
                    $ms2->setBlock(1);
                    $ms2->setOptional(0);
                    $ms2->setDependsOn(0);
                    $ms2->setNotification(1);
                    $ms2->setCloneable(0);
                    $ms2->setName("IT-C-OP-144ANEXO5");
                    $ms2->setStatusId(1);
                    $ms2->setCreated(new \DateTime());
                    $ms2->setModified(new \DateTime());
                    $ms2->setChecklist(1);
                    $ms2->setValidation("self");
                    $ms2->setPlantillaPrefix("");
                    $ms2->setRules("");
                    $ms2->setStepData("");
                }
                else{
                    $mw->setChecklist(0);
                    $ms->setValidation("self");
                }
            }

            $mw->setModified(new \DateTime());

            
            $mw->setCategory($category);

            if($request->get("is_active")){
                $mw->setIsActive(1);
            }
            else{
                $mw->setIsActive(0);
            }

            if($request->get("logbook")){
                $mw->setLogbook(1);
            }
            else{
                $mw->setLogbook(0);
            }


            $mw->setName($request->get("name"));
            $mw->setDescription($request->get("description"));

            if(!empty($mw)){
                $em->persist($mw);
            }
            if(!empty($ms)){
                $em->persist($ms);
            }
            if(!empty($ms2)){
                $em->persist($ms2);
            }
            
            $em->flush();
            
            if($request->get("tiene_checklist")){
                $ms->setValidation($ms2->getId());
                $ms2->setDependsOn($ms->getId());
            }
            
            if(!empty($ms)){
                $em->persist($ms);
            }
            if(!empty($ms2)){
                $em->persist($ms2);
            }
            $em->flush();

        }catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error desconocido al intentar guardar los datos de la plantilla".$e->getMessage()
                );
            $route = $this->container->get('router')->generate('nononsense_templates_edit', array("id" => $id));
        
            return $this->redirect($route);
        }

        

        $route = $this->container->get('router')->generate('nononsense_nuevo_contrato');
        return $this->redirect($route);
    }

    public function createAction($templateid, Request $request)
    {

        if ($templateid == 0) {
            $logbook = $request->query->get('logbook');
            $templateid = $request->query->get('templateid');
        }
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
        $metaData->setFechainicio($now);

        $em->persist($metaData);

        $em->persist($instanciaWorkflow);
        $em->flush();

        $precreation = $MasterWorkflow->getPrecreation();

        /*
         * logbook -> post!!
         */

        switch ($precreation) {
            case "default":
                $instanciaWorkflow->setStatus(-1); // estado sin actividad
                $em->persist($instanciaWorkflow);
                $em->flush();
                $route = $this->container->get('router')->generate('nononsense_prevalidation_creation', array("registroid" => $instanciaWorkflow->getId(), "stepid" => $firststep, "logbook" => $logbook));
                break;
            case "codigolote":
                $route = $this->container->get('router')->generate('nononsense_precreation_codigo_lote_interface', array("registroid" => $instanciaWorkflow->getId(), "logbook" => $logbook));
                break;
            case "equipoqr":
                $route = $this->container->get('router')->generate('nononsense_precreation_equipo_qr_interface', array("registroid" => $instanciaWorkflow->getId(), "logbook" => $logbook));
                break;
            case "equipopesaqr":
                $route = $this->container->get('router')->generate('nononsense_precreation_equipo_pesa_qr_interface', array("registroid" => $instanciaWorkflow->getId(), "logbook" => $logbook));
                break;
            default:
                $instanciaWorkflow->setStatus(-1); // estado sin actividad
                $em->persist($instanciaWorkflow);
                $em->flush();
                $route = $this->container->get('router')->generate('nononsense_prevalidation_creation', array("registroid" => $instanciaWorkflow->getId(), "stepid" => $firststep, "logbook" => $logbook));
                break;
        }


        return $this->redirect($route);
    }

    public function checkPreValidationRegistroAction($registroid, $stepid, $logbook)
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


        switch ($prevalidationcreate) {
            case "intervencion":
                $metaData = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:MetaData')
                    ->findOneBy(array("workflow_id" => $registroid));

                $equipo = $metaData->getEquipo();

                $bloqueo = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:BloqueoMasterWorkflow')
                    ->findOneBy(array("master_workflow_id" => $registro->getMasterWorkflowEntity()->getId(), "status" => 0, "equipo" => $equipo));

                if ($bloqueo == null) {
                    $valido = true;
                } else {
                    $valido = false;
                    $causa = "Recepción del equipo tras intervención pendiente de verificación, la plantilla esta bloqueada hasta que se verifique.";
                }

                break;
            case "codigoloteunico":
                /*
                 * Sólo puede haber un registro con ese par codigo / lote por tipo de plantilla.
                 * ¿Si hay uno en proceso entiendo que no debería dejar seguir?
                 */
                $metaData = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:MetaData')
                    ->findOneBy(array("workflow_id" => $registroid));

                $lote = $metaData->getLote();
                $material = $metaData->getMaterial();

                $metaDataArray = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:MetaData')
                    ->findBy(array("lote" => $lote, "material" => $material));

                $it = 0;
                $registroViejoReconciliacionId = 0;

                $tipoPlantilla = $registro->getMasterWorkflow();
                $valido = true;

                foreach ($metaDataArray as $metaData) {

                    $registroM = $metaData->getInstanciaWorkflow();

                    if ($registroM->getMasterWorkflow() == $tipoPlantilla) {

                        if ($registroM->getStatus() != 20 &&
                            $registroM->getStatus() != -2) {
                            //echo "posible reconciliación, he encontrado el siguiente id: " . $registroM->getId(). " con el siguiente estado: ".$registroM->getStatus(). "<br />";

                            // posible reconciliación
                            //if ($registroM->getStatus() != 6 && $registroM->getStatus() != 8 && $registroM->getStatus() != 9 && $registroM->getStatus() != 10) {
                            if($registroM->getStatus()<0){
                                // En proceso, ya no es valido
                                $valido = false;
                                $registroViejoReconciliacionId = $registroM->getId();
                                //  echo "Proceso no válido <br/>";
                                $reconciliacion = false;

                            } else {
                                // Estados correctos, no problem
                                //echo "El registro que he detectado está en un estado final y se puede reconciliar, ole!<br />";
                                $valido = false;
                                $posibleReconciliado = true;
                                $registroViejoReconciliacionId = $registroM->getId();
                                $reconciliacion = true;
                            }
                        }
                    }

                }

                //var_dump($it);
                if (!$valido) {
                    $step = $this->getDoctrine()
                        ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                        ->findOneBy(array("workflow_id" => $registroViejoReconciliacionId, "dependsOn" => 0));

                    $causa = "Ya existe un registro (" . $step->getId(). ") para el Código Material: " . $material . " Lote: " . $lote;
                    //echo 'registro no válido por: '. $causa;
                } else {
                    //echo 'registro válido<br/>';
                }

                break;
            default:
                $valido = true;
                break;
        }
        //exit;

        if ($valido) {
            $registro->setStatus(0);
            $em->persist($registro);
            $em->flush();

            if ($posibleReconciliado) {
                // Actualizar:
                $reconciliacionRegistro = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
                    ->findOneBy(array("registro_viejo_id" => $registroViejoReconciliado));

                $reconciliacionRegistro->setRegistroNuevoId($registro->getId());
                $em->persist($reconciliacionRegistro);
                $em->flush();
            }

            $route = $this->container->get('router')->generate('nononsense_registro_concreto_link', array("stepid" => $stepid, "form" => 0, "revisionid" => 0, "logbook" => $logbook));
        } else {
            if ($reconciliacion) {
                /*
                 * Flujo especial
                 */
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $causa
                );
                $route = $this->container->get('router')->generate('nononsense_solicitud_reconciliacion', array("registroViejoId" => $registroViejoReconciliacionId, "registroNuevoId" => $registro->getId()));

            } else {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'No se puede crear el registro debido a: ' . $causa
                );
                $route = $this->container->get('router')->generate('nononsense_home_homepage');
            }

        }
        return $this->redirect($route);
    }

    public function solicitudReconciliacionAction($registroViejoId, $registroNuevoId)
    {
        return $this->render('NononsenseHomeBundle:Contratos:registro_reconciliacionInterface.html.twig', array(
            "registroid" => $registroViejoId,
            "nuevoregistroid" => $registroNuevoId
        ));
    }

    public function solicitudReconciliacionFormularioAction($registroid, $nuevoregistroid)
    {
        /*
         * Antes:
         * Crear nuevo registro especial de reconciliación.
         * Crear una reconciliacionRegistro en status 0
         *
         * Ahora:
         * Crear una entidad reconciliacionRegistro y utilizarla
         * En validación debe ir al FLL.
         */

        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $registroViejo = $reconciliacionRegistro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($registroid);

        $registroNuevo = $reconciliacionRegistro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($nuevoregistroid);


        /*
         * Creacion de la entidad de control reconciliacionRegistro
         */
        $reconciliacionRegistro = new ReconciliacionRegistro();
        $reconciliacionRegistro->setStatus(-1); // Estado pendiente
        //$reconciliacionRegistro->setRegistroViejoId($registroid);
        //$reconciliacionRegistro->setRegistroNuevoId($nuevoregistroid);
        $reconciliacionRegistro->setRegistroViejoEntity($registroViejo);
        $reconciliacionRegistro->setRegistroNuevoEntity($registroNuevo);
        //$reconciliacionRegistro->setUserId($user->getId());
        $reconciliacionRegistro->setUserEntiy($user);
        $reconciliacionRegistro->setDescription("Petición en estado pendiente");

        $em->persist($reconciliacionRegistro);
        $em->flush();

        $subcat = $registroViejo->getMasterWorkflowEntity()->getCategory()->getName();
        $name = $registroViejo->getMasterWorkflowEntity()->getName();
        $fecha = $reconciliacionRegistro->getCreated();

        $fisrtStep =  $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registroid, "dependsOn" => 0));

        $peticion = array(
            "id" => $reconciliacionRegistro->getId(),
            "idafectado" => $fisrtStep->getId(),
            "subcat" => $subcat,
            "name" => $name,
            "fecha" => $fecha,
            "nameUser" => $user->getName(),
            "workflow" => $registroid
        );

        //Cargar interfaz para ello.
        return $this->render('NononsenseHomeBundle:Contratos:formulario_peticion_reconciliacion.html.twig', array(
            "peticion" => $peticion,

        ));

    }

    public function SolicitarPeticionAction($peticionid,Request $request){
        $peticionEntity = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
            ->find($peticionid);

        $registroViejoId = $peticionEntity->getRegistroViejoId();
        //$registroNuevoId = $peticionEntity->getRegistroNuevoId();
/*
        $registroNuevo = $reconciliacionRegistro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($registroNuevoId);
  */
        $registroNuevo = $peticionEntity->getRegistroNuevoEntity();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registroViejoId, "dependsOn"=>0));

        $step_nuevo = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registroNuevo, "dependsOn"=>0));

        /*
         * Guardar firma
         */

        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        /*
        * Guardar evidencia un paso "mas" auxiliar
        */
        $evidencia = new EvidenciasStep();
        $evidencia->setStepEntity($step_nuevo);
        $evidencia->setStatus(0);
        $evidencia->setUserEntiy($user);
        $evidencia->setToken($step_nuevo->getToken());
        $evidencia->setStepDataValue($step_nuevo->getStepDataValue());

        $firmaImagen = $request->get('firma');
        $comentario = $request->get('comment');

        $descp = "Petición de reconciliación solicitada. " . $comentario;

        $peticionEntity->setStatus(0);
        $peticionEntity->setDescription($comentario);

        /*
        * Guardar firma
        */
        $firmas = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findBy(array("step_id" => $step_nuevo->getId()));

        $counter = count($firmas) + 1;

        $firma = new FirmasStep();
        $firma->setAccion($descp);
        $firma->setStepEntity($step_nuevo);
        $firma->setUserEntiy($user);
        $firma->setFirma($firmaImagen);
        $firma->setStatus(1);
        $firma->setNumber($counter);
        $firma->setElaboracion(0);

        $evidencia->setFirmaEntity($firma);

        $registroNuevo->setStatus(16); // Esperando autorización para reconciliación

        $em->persist($evidencia);
        $em->persist($peticionEntity);
        $em->persist($firma);
        $em->persist($registroNuevo);
        $em->flush();

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registroNuevo->getId(), "dependsOn" => 0));

        $this->get('session')->getFlashBag()->add(
            'success',
            "Creada la solicitud de reconciliación. Cuando se la autoricen podrá comenzar la elaboración del registro: " . $step->getId()
        );

        $groups = $em->getRepository(Groups::class)->findBy(["tipo" => "FLL"]);
        foreach($groups as $group){
            $aux_users = $em->getRepository(GroupUsers::class)->findBy(["group" => $group]);
            foreach ($aux_users as $aux_user) {
                $emails[]=$aux_user->getUser()->getEmail();
            }
        }

        $unique_emails = array_unique($emails);

        foreach($unique_emails as $email){
            $subject="Solicitud de reconciliación";
            $mensaje='Se ha solicitado una reconciliación de ID '.$peticionEntity->getId().' con el motivo:<br> <b>'.$peticionEntity->getDescription().'</b>.<br><br> Para poder gestionarla pulse en el siguiente link';
            $baseURL=$this->container->get('router')->generate('nononsense_autorizar_peticion',array("peticionid"=>$peticionid),TRUE);
            
            $this->_sendNotification($email, $baseURL, "", "", $subject, $mensaje);
        }

        $route = $this->container->get('router')->generate('nononsense_search');
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

    public function codigoLoteInterfaceAction($registroid, $logbook)
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
            "subCat" => $subCat,
            "logbook" => $logbook
        ));
    }

    public function equipoQRInterfaceAction($registroid, $logbook)
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
            "master" => $master,
            "logbook" => $logbook
        ));
    }

    public function equipoPesaQRInterfaceAction($registroid, $logbook)
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

        $valoresAdmitidos = array();
        $listadoPesas = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:PesasQR')
            ->findAll();

        foreach ($listadoPesas as $pesa){
            $valoresAdmitidos[] = $pesa->getSap();
        }


        return $this->render('NononsenseHomeBundle:Contratos:registro_creacion_equipo_pesa_qr.html.twig', array(
            "registroid" => $registroid,
            "documentName" => $documentName,
            "subCat" => $subCat,
            "master" => $master,
            "logbook" => $logbook,
            "valoresAdmitidos" => $valoresAdmitidos
        ));
    }

    public function codigoLoteSaveAction($registroid, Request $request, $logbook)
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
        $masterData->u_material = new \stdClass();
        $masterData->u_material->nameVar = "u_material";
        $masterData->u_material->valueVar = array($codigo);
        $masterData->u_material->step = "";

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

        $route = $this->container->get('router')->generate('nononsense_prevalidation_creation', array("registroid" => $registro->getId(), "stepid" => $firststep->getId(), "logbook" => $logbook));
        return $this->redirect($route);
    }

    public function equipoQRSaveAction($registroid, Request $request, $logbook)
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

        $route = $this->container->get('router')->generate('nononsense_prevalidation_creation', array("registroid" => $registro->getId(), "stepid" => $firststep->getId(), "logbook" => $logbook));
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
         * Obtener los valores de la entidad PESASQR a partir del SAP.
         */
        $sap = $request->query->get('sap');

        $PesasQr = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:PesasQR')
            ->findOneBy(array("sap"=>$sap));



        $equipo = $PesasQr->getEquipo();

        $ubicacion= $PesasQr->getUbicacion();
        $decimales = $PesasQr->getDecimales();
        $legibilidad = $PesasQr->getLegibilidad();
        $pesada_maxima = $PesasQr->getPesadaMaxima();
        $pesada_minima= $PesasQr->getPesadaMinima();
        $pesa_chequeo_sensibilidad = $PesasQr->getPesaChequeoSensibilidad();
        $cl = $PesasQr->getCL();
        $cl_sup = $PesasQr->getCLSup();
        $cl_inf = $PesasQr->getCLInf();
        $wl = $PesasQr->getWL();
        $wl_sup = $PesasQr->getWLSup();
        $wl_inf = $PesasQr->getWLInf();
        $peso_chequeo_repetibilidad= $PesasQr->getPesaChequeoRepetibilidad();
        $cl_desv_std = $PesasQr->getCLDev();
        $wl_desv_std= $PesasQr->getWLDev();


/*
        $equipo = $request->query->get('equipo');

        $ubicacion= $request->query->get('ubicacion');
        $decimales = $request->query->get('decimales');
        $legibilidad = $request->query->get('legibilidad');
        $pesada_maxima = $request->query->get('pesada_maxima');
        $pesada_minima= $request->query->get('pesada_minima');
        $pesa_chequeo_sensibilidad = $request->query->get('pesa_chequeo_sensibilidad');
        $cl = $request->query->get('cl');
        $cl_sup = $request->query->get('cl_sup');
        $cl_inf = $request->query->get('cl_inf');
        $wl = $request->query->get('wl');
        $wl_sup = $request->query->get('wl_sup');
        $wl_inf = $request->query->get('wl_inf');
        $peso_chequeo_repetibilidad= $request->query->get('peso_chequeo_repetibilidad');
        $cl_desv_std = $request->query->get('cl_desv_std');
        $wl_desv_std= $request->query->get('wl_desv_std');
*/
        $masterData = new \stdClass();

        $masterData->u_equipo = new \stdClass();
        $masterData->u_equipo->nameVar = "u_equipo";
        $masterData->u_equipo->valueVar = array($equipo);
        $masterData->u_equipo->step = "";

        $this->createMasterValue($masterData,"sap",$sap);
        $this->createMasterValue($masterData,"ubicacion",$ubicacion);
        $this->createMasterValue($masterData,"decimales",$decimales);
        $this->createMasterValue($masterData,"legibilidad",$legibilidad);
        $this->createMasterValue($masterData,"pesada_maxima",$pesada_maxima);
        $this->createMasterValue($masterData,"pesada_minima",$pesada_minima);
        $this->createMasterValue($masterData,"pesa_chequeo_sensibilidad",$pesa_chequeo_sensibilidad);
        $this->createMasterValue($masterData,"cl",$cl);
        $this->createMasterValue($masterData,"cl_sup",$cl_sup);
        $this->createMasterValue($masterData,"cl_inf",$cl_inf);
        $this->createMasterValue($masterData,"wl",$wl);
        $this->createMasterValue($masterData,"wl_sup",$wl_sup);
        $this->createMasterValue($masterData,"wl_inf",$wl_inf);
        $this->createMasterValue($masterData,"peso_chequeo_repetibilidad",$peso_chequeo_repetibilidad);
        $this->createMasterValue($masterData,"cl_desv_std",$cl_desv_std);
        $this->createMasterValue($masterData,"wl_desv_std",$wl_desv_std);

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

        $route = $this->container->get('router')->generate('nononsense_prevalidation_creation', array("registroid" => $registro->getId(), "stepid" => $firststep->getId()));
        return $this->redirect($route);
    }

    private function createMasterValue(&$masterData, $varName, $varValue){
        $masterData->{$varName} = new \stdClass();
        $masterData->{$varName}->nameVar = $varName;
        $masterData->{$varName}->valueVar = array($varValue);
        $masterData->{$varName}->step = "";
    }

    private function checkValid($tipoContrato, $mse_sms, &$sections, $cif, $cma, $arrayTipoProductos, $codigo_proveedor)
    {
        $valido = true;


        return $valido;
    }

    private function _sendNotification($mailTo, $link, $logo, $accion, $subject, $message)
    {
        $mailLogger = new \Swift_Plugins_Loggers_ArrayLogger();
        $this->get('mailer')->registerPlugin(new \Swift_Plugins_LoggerPlugin($mailLogger));
        $email = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->container->getParameter('mailer_user'))
            ->setTo($mailTo)
            ->setBody(
                $this->renderView(
                    'NononsenseHomeBundle:Email:notificationUser.html.twig', array(
                    'logo' => $logo,
                    'accion' => $accion,
                    'message' => $message,
                    'link' => $link
                )),
                'text/html'
            );
        if ($this->get('mailer')->send($email)) {
            //echo '[SWIFTMAILER] sent email to ' . $mailTo;
            //echo 'LOG: ' . $mailLogger->dump();
            return true;
        } else {
            //echo '[SWIFTMAILER] not sending email: ' . $mailLogger->dump();
            return false;
        }

    }
}