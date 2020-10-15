<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\HomeBundle\Entity\Contracts;
use Nononsense\HomeBundle\Entity\ContractsRepository;
use Nononsense\HomeBundle\Entity\RecordsContracts;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ContractsController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('plantillas_contratos_gestion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $can_create_plantilla = $this->get('app.security')->permissionSeccion('plantillas_crear_plantilla');
        $can_create_register = $this->get('app.security')->permissionSeccion('contratos_crear_registro');

        $filters = [];

        if (!$request->get("export_excel")) {
            if ($request->get("page")) {
                $filters["limit_from"] = $request->get("page") - 1;
            } else {
                $filters["limit_from"] = 0;
            }
            $filters["limit_many"] = 15;
            $paginate = 1;
        } else {
            $paginate = 0;
        }

        if ($request->get("name")) {
            $filters["name"] = $request->get("name");
        }

        $array_item["filters"] = $filters;
        /** @var ContractsRepository $contractsRepository */
        $contractsRepository = $this->getDoctrine()->getRepository(Contracts::class);
        $array_item["items"] = $contractsRepository->list($filters, $paginate);
        $array_item["count"] = $contractsRepository->count($filters);
        $array_item["pagination"] = Utils::getPaginator($request, $filters["limit_many"], $array_item["count"]);

        $array_item['can_create_plantilla'] = $can_create_plantilla;
        $array_item['can_create_register'] = $can_create_register;

        return $this->render('NononsenseHomeBundle:Contratos:contracts.html.twig', $array_item);
    }

    public function editAction(Request $request, string $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('plantillas_crear_plantilla');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();

        if ($id != 0) {
            $item = $em->getRepository(Contracts::class)->find($id);

            if (!$item) {
                return $this->redirect($this->container->get('router')->generate('nononsense_contracts'));
            }

            $records = $em->getRepository(RecordsContracts::class)->getInProcessContracts($id);

            if ($records) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "No se puede editar una plantilla que tiene contratos activos. Crea una nueva plantilla."
                );
                $array_item["canUpdate"] = false;
            } else {
                $array_item["canUpdate"] = true;
                $array_item["apiTemplate"] = $this->getContractData($item->getPlantillaId());
            }
        } else {
            $array_item["canUpdate"] = true;
            $item = new Contracts();
        }

        $array_item["item"] = $item;

        return $this->render('NononsenseHomeBundle:Contratos:contract.html.twig', $array_item);
    }

    public function updateAction(Request $request, string $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('plantillas_crear_plantilla');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();

        try {
            $not_update = 0;
            $update_template = 0;
            if ($id != 0) {
                $contract = $em->getRepository(Contracts::class)->find($id);

                if ($request->files->get('template') && $request->get("template_name")) {
                    $update_template = 1;
                    $template_name = $request->get("template_name");
                }
                $base_url = $this->getParameter('api_docoaro') . "/documents/" . $contract->getPlantillaId();
            } else {
                $contract = new Contracts();
                $user = $this->container->get('security.context')->getToken()->getUser();
                $contract->setUserCreatedEntiy($user);

                if (!$request->files->get('template')) {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        "Es necesario adjuntar un documento paga subir la plantilla"
                    );
                    return $this->redirect(
                        $this->container->get('router')->generate('nononsense_contracts_edit', ["id" => $id])
                    );
                } else {
                    $update_template = 1;
                    $template_name = $request->get("name");
                    $base_url = $this->getParameter('api_docoaro') . "/documents";
                }
            }

            $records = $em->getRepository(RecordsContracts::class)->getInProcessContracts($id);

            if ($records) {
                $not_update = 1;
            }

            if (!$not_update) {
                if ($update_template == 1) {
                    $file = $request->files->get('template');
                    $data_file = curl_file_create(
                        $file->getRealPath(),
                        $file->getClientMimeType(),
                        $file->getClientOriginalName()
                    );
                    $post = array('name' => $template_name, 'file' => $data_file);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $base_url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt(
                        $ch,
                        CURLOPT_HTTPHEADER,
                        array(
                            "Content-Type: multipart/form-data",
                            "Api-Key: " . $this->getParameter('api_key_docoaro')
                        )
                    );
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $raw_response = curl_exec($ch);
                    $response = json_decode($raw_response, true);

                    if (!$response["version"]) {
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            'Error al subir la plantilla. ' . $response["message"]
                        );
                        return $this->redirect(
                            $this->container->get('router')->generate('nononsense_contracts_edit', ["id" => $id])
                        );
                    }
                    $contract->setPlantillaId($response["id"]);
                }
            }

            $contract->setName($request->get("name"));
            $contract->setDescription($request->get("description"));
            $contract->setPosition(1);
            $contract->setBlock(1);
            $contract->setOptional(0);
            $contract->setDependsOn(0);
            $contract->setCreated(new DateTime());
            $contract->setModified(new DateTime());

            if ($request->get("is_active")) {
                $contract->setIsActive(1);
            } else {
                $contract->setIsActive(0);
            }

            $em->persist($contract);
            $em->flush();
            $this->get('session')->getFlashBag()->add('message', "Datos guardados correctamente");
        } catch (Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error desconocido al intentar guardar los datos de la plantilla" . $e->getMessage()
            );
            $route = $this->container->get('router')->generate('nononsense_contracts_edit', ["id" => $id]);

            return $this->redirect($route);
        }
        $route = $this->container->get('router')->generate('nononsense_contracts_edit', ["id" => $id]);

        return $this->redirect($route);
    }

    private function getContractData($templateId)
    {
        $result = [];
        if ($templateId) {
            $base_url = $this->getParameter('api_docoaro') . "/documents/" . $templateId."?keyPrivated=".$this->getParameter('key_privated_config_docoaro');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Api-Key: " . $this->getParameter('api_key_docoaro')));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $raw_response = curl_exec($ch);
            $result = json_decode($raw_response, true);
        }
        return $result;
    }
}
