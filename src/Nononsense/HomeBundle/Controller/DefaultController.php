<?php

namespace Nononsense\HomeBundle\Controller;
use Nononsense\UtilsBundle\Classes;

use Nononsense\HomeBundle\Entity\Areas;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        $data['sectionsP'] = array();
        $productions=$em->getRepository('NononsenseHomeBundle:SAInProduction')->findBy(array("production" => true));
        foreach ($productions as $production) {
            array_push($data['sectionsP'],$production->getSearch());
        }

        return $this->render('NononsenseHomeBundle:Contratos:home.html.twig', $data);
    }
    
    public function navSideAction()
    {
    	$em = $this->getDoctrine()->getManager();

		$array_subsecciones = array();
        $array_groups = array();         
        foreach ($this->getUser()->getGroups() as $group) {
            array_push($array_groups, $group->getGroup()->getId());
        }

        $subseccionsGroup = $em->getRepository('NononsenseUserBundle:GroupsSubsecciones')->findBy(array('group'=>$array_groups));
        foreach ($subseccionsGroup as $subseccionGroup) {
            array_push($array_subsecciones, $subseccionGroup->getSubseccion()->getNameId());
        }
        $data = array();
        $data['array_subsecciones'] = $array_subsecciones;
        $data['superadmin']=$this->getUser()->getSuperAdmin();
        $data['sectionsP'] = array();
        $productions=$em->getRepository('NononsenseHomeBundle:SAInProduction')->findBy(array("production" => true));
        foreach ($productions as $production) {
            array_push($data['sectionsP'],$production->getSearch());
        }

        return $this->render('::nav_side.html.twig', $data);
    }

    public function popupAreasAction()
    {
        $em = $this->getDoctrine()->getManager();

        $data["areas"] = $this->getDoctrine()->getRepository(Areas::class)->findBy(array(),array("name" => "ASC"));

        return $this->render('::popupAreas.html.twig', $data);
    }

    public function popupAZAction()
    {
        return $this->render('NononsenseHomeBundle:Archive:popupAZ.html.twig');
    }


    public function topSideAction(){

        $em     = $this->getDoctrine()->getManager();

        $filters['user']    =  $this->container->get('security.context')->getToken()->getUser();
        $filters['unread']  = true;

        $messages = $em->getRepository('NononsenseNotificationsBundle:Notifications')->countBy($filters);

        return $this->render('::nav_top.html.twig', ['messages' => $messages]);
    }
}
