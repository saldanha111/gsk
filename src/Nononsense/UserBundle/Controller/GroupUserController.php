<?php

namespace Nononsense\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UserBundle\Entity\Roles;
use Nononsense\UserBundle\Form\Type as FormUsers;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;


class GroupUserController extends Controller
{
    public function adduserAction($page, $query = 'q')
    {
        $maxResults = $this->container->getParameter('results_per_page');

         $users = $this->getDoctrine()
                      ->getRepository('NononsenseUserBundle:Users')
                      ->listUsers($page, $maxResults, 'id', $query, false);

        $paging = array(
            'page' => $page,
            'path' => 'nononsense_users_homepage',
            'count' => max(ceil($users->count() / $maxResults), 1),
            'results' => $users->count()
            );
        $path = '/' . $this->container->getParameter('user_img_dir');
        return $this->render('NononsenseUserBundle:Users:index.html.twig', array(
            'users' => $users,
            'webPath' => $path,
            'paging' => $paging,
            'query' => $query
        ));
    }
    
}
