<?php

namespace Nononsense\HomeBundle\Controller;

use Datetime;
use Exception;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\HomeBundle\Entity\InstanciasSteps;
use Nononsense\HomeBundle\Entity\ProductsDissolution;
use Nononsense\HomeBundle\Entity\ProductsDissolutionRepository;
use Nononsense\HomeBundle\Entity\ProductsInputsRepository;
use Nononsense\HomeBundle\Entity\ProductsInputStatus;
use Nononsense\HomeBundle\Entity\ProductsInputStatusRepository;
use Nononsense\HomeBundle\Entity\ProductsOutputsRepository;
use Nononsense\HomeBundle\Entity\ProductsRepository;
use Nononsense\HomeBundle\Entity\ProductsSignatures;
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

    public function receptionAction(Request $request, $type, $casNb = null)
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
            'casNumber' => urldecode($casNb)
        ];

        if($activeType && $activeType->getSlug() == 'reactivo'){
            $qrCode = $this->generateReactivoQrCode();
            return $this->redirect(
                $this->generateUrl('nononsense_products_inputs', ['type' => $type, 'qrCode' => $qrCode])
            );
        }else{
            return $this->render('NononsenseHomeBundle:Products:reception_index.html.twig', $data);
        }
    }

    public function newAction(Request $request, $type)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('productos_gestion');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        /** @var ProductsTypesRepository $productsTypeRepository */
        $productsTypeRepository = $this->getDoctrine()->getRepository(ProductsTypes::class);
        /** @var ProductsTypes $activeType */
        $activeType = $productsTypeRepository->findOneBy(['slug' => $type]);

        if($activeType){
            $qrCode = $this->generateMaterialQrCode();
            return $this->redirect(
                $this->generateUrl(
                    'nononsense_products_edit',
                    ['type' => $type, 'id' => 0, 'qrCode' => $qrCode]
                )
            );
        }else{
            $this->get('session')->getFlashBag()->add(
                'error',
                "No se reconoce el tipo de material o reactivo. Vuelve a intentarlo."
            );
            return $this->redirect(
                $this->generateUrl('nononsense_home_homepage')
            );
        }
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
        }elseif($request->get("a_pdf") == 1){
            $items = $productInputRepository->listForStock($filters, 0);
            return $this->exportPDFProducts($items);
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
                    if($actualType->getSlug() === 'material'){
                        $stockEdited = $this->editStockMaterial($request->get('amount'), $product, $request->get('observations'), true);
                        if(!$stockEdited){
                            $em = $this->getDoctrine()->getManager();
                            $em->remove($product);
                            $em->flush();
                            $this->get('session')->getFlashBag()->add(
                                'error',
                                'El ' . $actualType->getName() . ' no se ha guardado correctamente, vuelve a intentarlo'
                            );
                        }else{
                            $this->get('session')->getFlashBag()->add(
                                'message',
                                'El ' . $actualType->getName() . ' se ha guardado correctamente, Ahora puedes generar la recepción.'
                            );
                        }
                    }else{
                        $this->get('session')->getFlashBag()->add(
                            'message',
                            'El ' . $actualType->getName() . ' se ha guardado correctamente, Ahora puedes generar la recepción.'
                        );
                        return $this->redirect(
                            $this->generateUrl('nononsense_products_reception', ['type' => $actualType->getSlug()])
                        );
                    }
                } else {
                    $this->get('session')->getFlashBag()->add(
                        'message',
                        "El " . $actualType->getName() . " se ha guardado correctamente"
                    );
                }
            } else {
                $this->get('session')->getFlashBag()->add('error', $saved['message']);
            }
            return $this->redirect(
                $this->generateUrl('nononsense_products_input_list', ['type' => $actualType->getSlug()])
            );
        }

        if ($qrCode) {
            $product->setQrCode($qrCode);
        }

        $array_item = array();
        $array_item['presentations'] =
            $this->getDoctrine()
                ->getRepository(ProductsPresentation::class)
                ->findBy(['active' => true]);
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

        $em = $this->getDoctrine()->getManager();
        /** @var ProductsRepository $productsRepository */
        $productsRepository = $em->getRepository(Products::class);
        /** @var Products $product */
        $product = $productsRepository->find($id);

        if(!$product){
            $this->get('session')->getFlashBag()->add('error', 'No se ha encontrado el producto');
            return $this->redirect($this->generateUrl('nononsense_products'));
        }
        $productType = $product->getType();

        if ($request->getMethod() == 'POST') {
            $error = 0;
            $password = $request->get('password');
            if(!$this->get('utilities')->checkUser($password)){
                $this->get('session')->getFlashBag()->add('error', "La contraseña no es correcta.");
                $error = 1;
            }

            $observations = $request->get('observations');
            if(!$observations){
                $this->get('session')->getFlashBag()->add('error', "Para realizar alguna modificación necesitas escribir el motivo en el cuadro de observaciones.");
                $error = 1;
            }

            if(!$error){
                $stockEdited = false;
                $oldMinStock = $product->getStockMinimum();
                $minStockEdited = $this->editMinStock($request->get('stockMinimum'), $product);
                if($minStockEdited){
                    $now = new DateTime();
                    $signature = 'Modificación de stock mínimo registrado con contraseña de usuario ' . $this->getUser()->getName() . ' el día ' . $now->format('d-m-Y H:i:s');
                    $this->saveSignature(
                        $product,
                        $oldMinStock,
                        $request->get('stockMinimum'),
                        'Edit minStock',
                        $signature,
                        $this->getUser(), $request->get('observations')
                    );
                }
                if($productType->getSlug() == 'material' && $product->getStock() != $request->get('stock')){
                    $oldStock = $product->getStock();
                    $stockEdited = $this->editStockMaterial((int) $request->get('stock'), $product, $request->get('observations'), false);
                    if($stockEdited){
                        $now = new DateTime();
                        $signature = 'Modificación de stock registrado con contraseña de usuario ' . $this->getUser()->getName() . ' el día ' . $now->format('d-m-Y H:i:s');
                        $this->saveSignature(
                            $product,
                            $oldStock,
                            $request->get('stock'),
                            'Edit stock',
                            $signature,
                            $this->getUser(),
                            $request->get('observations')
                        );
                    }
                }elseif(
                    $productType->getSlug() == 'reactivo' &&
                    $product->getStock() > 0 &&
                    $request->get('output') &&
                    count($request->get('output'))
                ){
                    $oldStock = $product->getStock();
                    $stockEdited = $this->editStockReactivo([$request->get('output')], $product);
                    if($stockEdited){
                        $now = new DateTime();
                        $signature = 'Modificación de stock registrado con contraseña de usuario ' . $this->getUser()->getName() . ' el día ' . $now->format('d-m-Y H:i:s');
                        $this->saveSignature(
                            $product,
                            $oldStock,
                            ($oldStock - count($request->get('output'))),
                            'Edit stock',
                            $signature,
                            $this->getUser(),
                            $request->get('observations')
                        );
                    }
                }

                $oldActive = $product->getActive();
                $active = (bool) $request->get('active');
                $product->setActive($active );
                $em->persist($product);
                $em->flush();

                $activeEdited = $oldActive != $product->getActive();
                if($activeEdited){
                    $now = new DateTime();
                    $signature = 'Modificación del estado registrado con contraseña de usuario ' . $this->getUser()->getName() . ' el día ' . $now->format('d-m-Y H:i:s');
                    $this->saveSignature(
                        $product,
                        (int) $oldActive,
                        (int) $product->getActive(),
                        'Edit Active',
                        $signature,
                        $this->getUser(),
                        $request->get('observations')
                    );
                }

                if($minStockEdited || $stockEdited || $activeEdited){
                    $this->get('session')->getFlashBag()->add('message', 'Datos actualizados con éxito');
                }
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
        if($minStock && $product->getStockMinimum() != $minStock){
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
     * @param string $comment
     * @param bool $new
     * @return bool
     * @throws Exception
     */
    private function editStockMaterial(int $newStock, Products $product, string $comment = '', $new = false): bool
    {
        $productStock = $product->getStock();
        if($productStock != $newStock){
            $diff = $newStock - $productStock;
            if($diff > 0){
                $result = $this->insertMaterial($product, $diff, $comment);
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
        }elseif ($new){
            $result = $this->insertMaterial($product, 0, $comment);
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

    private function insertMaterial($product, $amount, $comment)
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
        $productInput->setObservations($comment);
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

    private function saveSignature($product, $oldValue, $newValue, $action, $signature, $user, $observations)
    {
        $productSignature = new ProductsSignatures();
        $productSignature->setAction($action)
            ->setUser($user)
            ->setProduct($product)
            ->setOldValue($oldValue)
            ->setNewValue($newValue)
            ->setSignature($signature)
            ->setObservations($observations);
        $em = $this->getDoctrine()->getManager();
        $em->persist($productSignature);
        $em->flush();
        return true;
    }

    public function inputAction(Request $request, $type, $qrCode, $internalCode = null)
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
                    $endDate = $request->get("destructionDate");
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
            if($array_item['product']){
                return $this->render('NononsenseHomeBundle:Products:input_material.html.twig', $array_item);
            }else{
                $this->get('session')->getFlashBag()->add('error', "No se encuentra el material.");
                return $this->redirect($this->generateUrl('nononsense_products_input_list', ['type' =>$actualType->getSlug()]));
            }
        } elseif ($actualType->getSlug() === 'reactivo') {
            if($internalCode){
                $array_item['product'] = $productsRepository->findOneBy(['internalCode' => urldecode($internalCode)]);
            }
            return $this->render('NononsenseHomeBundle:Products:input_reactivo.html.twig', $array_item);
        } else {
            $this->get('session')->getFlashBag()->add('error', "No se encuentra el tipo de material del producto");
            return $this->redirect($this->generateUrl('nononsense_products'));
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
        $internalCode = $request->get("internalCode");

        try {
            if ($internalCode) {
                $productsRepository = $this->getDoctrine()->getRepository(Products::class);
                /** @var Products $product */
                $product = $productsRepository->findOneBy(['internalCode' => $internalCode]);

                if ($product) {
                    $data = [
                        'name' => $product->getName(),
                        'partNumber' => $product->getPartNumber(),
                        'casNumber' => $product->getCasNumber(),
                        'internalCode' => $product->getInternalCode(),
                        'presentation' => $product->getPresentation(),
                        'provider' => $product->getProvider(),
                        'minStock' => $product->getStockMinimum(),
                        'static' => $product->getStatic(),
                        'active' => $product->getActive()
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

        /** @var ProductsInputsRepository $productsInputsRepository */
        $productsInputsRepository = $this->getDoctrine()->getRepository(ProductsInputs::class);

        /** @var ProductsTypesRepository $typesRepository */
        $typesRepository = $this->getDoctrine()->getRepository(ProductsTypes::class);
        $typeObj = $typesRepository->findOneBy(['slug' => $type]);

        if ($request->get("a_excel") == 1) {
            $items = $productsInputsRepository->list($filters, 0);
            return self::exportExcelProductsInputs($items, $type);
        }elseif ($request->get("a_pdf") == 1) {
            $items = $productsInputsRepository->list($filters, 0);
            return self::exportPDFProductsInputs($items, $type);
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
                        $this->get('session')->getFlashBag()->add('error', "El " .$type. " " .$name. " ha llegado al límite de stock.");
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

    public function useReactivoAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('reactivos_use');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        if ($request->getMethod() == 'POST') {
            try {
                $em = $this->getDoctrine()->getManager();
                $type = $request->get("type");
                $id = $request->get("UseInputId");
                if ($request->get("expiryDateUse")) {
                    $expiryDate = new DateTime($request->get("expiryDateUse"));
                }
                $productInput = $em->getRepository(ProductsInputs::class)->find($id);
                $usedState = $em->getRepository(ProductsInputStatus::class)->findOneBy(['slug' => 'usado']);
                $endState = $em->getRepository(ProductsInputStatus::class)->findOneBy(['slug' => 'terminado']);

                if ($productInput) {
                    if ($type === 'end' && $productInput->getState()->getSlug() === 'usado') {
                        $productInput->setState($endState);
                    } elseif ($type === 'use' && $productInput->getState()->getSlug() === 'retirado') {
                        $productInput->setExpiryDate($expiryDate);
                        $productInput->setState($usedState);
                    }
                    $em->persist($productInput);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add(
                        'message',
                        "El producto se ha marcado como " . $productInput->getState()->getName() . " correctamente"
                    );
                } else {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        "No se ha podido marcar el reactivo."
                    );
                }
            } catch (Exception $e) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "No se ha podido marcar el reactivo."
                );
            }
        }
        return $this->render('NononsenseHomeBundle:Products:use.html.twig');
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
        $now = new DateTime();
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
                $data['isExpired'] = ($destructionDate < $now || $expiryDate < $now);
                $data['status'] = $productInput->getState()->getSlug();
                $data['observations'] = $productInput->getObservations();
                $data['isReactivo'] = $isReactivo;
                $data['minStock'] = $product->getStockMinimum();

                $status = 200;
            }
        } catch (Exception $e) {
        }

        $array_return['data'] = $data;
        $array_return['status'] = $status;

        return new JsonResponse($array_return);
    }

    public function useDataJsonAction($id)
    {
        $array_return = array();
        $data = array();
        $status = 500;
        $now = new DateTime();
        $em = $this->getDoctrine()->getManager();
        $message = '';
        try {
            /** @var ProductsInputs $productInput */
            $productInput = $em->getRepository(ProductsInputs::class)->findOneBy(['qrCode' => $id]);

            if ($productInput) {
                $product = $productInput->getProduct();

                $receptionDate = $productInput->getReceptionDate();
                $expiryDate = $productInput->getExpiryDate();
                $destructionDate = $productInput->getDestructionDate();

                if($product->getType()->getSlug() === 'reactivo'){
                    $isReactivo = true;
                }else{
                    $isReactivo = false;
                }

                if($productInput->getState()->getSlug() == 'recibido'){
                    /*
                     * Envío por email al administrador de pedidos
                     */
                    $adminGroup = $em->getRepository(Groups::class)->findBy(["name" => 'reactivos-admin']);
                    $usersAdmin = $em->getRepository(GroupUsers::class)->findBy(["group" => $adminGroup]);
                    $user = $this->getUser()->getName();
                    $subject = "Error en la utilización de reactivo";
                    $message = "El usuario " . $user . " ha intentado utilizar el reactivo " . $product->getName() . " sin realizar previamente la retirada del almacén.";
                    $message .= "<br/> El " . (new DateTime())->format('d-m-Y H:i:s');
                    foreach($usersAdmin as $adm){
                        $this->get('utilities')->sendNotification($adm->getUser()->getEmail(), '', '', '', $subject, $message);
                    }
                }

                if($destructionDate < $now || $expiryDate < $now){
                    if($destructionDate < $now){
                        $message = 'destrucción ' . $destructionDate->format('Y-m-d H:i:s');
                    }else{
                        $message = 'caducidad ' . $expiryDate->format('Y-m-d H:i:s');
                    }
                    $state = $em->getRepository(ProductsInputStatus::class)->findOneBy(['slug' => 'caducado']);
                    $productInput->setState($state);
                    $em->persist($productInput);
                    $em->flush();
                }

                $data['id'] = $productInput->getId();
                $data['partNumber'] = $product->getPartNumber();
                $data['casNumber'] = $product->getCasNumber();
                $data['internalCode'] = $product->getInternalCode();
                $data['name'] = $product->getName();
                $data['provider'] = $product->getProvider();
                $data['presentation'] = $product->getPresentation();
                $data['lotNumber'] = $productInput->getLotNumber();
                $data['receptionDate'] = ($receptionDate) ? $receptionDate->format('d-m-Y') : '';
                $data['expiryDate'] = ($expiryDate) ? $expiryDate->format('d-m-Y') : '';
                $data['destructionDate'] = ($destructionDate) ? $destructionDate->format('d-m-Y') : '';
                $data['isExpired'] = ($destructionDate < $now || $expiryDate < $now);
                $data['status'] = $productInput->getState()->getSlug();
                $data['observations'] = $productInput->getObservations();
                $data['isReactivo'] = $isReactivo;
                $data['message'] = $message;

                $status = 200;
            }
        } catch (Exception $e) {
        }

        $array_return['data'] = $data;
        $array_return['status'] = $status;

        return new JsonResponse($array_return);
    }

    public function checkDisolutionAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('reactivos_disolution');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        if ($request->getMethod() == 'POST') {
            $code = $request->get('input_id');
            $stepRepository = $this->getDoctrine()->getRepository(InstanciasSteps::class);
            $instancias = $stepRepository->search('list',["content" => $code , 'user' => $this->getUser()]);
            $minId = 0;
            if($instancias){
                foreach($instancias as $inst){
                    if($minId === 0 || $inst['id_grid'] < $minId){
                        $minId = $inst['id_grid'];
                    }
                }
                return $this->redirect($this->generateUrl('nononsense_search').'?id='.$minId);
            }else{
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "No se ha encontrado la disolución."
                );
            }
        }
        return $this->render('NononsenseHomeBundle:Products:check_disolution.html.twig');
    }

    private function generateQrProductInput(ProductsInputs $productInput)
    {
        $filename = "qr_material_input_" . $productInput->getId() . ".png";
        $rootdir = $this->get('kernel')->getRootDir();
        $ruta_img_qr = $rootdir . "/files/material_inputs_qr/";
        $qrWidth = 154;
        $qrPadding = 2;

        $productType = $productInput->getProduct()->getType();
        if ($productType->getSlug() === 'reactivo') {
            $text = [
                $productInput->getProduct()->getInternalCode(),
                'F.Destruccion: ' . $productInput->getDestructionDate()->format('Y-m-d')
            ];
        }else{
            $text = [$productInput->getProduct()->getInternalCode()];
        }

        $qrCode = new QrCode();
        $qrCode
            ->setText($productInput->getqrCode())
            ->setSize($qrWidth)
            ->setPadding($qrPadding)
            ->setErrorCorrection('high')
            ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
            ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
            ->setImageType(QrCode::IMAGE_TYPE_PNG);

        $qrCode->save($ruta_img_qr . $filename);

        return $this->addQrInfoData($ruta_img_qr . $filename, ($qrWidth + $qrPadding*2), $text);
    }

    /**
     * @param $qrPath
     * @param $qrWidth
     * @param $text
     */
    private function addQrInfoData($qrPath, $qrWidth, $text)
    {
        $lines = count($text);
        $squareHeight = 20*$lines;
        $rectangle = imagecreatetruecolor($qrWidth, $squareHeight);
        $white = imagecolorallocate($rectangle, 255, 255, 255);
        imagefilledrectangle($rectangle, 1, 1, $qrWidth-2, $squareHeight-3, $white);

        $qrImage = imagecreatefrompng($qrPath);
        $black = imagecolorallocate($rectangle, 0, 0, 0);
        $rootdir = $this->get('kernel')->getRootDir();
        $font_path = $rootdir . '/Resources/font/opensans.ttf';

        $space = 15;
        foreach($text as $line){
            imagettftext($rectangle, 7, 0, 4, $space, $black, $font_path, $line);
            $space += $space;
        }

        ob_start();
        $new = imagecreate($qrWidth, $qrWidth+$squareHeight);
        imagecopy($new, $qrImage, 0, 0, 0, 0, $qrWidth, $qrWidth);
        imagecopy($new, $rectangle, 0, $qrWidth+1, 0, 0, $qrWidth, $squareHeight);
        imagepng($new);
        $content = ob_get_clean();

        // Clear Memory
        imagedestroy($qrImage);
        imagedestroy($rectangle);
        imagedestroy($new);

        return $content;
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
                $qrImage = self::generateQrProductInput($productInput);

                $response = new Response();
                $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, 'image.png');
                $response->headers->set('Content-Disposition', $disposition);
                $response->headers->set('Content-Type', 'image/png');
                $response->setContent($qrImage);
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
                    if($data->data->u_caduc{$key}){
                        $expiryDate = $this->getEndDate($input->getDestructionDate()->format('Y-m-d H:i:s'), $data->data->u_caduc{$key});
                        if($expiryDate){
                            $input->setDestructionDate(new DateTime($expiryDate));
                            $input->setExpiryDate(new DateTime($expiryDate));
                        }
                    }
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
        $data[0] = ['u_qr_data' => 'Reactivo no registrado'];
        /** @var ProductsInputsRepository $inputsRepository */
        $inputsRepository = $this->getDoctrine()->getRepository(ProductsInputs::class);
        /** @var ProductsInputs $output */
        $input = $inputsRepository->findOneBy(['qrCode' => $code]);

        if($input){
            $arrInput = [$input];
            if(count($arrInput) > 0){
                $data = [];
                foreach($arrInput as $key => $input){
                    switch($input->getState()->getSlug()){
                        case 'usado':
                            $openDate = $input->getOpenDate()?: new DateTime();
                            $data[$key] = [
                                'u_tipo_muest' => $input->getProduct()->getName(),
                                'u_cas' => ($input->getProduct()->getCasNumber())?:'',
                                'u_lote' => ($input->getLotNumber())?:'',
                                'u_caduc' => ($input->getExpiryDate()) ? $input->getExpiryDate()->format('Y-m-d'):'',
                                'u_proveed' => ($input->getProduct()->getProvider())?:'',
                                'u_date' => $openDate->format('Y-m-d'),
                                'u_qr_data' => $input->getQrCode()
                            ];
                            break;
                        case 'retirado':
                            $data[$key] = ['u_qr_data' => 'Utilización no registrada'];
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
        }else {
            /** @var ProductsDissolutionRepository $dissolutionRepository */
            $dissolutionRepository = $this->getDoctrine()->getRepository(ProductsDissolution::class);
            /** @var ProductsDissolution $dissolution */
            $dissolution = $dissolutionRepository->findOneBy(['qrCode' => $code]);
            if(!$dissolution){
                $data[0] = ['u_qr_data' => 'Reactivo o disolución no encontrado'];
            }elseif($dissolution && $dissolution->getExpiryDate() < (new DateTime())){
                $data[0] = ['u_qr_data' => 'Caducidad alcanzada.'];
            }else{
                $data[0] = [
                    'u_tipo_muest' => $dissolution->getName(),
                    'u_cas' => 'N/A',
                    'u_lote' => 'N/A',
                    'u_caduc' => ($dissolution->getExpiryDate()) ? $dissolution->getExpiryDate()->format('Y-m-d'):'',
                    'u_proveed' => 'N/A',
                    'u_date' => $dissolution->getCreated()->format('Y-m-d'),
                    'u_qr_data' => $dissolution->getQrCode()
                ];
            }
        }

        return new Response(json_encode($data, JSON_FORCE_OBJECT));
    }

    private function exportExcelProducts($items)
    {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties();
        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Tipo')
            ->setCellValue('B1', 'Nombre')
            ->setCellValue('C1', 'Qr')
            ->setCellValue('D1', 'Part. Number')
            ->setCellValue('E1', 'CAS Number')
            ->setCellValue('F1', 'Código interno')
            ->setCellValue('G1', 'Proveedor')
            ->setCellValue('H1', 'Presentación')
            ->setCellValue('I1', 'Fecha de destrucción')
            ->setCellValue('J1', 'Estado')
            ->setCellValue('K1', 'Stock actual')
            ->setCellValue('L1', 'Stock Mínimo')
            ->setCellValue('M1', 'Observaciones')
            ->setCellValue('N1', 'Activo');

        $i = 2;
        foreach ($items as $product) {
            $phpExcelObject->getActiveSheet()
                ->setCellValue('A' . $i, $product['productType'])
                ->setCellValue('B' . $i, $product['productName'])
                ->setCellValue('C' . $i, $product['qrCode'])
                ->setCellValue('D' . $i, $product['partNumber'])
                ->setCellValue('E' . $i, $product['casNumber'])
                ->setCellValue('F' . $i, $product['internalCode'])
                ->setCellValue('G' . $i, $product['provider'])
                ->setCellValue('H' . $i, $product['presentation'])
                ->setCellValue('I' . $i, $product['destructionDate'])
                ->setCellValue('J' . $i, $product['state'])
                ->setCellValue('K' . $i, $product['stock'])
                ->setCellValue('L' . $i, $product['minStock'])
                ->setCellValue('M' . $i, $product['observations'])
                ->setCellValue('N' . $i, ($product['active'] === true) ? 'Activo' : 'Desactivado');
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

    private function exportPDFProducts($items)
    {
        $html = '<html>
                    <body style="font-size:8px;width:100%">
                        <table autosize="1" style="overflow:wrap;width:100%">
                            <tr style="font-size:8px;width:100%">
                                <th style="font-size:8px;width:5%">Tipo</th>
                                <th style="font-size:8px;width:10%">Nombre</th>
                                <th style="font-size:8px;width:7%">QR</th>
                                <th style="font-size:8px;width:9%">Part. Number</th>
                                <th style="font-size:8px;width:9%">CAS Number</th>
                                <th style="font-size:8px;width:9%">Código interno</th>
                                <th style="font-size:8px;width:9%">Proveedor</th>
                                <th style="font-size:8px;width:10%">Presentación</th>
                                <th style="font-size:8px;width:8%">Fecha de destrucción</th>
                                <th style="font-size:8px;width:5%">Estado</th>
                                <th style="font-size:8px;width:3%">Stock actual</th>
                                <th style="font-size:8px;width:3%">Stock Mínimo</th>
                                <th style="font-size:8px;width:10%">Observaciones</th>
                                <th style="font-size:8px;width:3%">Activo</th>
                            </tr>';

        foreach($items as $item) {
            $destructionFormatted = ($item['destructionDate']) ? (new DateTime($item['destructionDate']))->format('Y-m-d') : '';
            $productType = $item['productType'];
            $productName = $item['productName'];
            $qrCode = $item['qrCode'];
            $partNumber = $item['partNumber'];
            $casNumber = $item['casNumber'];
            $internalCode = $item['internalCode'];
            $provider = $item['provider'];
            $presentation = $item['presentation'];
            $destructionDate = $destructionFormatted;
            $state = $item['state'];
            $stock = $item['stock'];
            $minStock = $item['minStock'];
            $observations = $item['observations'];
            $active = ($item['active'] === true) ? 'Si' : 'No';
            $html .= "
                            <tr style='font-size:8px'>
                                <td>$productType</td>
                                <td>$productName</td>
                                <td>$qrCode</td>
                                <td>$partNumber</td>
                                <td>$casNumber</td>
                                <td>$internalCode</td>
                                <td>$provider</td>
                                <td>$presentation</td>
                                <td>$destructionDate</td>
                                <td>$state</td>
                                <td>$stock</td>
                                <td>$minStock</td>
                                <td>$observations</td>
                                <td>$active</td>
                            </tr>";
        }

        $html .= '
                        </table>
                    </body>
                </html>';

        return $this->returnPDFResponseFromHTML($html);
    }

    private function returnPDFResponseFromHTML($html){
        //set_time_limit(30); uncomment this line according to your needs
        // If you are not in a controller, retrieve of some way the service container and then retrieve it
        //$pdf = $this->container->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //if you are in a controlller use :
        $pdf = $this->get("white_october.tcpdf")->create('horizontal', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAuthor('GSK');
        $pdf->SetTitle(('Registros GSK'));
        $pdf->SetSubject('Registros GSK');
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 9, '', true);
        //$pdf->SetMargins(20,20,40, true);
        $pdf->AddPage('L', 'A4');


        $filename = 'list_records';

        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        return $pdf->Output($filename.".pdf",'I'); // This will output the PDF as a response directly
    }

    /** ProductInpus[] $items
     * @param ProductsInputs[] $items
     * @return
     */
    private function exportExcelProductsInputs($items, $type)
    {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties();
        if($type=== 'reactivo'){
            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Id')
                ->setCellValue('B1', 'CAS Number')
                ->setCellValue('C1', 'Part. Number')
                ->setCellValue('D1', 'Nombre')
                ->setCellValue('E1', 'Proveedor')
                ->setCellValue('F1', 'Presentación')
                ->setCellValue('G1', 'Cantidad')
                ->setCellValue('H1', 'Fecha de recepción')
                ->setCellValue('I1', 'Comentarios')
                ->setCellValue('J1', 'Usuario');
        }else{
            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Id')
                ->setCellValue('B1', 'Part. Number')
                ->setCellValue('C1', 'Nombre')
                ->setCellValue('D1', 'Proveedor')
                ->setCellValue('E1', 'Presentación')
                ->setCellValue('F1', 'Cantidad')
                ->setCellValue('G1', 'Fecha de recepción')
                ->setCellValue('H1', 'Comentarios')
                ->setCellValue('I1', 'Usuario');
        }

        $i = 2;
        foreach ($items as $item) {
            if($type === 'reactivo'){
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
            }else{
                $phpExcelObject->getActiveSheet()
                    ->setCellValue('A' . $i, $item->getId())
                    ->setCellValue('B' . $i, $item->getProduct()->getPartNumber())
                    ->setCellValue('C' . $i, $item->getProduct()->getName())
                    ->setCellValue('D' . $i, $item->getProduct()->getProvider())
                    ->setCellValue('E' . $i, $item->getProduct()->getPresentation())
                    ->setCellValue('F' . $i, $item->getAmount())
                    ->setCellValue('G' . $i, $item->getReceptionDate()->format('Y-m-d H:i:s'))
                    ->setCellValue('H' . $i, $item->getObservations())
                    ->setCellValue('I' . $i, $item->getUser()->getName());
            }
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

    /** ProductInpus[] $items
     * @param ProductsInputs[] $items
     * @param string $type
     * @return
     */
    private function exportPDFProductsInputs($items, $type)
    {
        $html = '<html>
                    <body style="font-size:8px;width:100%">
                        <table autosize="1" style="overflow:wrap;width:100%">
                            <tr style="font-size:8px;width:100%">
                                <th style="font-size:8px;width:3%">Id</th>';
        if($type === 'reactivo'){
            $html.= '<th style="font-size:8px;width:11%">CAS Number</th>';
        }
            $html .='<th style="font-size:8px;width:11%">Part. Number</th>
            <th style="font-size:8px;width:14%">Nombre</th>
            <th style="font-size:8px;width:14%">Proveedor</th>
            <th style="font-size:8px;width:14%">Presentación</th>
            <th style="font-size:8px;width:3%">Cantidad</th>
            <th style="font-size:8px;width:12%">Fecha de recepción</th>
            <th style="font-size:8px;width:12%">Comentarios</th>
            <th style="font-size:8px;width:6%">Usuario</th>
        </tr>';

        foreach($items as $item) {
            $id = $item->getId();
            $casNumber = $item->getProduct()->getCasNumber();
            $partNumber = $item->getProduct()->getPartNumber();
            $name = $item->getProduct()->getName();
            $provider = $item->getProduct()->getProvider();
            $presentation = $item->getProduct()->getPresentation();
            $amount = $item->getAmount();
            $receptionDate = $item->getReceptionDate()->format('Y-m-d H:i:s');
            $observations = $item->getObservations();
            $user = $item->getUser()->getName();
            $html .= "
                            <tr style='font-size:8px'>
                                <td> $id </td>";
            if($type === 'reactivo'){
                $html.= "<td> $casNumber </td>";
            }

                $html.="<td> $partNumber </td>
                <td> $name </td>
                <td> $provider </td>
                <td> $presentation </td>
                <td> $amount </td>
                <td> $receptionDate </td>
                <td> $observations </td>
                <td> $user </td>
            </tr>";
        }

        $html .= '
                        </table>
                    </body>
                </html>';

        return $this->returnPDFResponseFromHTML($html);
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
            ->setCellValue('A1', 'Id')
            ->setCellValue('B1', 'Entrada Id')
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
     * @param string $destructionDate
     * @param string $expiryDate
     * @return string|null
     */
    private function getEndDate($destructionDate, $expiryDate)
    {
        if(strtotime($destructionDate) < strtotime($expiryDate)){
            $endDate = $destructionDate;
        }else{
            $endDate = $expiryDate;
        }
        return $endDate;
    }

    /**
     * @return string
     */
    private function generateMaterialQrCode()
    {
        $prefix = 'MAT';
        /** @var ProductsRepository $prodcutsRepository */
        $prodcutsRepository = $this->getDoctrine()->getRepository(Products::class);
        /** @var Products $lastProduct */
        $lastProduct = $prodcutsRepository->findBy([],['id'=>'DESC'],1,0);

        $productNumber = str_pad(((int)$lastProduct[0]->getId()+1), 8, '0', STR_PAD_LEFT);

        return $prefix.$productNumber;
    }

    /**
     * @return string
     */
    private function generateReactivoQrCode()
    {
        $prefix = 'REACT';
        /** @var ProductsInputsRepository $prodcutsRepository */
        $inputsRepository = $this->getDoctrine()->getRepository(ProductsInputs::class);
        /** @var ProductsInputs $lastInput */
        $lastInput = $inputsRepository->findBy([],['id'=>'DESC'],1,0);

        $inputNumber = str_pad(((int)$lastInput[0]->getId()+1), 8, '0', STR_PAD_LEFT);

        return $prefix.$inputNumber;
    }
}
