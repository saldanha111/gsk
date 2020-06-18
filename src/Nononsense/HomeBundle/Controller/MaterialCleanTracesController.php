<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Nononsense\HomeBundle\Entity\MaterialCleanCenters;
use Nononsense\HomeBundle\Entity\MaterialCleanCleans;
use Nononsense\HomeBundle\Entity\MaterialCleanCleansRepository;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MaterialCleanTracesController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_traces_list');
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

        if($request->get("material")){
            $filters["material"]=$request->get("material");
            $filters2["material"]=$request->get("material");
        }

        if($request->get("lot")){
            $filters["lot"]=$request->get("lot");
            $filters2["lot"]=$request->get("lot");
        }

        if($request->get("clean_date_start")){
            $filters["clean_date_start"]=$request->get("clean_date_start");
            $filters2["clean_date_start"]=$request->get("clean_date_start");
        }

        if($request->get("clean_date_end")){
            $filters["clean_date_end"]=$request->get("clean_date_end");
            $filters2["clean_date_end"]=$request->get("clean_date_end");
        }

        if($request->get("verification_date_start")){
            $filters["verification_date_start"]=$request->get("verification_date_start");
            $filters2["verification_date_start"]=$request->get("verification_date_start");
        }

        if($request->get("verification_date_end")){
            $filters["verification_date_end"]=$request->get("verification_date_end");
            $filters2["verification_date_end"]=$request->get("verification_date_end");
        }

        if($request->get("user")){
            $filters["user"]=$request->get("user");
            $filters2["user"]=$request->get("user");
        }

        if($request->get("state")){
            $filters["state"]=$request->get("state");
            $filters2["state"]=$request->get("state");
        }

        $array_item["filters"]=$filters;
        $array_item['status'] = MaterialCleanCleansRepository::status;
        $array_item["items"] = $this->getDoctrine()->getRepository(MaterialCleanCleans::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(MaterialCleanCenters::class)->count($filters2);
        if($array_item['count'] && isset($filters["lot"])){
            /** @var MaterialCleanCleans $firstTrace */
            $firstTrace = $array_item["items"][0];
            $status = $firstTrace->getStatus();
            if($status == 2 && $this->get('app.security')->permissionSeccion('mc_traces_dirty')){
                $array_item["formAction"] = $this->container->get('router')->generate('nononsense_mclean_traces_dirty', ['lot' => $filters["lot"]]);
                $array_item["buttonName"] = 'Marcar lote como Material Sucio';
            }elseif($status == 3 && $this->get('app.security')->permissionSeccion('mc_traces_review')){
                $array_item["formAction"] = $this->container->get('router')->generate('nononsense_mclean_traces_review', ['lot' => $filters["lot"]]);
                $array_item["buttonName"] = 'Revisar Lote';
            }
        }

        $url=$this->container->get('router')->generate('nononsense_mclean_traces_list');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=true;
        }
        else{
            $parameters=false;
        }
        $array_item["pagination"]= Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        return $this->render('NononsenseHomeBundle:MaterialClean:traces_index.html.twig',$array_item);
    }

    public function markDirtyAction(Request $request, $lot)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_traces_dirty');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $error = false;

        $em = $this->getDoctrine()->getManager();
        /** @var MaterialCleanCleansRepository $traces */
        $cleansRepository = $em->getRepository('NononsenseHomeBundle:MaterialCleanCleans');
        $traces = $cleansRepository->findBy(['lotNumber' => $lot, 'status' => 2]);

        if (!$traces) {
            $this->get('session')->getFlashBag()->add('error', "No se ha encontrado material usado con ese número de lote.");
            $error = true;
        }

        if(!$error){
            try{
                /** @var MaterialCleanCleans $trace */
                foreach($traces as $trace){
                    $trace->setStatus(3)
                        ->setDirtyMaterialUser($this->getUser())
                        ->setDirtyMaterialDate(new DateTime())
                        ->setDirtyMaterialSignature($request->get('firma'));

                    $em->persist($trace);
                    $em->flush();
                }
                    $this->get('session')->getFlashBag()->add('message',"El material se ha marcado correctamente");

            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar marcar el material como Material sucio: ".$e->getMessage());
            }
        }
        return $this->redirect($this->generateUrl('nononsense_mclean_traces_list'));
    }

    public function markReviewAction(Request $request, $lot)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_traces_dirty');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $error = false;

        $em = $this->getDoctrine()->getManager();
        /** @var MaterialCleanCleansRepository $traces */
        $cleansRepository = $em->getRepository('NononsenseHomeBundle:MaterialCleanCleans');
        $traces = $cleansRepository->findBy(['lotNumber' => $lot, 'status' => 3]);

        if (!$traces) {
            $this->get('session')->getFlashBag()->add('error', "No se ha encontrado material sucio con ese número de lote.");
            $error = true;
        }

        if(!$error){
            try{
                /** @var MaterialCleanCleans $trace */
                foreach($traces as $trace){
                    $trace->setStatus(4)
                        ->setReviewUser($this->getUser())
                        ->setReviewDate(new DateTime())
                        ->setReviewSignature($request->get('firma'));

                    $em->persist($trace);
                    $em->flush();
                }
                $this->get('session')->getFlashBag()->add('message',"El material se ha marcado correctamente");

            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar marcar el material como Revisado: ".$e->getMessage());
            }
        }
        return $this->redirect($this->generateUrl('nononsense_mclean_traces_list'));
    }
}
