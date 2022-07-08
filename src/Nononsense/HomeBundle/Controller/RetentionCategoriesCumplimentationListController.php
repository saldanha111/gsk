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

class RetentionCategoriesCumplimentationListController extends Controller
{

    public function searchCumplimentationsListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $is_valid = $this->get('app.security')->permissionSeccion('retention_admin');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $defaultLimit = 15;
        $filters = Utils::getListFilters($request);

        $filters['limit_many'] = $request->get('limit_many') ?? $defaultLimit;

        $array_item=[];
        $this->getData($array_item);
        $this->checkData($array_item);

        /** @var CVRecordsRepository $cvRecordsRepository */
        $cvRecordsRepository = $em->getRepository(CVRecords::class);
        $cumplimentations = $cvRecordsRepository->findAll();
        /** @var CVRecords $cumplimentation */
        forEach($cumplimentations as $cumplimentation) {
            $template = $cumplimentation->getTemplate();
        }

        $items = $cvRecordsRepository->listCVRecordsByRetention($filters);
        $cvRecords = $this->parseToView($items);
        $totalItems = $cvRecordsRepository->count($filters);
        $data = [
            'items' => $cvRecords,
        ];
        return $this->render('NononsenseHomeBundle:Retention:list_retention_cumplimentations.html.twig',
            [
                "areas" => $array_item["areas"],
                "retention_categories" => $array_item["retention_categories"],
                "states" => $array_item["states"],
                "retention_representatives" => $array_item["retention_representatives"],
                "filters" => $filters,
                "data" => $data,
                "pagination" =>  Utils::getPaginator($request, $filters['limit_many'], $totalItems),
                'count' => $totalItems
            ]
        );
    }

    /**
     * @param array $array_item
     */
    public function getData(array &$array_item)
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





    public function destroyTemplateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $idsToBeMarkedAsDestroyed = array_map('intval', explode(',', $request->get('ids')));

        $templates = $this->getDoctrine()->getRepository(TMTemplates::class)->findBy(["id" => $idsToBeMarkedAsDestroyed]);

        $thereWasErrors = false;
        $msg = "Todas las plantillas fueron marcadas para su destrucción";
        /**
         * @var TMTemplates $template
         */
        forEach($templates as $template) {
            $template->setDestructionDate(new DateTime());
            try {
                $em->persist($template);
                $em->flush();
            } catch(Exception $exc) {
                $thereWasErrors = true;
            }
        }

        if ($thereWasErrors) {
            $msg = "Algunas plantillas no se marcaron para su destrucción";

        }
        return new JsonResponse([
            "result" => (!$thereWasErrors),
            "message" => $msg
        ]);
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



    public function detailTemplateAction (int $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('admin_gp');
        if(!$is_valid){
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos suficientes'
            );
            $route=$this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $data = $this->getDataFromTemplate($id);

        return $this->render('NononsenseHomeBundle:Retention:template_detail.html.twig',
            ["template" => $data["template"]]
        );

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

    private function hayDatos(array $areas, array $states, array $retention_representatives)  {
        return (count($areas) > 0 && count($states) > 0 && count($retention_representatives) > 0);
    }

//    private function getRetentionCategoriesByTemplateId(int $templateId): array
//    {
//        return $this->getDoctrine()->getRepository(RetentionCategories::class)
//            ->getRetentionCategoriesByTemplateId($templateId);
//    }

    /**
     * @param array $array_item
     * @return void
     */
    private function checkData(array $array_item): void
    {
        if (!$this->hayDatos($array_item["areas"], $array_item["states"], $array_item["retention_representatives"])) {
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
//        $data["template"]["retentionUser"] = $template->getRetentionUser()->getName()
        $data["template"]["owner"] = $template->getOwner()->getName();
        $data["template"]["backup"] = $template->getBackup()->getName();
        $data["template"]["effectiveDate"] = $template->getEffectiveDate();
        $data["template"]["stateDate"] = $template->getStartRetention();

        return $data;
    }
}
