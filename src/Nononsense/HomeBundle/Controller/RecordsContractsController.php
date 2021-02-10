<?php

namespace Nononsense\HomeBundle\Controller;

use Aws\Result;
use Aws\Sns\SnsClient;
use DateInterval;
use DateTime;
use Exception;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\GroupBundle\Entity\GroupUsersRepository;
use Nononsense\HomeBundle\Entity\RecordsContracts;
use Nononsense\HomeBundle\Entity\RecordsContractsPinComite;
use Nononsense\HomeBundle\Entity\RecordsContractsPinComiteRepository;
use Nononsense\HomeBundle\Entity\RecordsContractsRepository;
use Nononsense\HomeBundle\Entity\RecordsContractsSignatures;
use Nononsense\HomeBundle\Entity\ContractsTypes;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\UserBundle\Entity\Users;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RecordsContractsController extends Controller
{
    public const VERSION_DIRECTOR = 1; // Versión del contrato para el Director de rrhh.
    public const VERSION_COMMISSION = 2; // Versión del contrato para el comité de rrhh.
    public const WORKER_SIGN_COMISSION = 4; // Número de firma del trabajador para el contrato del comité de rrhh.
    public const WORKER_SIGN_DIRECTOR = 3; // Número de firma del trabajador para el contrato del director de rrhh.

    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('contratos_gestion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $user = $this->container->get('security.context')->getToken()->getUser();
        $Egroups = $this->getDoctrine()
            ->getRepository('NononsenseGroupBundle:GroupUsers')
            ->findBy(array("user" => $user));

        $filters = array();
        $filters["user"] = $user;
        $array_item["suser"]["id"] = $user->getId();

        $groups = [];
        foreach ($Egroups as $group) {
            $groups[] = $group->getGroup()->getId();
        }

        if ($groups) {
            $filters["groups"] = $groups;
        }

        if ($request->get("pending_for_me")) {
            $filters["pending_for_me"] = $request->get("pending_for_me");
        }

        if (!$request->get("export_excel")) {
            $paginate = 1;
            if ($request->get("page")) {
                $filters["limit_from"] = $request->get("page") - 1;
            } else {
                $filters["limit_from"] = 0;
            }
            $filters["limit_many"] = 15;
        } else {
            $paginate = 0;
        }

        if ($request->get("content")) {
            $filters["content"] = $request->get("content");
        }

        if ($request->get("name")) {
            $filters["name"] = $request->get("name");
        }

        if ($request->get("type")) {
            $filters["type"] = $request->get("type");
        }

        if ($request->get("status")) {
            $filters["status"] = $request->get("status");
        }

        if ($request->get("from")) {
            $filters["from"] = $request->get("from");
        }

        if ($request->get("until")) {
            $filters["until"] = $request->get("until");
        }

        /** @var RecordsContractsRepository $rescordsContractsRepository */
        $rescordsContractsRepository = $this->getDoctrine()->getRepository(RecordsContracts::class);

        $array_item["states"][0] = "Creado";
        $array_item["states"][1] = "Dirección RRHH";
        $array_item["states"][2] = "En comité";
        $array_item["states"][3] = "Para enviar";
        $array_item["states"][4] = "Firmado";
        $array_item["suser"]["groups"] = $groups;
        $array_item["filters"] = $filters;
        $array_item["types"] = $this->getDoctrine()->getRepository(ContractsTypes::class)->findAll();
        $array_item["items"] = $rescordsContractsRepository->list($filters, $paginate);
        $array_item["count"] = $rescordsContractsRepository->count($filters);

        $group_direccion_rrhh = $this->getDoctrine()
            ->getRepository(Groups::class)
            ->find($this->getParameter("group_id_direccion_rrhh"));
        $array_item['group_direccion_rrhh'] = $group_direccion_rrhh;

        $group_comite_rrhh = $this->getDoctrine()
            ->getRepository(Groups::class)
            ->find($this->getParameter("group_id_comite_rrhh"));
        $array_item['group_comite_rrhh'] = $group_comite_rrhh;

        /** @var GroupUsersRepository $groupUsersRepository */
        $groupUsersRepository = $this->getDoctrine()->getRepository(GroupUsers::class);
        $isGroupDirectionOrAdmin = $groupUsersRepository->isMemberOfAnyGroup(
            $user->getId(),
            [$this->getParameter("group_id_direccion_rrhh"),$this->getParameter("group_id_contratos_rrhh")]
        );
        $array_item['direction_or_admin'] = $isGroupDirectionOrAdmin;
        $array_item["pagination"] = Utils::getPaginator($request, $filters["limit_many"], $array_item["count"]);

        return $this->render('NononsenseHomeBundle:Contratos:records_contracts.html.twig', $array_item);
    }


    /**
     * Crea un nuevo contrato tomando como base la plantilla $id
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction($id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('contratos_crear_registro');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $contract = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:Contracts')
            ->find($id);

        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $record = new RecordsContracts();
        $record->setIsActive(true);
        $record->setStatus(0);
        $record->setDescription("");
        $record->setMasterDataValues("");
        $record->setCreated(new \DateTime());
        $record->setObservaciones("");
        $record->setYear(date("Y"));
        $record->setUserCreatedEntiy($user);
        $record->setContract($contract);
        $record->setDependsOn(0);
        $record->setToken("");
        $record->setStepDataValue("");
        $record->setFiles("");

        $record->setType($contract->getType());
        $em->persist($record);
        $em->flush();

        $route = $this->container->get('router')->generate(
            'nononsense_records_contracts_link',
            array("id" => $record->getId())
        );

        return $this->redirect($route);
    }

    /* Donde Generamos el link que llama a docxpresso */
    public function linkAction(Request $request, $id)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        /** @var RecordsContracts $record */
        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
            ->find($id);

        /** @var GroupUsersRepository $groupUsersRepository */
        $groupUsersRepository = $this->getDoctrine()->getRepository('NononsenseGroupBundle:GroupUsers');
        $isGroup_direccion_rrhh = $groupUsersRepository->isMemberOfAnyGroup(
            $user->getId(),
            [$this->getParameter("group_id_direccion_rrhh")]
        );

        $isGroup_comite_rrhh = $groupUsersRepository->isMemberOfAnyGroup(
            $user->getId(),
            [$this->getParameter("group_id_comite_rrhh")]
        );

        $isGroup_admin_rrhh = $groupUsersRepository->isMemberOfAnyGroup(
            $user->getId(),
            [$this->getParameter("group_id_contratos_rrhh")]
        );

        if($isGroup_admin_rrhh){
            $url_edit_documento = $this->getLinkForAdmin($record);
        }elseif($isGroup_direccion_rrhh){
            $url_edit_documento = $this->getLinkForDirection($record);
        }elseif ($isGroup_comite_rrhh){
            $url_edit_documento = $this->getLinkForComite($record);
        }else{
            $this->get('session')->getFlashBag()->add(
                'error',
                "No tienes permisos para visualizar este contrato."
            );
            $url_edit_documento = $this->container->get('router')->generate('nononsense_records_contracts');
        }
        return $this->redirect($url_edit_documento);
    }

    /**
     * @param RecordsContracts $record
     * @return string
     */
    private function getLinkForAdmin($record)
    {
        $baseUrl = $this->getParameter("cm_installation");
        switch ($record->getStatus()){
            case 0 :
                $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_creation.js?v=" . uniqid();
                $fillInUrl = $this->getFillInUrl($record, $scriptUrl, self::VERSION_DIRECTOR);
                break;
            case 1 :
            case 2 :
            case 3 :
                $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_without_perms.js?v=" . uniqid();
                $fillInUrl = $this->getFillInUrl($record, $scriptUrl, self::VERSION_DIRECTOR);
                break;
            case 4 :
                $fillInUrl = $this->container->get('router')->generate(
                    'nononsense_records_contracts_public_view_contract',
                    ["token" => $record->getTokenPublicSignature(), "version" => self::VERSION_DIRECTOR]
                );
                break;
            default :
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "El contrato está en un estado desconocido y no se puede visualizar."
                );
                $fillInUrl = $this->container->get('router')->generate('nononsense_records_contracts');
        }
        return $fillInUrl;
    }

    /**
     * @param RecordsContracts $record
     * @return string
     */
    private function getLinkForComite($record)
    {
        $baseUrl = $this->getParameter("cm_installation");
        switch ($record->getStatus()){
            case 0 :
            case 1 :
                $this->get('session')->getFlashBag()->add('error',"Aún no puedes visualizar el contrato seleccionado.");
                $fillInUrl = $this->container->get('router')->generate('nononsense_records_contracts');
                break;
            case 2 :
            case 3 :
                $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_sign_comite_rrhh.js?v=" . uniqid();
                $fillInUrl = $this->getFillInUrl($record, $scriptUrl, self::VERSION_COMMISSION);
                break;
            case 4 :
                $fillInUrl = $this->container->get('router')->generate(
                    'nononsense_records_contracts_public_view_contract',
                    ["token" => $record->getTokenPublicSignatureComite(), "version" => self::VERSION_COMMISSION]
                );
                break;
            default :
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "El contrato está en un estado desconocido y no se puede visualizar."
                );
                $fillInUrl = $this->container->get('router')->generate('nononsense_records_contracts');
        }
        return $fillInUrl;
    }

    /**
     * @param RecordsContracts $record
     * @return string
     */
    private function getLinkForDirection($record)
    {
        $baseUrl = $this->getParameter("cm_installation");
        switch ($record->getStatus()){
            case 0 :
                $this->get('session')->getFlashBag()->add('error',"Aún no puedes visualizar el contrato seleccionado.");
                $fillInUrl = $this->container->get('router')->generate('nononsense_records_contracts');
                break;
            case 1 :
            case 2 :
            case 3 :
                $scriptUrl = $baseUrl . "../js/js_oarodoc/contracts_sign_director_rrhh.js?v=" . uniqid();
                $fillInUrl = $this->getFillInUrl($record, $scriptUrl, self::VERSION_DIRECTOR);
                break;
            case 4 :
                $fillInUrl = $this->container->get('router')->generate(
                    'nononsense_records_contracts_public_view_contract',
                    ["token" => $record->getTokenPublicSignature(), "version" => self::VERSION_DIRECTOR]
                );
                break;
            default :
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "El contrato está en un estado desconocido y no se puede visualizar."
                );
                $fillInUrl = $this->container->get('router')->generate('nononsense_records_contracts');
        }
        return $fillInUrl;
    }

    private function getFillInUrl($record, $scriptUrl, $version)
    {
        $baseUrl = $this->getParameter("cm_installation");
        $id = $record->getId();
        $baseUrlAux = $this->getParameter("cm_installation_aux");
        $redirectUrl = $baseUrl . "recordsContracts/redirectFromData/" . $id;
        $token_get_data = $this->get('utilities')->generateToken();

        $getDataUrl = $baseUrlAux . "dataRecordsContracts/requestData/" . $id . "/" . $token_get_data . "/" . $version;
        $callbackUrl = $baseUrlAux . "dataRecordsContracts/returnData/" . $id;

        $id_plantilla = $record->getContract()->getPlantillaId();

        $base_url = $this->getParameter('api_docoaro') . "/documents/" . $id_plantilla . "?getDataUrl="
            . $getDataUrl . "&redirectUrl=" . $redirectUrl . "&callbackUrl=" . $callbackUrl . "&scriptUrl=" . $scriptUrl;

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

        return $response["fillInUrl"];
    }

    /* Función a la que llama docxpresso antes de abrir la vista previa para saber si necesita cargar datos en las plantillas */
    public function RequestDataAction($id, $token, $sign_public_type)
    {
        $expired_token = $this->get('utilities')->tokenExpired($token);
        if ($expired_token == 1) {
            $data["expired_token"] = 1;
        } else {
            // get the InstanciasSteps entity
            /** @var RecordsContracts $record */
            $record = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsContracts')
                ->find($id);

            /* Prefill contract */
            if ($record->getStepDataValue()) {
                $data["data"] = json_decode($record->getStepDataValue(), true)["data"];
            }

            // La firma del trabajador aparece siempre si ya ha firmado.
            if($sign_public_type == self::VERSION_DIRECTOR){
                $recordsContractsSignatures = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
                    ->findOneBy(array('record' => $record, 'number' => self::WORKER_SIGN_DIRECTOR));
            }elseif($sign_public_type == self::VERSION_COMMISSION){
                $recordsContractsSignatures = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
                    ->findOneBy(array('record' => $record, 'number' => self::WORKER_SIGN_COMISSION));
            }

            if ($recordsContractsSignatures) {
                $firma = $recordsContractsSignatures->getFirma();
                $data["data"]["firma_trabajador"] = "<img src='" . $firma . "' />";
                $data["data"]["nom_trabajador"] = $data["data"]["tra_nombre"];
                $data["data"]["f_fir_trabajador"] = ($recordsContractsSignatures->getModified()) ? $recordsContractsSignatures->getModified()->format('d-m-Y') : '';
            }

            // La firma del Director siempre aparece si ya ha firmado.
            /** @var RecordsContractsSignatures $recordsContractsSignatures */
            $recordsContractsSignatures = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
                ->findOneBy(array('record' => $record, 'number' => '1'));

            if ($recordsContractsSignatures) {
                $firma = $recordsContractsSignatures->getFirma();
                $data["data"]["firma_direccion_rrhh"] = "<img src='" . $firma . "' />";
                $data["data"]["nom_director"] = $recordsContractsSignatures->getUserEntiy()->getName();
                $data["data"]["f_fir_director"] = ($recordsContractsSignatures->getModified()) ? $recordsContractsSignatures->getModified()->format('d-m-Y') : '';
            }

            // La firma de la comisión sólo es visible si estamos viendo la versión de la comisión
            if ($sign_public_type == self::VERSION_COMMISSION) {
                $recordsContractsSignatures = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
                    ->findOneBy(array('record' => $record, 'number' => '2'));

                if ($recordsContractsSignatures) {
                    $firma = $recordsContractsSignatures->getFirma();
                    $data["data"]["firma_comite_rrhh"] = "<img src='" . $firma . "' />";
                    $data["data"]["nom_comite"] = $recordsContractsSignatures->getUserEntiy()->getName();
                    $data["data"]["f_fir_comite"] = ($recordsContractsSignatures->getModified()) ? $recordsContractsSignatures->getModified()->format('d-m-Y') : '';
                }
            }

            $data["data"]["numero_solicitud"] = $record->getId();
            // Si el documento ya ha sido rellenado no dejamos modificar nada.
            if($record->getStatus() > 0){
                $data['configuration']['form_readonly'] = 1;
            }
        }

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent(json_encode($data));

        return $response;
    }

    /* Función a la que se conecta doxpresso para mandar los datos - Webhook*/
    public function returnDataAction($id)
    {
        // get the InstanciasSteps entity
        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
            ->find($id);

        $request = Request::createFromGlobals();
        $params = array();
        $content = $request->getContent();

        if (!empty($content)) {
            $params = json_decode($content, true); // 2nd param to get as array
        }

        $em = $this->getDoctrine()->getManager();

        unset($params["data"]['firma_direccion_rrhh']);
        unset($params["data"]['firma_comite_rrhh']);
        unset($params["data"]['firma_trabajador']);
        $record->setStepDataValue(
            json_encode(array("data" => $params["data"], "action" => $params["action"]), JSON_FORCE_OBJECT)
        );

        $em->persist($record);
        $em->flush();

        $responseAction = new Response();
        $responseAction->setStatusCode(200);
        $responseAction->setContent("OK");
        return $responseAction;
    }

    /* Pagina a la que vamos tras volver de docxpresso */
    public function redirectFromDataAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
            ->find($id);

        if (!$record) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error desconocido al intentar guardar los datos del contrato"
            );
        } else {
            $stepData = $record->getStepDataValue();
            $stepDataJSON = json_decode($stepData, true);
            $status = $record->getStatus();
            $action = $stepDataJSON["action"];
            if(isset($stepDataJSON['data']["tra_nombre"]) && $stepDataJSON['data']["tra_nombre"] && !$record->getWorkerName()){
                $record->setWorkerName($stepDataJSON['data']["tra_nombre"]);
            }

            if ($status == 0 && $action == 'save_partial') {//el contrato ha sido rellenando parcialmente
                $this->get('session')->getFlashBag()->add('message', "El contrato se ha guardado correctamente");
            }
            if ($status == 0 && $action == 'save') {//esta pasando a firma direccion rrhh
                $new_status = 1;
                $record->setStatus($new_status);
                $users_direccion_rrhh = $em->getRepository(GroupUsers::class)->findBy(
                    ["group" => $this->getParameter("group_id_direccion_rrhh")]
                );
                $subject = "Contrato pendiente de firma";
                $mensaje = 'El Contrato con ID ' . $record->getId() . ' está pendiente de firma por su parte';
                $link = $this->container->get('router')->generate(
                    'nononsense_records_contracts_link',
                    array("id" => $record->getId()),
                    true
                );

                foreach ($users_direccion_rrhh as $user_dir_rrhh) {
                    try{
                        $this->get('utilities')->sendNotification(
                            $user_dir_rrhh->getUser()->getEmail(),
                            $link,
                            "",
                            "",
                            $subject,
                            $mensaje
                        );
                    } catch(Exception $e){
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            "Error. Se he producido un error al intentar enviar la notificación del contrato. Revisa que ".
                            "todos los datos sean correctos. Mensaje del error: " . $e->getMessage()
                        );
                        return $this->redirect($this->container->get('router')->generate('nononsense_records_contracts'));
                    }

                }
                $this->get('session')->getFlashBag()->add(
                    'message',
                    "El contrato se ha enviado a firmar a Dirección RRHH"
                );
            }
            if ($status == 1 && $action == 'save') {//el contrato va a firmarse por direccion rrhh
                $can_sign = 0;
                $isGroup_direccion_rrhh = $this->getDoctrine()->getRepository(
                    'NononsenseGroupBundle:GroupUsers'
                )->isMemberOfAnyGroup($user->getId(), array($this->getParameter("group_id_direccion_rrhh")));
                if ($isGroup_direccion_rrhh) {
                    $can_sign = 1;
                }
                if ($can_sign == 1) {
                    return $this->redirect(
                        $this->container->get('router')->generate(
                            'nononsense_records_contracts_sign_gsk',
                            array("id" => $id)
                        )
                    );
                } else {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        "No tienes permisos para firmar. Solo pueden firmar miembros de Dirección RRHH"
                    );
                }
            }
            if ($status == 2 && $action == 'save') {//el contrato va a firmarse por comite rrhh
                $can_sign = 0;
                $isGroup_comite_rrhh = $this->getDoctrine()->getRepository(
                    'NononsenseGroupBundle:GroupUsers'
                )->isMemberOfAnyGroup($user->getId(), array($this->getParameter("group_id_comite_rrhh")));
                if ($isGroup_comite_rrhh) {
                    $can_sign = 1;
                }
                if ($can_sign == 1) {
                    return $this->redirect(
                        $this->container->get('router')->generate(
                            'nononsense_records_contracts_sign_gsk',
                            array("id" => $id)
                        )
                    );
                } else {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        "No tienes permisos para firmar. Solo pueden firmar miembros del Comité de RRHH"
                    );
                }
            }
            if($status == 3 && $action == 'save') {
                $isGroup_comite_rrhh = $this->getDoctrine()->getRepository(
                    'NononsenseGroupBundle:GroupUsers'
                )->isMemberOfAnyGroup($user->getId(), array($this->getParameter("group_id_comite_rrhh")));
                if ($isGroup_comite_rrhh) {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        "El contrato ya ha sido firmado por un miembro del comité"
                    );
                }

                $isGroup_direccion_rrhh = $this->getDoctrine()->getRepository(
                    'NononsenseGroupBundle:GroupUsers'
                )->isMemberOfAnyGroup($user->getId(), array($this->getParameter("group_id_direccion_rrhh")));
                if ($isGroup_direccion_rrhh) {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        "El contrato ya ha sido firmado por un miembro de Dirección RRHH"
                    );
                }
            }

            $em->persist($record);
            $em->flush();
        }

        return $this->redirect($this->container->get('router')->generate('nononsense_records_contracts'));
    }

    public function signContractGskAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('contratos_gestion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $record = $this->getDoctrine()->getRepository('NononsenseHomeBundle:RecordsContracts')->find($id);
        if (!$record) {
            $this->get('session')->getFlashBag()->add('error', "El contrato no existe");
            return $this->redirect($this->container->get('router')->generate('nononsense_records_contracts'));
        }

        // Si lo tiene que firmar el Director cargamos la plantilla con certificado digital
        if ($record->getStatus() == 1) {
            $template = 'NononsenseHomeBundle:Contratos:record_contract_certificate.html.twig';
        } else {
            $template = 'NononsenseHomeBundle:Contratos:record_contract_sign.html.twig';
        }

        $array_data = array();
        $array_data['record'] = $record;

        return $this->render($template, $array_data);
    }

    /* Proceso donde firmamos los documentos */
    public function signAction($id, Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $record = $this->getDoctrine()->getRepository('NononsenseHomeBundle:RecordsContracts')->find($id);
        if (!$record) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error desconocido al intentar guardar los datos del contrato"
            );
        } else {
            $status = $record->getStatus();
            if ($status == 1) {//la firma es del director de rrhh
                $this->signAsDirector($record, $request, $user);
            } elseif ($status == 2) {//la firma es del comite de rrhh
                if(!$request->get('privacy')){
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        "Debe aceptar las condiciones de uso"
                    );
                }else{
                    $this->signAsComite($record, $request, $user);
                }
            }
        }
        return $this->redirect($this->container->get('router')->generate('nononsense_records_contracts'));
    }

    public function showUseConditionsAction()
    {
        return $this->render('NononsenseHomeBundle:Contratos:conditions.html.twig');
    }

    private function signAsDirector($record, $request, $user)
    {
        $sended = 0;
        $errors = 0;
        $em = $this->getDoctrine()->getManager();
        /** @var RecordsContractsSignatures $signature */
        $signature = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
            ->findOneBy(array("record" => $record, "number" => 1));

        if (!$signature) {
            $canSign = $this->getDoctrine()->getRepository(
                'NononsenseGroupBundle:GroupUsers'
            )->isMemberOfAnyGroup($user->getId(), array($this->getParameter("group_id_direccion_rrhh")));

            if ($canSign) {
                $route = '/files/documents/' . $record->getId() . '/signfiles/';
                $file = $request->files->get('signaturefile');
                $signFile = $this->uploadFile($file, $route);
                $signPass = $request->get('signaturepass');
                $signImage = '';
                $endDate = new DateTime('');
                $endDate->sub(new DateInterval('P1D'));

                // Obtenemos los datos del certificado para validarlo
                if ($signFile) {
                    $rootDir = $this->get('kernel')->getRootDir();
                    $certData = [];
                    if (file_exists($rootDir . $signFile)) {
                        $p12File = file_get_contents($rootDir . $signFile);
                        openssl_pkcs12_read($p12File, $certData, $signPass);
                        if (isset($certData['cert'])) {
                            $endDate->add(new DateInterval('P2D'));
                            $certParsed = openssl_x509_parse($certData['cert']);
                            $signImage = $this->createImageForSign($certParsed['subject']['CN']);
                            if ($certParsed && isset($certParsed['validTo_time_t'])) {
                                try {
                                    // TODO modificar para subir a producción
//                                $endDate->setTimestamp($certParsed['validTo_time_t']);
                                } catch (Exception $e) {
                                    echo $e->getMessage();
                                }
                            }
                        }
                    }
                }
                // Si el certificado no es válido o la fecha de validez no es correcta enviamos de vuelta a la página de firma
                if ($endDate->diff(new DateTime())->invert != 1) {
                    $this->get('session')
                        ->getFlashBag()
                        ->add('error', "La firma no se grabó porque hay un problema con tu certificado digital");
                    return $this->redirect($this->container->get('router')->generate('nononsense_records_contracts'));
                }

                $signature = new RecordsContractsSignatures();
                $signature->setNumber(1);
                $signature->setUserEntiy($user);
                $signature->setRecord($record);
                $signature->setSignFile($signFile);
                $signature->setSignPass($signPass);
                $signature->setFirma($signImage);
                $em->persist($signature);

                $record->setStatus(2);
                $em->persist($record);
                $this->get('session')->getFlashBag()->add('message', "La firma se ha grabado correctamente");

                $users_comite_rrhh = $em->getRepository(GroupUsers::class)->findBy(
                    ["group" => $this->getParameter("group_id_comite_rrhh")]
                );

                $link = $this->container->get('router')->generate(
                    'nononsense_records_contracts_link',
                    array("id" => $record->getId()),
                    true
                );

                foreach ($users_comite_rrhh as $user_comite_rrhh) {
                    /** @var Users $user */
                    $user = $user_comite_rrhh->getUser();
                    $pin = $this->generatePinComite($record, $user);
                    if($pin){
                        // datos del email
                        $subject = "Contrato pendiente de firma";
                        $emailMessage = 'El Contrato con ID ' . $record->getId() . ' está pendiente de firma por su parte. ';
                        $emailMessage .= 'Use el siguiente pin ' . $pin . ' para firmar el siguiente contrato: ';
                        // datos del sms
                        $textMessage = 'El Contrato con ID ' . $record->getId() . ' está pendiente de firma por su parte. ';
                        $textMessage .= 'Use el siguiente pin ' . $pin . ' para firmar el siguiente contrato: ' . $link;
                        $phonePrefix = '+34';
                        $phoneNumber = $user->getPhone();
                        // Enviamos por sms
                        if($this->sendBySMS($phonePrefix.$phoneNumber, $textMessage)){
                            $sended++;
                            $this->get('Utilities')->saveLog('sms', 'contrato enviado por sms');
                        }else{
                            $errors++;
                        }
                        // Enviamos por email
                        try{
                            $this->get('utilities')->sendNotification(
                                $user->getEmail(),
                                $link,
                                "",
                                "",
                                $subject,
                                $emailMessage
                            );
                        }catch(Exception $e){
                            $errors++;
                        }

                    }else{
                        $errors++;
                    }
                }
            } else {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "La firma no se grabó porque no tienes permisos suficientes"
                );
            }
        } else {
            $this->get('session')->getFlashBag()->add(
                'error',
                "La firma no se grabó porque este contrato ya había sido firmado por el director de RRHH"
            );
        }
        $em->flush();
        $sendMessage = 'Se han enviado ' .$sended. ' mensajes.';
        if($errors){
            $sendMessage .= 'Han fallado ' .$errors. ' mensajes.';
        }
        $this->get('session')->getFlashBag()->add('message', $sendMessage);
    }

    private function generatePinComite($contractId, $user)
    {
        /** @var RecordsContractsPinComiteRepository $pinComiteRepository */
        $pinComiteRepository = $this->getDoctrine()->getRepository(RecordsContractsPinComite::class);
        /** @var RecordsContractsPinComite $pinComite */
        $pinComite = $pinComiteRepository->findOneBy(['user' => $user, 'contract' => $contractId]);

        if(!$pinComite){
            $pinComite = new RecordsContractsPinComite();
            $pinComite->setUser($user);
            $pinComite->setContract($contractId);
        }
        $pin = rand(100000, 900000);
        $pinComite->setPin($pin);
        $em = $this->getDoctrine()->getManager();
        $em->persist($pinComite);
        $em->flush();

        return $pin;
    }

    private function signAsComite($record, $request, $user)
    {
        $signature = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
            ->findOneBy(array("record" => $record, "number" => 2));
        $firma = $request->get('firma');

        $pin = $request->get('pin');
        /** @var RecordsContractsRepository $contractRepository */
        $contractRepository = $this->getDoctrine()->getRepository(RecordsContracts::class);
        /** @var RecordsContracts $contract */
        $contract = $contractRepository->find($record);

        /** @var RecordsContractsPinComiteRepository $pinComiteRepository */
        $pinComiteRepository = $this->getDoctrine()->getRepository(RecordsContractsPinComite::class);
        /** @var RecordsContractsPinComite $pinComite */
        $pinComite = $pinComiteRepository->findOneBy(['pin' => $pin, 'user' => $user, 'contract' => $contract]);

        if(!$pinComite){
            $this->get('session')->getFlashBag()->add(
                'error',
                "No se ha realizado la firma. El código o el usuario logueado no son correctos."
            );
            return false;
        }

        if (!$signature) {
            $can_sign = 0;

            $isGroup_comite_rrhh = $this->getDoctrine()->getRepository(
                'NononsenseGroupBundle:GroupUsers'
            )->isMemberOfAnyGroup($user->getId(), array($this->getParameter("group_id_comite_rrhh")));
            if ($isGroup_comite_rrhh) {
                $can_sign = 1;
            }

            if ($can_sign == 1) {
                $signature = new RecordsContractsSignatures();
                $signature->setFirma($firma);
                $signature->setNumber(2);
                $signature->setUserEntiy($user);
                $signature->setRecord($record);

                $em = $this->getDoctrine()->getManager();
                $em->persist($signature);

                $record->setStatus(3);
                $em->persist($record);
                $em->flush();

                $this->get('session')->getFlashBag()->add('message', "La firma se ha grabado correctamente");
            } else {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "La firma no se grabó porque no tienes permisos suficientes"
                );
            }
        } else {
            $this->get('session')->getFlashBag()->add(
                'error',
                "La firma no se grabó porque este contrato ya había sido firmado por el comité de RRHH"
            );
        }
    }

    public function editAction($id)
    {
        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
            ->find($id);

        $route = $this->container->get('router')->generate('nononsense_records_link', array("id" => $record->getId()));
        return $this->redirect($route);
    }

    /* Enviamos un contacto al trabajador para firma de contrato */
    public function sendContractAction(Request $request, $type)
    {
        $id = $request->get('record_contract_id_dialog');
        $email_email = $request->get('email_email');
        $phonePrefix = $request->get('phone_prefix');
        $phoneNumber = $request->get('phone_sms');

        $record = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RecordsContracts')
            ->find($id);

        if (!$record) {
            $this->get('session')->getFlashBag()->add('error', "El contrato no existe");
        } elseif ($record->getStatus() != 3) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "El contrato no se puede enviar. Su estado no es 'Para enviar'"
            );
        } else {
            try {
                $em = $this->getDoctrine()->getManager();

                $token = uniqid() . rand(10000, 90000);
                $token_comit = rand(10000, 90000) . uniqid();
                $pin = rand(100000, 900000);

                $record->setTokenPublicSignature($token);
                $record->setTokenPublicSignatureComite($token_comit);
                $record->setPin($pin);
                $em->persist($record);

                $signLink1 = $this->generateUrl(
                    'nononsense_records_contracts_public_sign_contract',
                    ['version' => self::VERSION_DIRECTOR, 'token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $signLink2 = $this->generateUrl(
                    'nononsense_records_contracts_public_sign_contract',
                    ['version' => self::VERSION_COMMISSION, 'token' => $token_comit],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $sended = false;
                switch($type){
                    case 'email':
                        if($this->sendByEmail($email_email, $signLink1, $signLink2, $pin)){
                            $this->get('Utilities')->saveLog('mail', 'contrato enviado por mail');
                            $sended = true;
                        }
                        break;
                    case 'sms':
                        $textMessage =  'Use el siguiente pin ' . $pin . ' para firmar los siguientes contratos: ' . $signLink1;
                        $textMessage .= ' (1/2) | ' . $signLink2 . ' (2/2)';
                        if($this->sendBySMS($phonePrefix.$phoneNumber, $textMessage)){
                            $sended = true;
                            $this->get('Utilities')->saveLog('sms', 'contrato enviado por sms');
                        }
                        break;
                }

                if($sended){
                    $em->flush();
                }

            } catch (Exception $e) {
                echo $e->getMessage();
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "El contrato no se ha podido enviar para su firma"
                );
            }
        }


        return $this->redirect($this->container->get('router')->generate('nononsense_records_contracts'));
    }

    private function sendByEmail($email_email, $signLink1, $signLink2, $pin)
    {
        $mailSubject = 'Firma Contrato';
        $mailTo = $email_email;
        $mailBody = $this->renderView(
            'NononsenseHomeBundle:Email:requestSignContract.html.twig',
            array(
                'link1' => $signLink1,
                'link2' => $signLink2,
                'pin' => $pin,
            )
        );

        if ($this->get('utilities')->sendNotification($mailTo, "", "", "", $mailSubject, $mailBody, false)) {
            $this->get('session')->getFlashBag()->add(
                'message',
                "El contrato ha sido enviado para su firma correctamente"
            );
            return true;
        } else {
            $this->get('session')->getFlashBag()->add(
                'error',
                "El contrato no se ha podido enviar para su firma"
            );
            return false;
        }
    }

    private function sendBySMS($phoneNumber, $textMessage)
    {
        $client = $this->getClientSmsAws();
        try {
            /** @var Result $snsClientResult */
            $snsClientResult = $client->publish(
                [
                    'Message' => $textMessage,
                    'PhoneNumber' => $phoneNumber,
                    'MessageStructure' => 'SMS',
                    'MessageAttributes' => [
                        'AWS.SNS.SMS.SenderID' => [
                            'DataType' => 'String',
                            'StringValue' => 'GSK',
                        ],
                        'AWS.SNS.SMS.SMSType' => [
                            'DataType' => 'String',
                            'StringValue' => 'Transactional', // Transactional
                        ]
                    ]
                ]
            );
            // Sólo validamos si tenemos id del mensaje
            if($snsClientResult->hasKey('MessageId')){
                return true;
            }
        } catch (Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "El contrato no se ha podido enviar para su firma"
            );
        }
        return false;
    }

    private function getClientSmsAws()
    {
        $region = $this->getParameter("sns_region");
        $key = $this->getParameter("sns_key");
        $secret = $this->getParameter("sns_secret");

        return new SnsClient(
            [
                'version' => 'latest',
                'region' => $region,
                'credentials' => [
                    'key' => $key,
                    'secret' => $secret
                ]
            ]
        );
    }

    public function signContractAction(Request $request, $version, $token)
    {
        $result = [
            'type' => 'none',
            'message' => ''
        ];

        $contract = null;
        $signNumber = null;
        $secondSignNumber = null;

        if($version == self::VERSION_DIRECTOR){
            /** @var  RecordsContracts $contract */
            $contract = $this->getDoctrine()
                ->getRepository(RecordsContracts::class)
                ->findOneBy(['tokenPublicSignature' => $token]);
            $signNumber = self::WORKER_SIGN_DIRECTOR;
            $secondSignNumber = self::WORKER_SIGN_COMISSION;
        }elseif($version == self::VERSION_COMMISSION){
            /** @var  RecordsContracts $contract */
            $contract = $this->getDoctrine()
                ->getRepository(RecordsContracts::class)
                ->findOneBy(['tokenPublicSignatureComite' => $token]);
            $signNumber = self::WORKER_SIGN_COMISSION;
            $secondSignNumber = self::WORKER_SIGN_DIRECTOR;
        }

        if ($contract) {
            $firma = $request->get('firma');

            $em = $this->getDoctrine()->getManager();

            $recordSignature = $this->getDoctrine()
                ->getRepository(RecordsContractsSignatures::class)
                ->findOneBy(array('record' => $contract, 'number' => $signNumber));

            if (!$recordSignature && $signNumber) {
                if ($request->getMethod() == "POST") {
                    $pin = $request->get('pin');

                    if ($contract->getPin() == $pin) {
                        $sign = new RecordsContractsSignatures();
                        $sign->setUserEntiy($contract->getUserCreatedEntiy());
                        $sign->setRecord($contract);
                        $sign->setNumber($signNumber);
                        $sign->setCreated(new \DateTime());
                        $sign->setFirma($request->get('firma'));
                        $em->persist($sign);
                        $em->flush();

                        $secondSign = $this->getDoctrine()
                            ->getRepository(RecordsContractsSignatures::class)
                            ->findOneBy(array('record' => $contract, 'number' => $secondSignNumber));

                        if($secondSign){
                            $contract->setStatus(4);
                            $em->persist($contract);
                            $em->flush();
                        }

                        $contractSaved = $this->saveAndSignContract($contract, $token, $version);

                        if(!$contractSaved){
                            $result = [
                                'type' => 'error',
                                'message' => 'El contrato se ha firmado correctamente pero ha ocurrido un error al certificarlo digitalmente.'
                            ];
                        }else{
                            $result = [
                                'type' => 'success',
                                'message' => 'El contrato se ha firmado correctamente'
                            ];
                        }
                    } else {
                        $result = [
                            'type' => 'error',
                            'message' => 'El pin no es correcto'
                        ];
                        $firma = null;
                    }
                }
            } else {
                $firma = $recordSignature->getFirma();
            }

            $pdfUrl = $this->generateUrl('nononsense_records_contracts_public_view_contract',['version' => $version, 'token' => $token]);
            $array_data = [];
            $array_data['token'] = $token;
            $array_data['result'] = $result;
            $array_data['firma'] = $firma;
            $array_data['time'] = time();
            $array_data['pdfUrl'] = $pdfUrl;
            $array_data['version'] = $version;

            return $this->render('NononsenseHomeBundle:Contratos:sign_contract.html.twig', $array_data);
        }
        throw new Exception("Contrato no existente", 1);
    }

    /**
     * @param RecordsContracts $record
     * @param string $token
     * @param int $version
     * @return string
     */
    private function saveAndSignContract($record, $token, $version)
    {
        $fileSigned = false;
        $filePath = '';
        $p12Path = '';
        $p12Pass = '';
        $rootDir = $this->get('kernel')->getRootDir();
        $pdfUrl = $this->getPdfUrl($record, $version);

        ob_start();
        readfile($pdfUrl);
        $pdfFile = ob_get_clean();

        if ($pdfFile) {
            $route = '/files/documents/contracts/' . $token . '/' . $version . '/';
            $filename = $version . $token . '.pdf';
            $filePath = $this->uploadFile($pdfFile, $route, $filename, 2);
        }

        if ($filePath) {
            /** @var RecordsContractsSignatures $recordSignature */
            $recordSignature = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:RecordsContractsSignatures')
                ->findOneBy(array('record' => $record, 'number' => 1));

            if ($recordSignature) {
                $p12Path = $rootDir . $recordSignature->getSignFile();
                $p12Pass = $recordSignature->getSignPass();
            }
            $fileSigned = $this->get('utilities')->signWithP12($rootDir . $filePath, $p12Path, $p12Pass);
        }

        return $fileSigned;
    }

    private function getPdfUrl($record, $version = null)
    {
        $baseUrl = $this->getParameter("cm_installation");
        $baseUrlAux = $this->getParameter("cm_installation_aux");
        $id_plantilla = $record->getContract()->getPlantillaId();
        $scriptUrl_link = '';

        $token_get_data = $this->get('utilities')->generateToken();
        $getDataUrl = $baseUrlAux . "dataRecordsContracts/requestData/" . $record->getId(
            ) . "/" . $token_get_data . "/" . $version;
        $redirectUrl = $this->container->get('router')->generate(
            'nononsense_records_contracts_public_sign_contract',
            array("version" => $version, "token" => $record->getId())
        );

        if ($version == self::VERSION_COMMISSION || $version == self::VERSION_DIRECTOR) {
            $scriptUrl_link = $baseUrl . "../js/js_oarodoc/contracts_sign_public_" . $version . ".js?v=" . uniqid();
        }

        $base_url = $this->getParameter(
                'api_docoaro'
            ) . "/documents/" . $id_plantilla . "?getDataUrl=" . $getDataUrl . "&redirectUrl=" . $redirectUrl . "&scriptUrl=" . $scriptUrl_link;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array("Api-Key: " . $this->getParameter('api_key_docoaro'))
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $raw_response = curl_exec($ch);
        $APIresponse = json_decode($raw_response, true);

        return $APIresponse["pdfUrl"];
    }

    public function viewContractAction(Request $request, $version, $token)
    {
        $record = null;
        /** @var RecordsContracts $record */
        if($version == self::VERSION_DIRECTOR){
            $record = $this->getDoctrine()
                ->getRepository(RecordsContracts::class)
                ->findOneBy(['tokenPublicSignature' => $token]);
        }elseif($version == self::VERSION_COMMISSION){
            $record = $this->getDoctrine()
                ->getRepository(RecordsContracts::class)
                ->findOneBy(['tokenPublicSignatureComite' => $token]);
        }
        if ($record) {
            $relativePath = '/files/documents/contracts/' . $token . '/' . $version . '/' . $version . $token . '.pdf';
            if (file_exists($this->get('kernel')->getRootDir() . $relativePath)) {
                $pdfUrl = $this->get('kernel')->getRootDir() . $relativePath;
            } else {
                $pdfUrl = $this->getPdfUrl($record, $version);
            }
            ob_start();
            readfile($pdfUrl);
            $pdfFile = ob_get_clean();
            $response = new Response();

            $name = $record->getWorkerName();
            if(!$name){
                $name = $token;
            }

            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', 'attachment; filename="'. str_replace(' ', '_', $name) .'_' . $version . '.pdf');
            $response->sendHeaders();
            $response->setContent($pdfFile);
            return $response;
        }
    }

    /**
     * Guarda el archivo $file en la ruta $route y devuelve la ruta del archivo en el servidor.
     * @param mixed $file
     * @param string $route
     * @param string | null $filename
     * @param int $uploadType Si el tipo es 1, subimos un archivo recogido en un request, si es 2 creamos el archivo.
     * @return string
     */
    private function uploadFile($file, $route, $filename = null, $uploadType = 1)
    {
        $full_path = $this->get('kernel')->getRootDir() . $route;
        $fs = new Filesystem();
        if (!$fs->exists($full_path)) {
            $fs->mkdir($full_path);
        }
        if (!$filename) {
            $filename = uniqid();
        }
        if ($uploadType == 1) {
            $file->move($full_path, $filename);
        } elseif ($uploadType == 2) {
            $fs->dumpFile($full_path . '/' . $filename, $file);
        }
        return $route . $filename;
    }

    /**
     * Genera una imagen .png con el texto encadenando los textos $firstText y $text con un salto de línea
     * @param string $text
     * @return string
     */
    private function createImageForSign($text)
    {
        $firstText = 'Firmado digitalmente por:';
        $image = imagecreate(300, 150);
        $background = imagecolorallocate($image, 255, 255, 255);
        $color = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $background);
        imagestring($image, 10, 5, 5, $firstText, $color);
        imagestring($image, 10, 5, 25, $text, $color);
        ob_start();
        imagepng($image);
        return 'data:image/png;base64,' . base64_encode(ob_get_clean());
    }
}
