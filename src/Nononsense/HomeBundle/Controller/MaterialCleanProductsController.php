<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\HomeBundle\Entity\MaterialCleanProducts;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MaterialCleanProductsController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_products_list');
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
        $array_item["items"] = $this->getDoctrine()->getRepository(MaterialCleanProducts::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(MaterialCleanProducts::class)->count($filters2);

        $url=$this->container->get('router')->generate('nononsense_mclean_products_list');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=true;
        }
        else{
            $parameters=false;
        }
        $array_item["pagination"]= Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        return $this->render('NononsenseHomeBundle:MaterialClean:product_index.html.twig',$array_item);
    }

    public function editAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_products_edit');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('NononsenseHomeBundle:MaterialCleanProducts')->find($id);

        if(!$product){
            $product = new MaterialCleanProducts();
        }

        if($request->getMethod()=='POST'){
            try{
                $product->setName($request->get("name"));
                $product->setTagsNumber($request->get("tags_number"));
                $product->setActive($request->get("active"));

                $error = 0;
                $productName = $em->getRepository('NononsenseHomeBundle:MaterialCleanProducts')->findOneByName($request->get("name"));
                if($productName && $productName->getId()!=$product->getId()){
                    $this->get('session')->getFlashBag()->add('error', "Ese producto ya está registrado.");
                    $error = 1;
                }

                if($error==0){
                    $em->persist($product);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('message',"El producto se ha guardado correctamente");
                    return $this->redirect($this->generateUrl('nononsense_mclean_products_list'));
                }
            }
            catch(Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar guardar los datos del producto: ".$e->getMessage());
            }
        }

        $array_item = array();
        $array_item['product'] = $product;
        return $this->render('NononsenseHomeBundle:MaterialClean:product_edit.html.twig',$array_item);
    }

    public function getAllAction(Request $request, $id)
    {
//        $array_return = array();
//        $data = array();
//        $status = 500;
//        try{
//            /** @var MaterialCleanProducts $productInput */
//            $productInput = $this->getDoctrine()->getRepository('NononsenseHomeBundle:MaterialCleanProducts')->find($id);
//            if($materialInput){
//                $expirationDays = $materialInput->getExpirationDays();
//                $expirationInterval = new \DateInterval('P' . $expirationDays . 'D');
//                $expirationDate = (new DateTime())->add($expirationInterval);
//
//                $data['expirationDays'] = $expirationDays;
//                $data['expirationDate'] = $expirationDate->format('d-m-Y');
//                $status = 200;
//            }
//        }
//        catch(\Exception $e){
//
//        }
//
//        $array_return['data'] = $data;
//        $array_return['status'] = $status;
//
//        return new JsonResponse($array_return);
    }

    public function deleteAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_products_edit');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        try{
            $em = $this->getDoctrine()->getManager();
            $product = $em->getRepository('NononsenseHomeBundle:MaterialCleanProducts')->find($id);

            if($product){
                $product->setActive(false);
                $em->persist($product);
                $em->flush();
                $this->get('session')->getFlashBag()->add('message',"El producto se ha inactivado correctamente");
            }
            else{
                $this->get('session')->getFlashBag()->add('message',"El producto no existe");
            }
        }
        catch(\Exception $e){
            $this->get('session')->getFlashBag()->add('error',"Error al intentar inactivar el producto: ".$e->getMessage());
        }

        return $this->redirect($this->generateUrl('nononsense_mclean_products_list'));
    }
}
