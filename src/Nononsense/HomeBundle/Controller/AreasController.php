<?php
/**
 * Nodalblock
 * User: Sergio
 * Date: 02/08/2019
 * Time: 07:07
 */
namespace Nononsense\HomeBundle\Controller;

use Nononsense\HomeBundle\Entity\Areas;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\GroupBundle\Entity\Groups;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasSteps;
use Symfony\Component\Filesystem\Filesystem;
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

class AreasController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

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
        $array_item["items"] = $this->getDoctrine()->getRepository(Areas::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(Areas::class)->count($filters2,$types);

        $url=$this->container->get('router')->generate('nononsense_areas');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);
        
        return $this->render('NononsenseHomeBundle:Contratos:areas.html.twig',$array_item);
    }

    public function editAction(Request $request, string $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $serializer = $this->get('serializer');

        if($id!=0){
            $item = $this->getDoctrine()->getRepository(Areas::class)->findOneById($id);
            if(!$item){
                return $this->redirect($this->container->get('router')->generate('nononsense_areas'));
            }
            $array_item["item"] = json_decode($serializer->serialize($item, 'json',array('groups' => array('detail_area'))),true);
        }

        return $this->render('NononsenseHomeBundle:Contratos:area.html.twig',$array_item);
    }

    public function updateAction(Request $request, string $id)
    {   
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();

        try {
            $not_update=0;
            if($id!=0){
                $area = $this->getDoctrine()->getRepository(Areas::class)->findOneById($id);
            }
            else{
                $area = new Areas();
            }

            $area->setName($request->get("name"));
            $area->setCreated(new \DateTime());

            if($request->get("is_active")){
                $area->setIsActive(1);
            }
            else{
                $area->setIsActive(0);
            }

            $em->persist($area);
            $em->flush();

        }catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error desconocido al intentar guardar los datos del are".$e->getMessage()
                );
            $route = $this->container->get('router')->generate('nononsense_areas_edit', array("id" => $id));
        
            return $this->redirect($route);
        }


        $route = $this->container->get('router')->generate('nononsense_areas');
        
        return $this->redirect($route);
    }
}