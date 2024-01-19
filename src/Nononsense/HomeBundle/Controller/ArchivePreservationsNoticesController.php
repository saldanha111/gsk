<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\HomeBundle\Entity\ArchiveSignatures;
use Nononsense\HomeBundle\Entity\ArchivePreservations;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ArchivePreservationsNoticesController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('archive_admin');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $defaultLimit = 15;
        $filters = Utils::getListFilters($request);
        $filters['limit_many'] = ($request->get('limit_many')) ?: $defaultLimit;

        $archiveCategoriesRepository = $em->getRepository(ArchivePreservations::class);
        $items = $archiveCategoriesRepository->list($filters);
        $totalItems = $archiveCategoriesRepository->count($filters);

        $data = [
            'filters' => $filters,
            'items' => $items,
            'count' => $totalItems,
            'pagination' => Utils::getPaginator($request, $filters['limit_many'], $totalItems)
        ];

        return $this->render('NononsenseHomeBundle:Archive:list_preservations.html.twig', $data);
    }

    public function editAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('archive_admin');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository(ArchivePreservations::class)->findOneBy(['id' => $id]);

        if (!$category) {
            $category = new ArchivePreservations();
            $category->setCreated(new \DateTime());
        }

        if ($request->getMethod() == 'POST' && $this->saveData($request, $category)) {
            return $this->redirect($this->generateUrl('nononsense_archive_preservations_list'));
        }

        $data = [
            'category' => $category,
            'used' => false
        ];

        return $this->render('NononsenseHomeBundle:Archive:preservation_edit.html.twig', $data);
    }

    /**
     * @param Request $request
     * @param ArchivePreservations $category
     * @return bool
     */
    private function saveData(Request $request, ArchivePreservations $category)
    {
        $em = $this->getDoctrine()->getManager();
        $saved = false;
        $action = 5;
        $actionActive=null;
        $changes="";
        if ($category->getId()) {
            $action = 2;
        }
        $em->getConnection()->beginTransaction();
        try {
            if($action!=5){
                $changes=$this->getChanges($request,$category);  
            }
            $category->setModified(new DateTime());
            $category->setName($request->get('name'));
            $category->setDescription($request->get('description'));
            $active=($request->get('active') || $action==5) ? true : false;
            if($action==2 && $active!=$category->getActive()){
                if($changes==""){
                    $action=NULL;
                }
                $actionActive=4;
                if($active){
                    $actionActive=3;
                }
            }
            $category->setActive($active);
            
            if (!$request->get('name') || !$request->get('description')) {
                throw new Exception('Todos los datos son obligatorios.');
            }

            $comment="";
            if($request->get("comment")){
                $comment=$request->get("comment");
            }

            $em->persist($category);
            $em->flush();
            if($action){
                $this->get('utilities')->saveLogArchive($this->getUser(),$action,$comment,"preservation",$category->getId(),NULL,NULL,$changes);
            }

            if($actionActive){
                $this->get('utilities')->saveLogArchive($this->getUser(),$actionActive,$comment,"preservation",$category->getId());
            }

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add(
                'message',
                "La preservation notice se ha guardado correctamente"
            );
            $saved = true;
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error al intentar guardar los datos de la preservation notice"
            );
        }
        return $saved;
    }

    public function getChanges($request,$item){
        $changes="";
        $em = $this->getDoctrine()->getManager();

        if($request->get("name") && $request->get("name")!=$item->getName()){
            $changes.="<tr><td>Nombre</td><td>".$item->getName()."</td><td>".$request->get("name")."</td></tr>";
        }

        if($request->get("description") && $request->get("description")!=$item->getDescription()){
            $changes.="<tr><td>Descripción</td><td>".$item->getDescription()."</td><td>".$request->get("description")."</td></tr>";
        }

        if($changes!=""){
            $changes="\n<table class='table'><tr><td>Campo</td><td>Anterior</td><td>Nuevo</td></tr>".$changes."</table>";
        }

        return $changes;
    }
}
