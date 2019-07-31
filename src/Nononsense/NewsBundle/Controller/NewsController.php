<?php

namespace Nononsense\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nononsense\NewsBundle\Entity\News;
use Nononsense\NewsBundle\Form\Type as FormNews;
use Symfony\Component\HttpFoundation\Request;


class NewsController extends Controller
{
    public function indexAction($page, $query = 'q')
    {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $admin = false;
        } else {
            $admin = true;
        }
        $maxResults = $this->container->getParameter('results_per_page');

        $news = $this->getDoctrine()
                     ->getRepository('NononsenseNewsBundle:News')
                     ->listNews($page, $maxResults, 'id', $query, $admin);

        $paging = array(
            'page' => $page,
            'path' => 'nononsense_news_homepage',
            'count' => max(ceil($news->count() / $maxResults), 1),
            'results' => $news->count()
            );
 
        return $this->render('NononsenseNewsBundle:News:index.html.twig', array(
            'news' => $news,
            'paging' => $paging,
            'query' => $query
        ));
    }
    
    public function showAction($id)
    {
        $news = $this->getDoctrine()
                     ->getRepository('NononsenseNewsBundle:News')
                     ->findOneByIdJoinedToUser($id);
        
        $user = $news->getUser();
 
        return $this->render('NononsenseNewsBundle:News:show.html.twig', array(
            'news' => $news,
            'user' => $user
        ));
    }
    
    public function createAction(Request $request)
    {
        // if does not enjoy the required permission send the user to the
        //news list
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('nononsense_news_homepage'));
        }
        // create a news entity
        $news = new News();
        $news->setIsActive(true);
        $news->setBody($this->get('translator')->trans('<p>Insert <strong>here</strong> the news content.</p>'));
        $news->setUser($this->getUser());

        $form = $this->createForm(new FormNews\NewsType(), $news);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();                
            $em->persist($news);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
            'createdNews',
            'The entry entitled: "' . $news->getTitle() . '" has been created.'
            );
            return $this->redirect($this->generateUrl('nononsense_news_homepage'));
        }

        return $this->render('NononsenseNewsBundle:News:create.html.twig', array(
            'createNews' => $form->createView(),
            'edit' => false
        ));
    }
    
    public function deleteAction($id, Request $request)
    {
        // if does not enjoy the required permission send the user to the
        //news list
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('nononsense_news_homepage'));
        }
        // get the news entity
        $news = $this->getDoctrine()
                     ->getRepository('NononsenseNewsBundle:News')
                     ->find($id);

        $form = $this->createForm(new FormNews\DeleteNewsType(), $news);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $row = $em->getRepository('NononsenseNewsBundle:News')
                      ->findOneBy(array('id' => $id));
            $em->remove($row);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
            'deletedNews',
            'The entry entitled: "' . $row->getTitle() . '" has been removed.'
            );
            return $this->redirect($this->generateUrl('nononsense_news_homepage'));
        }

        return $this->render('NononsenseNewsBundle:News:delete.html.twig', array(
            'deleteNews' => $form->createView(),
            'news' => $news
        ));
    }
    
    public function editAction($id, Request $request)
    {
        // if does not enjoy the required permission send the user to the
        //news list
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('nononsense_news_homepage'));
        }
        // get the news entity
        $news = $this->getDoctrine()
                     ->getRepository('NononsenseNewsBundle:News')
                     ->find($id);


        $form = $this->createForm(new FormNews\NewsType(), $news);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();                
            $em->persist($news);
            $em->flush();
            return $this->redirect($this->generateUrl('nononsense_news_homepage'));
        }

        return $this->render('NononsenseNewsBundle:News:create.html.twig', array(
            'createNews' => $form->createView(),
            'edit' => true
        ));
    }
}
