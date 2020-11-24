<?php

namespace Nononsense\HomeBundle\Controller;

use Datetime;
use Exception;
use Nononsense\HomeBundle\Entity\ProductsDissolution;
use Nononsense\HomeBundle\Entity\ProductsDissolutionRepository;
use Nononsense\HomeBundle\Entity\ProductsInputsRepository;
use Nononsense\HomeBundle\Entity\ProductsInputStatus;
use Nononsense\HomeBundle\Entity\ProductsInputStatusRepository;
use Nononsense\HomeBundle\Entity\ProductsOutputsRepository;
use Nononsense\HomeBundle\Entity\ProductsRepository;
use Nononsense\HomeBundle\Entity\ProductsTypes;
use Nononsense\HomeBundle\Entity\ProductsTypesRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nononsense\HomeBundle\Entity\Products;
use Nononsense\HomeBundle\Entity\ProductsPresentation;
use Nononsense\HomeBundle\Entity\ProductsInputs;
use Nononsense\HomeBundle\Entity\ProductsOutputs;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Nononsense\UtilsBundle\Classes\Utils;
use Endroid\QrCode\QrCode;

class ProductsController extends Controller
{

    public function receptionAction(Request $request, $type, $casNumber = null)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('productos_gestion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        /** @var ProductsTypesRepository $productsTypeRepository */
        $productsTypeRepository = $this->getDoctrine()->getRepository(ProductsTypes::class);
        /** @var ProductsTypes $activeType */
        $activeType = $productsTypeRepository->findOneBy(['slug' => $type]);

        if ($request->getMethod() == 'POST' && $activeType) {
            $qrCode = $request->get("qr-value");
            $casNumber = $request->get('cas-number');
            if ($activeType->getSlug() == 'material') {
                /** @var ProductsRepository $productsRepository */
                $productsRepository = $this->getDoctrine()->getRepository(Products::class);
                $product = $productsRepository->findOneBy(['qrCode' => $qrCode, 'type' => $activeType]);

                if (!$product) {
                    $this->get('session')->getFlashBag()->add(
                        'message',
                        "El material no está en el sistema. Puedes darlo de alta rellenando el siguiente formulario."
                    );
                    return $this->redirect(
                        $this->generateUrl(
                            'nononsense_products_edit',
                            ['type' => $type, 'id' => 0, 'qrCode' => $qrCode]
                        )
                    );
                }
            }
            return $this->redirect(
                $this->generateUrl('nononsense_products_inputs', ['type' => $type, 'qrCode' => $qrCode, 'casNb' => $casNumber])
            );
        }

        $data = [
            'type' => $type,
            'casNumber' => urldecode($casNumber)
        ];
        return $this->render('NononsenseHomeBundle:Products:reception_index.html.twig', $data);
    }

    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('productos_gestion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();

        $filters = Utils::getListFilters($request);
        $filters["limit_many"] = 15;

        /** @var ProductsInputsRepository $productInputRepository */
        $productInputRepository = $em->getRepository(ProductsInputs::class);

        /** @var ProductsInputStatusRepository $productInputStatusRepository */
        $productInputStatusRepository = $em->getRepository(ProductsInputStatus::class);

        /** @var ProductsTypesRepository $productsTypesRepository */
        $productsTypesRepository = $em->getRepository(ProductsTypes::class);

        if(isset($filters['destructionDateFrom']) && $filters['destructionDateFrom']){
            $destructionFrom = DateTime::createFromFormat('d-m-Y', $filters['destructionDateFrom']);
            $filters['destructionDateFrom'] = $destructionFrom->format('Y-m-d');
        }

        if(isset($filters['destructionDateTo']) && $filters['destructionDateTo']){
            $destructionTo = DateTime::createFromFormat('d-m-Y', $filters['destructionDateTo']);
            $filters['destructionDateTo'] = $destructionTo->format('Y-m-d');
        }

        if ($request->get("a_excel") == 1) {
            $items = $productInputRepository->listForStock($filters, 0);
            return $this->exportExcelProducts($items);
        }

        $totalItems = $productInputRepository->countForStock($filters);
        $listItems = $productInputRepository->listForStock($filters,1);

        if(isset($filters['destructionDateFrom']) && $filters['destructionDateFrom']){
            $filters['destructionDateFrom'] = $destructionFrom->format('d-m-Y');
        }

        if(isset($filters['destructionDateTo']) && $filters['destructionDateTo']){
            $filters['destructionDateTo'] = $destructionTo->format('d-m-Y');
        }

        $array_item = [
            "filters" => $filters,
            "items" => $listItems,
            "count" => $totalItems,
            "states" => $productInputStatusRepository->findAll(),
            "types" => $productsTypesRepository->findAll(),
            "pagination" => Utils::getPaginator($request, $filters['limit_many'], $totalItems)
        ];

        return $this->render('NononsenseHomeBundle:Products:index.html.twig', $array_item);
    }

    public function editAction(Request $request, $type, $id, $qrCode)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('productos_gestion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $newProduct = false;

        /** @var ProductsTypesRepository $productsTypesRepository */
        $productsTypesRepository = $this->getDoctrine()->getRepository(ProductsTypes::class);
        /** @var ProductsTypes $actualType */
        $actualType = $productsTypesRepository->findOneBy(['slug' => $type]);

        if (!$actualType) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "No se reconoce el tipo de material o reactivo. Vuelve a intentarlo."
            );
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        /** @var ProductsRepository $productsRepository */
        $productsRepository = $this->getDoctrine()->getRepository(Products::class);
        $product = $productsRepository->find($id);

        if (!$product) {
            $product = new Products();
            $newProduct = true;
        }

        if ($request->getMethod() == 'POST') {
            $saved = $this->saveProduct($product, $qrCode, $request, $actualType);
            if ($saved['error'] === 0) {
                if ($newProduct) {
                    $this->get('session')->getFlashBag()->add(
                        'message',
                        "El " . $actualType->getName() . " se ha guardado correctamente, Ahora puedes generar la recepción."
                    );
                    return $this->redirect(
                        $this->generateUrl('nononsense_products_reception', ['type' => $actualType->getSlug()])
                    );
                } else {
                    $this->get('session')->getFlashBag()->add(
                        'message',
                        "El " . $actualType->getName() . " se ha guardado correctamente"
                    );
                    return $this->redirect($this->generateUrl('nononsense_products'));
                }
            } else {
                $this->get('session')->getFlashBag()->add('error', $saved['message']);
            }
        }

        if ($qrCode) {
            $product->setQrCode($qrCode);
        }

        $array_item = array();
        $array_item['type'] = $actualType;
        $array_item['product'] = $product;

        return $this->render('NononsenseHomeBundle:Products:product.html.twig', $array_item);
    }

    private function saveProduct(Products $product, string $qrCode, Request $request, ProductsTypes $actualType)
    {
        $saved = ['error' => 0, 'message' => '', 'productId' => ''];
        try {
            $product->setQrCode($qrCode);
            $product->setType($actualType);

            if($request->get("name")){
                $product->setName($request->get("name"));
            }
            if($request->get("internalCode")) {
                $product->setInternalCode($request->get("internalCode"));
            }
            if($request->get("partNumber")) {
                $product->setPartNumber($request->get("partNumber"));
            }
            if($request->get("casNumber")) {
                $product->setCasNumber($request->get("casNumber"));
            }
            if($request->get("provider")) {
                $product->setProvider($request->get("provider"));
            }
            if($request->get("presentation")) {
                $product->setPresentation($request->get("presentation"));
            }
            if($request->get("stockMinimum")) {
                $product->setStockMinimum($request->get("stockMinimum"));
            }
            if($request->get("active")) {
                $product->setActive($request->get("active"));
            }
            if($request->get("static")) {
                $product->setStatic($request->get("static"));
            }

            /** @var ProductsRepository $productsRepository */
            $productsRepository = $this->getDoctrine()->getRepository(Products::class);
            $productPartNumber = $productsRepository->findOneBy(['partNumber' => $request->get("partNumber")]);
            if ($productPartNumber && $productPartNumber->getId() != $product->getId()) {
                $saved = [
                    'error' => 1,
                    'message' => 'Part. Number ya está registrado para otro producto',
                    'productId' => ''
                ];
            }

            if ($saved['error'] == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($product);
                $em->flush();
                $saved['productId'] = $product->getId();
            }
        } catch (Exception $e) {
            $saved = [
                'error' => 1,
                'message' => 'Error al intentar guardar los datos del producto: ' . $e->getMessage(),
                'productId' => ''
            ];
        }
        return $saved;
    }

    public function editStockAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('productos_gestion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        /** @var ProductsRepository $productsRepository */
        $productsRepository = $this->getDoctrine()->getRepository(Products::class);
        /** @var Products $product */
        $product = $productsRepository->find($id);

        if(!$product){
            $this->get('session')->getFlashBag()->add('error', 'No se ha encontrado el producto');
            return $this->redirect($this->generateUrl('nononsense_products'));
        }
        $productType = $product->getType();

        if ($request->getMethod() == 'POST') {
            $stockEdited = false;
            $minStockEdited = $this->editMinStock($request->get('stockMinimum'), $product);

            if($productType->getSlug() == 'material' && $product->getStock() != $request->get('stock')){
                $stockEdited = $this->editStockMaterial($request->get('stock'), $product);
            }elseif(
                $productType->getSlug() == 'reactivo' &&
                $product->getStock() > 0 &&
                $request->get('output') &&
                count($request->get('output'))
            ){
                $stockEdited = $this->editStockReactivo([$request->get('output')], $product);
            }

            if($minStockEdited || $stockEdited){
                $this->get('session')->getFlashBag()->add('message', 'Datos actualizados con éxito');
            }
        }

        $array_item = array();
        $array_item['type'] = $productType;
        $array_item['product'] = $product;

        if($productType->getSlug() === 'reactivo'){
            /** @var ProductsInputStatusRepository $productInputsRepository */
            $productInputsRepository = $this->getDoctrine()->getRepository(ProductsInputs::class);
            $array_item['productsInput'] = $productInputsRepository->findBy(['product' => $product, 'remainingAmount' => 1]);
            return $this->render('NononsenseHomeBundle:Products:product_edit_stock_reactivo.html.twig', $array_item);
        }
        return $this->render('NononsenseHomeBundle:Products:product_edit_stock_material.html.twig', $array_item);
    }

    /**
     *
     * @param int $minStock
     * @param Products $product
     * @return bool
     */
    private function editMinStock($minStock, Products $product)
    {
        $result = false;
        $productType = $product->getType();
        if($product->getStockMinimum() != $minStock){
            $minStockRequest = new Request(['stockMinimum' => $minStock]);
            $saved = $this->saveProduct($product, $product->getQrCode(), $minStockRequest, $productType);
            if ($saved['error'] === 0) {
                $result = true;
            } else {
                $this->get('session')->getFlashBag()->add('error', $saved['message']);
            }
        }
        return $result;
    }

    /** Product $prod
     * @param int $newStock
     * @param Products $product
     * @return bool
     * @throws Exception
     */
    private function editStockMaterial($newStock, Products $product)
    {
        $productStock = $product->getStock();
        if($productStock != $newStock){
            $diff = $newStock - $productStock;
            if($diff > 0){
                $result = $this->insertMaterial($product, $diff);
            }else{
                $outputAmount = $productStock - $newStock;
                /** @var ProductsInputsRepository $productsInputsRepository */
                $productsInputsRepository = $this->getDoctrine()->getRepository(ProductsInputs::class);
                while($outputAmount > 0){
                    $inputQr = $product->getQrCode();
                    $productInput = $productsInputsRepository->getOneByQrCode($inputQr);
                    if($productInput){
                        $outputAmount = $this->generateSingleOutput($productInput, $outputAmount);
                    }else{
                        throw new Exception('Ha ocurrido un error y no ha sido posible hacer la retirada de todas las unidades.');
                    }
                }
                $result = true;
            }
        }else{
            $result = true;
        }
        return $result;
    }

    /** Product $prod
     * @param int[] $inputIds
     * @param Products $product
     * @return bool
     * @throws Exception
     */
    private function editStockReactivo(array $inputIds, Products $product)
    {
        $result = false;
        if(count($inputIds)){
            /** @var ProductsInputsRepository $productsInputRepository */
            $productsInputRepository = $this->getDoctrine()->getRepository(ProductsInputs::class);
            foreach($inputIds as $input){
                /** @var ProductsInputs $productInput */
                $productInput = $productsInputRepository->findOneBy(['id' => $input,'product' => $product]);
                if($productInput){
                    $outputAmount = $productInput->getRemainingAmount();
                    $this->generateSingleOutput($productInput, $outputAmount);
                    $result = true;
                }else{
                    throw new Exception('Ha ocurrido un error y no ha sido posible hacer la retirada de todas las unidades.');
                }
            }
        }else{
            $result = true;
        }
        return $result;
    }

    private function insertMaterial($product, $amount)
    {
        /** @var ProductsInputStatusRepository $statusRepository */
        $statusRepository = $this->getDoctrine()->getRepository(ProductsInputStatus::class);
        /** @var ProductsInputStatus $state */
        $state = $statusRepository->findOneBy(['slug' => 'recibido']);
        $productInput = new ProductsInputs();
        $productInput->setQrCode($product->getQrCode());
        $productInput->setReceptionDate(new Datetime());
        $productInput->setAmount($amount);
        $productInput->setRemainingAmount($amount);
        $productInput->setState($state);
        $productInput->setProduct($product);
        $productInput->setUser($this->getUser());

        $em = $this->getDoctrine()->getManager();
        $em->persist($productInput);
        $em->flush();

        if ($em->contains($productInput)) {
            $stock = $product->getStock();
            $product->setStock($stock + $amount);
            $em->persist($product);
            $em->flush();
        }
        return true;
    }

    public function inputAction(Request $request, $type, $qrCode, $casNb = null)
    {
        $errorMessage = null;
        $is_valid = $this->get('app.security')->permissionSeccion('productos_recepcion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        /** @var ProductsTypesRepository $productsTypesRepository */
        $productsTypesRepository = $this->getDoctrine()->getRepository(ProductsTypes::class);
        /** @var ProductsTypes $actualType */
        $actualType = $productsTypesRepository->findOneBy(['slug' => $type]);

        $productInput = new ProductsInputs();
        if ($request->getMethod() == 'POST') {
            try {
                /** @var ProductsInputStatusRepository $statusRepository */
                $statusRepository = $this->getDoctrine()->getRepository(ProductsInputStatus::class);
                /** @var ProductsInputStatus $state */
                $state = $statusRepository->findOneBy(['slug' => 'recibido']);

                /** @var ProductsRepository $productsRepository */
                $productsRepository = $this->getDoctrine()->getRepository(Products::class);
                /** @var Products $product */

                if ($actualType->getSlug() === 'reactivo') {
                    $amount = 1;
                    $endDate = $this->getEndDate($request);
                    $product = $productsRepository->findOneBy(['casNumber' => $request->get("casNumber")]);
                    if (!$product) {
                        $saved = $this->saveProduct(new Products(), $qrCode, $request, $actualType);
                        if (!$saved['error']) {
                            $product = $productsRepository->find($saved['productId']);
                        } else {
                            $this->get('session')->getFlashBag()->add('error', $product['message']);
                        }
                    }
                } else {
                    $amount = $request->get("amount");
                    $product = $productsRepository->find($request->get("productId"));
                    $endDate = null;
                }

                if (!$state || !$product) {
                    $errorMessage = 'Se ha producido un error al crear la entrada. Vuelva a intentarlo.';
                }

                if($actualType->getSlug() === 'reactivo' && !$endDate){
                    $errorMessage = 'La fecha de caducidad y de destrucción son obligatorias';
                }

                if($errorMessage){
                    $this->get('session')->getFlashBag()->add('error', $errorMessage);
                    return $this->redirect(
                        $this->generateUrl('nononsense_products_inputs', ['type' => $type, 'qrCode' => $qrCode])
                    );
                }

                $destructionDate = ($endDate) ? new Datetime($endDate) : null;
                $expiryDate = ($endDate) ? new Datetime($endDate) : null;

                $productInput->setQrCode($qrCode);
                $productInput->setReceptionDate(new Datetime());
                $productInput->setDestructionDate($destructionDate);
                $productInput->setExpiryDate($expiryDate);
                $productInput->setAmount($amount);
                $productInput->setRemainingAmount($amount);
                $productInput->setObservations($request->get("observations"));
                $productInput->setLotNumber($request->get("lotNumber"));
                $productInput->setState($state);
                $productInput->setProduct($product);
                $productInput->setUser($this->getUser());

                $em = $this->getDoctrine()->getManager();
                $em->persist($productInput);
                $em->flush();

                if ($em->contains($productInput)) {
                    $stock = $product->getStock();
                    $product->setStock($stock + $amount);
                    $em->persist($product);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add(
                    'message',
                    "La recepción de material se ha guardado correctamente"
                );
                return $this->redirect(
                    $this->generateUrl('nononsense_products_input_list', ['type' => $actualType->getSlug()])
                );
            } catch (Exception $e) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error al intentar guardar los datos de la recepción: " . $e->getMessage()
                );
            }
        }

        /** @var ProductsRepository $productsRepository */
        $productsRepository = $this->getDoctrine()->getRepository(Products::class);

        $array_item = array();
        $array_item['type'] = $actualType;
        $array_item['productInput'] = $productInput;
        $array_item['qrCode'] = $qrCode;
        $array_item['presentations'] =
            $this->getDoctrine()
                ->getRepository(ProductsPresentation::class)
                ->findBy(['active' => true]);
        if ($actualType->getSlug() === 'material') {
            $array_item['product'] = $productsRepository->findOneBy(['qrCode' => $qrCode]);
            return $this->render('NononsenseHomeBundle:Products:input_material.html.twig', $array_item);
        } elseif ($actualType->getSlug() === 'reactivo') {
            if($casNb){
                $array_item['product'] = $productsRepository->findOneBy(['casNumber' => urldecode($casNb)]);
            }
            return $this->render('NononsenseHomeBundle:Products:input_reactivo.html.twig', $array_item);
        } else {
            $this->get('session')->getFlashBag()->add('error', "No se encuentra el tipo de material del producto");
            return $this->redirect($this->generateUrl('nononsense_products_inputs'));
        }
    }

    public function deleteAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('productos_gestion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $product = $em->getRepository('NononsenseHomeBundle:Products')->find($id);

            if ($product) {
                $product->setActive(false);
                $em->persist($product);
                $em->flush();
                $this->get('session')->getFlashBag()->add('message', "El producto se ha inactivado correctamente");
            } else {
                $this->get('session')->getFlashBag()->add('message', "El producto no existe");
            }
        } catch (Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error al intentar inactivar el producto: " . $e->getMessage()
            );
        }

        return $this->redirect($this->generateUrl('nononsense_products'));
    }

    public function destroyAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('productos_gestion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $product = $em->getRepository('NononsenseHomeBundle:Products')->find($id);

            if ($product) {
                $product->setDestroyed(true);
                $em->persist($product);
                $em->flush();
                $this->get('session')->getFlashBag()->add('message', "El producto se ha destruído correctamente");
            } else {
                $this->get('session')->getFlashBag()->add('message', "El producto no existe");
            }
        } catch (Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error al intentar destruir el producto: " . $e->getMessage()
            );
        }

        return $this->redirect($this->generateUrl('nononsense_products'));
    }

    public function productosJsonAction(Request $request)
    {
        $data = [];
        $status = 500;
        $casNumber = $request->get("casNumber");

        try {
            if ($casNumber) {
                $productsRepository = $this->getDoctrine()->getRepository(Products::class);
                /** @var Products $product */
                $product = $productsRepository->findOneBy(['casNumber' => $casNumber]);

                if ($product) {
                    $data = [
                        'name' => $product->getName(),
                        'partNumber' => $product->getPartNumber(),
                        'internalCode' => $product->getInternalCode(),
                        'presentation' => $product->getPresentation(),
                        'provider' => $product->getProvider(),
                        'minStock' => $product->getStockMinimum(),
                        'static' => $product->getStatic()
                    ];
                    $status = 200;
                }
            }
        } catch (Exception $e) {
        }

        $response = [
            'data' => $data,
            'status' => $status
        ];

        return new JsonResponse($response);
    }

    public function listInputsAction(Request $request, $type)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('productos_recepcion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters = Utils::getListFilters($request);
        $filters['type'] = $type;
        $filters["limit_many"] = 15;

        /** @var ProductsRepository $productsInputsRepository */
        $productsInputsRepository = $this->getDoctrine()->getRepository(ProductsInputs::class);

        /** @var ProductsTypesRepository $typesRepository */
        $typesRepository = $this->getDoctrine()->getRepository(ProductsTypes::class);
        $typeObj = $typesRepository->findOneBy(['slug' => $type]);

        if ($request->get("a_excel") == 1) {
            $items = $productsInputsRepository->list($filters, 0);
            return self::exportExcelProductsInputs($items);
        }

        $array_item["filters"] = $filters;
        $array_item["type"] = $typeObj;
        $array_item["items"] = $productsInputsRepository->list($filters);
        $array_item["count"] = $productsInputsRepository->count($filters);
        $array_item['pagination'] = Utils::getPaginator($request, $filters['limit_many'], $array_item["count"]);

        if($typeObj && $typeObj->getSlug() == 'reactivo'){
            return $this->render('NononsenseHomeBundle:Products:list_inputs_reactivo.html.twig', $array_item);
        }else{
            return $this->render('NononsenseHomeBundle:Products:list_inputs_material.html.twig', $array_item);
        }
    }

    public function listOutputsAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('productos_retirada');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters = Utils::getListFilters($request);
        $filters["limit_many"] = 15;

        /** @var ProductsOutputsRepository $productsOutputsRepository */
        $productsOutputsRepository = $this->getDoctrine()->getRepository(ProductsOutputs::class);

        if ($request->get("a_excel") == 1) {
            $items = $productsOutputsRepository->list($filters, 0);
            return self::exportExcelProductsOutputs($items);
        }

        $array_item["filters"] = $filters;
        $array_item["items"] = $productsOutputsRepository->list($filters);
        $array_item["count"] = $productsOutputsRepository->count($filters);
        $array_item["pagination"] = Utils::getPaginator($request, $filters["limit_many"], $array_item["count"]);

        return $this->render('NononsenseHomeBundle:Products:list_outputs.html.twig', $array_item);
    }

    public function editOutputAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('productos_retirada');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        if ($request->getMethod() == 'POST') {
            try {
                $outputAmount = $request->get("amount");
                $inputQr = $request->get("input_id");
                /** @var ProductsInputs $productInput */
                $productInput = $this->getDoctrine()->getRepository(ProductsInputs::class)->getOneByQrCode($inputQr);
                if ($productInput) {
                    $product = $productInput->getProduct();
                    if($product->getType()->getSlug() === 'material'){
                        $remainingAmount = $product->getStock();
                        if($outputAmount > $remainingAmount){
                            throw new Exception('No hay suficiente stock');
                        }
                        while($outputAmount > 0){
                            $productInput = $this->getDoctrine()->getRepository(ProductsInputs::class)->getOneByQrCode($inputQr);
                            if($productInput){
                                $outputAmount = $this->generateSingleOutput($productInput, $outputAmount);
                            }else{
                                throw new Exception('Ha ocurrido un error y no ha sido posible hacer la retirada de todas las unidades.');
                            }
                        }
                    }else{
                        $remainingAmount = $productInput->getRemainingAmount();
                        if($outputAmount > $remainingAmount){
                            throw new Exception('No hay suficiente stock');
                        }
                        $this->generateSingleOutput($productInput, $outputAmount);
                    }
                    if($product->getStock() <= $product->getStockMinimum()){
                        $type = $product->getType()->getName();
                        $name = $product->getName();
                        $this->get('session')->getFlashBag()->add('error', "El " .$type. " " .$name. " ha llagado al límite de stock.");
                    }
                    $this->get('session')->getFlashBag()->add('message', "La retirada se ha guardado correctamente");
                }else{
                    $this->get('session')->getFlashBag()->add('error',"No se ha encontrado el código o no hay stock para este producto.");
                }
                return $this->redirect($this->generateUrl('nononsense_products_outputs'));
            } catch (Exception $e) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error al intentar guardar los datos de la retirada: " . $e->getMessage()
                );
            }
        }
        return $this->render('NononsenseHomeBundle:Products:output.html.twig');
    }

    /**
     * @param ProductsInputs $productInput
     * @param int $outputAmount
     * @return int
     */
    private function generateSingleOutput($productInput, $outputAmount)
    {
        $em = $this->getDoctrine()->getManager();

        $product = $productInput->getProduct();
        $productOutput = new ProductsOutputs();
        $productOutput->setUser($this->getUser());
        $productOutput->setProductInput($productInput);


        $remainingAmount = $productInput->getRemainingAmount();
        if($outputAmount > $remainingAmount){
            $unitsToGet = $outputAmount - $remainingAmount;
            $outputAmount = $remainingAmount;
        }else{
            $unitsToGet = 0;
        }
        //actualizo remainingAmount del productInput y el estado si es necesario
        if($product->getType()->getSlug() === 'reactivo'){
            $state = $this->getDoctrine()->getRepository(ProductsInputStatus::class)->findOneBy(['slug' => 'retirado']);
            $productInput->setState($state);
        }
        $productOutput->setAmount($outputAmount);
        $newRemainingAmount = $remainingAmount - $productOutput->getAmount();
        $productInput->setRemainingAmount($newRemainingAmount);
        $em->persist($productInput);

        //actualizo stock del product
        $stock = $product->getStock();
        $newStock = $stock - $productOutput->getAmount();
        $product->setStock($newStock);
        $em->persist($product);
        $em->persist($productOutput);

        $em->flush();
        return $unitsToGet;
    }

    public function inputDataJsonAction($id)
    {
        $array_return = array();
        $data = array();
        $status = 500;
        try {
            /** @var ProductsInputs $productInput */
            $productInput = $this->getDoctrine()->getRepository(ProductsInputs::class)->findOneBy(['qrCode' => $id]);

            if ($productInput) {
                $product = $productInput->getProduct();

                $receptionDate = $productInput->getReceptionDate();
                $expiryDate = $productInput->getExpiryDate();
                $destructionDate = $productInput->getDestructionDate();

                if($product->getType()->getSlug() === 'reactivo'){
                    $isReactivo = true;
                    $stock = $productInput->getRemainingAmount();

                }else{
                    $stock = $product->getStock();
                    $isReactivo = false;
                }

                $data['id'] = $productInput->getId();
                $data['partNumber'] = $product->getPartNumber();
                $data['stock'] = $stock;
                $data['casNumber'] = $product->getCasNumber();
                $data['internalCode'] = $product->getInternalCode();
                $data['name'] = $product->getName();
                $data['provider'] = $product->getProvider();
                $data['presentation'] = $product->getPresentation();
                $data['lotNumber'] = $productInput->getLotNumber();
                $data['receptionDate'] = ($receptionDate) ? $receptionDate->format('d-m-Y') : '';
                $data['expiryDate'] = ($expiryDate) ? $expiryDate->format('d-m-Y') : '';
                $data['destructionDate'] = ($destructionDate) ? $destructionDate->format('d-m-Y') : '';
                $data['observations'] = $productInput->getObservations();
                $data['isReactivo'] = $isReactivo;

                $status = 200;
            }
        } catch (Exception $e) {
        }

        $array_return['data'] = $data;
        $array_return['status'] = $status;

        return new JsonResponse($array_return);
    }

    private function generateQrProductInput(ProductsInputs $productInput)
    {
        $filename = "qr_material_input_" . $productInput->getId() . ".png";
        $rootdir = $this->get('kernel')->getRootDir();
        $ruta_img_qr = $rootdir . "/files/material_inputs_qr/";
        $label = '';

        $cad_text = '';
        if ($productInput->getExpiryDate()) {
            $cad_text = $productInput->getExpiryDate()->format('Y-m-d');
        }

        $productType = $productInput->getProduct()->getType();
        if ($productType->getSlug() === 'reactivo') {
            $label = 'Cad.' . $cad_text . " - ";
            $label .= 'Dest.' . $productInput->getDestructionDate()->format('Y-m-d');
        }

        $qrCode = new QrCode();
        $qrCode
            ->setText($productInput->getqrCode())
            ->setSize(500)
            ->setPadding(5)
            ->setErrorCorrection('high')
            ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
            ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
            ->setLabel($label)
            ->setLabelFontSize(14)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);

        $qrCode->save($ruta_img_qr . $filename);

        return $filename;
    }

    private function generateQrProductOutput(ProductsOutputs $productOutput)
    {
        $filename = "qr_material_input_" . $productOutput->getId() . ".png";
        $rootdir = $this->get('kernel')->getRootDir();
        $ruta_img_qr = $rootdir . "/files/material_outputs_qr/";

        $data = [
            'type' => 'outputReactivos',
            'code' => $productOutput->getId()
        ];

        $qrCode = new QrCode();
        $qrCode
            ->setText(json_encode($data))
            ->setSize(500)
            ->setPadding(5)
            ->setErrorCorrection('high')
            ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
            ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
            ->setLabelFontSize(14)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);

        $qrCode->save($ruta_img_qr . $filename);

        return $filename;
    }

    public function inputQrAction($id)
    {
        $productInput = null;
        if ($id) {
            $productInput = $this->getDoctrine()->getRepository('NononsenseHomeBundle:ProductsInputs')->find($id);

            if ($productInput) {
                $filename = self::generateQrProductInput($productInput);

                $rootdir = $this->get('kernel')->getRootDir();
                $ruta_img_qr = $rootdir . "/files/material_inputs_qr/";

                $content = file_get_contents($ruta_img_qr . $filename);

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

    public function outputQrAction($id)
    {
        $productOutput = null;
        if ($id) {
            $productOutput = $this->getDoctrine()->getRepository(ProductsOutputs::class)->find($id);
            if ($productOutput) {
                $filename = self::generateQrProductOutput($productOutput);

                $rootdir = $this->get('kernel')->getRootDir();
                $ruta_img_qr = $rootdir . "/files/material_outputs_qr/";
                $content = file_get_contents($ruta_img_qr . $filename);
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

    public function useProductAction($data)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var ProductsInputsRepository $inputsRepository */
        $inputsRepository = $em->getRepository(ProductsInputs::class);
        /** @var ProductsInputs $input */
        $statusRepository = $em->getRepository(ProductsInputStatus::class);
        $openState = $statusRepository->findOneBy(['slug' => 'usado']);
        $finishedState = $statusRepository->findOneBy(['slug' => 'terminado']);
        foreach($data->data->u_qr_data as $key => $qrCode){
            $input = $inputsRepository->findOneBy(['qrCode' => $qrCode]);
            if($input){
                $change = false;
                if($input->getOpenDate() === null){
                    $input->setOpenDate(new DateTime());
                    $input->setState($openState);
                    $change = true;
                }
                if($data->data->u_terminado->{$key} === '1'){
                    $input->setState($finishedState);
                    $change = true;
                }
                if($change){
                    $em->persist($input);
                }
            }
        }
        $em->flush();
        return new Response(true);
    }

    public function jsonOutputDataAction($code){
        $data = ['u_qr_data' => 'Reactivo no registrado'];
        /** @var ProductsInputsRepository $inputsRepository */
        $inputsRepository = $this->getDoctrine()->getRepository(ProductsInputs::class);
        /** @var ProductsInputs $output */
        $input = $inputsRepository->findOneBy(['qrCode' => $code]);

        if($input){
            $arrInput = [$input];
        }else {
            /** @var ProductsDissolutionRepository $dissolutionRepository */
            $dissolutionRepository = $this->getDoctrine()->getRepository(ProductsDissolution::class);
            /** @var ProductsDissolution $dissolution */
            $dissolution = $dissolutionRepository->findOneBy(['qrCode' => $code]);
            $arrInput = $dissolution ? $dissolution->getLines() : [];
        }

        if(count($arrInput) > 0){
            $data = [];
            foreach($arrInput as $key => $input){
                switch($input->getState()->getSlug()){
                    case 'retirado':
                    case 'usado':
                        $openDate = $input->getOpenDate()?: new DateTime();
                        $data[$key] = [
                            'u_nombre_sustancia' => $input->getProduct()->getName(),
                            'u_cas' => ($input->getProduct()->getCasNumber())?:'',
                            'u_lote' => ($input->getLotNumber())?:'',
                            'u_caduc' => ($input->getExpiryDate()) ? $input->getExpiryDate()->format('Y-m-d'):'',
                            'u_cad_prep' => 'test1',
                            'u_proveed' => ($input->getProduct()->getProvider())?:'',
                            'u_date' => $openDate->format('Y-m-d'),
                            'u_qr_data' => $input->getQrCode()
                        ];
                        break;
                    case 'recibido':
                        $data[$key] = ['u_qr_data' => 'Extracción no registrada'];
                        break;
                    case 'terminado':
                        $data[$key] = ['u_qr_data' => 'Reactivo terminado'];
                        break;
                }
                if($input->getExpiryDate() < (new DateTime()) || $input->getDestructionDate() < (new DateTime())){
                    $data[$key] = ['u_qr_data' => 'Caducidad alcanzada.'];
                }
            }
        }

        return new Response(json_encode($data, JSON_FORCE_OBJECT));
    }

    private function exportExcelProducts($items)
    {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties();
        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Part. Number')
            ->setCellValue('B1', 'Cash Number')
            ->setCellValue('C1', 'Nombre')
            ->setCellValue('D1', 'Stock')
            ->setCellValue('E1', 'Proveedor')
            ->setCellValue('F1', 'Stock Mínimo');

        $i = 2;
        foreach ($items as $item) {
            /** @var Products $product */
            $product = $item['product'];
            $phpExcelObject->getActiveSheet()
                ->setCellValue('A' . $i, $product->getPartNumber())
                ->setCellValue('B' . $i, $product->getCasNumber())
                ->setCellValue('C' . $i, $product->getName())
                ->setCellValue('D' . $i, $product->getStock())
                ->setCellValue('E' . $i, $product->getProvider())
                ->setCellValue('F' . $i, $product->getStockMinimum());
            $i++;
        }

        $phpExcelObject->getActiveSheet()->setTitle('Listado de productos');
        $phpExcelObject->setActiveSheetIndex(0);
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'listado_productos.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /** ProductInpus[] $items
     * @param ProductsInputs[] $items
     * @return
     */
    private function exportExcelProductsInputs($items)
    {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties();
        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Id')
            ->setCellValue('B1', 'CAS Number')
            ->setCellValue('C1', 'Part. Number')
            ->setCellValue('D1', 'Nombre')
            ->setCellValue('E1', 'Proveedor')
            ->setCellValue('F1', 'Presentación')
            ->setCellValue('G1', 'Unidades entrantes')
            ->setCellValue('H1', 'Fecha de recepción')
            ->setCellValue('I1', 'Comentarios')
            ->setCellValue('J1', 'Usuario');

        $i = 2;
        foreach ($items as $item) {
            $phpExcelObject->getActiveSheet()
                ->setCellValue('A' . $i, $item->getId())
                ->setCellValue('B' . $i, $item->getProduct()->getCasNumber())
                ->setCellValue('C' . $i, $item->getProduct()->getPartNumber())
                ->setCellValue('D' . $i, $item->getProduct()->getName())
                ->setCellValue('E' . $i, $item->getProduct()->getProvider())
                ->setCellValue('F' . $i, $item->getProduct()->getPresentation())
                ->setCellValue('G' . $i, $item->getAmount())
                ->setCellValue('H' . $i, $item->getReceptionDate()->format('Y-m-d H:i:s'))
                ->setCellValue('I' . $i, $item->getObservations())
                ->setCellValue('J' . $i, $item->getUser()->getName());
            $i++;
        }

        $phpExcelObject->getActiveSheet()->setTitle('Listado recepciones material');
        $phpExcelObject->setActiveSheetIndex(0);
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'listado_recepciones_material.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @param ProductsOutputs[] $items
     * @return
     */
    private function exportExcelProductsOutputs($items)
    {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties();
        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Id salida')
            ->setCellValue('B1', 'Id entrada')
            ->setCellValue('C1', 'Part Number')
            ->setCellValue('D1', 'Nombre')
            ->setCellValue('E1', 'Cantidad')
            ->setCellValue('F1', 'Fecha de retirada')
            ->setCellValue('G1', 'Usuario');

        $i = 2;
        foreach ($items as $item) {
            $phpExcelObject->getActiveSheet()
                ->setCellValue('A' . $i, $item->getId())
                ->setCellValue('B' . $i, $item->getProductInput()->getId())
                ->setCellValue('C' . $i, $item->getProductInput()->getProduct()->getPartNumber())
                ->setCellValue('D' . $i, $item->getProductInput()->getProduct()->getName())
                ->setCellValue('E' . $i, $item->getAmount())
                ->setCellValue('F' . $i, $item->getDate()->format('Y-m-d H:i:s'))
                ->setCellValue('G' . $i, $item->getUser()->getName());

            $i++;
        }

        $phpExcelObject->getActiveSheet()->setTitle('Listado retiradas material');
        $phpExcelObject->setActiveSheetIndex(0);
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'listado_retiradas_material.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @param Request $request
     * @return string|null
     */
    private function getEndDate(Request $request)
    {
        if(strtotime($request->get("destructionDate")) < strtotime($request->get("expiryDate"))){
            $endDate = $request->get("destructionDate");
        }else{
            $endDate = $request->get("expiryDate");
        }
        return $endDate;
    }

}