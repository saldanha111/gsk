<?php

namespace Nononsense\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\HomeBundle\Entity\Certifications;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\UserBundle\Entity\AccountRequests;
use Nononsense\UserBundle\Entity\AccountRequestsGroups;
use Nononsense\UserBundle\Form\Type as Form;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
/**
* 
*/
class CertificationController extends Controller
{
	public function listAction(Request $request){

		if (!$this->isAllowed('crt_gestion')) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

		$certifications = $this->getDoctrine()->getRepository(Certifications::class)->findAll();

		return $this->render('NononsenseHomeBundle:Certifications:certifications.html.twig', ['certifications' => $certifications]);
	}

	public function downloadAction(Request $request, int $id){
		
		if (!$this->isAllowed('crt_gestion')) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

		$certifications = $this->getDoctrine()->getRepository(Certifications::class)->findOneBy(['id' => $id]);

		$response = new BinaryFileResponse($certifications->getPath());

		$response->setContentDisposition(
		    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
		    basename($certifications->getPath())
		);

		return $response;
	}

	private function isAllowed($section){

		if (!$this->get('app.security')->permissionSeccion($section)){

			$this->get('session')->getFlashBag()->add('error', 'No tiene permisos suficientes para acceder a esta sección.');

			return false;
		}

		return true;
	}

}


?>