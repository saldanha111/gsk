<?php

namespace Nononsense\GroupBundle\Controller;

use Nononsense\HomeBundle\Entity\Logs;
use Nononsense\HomeBundle\Entity\LogsTypes;
use Nononsense\UserBundle\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\GroupBundle\Entity\GroupsSignatures;
use Nononsense\UserBundle\Entity\GroupsSubsecciones;
use Nononsense\GroupBundle\Form\Type as FormGroups;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Nononsense\UserBundle\Entity\AccountRequests;
use Nononsense\UserBundle\Entity\AccountRequestsGroups;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class GroupController extends Controller
{
    public function indexAction(Request $request)
    {
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion') && !$this->get('app.security')->permissionSeccion('view_groups')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }
        $user = $this->container->get('security.context')->getToken()->getUser();

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

        if($request->get("user")){
            $filters["user"]=$request->get("user");
        }


        $array_item["filters"]=$filters;
        $array_item["groups"] = $this->getDoctrine()->getRepository(Groups::class)->list("list",$filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(Groups::class)->list("count",$filters);

        if ($this->get('app.security')->permissionSeccion('grupos_gestion') ) {
            $array_item["editable"]=true;
        }

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

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
            return $this->render('NononsenseGroupBundle:Group:index.html.twig',$array_item);  
        }
        else{
            //Exportamos a Excel
            $desc_pdf="Listado de grupos";

            if($request->get("export_excel")){
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                $phpExcelObject->getProperties();
                $phpExcelObject->setActiveSheetIndex(0)
                 ->setCellValue('A1', $desc_pdf." - ".$user->getUsername()." - ".$this->get('utilities')->sp_date(date("d/m/Y H:i:s")));
                $phpExcelObject->setActiveSheetIndex()
                 ->setCellValue('A2', 'Nº')
                 ->setCellValue('B2', 'Nombre')
                 ->setCellValue('C2', 'Descripción')
                 ->setCellValue('D2', 'Fecha alta')
                 ->setCellValue('E2', 'Estado');
            }

            if($request->get("export_pdf")){

                $html='<html><body style="font-size:8px;width:100%">';
                $sintax_head_f="<b>Filtros:</b><br>";

                if($request->get("name")){
                    $html.=$sintax_head_f."Grupo => ".$request->get("name")."<br>";
                    $sintax_head_f="";
                }

                if($request->get("user")){
                    $html.=$sintax_head_f."Usuario => ".$request->get("user")."<br>";
                    $sintax_head_f="";
                }


                if($request->get("state")){
                    switch($request->get("state")){
                        case "active": $hstate="Activo";break;
                        case "inactive": $hstate="Inactivo";break;
                    }
                    $html.=$sintax_head_f."Estado => ".$hstate."<br>";
                    $sintax_head_f="";
                }


                $html.='<br><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%">
                        <th style="font-size:8px;width:6%">Nº</th>
                        <th style="font-size:8px;width:30%">Nombre</th>
                        <th style="font-size:8px;width:44%">Descripción</th>
                        <th style="font-size:8px;width:10%">Fecha alta</th>
                        <th style="font-size:8px;width:10%">Estado</th>
                    </tr>';
            }

            $i=3;
            foreach($array_item["groups"] as $item){
                if($item["isActive"]){
                    $state="Si";
                }
                else{
                    $state="No";
                }
                if($request->get("export_excel")){
                    $phpExcelObject->getActiveSheet()
                    ->setCellValue('A'.$i, $item["id"])
                    ->setCellValue('B'.$i, $item["name"])
                    ->setCellValue('C'.$i, $item["description"])
                    ->setCellValue('D'.$i, ($item["created"]) ? $this->get('utilities')->sp_date($item["created"]->format('d/M/Y H:i:s')) : '')
                    ->setCellValue('E'.$i, $state);
                }

                if($request->get("export_pdf")){
                    $html.='<tr style="font-size:8px">
                        <td>'.$item["id"].'</td>
                        <td>'.$item["name"].'</td>
                        <td>'.$item["description"].'</td>
                        <td>'.(($item["created"]) ? $this->get('utilities')->sp_date($item["created"]->format('d/m/Y H:i:s')) : '').'</td>
                        <td>'.$state.'</td>
                    </tr>';
                }

                $i++;
            }

            if($request->get("export_excel")){
                $phpExcelObject->getActiveSheet()->setTitle('Listado de grupos');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                  ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                  'list_groups.xlsx'
                );
                $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'maxage=1');
                $response->headers->set('Content-Disposition', $dispositionHeader);

                return $response; 
            }

            if($request->get("export_pdf")){
                $html.='</table></body></html>';
                $this->get('utilities')->returnPDFResponseFromHTML($html,$desc_pdf);
            }
        }
        
        
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

            $permissions = $request->get('permissions');

            if($request->get('permissions')){
                foreach ($permissions as $permission) {
                    $newGroupsSubsecciones = new GroupsSubsecciones();
                    $newGroupsSubsecciones->setGroup($group);
                    $newGroupsSubsecciones->setSubseccion($em->getRepository('NononsenseUserBundle:Subsecciones')->find($permission));
                    $em->persist($newGroupsSubsecciones);
                }
            }

            $this->get('session')->getFlashBag()->add(
                'createdGroup',
                'El grupo '.$group->getName().' ha sido creado'
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
            

            $this->get('utilities')->logger(
                'GROUP', 
                'El grupo '.$group->getName().' ha sido creado', 
                $this->getUser()->getUsername()
            );

            return $this->redirect($this->generateUrl('nononsense_groups_homepage'));
        }

        $editable=false;
        if ($this->get('app.security')->permissionSeccion('grupos_gestion') ) {
            $editable=true;
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

            $this->get('utilities')->logger(
                'GROUP', 
                'El grupo '.$data['name'].' ha sido clonado con el nombre de '.$group->getName(), 
                $this->getUser()->getUsername()
            );
                        
            return $this->redirect($this->generateUrl('nononsense_groups_homepage'));
        }

        return $this->render('NononsenseGroupBundle:Group:clone.html.twig', array(
            'createGroup' => $form->createView(),
            'group' => $data
        ));
    }
    
    public function showAction(Request $request,$id)
    {
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion') && !$this->get('app.security')->permissionSeccion('view_groups')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $groupAdmin = $this->isGroupAdmin($id);

        $group = $this->getDoctrine()
                      ->getRepository('NononsenseGroupBundle:Groups')
                      ->find($id);
        

        $editable=false;
        $clonable = false;

        $signatures=$this->getDoctrine()->getRepository('NononsenseGroupBundle:GroupsSignatures')->findBy(array("group" => $group),array("id" => "ASC"));

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
            
            if ($this->get('app.security')->permissionSeccion('grupos_gestion') ) {
                $editable=true;
            }
            return $this->render('NononsenseGroupBundle:Group:show.html.twig', array(
                'group' => $group,
                'editable' => $editable,
                'clonable' => $clonable,
                'signatures' => $signatures
            ));
        }
        else{
        
            if($request->get("export_excel")){
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                $phpExcelObject->getProperties();
                $phpExcelObject->setActiveSheetIndex(0)
                 ->setCellValue('A1', "Audit trail Grupos - ".$user->getUsername()." - ".$this->get('utilities')->sp_date(date("d/m/Y H:i:s")));
                $phpExcelObject->setActiveSheetIndex()
                 ->setCellValue('A2', 'Fecha')
                 ->setCellValue('B2', 'Usuario')
                 ->setCellValue('C2', 'Acción')
                 ->setCellValue('D2', 'Justificación');
            }

            if($request->get("export_pdf")){
                $html='<html><body style="font-size:8px;width:100%"><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%">
                        <th style="font-size:8px;width:15%">Fecha</th>
                        <th style="font-size:8px;width:15%">Usuario</th>
                        <th style="font-size:8px;width:20%">Acción</th>
                        <th style="font-size:8px;width:50%">Justificación</th>
                    </tr>';
            }

            $i=3;
            foreach($signatures as $item){

                if($request->get("export_excel")){
                    $phpExcelObject->getActiveSheet()
                    ->setCellValue('A'.$i, ($item->getModified()) ? $this->get('utilities')->sp_date($item->getModified()->format("d/M/Y H:i:s")) : '')
                    ->setCellValue('B'.$i, $item->getUser()->getName())
                    ->setCellValue('C'.$i, $item->getDescription())
                    ->setCellValue('D'.$i, $item->getJustification());

                    $dom = new \DOMDocument;

                    @$dom->loadHTML($item->getChanges());

                    $tableData = [];

                    $rows = $dom->getElementsByTagName('tr');

                    foreach ($rows as $row) {
                      $cells = $row->getElementsByTagName('td');
                      
                      $rowData = [];

                      foreach ($cells as $cell) {
                        $rowData[] = $cell->textContent;
                      }
                      $tableData[] = $rowData;
                    }

                    foreach ($tableData as $rowData) {

                        $i++;
                        $phpExcelObject->getActiveSheet()
                            ->setCellValue('F'.$i, isset($rowData[0]) ? $rowData[0] : '')
                            ->setCellValue('G'.$i, isset($rowData[1]) ? $rowData[1] : '')
                            ->setCellValue('H'.$i, isset($rowData[2]) ? $rowData[2] : '');
                                       
                        
                    }
                }

                if($request->get("export_pdf")){
                    $html.='<tr style="font-size:8px">
                        <td>'.(($item->getModified()) ? $this->get('utilities')->sp_date($item->getModified()->format("d/M/Y H:i:s")) : '').'</td>
                        <td>'.$item->getUser()->getName().'</td>
                        <td>'.$item->getDescription().'</td>
                        <td>'.$item->getJustification().'</td>
                    </tr>';

                    if($item->getChanges()){
                        $html.='<tr style="font-size:8px"><td colspan="2">'.$item->getChanges().'</td></tr>';
                    }
                }

                $i++;
            }

            if($request->get("export_excel")){
                $phpExcelObject->getActiveSheet()->setTitle('Audit trail Grupos');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                  ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                  'audittrail_groups.xlsx'
                );
                $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'maxage=1');
                $response->headers->set('Content-Disposition', $dispositionHeader);

                return $response; 
            }

            if($request->get("export_pdf")){
                $html.='</table></body></html>';
                $this->get('utilities')->returnPDFResponseFromHTML($html,"Audit trail Grupos");
            }
        }
        
    }
    
    public function usersAction($id, $type = 'member')
    {
        $groupAdmin = $this->isGroupAdmin($id);

        if (!$this->get('app.security')->permissionSeccion('grupos_gestion') && !$this->get('app.security')->permissionSeccion('view_groups')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }
        
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion')){
            $admin = false;
        }
        else{
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

        $meUser = $this->container->get('security.context')->getToken()->getUser();

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

                $signature = new GroupsSignatures();
                $signature->setGroup($group);
                $signature->setUser($meUser);
                $signature->setDescription("Se añade a este grupo el usuario ".$user->getName());
                $signature->setJustification($request->get("justification"));
                $signature->setCreated(new \DateTime());
                $signature->setModified(new \DateTime());
                $em->persist($signature);

                $this->get('utilities')->logger(
                    'GROUP', 
                    'El usuario '.$user->getUsername().' de tipo '.$type.' ha sido añadido al grupo '.$group->getName().' manualmente', 
                    $this->getUser()->getUsername()
                );

                $this->simulateAccountRequest($user, $group, 1);

                if($user->getLocked() !== null){
                    $user->setLocked(null);
                }

                $em->persist($user);
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
        $user = $this->container->get('security.context')->getToken()->getUser();
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

        $signature = new GroupsSignatures();
        $signature->setGroup($row->getGroup());
        $signature->setUser($user);
        $signature->setDescription("Se elimina de este grupo el usuario ".$row->getUser()->getName());
        $signature->setJustification($request->get("justification"));
        $signature->setCreated(new \DateTime());
        $signature->setModified(new \DateTime());
        $em->persist($signature);

        if (empty($row)) {
            $this->get('session')->getFlashBag()->add(
                'errorDeletingUser',
                'No fue posible eliminar al usuario. Por favor, inténtelo de nuevo más tarde. Si el error persiste comuníquese con los administradores de la plataforma.'
            );
        } else {
            $em->remove($row);

            if(!(count($row->getUser()->getGroups())-1)){
                $row->getUser()->setLocked(new \DateTime());
                $em->persist($user);
            }

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

            $this->simulateAccountRequest($row->getUser(), $row->getGroup(), 0);
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

    private function simulateAccountRequest(Users $user, Groups $group, $requestType){
        $em = $this->getDoctrine()->getManager();

        $accountRequest = new AccountRequests();
        $accountRequest->setMudId($user->getUsername());
        $accountRequest->setEmail($this->getUser()->getEmail());
        $accountRequest->setUsername($this->getUser()->getName());
        $accountRequest->setDescription('Solicitud creada manualmente');
        $accountRequest->setRequestType($requestType);
        $accountRequest->setIsManual(1);

        $isActiveDirectory = ($user->getActiveDirectory() ? 1 : 0);
        $accountRequest->setActiveDirectory($isActiveDirectory);

        $accountRequestGroup = new AccountRequestsGroups();
        $accountRequestGroup->setRequestId($accountRequest);
        $accountRequestGroup->setGroupId($group);
        $accountRequestGroup->setStatus(1);
        $accountRequestGroup->setUpdated(new \DateTime());

        $accountRequest->addRequest($accountRequestGroup);

        $em->persist($accountRequest);
        $em->flush();

        return $accountRequest;
    }

    public function showUsersAction(Request $request, $id)
    {
        if (!$this->get('app.security')->permissionSeccion('grupos_gestion') && !$this->get('app.security')->permissionSeccion('view_groups')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $groupAdmin = $this->isGroupAdmin($id);

        $group = $this->getDoctrine()
                      ->getRepository('NononsenseGroupBundle:Groups')
                      ->find($id);
    
        if($request->get("export_excel")){
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

            $phpExcelObject->getProperties();
            $phpExcelObject->setActiveSheetIndex(0)
             ->setCellValue('A1', 'Grupo')
             ->setCellValue('A2', $group->getName())
             ->setCellValue('B1', 'Estado')
             ->setCellValue('B2', ($group->getIsActive() ? 'Activo' : 'Inactivo'))
             ->setCellValue('C1', 'Fecha de creación')
             ->setCellValue('C2', $this->get('utilities')->sp_date($group->getCreated()->format('d/m/Y')))
             ->setCellValue('D1', 'Descripción')
             ->setCellValue('D2', strip_tags($group->getDescription()));
        }

        if($request->get("export_pdf")){
            $html='<html><body style="font-size:8px;width:100%">';
            $html='
                <table autosize="1" style="overflow:wrap;width:100%">
                    <tr style="font-size:8px;width:100%">
                        <th style="font-size:8px;font-weight:bold;">Grupo</th>
                        <th style="font-size:8px;font-weight:bold">Estado</th>
                        <th style="font-size:8px;font-weight:bold">Fecha de creación</th>
                        <th style="font-size:8px;font-weight:bold">Descripción</th>
                    </tr>
                    <tr style="font-size:8px">
                        <td>'.$group->getName().'</td>
                        <td>'.($group->getIsActive() ? 'Activo' : 'Inactivo').'</td>
                        <td>'.$this->get('utilities')->sp_date($group->getCreated()->format('d/m/Y')).'</td>
                        <td>'.strip_tags($group->getDescription()).'</td>
                    </tr>
                </table>';
            $html.='
                <br><br><table autosize="1" style="overflow:wrap;width:100%">
                    <tr style="font-size:8px;width:100%">
                        <th style="font-size:8px;width:15%;font-weight:bold">Nombre y Apellidos</th>
                        <th style="font-size:8px;width:15%;font-weight:bold">Tipo</th>
                    </tr>';
        }

        if($request->get("export_excel")){
            $phpExcelObject->getActiveSheet()
             ->setCellValue('A4', 'Nombre y Apellidos')
             ->setCellValue('B4', 'Tipo');

             foreach(range('A','D') as $columnID) {
                $phpExcelObject->getActiveSheet()->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }
        }

        $i=5;

        foreach($group->getUsers() as $groupUser){

            if($request->get("export_excel")){
                $phpExcelObject->getActiveSheet()
                ->setCellValue('A'.$i, $groupUser->getUser()->getName())
                ->setCellValue('B'.$i, ($groupUser->getType() == 'admin' ? 'Administrador' : 'Miembro' ));
            }

            if($request->get("export_pdf")){
                $html.='<tr style="font-size:8px">
                    <td>'.$groupUser->getUser()->getName().'</td>
                    <td>'.($groupUser->getType() == 'admin' ? 'Administrador' : 'Miembro' ).'</td>
                </tr>';
            }

            $i++;
        }

        if($request->get("export_excel")){
            $phpExcelObject->getActiveSheet()->setTitle('Audit trail Grupo-Usuarios');
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $phpExcelObject->setActiveSheetIndex(0);

            // create the writer
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
            // create the response
            $response = $this->get('phpexcel')->createStreamedResponse($writer);
            // adding headers
            $dispositionHeader = $response->headers->makeDisposition(
              ResponseHeaderBag::DISPOSITION_ATTACHMENT,
              'audittrail_user_groups.xlsx'
            );
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response; 
        }

        if($request->get("export_pdf")){
            $html.='</table></body></html>';
            $this->get('utilities')->returnPDFResponseFromHTML($html,"Audit trail Grupo-Usuarios");
        }

        return $this->redirect($this->generateUrl('nononsense_group_show', array('id' => $id)));
    }
}
