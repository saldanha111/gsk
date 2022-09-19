<?php
declare(strict_types=1);

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\GroupBundle\Entity\GroupsRepository;
use Nononsense\HomeBundle\Entity\CVStates;
use Nononsense\HomeBundle\Entity\CVStatesRepository;
use Nononsense\HomeBundle\Entity\TMTemplates;
use Nononsense\HomeBundle\Utils\FiltersUtils;
use Nononsense\NotificationsBundle\Entity\NotificationsModels;
use Nononsense\NotificationsBundle\Entity\NotificationsModelsRepository;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UserBundle\Entity\UsersRepository;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NotificationsModelsController extends Controller
{

    const GROUP = 1;
    const USER = 2;
    const EMAIL = 3;
    const LIMIT_MANY = 15;

    public function creatorAction(Request $request)
    {
        if (!$this->isAllowed('notifications_gestion')) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        return $this->render('NononsenseHomeBundle:NotificationsModels:notifications_models_new.html.twig'
            , [
                "finder" => [
                    "statuses" => $this->statusFinder()
                ]
            ]
        );
    }

    public function addNotificationAction(Request $request)
    {
        $userid = $this->getUser()->getId();

        $data = [
            "templateId" => json_encode((int)$request->get("templateId"))
            , "stateId" => (int)$request->get("state")
            , "msg" => $request->get("msg")
            , "type" => (int)$request->get("type")
            , "collectionId" => (int)$request->get("collectionId")
            , "email" => $request->get("email")
            , "createdBy" => (int)$userid
            , "subject" => $request->get("subject")
        ];
        $notificationModel = $this->createNotification($data);
        try{
            $em = $this->getDoctrine()->getManager();
            $em->persist($notificationModel);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'sentMessage',
                'The notification model associated to this template: "' . $data["templateId"] . '" has been saved.'
            );
        } catch(\Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'The notification model associated to this template: "' . $data["templateId"] . '" has not been saved.'.$e->getMessage()
            );
        }


        return $this->redirectToRoute('nononsense_notifications_templates_notification_list');
    }

    public function listNotificationAction(Request $request) {

        if (!$this->isAllowed('notifications_gestion')) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $filters = [];
        FiltersUtils::paginationFilters($filters, (int) $request->get("page"), self::LIMIT_MANY);
        $paginate = 1;
        $fields = ["templateId"];
        FiltersUtils::requestToFilters($request, $filters, $fields);

        if ($request->get("templateId")) {
            /** @var TMTemplates $tmTemplate */
            $tmTemplate = $this->getDoctrine()->getRepository(TMTemplates::class)->find((int)$request->get("templateId"));

            $array_item["item"]["id"] = (int)$request->get("templateId");
            $array_item["item"]["name"] = $tmTemplate->getName();
        }

        /** @var NotificationsModelsRepository $notificationsModelsRepository */
        $notificationsModelsRepository = $this->getDoctrine()->getRepository(NotificationsModels::class);

        $notificationModels = $notificationsModelsRepository->list($filters, $paginate);

        $array_item["items"] = $this->getNotificationList($notificationModels);

        $array_item["count"] = $notificationsModelsRepository->count($filters);
        $array_item["pagination"] = Utils::getPaginator($request, $filters["limit_many"], $array_item["count"]);

        return $this->render('NononsenseHomeBundle:NotificationsModels:notifications_models_list.html.twig',$array_item);
    }

    public function removeNotificationAction(Request $request) {

        /** @var Users $user */
        $user = $this->container->get('security.context')->getToken()->getUser();

        $notificationModelId = (int) $request->get("notificationModelId");

        /** @var NotificationsModelsRepository $notificationsModelsRepository */
        $notificationsModelsRepository = $this->getDoctrine()->getRepository(NotificationsModels::class);

        /** @var NotificationsModels $notificationModel */
        $notificationModel = $notificationsModelsRepository->find($notificationModelId);
        $notificationModel->setRemovedAt();
        $notificationModel->setRemovedBy($user);
        $notificationModel->setIsRemoved(true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($notificationModel);
        $em->flush();

        return $this->redirectToRoute("nononsense_notifications_templates_notification_list");
    }

    public function detailNotificationAction(Request $request) {

        $notificationModelId = (int) $request->get("notificationModelId");

        /** @var NotificationsModelsRepository $notificationsModelsRepository */
        $notificationsModelsRepository = $this->getDoctrine()->getRepository(NotificationsModels::class);

        /** @var NotificationsModels $notificationModel */
        $notificationModel = $notificationsModelsRepository->find($notificationModelId);

        $notificationDetail = $this->fromNotificationModelToArray($notificationModel);
        $notificationDetail["msg"] = $notificationModel->getBody();
        $notificationDetail["subject"] = $notificationModel->getSubject();

        return $this->render('NononsenseHomeBundle:NotificationsModels:notifications_models_detail.html.twig',
            [
                "notificationModel" => $notificationDetail
            ]
        );
    }

    private function isAllowed($section){

        if (!$this->get('app.security')->permissionSeccion($section)){

            $this->get('session')->getFlashBag()->add('error', 'No tiene permisos suficientes para acceder a esta sección.');

            return false;
        }

        return true;
    }

    private function statusFinder(): array
    {
        /** @var CVStatesRepository $statusesRepository */
        $statusesRepository = $this->getDoctrine()->getRepository(CVStates::class);
        $statuses = $statusesRepository->list();

        return array_map("self::statusToSelect", $statuses);
    }

    private function getNotificationList(array $notificationsModelsIds): array
    {
        $myNotificationsModelsList = [];

        /** @var NotificationsModels $notificationModel */
        foreach($notificationsModelsIds as $notificationsModelsId) {
            $myNotificationsModelsList[] = $this->fromNotificationModelToArray($notificationsModelsId);
        }

        return $myNotificationsModelsList;
    }

    private static function statusToSelect($status): array
    {
        return [
            "id" => $status["id"]
            , "name" => $status["name"]
        ]
        ;
    }

    public function getUsersAction(): JsonResponse
    {
        /** @var UsersRepository $usersRepository */
        $usersRepository = $this->getDoctrine()->getRepository(Users::class);

        return new JsonResponse(["data" => $usersRepository->getUserNames()]);
    }

    public function getGroupsAction(): JsonResponse
    {
        /** @var GroupsRepository $groupsRepository */
        $groupsRepository = $this->getDoctrine()->getRepository(Groups::class);

        return new JsonResponse(["data" =>$groupsRepository->listGroupsForSelect()]);
    }

    private function createNotification($data)
    {
        $notificationModel = new NotificationsModels();

        /** @var TMTemplates $tmTemplate */

        $id = (int)$data["templateId"];
        if ($id === 0) {
            return null;
        }
        try {
            $tmTemplate = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(["id" => $data["templateId"]]);
        } catch(Exception $exc) {
            return null;
        }

        if (is_null($tmTemplate) || empty($tmTemplate)) {
            return null;
        }

        $notificationModel->setTemplateId($tmTemplate);


        /** @var CVStates $cvState */
        $cvState = $this->getDoctrine()->getRepository(CVStates::class)->find($data["stateId"]);

        $notificationModel->setState($cvState);

        $notificationModel->setBody($data["msg"]);
        $notificationModel->setSubject($data["subject"]);

        $type = $data["type"];

        switch($type) {
            case self::USER:
                /** @var Users $user */
                $user = $this->getDoctrine()->getRepository(Users::class)->find($data["collectionId"]);

                $notificationModel->setUser($user);
                $notificationModel->setEmail($user->getEmail());
                break;
            case self::GROUP:
                /** @var Groups $group */
                $group = $this->getDoctrine()->getRepository(Groups::class)->find($data["collectionId"]);
                $notificationModel->setGroup($group);
                break;
            case self::EMAIL:
                $notificationModel->setEmail($data["email"]);
                break;
            default:
                break;
        }

        /** @var Users $createdBy */
        $createdBy = $this->getDoctrine()->getRepository(Users::class)->find($data["createdBy"]);
        $notificationModel->setCreatedBy($createdBy);
        $notificationModel->setCreatedAt(new DateTime());

        return $notificationModel ;
    }

    private function getDestination(NotificationsModels $notificationModel)
    {
        $destination = "";

        if (!is_null($notificationModel->getUser())) {
            /** @var Users $user */
            $user = $this->getDoctrine()->getRepository(Users::class)->find($notificationModel->getUser());
            $destination = $user->getName();
        }

        if (!is_null($notificationModel->getGroup())) {
            /** @var Groups $group */
            $group = $this->getDoctrine()->getRepository(Groups::class)->find($notificationModel->getGroup());
            $destination = $group->getName();
        }

        if (!is_null($notificationModel->getEmail())) {
            $destination = $notificationModel->getEmail();
        }

        return $destination;
    }

    /**
     * @param NotificationsModels $notificationModel
     * @return array
     */
    public function fromNotificationModelToArray(NotificationsModels $notificationModel): array
    {

        /** @var TMTemplates $tmTemplate */
        $tmTemplate = $this->getDoctrine()->getRepository(TMTemplates::class)->find($notificationModel->getTemplateId());

        /** @var CVStates $state */
        $state = $this->getDoctrine()->getRepository(CVStates::class)->find($notificationModel->getState());

        $destination = $this->getDestination($notificationModel);

        if (!is_null($notificationModel->getCreatedBy())) {
            /** @var Users $createdBy */
            $createdBy = $this->getDoctrine()->getRepository(Users::class)->find($notificationModel->getCreatedBy());
            $createdByName = $createdBy->getName();
        } else {
            $createdByName = "-";
        }

        return [
            "id" => $notificationModel->getId()
            , "title" => $tmTemplate->getName()
            , "state" => $state->getName()
            , "destination" => $destination
            , "createdAt" => $notificationModel->getCreatedAt()->getTimestamp()
            , "createdBy" => $createdByName
            , "isRemoved" => $notificationModel->getIsRemoved()
        ];
    }
}