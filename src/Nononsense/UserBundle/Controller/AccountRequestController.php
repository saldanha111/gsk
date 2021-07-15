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
use Nononsense\UserBundle\Entity\AccountRequestsGroups;
use Nononsense\UserBundle\Entity\Roles;
use Nononsense\UserBundle\Form\Type as Form;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\JsonResponse;


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
        $filters['mudid']    	= $request->get('mudid');
        $limit 					= 15;

		$accountRequests     	= $this->getDoctrine()->getRepository(AccountRequests::class)->listBy($filters, $limit);

		foreach ($accountRequests['rows'] as $key => $rows) {
			$accountRequests['rows'][$key]->revised = 0; //Set revised property for group requests
			foreach ($rows->getRequest() as $k => $grequest) {
				if ($grequest->getStatus() !== NULL) {
					$accountRequests['rows'][$key]->revised += 1;
				}
			}
		}

		//$accountRequests     	= $this->getDoctrine()->getRepository(AccountRequestsGroups::class)->listBy($filters, $limit);

		$params    = $request->query->all();
		unset($params["page"]);
        $parameters = (!empty($params)) ? true : false;

		$pagination = Utils::paginador($limit, $request, false, $accountRequests["count"], "/", $parameters);

        return $this->render('NononsenseUserBundle:Users:accountRequests.html.twig', ['filters' => $filters, 'pagination' => $pagination,  'accountRequests' => $accountRequests['rows']]);
	}

	public function createAction(Request $request){

		$accountRequest = new AccountRequests();

        $form = $this->createForm(new Form\RequestAccountType(), $accountRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        		$em = $this->getDoctrine()->getManager();

            	$groups = $form->get('request')->getData(); //Get groups NOT MAPPED in AccountRequests entity.
            	$password = $form->get('_password')->getData();

            	$bulkMudIds = $form->get('bulk')->getData();

            	if ($bulkMudIds) {

            		$bulkMudIds = explode(',', preg_replace('(\s+)', '', $bulkMudIds));

            		foreach ($bulkMudIds as $key => $bulkMudId) {
	            		$bulkRequest = new AccountRequests();
	            		$bulkRequest->setMudId($bulkMudId);
	            		$bulkRequest->setEmail($form->get('email')->getData());
	            		$bulkRequest->setUsername($form->get('username')->getData());
	            		$bulkRequest->setDescription($form->get('description')->getData());

	            		foreach ($groups as $key => $group) {
			            	$bulkGroupRequest = new AccountRequestsGroups();
			            	$bulkGroupRequest->setRequestId($bulkRequest);
			            	$bulkGroupRequest->setGroupId($group);

			            	$bulkRequest->addRequest($bulkGroupRequest);
			            }
			            $em->persist($bulkRequest);
	            	}
            	}

	            foreach ($groups as $key => $group) {
	            	$groupRequest = new AccountRequestsGroups();
	            	$groupRequest->setRequestId($accountRequest);
	            	$groupRequest->setGroupId($group);

	            	$accountRequest->addRequest($groupRequest);
	            }

	            try {
		           	$this->signForm($accountRequest->getMudId(), $password); //Sign form with AD sAMAccountName and password.

		            $em->persist($accountRequest);
		            $em->flush();

		            //Application submitted successfully
		            $this->get('session')->getFlashBag()->add('success', 'Solicitud enviada con éxito');
	            } catch (\Exception $e) {
	            	$this->get('session')->getFlashBag()->add('errors', "Error");
	            }
        	
            return $this->redirect($this->generateUrl('nononsense_user_crate_requests'));
        }

        return $this->render('NononsenseUserBundle:Default:requestAccount.html.twig', array('form' => $form->createView()));
	}

	// public function updateAction(Request $request, $id){

	// 	if (!$this->isAllowed('usuarios_gestion')) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

	// 	$accountRequest      = $this->getDoctrine()->getRepository(AccountRequests::class)->find($id);

	// 	// foreach ($accountRequest->getRequest() as $key => $value) {
	// 	// 	var_dump($value->getGroupId()->getName());
	// 	// }

	// 	$form = $this->createForm(new Form\AccountRequestUpdateType(), $accountRequest);
	// 	$form->handleRequest($request);

	// 	$data = $form->getData();

	// 	if ($form->isSubmitted() && $form->isValid()) {

	// 		try {
	// 			if ($data->getStatus() == true) {
	// 				$this->addUserAction($accountRequest);
	// 			}
	// 			$em = $this->getDoctrine()->getManager();
	// 			$em->persist($accountRequest);
	// 			$em->flush();
	// 		} catch (\Exception $e) {
	// 			$this->get('session')->getFlashBag()->add('errors', $e->getMessage());
	// 		}

	// 		// $accountRequest = ($data->getStatus()) ? $this->acceptAction($accountRequest) : $this->removeAction($accountRequest);

	// 		return $this->redirect($this->generateUrl('nononsense_user_update_requests', ['id' => $id]));
 //        }

 //        return $this->render('NononsenseUserBundle:Users:accountRequestsUpdate.html.twig', ['account' => $accountRequest, 'form' => $form->createView()]);
	// }

	// public function getStatus($status){
	// 	$stauts[0] = 'Rechazada';
	// 	$status[1] = 'Aceptada';
	// 	$status[2] = 'Revision';

	// 	return $status[$status];
	// }

	public function ajaxUpdateAction(Request $request){

			//$id = 'p_1014';
			$id = trim($request->get('id'), 'p_');

			try {
				$accountRequest = $this->getDoctrine()->getRepository(AccountRequestsGroups::class)->find($id);
				$accountRequest->setStatus($request->get('status'));
				$accountRequest->setObservation(strip_tags($request->get('observation')));
				$message = ['type' => 'warning', 'message' => 'Solicitud cancelada con éxito'];

				if ($request->get('status') == 1) {
					$user = $this->checkMudId($accountRequest->getRequestId()->getMudId()); //Get user if exists
					if (!$user) {
						$user = $this->addUserAction($accountRequest->getRequestId()); //Create new user if not exists
						$header = ['apiKey:'.$this->getParameter('api3.key')];
            			Utils::api3($this->container->getParameter('api3.url').'/record-counter', $header ,'POST', ['type' => 'user']);
					}
					$this->addUserGroupAction($accountRequest->getGroupId(), $user);
					$message = ['type' => 'success', 'message' => 'Solicitud aceptada con éxito'];
				}

				$em = $this->getDoctrine()->getManager();
				$em->persist($accountRequest);
				$em->flush();

				
			} catch (\Exception $e) {
				$message = ['type' => 'error', 'message' => "error"];
			}

			$response = new JsonResponse($message);

			return $response;
	}

	public function addUserAction($accountRequest){

		    $user = new Users();
            $user->setUsername($accountRequest->getMudId());
            $user->setName($accountRequest->getUsername());
            $user->setDescription(''); //Required parameter. TO DO FIXE IT.
            $user->setIsActive(1);

            //Start Block Password DEV ONLY. TO DO GET AZURE ACTIVE DIRECTORY TOKEN
	            $generator = new SecureRandom();
	            $user->setSalt(base64_encode($generator->nextBytes(10)));

	            $factory 	= $this->get('security.encoder_factory');
	            $encoder 	= $factory->getEncoder($user);
	            $password 	= $encoder->encodePassword(1, $user->getSalt());
	            $user->setPassword($password);
            //End Block Password

            //$user->setMudId($accountRequest->getMudId()); 
            //$user->setEmail($accountRequest->getEmail()); // TO DO GET AZURE ACTIVE DIRECTORY EMAIL.

		    $validator 	= $this->get('validator');
		    $errors 	= $validator->validate($user);

		    if (count($errors) > 0) {

		    	foreach ($errors as $key => $error) {
		    		throw new \Exception($error->getMessage());
		    		//$this->get('session')->getFlashBag()->add('errors', $error->getMessage());
		    	}
		    }

		    $em = $this->getDoctrine()->getManager();

		    $role = $em->getRepository(Roles::class)->findOneBy(array('name' => 'ROLE_USER'));
            $user->addRole($role);

            $em->persist($user);
            $em->flush();

            //$this->get('session')->getFlashBag()->add('success', 'Usuario creado con exito.');
            //$message = ['type' => 'error', 'message' => 'Usuario creado con exito.'];
            //$this->addUserGroupAction($accountRequest->getRequestId()->getGroups(), $user);

            return $user;
	}

	public function addUserGroupAction($group, $user){

			$em 	= $this->getDoctrine()->getManager();

			$validator 	= $this->get('validator');

			$groupid = $em->getRepository(Groups::class)->find($group);

			$usergroup = new GroupUsers();
            $usergroup->setUser($user);
            $usergroup->setGroup($groupid);
            $usergroup->setType('member');

			$errors = $validator->validate($usergroup);

		    if (count($errors) > 0) {

		    	foreach ($errors as $key => $error) {
		    		throw new \Exception($error->getMessage());
		    		//$this->get('session')->getFlashBag()->add('errors', $error->getMessage());
		    	}
		    }

            $em->persist($usergroup);
            $em->flush();

            return $usergroup;
			// foreach ($groups as $key => $group) {
				

			// 	$usergroup = new GroupUsers();
	  //           $usergroup->setUser($user);
	  //           $usergroup->setGroup($groupid);
	  //           $usergroup->setType('member');

	  //           $errors = $validator->validate($usergroup);

	  //           if(count($errors) > 0){

	  //           	throw new \Exception($error->getMessage());
	  //           	// $this->get('session')->getFlashBag()->add('errors', $error->getMessage());

	  //           	// return false;
	  //           }

	  //           $em->persist($usergroup);
			// }

			// $em->flush();

			// //$this->get('session')->getFlashBag()->add('success', 'Usuario añadido a: '.implode(',', array_column($groups, 'name')));

			// return true;
	}

	// public function removeAction($accountRequest){

	// 	$this->get('session')->getFlashBag()->add('success', 'Solicitud cancelada.');

	// 	$accountRequest->setStatus(0);

	// 	$em = $this->getDoctrine()->getManager();
	// 	$em->persist($accountRequest);
	// 	$em->flush();

	// 	return $accountRequest;
	// }

	// public function acceptAction($accountRequest){

	// 	if ($this->addUserAction($accountRequest)){

	// 		$this->get('session')->getFlashBag()->add('success', 'Solicitud aceptada.');

	// 		$accountRequest->setStatus(1);

	// 		$em = $this->getDoctrine()->getManager();
	// 		$em->persist($accountRequest);
	// 		$em->flush();
	// 	}

	// 	return $accountRequest;
	// }

	public function checkMudId($mudId){

		$em 	= $this->getDoctrine()->getManager(); 
		$user 	= $em->getRepository(Users::class)->findOneBy(['username' => $mudId]);

		return $user;
	}

	public function isAllowed($section){

		if (!$this->get('app.security')->permissionSeccion($section)){

			$this->get('session')->getFlashBag()->add('error', 'No tiene permisos suficientes para acceder a esta sección.');

			return false;
		}

		return true;
	}

	private function signForm($cn, $pass, $hideLdapErrors = true){

		try {
			$ldapdn = 'cn={username},cn=users,dc=demo,dc=local';
			$ldapdn = str_replace('{username}', $cn, $ldapdn);

			$ldap   = $this->container->get('ldap');
			$bind   = $ldap->bind($ldapdn, $pass);
		} catch (\Exception $e) {
			if ($hideLdapErrors) {
				//The presented password is invalid.
				throw new \Exception('La firma no es válida.', 0, $e);
			}

			throw $e;
		}
	}

	public function getMudIdAction(Request $request){

		$em 	= $this->getDoctrine()->getManager();
		
		//error_reporting(0);

		$mudid = preg_replace('/[^a-z0-9]/i', '', $request->get('mudid'));

		// if (isset($mudid) && $mudid) {
		// 	# code...
		// }
		$ldapdn   = $this->container->getParameter('ldap.dn_string'); //'cn=admin,cn=users,dc=demo,dc=local'; $this->container->getParameter('ldap.search_dn');
		$ldappass = $this->container->getParameter('ldap.search_password');; //$this->container->getParameter('ldap.search_password');

		$uid_key = 'sAMAccountName'; //$this->container->getParameter('ldap.uid_key');
		$queryDn = 'dc=demo,dc=local'; //$this->container->getParameter('ldap.base_dn');

		$filter  = '({uid_key}='.$mudid.')';

		$userSearch  = str_replace('{uid_key}', $uid_key, $filter);

		try {
			if (!$mudid) {
				throw new \Exception("MUD ID introducino no encontrado");
			}
			
			$ldap   = $this->container->get('ldap');
			$bind   = $ldap->bind($ldapdn, $ldappass);
	        $query  = $ldap->find($queryDn, $userSearch, ['mail','displayname']);

	        if (!$query) {
	        	throw new \Exception("MUD ID introducino no encontrado");
	        }

	        $message = ['type' => 'success', 'message' => $query];
		} catch (\Exception $e) {
			$message = ['type' => 'error', 'message' => "error"];
		}

		$response = new JsonResponse($message);

		return $response;
	}

	public function removeAction(Request $request)
	{
		if (!$this->getUser()) return $this->redirect($this->generateUrl('nononsense_user_login'));

		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository(Users::class)->findOneBy(['id' => $this->getUser()]);

		$form = $this->createForm(new Form\RemoveRequestAccountType(), $user);
        $form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			try {

				$factory = $this->container->get('security.encoder_factory');
            	$encoder = $factory->getEncoder($user);

				if (!$encoder->isPasswordValid($user->getPassword(), $form->get('_password')->getData(), $user->getSalt())) {
					throw new \Exception("La firma no es válida.", 0);
				}
				
				$groups = $form->get('groups')->getData(); 

				if ($groups) {

					foreach ($groups as $key => $group) {
						$groupUser = $em->getRepository(GroupUsers::class)->findOneBy(['id' => $group]);
						$em->remove($groupUser);
					}

				}

				$this->get('session')->getFlashBag()->add('success', 'Solicitud enviada con éxito');
				$em->flush();
			} catch (\Exception $e) {
				$this->get('session')->getFlashBag()->add('errors', "error");
			}

		}

		return $this->render('NononsenseUserBundle:Default:requestAccountRemove.html.twig', array('form' => $form->createView()));
		
	}

}