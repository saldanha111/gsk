<?php
namespace Nononsense\HomeBundle\Controller;


use Nononsense\HomeBundle\Entity\Contracts;
use Nononsense\HomeBundle\Entity\RecordsContracts;
use Nononsense\HomeBundle\Entity\ContractsSignatures;
use Nononsense\HomeBundle\Entity\RecordsContractsSignatures;
use Nononsense\HomeBundle\Entity\ContractsTypes;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\GroupBundle\Entity\Groups;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasSteps;

use Nononsense\UtilsBundle\Classes;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ContractsController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('plantillas_contratos_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $can_create_plantilla = $this->get('app.security')->permissionSeccion('plantillas_crear_plantilla');
        $can_create_register = $this->get('app.security')->permissionSeccion('contratos_crear_registro');

        $filters=Array();
        $filters2=Array();
        $types=array();

        if(!$request->get("export_excel")){
            if($request->get("page")){
                $filters["limit_from"]=$request->get("page")-1;
            }
            else{
                $filters["limit_from"]=0;
            }
            $filters["limit_many"]=15;
        }
        else{
            $filters["limit_from"]=0;
            $filters["limit_many"]=99999999999;
        }


        if($request->get("name")){
            $filters["name"]=$request->get("name");
            $filters2["name"]=$request->get("name");
        }


        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(Contracts::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(Contracts::class)->count($filters2,$types);

        $url=$this->container->get('router')->generate('nononsense_contracts');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        $array_item['can_create_plantilla'] = $can_create_plantilla;
        $array_item['can_create_register'] = $can_create_register;
        
        return $this->render('NononsenseHomeBundle:Contratos:contracts.html.twig',$array_item);
    }

    public function editAction(Request $request, string $id)
    {

        $is_valid = $this->get('app.security')->permissionSeccion('plantillas_crear_plantilla');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }


        $serializer = $this->get('serializer');

        $array_item["users"] = $this->getDoctrine()->getRepository(Users::class)->findAll();
        $array_item["groups"] = $this->getDoctrine()->getRepository(Groups::class)->findAll();

        if($id!=0){
            $item = $this->getDoctrine()->getRepository(Contracts::class)->findOneById($id);

            if(!$item){
                return $this->redirect($this->container->get('router')->generate('nononsense_contracts'));
            }
            $array_item["item"] = json_decode($serializer->serialize($item, 'json',array('groups' => array('detail_contract'))),true);

            $baseSignatures = $this->getDoctrine()->getRepository('NononsenseHomeBundle:ContractsSignatures')->findBy(array("contract"=> $item),array("number" => "ASC"));
            $array_item["baseSignatures"] = json_decode($serializer->serialize($baseSignatures, 'json',array('groups' => array('list_baseS'))),true);

            $records = $this->getDoctrine()->getRepository(RecordsContracts::class)->findBy(array("contract" => $item));
            if($records){
                $array_item["not_update"]=1;
            }
        }

        return $this->render('NononsenseHomeBundle:Contratos:contract.html.twig',$array_item);
    }

    public function updateAction(Request $request, string $id)
    {   
        $is_valid = $this->get('app.security')->permissionSeccion('plantillas_crear_plantilla');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();

        try {
            $not_update=0;
            if($id!=0){
                $contract = $this->getDoctrine()->getRepository(Contracts::class)->findOneById($id);

                $signatures = $em->getRepository(ContractsSignatures::class)->findBy(["contract"=>$contract]);
                foreach ($signatures as $signature) {
                    $em->remove($signature);
                }
            }
            else{
                $contract = new Contracts();
                $user = $this->container->get('security.context')->getToken()->getUser();
                $contract->setUserCreatedEntiy($user);
            }

            $records = $this->getDoctrine()->getRepository(RecordsContracts::class)->findBy(array("contract" => $contract));
            if($records){
                $not_update=1;
            }

            if(!$not_update){
                $contract->setPlantillaId($request->get("plantilla_id"));
            }

            $contract->setName($request->get("name"));
            $contract->setDescription($request->get("description"));
            $contract->setPosition(1);
            $contract->setBlock(1);
            $contract->setOptional(0);
            $contract->setDependsOn(0);
            $contract->setCreated(new \DateTime());
            $contract->setModified(new \DateTime());

            if($request->get("is_active")){
                $contract->setIsActive(1);
            }
            else{
                $contract->setIsActive(0);
            }

            $em->persist($contract);
            $em->flush();

        }catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error desconocido al intentar guardar los datos de la plantilla".$e->getMessage()
                );
            $route = $this->container->get('router')->generate('nononsense_contracts_edit', array("id" => $id));
        
            return $this->redirect($route);
        }

        

        $route = $this->container->get('router')->generate('nononsense_contracts');
        
        return $this->redirect($route);
    }
}