<?php

namespace Nononsense\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UserBundle\Entity\Roles;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\UserBundle\Entity\AccountRequests;
use Nononsense\UserBundle\Entity\AccountRequestsGroups;
use Nononsense\HomeBundle\Entity\LogsTypes;
use Nononsense\HomeBundle\Entity\Logs;
use Nononsense\UserBundle\Form\Type as Form;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
* 
*/
class AccountRequestController extends Controller
{
	const TITLE_PDF = " Solicitudes de accesos";
    const FILENAME_PDF = "account_request";

	public function indexAction(Request $request){

		if (!$this->isAllowed('usuarios_gestion')) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

		$filters['page']        = (!$request->get('page')) ? 1 : $request->get('page');
        $filters['status']    	= $request->get('status');
        $filters['username']    = $request->get('username');
        $filters['from']     	= $request->get('from');
        $filters['until']    	= $request->get('until');
        $filters['mudid']    	= $request->get('mudid');
        $filters['requestType'] = $request->get('type');
        $limit 					= 15;
        $filters['uri']          = ($request->query->all()) ? $_SERVER['REQUEST_URI'].'&csv=true' : $_SERVER['REQUEST_URI'].'?csv=true';
        $filters['pdf']          = ($request->query->all()) ? $_SERVER['REQUEST_URI'].'&pdf=true' : $_SERVER['REQUEST_URI'].'?pdf=true';

        if ($request->get('csv')) return $this->reportCsvAction($this->getDoctrine()->getRepository(AccountRequests::class)->listBy($filters, 99999999999)['rows'], $filters);
        if ($request->get('pdf')) return $this->reportPDFAction($request, $this->getDoctrine()->getRepository(AccountRequests::class)->listBy($filters, 99999999999)['rows'], $filters);

		$accountRequests     	= $this->getDoctrine()->getRepository(AccountRequests::class)->listBy($filters, $limit);

		foreach ($accountRequests['rows'] as $key => $rows) {
			$accountRequests['rows'][$key]->revised = 0; //Set revised property for group requests
			foreach ($rows->getRequest() as $k => $grequest) {
				if ($grequest->getStatus() !== NULL) {
					$accountRequests['rows'][$key]->revised += 1;
				}
			}
		}

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

            		//Check the integrity of the mudids again
            		$bulkMudIds = $this->checkBulkMudIds($bulkMudIds);

            		if ($bulkMudIds['type'] == 'error'){
            			$this->get('session')->getFlashBag()->add('errors', $bulkMudIds['message']);

            			return $this->redirect($this->generateUrl('nononsense_user_crate_requests'));
            		}

            		foreach ($bulkMudIds['message'] as $key => $bulkMudId) {
	            		$bulkRequest = new AccountRequests();
	            		$bulkRequest->setMudId($bulkMudId['mudId']);
	            		$bulkRequest->setRefUsername($bulkMudId['displayname']);
	            		$bulkRequest->setEmail($form->get('email')->getData());
	            		$bulkRequest->setUsername($form->get('username')->getData());
	            		$bulkRequest->setDescription($form->get('description')->getData());
	            		$bulkRequest->setRequestType($form->get('requestType')->getData());
	            		$bulkRequest->setBaseMudId($form->get('mud_id')->getData());

	            		foreach ($groups as $key => $group) {
			            	$bulkGroupRequest = new AccountRequestsGroups();
			            	$bulkGroupRequest->setRequestId($bulkRequest);
			            	$bulkGroupRequest->setGroupId($group);

			            	$bulkRequest->addRequest($bulkGroupRequest);

			            	$logType = $em->getRepository(LogsTypes::class)->findOneBy(['stringId' => 'APPLY']);

			            	$log = new Logs();
					        $log->setType($logType);
					        $log->setDate(new \DateTime());
					        $log->setDescription($accountRequest->getMudId().' ha solicitado acceso al grupo '.$group->getName().' para el MUDID '.$bulkMudId['mudId']);

					        $user = $em->getRepository(Users::class)->findOneBy(['username' => $accountRequest->getMudId()]);

					        if ($user) {
					            $log->setUser($user);
					        }

					        $em->persist($log);
			            }

			            $em->persist($bulkRequest);
	            	}
            	}

	            try {
		           	$this->signForm($accountRequest->getMudId(), $password); //Sign form with AD sAMAccountName and password.

		            $em->flush();

		            //Application submitted successfully
		            $this->get('session')->getFlashBag()->add('success', 'Solicitud enviada con éxito');
	            } catch (\Exception $e) {
	            	$this->get('session')->getFlashBag()->add('errors', 'Error al firmar su solicitud, intentelo de nuevo');
	            }
        	
            return $this->redirect($this->generateUrl('nononsense_user_crate_requests'));
        }

        return $this->render('NononsenseUserBundle:Default:requestAccount.html.twig', array('form' => $form->createView()));
	}

	public function ajaxUpdateAction(Request $request){
			$id = trim($request->get('id'), 'p_');
			$em = $this->getDoctrine()->getManager();

			try {
				$accountRequest = $this->getDoctrine()->getRepository(AccountRequestsGroups::class)->findOneBy(['id' => $id]);

				if ($accountRequest->getRequestId()->getRequestType() == 1) {

					$message = ['type' => 'warning', 'message' => 'Solicitud de alta cancelada con éxito'];

					if ($request->get('status') == 1) {
						$user = $this->checkMudId($accountRequest->getRequestId()->getMudId()); //Get user if exists
						if (!$user) {
							$user = $this->addUserAction($accountRequest->getRequestId()); //Create new user if not exists
							$header = ['apiKey:'.$this->getParameter('api3.key')];
	            			Utils::api3($this->container->getParameter('api3.url').'/record-counter', $header ,'POST', ['type' => 'user']);
						}
						
						$groupUser = $em->getRepository(GroupUsers::class)->findOneBy(['user' => $user, 'group' => $accountRequest->getGroupId()]);
						if (!$groupUser) {
							$this->addUserGroupAction($accountRequest->getGroupId(), $user);
						}

						$message = ['type' => 'success', 'message' => 'Solicitud de alta aceptada con éxito'];
					}

				}else{
					
					$message = ['type' => 'warning', 'message' => 'Solicitud de baja cancelada con éxito'];

					if ($request->get('status') == 1) {

						$user = $this->checkMudId($accountRequest->getRequestId()->getMudId());
						if ($user) {
						
							$groupUser = $em->getRepository(GroupUsers::class)->findOneBy(['user' => $user, 'group' => $accountRequest->getGroupId()]);

							if ($groupUser) {
								$em->remove($groupUser);
							}

						}
						
						$message = ['type' => 'success', 'message' => 'Solicitud de baja aceptada con éxito'];
					}
				}

				$accountRequest->setStatus($request->get('status'));
				$accountRequest->setObservation(strip_tags($request->get('observation')));

				$extra = $accountRequest->getExtra();

				$extra[] = [
					'validatedBy' => $this->getUser()->getUsername(), 
					'status' => $request->get('status'),
					'created' => new \DateTime()
				];

				$accountRequest->setExtra($extra);

				$em->persist($accountRequest);
				$em->flush();

				$subject = ($accountRequest->getStatus()) ? 'Solicitud aceptada' : 'Solicitud rechazada';
				$accountRequestType = ($accountRequest->getRequestId()->getRequestType()) ? 'Alta' : 'Baja';
				$this->get('utilities')->sendNotification(
					$accountRequest->getRequestId()->getEmail(), 
					'', 
					'', 
					'', 
					$subject, 
					'<ul>
						<li>Grupo: '.$accountRequest->getGroupId()->getName().'</li>
						<li>MUDID: '.$accountRequest->getRequestId()->getMudId().'</li>
						<li>Tipo: '.$accountRequestType.'</li>
					</ul>
					<p>'.$accountRequest->getObservation().'</p>'
				);

				$this->get('utilities')->logger(
	         		'APPLY', 
	         		$message['message'].' - Grupo: '.$accountRequest->getGroupId()->getName().'- Usuario: '.$accountRequest->getRequestId()->getMudId(), 
	         		$this->getUser()->getUsername()
	         	);

			} catch (\Exception $e) {
				$message = ['type' => 'error', 'message' => $e->getMessage()];
			}

			$response = new JsonResponse($message);

			return $response;
	}

	public function addUserAction($accountRequest){

		    $user = new Users();
            $user->setUsername($accountRequest->getMudId());
            $user->setName($accountRequest->getMudId());
            $user->setDescription(''); //Required parameter. TO DO FIXE IT.
            $user->setIsActive(1);

            $width 	= $this->container->getParameter('avatar_width');
        	$height = $this->container->getParameter('avatar_height');
        	$image = \Nononsense\UtilsBundle\Classes\Utils::generateColoredPNG(['width' => $width, 'height' => $height]);
        	$user->setPhoto($image);
        	$user->setActiveDirectory(1);

            $generator = new SecureRandom();
            $user->setSalt(base64_encode($generator->nextBytes(10)));

            $factory 	= $this->get('security.encoder_factory');
            $encoder 	= $factory->getEncoder($user);
            $password 	= $encoder->encodePassword(uniqid(), $user->getSalt());
            $user->setPassword($password);

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
	}

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
			$ldapdn = $this->container->getParameter('ldap.dn_string');
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

		$mudid = preg_replace('/[^a-z0-9]/i', '', $request->get('mudid'));

		$ldapdn   = $this->container->getParameter('ldap.search_dn'); //'cn=admin,cn=users,dc=demo,dc=local'; $this->container->getParameter('ldap.search_dn');
		$ldappass = $this->container->getParameter('ldap.search_password');; //$this->container->getParameter('ldap.search_password');

		$uid_key = 'sAMAccountName'; //$this->container->getParameter('ldap.uid_key');
		$queryDn = $this->container->getParameter('ldap.base_dn'); //$this->container->getParameter('ldap.base_dn');

		$filter  = '({uid_key}='.$mudid.')';

		$userSearch  = str_replace('{uid_key}', $uid_key, $filter);

		try {
			if (!$mudid) {
				throw new \Exception("MUD ID introducido no encontrado");
			}
			
			$ldap   = $this->container->get('ldap');
			$bind   = $ldap->bind($ldapdn, $ldappass);
	        $query  = $ldap->find($queryDn, $userSearch, ['mail','displayname']);

	        if (!$query) {
	        	throw new \Exception("MUD ID introducido no encontrado");
	        }

	        if (!isset($query[0]['mail'][0]) || !trim($query[0]['mail'][0])) {
	        	throw new \Exception("El MUD ID introducido deber tener un email asociado");
	        }

	        $message = ['type' => 'success', 'message' => $query];
		} catch (\Exception $e) {
			$message = ['type' => 'error', 'message' => $e->getMessage()];
		}

		$response = new JsonResponse($message);

		return $response;
	}

	public function getBulkMudIdAction(Request $request){
		return new JsonResponse($this->checkBulkMudIds($request->get('bulk')));
	}


	public function checkBulkMudIds(string $bulkMudIds){
		$ldapdn   = $this->container->getParameter('ldap.search_dn');
		$ldappass = $this->container->getParameter('ldap.search_password');

		$uid_key = 'sAMAccountName';
		$queryDn = $this->container->getParameter('ldap.base_dn');

		$errors = [];
		$mudIds = [];

		try {
			$bulkMudIds = explode(',', preg_replace('(\s+)', '', $bulkMudIds));

			$ldap   = $this->container->get('ldap');
			$bind   = $ldap->bind($ldapdn, $ldappass);

			foreach($bulkMudIds as $key => $bulkMudId){
				$filter  = '({uid_key}='.$bulkMudId.')';
				$userSearch  = str_replace('{uid_key}', $uid_key, $filter);

				$query = $ldap->find($queryDn, $userSearch, ['displayname']);

				if (!$query) {
					$errors[] = $bulkMudId;
	        	}

	        	$mudIds[$key]['mudId'] = $bulkMudId;
	        	$mudIds[$key]['displayname'] = (isset($query[0]['displayname'][0])) ? $query[0]['displayname'][0] : null;
			}

			if ($errors){
				throw new \Exception("Los siguientes MUD_IDs no han sido encontrados: ".implode(', ', $errors));
			}

	        $message = ['type' => 'success', 'message' => $mudIds];
		} catch (\Exception $e) {
			$message = ['type' => 'error', 'message' => $e->getMessage()];
		}

		return $message;
	}

	public function reportCsvAction($data, $filters){

        if (!$this->get('app.security')->permissionSeccion('usuarios_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties();
        $phpExcelObject->setActiveSheetIndex(0)
        ->setCellValue('A1','NUMERO DE SOLICITUD')
        ->setCellValue('B1','MUD ID')
        ->setCellValue('C1','NOMBRE DEL SOLICITADO')
        ->setCellValue('D1','NOMBRE DEL SOLICITANTE')
        ->setCellValue('E1','MUD ID DEL SOLICITANTE')
        ->setCellValue('F1','FECHA DE SOLICITUD')
        ->setCellValue('G1','REVISADOS')
        ->setCellValue('H1','TIPO DE SOLICITUD');

        $row = 2;
        foreach ($data as $key => $value) {
        	switch($value->getRequestType()){
        		case 1: $type="Alta";break;
        		case 0: $type="Baja";break;
        	}
            $phpExcelObject->getActiveSheet()
             ->setCellValue('A'.$row, $value->getId())
             ->setCellValue('B'.$row, $value->getMudId())
             ->setCellValue('C'.$row, $value->getRefUsername())
             ->setCellValue('D'.$row, $value->getUsername())
             ->setCellValue('E'.$row, $value->getBaseMudId())
             ->setCellValue('F'.$row, date_format($value->getCreated(), 'd-m-Y'))
             ->setCellValue('G'.$row, '')
             ->setCellValue('H'.$row, $type);

            $row++;
        }

        for($col = 'A'; $col <= 'H'; $col++) {
            $phpExcelObject->getActiveSheet()
                ->getColumnDimension($col)
                ->setAutoSize(true);
        }
        
        $phpExcelObject->getActiveSheet()->setTitle('Account requests '.date('d-m-y'));
        $phpExcelObject->setActiveSheetIndex(0);

        $writer     = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response   = $this->get('phpexcel')->createStreamedResponse($writer);

        $dispositionHeader = $response->headers->makeDisposition(
          ResponseHeaderBag::DISPOSITION_ATTACHMENT,
          'Account-requests-'.date('d-m-y').'.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    public function reportPDFAction($request, $data, $filters){

        $html='<html><body style="font-size:8px;width:100%">';
        $sintax_head_f="<b>Filtros:</b><br>";

        if($request->get("username")){
            $html.=$sintax_head_f."Nombre del solicitante => ".$request->get("username")."<br>";
            $sintax_head_f="";
        }

        if($request->get("mudid")){
            $html.=$sintax_head_f."MUD ID => ".$request->get("mudid")."<br>";
            $sintax_head_f="";
        }

        if($request->get("type")!== null && is_numeric($request->get("type"))){
            switch($request->get("type")){
                case 0: $hstate="Baja";break;
                case 1: $hstate="Alta";break;
            }
            $html.=$sintax_head_f."Tipo de solicitud => ".$hstate."<br>";
            $sintax_head_f="";
        }

        if($request->get("from") || $request->get("until")){
            $html.=$sintax_head_f."Fecha  => ".$request->get("from") . " / " . $request->get("until") . "<br>";
            $sintax_head_f="";
        }

        $html.='<br>
            <table autosize="1" style="overflow:wrap;width:95%">
            <tr style="font-size:8px;width:100%">
                <th style="font-size:8px;width:10%">NUMERO DE SOLICITUD</th>
                <th style="font-size:8px;width:10%">MUD ID</th>
                <th style="font-size:8px;width:20%">NOMBRE DEL SOLICITADO</th>
                <th style="font-size:8px;width:20%">NOMBRE DEL SOLICITANTE</th>
                <th style="font-size:8px;width:10%">MUD ID DEL SOLICITANTE</th> 
                <th style="font-size:8px;width:10%">FECHA DE SOLICITUD</th> 
                <th style="font-size:8px;width:10%">REVISADOS</th> 
                <th style="font-size:8px;width:10%">TIPO DE SOLICITUD</th> 
            </tr>';

        foreach ($data as $key => $value) {
        	switch($value->getRequestType()){
        		case 1: $type="Alta";break;
        		case 0: $type="Baja";break;
        	}
            $html.='<tr style="font-size:8px">
                        <td>'.$value->getId().'</td>
                        <td>'.$value->getMudId().'</td>
                        <td>'.$value->getRefUsername().'</td>
                        <td>'.$value->getUsername().'</td>
                        <td>'.$value->getBaseMudId().'</td>
                        <td>'.date_format($value->getCreated(), 'd-m-Y').'</td>
                        <td></td>
                        <td>'.$type.'</td></tr>';
        }

        $html.='</table></body></html>';

        return $this->get('utilities')->returnPDFResponseFromHTML($html, self::TITLE_PDF, self::FILENAME_PDF);
    }
}