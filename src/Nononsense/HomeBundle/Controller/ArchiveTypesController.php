<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\HomeBundle\Entity\ArchiveSignatures;
use Nononsense\HomeBundle\Entity\ArchiveTypes;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ArchiveTypesController extends Controller
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

        $archiveCategoriesRepository = $em->getRepository(ArchiveTypes::class);
        $items = $archiveCategoriesRepository->list($filters);
        $totalItems = $archiveCategoriesRepository->count($filters);

        $data = [
            'filters' => $filters,
            'items' => $items,
            'count' => $totalItems,
            'pagination' => Utils::getPaginator($request, $filters['limit_many'], $totalItems)
        ];

        return $this->render('NononsenseHomeBundle:Archive:list_types.html.twig', $data);
    }

    public function editAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('archive_admin');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository(ArchiveTypes::class)->findOneBy(['id' => $id]);

        if (!$category) {
            $category = new ArchiveTypes();
            $category->setCreated(new \DateTime());
        }

        if ($request->getMethod() == 'POST' && $this->saveData($request, $category)) {
            return $this->redirect($this->generateUrl('nononsense_archive_types_list'));
        }

        $data = [
            'category' => $category,
            'used' => false
        ];

        return $this->render('NononsenseHomeBundle:Archive:type_edit.html.twig', $data);
    }

    /**
     * @param Request $request
     * @param ArchiveTypes $category
     * @return bool
     */
    private function saveData(Request $request, ArchiveTypes $category)
    {
        $em = $this->getDoctrine()->getManager();
        $saved = false;
        $action = 5;
        if ($category->getId()) {
            $action = 2;
        }
        $em->getConnection()->beginTransaction();
        try {
            $category->setModified(new DateTime());
            $category->setName($request->get('name'));
            $category->setDescription($request->get('description'));
            $active=($request->get('active') || $action==5) ? true : false;
            if($action==2 && $active!=$category->getActive()){
                $action=4;
                if($active){
                    $action=3;
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
            $this->get('utilities')->saveLogArchive($this->getUser(),$action,$comment,"type",$category->getId());
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add(
                'message',
                "El tipo de archivo se ha guardado correctamente"
            );
            $saved = true;
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error al intentar guardar el tipo de archivo"
            );
        }
        return $saved;
    }
}
