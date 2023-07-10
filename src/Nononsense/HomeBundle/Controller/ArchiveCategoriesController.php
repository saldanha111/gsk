<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\HomeBundle\Entity\ArchiveSignatures;
use Nononsense\HomeBundle\Entity\ArchiveCategories;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ArchiveCategoriesController extends Controller
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

        $archiveCategoriesRepository = $em->getRepository(ArchiveCategories::class);
        $items = $archiveCategoriesRepository->list($filters);
        $totalItems = $archiveCategoriesRepository->count($filters);

        $data = [
            'filters' => $filters,
            'items' => $items,
            'count' => $totalItems,
            'pagination' => Utils::getPaginator($request, $filters['limit_many'], $totalItems)
        ];

        return $this->render('NononsenseHomeBundle:Archive:list_categories.html.twig', $data);
    }

    public function editAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('archive_admin');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository(ArchiveCategories::class)->findOneBy(['id' => $id]);

        if (!$category) {
            $category = new ArchiveCategories();
        }

        if ($request->getMethod() == 'POST' && $this->saveData($request, $category)) {
            return $this->redirect($this->generateUrl('nononsense_archive_categories_list'));
        }

        $data = [
            'category' => $category,
            'used' => false
        ];

        return $this->render('NononsenseHomeBundle:Archive:category_edit.html.twig', $data);
    }

    /**
     * @param Request $request
     * @param ArchiveCategories $category
     * @return bool
     */
    private function saveData(Request $request, ArchiveCategories $category)
    {
        $em = $this->getDoctrine()->getManager();
        $saved = false;
        $action = 'create';
        if ($category->getId()) {
            $action = 'edit';
        }
        $em->getConnection()->beginTransaction();
        try {
            $retentionDays = [
                'days' => $request->get('days'),
                'months' => $request->get('months'),
                'years' => $request->get('years')
            ];

            $category->setModified(new DateTime());
            $category->setName($request->get('name'));
            $category->setDescription($request->get('description'));
            $category->setRetentionDaysFormatted($retentionDays);
            $category->setActive(($request->get('active') || $action=='create') ? true : false);
            $category->setCreated(new \DateTime());


            if (!$request->get('name') || !$request->get('description')) {
                throw new Exception('Todos los datos son obligatorios.');
            }

            $comment="";
            if($request->get("comment")){
                $comment=$request->get("comment");
            }

            $em->persist($category);
            $em->flush();
            $this->get('utilities')->saveLogArchive($this->getUser(),2,$comment,"category",$category->getId());
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add(
                'message',
                "La categoría de retencioón se ha guardado correctamente"
            );
            $saved = true;
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error al intentar guardar los datos de la categoría"
            );
        }
        return $saved;
    }
}
