<?php
namespace Nononsense\HomeBundle\Controller;

use Nononsense\HomeBundle\Entity\Areas;
use Nononsense\HomeBundle\Entity\AreasGroups;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\GroupBundle\Entity\AreaPrefixes;
use Nononsense\HomeBundle\Entity\TMTemplates;
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
        
        return $this->render('NononsenseHomeBundle:Areas:areas.html.twig',$array_item);
    }

    public function editAction(Request $request, string $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $array_item=array();

        $serializer = $this->get('serializer');

        if($id!=0){
            $item = $this->getDoctrine()->getRepository(Areas::class)->findOneBy(array("id"=>$id));
            if(!$item){
                return $this->redirect($this->container->get('router')->generate('nononsense_areas'));
            }
            $array_item["item"] = json_decode($serializer->serialize($item, 'json',array('groups' => array('detail_area'))),true);
        }

        

        return $this->render('NononsenseHomeBundle:Areas:area.html.twig',$array_item);
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
            if($request->get("master_template")){
                $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneById($request->get("master_template"));
                $area->setTemplate($template);
            }

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

    public function removegroupAction(Request $request, $id, $groupid)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $row = $em->getRepository('NononsenseHomeBundle:AreasGroups')
                  ->findOneBy(array('agroup' => $groupid, 
                                     'area' => $id)
                        );
        if (empty($row)) {
            $this->get('session')->getFlashBag()->add(
            'errorDeletingUser',
            'No fune posible eliminar el grupo'
            );
        } else {
            $em->remove($row);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
            'deletedGroup',
            'El grupo ha sido eliminado'
            );
        }
 
        return $this->redirect($this->generateUrl('nononsense_areas_edit', array('id' => $id)));
    }

    public function groupsAction(Request $request, $id)
    {
        
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $groups= $this->getDoctrine()
                      ->getRepository('NononsenseHomeBundle:AreasGroups')
                      ->findGroupsByArea(1, 100000, $id, 'q');
        
        return $this->render('NononsenseGroupBundle:Group:index_areas.html.twig', array(
            'groups' => $groups
        ));
    }

    public function addgroupsAction(Request $request, $id)
    {
        
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $groups = $em->getRepository('NononsenseGroupBundle:Groups')
                  ->findAllGroupsNotInArea($id);


        return $this->render('NononsenseGroupBundle:Group:searchgroup.html.twig', array(
            'groups' => $groups,
            'areaId' => $id,
        ));
    }

    public function addbulkAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }
        $data = $request->query->get('groups');
        $areaId = $request->query->get('id');
        $groupdata = json_decode($data);
        
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('NononsenseHomeBundle:AreasGroups');
        $area = $em->getRepository('NononsenseHomeBundle:Areas')->find($areaId);
        foreach ($groupdata as $id) {
            $new = new AreasGroups();            
            $new->setArea($area);
            $group = $em->getRepository('NononsenseGroupBundle:Groups')->find($id);
            $new->setAgroup($group);
            $em->persist($new);
        }
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'addedUsers',
            'The new members have been added.'
            );

        return $this->redirect($this->generateUrl('nononsense_areas_edit', array('id' => $areaId)));
    }

    public function listPrefixesJsonAction(Request $request, int $area)
    {
        $em = $this->getDoctrine()->getManager();
        $array=array();

        $items=$em->getRepository('NononsenseHomeBundle:AreaPrefixes')->findBy(array("area"=>$area));
        $serializer = $this->get('serializer');
        $array_items = json_decode($serializer->serialize($items,'json',array('groups' => array('json_prefix'))),true);
        foreach($array_items as $key => $item){
            $array["prefixes"][$key]["id"]=$item["id"];
            $array["prefixes"][$key]["name"]=$item["name"];
        }

        $response = new Response(json_encode($array), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}