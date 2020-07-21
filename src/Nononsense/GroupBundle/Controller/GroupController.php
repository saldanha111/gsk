<?php

namespace Nononsense\GroupBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\UserBundle\Entity\GroupsSubsecciones;
use Nononsense\GroupBundle\Form\Type as FormGroups;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;

class GroupController extends Controller
{
    public function indexAction($page, $query = 'q')
    {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $admin = false;
        } else {
            $admin = true;
        }
        $maxResults = $this->container->getParameter('results_per_page');

         $groups = $this->getDoctrine()
                        ->getRepository('NononsenseGroupBundle:Groups')
                        ->listGroups($page, $maxResults, 'id', $query, $admin);

        $paging = array(
            'page' => $page,
            'path' => 'nononsense_groups_homepage',
            'count' => max(ceil($groups->count() / $maxResults), 1),
            'results' => $groups->count()
            );
 
        return $this->render('NononsenseGroupBundle:Group:index.html.twig', array(
            'groups' => $groups,
            'paging' => $paging,
            'query' => $query
        ));
        return $this->render('');
    }
    
    public function createAction(Request $request)
    {
        // if does not enjoy the required permission send the user to the
        //groups list
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('nononsense_groups_homepage'));
        }
        // create a group entity
        $group = new Groups();
        $group->setIsActive(true);
        $group->setColor(\Nononsense\UtilsBundle\Classes\Utils::generateRandomColor());
        $group->setDescription($this->get('translator')->trans('<p>Insert <strong>here</strong> the group description.</p>'));

        $form = $this->createForm(new FormGroups\GroupType(), $group);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();                
            $em->persist($group);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
            'createdGroup',
            $this->get('translator')->trans('The group named: "') . $group->getName() . $this->get('translator')->trans('" has been created.')
            );
            return $this->redirect($this->generateUrl('nononsense_groups_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $secciones = $em->getRepository('NononsenseUserBundle:Secciones')->findBy(array(), array('name' => 'ASC'));

        return $this->render('NononsenseGroupBundle:Group:create.html.twig', array(
            'createGroup' => $form->createView(),
            'secciones' => $secciones,
            'subseccionesSelected' => []
        ));
    }
    
    public function editAction($id, Request $request)
    {
        $groupAdmin = $this->isGroupAdmin($id);
        // if does not enjoy the required permission send the user to the
        //groups list
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
            && !$groupAdmin) {
            return $this->redirect($this->generateUrl('nononsense_groups_homepage'));
        }
        // get the group entity
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('NononsenseGroupBundle:Groups')
                     ->findOneBy(array('id' => $id));

        $form = $this->createForm(new FormGroups\GroupType(), $group);
        $form->handleRequest($request);

        if ($form->isValid()) {      

            $query = "DELETE FROM NononsenseUserBundle:GroupsSubsecciones gs WHERE gs.group=$id";
            $query = $em->createQuery($query);
            $query->getResult();

            $permissions = $request->get('permissions');

            foreach ($permissions as $permission) {
                $newGroupsSubsecciones = new GroupsSubsecciones();
                $newGroupsSubsecciones->setGroup($group);
                $newGroupsSubsecciones->setSubseccion($em->getRepository('NononsenseUserBundle:Subsecciones')->find($permission));
                $em->persist($newGroupsSubsecciones);
            }

            $em->persist($group);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
            'createdGroup',
            $this->get('translator')->trans('The group named: "') . $group->getName() . $this->get('translator')->trans('" has been edited.')
            );
            return $this->redirect($this->generateUrl('nononsense_groups_homepage'));
        }

        $secciones = $em->getRepository('NononsenseUserBundle:Secciones')->findBy(array(), array('name' => 'ASC'));

        $subseccionesSelected = array();
        $groupsSubsecciones = $em->getRepository('NononsenseUserBundle:GroupsSubsecciones')->findBy(array('group'=>$group));
        foreach ($groupsSubsecciones as $groupSubseccion) {
            array_push($subseccionesSelected, $groupSubseccion->getSubseccion()->getId());
        }


        return $this->render('NononsenseGroupBundle:Group:edit.html.twig', array(
            'createGroup' => $form->createView(),
            'secciones' => $secciones,
            'subseccionesSelected' => $subseccionesSelected
        ));
    }
    
    public function cloneAction($id, Request $request)
    {
        // if does not enjoy the required permission send the user to the
        //groups list
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('nononsense_groups_homepage'));
        }
        // get the original group to be cloned
        $em = $this->getDoctrine();
        $cloned = $em->getRepository('NononsenseGroupBundle:Groups')
                     ->findOneBy(array('id' => $id));
        $data = array();
        $data['id'] = $id;
        $data['name'] = $cloned->getName();
        $data['desc'] = '<p><strong>[Cloned]</strong></p>' . $cloned->getDescription();
        $group = new Groups();
        $group->setIsActive(true);
        $group->setColor(\Nononsense\UtilsBundle\Classes\Utils::generateRandomColor());
        $group->setDescription($data['desc']);

        $form = $this->createForm(new FormGroups\GroupType(), $group);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();                
            $em->persist($group);
            $em->flush();
            //start the cloning of users
            $members = $em->getRepository('NononsenseGroupBundle:GroupUsers')
                     ->findAll(array('group' => $id));
            foreach ($members as $member) {
                $new = new GroupUsers();            
                $new->setGroup($group);
                $user = $em->getRepository('NononsenseUserBundle:Users')->find($member->getUser());
                $new->setUser($user);
                $new->setType($member->getType());
                $em->persist($new);   
            }
            $em->flush();
            
            $this->get('session')->getFlashBag()->add(
            'createdGroup',
            'The group named: "' . $data['name'] . '" has been cloned with the name "' . $group->getName() . '".'
            );
            return $this->redirect($this->generateUrl('nononsense_groups_homepage'));
        }

        return $this->render('NononsenseGroupBundle:Group:clone.html.twig', array(
            'createGroup' => $form->createView(),
            'group' => $data,
        ));
    }
    
    public function showAction($id)
    {
        $groupAdmin = $this->isGroupAdmin($id);

        $group = $this->getDoctrine()
                      ->getRepository('NononsenseGroupBundle:Groups')
                      ->find($id);
        
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
            || $groupAdmin) {
            $editable = true;
        } else {
            $editable = false;
        }
        
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $clonable = true;
        } else {
            $clonable = false;
        }
 
        return $this->render('NononsenseGroupBundle:Group:show.html.twig', array(
            'group' => $group,
            'editable' => $editable,
            'clonable' => $clonable,
        ));
    }
    
    public function usersAction($id, $type = 'member')
    {
        $groupAdmin = $this->isGroupAdmin($id);
        
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
            && !$groupAdmin) {
            $admin = false;
        } else {
            $admin = true;
        }

         $users= $this->getDoctrine()
                      ->getRepository('NononsenseGroupBundle:GroupUsers')
                      ->findUsersByGroup(1, 100000, $id, 'q', $type);

        
        $path = '/' . $this->container->getParameter('user_img_dir');
        return $this->render('NononsenseUserBundle:Groups:index.html.twig', array(
            'users' => $users,
            'webPath' => $path,
            'groupId' => $id,
            'type' => $type,
            'admin' => $admin
        ));
    }
    
    public function addusersAction($id, $type = 'member')
    {
        
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('NononsenseUserBundle:Users')
                  ->findAllUsersNotInGroup($id);

        /* this is to filter external users not within query
        $internal = array();
        foreach ($users as $user) {
            foreach($user->getRoles() as $role){
                if ($role->getRole() != 'ROLE_EXTERNAL') {
                    $internal[] = $user;
                }
            }
        }*/

        return $this->render('NononsenseUserBundle:Groups:searchuser.html.twig', array(
            'users' => $users,
            'type' => $type,
            'groupId' => $id,
        ));
    }
    
    public function addsingleuserAction(Request $request)
    {
        $id = $request->query->get('id');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('NononsenseUserBundle:Users')
                   ->find($id);
        $path = '/' . $this->container->getParameter('user_img_dir');
        return $this->render('NononsenseUserBundle:Groups:singleuser.html.twig', array(
            'user' => $user,
            'webPath' => $path,
        ));
    }

    public function addsinglegroupAction(Request $request)
    {
        $id = $request->query->get('id');
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('NononsenseGroupBundle:Groups')
                   ->find($id);
        return $this->render('NononsenseGroupBundle:Group:singlegroup.html.twig', array(
            'group' => $group,
        ));
    }
    
    public function addbulkAction(Request $request)
    {
        $data = $request->query->get('users');
        $groupId = $request->query->get('id');
        $type = $request->query->get('type');
        $userdata = json_decode($data);
        
        $groupAdmin = $this->isGroupAdmin($groupId);
        
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
            && !$groupAdmin) {
            return $this->redirect($this->generateUrl('nononsense_groups_homepage'));
        } 
        
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('NononsenseGroupBundle:GroupUsers');
        $group = $em->getRepository('NononsenseGroupBundle:Groups')->find($groupId);
        foreach ($userdata as $id) {
            $new = new GroupUsers();            
            $new->setGroup($group);
            $user = $em->getRepository('NononsenseUserBundle:Users')->find($id);
            $new->setUser($user);
            $new->setType($type);
            $em->persist($new);
        }
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'addedUsers',
            'The new members have been added.'
            );
        
        $group = $em->getRepository('NononsenseGroupBundle:Groups')
                    ->find($groupId);
        
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
            || $groupAdmin) {
            $editable = true;
        } else {
            $editable = false;
        }
        
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $clonable = true;
        } else {
            $clonable = false;
        }
        return $this->redirect($this->generateUrl('nononsense_group_show', array('id' => $groupId)));
    }
    
    public function removeuserAction($id, $type = 'member', $userid)
    {
        
        $groupAdmin = $this->isGroupAdmin($id);
        // if does not enjoy the required permission send the user to the
        //groups list
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
            && !$groupAdmin) {
            return $this->redirect($this->generateUrl('nononsense_groups_homepage'));
        }
        $em = $this->getDoctrine()->getManager();
        $row = $em->getRepository('NononsenseGroupBundle:GroupUsers')
                  ->findOneBy(array('user' => $userid, 
                                     'group' => $id,
                                     'type' => $type)
                        );
        if (empty($row)) {
            $this->get('session')->getFlashBag()->add(
            'errorDeletingUser',
            'It was not possible to remove the user. Please, try again later. If the errror persists contact the platform administrators.'
            );
        } else {
            $em->remove($row);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
            'deletedUser',
            'The user membership was revoked.'
            );
        }
        
        $group = $em->getRepository('NononsenseGroupBundle:Groups')
                    ->find($id);
        
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
            || $groupAdmin) {
            //TODO: check if the user is an administrator
            $editable = true;
        } else {
            $editable = false;
        }
        
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $clonable = true;
        } else {
            $clonable = false;
        }
 
        return $this->redirect($this->generateUrl('nononsense_group_show', array('id' => $id)));
    }
    
    public function isGroupAdmin($id)
    {
        $userid = $this->getUser()->getId();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('NononsenseGroupBundle:GroupUsers')
                   ->findOneBy(array('user' => $userid, 
                                     'group' => $id,
                                     'type' => 'admin'));
        if (!empty($user)) {
            return true;
        } else {
            return false;
        }
    }
}
