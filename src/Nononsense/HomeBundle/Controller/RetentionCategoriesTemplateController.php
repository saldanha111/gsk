<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\HomeBundle\Entity\Areas;
use Nononsense\HomeBundle\Entity\MaterialCleanCleans;
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
use Symfony\Component\HttpFoundation\Response;

class RetentionCategoriesTemplateController extends Controller
{
    public function searchTemplatesListAction(Request $request)
    {
        $hasPermission = $this->checkPermission();
        if (!$hasPermission) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $DEFAULT_LIMIT = 15;

        $filters["limit_from"] = 0;

        $em = $this->getDoctrine()->getManager();

        $array_item = [];
        $this->getData($array_item);
        $this->checkData($array_item);

        $filters = Utils::getListFilters($request);
        $filters['limit_many'] = $request->get('limit_many') ?? $DEFAULT_LIMIT;

        /** @var TMTemplatesRepository $tmTemplatesRepository */
        $tmTemplatesRepository = $em->getRepository(TMTemplates::class);

        $items = $tmTemplatesRepository->listTemplatesByRetention($filters);
        if (isset($filters["f_destruction_option"]) && ("only_destroyed" === $filters["f_destruction_option"])) {
            $this->getPhysicalDestroyedTemplates($items);
        }

        $templates = $this->parseToView($items);

        $totalItems = $tmTemplatesRepository->count($filters);
        $data = [];
        $hasData = count($items) > 0;

        if ($hasData) {
            $data = [
                'items' => $templates,
            ];
        }

        return $this->render('NononsenseHomeBundle:Retention:list_retention_templates.html.twig'
            , [
                "areas" => $array_item["areas"],
                "retention_categories" => $array_item["retention_categories"],
                "states" => $array_item["states"],
                "retention_representatives" => $array_item["retention_representatives"],
                "filters" => $filters,
                "data" => $data,
                "hasData" => $hasData,
                "pagination" => Utils::getPaginator($request, $filters['limit_many'], $totalItems)
            ]
        );
    }

    public function signAndDestroyAction(Request $request)
    {
        $password = $request->get('password');
        if (!$this->get('utilities')->checkUser($password)) {
            return $this->returnToHomePage("La contraseña no es correcta.");
        }
        $description = $request->get('description');
        if (empty($description)) {
            return $this->returnToHomePage("Tienes que proporcionar una descripción para la firma.");
        }
        $ids = array_map("intval", explode(",", $request->get("ids")));
        if (0 === count($ids)) {
            return $this->returnToHomePage("No se han proporcionado plantillas para ser firmadas.");
        }

        $thereWasError = $this->signThenDestroy($ids, $password, $description);

        if ($thereWasError) {
            return $this->returnToHomePage("Hubo un error cuando se firmaba la destrucción de algunas plantillas.");
        }
        $this->generateBlockchainCertificate($ids, 'Destrucción lógica de plantillas');
        return $this->returnToHomePage("La destrucción de las plantillas fueron firmadas.", "success");

    }

    public function detailTemplateAction(int $id)
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

    public function detailTemplateUpdateAction(Request $request): RedirectResponse
    {
        $hasPermission = $this->checkPermission();
        if (!$hasPermission) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $boundedCategories = array_map('intval', explode(",", $request->get("boundedCategories")));
        $templateId = (int)$request->get("templateId");
        try {
            $this->updateBindingTemplateRetentionCategories($templateId, $boundedCategories);
        } catch (Exception $exception) {
            return $this->returnToHomePage("No se pudieron actualizar las categorías de retención asociadas a la plantilla");
        }

        $route = $this->container->get('router')->generate('nononsense_home_homepage');
        return $this->redirect($route);
    }

//    public function reviewTemplatesAction(Request $request) {
//        return $this->render('NononsenseHomeBundle:Retention:list_review_retention_templates.html.twig',
//            [
//                "areas" => $array_item["areas"],
//                "retention_categories" => $array_item["retention_categories"],
//                "states" => $array_item["states"],
//                "retention_representatives" => $array_item["retention_representatives"],
//                "filters" => $filters,
//                "data" => $data,
//                'hasData' => $hasData,
//                "pagination" =>  Utils::getPaginator($request, $filters['limit_many'], $totalItems)
//            ]
//        );
//    }

    public function annualReviewTemplatesAction(Request $request)
    {
        $templateIDs = array_map('intval', explode(',', $request->get('ids')));
        $hasPermission = $this->checkPermission();
        if (!$hasPermission) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $DEFAULT_LIMIT = 15;

        $filters["limit_from"] = 0;

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
                , "pagination" => Utils::getPaginator($request, $filters['limit_many'], $totalItems)
            ]
        );
    }

    public function signAnnualReviewAction(Request $request): JsonResponse
    {
        $thereWasError = false;
        $password = $request->get('password');
        if (!$this->get('utilities')->checkUser($password)) {
            $thereWasError = true;
            $msg = "La firma no es correcta.";
        }
        $description = $request->get('description');
        if (empty($description)) {
            $thereWasError = true;
            $msg = "Tienes que proporcionar una descripción para la firma.";
        }
        $ids = array_map("intval", explode(",", $request->get("ids")));
        if (0 === count($ids)) {
            $thereWasError = true;
            $msg = "No se han proporcionado plantillas para ser firmadas.";
        }

        $action = $request->get("action");

        $DESTROY = "destroy";

        if ($DESTROY === $action) {
            $thereWasError = $this->signThenDestroy($ids, $password, $description, true);

            if ($thereWasError) {
                $msg = "Hubo un error cuando se firmaban/destruían algunas plantillas.";
            } else {
                $msg = "La destrucción de las plantillas fue firmada correctamente.";
                $this->generateBlockchainCertificate($ids, 'Destrucción lógica de plantillas');
            }

        } else {
            $thereWasError = $this->signThenReview($ids, $password, $description, true);

            if ($thereWasError) {
                $msg = "Hubo un error cuando se marcaban como 'Revisada, no destruidla' algunas plantillas.";
            } else {
                $msg = "Se marcaron correctamente como 'Revisada, no destruidla' las plantillas seleccionadas.";
            }

        }

        return new JsonResponse([
            "result" => !$thereWasError,
            "msg" => $msg
        ]);

    }

    public function markThisTemplateAsReviewedDontDeleteAction(Request $request)
    {
        $hasPermission = $this->checkPermission();
        if (!$hasPermission) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $em = $this->getDoctrine()->getManager();

        $templateId = (int)$request->get("id");
        /** @var TMTemplates $template */
        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->find($templateId);
        try {
            $template->setReviewedDontDestroy();
            $em->persist($template);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                "success",
                "La plantilla ha sido marcada como revisada y no podrá ser destruida"
            );
        } catch (Exception $exc) {
            return $this->returnToHomePage("No se pudo marcar la plantilla como 'Revisada, no destruirla'.", 'error');
        }

        $route = $this->container->get('router')->generate('nononsense_home_homepage');
        return $this->redirect($route);
    }

    public function getTemplatesMarkedToBeDestroyedAction(Request $request)
    {
        $hasPermission = $this->checkPermission();
        if (!$hasPermission) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $em = $this->getDoctrine()->getManager();

        $DEFAULT_LIMIT = 15;

        $filters["limit_from"] = 0;
        $filters['limit_many'] = $request->get('limit_many') ?? $DEFAULT_LIMIT;

        $templateId = (int)$request->get("id");
        /** @var TMTemplates $template */
        $items = $this->getDoctrine()->getRepository(TMTemplates::class)->listTemplatesToBeDeleted($filters);
        $templates = $this->parseToView($items);

        $totalItems = count($templates);
        $data = [];
        $hasData = count($templates) > 0;

        if ($hasData) {
            $data = [
                'items' => $templates,
            ];
        }

        return $this->render('NononsenseHomeBundle:Retention:list_templates_marked_to_be_deleted.html.twig'
            , [
                "data" => $data,
                "hasData" => $hasData,
                "pagination" => Utils::getPaginator($request, $filters['limit_many'], $totalItems)
            ]
        );

    }

    private function getData(array &$array_item)
    {
        $areas = $this->getDoctrine()->getRepository(Areas::class)->findBy(
            [],
            ["name" => "ASC"]
        );
        /** @var Areas $area */
        foreach ($areas as $area) {
            $array_item["areas"][] = [
                "id" => $area->getId(),
                "name" => $area->getName()
            ];
        }

        $retentionCatgories = $this->getDoctrine()->getRepository(RetentionCategories::class)->findBy(
            ["active" => true]
        );
        /** @var RetentionCategories $retentionCatgory */
        foreach ($retentionCatgories as $retentionCategory) {
            $array_item["retention_categories"][] = [
                "id" => $retentionCategory->getId(),
                "name" => $retentionCategory->getName()
            ];
        }

        $APROBADA = 5;
        $EN_VIGOR = 6;
        $OBSOLETA = 7;
        $BAJA = 8;
        $statesToBeRetrieved = [$APROBADA, $EN_VIGOR, $OBSOLETA, $BAJA];
        $states = $this->getDoctrine()->getRepository(TMStates::class)->findBy(["id" => $statesToBeRetrieved]);
        /** @var TMStates $state */
        foreach ($states as $state) {
            $array_item["states"][] = [
                "id" => $state->getId(),
                "name" => $state->getName()
            ];
        }

        $groupUsers = $this->getDoctrine()->getRepository(GroupUsers::class)->findAll();
        $RETENTION_REPRESENTATIVE = 48;
        /** @var GroupUsers $groupUser */
        foreach ($groupUsers as $groupUser) {
//            Utils::debug();
            if ($RETENTION_REPRESENTATIVE === $groupUser->getGroup()->getId()) {
                $array_item["retention_representatives"][] = [
                    "id" => $groupUser->getUser()->getId(),
                    "username" => $groupUser->getUser()->getUsername()
                ];
            }
        }
//
    }

    private function parseToView(array $items): array
    {
        $dataToView = [];
        $today = date("Y-m-d");
        foreach ($items as $item) {
            $toBeDestroyed = is_null($item["destructionDate"]) && !$item["isDeleted"]
                && ($today > $item["finishRetention"])
                && is_null($item["retentionReviewDate"])
                && !$item["reviewedDontDestroy"];
            $dataToView[] = [
                "id" => $item["id"],
                "destroyDate" => $item["destructionDate"],
                "mostRestrictiveCategoryName" => $this->getMostRestrictiveCategoryName((int)$item["id"]),
                "title" => $item["name"],
                "code" => $item["number"],
                "edition" => $item["numEdition"],
                "area" => $item["area"]["name"],
                "state" => $item["tmState"]["name"],
                "retentionRepresentative" => $item["retentionRepresentative"]["username"],
                "startDateRetention" => $item["startRetention"],
                "toBeDestroyed" => $toBeDestroyed,
                "wasReviewed" => !is_null($item["retentionReviewDate"]) && $item["reviewedDontDestroy"],
                "annualReviewDate" => $item["annualReviewDate"]
            ];
        }
        return $dataToView;
    }

    private function parseToReview(array $items): array
    {
        $dataToReview = [];
        /** @var TMTemplates $item */
        foreach ($items as $item) {
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

    private function returnToHomePage(
        string $msg,
        string $type = "error"
    ): RedirectResponse
    {
        $this->get('session')->getFlashBag()->add(
            $type,
            $msg
        );
        $route = $this->container->get('router')->generate('nononsense_home_homepage');

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
            : "";
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
        $today = date("Y-m-d");

        /**
         * @var TMTemplates $template
         */
        $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id" => $templateId));
        $mostRestrictiveRetentionCategory = TMTemplatesService::getTheMostRestrictiveCategoryByTemplateId($template);
        $data["template"]["id"] = $template->getId();
        $data["template"]["destructionDate"] = $template->getDestructionDate();
        $data["template"]["mostRestrictiveRetentionCategory"] = !is_null($mostRestrictiveRetentionCategory)
            ? $mostRestrictiveRetentionCategory->getName()
            : "";
        $data["template"]["name"] = $template->getName();
        $data["template"]["code"] = $template->getNumber();
        $data["template"]["edition"] = $template->getNumEdition();
        $data["template"]["area"] = $template->getArea()->getName();
        $data["template"]["state"] = $template->getTmState()->getName();
        if (!is_null($template->getRetentionRepresentative())) {
            $retentionRepresentativeName = $template->getRetentionRepresentative()->getName();
        } else {
            $retentionRepresentativeName = "";
        }
        $data["template"]["retentionRepresentative"] = $retentionRepresentativeName;
        $data["template"]["owner"] = $template->getOwner()->getName();
        $data["template"]["backup"] = $template->getBackup()->getName();
        $data["template"]["effectiveDate"] = $template->getEffectiveDate();
        $data["template"]["stateDate"] = $template->getStateDate();

        $data["retentionCategories"] = $this->getDoctrine()->getRepository(RetentionCategories::class)->getActiveRetentionCategories();

        $retentions = $template->getRetentions();
        /** @var RetentionCategories $retention */
        $data["template"]["boundedRetentionCategories"] = [];
        foreach ($retentions as $retention) {
            if ($retention->getActive()) {
                $data["template"]["boundedRetentionCategories"][] = [
                    "id" => $retention->getId(),
                    "name" => $retention->getName(),
                    "retentionTime" => $retention->getRetentionDays()
                ];
            }
        }

        $data["template"]["toggleDestructionButton"] = is_null($template->getDestructionDate()) && ($today > $template->getFinishRetention());
        $data["template"]["annuallyReviewed"] = !is_null($template->getAnnualReviewDate());

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
            /** @var RetentionCategories $boundedRetentionCategoryID */
            foreach ($boundedRetentionCategories as $boundedRetentionCategoryID) {
                /** @var RetentionCategories $boundedRetentionCategory */
                $boundedRetentionCategory = $this->getDoctrine()->getRepository(RetentionCategories::class)->find($boundedRetentionCategoryID);
                $template->addRetention($boundedRetentionCategory);
            }

            // Once I have re-assigned new retention categories to this template, I have to recalculate
            // what is its new retention start date. This date is obtained from the most restrictive
            // retention category from their new ones.

            $theMostRestrictiveBoundedCategory = TMTemplatesService::getTheMostRestrictiveCategoryByTemplateId($template);
            if (!is_null($theMostRestrictiveBoundedCategory)) {
                $template->setFinishRetention($theMostRestrictiveBoundedCategory->getRetentionDays());
                $template->setDestructionDateByDays($theMostRestrictiveBoundedCategory->getRetentionDays());
            } else {
                $template->setFinishRetentionByDate(null);
                $template->setDestructionDate(null);
            }


            $em->persist($template);
            $em->flush();
        }
    }

    private function toSign($template, string $password, string $description, string $signatureMeaning): TMSignatures
    {

        $base_url = $this->getParameter('api_docoaro') . "/documents/" . $template->getPlantillaId();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Api-Key: " . $this->getParameter('api_key_docoaro')));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $raw_response = curl_exec($ch);
        $response = json_decode($raw_response, true);

        $signature = new TMSignatures();
        $signature->setTemplate($template);
        /** @var TMActions $destructionAction * */
        $destructionAction = $this->getDoctrine()->getRepository(TMActions::class)->findOneBy(array("id" => "18"));
        $signature->setAction($destructionAction);
        $user = $this->container->get('security.context')->getToken()->getUser();
        $signature->setUserEntiy($user);
        $signature->setCreated(new \DateTime());
        $signature->setModified(new \DateTime());
        $signature->setSignature($password);
        $signature->setVersion($response["version"]["id"]);
        $signature->setConfiguration($response["version"]["configuration"]["id"]);
        $signature->setDescription($signatureMeaning);
        $signature->setUserText($description);

        return $signature;
    }


    private function signThenDestroy(array $ids, string $password, string $description, bool $annualReviewed = false): bool
    {
        $DESTRUCTION_MEANING = "Los registros han alcanzado su tiempo de retención no estando ligados a una preservation notices, por lo que se procede a su destrucción";
        $thereWasError = false;
        $DESTROY_ON_RETENTION_ID = 15;
        /** @var TMStates $destroyOnRetentionState * */
        $destroyOnRetentionState = $this->getDoctrine()->getRepository(TMStates::class)->find(["id" => $DESTROY_ON_RETENTION_ID]);
        foreach ($ids as $id) {
            $em = $this->getDoctrine()->getManager();
            /** @var TMTemplates $template * */
            $template = $em->getRepository(TMTemplates::class)->find($id);
            if ($template) {
                try {
                    $signature = $this->toSign($template, $password, $description, $DESTRUCTION_MEANING);
                    $em->persist($signature);
                    if ($annualReviewed) {
                        $template->setAnnualReviewDate(new DateTime());
                    }
                    $template->setDestructionDate(new DateTime());
                    $template->setTmState($destroyOnRetentionState);
                    $em->persist($template);
                    $em->flush();
                } catch (Exception $exception) {
                    $thereWasError = true;
                }
            }
        }

        return $thereWasError;
    }

    private function signThenReview(array $ids, string $password, string $description, bool $annualReviewed): bool
    {
        $thereWasError = false;
        foreach ($ids as $id) {
            $em = $this->getDoctrine()->getManager();
            /** @var TMTemplates $template * */
            $template = $em->getRepository(TMTemplates::class)->find($id);
            if ($template) {
                $signature = $this->toSign($template, $password, $description, "Sin información");
                try {
                    $em->persist($signature);
                    $template->setReviewedDontDestroy();
                    if ($annualReviewed) {
                        $template->setAnnualReviewDate(new DateTime());
                    }
                    $em->persist($template);
                    $em->flush();

                    $this->generateBlockchainCertificate($ids, 'Revisión anual de plantillas');
                } catch (Exception $exception) {
                    $thereWasError = true;
                }
            }
        }

        return $thereWasError;
    }

    private function generateBlockchainCertificate(array $ids, string $subject)
    {
        try{
            $html =  (1 === count($ids)) ? $this->generateHTMLDetail($ids[0]) : $this->generateHTMLTable($ids);
            $orientation = (1 === count($ids)) ? "vertical" : "landscape";
            $file = Utils::generatePdf($this->container, 'GSK - Retención y destrucción', $subject, $html, 'retention', $this->getParameter('crt.root_dir'), $orientation, true);
//            $response = new Response();
//            $content = file_get_contents($this->getParameter('crt.root_dir').$file);
//            //set headers
//            $response->headers->set('Content-Type', 'mime/type');
//            $response->headers->set('Content-Disposition', 'attachment;filename="'.$file);
//
//            $response->setContent($content);
//            return $response;
                Utils::setCertification($this->container, $file, 'retention', $template->getId());
//            }
            $this->get('session')->getFlashBag()->add('message',"Se ha generado satisfactoriamente el re");

        }
        catch(\Exception $e){
            $this->get('session')->getFlashBag()->add('error', $e->getMessage());
        }
    }

    private function generateHTMLDetail(int $id): string {
        $em = $this->getDoctrine()->getManager();
        /** @var TMTemplates $template * */
        $template = $em->getRepository(TMTemplates::class)->find($id);
        $retentionRepresentativeName = !is_null($template->getRetentionRepresentative())
                                        ? $template->getRetentionRepresentative()->getName()
                                        : ""
        ;
        $htmlSinCategoriasDeRetencion =  "
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Fecha de destrucción</td>
                    <td>" . $template->getDestructionDate()->format('d/m/Y') . "</td>
                </tr>
                <tr>
                    <td>Categ. de Retención más restrictiva</td>
                    <td>" . $this->getMostRestrictiveCategoryName($id) . "</td>
                </tr>
                <tr>
                    <td>Título</td>
                    <td>" . $template->getName() . "</td>
                </tr>
                <tr>
                    <td>Código</td>
                    <td>" . $template->getNumber() . "</td>
                </tr>
                <tr>
                    <td>Edición</td>
                    <td>" . $template->getNumEdition() . "</td>
                </tr>
                <tr>
                    <td>Área</td>
                    <td>" . $template->getArea()->getName() . "</td>
                </tr>
                <tr>
                    <td>Estado</td>
                    <td>" . $template->getTmState()->getName() . "</td>
                </tr>
                <tr>
                    <td>Fecha del estado</td>
                    <td>" . $template->getStateDate()->format('d-m-Y') . "</td>
                </tr>
                <tr>
                    <td>Dueño</td>
                    <td>" . $template->getOwner()->getName() . "</td>
                </tr>
                <tr>
                    <td>Backup dueño</td>
                    <td>" . $template->getBackup()->getName() . "</td>
                </tr>
                <tr>
                    <td>Representante de Retención</td>
                    <td>" . $retentionRepresentativeName . "</td>
                </tr>
                <tr>
                    <td>Fecha efectiva</td>
                    <td>" . $template->getEffectiveDate()->format('d-m-Y') . "</td>
                </tr>
                <tr>
                    <td>Categorías de Retención </td>
                    <td></td>
                </tr>                
            </tbody>
        </table>
        %s
        " . $this->getReportDate()
        ;
        $categoriasDeRetencion = "
            <table>
                <thead>
                    <tr>
                        <th><strong>Nombre</strong></th>
                        <th><strong>Tiempo de Retención</strong></th>
                    </tr>                
                </thead>
                <tbody> 
                    %s 
                </tbody>
            </table>
        "
        ;
        $retentionCategoriesLinkedToThisTemplate = $template->getRetentions();
        $retentionCategoryRows = "";
        forEach($retentionCategoriesLinkedToThisTemplate as $retentionCategoryLinkedToThisTemplate) {
            $retentionCategoryRows .= "
                    <tr>
                        <td>" . $retentionCategoryLinkedToThisTemplate->getName() . "</td>
                        <td>" . $retentionCategoryLinkedToThisTemplate->getRetentionDays() . " días</td>
                    </tr>
            "
            ;
        }
        $retentionCategories = sprintf($categoriasDeRetencion, $retentionCategoryRows);
        return sprintf($htmlSinCategoriasDeRetencion, $retentionCategories);
    }

    private function generateHTMLTable(array $ids): string {
        $em = $this->getDoctrine()->getManager();
        $htmlTableTemplate = "
            <table style='font-size:20%%'>
                <thead>
                    <tr>
                        <th>Fecha de Destrucción</th>
                        <th>Título</th>
                        <th>Categoría de Retención más restrictiva</th>
                        <th>Código</th>
                        <th>Edición</th>
                        <th>Área</th>
                        <th>Estado</th>
                        <th>Representante de Retención</th>
                        <th>Fecha inicio período de retención</th>
                        <th>Fecha de revisión anual</th>
                    </tr>
                </thead>
                <tbody>
                    %s
                </tbody>
            </table>
        " . $this->getReportDate()
        ;
        $html = "";
        foreach($ids as $id) {
            /** @var TMTemplates $template * */
            $template = $em->getRepository(TMTemplates::class)->find($id);
            $mostRestrictiveCategoryName = $this->getMostRestrictiveCategoryName($id);
            $retentionRepresentativeName = !is_null($template->getRetentionRepresentative())
                                            ? $template->getRetentionRepresentative()->getName()
                                            : ""
            ;
            $startRetentionDate = !is_null($template->getStartRetention())
                ? $template->getStartRetention()->format("d-m-Y")
                : ""
            ;
            $endRetentionDate = !is_null($template->getFinishRetention())
                ? $template->getFinishRetention()->format("d-m-Y")
                : ""
            ;
            $annualReviewDate = !is_null($template->getAnnualReviewDate())
                ? $template->getAnnualReviewDate()->format("d-m-Y")
                : ""
            ;
            $html .= "
                <tr style='font-size: xx-small'>
                    <td style='font-size: xx-small'>" . $template->getDestructionDate()->format('d/m/Y') . "</td>
                    <td style='font-size: xx-small'>" . $template->getName() . "</td>
                    <td style='font-size: xx-small'>" . $mostRestrictiveCategoryName . "</td>
                    <td style='font-size: xx-small'>" . $template->getNumber() . "</td>
                    <td style='font-size: xx-small'>" . $template->getNumEdition() . "</td>
                    <td style='font-size: xx-small'>" . $template->getArea()->getName() . "</td>
                    <td style='font-size: xx-small'>" . $template->getTmState()->getName() . "</td>
                    <td style='font-size: xx-small'>" . $retentionRepresentativeName . "</td>
                    <td style='font-size: xx-small'>" . $startRetentionDate . "</td>
                    <td style='font-size: xx-small'>" . $endRetentionDate . "</td>
                    <td style='font-size: xx-small'>" . $annualReviewDate . "</td>
                </tr>
            "
            ;
        }
        return sprintf($htmlTableTemplate, $html);
    }

    private function getReportDate(): string
    {
        return "
        <hr>
        <span style='font-size: xx-small'>Fecha de generación de este informe: ". date("d-m-Y h:i:s") . "</span>
        "
        ;
    }

}