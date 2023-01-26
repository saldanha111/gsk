<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\HomeBundle\Entity\MaterialCleanProducts;
use Nononsense\HomeBundle\Entity\MaterialCleanProductsLog;
use Nononsense\HomeBundle\Entity\MaterialCleanProductsRepository;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MaterialCleanProductsController extends Controller
{
    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_products_list');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $array_item["canCreate"] = $this->get('app.security')->permissionSeccion('mc_products_new');

        $filters = [];
        $filters2 = [];

        if ($request->get("page")) {
            $filters["limit_from"] = $request->get("page") - 1;
        } else {
            $filters["limit_from"] = 0;
        }
        $filters["limit_many"] = 15;

        if ($request->get("name")) {
            $filters["name"] = $request->get("name");
            $filters2["name"] = $request->get("name");
        }

        /** @var MaterialCleanProductsRepository $productRepository */
        $productRepository = $this->getDoctrine()->getRepository(MaterialCleanProducts::class);
        $array_item["filters"] = $filters;
        $array_item["items"] = $productRepository->list($filters);
        $array_item["count"] = $productRepository->count($filters2);

        $url = $this->container->get('router')->generate('nononsense_mclean_products_list');
        $params = $request->query->all();
        unset($params["page"]);
        if (!empty($params)) {
            $parameters = true;
        } else {
            $parameters = false;
        }
        $array_item["pagination"] = Utils::paginador(
            $filters["limit_many"],
            $request,
            $url,
            $array_item["count"],
            "/",
            $parameters
        );

        return $this->render('NononsenseHomeBundle:MaterialClean:product_index.html.twig', $array_item);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var MaterialCleanProductsRepository $productRepository */
        $productRepository = $em->getRepository('NononsenseHomeBundle:MaterialCleanProducts');
        $product = $productRepository->find($id);

        if (!$product) {
            $is_valid = $this->get('app.security')->permissionSeccion('mc_products_new');
            if (!$is_valid) {
                return $this->redirect($this->generateUrl('nononsense_home_homepage'));
            }
            $product = new MaterialCleanProducts();
        }else{
            $is_valid = $this->get('app.security')->permissionSeccion('mc_products_edit');
            if (!$is_valid) {
                return $this->redirect($this->generateUrl('nononsense_home_homepage'));
            }
        }

        if ($request->getMethod() == 'POST') {
            try {
                if($product->getId()){
                    $log = new MaterialCleanProductsLog();
                    $log->setUpdated($product->getUpdated())
                        ->setCreated(new DateTime())
                        ->setProduct($product)
                        ->setValidated($product->getValidated())
                        ->setUpdateUser($product->getUpdateUser())
                        ->setValidateUser($product->getValidateUser())
                        ->setActive($product->getActive())
                        ->setName($product->getName())
                        ->setTagsNumber($product->getTagsNumber())
                        ->setUpdateComment($product->getUpdateComment());
                    $product->setUpdateComment($request->get("update_comment"));
                }
                $password = $request->get('password');
                if(!$this->get('utilities')->checkUser($password)){
                    $this->get('session')->getFlashBag()->add('error', "La contraseña no es correcta.");
                    return $this->redirect($this->generateUrl('nononsense_mclean_products_list'));
                }

                if(!$product->getName()){
                    $product->setName($request->get("name"));
                }
                $product->setTagsNumber((int) $request->get("tags_number"));
                $product->setActive($request->get("active"));

                $error = 0;
                $productName = $productRepository->findOneBy(['name' => $request->get("name")]);
                if ($productName && $productName->getId() != $product->getId()) {
                    $this->get('session')->getFlashBag()->add('error', "Ese producto ya está registrado.");
                    $error = 1;
                }

                if ($error == 0) {
                    $product->setUpdateUser($this->getUser());
                    $product->setValidated(false);
                    $product->setValidateUser(null);
                    $product->setUpdated(new DateTime());
                    $em->persist($product);
                    if(isset($log) && $log){
                        $em->persist($log);
                    }
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('message', "El producto se ha guardado correctamente");
                    return $this->redirect($this->generateUrl('nononsense_mclean_products_list'));
                }
            } catch (Exception $e) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error al intentar guardar los datos del producto "
                );
            }
        }

        $array_item = ['product' => $product, 'currentUser' => $this->getUser()];
        return $this->render('NononsenseHomeBundle:MaterialClean:product_edit.html.twig', $array_item);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|Response
     */
    public function validateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var MaterialCleanProductsRepository $productRepository */
        $productRepository = $em->getRepository('NononsenseHomeBundle:MaterialCleanProducts');
        $product = $productRepository->find($id);

        $is_valid = $this->get('app.security')->permissionSeccion('mc_products_edit');
        if (!$is_valid) {
            $this->get('session')->getFlashBag()->add('error', "No tienes permisos para validar productos.");
            return $this->redirect($this->generateUrl('nononsense_mclean_products_list'));
        }

        try {
            $password = $request->get('valPassword');
            if(!$this->get('utilities')->checkUser($password)){
                $this->get('session')->getFlashBag()->add('error', "La contraseña no es correcta.");
                return $this->redirect($this->generateUrl('nononsense_mclean_products_list'));
            }

            $updatedProductUser = $product->getUpdateUser();
            if(!$updatedProductUser || $updatedProductUser === $this->getUser()){
                $this->get('session')->getFlashBag()->add('error', "El usuario que editó el producto no puede validarlo");
                return $this->redirect($this->generateUrl('nononsense_mclean_products_list'));
            }

            $product->setValidateUser($this->getUser());
            $product->setValidated(true);
            $product->setUpdated(new DateTime());
            $em->persist($product);
            $em->flush();
            $this->get('session')->getFlashBag()->add('message', "El producto se ha validado correctamente");
        } catch (Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Error al intentar validar producto "
            );
        }
        return $this->redirect($this->generateUrl('nononsense_mclean_products_list'));
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function deleteAction($id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_products_edit');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        try {
            $em = $this->getDoctrine()->getManager();

            /** @var MaterialCleanProductsRepository $productRepository */
            $productRepository = $em->getRepository('NononsenseHomeBundle:MaterialCleanProducts');
            $product = $productRepository->find($id);

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
                "Error al intentar inactivar el producto"
            );
        }

        return $this->redirect($this->generateUrl('nononsense_mclean_products_list'));
    }
}
