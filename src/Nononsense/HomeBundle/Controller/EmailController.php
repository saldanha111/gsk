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

        $resultado = $this->get('utilities')->sendNotification($email, $link, $logo, "", $asunto, $message);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent('<html><body><div>' . $email . '</div><div>Resultado del email: '.$resultado.'</div></body></html>');
        return $response;

    }

}
