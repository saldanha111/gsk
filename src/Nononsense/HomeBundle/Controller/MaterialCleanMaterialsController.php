<?php

namespace Nononsense\HomeBundle\Controller;

use Nononsense\HomeBundle\Entity\MaterialCleanMaterials;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MaterialCleanMaterialsController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc-materials-list');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters = [];
        $filters2 = [];

        if($request->get("page")){
            $filters["limit_from"]=$request->get("page")-1;
        }
        else{
            $filters["limit_from"]=0;
        }
        $filters["limit_many"]=15;

        if($request->get("name")){
            $filters["name"]=$request->get("name");
            $filters2["name"]=$request->get("name");
        }

        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(MaterialCleanMaterials::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(MaterialCleanMaterials::class)->count($filters2);

        $url=$this->container->get('router')->generate('nononsense_mclean_materials_list');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=true;
        }
        else{
            $parameters=false;
        }
        $array_item["pagination"]= Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        return $this->render('NononsenseHomeBundle:MaterialClean:material_index.html.twig',$array_item);
    }

    public function editAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc-materials-edit');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $material = $em->getRepository('NononsenseHomeBundle:MaterialCleanMaterials')->find($id);

        if(!$material){
            $material = new MaterialCleanMaterials();
        }

        if($request->getMethod()=='POST'){
            try{

                $material->setName($request->get("name"));
                $material->setExpirationDays($request->get("expiration_days"));
                $material->setActive($request->get("active"));

                $error = 0;
                $materialName = $em->getRepository('NononsenseHomeBundle:MaterialCleanMaterials')->findOneByName($request->get("name"));
                if($materialName && $materialName->getId()!=$material->getId()){
                    $this->get('session')->getFlashBag()->add('error', "Ese material ya está registrado.");
                    $error = 1;
                }

                if($error==0){
                    $em->persist($material);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('message',"El material se ha guardado correctamente");
                    return $this->redirect($this->generateUrl('nononsense_mclean_materials_list'));
                }
            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar guardar los datos del material: ".$e->getMessage());
            }
        }

        $array_item = array();
        $array_item['material'] = $material;

        return $this->render('NononsenseHomeBundle:MaterialClean:material_edit.html.twig',$array_item);
    }

    public function deleteAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc-materials-edit');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        try{
            $em = $this->getDoctrine()->getManager();
            $material = $em->getRepository('NononsenseHomeBundle:MaterialCleanMaterials')->find($id);

            if($material){
                $material->setActive(false);
                $em->persist($material);
                $em->flush();
                $this->get('session')->getFlashBag()->add('message',"El material se ha inactivado correctamente");
            }
            else{
                $this->get('session')->getFlashBag()->add('message',"El material no existe");
            }
        }
        catch(\Exception $e){
            $this->get('session')->getFlashBag()->add('error',"Error al intentar inactivar el material: ".$e->getMessage());
        }

        return $this->redirect($this->generateUrl('nononsense_mclean_materials_list'));
    }
}
