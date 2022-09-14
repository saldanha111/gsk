<?php

namespace Nononsense\GroupBundle\Controller;

use Nononsense\HomeBundle\Entity\Logs;
use Nononsense\HomeBundle\Entity\LogsTypes;
use Nononsense\UserBundle\Entity\Users;
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
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $admin = true;
        
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
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
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
                'El grupo '.$group->getName().' ha sido creado'
            );

            $this->get('utilities')->logger(
                'GROUP', 
                'El grupo '.$group->getName().' ha sido creado', 
                $this->getUser()->getUsername()
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
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
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
                'El grupo '.$group->getName().' ha sido editado'
            );

            $this->get('utilities')->logger(
                'GROUP', 
                'El grupo '.$group->getName().' ha sido editado', 
                $this->getUser()->getUsername()
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
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
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
                'El grupo '.$data['name'].' ha sido clonado con el nombre de '.$group->getName()
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
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $groupAdmin = $this->isGroupAdmin($id);

        $group = $this->getDoctrine()
                      ->getRepository('NononsenseGroupBundle:Groups')
                      ->find($id);
        

        $editable = true;
        $clonable = false;
        
 
        return $this->render('NononsenseGroupBundle:Group:show.html.twig', array(
            'group' => $group,
            'editable' => $editable,
            'clonable' => $clonable,
        ));
    }
    
    public function usersAction($id, $type = 'member')
    {
        $groupAdmin = $this->isGroupAdmin($id);

        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }
        
        $admin = true;

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
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }
        
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
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

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
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

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
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $data = $request->query->get('users');
        $groupId = $request->query->get('id');
        $type = $request->query->get('type');
        $userdata = json_decode($data);
        $error = false;
        
        $groupAdmin = $this->isGroupAdmin($groupId);
        
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('NononsenseGroupBundle:GroupUsers');
        $group = $em->getRepository(Groups::class)->find($groupId);
        foreach ($userdata as $id) {
            $user = $em->getRepository(Users::class)->find($id);
            if($group && $group->getId() == $this->getParameter("group_id_comite_rrhh") && $user && !$user->getPhone()){
                $error = true;
                $this->get('session')->getFlashBag()->add(
                    'errorAddingUsers',
                    'Para añadir un usuario a este grupo es obligatorio que tenga un número de teléfono. Añade un número de teléfono al usuario y vuelve a intentarlo.'
                );
            }else{
                $new = new GroupUsers();
                $new->setGroup($group);
                $new->setUser($user);
                $new->setType($type);
                $em->persist($new);

                $this->get('utilities')->logger(
                    'GROUP', 
                    'El usuario '.$user->getUsername().' de tipo '.$type.' ha sido añadido al grupo '.$group->getName().' manualmente', 
                    $this->getUser()->getUsername()
                );
            }
        }
        $em->flush();

        if(!$error){
            $this->get('session')->getFlashBag()->add(
                'addedUsers',
                'Los nuevos miembros han sido añadidos'
            );
        }
        return $this->redirect($this->generateUrl('nononsense_group_show', array('id' => $groupId)));
    }
    
    public function removeuserAction(Request $request, $id, $type = 'member', $userid)
    {
        
        $groupAdmin = $this->isGroupAdmin($id);
        // if does not enjoy the required permission send the user to the
        //groups list
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
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
                'No fue posible eliminar al usuario. Por favor, inténtelo de nuevo más tarde. Si el error persiste comuníquese con los administradores de la plataforma.'
            );
        } else {
            $em->remove($row);
            $em->flush();

            $this->get('utilities')->logger(
                'GROUP', 
                'El usuario '.$row->getUser()->getUsername().' de tipo '.$type.' ha sido eliminado del grupo '.$row->getGroup()->getName().' manualmente', 
                $this->getUser()->getUsername()
            );

            $this->get('session')->getFlashBag()->add(
                'deletedUser',
                'Usuario eliminado del grupo con éxito'
            );
        }
        
        $group = $em->getRepository('NononsenseGroupBundle:Groups')
                    ->find($id);
        
        $editable = true;
        $clonable = false;
 
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
