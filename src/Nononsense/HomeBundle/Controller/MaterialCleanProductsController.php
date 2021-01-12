<?php

namespace Nononsense\HomeBundle\Controller;

use Exception;
use Nononsense\HomeBundle\Entity\MaterialCleanProducts;
use Nononsense\HomeBundle\Entity\MaterialCleanProductsRepository;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MaterialCleanProductsController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_products_list');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_products_edit');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();

        /** @var MaterialCleanProductsRepository $productRepository */
        $productRepository = $em->getRepository('NononsenseHomeBundle:MaterialCleanProducts');
        $product = $productRepository->find($id);

        if (!$product) {
            $product = new MaterialCleanProducts();
        }

        if ($request->getMethod() == 'POST') {
            try {
                if(!$product->getName()){
                    $product->setName($request->get("name"));
                }
                $product->setTagsNumber($request->get("tags_number"));
                $product->setActive($request->get("active"));

                $error = 0;
                $productName = $productRepository->findOneBy(['name' => $request->get("name")]);
                if ($productName && $productName->getId() != $product->getId()) {
                    $this->get('session')->getFlashBag()->add('error', "Ese producto ya está registrado.");
                    $error = 1;
                }

                if ($error == 0) {
                    $em->persist($product);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('message', "El producto se ha guardado correctamente");
                    return $this->redirect($this->generateUrl('nononsense_mclean_products_list'));
                }
            } catch (Exception $e) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error al intentar guardar los datos del producto: " . $e->getMessage()
                );
            }
        }

        $array_item = ['product' => $product];
        return $this->render('NononsenseHomeBundle:MaterialClean:product_edit.html.twig', $array_item);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
                "Error al intentar inactivar el producto: " . $e->getMessage()
            );
        }

        return $this->redirect($this->generateUrl('nononsense_mclean_products_list'));
    }
}
