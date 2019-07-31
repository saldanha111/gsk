<?php

namespace Nononsense\NotificationsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nononsense\NotificationsBundle\Entity\Notifications;
use Nononsense\NotificationsBundle\Entity\MessagesUsers;
use Nononsense\NotificationsBundle\Form\Type as FormMessages;
use Symfony\Component\HttpFoundation\Request;


class MailboxController extends Controller
{
    public function indexAction($page, $query = 'q', $group, $filter = 'inbox')
    {
        $userid = $this->getUser()->getId();
        $groups = $this->getDoctrine()
                      ->getRepository('NononsenseGroupBundle:Groups')
                      ->findGroupsByUser($userid);
        
        
        return $this->render('NononsenseNotificationsBundle:Mailbox:index.html.twig', array(
            'userid' => $userid,
            'page' => $page,
            'query' => $query,
            'groupId' => $group,
            'filter' => $filter,
            'groups' => $groups,
        ));
    }
    
    public function inboxAction($page, $query = 'q', $group, $filter = 'inbox')
    {
        $query = urldecode($query);
        $maxResults = $this->container->getParameter('messages_per_page');
        
        if ($filter == 'trash') {
            $title = $this->get('translator')->trans('Trash');
            $messages = $this->getDoctrine()
                             ->getRepository('NononsenseNotificationsBundle:Notifications')
                             ->findTrashMessages($this->getUser()->getId(), $page, $maxResults, $query, $group);
        } else if ($filter == 'sent') {
            $title = $this->get('translator')->trans('Sent Mail');;
            $messages = $this->getDoctrine()
                             ->getRepository('NononsenseNotificationsBundle:Notifications')
                             ->findOwnMessages($this->getUser()->getId(), $page, $maxResults, $query, $group);
        } else {
            $title = $this->get('translator')->trans('Inbox');
            $messages = $this->getDoctrine()
                             ->getRepository('NononsenseNotificationsBundle:Notifications')
                             ->findUserMessages($this->getUser()->getId(), $page, $maxResults, $query, $group);
        }
        
        
        $unread = $this->getDoctrine()
                       ->getRepository('NononsenseNotificationsBundle:Notifications')
                       ->findUnreadMessages($this->getUser()->getId());
        
        
        $paging = array(
            'page' => $page,
            'path' => 'nononsense_notifications_inbox',
            'count' => max(ceil($messages->count() / $maxResults), 1),
            'results' => $messages->count(),
            'show' => $maxResults
            );
 
        return $this->render('NononsenseNotificationsBundle:Mailbox:inbox.html.twig', array(
            'title' => $title,
            'filter' => $filter,
            'messages' => $messages,
            'paging' => $paging,
            'query' => $query,
            'groupId' => $group,
            'unread' => $unread
        ));
    }
    
    public function showAction($messageId)
    {
        if (false === $this->grantAccess($messageId)) {
            return $this->render('NononsenseNotificationsBundle:Mailbox:forbidden.html.twig');
        }
        $message = $this->getDoctrine()
                        ->getRepository('NononsenseNotificationsBundle:Notifications')
                        ->find($messageId);
        //check if is already read or trashed
        $mar = $this->getDoctrine()
                    ->getRepository('NononsenseNotificationsBundle:MessagesUsers')
                    ->findOneBy(array('notification' => $messageId, 'user' => $this->getUser()->getId()));
        
        if (!$mar) {
            $mu = new MessagesUsers();
            $mu->setUser($this->getUser());
            $mu->setNotification($message);
            $mu->setTrash(0);
            $em = $this->getDoctrine()->getManager();                
            $em->persist($mu);
            $em->flush();
            $trash = 0;
        } else {
            $trash = $mar->getTrash();
        }
        //get the new unread count
        $unread = $this->getDoctrine()
                           ->getRepository('NononsenseNotificationsBundle:Notifications')
                           ->findUnreadMessages($this->getUser()->getId());
        
        return $this->render('NononsenseNotificationsBundle:Mailbox:show.html.twig', array(
            'message' => $message,
            'unread' => $unread,
            'trash' => $trash,
        ));
    }
    
    public function composeAction(Request $request)
    {
        
        $userid = $this->getUser()->getId();
        $unread = $this->getDoctrine()
                           ->getRepository('NononsenseNotificationsBundle:Notifications')
                           ->findUnreadMessages($this->getUser()->getId());
        // create a notifications entity
        $message = new Notifications();
        $message->setBody($this->get('translator')->trans('<p>Introduce the <strong>message content</strong> here.</p>'));

        $form = $this->createForm(new FormMessages\ComposeType(), $message, array('userid' => $userid, 'action' => $this->generateUrl('nononsense_notifications_compose')));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $message->setAuthor($this->getUser());
            $em = $this->getDoctrine()->getManager();                
            $em->persist($message);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
            'sentMessage',
            'The message entitled: "' . $message->getSubject() . '" has been sent.'
            );
            return $this->redirect($this->generateUrl('nononsense_notifications_homepage'));
        } 

        return $this->render('NononsenseNotificationsBundle:Mailbox:compose.html.twig', array(
            'composeMessage' => $form->createView(),
            'unread' => $unread
        ));
    }
    
    private function grantAccess($messageId) {
        $userid = $this->getUser()->getId();
        $check = $this->getDoctrine()
                      ->getRepository('NononsenseNotificationsBundle:Notifications')
                      ->check($userid, $messageId);
        return $check;
    }
    
    public function widgetAction()
    {
        /*
        $messages = $this->getDoctrine()
                         ->getRepository('NononsenseNotificationsBundle:Notifications')
                         ->findWidgetMessages($this->getUser()->getId());
        
        $unread = $this->getDoctrine()
                       ->getRepository('NononsenseNotificationsBundle:Notifications')
                       ->findUnreadMessages($this->getUser()->getId());
        */
        $messages = $this->getDoctrine()
            ->getRepository('NononsenseNotificationsBundle:Notifications')
            ->findWidgetMessages(0);

        $unread = $this->getDoctrine()
            ->getRepository('NononsenseNotificationsBundle:Notifications')
            ->findUnreadMessages(0);


        return $this->render('NononsenseNotificationsBundle:Mailbox:widget.html.twig', array(
            'messages' => $messages,
            'unread' => $unread,
            'webPath' => '/' . $this->container->getParameter('user_img_dir'),
        ));
    }
    
    public function markasreadAction($page, $query = 'q', $group, $filter = 'inbox', $items = '')
    {
        $messages = explode('-', $items);
        if (count($messages) > 1) {
            array_pop($messages);
        }
        foreach ($messages as $message) {
            $this->setReadMessage($message);
        }
        return $this->redirect($this->generateUrl('nononsense_notifications_inbox', array(
            'filter' => $filter,
            'query' => $query,
            'group' => $group,
            'page' => $page
        )));
    }
    
    public function markastrashAction($items = '')
    {
        $messages = explode('-', $items);

        if (count($messages) > 1) {
            array_pop($messages);
        }
        foreach ($messages as $message) {
            $this->setTrashMessage($message, 1);
        }
        return $this->redirect($this->generateUrl('nononsense_notifications_inbox'));
    }
    
    public function markasuntrashAction($items = '')
    {
        $messages = explode('-', $items);
        if (count($messages) > 1) {
            array_pop($messages);
        }
        foreach ($messages as $message) {
            $this->setTrashMessage($message, 0);
        }
        return $this->redirect($this->generateUrl('nononsense_notifications_inbox'));
    }
    
    public function setReadMessage($id) {
        //check if the user has permission over that message
        if (false === $this->grantAccess($id)) {
            return;
        }
        //check if is already read or trashed
        $mar = $this->getDoctrine()
                    ->getRepository('NononsenseNotificationsBundle:MessagesUsers')
                    ->findOneBy(array('notification' => $id, 'user' => $this->getUser()->getId()));
        $em = $this->getDoctrine()->getManager();
        if (!$mar) {
            $message = $this->getDoctrine()
                            ->getRepository('NononsenseNotificationsBundle:Notifications')
                            ->find($id);
            $mu = new MessagesUsers();
            $mu->setUser($this->getUser());
            $mu->setNotification($message);
            $mu->setTrash(0);                
            $em->persist($mu);
            $em->flush();
        } else {
            $em->remove($mar);
            $em->flush();
        } 
    }
    
    public function setTrashMessage($id, $val) {
        //check if the user has permission over that message
        if (false === $this->grantAccess($id)) {
            return;
        }
        //check if is already read or trashed
        $mar = $this->getDoctrine()
                    ->getRepository('NononsenseNotificationsBundle:MessagesUsers')
                    ->findOneBy(array('notification' => $id, 'user' => $this->getUser()->getId()));
        
        if (!$mar) {
            $message = $this->getDoctrine()
                            ->getRepository('NononsenseNotificationsBundle:Notifications')
                            ->find($id);
            $mu = new MessagesUsers();
            $mu->setUser($this->getUser());
            $mu->setNotification($message);
            $mu->setTrash($val);
            $em = $this->getDoctrine()->getManager();                
            $em->persist($mu);
            $em->flush();
        }  else {
            $em = $this->getDoctrine()->getManager(); 
            $mar->setTrash($val);
            $em->flush();
        } 
    }
}
