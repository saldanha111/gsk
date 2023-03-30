<?php

namespace Nononsense\GroupBundle\Controller;

use Nononsense\UserBundle\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\GroupBundle\Entity\GroupsSignatures;
use Nononsense\UserBundle\Entity\GroupsSubsecciones;
use Nononsense\GroupBundle\Form\Type as FormGroups;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;

class GroupController extends Controller
{
    public function indexAction(Request $request)
    {
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        if(!$request->get("export_excel")){
            if($request->get("page")){
                $filters["limit_from"]=$request->get("page")-1;
            }
            else{
                $filters["limit_from"]=0;
            }
            $filters["limit_many"]=10;
        }
        else{
            $filters["limit_from"]=0;
            $filters["limit_many"]=99999999999;
        }

        if($request->get("name")){
            $filters["name"]=$request->get("name");
        }

        if($request->get("state")){
            $filters["state"]=$request->get("state");
        }


        $array_item["filters"]=$filters;
        $array_item["groups"] = $this->getDoctrine()->getRepository(Groups::class)->list("list",$filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(Groups::class)->list("count",$filters);

        $url=$this->container->get('router')->generate('nononsense_tm_templates');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        $admin = true;
        
        /*$maxResults = $this->container->getParameter('results_per_page');

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
        return $this->render('');*/
        return $this->render('NononsenseGroupBundle:Group:index.html.twig',$array_item);  
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

            $this->get('session')->getFlashBag()->add(
            'createdGroup',
            $this->get('translator')->trans('The group named: "') . $group->getName() . $this->get('translator')->trans('" has been created.')
            );

            /* Añadimos audittrail a las fichas de grupos */
            $user = $this->container->get('security.context')->getToken()->getUser();
            $signature = new GroupsSignatures();
            $signature->setGroup($group);
            $signature->setUser($user);
            $signature->setDescription("Se crea un nuevo grupo");
            $signature->setJustification($request->get("justification"));
            $signature->setCreated(new \DateTime());
            $signature->setModified(new \DateTime());
            $em->persist($signature);

            $em->flush();

            $group->setColor(\Nononsense\UtilsBundle\Classes\Utils::generateRandomColor());
            $group->setDescription($this->get('translator')->trans('<p>Insert <strong>here</strong> the group description.</p>'));
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
        $changes=$this->getChangesInGroup($request,$group);             
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

            /* Añadimos audittrail a las fichas de grupos */
            $user = $this->container->get('security.context')->getToken()->getUser();
            $signature = new GroupsSignatures();
            $signature->setGroup($group);
            $signature->setUser($user);
            $signature->setDescription("Se modifica el grupo");
            $signature->setJustification($request->get("justification"));
            $signature->setChanges($changes);
            $signature->setCreated(new \DateTime());
            $signature->setModified(new \DateTime());
            $em->persist($signature);

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
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $groupAdmin = $this->isGroupAdmin($id);

        $group = $this->getDoctrine()
                      ->getRepository('NononsenseGroupBundle:Groups')
                      ->find($id);
        

        $editable = true;
        $clonable = false;

        $signatures=$this->getDoctrine()->getRepository('NononsenseGroupBundle:GroupsSignatures')->findBy(array("group" => $group),array("id" => "ASC"));
        
 
        return $this->render('NononsenseGroupBundle:Group:show.html.twig', array(
            'group' => $group,
            'editable' => $editable,
            'clonable' => $clonable,
            'signatures' => $signatures
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
            }
        }
        $em->flush();

        if(!$error){
            $this->get('session')->getFlashBag()->add(
                'addedUsers',
                'The new members have been added.'
            );
        }
        return $this->redirect($this->generateUrl('nononsense_group_show', array('id' => $groupId)));
    }
    
    public function removeuserAction($id, $type = 'member', $userid)
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

    public function getChangesInGroup($request,$group){
        $changes="";
        $em = $this->getDoctrine()->getManager();
        if($request->get("groups")){
            if($request->get("groups")["name"] && $request->get("groups")["name"]!=$group->getName()) {
                $changes.="<tr><td>Nombre</td><td>".$group->getName()."</td><td>".$request->get("groups")["name"]."</td></tr>";
            }
            if($request->get("groups")["color"] && $request->get("groups")["color"]!=$group->getColor()) {
                $changes.="<tr><td>Color</td><td>".$group->getColor()."</td><td>".$request->get("groups")["color"]."</td></tr>";
            }
            if(!array_key_exists("isActive", $request->get("groups"))){
                $active=FALSE;
            }
            else{
               $active=TRUE;
            }
            
            if($active!=$group->getIsActive()) {
                if($group->getIsActive()){
                    $new="No";
                    $old="Yes";
                }
                else{
                    $new="Yes";
                    $old="No";
                }
                $changes.="<tr><td>Activo</td><td>".$old."</td><td>".$new."</td></tr>";
            }
            
            if($request->get("groups")["description"] && $request->get("groups")["description"]!=$group->getDescription()) {
                $changes.="<tr><td>Descripción</td><td>".$group->getDescription()."</td><td>".$request->get("groups")["description"]."</td></tr>";
            }

            $permissions = $request->get('permissions');
            $saved=$em->getRepository('NononsenseUserBundle:GroupsSubsecciones')->findBy(array("group" => $group));
            foreach($saved as $save){
                if (!in_array($save->getSubseccion()->getId(), $permissions)) {
                    $changes.="<tr><td>Permiso ".$save->getSubseccion()->getName()."</td><td>Si</td><td>No</td></tr>";
                }
            }
            foreach ($permissions as $permission) {
                $subseccion=$em->getRepository('NononsenseUserBundle:Subsecciones')->find($permission);
                $exist=$em->getRepository('NononsenseUserBundle:GroupsSubsecciones')->findOneBy(array("group" => $group, "subseccion" => $subseccion));
                if(!$exist){
                    $changes.="<tr><td>Permiso ".$subseccion->getName()."</td><td>No</td><td>Si</td></tr>";
                }
            }

            if($changes!=""){
                $changes="\n<table><tr><td>Campo</td><td>Anterior</td><td>Nuevo</td></tr>".$changes."</table>";
            }
        }

        return $changes;
    }
}
