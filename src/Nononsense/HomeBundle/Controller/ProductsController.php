<?php

namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Nononsense\HomeBundle\Entity\Products;
use Nononsense\HomeBundle\Entity\ProductsTypes;
use Nononsense\HomeBundle\Entity\ProductsInputs;
use Nononsense\HomeBundle\Entity\ProductsOutputs;
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

use Endroid\QrCode\QrCode;

class ProductsController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = self::checkPerms();
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters=array();
        $filters2=array();

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

        if($request->get("active")){
            $filters["active"]=$request->get("active");
            $filters2["active"]=$request->get("active");
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

        if($request->get("underMinimumStock")){
            $filters["underMinimumStock"]=$request->get("underMinimumStock");
            $filters2["underMinimumStock"]=$request->get("underMinimumStock");
        }

        if($request->get("a_excel")==1){
            $items = $this->getDoctrine()->getRepository(Products::class)->list($filters, 0);
            return self::exportExcelProducts($items);
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

    public function editAction(Request $request, $id)
    {
        $is_valid = self::checkPerms();
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('NononsenseHomeBundle:Products')->find($id);

        if(!$product){
            $product = new Products();
        }

        if($request->getMethod()=='POST'){
            try{

                $product->setName($request->get("name"));
                $product->setPartNumber($request->get("partNumber"));
                $product->setCashNumber($request->get("cashNumber"));
                $product->setDescription($request->get("description"));
                $product->setProvider($request->get("provider"));
                $product->setPresentation($request->get("presentation"));
                $product->setAnalysisMethod($request->get("analysisMethod"));
                $product->setObservations($request->get("observations"));
                $product->setStockMinimum($request->get("stockMinimum"));
                $product->setActive($request->get("active"));
                
                $type = $this->getDoctrine()->getRepository('NononsenseHomeBundle:ProductsTypes')->find($request->get("type"));
                $product->setType($type);

                $error = 0;
                $productPartNumber = $em->getRepository('NononsenseHomeBundle:Products')->findOneByPartNumber($request->get("partNumber"));
                if($productPartNumber && $productPartNumber->getId()!=$product->getId()){
                    $this->get('session')->getFlashBag()->add('error', "Part. Number ya está registrado para otro producto");
                    $error = 1;
                }

                if($error==0){
                    $em->persist($product);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('message',"El producto se ha guardado correctamente");
                    return $this->redirect($this->generateUrl('nononsense_products'));
                }
            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar guardar los datos del producto: ".$e->getMessage());
            }
        }

        $array_item = array();
        $array_item['product'] = $product;
        $array_item['types'] = $this->getDoctrine()->getRepository(ProductsTypes::class)->findBy([], ['name' => 'ASC']);

        return $this->render('NononsenseHomeBundle:Products:product.html.twig',$array_item);
    }

    public function deleteAction(Request $request, $id)
    {
        $is_valid = self::checkPerms();
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        try{
            $em = $this->getDoctrine()->getManager();
            $product = $em->getRepository('NononsenseHomeBundle:Products')->find($id);

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

        return $this->redirect($this->generateUrl('nononsense_products'));
    }

    public function deleteInputAction(Request $request, $id)
    {
        $is_valid = self::checkPerms();
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        try{
            $em = $this->getDoctrine()->getManager();
            $productInput = $em->getRepository('NononsenseHomeBundle:ProductsInputs')->find($id);

            if($productInput){
                if(count($productInput->getProductsOutputs())>0){
                    $this->get('session')->getFlashBag()->add('message',"La recepción de material no se puede borrar porque tiene salidas asociadas");
                }
                else{
                    $em->remove($productInput);

                    //actualizo stock del product
                    $product = $productInput->getProduct();
                    $stock = $product->getStock();
                    $newStock = $stock - $productInput->getAmount();
                    $product->setStock($newStock);
                    $em->persist($product);

                    $em->flush();    
                    $this->get('session')->getFlashBag()->add('message',"La recepción de material se ha borrado correctamente");
                }
            }
            else{
                $this->get('session')->getFlashBag()->add('message',"La recepción de material no existe");
            }
        }
        catch(\Exception $e){
            $this->get('session')->getFlashBag()->add('error',"Error al intentar borrar la recepción de material: ".$e->getMessage());
        }

        return $this->redirect($this->generateUrl('nononsense_products_inputs'));
    }

    public function deleteOutputAction(Request $request, $id)
    {
        $is_valid = self::checkPerms();
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        try{
            $em = $this->getDoctrine()->getManager();
            $productOutput = $em->getRepository('NononsenseHomeBundle:ProductsOutputs')->find($id);

            if($productOutput){

                //actualizo remainingAmount del productInput
                $productInput = $productOutput->getProductInput();
                $remainingAmount = $productInput->getRemainingAmount();
                $newRemainingAmount = $remainingAmount + $productOutput->getAmount();
                $productInput->setRemainingAmount($newRemainingAmount);
                $em->persist($productInput);

                //actualizo stock del product
                $product = $productOutput->getProductInput()->getProduct();
                $stock = $product->getStock();
                $newStock = $stock + $productOutput->getAmount();
                $product->setStock($newStock);
                $em->persist($product);

                $em->remove($productOutput);

                $em->flush();    
                $this->get('session')->getFlashBag()->add('message',"La retirada de material se ha borrado correctamente");
            }
            else{
                $this->get('session')->getFlashBag()->add('message',"La retirada de material no existe");
            }
        }
        catch(\Exception $e){
            $this->get('session')->getFlashBag()->add('error',"Error al intentar borrar la retirada de material: ".$e->getMessage());
        }

        return $this->redirect($this->generateUrl('nononsense_products_outputs'));
    }

    
    public function productosJsonAction(Request $request){
        $is_valid = self::checkPerms();
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();

        $filters = array();
        $filters['name'] = $request->get("name");
        $filters['partNumber'] = $request->get("name");
        $filters['limit_from'] = 0;
        $filters['limit_many'] = 10;
        $products = $this->getDoctrine()->getRepository(Products::class)->productsForJson($filters);

        for($i=0;$i<count($products);$i++){

            $expirationMonths = $products[$i]['expirationMonths'];
            $destructionMonths = $products[$i]['destructionMonths']; 

            $dateToday = new \DateTime();
            $products[$i]['receptionDate'] = $dateToday->format('Y-m-d');

            $products[$i]['destructionDate'] = $products[$i]['receptionDate'];
            if($destructionMonths>0){
                $dateToday->add(new \DateInterval('P'.$destructionMonths.'M'));    
                $products[$i]['destructionDate'] = $dateToday->format('Y-m-d');
                $dateToday->sub(new \DateInterval('P'.$destructionMonths.'M')); 
            }
        }

        return new JsonResponse($products);
    }

    public function listInputsAction(Request $request)
    {
        $is_valid = self::checkPerms();
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters=array();
        $filters2=array();

        if($request->get("page")){
            $filters["limit_from"]=$request->get("page")-1;
        }
        else{
            $filters["limit_from"]=0;
        }
        $filters["limit_many"]=15;


        if($request->get("partNumber")){
            $filters["partNumber"]=$request->get("partNumber");
            $filters2["partNumber"]=$request->get("partNumber");
        }        

        if($request->get("name")){
            $filters["name"]=$request->get("name");
            $filters2["name"]=$request->get("name");
        }

        if($request->get("receptionDateFrom")){
            $filters["receptionDateFrom"]=$request->get("receptionDateFrom");
            $filters2["receptionDateFrom"]=$request->get("receptionDateFrom");
        }

        if($request->get("receptionDateTo")){
            $filters["receptionDateTo"]=$request->get("receptionDateTo");
            $filters2["receptionDateTo"]=$request->get("receptionDateTo");
        }

        if($request->get("expiryDateFrom")){
            $filters["expiryDateFrom"]=$request->get("expiryDateFrom");
            $filters2["expiryDateFrom"]=$request->get("expiryDateFrom");
        }

        if($request->get("expiryDateTo")){
            $filters["expiryDateTo"]=$request->get("expiryDateTo");
            $filters2["expiryDateTo"]=$request->get("expiryDateTo");
        }

        if($request->get("destructionDateFrom")){
            $filters["destructionDateFrom"]=$request->get("destructionDateFrom");
            $filters2["destructionDateFrom"]=$request->get("destructionDateFrom");
        }

        if($request->get("destructionDateTo")){
            $filters["destructionDateTo"]=$request->get("destructionDateTo");
            $filters2["destructionDateTo"]=$request->get("destructionDateTo");
        }

        if($request->get("openDateFrom")){
            $filters["openDateFrom"]=$request->get("openDateFrom");
            $filters2["openDateFrom"]=$request->get("openDateFrom");
        }

        if($request->get("openDateTo")){
            $filters["openDateTo"]=$request->get("openDateTo");
            $filters2["openDateTo"]=$request->get("openDateTo");
        }

        if($request->get("a_excel")==1){
            $items = $this->getDoctrine()->getRepository(ProductsInputs::class)->list($filters, 0);
            return self::exportExcelProductsInputs($items);
        }

        $array_item["filters"]=$filters;
        $array_item["types"] = $this->getDoctrine()->getRepository(ProductsTypes::class)->findBy([], ['name' => 'ASC']);
        $array_item["items"] = $this->getDoctrine()->getRepository(ProductsInputs::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(ProductsInputs::class)->count($filters2);


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

    public function listOutputsAction(Request $request)
    {
        $is_valid = self::checkPerms();
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters=array();
        $filters2=array();
        
        if($request->get("page")){
            $filters["limit_from"]=$request->get("page")-1;
        }
        else{
            $filters["limit_from"]=0;
        }
        $filters["limit_many"]=15;


        if($request->get("partNumber")){
            $filters["partNumber"]=$request->get("partNumber");
            $filters2["partNumber"]=$request->get("partNumber");
        }        

        if($request->get("name")){
            $filters["name"]=$request->get("name");
            $filters2["name"]=$request->get("name");
        }

        if($request->get("withdrawalDateFrom")){
            $filters["withdrawalDateFrom"]=$request->get("withdrawalDateFrom");
            $filters2["withdrawalDateFrom"]=$request->get("withdrawalDateFrom");
        }

        if($request->get("withdrawalDateTo")){
            $filters["withdrawalDateTo"]=$request->get("withdrawalDateTo");
            $filters2["withdrawalDateTo"]=$request->get("withdrawalDateTo");
        }

        if($request->get("a_excel")==1){
            $items = $this->getDoctrine()->getRepository(ProductsOutputs::class)->list($filters, 0);
            return self::exportExcelProductsOutputs($items);
        }

        $array_item["filters"]=$filters;
        $array_item["types"] = $this->getDoctrine()->getRepository(ProductsTypes::class)->findBy([], ['name' => 'ASC']);
        $array_item["items"] = $this->getDoctrine()->getRepository(ProductsOutputs::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(ProductsOutputs::class)->count($filters2);


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

        return $this->render('NononsenseHomeBundle:Products:list_outputs.html.twig',$array_item);
    }

    public function editInputAction(Request $request, $id)
    {
        $is_valid = self::checkPerms();
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $productInput = $this->getDoctrine() ->getRepository('NononsenseHomeBundle:ProductsInputs')->find($id);

        if(!$productInput){
            $productInput = new ProductsInputs();
        }

        if($request->getMethod()=='POST'){
            try{
                $em = $this->getDoctrine()->getManager();

                $productInput->setReceptionDate(new \Datetime());
                $productInput->setDestructionDate(new \Datetime($request->get("destructionDate")));
                
                if($request->get("expiryDate")!=''){
                    $productInput->setExpiryDate(new \Datetime($request->get("expiryDate")));
                }
                
                if($request->get("openDate")!=''){
                    $productInput->setOpenDate(new \Datetime($request->get("openDate")));
                }
                
                //actualizo amount y remainingAmount del productInput pero solo si estoy creando.
                if(!$productInput->getId()){
                    $productInput->setAmount($request->get("amount"));
                    $productInput->setRemainingAmount($request->get("amount"));    
                }

                $update_stock_product = 0;

                //si la entrada ya tenía un product y se lo estoy cambiando en esta edicion, actualizo los stocks del producto anterior y del nuevo
                if($productInput->getProduct()){
                    if($request->get("product")!=$productInput->getProduct()->getId()){
                        $product_before = $productInput->getProduct();
                        $stock = $product_before->getStock();
                        $newStock = $stock - $productInput->getAmount();
                        $product_before->setStock($newStock);
                        $em->persist($product_before);

                        $update_stock_product = 1;    
                    }    
                }
                

                //solo dejo modificar el producto si no tiene salidas asociadas
                if(count($productInput->getProductsOutputs())==0){
                    $product = $this->getDoctrine()->getRepository('NononsenseHomeBundle:Products')->find($request->get("product"));
                    if($product){
                        $productInput->setProduct($product);
                    
                        //actualizo stock del product pero solo si estoy creando o si le estoy cambiando el producto a la entrada
                        if($update_stock_product==1 || !$productInput->getId()){
                            $stock = $product->getStock();
                            $newStock = $stock + $productInput->getAmount();
                            $product->setStock($newStock);
                            $em->persist($product);    
                        }
                    }    
                }


                $em->persist($productInput);
                $em->flush();   

                self::generateQrProductInput($productInput);

                $this->get('session')->getFlashBag()->add('message',"La recepción de material se ha guardado correctamente");
                return $this->redirect($this->generateUrl('nononsense_products_inputs'));
            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error',"Error al intentar guardar los datos de la recepción: ".$e->getMessage());
            }
        }
        

        $array_item = array();

        $array_item['productInput'] = $productInput;

        return $this->render('NononsenseHomeBundle:Products:input.html.twig',$array_item);
    }

    public function editOutputAction(Request $request, $id)
    {
        $is_valid = self::checkPerms();
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $productOutput = $this->getDoctrine()->getRepository('NononsenseHomeBundle:ProductsOutputs')->find($id);

        if(!$productOutput){
            $productOutput = new ProductsOutputs();
        }

        if($request->getMethod()=='POST'){
            try{
                $em = $this->getDoctrine()->getManager();

                //no contemplo la opción edicion
                if(!$productOutput->getId()){
                    $productOutput->setAmount($request->get("amount"));

                    $productInput = $this->getDoctrine() ->getRepository('NononsenseHomeBundle:ProductsInputs')->find($request->get("input_id"));
                    if($productInput){
                        $productOutput->setProductInput($productInput);

                        //actualizo remainingAmount del productInput
                        $remainingAmount = $productInput->getRemainingAmount();
                        $newRemainingAmount = $remainingAmount - $productOutput->getAmount();
                        $productInput->setRemainingAmount($newRemainingAmount);
                        $em->persist($productInput);

                        //actualizo stock del product
                        $product = $productInput->getProduct();
                        $stock = $product->getStock();
                        $newStock = $stock - $productOutput->getAmount();
                        $product->setStock($newStock);
                        $em->persist($product);

                        if(!$productInput->getOpenDate()){
                            $productInput->setOpenDate(new \DateTime());

                            $expiryDate = new \DateTime();
                            $expirationMonths = $productInput->getProduct()->getType()->getExpirationMonths();
                            if($expirationMonths>0){
                                $expiryDate->add(new \DateInterval('P'.$expirationMonths.'M'));    
                            }

                            $productInput->setExpiryDate($expiryDate);
                            $em->persist($product);
                        }
                    }
                    
                    $em->persist($productOutput);
                    $em->flush();   
                }

                $this->get('session')->getFlashBag()->add('message',"La retirada se ha guardado correctamente");
                return $this->redirect($this->generateUrl('nononsense_products_outputs'));
            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar guardar los datos de la retirada: ".$e->getMessage());
            }
        }

        $array_item = array();

        $array_item['productOutput'] = $productOutput;

        return $this->render('NononsenseHomeBundle:Products:output.html.twig',$array_item);
    }

    public function inputDataJsonAction($id)
    {
        $is_valid = self::checkPerms();
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $array_return = array();
        $data = array();
        $status = 500;
        try{
            $productInput = $this->getDoctrine()->getRepository('NononsenseHomeBundle:ProductsInputs')->find($id);

            if($productInput){
                $data['partNumberProduct'] = $productInput->getProduct()->getPartNumber();
                $data['nameProduct'] = $productInput->getProduct()->getName();
                $data['remainingAmountProductInput'] = $productInput->getRemainingAmount();    
                $status = 200;
            }
        }
        catch(\Exception $e){

        }
        
        $array_return['data'] = $data;
        $array_return['status'] = $status;

        return new JsonResponse($array_return);
    }
    
    private function generateQrProductInput($productInput){
        $filename = "qr_material_input_".$productInput->getId().".png";
        $rootdir = $this->get('kernel')->getRootDir();
        $ruta_img_qr = $rootdir . "/files/material_inputs_qr/";

        $cad_text = '';
        if($productInput->getExpiryDate()){
            $cad_text = $productInput->getExpiryDate()->format('Y-m-d');
        }

        $label = 'Cad.'.$cad_text." - ";
        $label .= 'Dest.'.$productInput->getDestructionDate()->format('Y-m-d');

        $qrCode = new QrCode();
        $qrCode
        ->setText($productInput->getId())
        ->setSize(500)
        ->setPadding(5)
        ->setErrorCorrection('high')
        ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
        ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
        ->setLabel($label)
        ->setLabelFontSize(14)
        ->setImageType(QrCode::IMAGE_TYPE_PNG)
        ;
         
        $qrCode->save($ruta_img_qr.$filename);

        return $filename;
    }

    public function inputQrAction($id){
        
        $is_valid = self::checkPerms();
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();    

        $productInput = null;
        if($id){
            $productInput = $this->getDoctrine()->getRepository('NononsenseHomeBundle:ProductsInputs')->find($id);

            if($productInput){
                $filename = self::generateQrProductInput($productInput);

                $rootdir = $this->get('kernel')->getRootDir();
                $ruta_img_qr = $rootdir . "/files/material_inputs_qr/";

                $content = file_get_contents($ruta_img_qr.$filename);

                $response = new Response();
                $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, 'image.png');
                $response->headers->set('Content-Disposition', $disposition);
                $response->headers->set('Content-Type', 'image/png');
                $response->setContent($content);
                return $response;
            }
        }

        echo "Error al generar el QR";
        exit();
    }

    private function exportExcelProducts($items){

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties();
        $phpExcelObject->setActiveSheetIndex(0)
         ->setCellValue('A1', 'Part. Number')
         ->setCellValue('B1', 'Cash Number')
         ->setCellValue('C1', 'Nombre')
         ->setCellValue('D1', 'Descripción')
         ->setCellValue('E1', 'Stock')
         ->setCellValue('F1', 'Proveedor')
         ->setCellValue('G1', 'Stock Mínimo')
         ->setCellValue('H1', 'Método análisis')
         ->setCellValue('I1', 'Observaciones')
         ->setCellValue('J1', 'Tipo de Producto');

        $i=2;
        foreach($items as $item){

            $phpExcelObject->getActiveSheet()
            ->setCellValue('A'.$i, $item["partNumber"])
            ->setCellValue('B'.$i, $item["cashNumber"])
            ->setCellValue('C'.$i, $item["name"])
            ->setCellValue('D'.$i, $item["description"])
            ->setCellValue('E'.$i, $item["stock"])
            ->setCellValue('F'.$i, $item["provider"])
            ->setCellValue('G'.$i, $item["stockMinimum"])
            ->setCellValue('H'.$i, $item["analysisMethod"])
            ->setCellValue('I'.$i, $item["observations"])
            ->setCellValue('J'.$i, $item["nameType"]);

            $i++;
        }

        $phpExcelObject->getActiveSheet()->setTitle('Listado de productos');
        $phpExcelObject->setActiveSheetIndex(0);
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'listado_productos.xlsx');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response; 
    }

    private function exportExcelProductsInputs($items){

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties();
        $phpExcelObject->setActiveSheetIndex(0)
         ->setCellValue('A1', 'Part. Number')
         ->setCellValue('B1', 'Nombre')
         ->setCellValue('C1', 'Unidades entrantes')
         ->setCellValue('D1', 'Unidades restantes')
         ->setCellValue('E1', 'Fecha recepción')
         ->setCellValue('F1', 'Fecha caducidad')
         ->setCellValue('G1', 'Fecha destrucción')
         ->setCellValue('H1', 'Fecha apertura');

        $i=2;
        foreach($items as $item){

            $phpExcelObject->getActiveSheet()
            ->setCellValue('A'.$i, $item->getProduct()->getPartNumber())
            ->setCellValue('B'.$i, $item->getProduct()->getName())
            ->setCellValue('C'.$i, $item->getAmount())
            ->setCellValue('D'.$i, $item->getRemainingAmount())
            ->setCellValue('E'.$i, $item->getReceptionDate()->format('Y-m-d H:i:s'))
            ->setCellValue('F'.$i, $item->getExpiryDate()->format('Y-m-d H:i:s'))
            ->setCellValue('G'.$i, $item->getDestructionDate()->format('Y-m-d H:i:s'))
            ->setCellValue('H'.$i, $item->getOpenDate()->format('Y-m-d H:i:s'));

            $i++;
        }

        $phpExcelObject->getActiveSheet()->setTitle('Listado recepciones material');
        $phpExcelObject->setActiveSheetIndex(0);
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'listado_recepciones_material.xlsx');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response; 
    }

    private function exportExcelProductsOutputs($items){

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties();
        $phpExcelObject->setActiveSheetIndex(0)
         ->setCellValue('A1', 'Part. Number')
         ->setCellValue('B1', 'Nombre')
         ->setCellValue('C1', 'Cantidad')
         ->setCellValue('D1', 'Fecha retirada');

        $i=2;
        foreach($items as $item){

            $phpExcelObject->getActiveSheet()
            ->setCellValue('A'.$i, $item["productPartNumber"])
            ->setCellValue('B'.$i, $item["productName"])
            ->setCellValue('C'.$i, $item["amount"])
            ->setCellValue('D'.$i, $item["date"]->format('Y-m-d'));

            $i++;
        }

        $phpExcelObject->getActiveSheet()->setTitle('Listado retiradas material');
        $phpExcelObject->setActiveSheetIndex(0);
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'listado_retiradas_material.xlsx');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response; 
    }

    private function checkPerms(){
        
        $is_valid = false;

        $user = $this->container->get('security.context')->getToken()->getUser();

        $Egroups = $this->getDoctrine()
            ->getRepository('NononsenseGroupBundle:GroupUsers')
            ->findBy(array("user"=>$user));

        foreach ($Egroups as $egroup) {
            if($egroup->getGroup()->getId()==16){
                $is_valid = true;
                break;
            }
            
        }

        return $is_valid;
    }

}