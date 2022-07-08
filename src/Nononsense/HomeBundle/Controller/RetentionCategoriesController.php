<?php

namespace Nononsense\HomeBundle\Controller;

use DateInterval;
use DateTime;
use Exception;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\HomeBundle\Entity\RCSignatures;
use Nononsense\HomeBundle\Entity\RCStates;
use Nononsense\HomeBundle\Entity\RCTypes;
use Nononsense\HomeBundle\Entity\RetentionCategories;
use Nononsense\HomeBundle\Entity\RetentionCategoriesRepository;
use Nononsense\HomeBundle\Entity\TMTemplates;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RetentionCategoriesController extends Controller
{
    public function listAction(Request $request)
    {
        $DEFAULT_LIMIT = 15;

        $hasPermission = $this->get('app.security')->permissionSeccion('retention_admin');
        if (!$hasPermission) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();

        $filters = Utils::getListFilters($request);
        $filters['limit_many'] = ($request->get('limit_many')) ?: $DEFAULT_LIMIT;

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

        return $this->render('NononsenseHomeBundle:Retention:retention_categories_management.html.twig', $data);
    }

    public function editAction(Request $request, $id)
    {
        $hasPermission = $this->get('app.security')->permissionSeccion('retention_admin');
        if (!$hasPermission) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository(retentionCategories::class)->findOneBy(['id' => $id, 'deletedAt' => null]);
        if (!$category) {
            $category = new retentionCategories();
        }

        if ($request->getMethod() == 'POST') {
            if ($this->saveData($request, $category)) {
                return $this->redirect($this->generateUrl('nononsense_retention_categories_list'));
            }
        }

        $states = $em->getRepository(RCStates::class)->findAll();
        $types = $em->getRepository(RCTypes::class)->findAll();
        $users = $em->getRepository(Users::class)->findBy(array(),array("name" => "ASC"));
        $groups = $em->getRepository(Groups::class)->findBy(array(),array("name" => "ASC"));
        $used = (count($category->getTemplates()) > 1);

        if (!$this->thereAreData($states, $types, $users, $groups)) {
            $this->addFlash("error", "No hemos podido recuperar los tipos, los estados, los usuarios o los grupos");
            $data = [];
        } else {
            $data = [
                'category' => $category,
                'states' => $states,
                'types' => $types,
                'users' => $users,
                'groups' => $groups,
                'used' => $used
            ];
        }

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
                $e->getMessage()
            );
            return $this->redirect($this->generateUrl('nononsense_retention_categories_edit', ['id' => $id]));
        }
        return $this->redirect($this->generateUrl('nononsense_retention_categories_list'));
    }

    /**
     * @param Request $request
     * @param RetentionCategories $category
     * @return bool | Exception
     */
    private function saveData(Request $request, retentionCategories $category)
    {
        if (!$request->get('comment') || !$request->get('signature')) {
            throw new Exception('Para realizar una acción tienes que escribir un comentario y firmar.');
        }

        if (
            !$request->get('name') || !$request->get('description') ||
            (!$request->get('group') && !$request->get('user')) ||
            !$request->get('state') || !$request->get('type') ||
            !$request->get('retention_period_start_date')
        ) {
            throw new Exception('Todos los datos son obligatorios.');
        }

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

            $category->setModified(new DateTime());
            $category->setName($request->get('name'));
            $category->setDescription($request->get('description'));
            $category->setRetentionPeriodStartDate(DateTime::createFromFormat('d-m-Y',$request->get('retention_period_start_date')));
            $category->setRetentionDaysFormatted($retentionDays);
            $category->setRetentionPeriodEndDate(DateTime::createFromFormat('d-m-Y',$request->get('retention_period_start_date'))); // It is automatically computed
            $this->updateFinishRetentionDateTemplates($category->getId(), $category->getRetentionDays());
            $category->setActive((bool)$request->get('active'));
            $category->setDocumentState($state);
            $category->setType($type);

            $userId = (int) $request->get('user');
            if ($userId) {
                /** @var Users $user */
                $user = $em->getRepository(Users::class)->find($userId);
                $category->setDestroyUser($user);
            } else {
                $groupId = (int) $request->get('group');
                /** @var Groups $group */
                $group = $em->getRepository(Groups::class)->find($groupId);
                $category->setDestroyGroup($group);
            }

            $em->persist($category);
            $em->flush();

            $this->saveLog($action, $request->get('comment'), $request->get('signature'), $category);

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add(
                'message',
                "La categoría de retención se ha guardado correctamente"
            );
            $saved = true;
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add(
                'error', $e->getMessage()
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

    private function thereAreData(array $states, array $types, array $users, array $groups): bool  {
        return (count($states) > 0 && count($types) > 0 && count($users) > 0 && count($groups) > 0);
    }

    private function updateFinishRetentionDateTemplates(int $categoryId, int $retentionDays)
    {
        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();
        $sqlTemplatesWithThisCategory =
        "
            select tmtemplates_id
            from tm_retentions tr
            where tr.retentioncategories_id = %d
        ";
        $sqlTemplatesWithThisCategoryWithValues = sprintf($sqlTemplatesWithThisCategory, $categoryId);

        $templatesWithThisCategorySTMT = $connection->prepare($sqlTemplatesWithThisCategoryWithValues);
        $templatesWithThisCategorySTMT->execute();
        $templatesWithThisCategory = $templatesWithThisCategorySTMT->fetchAll();
        $updateTMTemplatesWithRetentionDays =
            "
            update tm_templates
            set start_retention = DATEADD(day,  %d, start_retention)
            where tm_templates.id = %d
            and tm_templates.destruction_date is null or datalength(tm_templates.destruction_date) = 0
        ";
        foreach($templatesWithThisCategory as $templateWithThisCategory) {
            $updateTMTemplatesWithRetentionDaysSQL = sprintf(
                $updateTMTemplatesWithRetentionDays, $retentionDays, $templateWithThisCategory["tmtemplates_id"]
            );
            $connection->executeUpdate($updateTMTemplatesWithRetentionDaysSQL);
        }

    }
}
