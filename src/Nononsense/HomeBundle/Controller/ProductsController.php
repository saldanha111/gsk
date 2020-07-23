<?php

namespace Nononsense\HomeBundle\Controller;

use Datetime;
use Exception;
use Nononsense\HomeBundle\Entity\ProductsInputStatus;
use Nononsense\HomeBundle\Entity\ProductsInputStatusRepository;
use Nononsense\HomeBundle\Entity\ProductsOutputsRepository;
use Nononsense\HomeBundle\Entity\ProductsRepository;
use Nononsense\HomeBundle\Entity\ProductsTypesRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nononsense\HomeBundle\Entity\Products;
use Nononsense\HomeBundle\Entity\ProductsTypes;
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

    public function receptionAction(Request $request, $type)
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
                $this->generateUrl('nononsense_products_inputs', ['type' => $type, 'qrCode' => $qrCode])
            );
        }

        $data = ['type' => $type];
        return $this->render('NononsenseHomeBundle:Products:reception_index.html.twig', $data);
    }

    public function listAction(Request $request, $type)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('productos_gestion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters = $this->getListFilters($request);
        $filters['type'] = $type;
        $filters["limit_many"] = 15;

        /** @var ProductsRepository $productRepository */
        $productRepository = $this->getDoctrine()->getRepository(Products::class);

        if ($request->get("a_excel") == 1) {
            $items = $productRepository->list($filters, 0);
            return $this->exportExcelProducts($items);
        }

        $totalItems = $productRepository->count($filters);

        $array_item = [
            "filters" => $filters,
            "type" => $type,
            "items" => $productRepository->list($filters),
            "count" => $totalItems,
            "pagination" => Utils::getPaginator($request, $filters['limit_many'], $totalItems)
        ];

        return $this->render('NononsenseHomeBundle:Products:index.html.twig', $array_item);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getListFilters(Request $request)
    {
        $filters = [];

        if ($request->get("page")) {
            $filters["limit_from"] = $request->get("page") - 1;
        } else {
            $filters["limit_from"] = 0;
        }

        foreach ($request->query->all() as $key => $element) {
            if (strpos($key, 'f_') === 0) {
                $filterName = str_replace('f_', '', $key);
                $filters[$filterName] = $element;
            }
        }

        return $filters;
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
                        "El " . $actualType->getName(
                        ) . " se ha guardado correctamente, Ahora puedes generar la recepción."
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
            $product->setName($request->get("name"));
            $product->setInternalCode($request->get("internalCode"));
            $product->setPartNumber($request->get("partNumber"));
            $product->setCasNumber($request->get("casNumber"));
            $product->setProvider($request->get("provider"));
            $product->setPresentation($request->get("presentation"));
            $product->setStockMinimum($request->get("stockMinimum"));
            $product->setActive($request->get("active"));
            $product->setType($actualType);

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

    public function inputAction(Request $request, $type, $qrCode)
    {
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
                    $product = $productsRepository->find($request->get("productId"));
                }

                if (!$state || !$product) {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        "Se ha producido un error al crear la entrada. Vuelva a intentarlo."
                    );
                    return $this->redirect(
                        $this->generateUrl('nononsense_products_inputs', ['type' => $type, 'qrCode' => $qrCode])
                    );
                }

                $destructionDate = ($request->get("destructionDate")) ? new Datetime($request->get("destructionDate")) : null;
                $expiryDate = ($request->get("expiryDate")) ? new Datetime($request->get("expiryDate")) : null;

                $productInput->setQrCode($qrCode);
                $productInput->setReceptionDate(new Datetime());
                $productInput->setDestructionDate($destructionDate);
                $productInput->setExpiryDate($expiryDate);
                $productInput->setAmount($request->get("amount"));
                $productInput->setRemainingAmount($request->get("amount"));
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
                    $product->setStock($stock + $request->get("amount"));
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

        $array_item = array();
        $array_item['type'] = $actualType;
        $array_item['productInput'] = $productInput;
        $array_item['qrCode'] = $qrCode;
        if ($actualType->getSlug() === 'material') {
            /** @var ProductsRepository $productsRepository */
            $productsRepository = $this->getDoctrine()->getRepository(Products::class);
            /** @var Products $product */
            $array_item['product'] = $productsRepository->findOneBy(['qrCode' => $qrCode]);
            return $this->render('NononsenseHomeBundle:Products:input_material.html.twig', $array_item);
        } elseif ($actualType->getSlug() === 'reactivo') {
            return $this->render('NononsenseHomeBundle:Products:input_reactivo.html.twig', $array_item);
        } else {
            $this->get('session')->getFlashBag()->add('error', "No se encuentra el tipo de material del producto");
            return $this->redirect($this->generateUrl('nononsense_products_inputs'));
        }
    }

//    public function editInputAction(Request $request, $type, $id, $qrCode)
//    {
//        $is_valid = $this->get('app.security')->permissionSeccion('productos_recepcion');
//        if (!$is_valid) {
//            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
//        }
//
//        $productInput = $this->getDoctrine()->getRepository('NononsenseHomeBundle:ProductsInputs')->find($id);
//
//        if (!$productInput) {
//            $productInput = new ProductsInputs();
//        }
//
//        if ($request->getMethod() == 'POST') {
//            try {
//                $em = $this->getDoctrine()->getManager();
//
//                $productInput->setReceptionDate(new Datetime());
//                $productInput->setDestructionDate(new Datetime($request->get("destructionDate")));
//
//                if ($request->get("expiryDate") != '') {
//                    $productInput->setExpiryDate(new Datetime($request->get("expiryDate")));
//                }
//
//                if ($request->get("openDate") != '') {
//                    $productInput->setOpenDate(new Datetime($request->get("openDate")));
//                }
//
//                //actualizo amount y remainingAmount del productInput pero solo si estoy creando.
//                if (!$productInput->getId()) {
//                    $productInput->setAmount($request->get("amount"));
//                    $productInput->setRemainingAmount($request->get("amount"));
//                }
//
//                $update_stock_product = 0;
//
//                //si la entrada ya tenía un product y se lo estoy cambiando en esta edicion, actualizo los stocks del producto anterior y del nuevo
//                if ($productInput->getProduct()) {
//                    if ($request->get("product") != $productInput->getProduct()->getId()) {
//                        $product_before = $productInput->getProduct();
//                        $stock = $product_before->getStock();
//                        $newStock = $stock - $productInput->getAmount();
//                        $product_before->setStock($newStock);
//                        $em->persist($product_before);
//
//                        $update_stock_product = 1;
//                    }
//                }
//
//
//                //solo dejo modificar el producto si no tiene salidas asociadas
//                if (count($productInput->getProductsOutputs()) == 0) {
//                    $product = $this->getDoctrine()->getRepository('NononsenseHomeBundle:Products')->find(
//                        $request->get("product")
//                    );
//                    if ($product) {
//                        $productInput->setProduct($product);
//
//                        //actualizo stock del product pero solo si estoy creando o si le estoy cambiando el producto a la entrada
//                        if ($update_stock_product == 1 || !$productInput->getId()) {
//                            $stock = $product->getStock();
//                            $newStock = $stock + $productInput->getAmount();
//                            $product->setStock($newStock);
//                            $em->persist($product);
//                        }
//                    }
//                }
//
//
//                $em->persist($productInput);
//                $em->flush();
//
//                self::generateQrProductInput($productInput);
//
//                $this->get('session')->getFlashBag()->add(
//                    'message',
//                    "La recepción de material se ha guardado correctamente"
//                );
//                return $this->redirect($this->generateUrl('nononsense_products_inputs'));
//            } catch (\Exception $e) {
//                $this->get('session')->getFlashBag()->add(
//                    'error',
//                    "Error al intentar guardar los datos de la recepción: " . $e->getMessage()
//                );
//            }
//        }
//
//
//        $array_item = array();
//
//        $array_item['type'] = $type;
//        $array_item['productInput'] = $productInput;
//
//        return $this->render('NononsenseHomeBundle:Products:input.html.twig', $array_item);
//    }


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

//    public function deleteInputAction(Request $request, $type, $id)
//    {
//        $is_valid = $this->get('app.security')->permissionSeccion('productos_recepcion');
//        if (!$is_valid) {
//            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
//        }
//
//        try {
//            $em = $this->getDoctrine()->getManager();
//            $productInput = $em->getRepository('NononsenseHomeBundle:ProductsInputs')->find($id);
//
//            if ($productInput) {
//                if (count($productInput->getProductsOutputs()) > 0) {
//                    $this->get('session')->getFlashBag()->add(
//                        'message',
//                        "La recepción de material no se puede borrar porque tiene salidas asociadas"
//                    );
//                } else {
//                    $em->remove($productInput);
//
//                    //actualizo stock del product
//                    $product = $productInput->getProduct();
//                    $stock = $product->getStock();
//                    $newStock = $stock - $productInput->getAmount();
//                    $product->setStock($newStock);
//                    $em->persist($product);
//
//                    $em->flush();
//                    $this->get('session')->getFlashBag()->add(
//                        'message',
//                        "La recepción de material se ha borrado correctamente"
//                    );
//                }
//            } else {
//                $this->get('session')->getFlashBag()->add('message', "La recepción de material no existe");
//            }
//        } catch (\Exception $e) {
//            $this->get('session')->getFlashBag()->add(
//                'error',
//                "Error al intentar borrar la recepción de material: " . $e->getMessage()
//            );
//        }
//
//        return $this->redirect($this->generateUrl('nononsense_products_inputs'));
//    }

//    public function deleteOutputAction(Request $request, $id)
//    {
//        $is_valid = $this->get('app.security')->permissionSeccion('productos_retirada');
//        if (!$is_valid) {
//            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
//        }
//
//        try {
//            $em = $this->getDoctrine()->getManager();
//            $productOutput = $em->getRepository('NononsenseHomeBundle:ProductsOutputs')->find($id);
//
//            if ($productOutput) {
//                //actualizo remainingAmount del productInput
//                $productInput = $productOutput->getProductInput();
//                $remainingAmount = $productInput->getRemainingAmount();
//                $newRemainingAmount = $remainingAmount + $productOutput->getAmount();
//                $productInput->setRemainingAmount($newRemainingAmount);
//                $em->persist($productInput);
//
//                //actualizo stock del product
//                $product = $productOutput->getProductInput()->getProduct();
//                $stock = $product->getStock();
//                $newStock = $stock + $productOutput->getAmount();
//                $product->setStock($newStock);
//                $em->persist($product);
//
//                $em->remove($productOutput);
//
//                $em->flush();
//                $this->get('session')->getFlashBag()->add(
//                    'message',
//                    "La retirada de material se ha borrado correctamente"
//                );
//            } else {
//                $this->get('session')->getFlashBag()->add('message', "La retirada de material no existe");
//            }
//        } catch (\Exception $e) {
//            $this->get('session')->getFlashBag()->add(
//                'error',
//                "Error al intentar borrar la retirada de material: " . $e->getMessage()
//            );
//        }
//
//        return $this->redirect($this->generateUrl('nononsense_products_outputs'));
//    }


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
                        'minStock' => $product->getStockMinimum()
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

        $filters = $this->getListFilters($request);
        $filters['type'] = $type;
        $filters["limit_many"] = 15;

        /** @var ProductsRepository $productsInputsRepository */
        $productsInputsRepository = $this->getDoctrine()->getRepository(ProductsInputs::class);

        if ($request->get("a_excel") == 1) {
            $items = $productsInputsRepository->list($filters, 0);
            return self::exportExcelProductsInputs($items);
        }

        $array_item["filters"] = $filters;
        $array_item["type"] = $type;
        $array_item["items"] = $productsInputsRepository->list($filters);
        $array_item["count"] = $productsInputsRepository->count($filters);
        $array_item['pagination'] = Utils::getPaginator($request, $filters['limit_many'], $array_item["count"]);

        return $this->render('NononsenseHomeBundle:Products:list_inputs.html.twig', $array_item);
    }

    public function listOutputsAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('productos_retirada');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters = $this->getListFilters($request);
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
                    $stock = $productInput->getRemainingAmount();
                }else{
                    $stock = $product->getStock();
                }

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

        $qrCode = new QrCode();
        $qrCode
            ->setText($productOutput->getId())
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

    private function exportExcelProducts($items)
    {
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

        $i = 2;
        foreach ($items as $item) {
            $phpExcelObject->getActiveSheet()
                ->setCellValue('A' . $i, $item["partNumber"])
                ->setCellValue('B' . $i, $item["cashNumber"])
                ->setCellValue('C' . $i, $item["name"])
                ->setCellValue('D' . $i, $item["description"])
                ->setCellValue('E' . $i, $item["stock"])
                ->setCellValue('F' . $i, $item["provider"])
                ->setCellValue('G' . $i, $item["stockMinimum"])
                ->setCellValue('H' . $i, $item["analysisMethod"])
                ->setCellValue('I' . $i, $item["observations"])
                ->setCellValue('J' . $i, $item["nameType"]);

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

    private function exportExcelProductsInputs($items)
    {
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

        $i = 2;
        foreach ($items as $item) {
            $phpExcelObject->getActiveSheet()
                ->setCellValue('A' . $i, $item->getProduct()->getPartNumber())
                ->setCellValue('B' . $i, $item->getProduct()->getName())
                ->setCellValue('C' . $i, $item->getAmount())
                ->setCellValue('D' . $i, $item->getRemainingAmount())
                ->setCellValue('E' . $i, $item->getReceptionDate()->format('Y-m-d H:i:s'))
                ->setCellValue('F' . $i, $item->getExpiryDate()->format('Y-m-d H:i:s'))
                ->setCellValue('G' . $i, $item->getDestructionDate()->format('Y-m-d H:i:s'))
                ->setCellValue('H' . $i, $item->getOpenDate()->format('Y-m-d H:i:s'));

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

    private function exportExcelProductsOutputs($items)
    {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties();
        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Part. Number')
            ->setCellValue('B1', 'Nombre')
            ->setCellValue('C1', 'Cantidad')
            ->setCellValue('D1', 'Fecha retirada');

        $i = 2;
        foreach ($items as $item) {
            $phpExcelObject->getActiveSheet()
                ->setCellValue('A' . $i, $item["productPartNumber"])
                ->setCellValue('B' . $i, $item["productName"])
                ->setCellValue('C' . $i, $item["amount"])
                ->setCellValue('D' . $i, $item["date"]->format('Y-m-d'));

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

}