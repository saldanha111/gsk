<?php

namespace Nononsense\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\UserBundle\Entity\AccountRequests;
use Nononsense\UserBundle\Form\Type as Form;
use Nononsense\UtilsBundle\Classes\Utils;

/**
* 
*/
class AccountRequestController extends Controller
{

	public function indexAction(Request $request){

		if (!$this->isAllowed('usuarios_gestion')) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

		$filters['page']        = (!$request->get('page')) ? 1 : $request->get('page');
        $filters['status']    	= $request->get('status');
        $filters['username']    = $request->get('username');
        $filters['from']     	= $request->get('from');
        $filters['until']    	= $request->get('until');

		$accountRequests     	= $this->getDoctrine()->getRepository(AccountRequests::class)->listBy($filters, 15); 

		$params    = $request->query->all();
		unset($params["page"]);
        $parameters = (!empty($params)) ? true : false;

		$pagination = Utils::paginador(15, $request, false, $accountRequests["count"], "/", $parameters);

        return $this->render('NononsenseUserBundle:Users:accountRequests.html.twig', ['filters' => $filters, 'pagination' => $pagination,  'accountRequests' => $accountRequests['rows']]);
	}

	public function createAction(Request $request){

		$accountRequest = new AccountRequests();

        $form = $this->createForm(new Form\RequestAccountType(), $accountRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            if (!$this->checkMudId($data->getMudId())) {

	            foreach ($data->getGroups() as $key => $value) {
	                $groups[$key]['id'] = $value->getId();
	                $groups[$key]['name'] = $value->getName();
	            }

            	$em = $this->getDoctrine()->getManager(); 
	            $accountRequest->setGroups($groups);
	            $em->persist($accountRequest);
	            $em->flush();

	            $this->get('session')->getFlashBag()->add('success', 'Solicitud enviada con éxito.');
            }
        	
            return $this->redirect($this->generateUrl('nononsense_user_crate_requests'));
        }

        return $this->render('NononsenseUserBundle:Default:requestAccount.html.twig', array('form' => $form->createView()));
	}

	public function updateAction(Request $request, $id){

		if (!$this->isAllowed('usuarios_gestion')) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

		$accountRequest      = $this->getDoctrine()->getRepository(AccountRequests::class)->find($id);

		$form = $this->createForm(new Form\AccountRequestUpdateType(), $accountRequest);
		$form->handleRequest($request);

		$data = $form->getData();

		if ($form->isSubmitted() && $form->isValid()) {

			$accountRequest = ($data->getStatus()) ? $this->acceptAction($accountRequest) : $this->removeAction($accountRequest);

			return $this->redirect($this->generateUrl('nononsense_user_update_requests', ['id' => $id]));
        }

        return $this->render('NononsenseUserBundle:Users:accountRequestsUpdate.html.twig', ['account' => $accountRequest, 'form' => $form->createView()]);
	}

	public function addUserAction($accountRequest){

		    $user = new Users();
            $user->setUsername($accountRequest->getUsername());
            $user->setName($accountRequest->getUsername());
            $user->setDescription('<p>Insert <strong>here</strong> the user description.</p>'); //Required parameter. TO DO FIXE IT.
            //$user->setIsActive(1);

            //Start Block Password DEV ONLY. TO DO GET AZURE ACTIVE DIRECTORY TOKEN
	            $generator = new SecureRandom();
	            $user->setSalt(base64_encode($generator->nextBytes(10)));

	            $factory 	= $this->get('security.encoder_factory');
	            $encoder 	= $factory->getEncoder($user);
	            $password 	= $encoder->encodePassword('DAKda$da1lK', $user->getSalt());
	            $user->setPassword($password);
            //End Block Password

            $user->setMudId($accountRequest->getMudId()); 
            $user->setEmail($accountRequest->getUsername().'@manual.com'); // TO DO GET AZURE ACTIVE DIRECTORY EMAIL.

		    $validator 	= $this->get('validator');
		    $errors 	= $validator->validate($user);

		    if (count($errors) > 0) {

		    	foreach ($errors as $key => $error) {
		    		$this->get('session')->getFlashBag()->add('errors', $error->getMessage());
		    	}
		    	
		    	return false;
		    }

		    $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Usuario creado con exito.');

            $this->addUserGroupAction($accountRequest->getGroups(), $user);

            return true;
	}

	public function addUserGroupAction($groups, $user){

			$em 	= $this->getDoctrine()->getManager();

			$validator 	= $this->get('validator');

			foreach ($groups as $key => $group) {
				
				$groupid = $em->getRepository(Groups::class)->find($group['id']);

				$usergroup = new GroupUsers();
	            $usergroup->setUser($user);
	            $usergroup->setGroup($groupid);
	            $usergroup->setType('member');

	            $errors = $validator->validate($usergroup);

	            if(count($errors) > 0){

	            	$this->get('session')->getFlashBag()->add('errors', $error->getMessage());

	            	return false;
	            }

	            $em->persist($usergroup);
			}

			$em->flush();

			$this->get('session')->getFlashBag()->add('success', 'Usuario añadido a: '.implode(',', array_column($groups, 'name')));

			return true;
	}

	public function removeAction($accountRequest){

		$this->get('session')->getFlashBag()->add('success', 'Solicitud cancelada.');

		$accountRequest->setStatus(0);

		$em = $this->getDoctrine()->getManager();
		$em->persist($accountRequest);
		$em->flush();

		return $accountRequest;
	}

	public function acceptAction($accountRequest){

		if ($this->addUserAction($accountRequest)){

			$this->get('session')->getFlashBag()->add('success', 'Solicitud aceptada.');

			$accountRequest->setStatus(1);

			$em = $this->getDoctrine()->getManager();
			$em->persist($accountRequest);
			$em->flush();
		}

		return $accountRequest;
	}

	public function checkMudId($mudId){

		$em 	= $this->getDoctrine()->getManager(); 
		$user 	= $em->getRepository(Users::class)->findOneBy(['mudId' => $mudId]);

		if (!empty($user)) {

			$this->get('session')->getFlashBag()->add('errors', 'Ya existe un usuario asociado a ese MUD_ID.');

			return true;
		}

		$apply 	= $em->getRepository(AccountRequests::class)->findOneBy(['mudId' => $mudId, 'status' => NULL]);

		if (!empty($apply)) {

			$this->get('session')->getFlashBag()->add('errors', 'Ya existe una solicitud asociada a ese MUD_ID.');

			return true;
		}

		return false;
	}

	public function isAllowed($section){

		if (!$this->get('app.security')->permissionSeccion($section)){

			$this->get('session')->getFlashBag()->add('error', 'No tiene permisos suficientes para acceder a esta sección.');

			return false;
		}

		return true;
	}

}