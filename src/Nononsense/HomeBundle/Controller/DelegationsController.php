<?php

namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Nononsense\HomeBundle\Entity\Delegations;
use Nononsense\HomeBundle\Entity\DelegationsTypes;
use Nononsense\UserBundle\Entity\Users;
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

class DelegationsController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('delegations_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters=array();
        $filters2=array();

        $filters=array_filter($request->query->all());
        $filters2=array_filter($request->query->all());

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

        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(Delegations::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(Delegations::class)->count($filters2);


        $url=$this->container->get('router')->generate('nononsense_delegations_list');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        return $this->render('NononsenseHomeBundle:Delegations:index.html.twig',$array_item);
    }

    public function editAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('delegations_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $delegation = $em->getRepository('NononsenseHomeBundle:Delegations')->find($id);
        if(!$delegation){
            $delegation = new Delegations();
        }

        if($request->getMethod()=='POST'){
            try{
                $user = $this->getDoctrine()->getRepository(Users::class)->find($request->get("user"));
                $sustitute = $this->getDoctrine()->getRepository(Users::class)->find($request->get("sustitute"));
                $type = $this->getDoctrine()->getRepository(DelegationsTypes::class)->find($request->get("type"));

                $delegation->setUser($user);
                $delegation->setSustitute($sustitute);
                $delegation->setType($type);
                $delegation->setCreated(new \DateTime());


                $em->persist($delegation);
                $em->flush();
                $this->get('session')->getFlashBag()->add('message',"La delegación de firma se ha guardado correctamente");
                return $this->redirect($this->generateUrl('nononsense_delegations_list'));
            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar guardar la delegación de firma: ".$e->getMessage());
            }
        }

        $array_item['delegation'] = $delegation;
        $array_item["users"] = $em->getRepository(Users::class)->findBy(array(),array("name" => "ASC"));
        $array_item["types"] = $this->getDoctrine()->getRepository(DelegationsTypes::class)->findAll();
        $array_item['time'] = time();

        return $this->render('NononsenseHomeBundle:Delegations:detail.html.twig',$array_item);
    }

    public function deleteAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('delegations_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        try{
            $em = $this->getDoctrine()->getManager();
            $delegation = $em->getRepository('NononsenseHomeBundle:Delegations')->find($id);

            if($delegation){
                $delegation->setDeleted(new \DateTime());

                $em->persist($delegation);
                $em->flush();
            }

            $this->get('session')->getFlashBag()->add('message',"La delegación de firma se ha cancelado correctamente");
        }
        catch(\Exception $e){
            $this->get('session')->getFlashBag()->add('error',"Error al cancelar la delegación de firmma: ".$e->getMessage());
        }

        return $this->redirect($this->generateUrl('nononsense_delegations_list'));
    }
}