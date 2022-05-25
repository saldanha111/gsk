<?php
declare(strict_types=1);

namespace Nononsense\HomeBundle\Controller;

use DateTime;
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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NotificationsModelsController extends Controller
{

    const GROUP = 1;
    const USER = 2;
    const EMAIL = 3;

    public function showOverviewAction(Request $request)
    {
        if (!$this->isAllowed('crt_gestion')) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $user = $this->container->get('security.context')->getToken()->getUser();

        return $this->render('NononsenseHomeBundle:NotificationsModels:notifications_models_management.html.twig',
            [
                "finder" => [
                    "statuses" => $this->statusFinder()
                ]
            ]
        );

    }

    public function creatorAction(Request $request)
    {
        if (!$this->isAllowed('crt_gestion')) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $user = $this->container->get('security.context')->getToken()->getUser();

        $data = ["templateId"];
        $filters = [];
        FiltersUtils::requestToFilters($request,$filters, $data);

        $templates = $this->templateFinder($filters);
        if(count($templates) < 1) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'La plantilla indicada ya tiene una nueva edición en proceso'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $myTemplate = $this->getMyTemplate($templates);

        return $this->render('NononsenseHomeBundle:NotificationsModels:notifications_models_management.html.twig',
            [
                "finder" => [
                    "statuses" => $this->statusFinder()
                    , "filter" => $filters
                ],
                "data" => [
                    "template" => $myTemplate
                ]
            ]
        );

    }

    public function addNotificationAction(Request $request)
    {
        $userid = $this->getUser()->getId();
        $data = [
            "templateId" => (int)$request->get("templateId")
            , "stateId" => (int)$request->get("state")
            , "msg" => $request->get("msg")
            , "type" => (int)$request->get("type")
            , "collectionId" => (int)$request->get("collectionId")
            , "email" => $request->get("email")
            , "createdBy" => (int)$userid
        ];
        $notificationModel = $this->createNotification($data);

        $em = $this->getDoctrine()->getManager();
        $em->persist($notificationModel);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'sentMessage',
            'The notification model associated to this template: "' . $data["templateId"] . '" has been saved.'
        );

        return $this->redirectToRoute('nononsense_notifications_templates_notification_list');
    }

    public function listNotificationAction(Request $request) {

        return $this->render('NononsenseHomeBundle:NotificationsModels:notifications_models_management.html.twig',
            [
                "finder" => [
                    "statuses" => $this->statusFinder()
                ],
                "list" => $this->getNotificationList()
            ]
        );
    }

    public function removeNotificationAction(Request $request) {

        $notificationModelId = (int) $request->get("notificationModelId");

        /** @var NotificationsModelsRepository $notificationsModelsRepository */
        $notificationsModelsRepository = $this->getDoctrine()->getRepository(NotificationsModels::class);

        /** @var NotificationsModels $notificationModel */
        $notificationModel = $notificationsModelsRepository->find($notificationModelId);
        $notificationModel->setIsRemoved(true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($notificationModel);
        $em->flush();

        return $this->render('NononsenseHomeBundle:NotificationsModels:notifications_models_management.html.twig',
            [
                "finder" => [
                    "statuses" => $this->statusFinder()
                ],
                "list" => $this->getNotificationList()
            ]
        );
    }

    public function detailNotificationAction(Request $request) {

        $notificationModelId = (int) $request->get("notificationModelId");

        /** @var NotificationsModelsRepository $notificationsModelsRepository */
        $notificationsModelsRepository = $this->getDoctrine()->getRepository(NotificationsModels::class);

        /** @var NotificationsModels $notificationModel */
        $notificationModel = $notificationsModelsRepository->find($notificationModelId);

        $notificationDetail = $this->fromNotificationModelToArray($notificationModel);
        $notificationDetail["msg"] = $notificationModel->getBody();

        return $this->render('NononsenseHomeBundle:NotificationsModels:notifications_models_detail.html.twig',
            [
                "notificationModel" => $notificationDetail
            ]
        );
    }

    public function templateFinder(array $filters): array
    {
        $filter = [
            "no_request_in_proccess" => 1
        ];
        if (isset($filters["templateId"])) {
            $filter["id"] = $filters["templateId"];
        }
        if (isset($filters["state"])) {
            $filter["state"] = $filters["state"];
        }

        return $this->getDoctrine()->getRepository('NononsenseHomeBundle:TMTemplates')->listActiveForRequest($filter);

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

    private function getNotificationList(): array
    {
        /** @var NotificationsModelsRepository $notificationsModelsRepository */
        $notificationsModelsRepository = $this->getDoctrine()->getRepository(NotificationsModels::class);

        $notificationsModelsList = $notificationsModelsRepository->findAll();
        $myNotificationsModelsList = [];

        /** @var NotificationsModels $notificationModel */
        foreach($notificationsModelsList as $notificationModel) {
            $myNotificationsModelsList[] = $this->fromNotificationModelToArray($notificationModel);
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

    private function getMyTemplate(array $templates): array
    {
        $template = $templates[0];
        return [
            "id" => $template->getId(),
            "name" => $template->getName(),
            "statusId" => $template->getTmState()->getId()
        ];
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

    private function createNotification(array $data)
    {
        $notificationModel = new NotificationsModels();

        /** @var TMTemplates $tmTemplate */
        $tmTemplate = $this->getDoctrine()->getRepository(TMTemplates::class)->find($data["templateId"]);
        $notificationModel->setTemplateId($tmTemplate);

        /** @var CVStates $cvState */
        $cvState = $this->getDoctrine()->getRepository(CVStates::class)->find($data["stateId"]);
        $notificationModel->setState($cvState);

        $notificationModel->setBody($data["msg"]);

        $type = $data["type"];

        switch($type) {
            case self::USER:
                /** @var Users $user */
                $user = $this->getDoctrine()->getRepository(Users::class)->find($data["collectionId"]);
                $notificationModel->setUser($user);
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