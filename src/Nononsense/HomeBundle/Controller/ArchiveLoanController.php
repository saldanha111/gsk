<?php

namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Nononsense\HomeBundle\Entity\ArchiveUseStates;
use Nononsense\HomeBundle\Entity\ArchiveSignatures;
use Nononsense\HomeBundle\Entity\ArchiveActions;
use Nononsense\HomeBundle\Entity\ArchiveLocations;
use Nononsense\HomeBundle\Entity\ArchiveAZ;
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

class ArchiveLoanController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('archive_agent');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }
        $user = $this->container->get('security.context')->getToken()->getUser();

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

        $filters["areas"]=$this->get('app.security')->getAreas('archive_agent');
        $filters2["areas"]=$this->get('app.security')->getAreas('archive_agent');
        $filters["actions"]=array(8,9,10,11);
        $filters2["actions"]=array(8,9,10,11);
        $filters["not_available"]=1;
        $filters2["not_available"]=1;
        

        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(ArchiveSignatures::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(ArchiveSignatures::class)->count($filters2);
        $array_item["actions"] = $this->getDoctrine()->getRepository(ArchiveActions::class)->findAll();


        $url=$this->container->get('router')->generate('nononsense_archive_loan');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);


        return $this->render('NononsenseHomeBundle:Archive:loan.html.twig',$array_item);
    }

    public function updateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $is_valid = $this->get('app.security')->permissionSeccion('archive_agent');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        if(!$request->get("password")){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'La firma es incorrecta'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        if(!$request->get("action") || !$request->get("id") || !$request->get("comment")){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No se pudo gestionar el préstamo'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $signature=$this->getDoctrine()->getRepository(ArchiveSignatures::class)->findOneBy(array("id" => $request->get('id')));
        $record=$signature->getRecord();
        switch($request->get("action")){
            case "9":
                    if($record->getUseState()->getId()!=1){
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            'El registro se encuentra actualmente prestado y no se puede prestar'
                        );
                        $route = $this->container->get('router')->generate('nononsense_home_homepage');
                        return $this->redirect($route);
                    }
                    $newState=$this->getDoctrine()->getRepository(ArchiveUseStates::class)->findOneBy(array("id" =>3));
                    $record->setUseState($newState);
                    $em->persist($record);
                    $signature->setNotAvailable(TRUE);
                    $em->persist($record);
                    $patern=$signature->getId();
                break;
            case "10":
                    $signature->setNotAvailable(TRUE);
                    $em->persist($record);
                    $patern=$signature->getId();
                break;
            case "11":
                    if($record->getUseState()->getId()!=3){
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            'Este registro no se encuentra actualmente prestado y por tanto no se puede gestionar la devolución'
                        );
                        $route = $this->container->get('router')->generate('nononsense_home_homepage');
                        return $this->redirect($route);
                    }
                    $newState=$this->getDoctrine()->getRepository(ArchiveUseStates::class)->findOneBy(array("id" =>1));
                    $record->setUseState($newState);

                    if($request->get('location')){
                        $az = $em->getRepository(ArchiveAZ::class)->findOneBy(['id' => $request->get('location')]);
                        $record->setAZ($az);
                    }

                    $em->persist($record);
                    $signature->setNotAvailable(TRUE);
                    $em->persist($record);
                    $patern=$signature->getPatern()->getId();
                break;
        }

        $this->get('utilities')->saveLogArchive($this->getUser(),$request->get("action"),$request->get('comment'),"record",$record->getId(),NULL,$patern);
        
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "El préstamo se ha gestionado correctamente");

        return $this->redirect($this->generateUrl('nononsense_archive_load_update'));
    }
}