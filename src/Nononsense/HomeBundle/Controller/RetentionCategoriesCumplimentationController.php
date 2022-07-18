<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\HomeBundle\Entity\Areas;
use Nononsense\HomeBundle\Entity\CVRecords;
use Nononsense\HomeBundle\Entity\CVRecordsRepository;
use Nononsense\HomeBundle\Entity\CVStates;
use Nononsense\HomeBundle\Entity\RCStates;
use Nononsense\HomeBundle\Entity\RetentionCategories;
use Nononsense\HomeBundle\Entity\TMActions;
use Nononsense\HomeBundle\Entity\TMSignatures;
use Nononsense\HomeBundle\Entity\TMStates;
use Nononsense\HomeBundle\Entity\TMTemplates;
use Nononsense\HomeBundle\Entity\TMTemplatesRepository;
use Nononsense\HomeBundle\Entity\TMWorkflow;
use Nononsense\HomeBundle\Services\TMTemplatesService;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RetentionCategoriesCumplimentationController extends Controller
{

    public function searchCumplimentationsListAction(Request $request)
    {
        $hasPermission = $this->checkPermission();
        if (!$hasPermission) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $DEFAULT_LIMIT = 15;

        $filters["limit_from"]=0;

        $em = $this->getDoctrine()->getManager();

        $array_item = [];
        $this->getData($array_item);
        $this->checkData($array_item);

        $filters = Utils::getListFilters($request);
        $filters['limit_many'] = $request->get('limit_many') ?? $DEFAULT_LIMIT;

        /** @var CVRecordsRepository $cvRecordsRepository */
        $cvRecordsRepository = $em->getRepository(CVRecords::class);

        $items = $cvRecordsRepository->listCVRecordsByRetention($filters);
        if (isset($filters["f_destruction_option"]) && ("only_destroyed" === $filters["f_destruction_option"])) {
            $this->getPhysicalDestroyedTemplates($items);
        }

        $cvRecords = $this->parseToView($items);

        $totalItems = $cvRecordsRepository->count($filters);
        $data = [];
        $hasData = count($items) > 0;

        if ($hasData)
        {
            $data = [
                'items' => $cvRecords,
            ];
        }

        return $this->render('NononsenseHomeBundle:Retention:list_retention_cumplimentations.html.twig',
            [
                "areas" => $array_item["areas"],
                "retention_categories" => $array_item["retention_categories"],
                "states" => $array_item["states"],
                "retention_representatives" => $array_item["retention_representatives"],
                "filters" => $filters,
                "data" => $data,
                'hasData' => $hasData,
                "pagination" =>  Utils::getPaginator($request, $filters['limit_many'], $totalItems)
            ]
        );
    }

    public function destroyTemplateAction(Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();

        $idsToBeMarkedAsDestroyed = array_map('intval', explode(',', $request->get('ids')));

        $templates = $this->getDoctrine()->getRepository(TMTemplates::class)->findBy(["id" => $idsToBeMarkedAsDestroyed]);

        $thereWasError = false;
        /**
         * @var TMTemplates $template
         */
        forEach($templates as $template) {
            $template->setDestructionDate(new DateTime());
            try {
                $em->persist($template);
                $em->flush();
            } catch(Exception $exc) {
                $thereWasError = true;
            }
        }

        $msg = ($thereWasError)
            ? "Algunas plantillas no se marcaron para su destrucción"
            : "Todas las plantillas fueron marcadas para su destrucción";

        return new JsonResponse([
            "result" => (!$thereWasError),
            "message" => $msg
        ]);
    }

    public function detailTemplateAction (int $id)
    {
        $hasPermission = $this->checkPermission();
        if (!$hasPermission) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $data = $this->getDataFromTemplate($id);

        return $this->render('NononsenseHomeBundle:Retention:template_detail.html.twig',
            [
                "template" => $data["template"],
                "retentionCategories" => $data["retentionCategories"] ?? []
            ]
        );

    }

    public function detailTemplateUpdateAction (Request $request): RedirectResponse
    {
        $hasPermission = $this->checkPermission();
        if (!$hasPermission) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $boundedCategories = array_map('intval', explode(",", $request->get("boundedCategories")));
        $templateId = (int)$request->get("templateId");
        try {
            $this->updateBindingTemplateRetentionCategories($templateId, $boundedCategories);
        } catch(Exception $exception) {
            return $this->returnToHomePage("No se pudieron actualizar las categorías de retención asociadas a la plantilla");
//            return $this->returnToHomePage($exception->getMessage());
        }
//        $is_valid = $this->get('app.security')->permissionSeccion('admin_gp');
//        if(!$is_valid){
//            $this->get('session')->getFlashBag()->add(
//                'error',
//                'No tiene permisos suficientes'
//            );
//            $route=$this->container->get('router')->generate('nononsense_home_homepage');
//            return $this->redirect($route);
//        }
//
//        $data = $this->getDataFromTemplate($id);

        $route=$this->container->get('router')->generate('nononsense_home_homepage');
        return $this->redirect($route);

    }

    public function reviewCumplimentationsAction(Request $request)
    {
        $templateIDs = array_map('intval', explode(',', $request->get('ids')));
        $hasPermission = $this->checkPermission();
        if (!$hasPermission) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $DEFAULT_LIMIT = 15;

        $filters["limit_from"]=0;

        $filters = Utils::getListFilters($request);
        $filters['limit_many'] = $request->get('limit_many') ?? $DEFAULT_LIMIT;

        /** @var TMTemplatesRepository $tmTemplatesRepository */
        $em = $this->getDoctrine()->getManager();
        $tmTemplatesRepository = $em->getRepository(TMTemplates::class);

        $items = $tmTemplatesRepository->listTemplatesToReview($filters, $templateIDs);
        $templates = $this->parseToReview($items);

        $totalItems = count($items);
        $hasData = $totalItems > 0;
        return $this->render('NononsenseHomeBundle:Retention:list_retention_review_templates.html.twig'
            , [
                "items" => $templates
                , "hasData" => $hasData
                , "pagination" =>  Utils::getPaginator($request, $filters['limit_many'], $totalItems)
            ]
        );
    }

    public function signAnnualReviewAction(Request $request) {
//
//        if(!$this->get('app.security')->permissionSeccion('dueno_gp') && !$this->get('app.security')->permissionSeccion('elaborador_gp')){
//            return $this->returnToHomePage("No tiene permisos suficientes");
//        }
//
//        $password =  $request->get('password');
//        if(!$password || !$this->get('utilities')->checkUser($password)){
//            return $this->returnToHomePage("No se pudo firmar el registro, la contraseña es incorrecta");
//        }
//
//        $templates = $this->getDoctrine()->getRepository(TMTemplates::class)->findBy(["destructionDate" => new DateTime()]);
//
//        /**
//         * @var TMTemplates $template
//         */
//        forEach($templates as $template) {
//            if(!empty($template->getDateReview()) && $template->getDateReview()>date("Y-m-d")){
//                return $this->returnToHomePage("No se puede realizar una solicitud de esta plantilla puesto que aún ha llegado la fecha de su revisión periódica");
//            }
////            if($template){
//            $action = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 12));
//            if($action){
//                $this->get('session')->getFlashBag()->add('message','La solicitud de revisión ha sido tramitada');
//                $previous_signature = $this->getDoctrine()->getRepository(TMSignatures::class)->findOneBy(array("template"=>$template),array("id" => "ASC"));
//                if($template->getTmState()->getId()==6){
//                    $signature = new TMSignatures();
//                    $signature->setTemplate($template);
//                    $signature->setAction($action);
//                    $signature->setUserEntiy($user);
//                    $signature->setCreated(new \DateTime());
//                    $signature->setModified(new \DateTime());
//                    $signature->setSignature();
//                    $signature->setVersion($previous_signature->getVersion());
//                    $signature->setConfiguration($previous_signature->getConfiguration());
//                    if($request->get("description")){
//                        $signature->setDescription($request->get("description"));
//                    }
//                    $em->persist($signature);
//                    $template->setRequestReview($signature);
//                    $em->persist($template);
//
//                    $users_notifications=array();
//                    $action_elab = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => 2));
//                    $elabs = $this->getDoctrine()->getRepository(TMWorkflow::class)->findBy(array("template" => $template, "action" => $action_elab));
//                    foreach($elabs as $elab){
//                        if($elab->getUserEntiy()){
//                            $users_notifications[]=$elab->getUserEntiy()->getEmail();
//                        }
//                        else{
//                            foreach($elab->getGroupEntiy()->getUsers() as $user_group){
//                                $users_notifications[]=$user_group->getUser()->getEmail();
//                            }
//                        }
//                    }
//
//                    $subject="Solicitud de revisión";
//                    $mensaje='Se ha tramitado la solicitud de revisión para la plantilla con Código '.$template->getNumber().' - Título: '.$template->getName().' - Edición: '.$template->getNumEdition().'. Para poder revisar dicha soliciutd puede acceder a "Gestión de plantillas -> Solicitudes de revisiones", buscar la plantilla correspondiente y pulsar en Tramitar';
//                    $baseURL=$this->container->get('router')->generate('nononsense_tm_template_detail_review', array("id" => $id),TRUE);
//                    foreach($users_notifications as $email){
//                        $this->get('utilities')->sendNotification($email, $baseURL, "", "", $subject, $mensaje);
//                    }
//
//                    $em->flush();
//
//                    $route=$this->container->get('router')->generate('nononsense_home_homepage');
//                    return $this->redirect($route);
//                }
//
//            }
//        }
//        return $this->returnToHomePage("No se ha podido efectuar la operación sobre la plantilla especificada. Es posible que ya se haya realizado una acción sobre ella o que la plantilla ya no exista");
    }

    private function getData(array &$array_item)
    {
        $areas = $this->getDoctrine()->getRepository(Areas::class)->findBy(
            [],
            ["name" => "ASC"]
        );
        /** @var Areas $area */
        foreach($areas as $area) {
            $array_item["areas"][] = [
                 "id" => $area->getId(),
                 "name" => $area->getName()
            ];
         }

        $retentionCatgories = $this->getDoctrine()->getRepository(RetentionCategories::class)->findBy(
            ["active" => true]
        );
        /** @var RetentionCategories $retentionCatgory */
        foreach($retentionCatgories as $retentionCategory) {
            $array_item["retention_categories"][] = [
                "id" => $retentionCategory->getId(),
                "name" => $retentionCategory->getName()
            ];
        }
        $array_item["states"] =  $this->getDoctrine()->getRepository(RCStates::class)->getRetentionCumplimentationStates();

        $array_item["retention_representatives"] = $this->getDoctrine()->getRepository(Users::class)->findAll();
    }

    private function parseToView(array $items): array
    {
        $dataToView = [];
        foreach($items as $item) {
            $dataToView[] = [
                "id" => $item["id"],
                "destroyDate" => $item["template"]["destructionDate"],
                "mostRestrictiveCategoryName" => $this->getMostRestrictiveCategoryName((int) $item["template"]["id"]),
                "title" => $item["template"]["name"],
                "code" => $item["template"]["number"],
                "edition" => $item["template"]["numEdition"],
                "area" => $item["template"]["area"]["name"],
                "state" => $item["state"]["name"],
                "startDateRetention" => $item["template"]["startRetention"],
                "toggleDestructionButton" => (date("Y-m-d") > $item["template"]["finishRetention"])
            ];
        }
        return $dataToView;
    }

    private function parseToReview(array $items): array
    {
        $dataToReview = [];
        /** @var TMTemplates $item */
        foreach($items as $item) {
            /** @var Users $owner */
            $id = $item["id"];

            $dataToReview[] = $this->getDataFromTemplate($id);

        }
        return $dataToReview;
    }

    private function checkPermission()
    {
        $is_valid = $this->get('app.security')->permissionSeccion('retention_admin');
        if (!$is_valid) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos suficientes'
            );
        }
        return $is_valid;
    }

    private function returnToHomePage(string $msgError, string $type = "error"): RedirectResponse
    {
        $this->get('session')->getFlashBag()->add(
            $type,
            $msgError
        );
        $route=$this->container->get('router')->generate('nononsense_home_homepage');
        return $this->redirect($route);
    }

    private function getMostRestrictiveCategoryName(int $templateId): string
    {
        /**
         * @var TMTemplates $template
         */
        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->find($templateId);
        $mostRestrictiveCategory = TMTemplatesService::getTheMostRestrictiveCategoryByTemplateId($template);

        return (!is_null($mostRestrictiveCategory))
            ? $mostRestrictiveCategory->getName()
            : ""
            ;
    }

    private function hasData(array $areas, array $retentionCategories, array $states, array $retention_representatives): bool
    {
        return (count($areas) > 0 && count($retentionCategories) > 0 && count($states) > 0 && count($retention_representatives) > 0);
    }

    private function checkData(array $array_item)
    {
        if (!$this->hasData($array_item["areas"], $array_item["retention_categories"], $array_item["states"], $array_item["retention_representatives"])) {
            $this->addFlash("error", "No se pudieron cargar los datos para la búsqueda");
            $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }
    }

    private function getDataFromTemplate(int $templateId): array
    {
        /**
         * @var TMTemplates $template
         */
        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $templateId));

        $data["template"]["id"] = $template->getId();
        $data["template"]["destructionDate"] = $template->getDestructionDate();
        $data["template"]["mostRestrictiveRetentionCategory"] = TMTemplatesService::getTheMostRestrictiveCategoryByTemplateId($template)->getName();
        $data["template"]["name"] = $template->getName();
        $data["template"]["code"] = $template->getNumber();
        $data["template"]["edition"] = $template->getNumEdition();
        $data["template"]["area"] = $template->getArea()->getName();
        $data["template"]["state"] = $template->getTmState()->getName();
        $data["template"]["owner"] = $template->getOwner()->getName();
        $data["template"]["backup"] = $template->getBackup()->getName();
        $data["template"]["effectiveDate"] = $template->getEffectiveDate();
        $data["template"]["stateDate"] = $template->getStartRetention();

        return $data;
    }

    /**
     * @throws Exception
     */
    private function updateBindingTemplateRetentionCategories(int $templateID, array $boundedRetentionCategories)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var TMTemplates $template */
        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->find($templateID);
        $template->clearRetentions();
        if (!empty($boundedRetentionCategories)) {
            /** @var RetentionCategories  $boundedRetentionCategoryID*/
            forEach($boundedRetentionCategories as $boundedRetentionCategoryID) {
                /** @var RetentionCategories $boundedRetentionCategory */
                $boundedRetentionCategory = $this->getDoctrine()->getRepository(RetentionCategories::class)->find($boundedRetentionCategoryID);
                $template->addRetention($boundedRetentionCategory);
            }

            // Once I have re-assigned new retention categories to this template, I have to recalculate
            // what its new retention start date is. This date is obtained from the most restrictive
            // retention category from their new ones.

            $theMostRestrictiveBoundedCategory = TMTemplatesService::getTheMostRestrictiveCategoryByTemplateId($template);

            $template->setFinishRetentionByDate($theMostRestrictiveBoundedCategory->getRetentionPeriodEndDate());

            $em->persist($template);
            $em->flush();
        }
    }
}
