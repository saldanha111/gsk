<?php

namespace Nononsense\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Nononsense\UserBundle\Entity;
use Nononsense\UserBundle\Form\Type as FormUsers;

class LoginController extends Controller
{
    public function loginAction(Request $request)
    {   
        
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $response = $this->render('NononsenseUserBundle:Default:login.html.twig', 
                array(
                'last_username' => $lastUsername,
                'error'         => $error,
                )
            );
        $response->headers->clearCookie('warning');
        return $response;
    }
    
    
    public function userLoginInitAction()
    {
        $this->get('session')->getFlashBag()->add(
            'success',
            'Login successful.'
        );

        /*
         * Aquí debería parsear los permisos
         */
        //$authorization_rest = $this->get('app.authorization_rest');


        return $this->redirect($this->generateUrl('nononsense_home_homepage'));
    }

    public function logoutAction()
    {
        return array();
    }
    
    public function recoverPasswordAction($error = 0, Request $request)
    {
        $hashCode = $request->query->get('code');
        $hashUser = $request->query->get('id');
        $em = $this->getDoctrine()->getManager();

        if ($hashCode === null && $hashUser === null) {

            $form = $this->createForm(new FormUsers\RecoverPassType());
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $user = $em->getRepository('NononsenseUserBundle:Users')->findOneBy(array('email' => $data['email']));
                if ($user){
                    $role = $user->getRoles()[0]->getName();
                } else {
                    return $this->redirect($this->generateUrl('nononsense_user_recover_password', array('error' => 1)));
                }
                
                if ($role == 'ROLE_ADMIN' || $role == 'ROLE_SUPER_ADMIN') {
                    return $this->redirect($this->generateUrl('nononsense_user_recover_password', array('error' => 3)));
                }
                
                if ($user) {
                    $generator = new SecureRandom();
                    $hash = base64_encode($generator->nextBytes(10));
                    // add the code to user's table to be able to recover the account later after checking it
                    $user->setRecoverPass($hash);
                    $em->flush();

                    $url = $this->generateUrl('nononsense_user_recover_password', array('id' => $user->getId(), 'code' => $hash), true);
                    //build the message body
                    $body = $this->get('translator')->trans('Dear Docxpresso user,');
                    $body .= "\n" . $this->get('translator')->trans('You have received this email because there was a request for a new password for your Docxpresso user account.');
                    $body .= "\n" . $this->get('translator')->trans('You may generate a new password on:');
                    $body .= $url;
                    $body .= "\n" . $this->get('translator')->trans('If you did not request a new password or you have recovered it somehow in the meantime you may just safely ignore this message.');
                    $body .= "\n\n" . $this->get('translator')->trans('Best regards,');
                    $body .= "\n" . $this->get('translator')->trans('Docxpresso Support Team');
                    $message = \Swift_Message::newInstance()
                    ->setSubject($this->get('translator')->trans('Docxpresso: Recover password'))
                    ->setFrom('noreply@docxpresso.com')
                    ->setTo($user->getEmail())
                    ->setBody($body);

                    
                    $email = $this->get('mailer')->send($message);
                    return $this->redirect($this->generateUrl('nononsense_user_recover_password', array('error' => 2)));
                } else {
                    return $this->redirect($this->generateUrl('nononsense_user_recover_password', array('error' => 1)));
                }   
            }
            return $this->render('NononsenseUserBundle:Default:recoverPassword.html.twig', array(
                'form' => $form->createView(),
                'error' => $error
                ));

        } elseif ($hashCode !== null && $hashCode !== 'pending' && $hashUser !== null) {
            // if the request has the code and user
            $user = $em->getRepository('NononsenseUserBundle:Users')->findOneBy(
                array(
                    'recoverPass' => $hashCode,
                    'id' => $hashUser,
                )
            );

            if ($user) {
                // if hash code and user are valid cahnge hash status to pending
                $user->setRecoverPass('pending');
                $em->flush();
                // auto log in the user
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main',serialize($token));

                return $this->redirect($this->generateUrl('nononsense_user_modify_password', array('id' => $hashUser)));

            } else {
                // redirect to login
                return $this->redirect($this->generateUrl('nononsense_user_login'));
            }
        }
    }
    
    public function accessByTokenAction(Request $request)
    {
        //check for POST data
        $postData = $request->request->all();
        foreach ($postData as $key => $value) {
            ${$key} = $value;
        }
        //check for GET data
        $queryData = $request->query->all();
        foreach ($queryData as $key => $value) {
            ${$key} = $value;
        }
        $caducidad = $this->container->getParameter('apikey_expiration');
        $masterKey = $this->container->getParameter('apikey');
        $dataKey = sha1($timestamp . '-' . $uniqid . '-' . $options, true);
	$newAPIKEY = bin2hex(\Nononsense\UtilsBundle\Classes\Utils::sha1_hmac($masterKey, $dataKey));

        if ( $newAPIKEY == $APIKEY && (time() - $timestamp) < $caducidad ) {
            $opt = json_decode(\Nononsense\UtilsBundle\Classes\Utils::base64_decode_url_safe($options));
            $email = $opt->email;
            $user = $this->getDoctrine()
                     ->getRepository('NononsenseUserBundle:Users')
                     ->findOneBy(array('email' => $email));
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);

            if (!empty($opt->url)){
                $url = parse_url($opt->url);
                if(!empty($url['scheme'])){
                    return $this->redirect($opt->url);
                } else {
                    if (substr($opt->url, 0, 1) != '/'){
                        $opt->url = '/' . $opt->url;
                    }
                    return $this->redirect($this->container->getParameter('docxpresso_installation') . $opt->url);
                }
            } else {
                return $this->redirect($this->generateUrl('nononsense_home_homepage'));
            }

        } else {
            $message = "Access Denied.";
            $response = new Response();
            $response->setStatusCode(403);
            $response->setContent($message);
            return $response;
        }
    }
    
    public function requestAccountAction()
    {
    }
 
    public function ldapComponentAction(Request $request){
        
        error_reporting(0);

        echo 'v2';

        $form = $this->createForm(new FormUsers\ldapType());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $response   = new Response();
            $data       = $form->getData();

            $justthese = array("cn", "givenname", "mail", "displayname", "sAMAccountName", "telephonenumber");

            try {

                $ldap       = $this->container->get('ldap');

                $ldaprdn    = $data['dn']; //cn=admin,ou=users,dc=wmservice,dc=corpnet1,dc=com
                $ldappass   = $data['_password'];

                $filter     = $data['filter']; //(objectClass=inetOrgPerson)
                $queryDn    = $data['querydn']; //dc=wmservice,dc=corpnet1,dc=com

                $bind       = $ldap->bind($ldaprdn, $ldappass);
                $query      = $ldap->find($queryDn, $filter, $justthese);

                var_dump($query);

            } catch (\Exception $e) {

                $response->setContent(json_encode([
                    'Error: ' => $e->getMessage()
                ]));
            }


            $response->headers->set('Content-Type', 'application/json');
            return $response;
            
        }

        return $this->render('NononsenseUserBundle:Default:ldapLogin.html.twig', ['form'=>$form->createView()]);

    }   

}
