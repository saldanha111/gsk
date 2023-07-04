<?php

namespace Nononsense\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function widgetAction()
    {        
        // $path = '/' . $this->container->getParameter('user_img_dir');
        // //userthumb.jpg

        // $rootdir = $this->get('kernel')->getRootDir();
        // $rootdiruserimg = $rootdir .'/../web' .$path;

        // $user = $this->getUser();

        // if(file_exists($rootdiruserimg . 'thumb_'.$user->getId().'.jpg')){
        //     $imgExists = 1;

        // }else{

        //     $imgExists = 0;
        //     $path = $path .'userthumb.jpg';

        // }

        return $this->render('NononsenseUserBundle:Default:widget.html.twig');
    }
}
