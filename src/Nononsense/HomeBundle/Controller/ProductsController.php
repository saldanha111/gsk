<?php

namespace Nononsense\HomeBundle\Controller;


use Nononsense\HomeBundle\Entity\Products;
use Nononsense\HomeBundle\Entity\ProductsTypes;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nononsense\HomeBundle\Form\Type as FormProveedor;

use Nononsense\UtilsBundle\Classes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Nononsense\UtilsBundle\Classes\Auxiliar;
use Nononsense\UtilsBundle\Classes\Utils;

class ProductsController extends Controller
{
    public function listAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $Egroups = $this->getDoctrine()
            ->getRepository('NononsenseGroupBundle:GroupUsers')
            ->findBy(array("user"=>$user));
        
        $filters=array();
        $filters2=array();

        $filters["user"]=$user;
        $filters2["user"]=$user;

        
        if($request->get("page")){
            $filters["limit_from"]=$request->get("page")-1;
        }
        else{
            $filters["limit_from"]=0;
        }
        $filters["limit_many"]=15;


        if($request->get("id")){
            $filters["id"]=$request->get("id");
            $filters2["id"]=$request->get("id");
        }

        if($request->get("partNumber")){
            $filters["partNumber"]=$request->get("partNumber");
            $filters2["partNumber"]=$request->get("partNumber");
        }        

        if($request->get("name")){
            $filters["name"]=$request->get("name");
            $filters2["name"]=$request->get("name");
        }

        if($request->get("provider")){
            $filters["provider"]=$request->get("provider");
            $filters2["provider"]=$request->get("provider");
        }

        if($request->get("type")){
            $filters["type"]=$request->get("type");
            $filters2["type"]=$request->get("type");
        }

        if($request->get("stock_from")){
            $filters["stock_from"]=$request->get("stock_from");
            $filters2["stock_from"]=$request->get("stock_from");
        }

        if($request->get("stock_to")){
            $filters["stock_to"]=$request->get("stock_to");
            $filters2["stock_to"]=$request->get("stock_to");
        }

        if($request->get("minimum_stock_from")){
            $filters["minimum_stock_from"]=$request->get("minimum_stock_from");
            $filters2["minimum_stock_from"]=$request->get("minimum_stock_from");
        }

        if($request->get("minimum_stock_to")){
            $filters["minimum_stock_to"]=$request->get("minimum_stock_to");
            $filters2["minimum_stock_to"]=$request->get("minimum_stock_to");
        }

        $array_item["filters"]=$filters;
        $array_item["types"] = $this->getDoctrine()->getRepository(ProductsTypes::class)->findBy([], ['name' => 'ASC']);
        $array_item["items"] = $this->getDoctrine()->getRepository(Products::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(Products::class)->count($filters2);


        $url=$this->container->get('router')->generate('nononsense_products');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        return $this->render('NononsenseHomeBundle:Products:index.html.twig',$array_item);
    }

    public function editAction($id)
    {

        $array_item = array();

        $product = $this->getDoctrine()->getRepository('NononsenseHomeBundle:Products')->find($id);

        $array_item['product'] = $product;
        $array_item["types"] = $this->getDoctrine()->getRepository(ProductsTypes::class)->findBy([], ['name' => 'ASC']);
        

        return $this->render('NononsenseHomeBundle:Products:product.html.twig',$array_item);
    }

    public function updateAction(Request $request, $id)
    {

        try{
            $product = $this->getDoctrine() ->getRepository('NononsenseHomeBundle:Products')->find($id);

            if(!$product){
                $product = new Products();
            }

            $em = $this->getDoctrine()->getManager();

            $product->setName($request->get("name"));
            $product->setPartNumber($request->get("partNumber"));
            $product->setDescription($request->get("description"));
            $product->setProvider($request->get("provider"));
            $product->setPresentation($request->get("presentation"));
            $product->setAnalysisMethod($request->get("analysisMethod"));
            $product->setObservations($request->get("observations"));
            $product->setStockMinimum($request->get("stockMinimum"));
            
            $type = $this->getDoctrine()->getRepository('NononsenseHomeBundle:ProductsTypes')->find($request->get("type"));
            $product->setType($type);

            $em->persist($product);
            $em->flush();    
        }
        catch(\Exception $e){
            $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error al intentar guardar los datos del producto: ".$e->getMessage()
                );
            $route = $this->container->get('router')->generate('nononsense_products_edit', array("id" => $id));
            return $this->redirect($route);
        }

        return $this->redirect($this->generateUrl('nononsense_products'));
    }

    public function listInputsAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $Egroups = $this->getDoctrine()
            ->getRepository('NononsenseGroupBundle:GroupUsers')
            ->findBy(array("user"=>$user));
        
        $filters=array();
        $filters2=array();

        $filters["user"]=$user;
        $filters2["user"]=$user;

        
        if($request->get("page")){
            $filters["limit_from"]=$request->get("page")-1;
        }
        else{
            $filters["limit_from"]=0;
        }
        $filters["limit_many"]=15;


        if($request->get("id")){
            $filters["id"]=$request->get("id");
            $filters2["id"]=$request->get("id");
        }

        if($request->get("partNumber")){
            $filters["partNumber"]=$request->get("partNumber");
            $filters2["partNumber"]=$request->get("partNumber");
        }        

        if($request->get("name")){
            $filters["name"]=$request->get("name");
            $filters2["name"]=$request->get("name");
        }

        if($request->get("provider")){
            $filters["provider"]=$request->get("provider");
            $filters2["provider"]=$request->get("provider");
        }

        if($request->get("type")){
            $filters["type"]=$request->get("type");
            $filters2["type"]=$request->get("type");
        }

        if($request->get("stock_from")){
            $filters["stock_from"]=$request->get("stock_from");
            $filters2["stock_from"]=$request->get("stock_from");
        }

        if($request->get("stock_to")){
            $filters["stock_to"]=$request->get("stock_to");
            $filters2["stock_to"]=$request->get("stock_to");
        }

        if($request->get("minimum_stock_from")){
            $filters["minimum_stock_from"]=$request->get("minimum_stock_from");
            $filters2["minimum_stock_from"]=$request->get("minimum_stock_from");
        }

        if($request->get("minimum_stock_to")){
            $filters["minimum_stock_to"]=$request->get("minimum_stock_to");
            $filters2["minimum_stock_to"]=$request->get("minimum_stock_to");
        }

        $array_item["filters"]=$filters;
        $array_item["types"] = $this->getDoctrine()->getRepository(ProductsTypes::class)->findBy([], ['name' => 'ASC']);
        $array_item["items"] = $this->getDoctrine()->getRepository(Products::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(Products::class)->count($filters2);


        $url=$this->container->get('router')->generate('nononsense_products');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        return $this->render('NononsenseHomeBundle:Products:list_inputs.html.twig',$array_item);
    }

    public function editInputAction($id)
    {

        $array_item = array();

        $product = $this->getDoctrine()->getRepository('NononsenseHomeBundle:Products')->find($id);

        $array_item['product'] = $product;
        $array_item["types"] = $this->getDoctrine()->getRepository(ProductsTypes::class)->findBy([], ['name' => 'ASC']);
        

        return $this->render('NononsenseHomeBundle:Products:product.html.twig',$array_item);
    }

    public function updateInputAction(Request $request, $id)
    {

        try{
            $product = $this->getDoctrine() ->getRepository('NononsenseHomeBundle:Products')->find($id);

            if(!$product){
                $product = new Products();
            }

            $em = $this->getDoctrine()->getManager();

            $product->setName($request->get("name"));
            $product->setPartNumber($request->get("partNumber"));
            $product->setDescription($request->get("description"));
            $product->setProvider($request->get("provider"));
            $product->setPresentation($request->get("presentation"));
            $product->setAnalysisMethod($request->get("analysisMethod"));
            $product->setObservations($request->get("observations"));
            $product->setStockMinimum($request->get("stockMinimum"));
            
            $type = $this->getDoctrine()->getRepository('NononsenseHomeBundle:ProductsTypes')->find($request->get("type"));
            $product->setType($type);

            $em->persist($product);
            $em->flush();    
        }
        catch(\Exception $e){
            $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error al intentar guardar los datos del producto: ".$e->getMessage()
                );
            $route = $this->container->get('router')->generate('nononsense_products_edit', array("id" => $id));
            return $this->redirect($route);
        }

        return $this->redirect($this->generateUrl('nononsense_products'));
    }

}