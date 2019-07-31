<?php

namespace Nononsense\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Ldap\LdapClient;


class EmailController extends Controller
{
    public function sendmailAction(Request $request)
    {
        $username = $request->query->get('username');
        //test auth


        //TODO: control access to Editors
        $email = "gusherpol@gmail.com";

        $asunto = 'GSK TEST EMAIL';

        //TODO: extraer datos reales para eso necesitaremos los ids del contraato


        //completar asunto


        $message = "Email de pruebas!!!";

        //ruta al logo
        $baseURL = $this->container->getParameter('cm_installation');
        $logo = "";

        //TODO: link de momento ponemos el enlace a la $baseURL
        $link = $baseURL;

        $resultado = $this->_sendNotification($email, $link, $logo, "", $asunto, $message);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent('<html><body><div>' . $email . '</div><div>Resultado del email: '.$resultado.'</div></body></html>');
        return $response;

    }

    private function _sendNotification($mailTo, $link, $logo, $accion, $subject, $message)
    {
        $mailLogger = new \Swift_Plugins_Loggers_ArrayLogger();
        $this->get('mailer')->registerPlugin(new \Swift_Plugins_LoggerPlugin($mailLogger));
        $email = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->container->getParameter('mailer_user'))
            ->setTo($mailTo)
            ->setBody(
                $this->renderView(
                    'NononsenseHomeBundle:Email:notificationUser.html.twig', array(
                    'logo' => $logo,
                    'accion' => $accion,
                    'message' => $message,
                    'link' => $link
                )),
                'text/html'
            );
        $failures = "";
        if ($this->get('mailer')->send($email,$failures)) {
            echo '[SWIFTMAILER] sent email to ' . $mailTo;
            echo 'LOG: ' . $mailLogger->dump();
            return true;
        } else {
            echo '[SWIFTMAILER] not sending email: ' . $mailLogger->dump();
            var_dump($failures);
            return false;
        }

    }
}
