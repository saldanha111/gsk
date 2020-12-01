<?php

namespace Nononsense\HomeBundle\Controller;
use Nononsense\UtilsBundle\Classes;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('NononsenseHomeBundle:Contratos:home.html.twig');
    }
    
    public function navSideAction()
    {
    	$em = $this->getDoctrine()->getManager();

		$array_subsecciones = array();        
        $Egroups =$em->getRepository('NononsenseGroupBundle:GroupUsers')->findBy(array("user"=>$this->getUser()));
        foreach ($Egroups as $groupUser) {

            $subseccionsGroup = $em->getRepository('NononsenseUserBundle:GroupsSubsecciones')->findBy(array('group'=>$groupUser->getGroup()));
            foreach ($subseccionsGroup as $subseccionGroup) {
            	array_push($array_subsecciones, $subseccionGroup->getSubseccion()->getNameId());
            }
        }

        $data = array();
        $data['array_subsecciones'] = $array_subsecciones;

        return $this->render('::nav_side.html.twig', $data);
    }

    public function topSideAction(){

        $em     = $this->getDoctrine()->getManager();

        $user   = $this->container->get('security.context')->getToken()->getUser();

        $messages = $em->getRepository('NononsenseNotificationsBundle:Notifications')->findBy(array('author' => $user));

        return $this->render('::nav_top.html.twig', ['messages' => $messages]);
    }
}
