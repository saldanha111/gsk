<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\HomeBundle\Entity\RCSignatures;
use Nononsense\HomeBundle\Entity\RCStates;
use Nononsense\HomeBundle\Entity\RCTypes;
use Nononsense\HomeBundle\Entity\RetentionCategories;
use Nononsense\HomeBundle\Entity\RetentionCategoriesRepository;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RetentionCategoriesController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('retention_admin');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $defaultLimit = 15;
        $filters = Utils::getListFilters($request);
        $filters['limit_many'] = ($request->get('limit_many')) ?: $defaultLimit;

        /** @var retentionCategoriesRepository $retentionCategoriesRepository */
        $retentionCategoriesRepository = $em->getRepository(retentionCategories::class);
        $states = $em->getRepository(RCStates::class)->findAll();
        $types = $em->getRepository(RCTypes::class)->findAll();
        $items = $retentionCategoriesRepository->list($filters);
        $totalItems = $retentionCategoriesRepository->count($filters);

        $data = [
            'filters' => $filters,
            'types' => $types,
            'states' => $states,
            'items' => $items,
            'count' => $totalItems,
            'pagination' => Utils::getPaginator($request, $filters['limit_many'], $totalItems)
        ];

        return $this->render('NononsenseHomeBundle:Retention:list_retention_categories.html.twig', $data);
    }

    public function editAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('retention_admin');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository(retentionCategories::class)->findOneBy(['id' => $id, 'deletedAt' => null]);

        if (!$category) {
            $category = new retentionCategories();
        }

        if ($request->getMethod() == 'POST' && $this->saveData($request, $category)) {
            return $this->redirect($this->generateUrl('nononsense_retention_categories_list'));
        }

        $states = $em->getRepository(RCStates::class)->findAll();
        $types = $em->getRepository(RCTypes::class)->findAll();
        $users = $em->getRepository(Users::class)->listUsersByPermission("retention_agent");
        $groups = $em->getRepository(Groups::class)->listGroupsByPermission("retention_agent");
        $used = (count($category->getTemplates()) > 1);

        $data = [
            'category' => $category,
            'states' => $states,
            'types' => $types,
            'users' => $users,
            'groups' => $groups,
            'used' => $used
        ];

        return $this->render('NononsenseHomeBundle:Retention:category_edit.html.twig', $data);
    }

    public function deleteAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('retention_admin');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            /** @var RetentionCategories $retentionCategory */
            $retentionCategory = $em->getRepository(retentionCategories::class)->findOneBy(
                ['id' => $id, 'deletedAt' => null]
            );

            if ($retentionCategory) {
                $retentionCategory->setDeletedAt(new DateTime());
                $em->persist($retentionCategory);
                $em->flush();
                $this->saveLog('delete', $request->get('comment'), $request->get('signature'), $retentionCategory);
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('message', "La categoría se ha eliminado correctamente");
            } else {
                $this->get('session')->getFlashBag()->add('error', "La categoría no existe");
            }
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error al intentar eliminar la categoría"
            );
            return $this->redirect($this->generateUrl('nononsense_retention_categories_edit', ['id' => $id]));
        }
        return $this->redirect($this->generateUrl('nononsense_retention_categories_list'));
    }

    /**
     * @param Request $request
     * @param RetentionCategories $category
     * @return bool
     */
    private function saveData(Request $request, retentionCategories $category)
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
            /** @var RCStates $state */
            $state = $em->getRepository(RCStates::class)->find($request->get('state'));
            /** @var RCTypes $type */
            $type = $em->getRepository(RCTypes::class)->find($request->get('type'));
            /** @var Users $user */
            $user = $em->getRepository(Users::class)->find($request->get('user'));
            /** @var Groups $group */
            $group = $em->getRepository(Groups::class)->find($request->get('group'));

            $category->setModified(new DateTime());
            $category->setName($request->get('name'));
            $category->setDescription($request->get('description'));
            $category->setRetentionDaysFormatted($retentionDays);
            $category->setActive(($request->get('active')) ? true : false);
            $category->setDocumentState($state);
            $category->setType($type);
            if ($user) {
                $category->setDestroyUser($user);
            }
            if ($group) {
                $category->setDestroyGroup($group);
            }

            if (!$request->get('name') || !$request->get('description') || (!$user && !$group) || !$state || !$type) {
                throw new Exception('Todos los datos son obligatorios.');
            }

            $comment="";
            if($request->get("comment")){
                $comment=$request->get("comment");
            }

            $em->persist($category);
            $em->flush();
            $this->saveLog($action, $comment, $request->get('signature'), $category);
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

    /**
     * @param string $comment
     * @param string $signature
     * @param RetentionCategories $retentionCategory
     * @param string $action
     * @return bool
     * @throws Exception
     */
    private function saveLog(string $action, string $comment, string $signature, RetentionCategories $retentionCategory)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$signature) {
            throw new Exception('Para realizar una acción tienes que escribir un comentario y firmar.');
        }

        $signatureLog = new RCSignatures();
        $signatureLog->setAction($action)
            ->setDescription($comment)
            ->setRetentionCategory($retentionCategory)
            ->setSignature($signature)
            ->setUserEntiy($this->getUser());
        $em->persist($signatureLog);
        $em->flush();
        return true;
    }
}
