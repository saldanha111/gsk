<?php 

namespace Nononsense\NotificationsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nononsense\NotificationsBundle\Entity\Notifications;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nononsense\UtilsBundle\Classes\Utils;


/**
* 
*/
class MessagesController extends Controller
{
	
	public function indexAction(Request $request){

        $em     = $this->getDoctrine()->getManager();

        $filters['user']        = $this->container->get('security.context')->getToken()->getUser();
        $filters['page']        = (!$request->get('page')) ? 1 : $request->get('page');
        $filters['section']     = $request->get('section');
        $limit  = 15;

        $emails = $em->getRepository(Notifications::class)->listBy($filters, $limit);

        $params    = $request->query->all();
        unset($params["page"]);
        $parameters = (!empty($params)) ? true : false; 

        $pagination = Utils::paginador($limit, $request, false, $emails->count(), "/", $parameters);

        return $this->render('NononsenseNotificationsBundle:Mailbox:mails.html.twig', ['emails' => $emails, 'pagination' => $pagination]);
    }

    public function editAction(Request $request){
		
		$em     = $this->getDoctrine()->getManager();

		$user   = $this->container->get('security.context')->getToken()->getUser();

		$email  = $em->getRepository(Notifications::class)->find(array('author' => $user, 'id' => $request->get('id')));

		$email->setModified(new \DateTime());

		$em->persist($email);
		$em->flush($email);
		
        return $this->render('NononsenseNotificationsBundle:Mailbox:mailsView.html.twig', ['email' => $email]);
    }

    public function readAction(Request $request){

    	$emails = json_decode($request->get('emails'));

    	$em     = $this->getDoctrine()->getManager();

    	$user   = $this->container->get('security.context')->getToken()->getUser();

    	$emails = $em->getRepository(Notifications::class)->findBy(array('author' => $user, 'id' => $emails));

        if ($emails) {

            foreach ($emails as $key => $email) {
                if ($email->getModified() != $email->getCreated()) {
                    $email->setModified(new \DateTime());
                    $em->persist($email);

                }
            }

            $em->flush($email);
        }

        $response = new Response();
        $response->setContent(json_encode([
            'success' => true,
        ]));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}