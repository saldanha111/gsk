<?php

namespace Nononsense\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UserBundle\Entity\Roles;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\UserBundle\Entity\AccountRequestsGroups;
use Nononsense\UserBundle\Form\Type as FormUsers;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Nononsense\HomeBundle\Entity\Logs;
use Nononsense\HomeBundle\Entity\LogsTypes;

class UsersController extends Controller
{

    const TITLE_PDF = " Listado de usuarios";
    const FILENAME_PDF = "list_users";

    public function indexAction($page, $query = 'q')
    {
        if (!$this->get('app.security')->permissionSeccion('usuarios_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $admin = false;


        $maxResults = $this->container->getParameter('results_per_page');

         $users = $this->getDoctrine()
                      ->getRepository('NononsenseUserBundle:Users')
                      ->listUsers($page, $maxResults, 'id', $query, $admin);

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
    
    public function showAction($id, Request $request)
    {
        if (!$this->get('app.security')->permissionSeccion('usuarios_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $user = $this->getDoctrine()
                     ->getRepository('NononsenseUserBundle:Users')
                     ->find($id);
        
        $templates =array();

        $editable = true;

        $filters['page'] = (!$request->get('page')) ? 1 : $request->get('page');
        $filters['mudid'] = $user->getUsername();
        $limit  = 15;

        $accountRequests = $this->getDoctrine()->getRepository(AccountRequestsGroups::class)->listBy($filters, $limit);

        $params = $request->query->all();           
        unset($params["page"]);
        $parameters = (!empty($params)) ? true : false;

        $pagination    = \Nononsense\UtilsBundle\Classes\Utils::paginador($limit, $request, false, $accountRequests["count"], "/", $parameters);

        $path = '/' . $this->container->getParameter('user_img_dir');
        return $this->render('NononsenseUserBundle:Users:profile.html.twig', array(
            'user' => $user,
            'webPath' => $path,
            'templates' => $templates,
            'editable' => $editable,
            'accountRequests' => $accountRequests['rows'],
            'pagination' => $pagination
        ));
    }
    
    public function createAction(Request $request)
    {       
        if (!$this->get('app.security')->permissionSeccion('usuarios_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }
        
        // create a user entity
        $width = $this->container->getParameter('avatar_width');
        $height = $this->container->getParameter('avatar_height');
        $size = array('width' => $width, 'height' => $height);
        $image = \Nononsense\UtilsBundle\Classes\Utils::generateColoredPNG($size);
        $user = new Users();
        $user->setIsActive(true);
        $user->setPhoto($image);
        $user->setDescription($this->get('translator')->trans('<p>Insert <strong>here</strong> the user description.</p>'));

        
        $form = $this->createForm(new FormUsers\UserType(true), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $roleId = $request->request->get('account');
            //get assosiated role entity
            $role = $this->getDoctrine()
                         ->getRepository('NononsenseUserBundle:Roles')
                         ->find($roleId);
            $user->addRole($role);
            // generate a new unique salt
            $generator = new SecureRandom();
            $user->setSalt(base64_encode($generator->nextBytes(10)));
            // encode password and set the new value instead the plain value
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
            $user->setPassword($password);
            $em = $this->getDoctrine()->getManager();                
            $em->persist($user);
            $em->flush();
            //grab the id
            $id = $user->getId();
            //save the uploaded image as a  medium size image and a thumb
            $webPath = $this->container->getParameter('user_img_dir');
            $absolutePath = __DIR__ .'/../../../../web/' . $webPath;
            $imagePath = $absolutePath . 'user_' . $id . '.jpg';
            $img = \Nononsense\UtilsBundle\Classes\Utils::resize2JPG($user->getPhoto(), $width, $height, 90, $imagePath);
            $thumbPath = $absolutePath . 'thumb_' . $id . '.jpg';
            $thumb = \Nononsense\UtilsBundle\Classes\Utils::resize2JPG($user->getPhoto(), 140, 140, 100, $thumbPath);
            //Notify the user
            $this->get('session')->getFlashBag()->add(
            'createdUser',
            $this->get('translator')->trans('The user with username: "') . $user->getUsername() . $this->get('translator')->trans('" has been created.')
            );

            $this->get('utilities')->logger(
                'USER', 
                'El usuario '.$user->getUsername().' ha sido creado', 
                $this->getUser()->getUsername()
            );

            return $this->redirect($this->generateUrl('nononsense_users_homepage'));
        }

        return $this->render('NononsenseUserBundle:Users:create.html.twig', array(
            'createUser' => $form->createView(),
            'photo' => $image,
            'rol' => 2, // editor by default
            'admin' => true,
            'create' => true
        ));
    }
    
    public function deleteAction($id, Request $request)
    {
        if (!$this->get('app.security')->permissionSeccion('usuarios_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        //TODO: we can not remove users just like that!!!
        // create a user entity
        // get the user entity
        $user = $this->getDoctrine()
                     ->getRepository('NononsenseUserBundle:Users')
                     ->find($id);

        $form = $this->createForm(new FormUsers\DeleteUserType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $row = $em->getRepository('NononsenseUserBundle:Users')
                      ->findOneBy(array('id' => $id));
            $em->remove($row);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
            'deletedUser',
            $this->get('translator')->trans('The user with username: "') . $user->getUsername() . $this->get('translator')->trans('" has been removed.')
            );

            $this->get('utilities')->logger(
                'USER', 
                'El usuario '.$user->getUsername().' ha sido eliminado', 
                $this->getUser()->getUsername()
            );

            return $this->redirect($this->generateUrl('nononsense_users_homepage'));
        }

        return $this->render('NononsenseUserBundle:Users:delete.html.twig', array(
            'deleteUser' => $form->createView(),
            'user' => $user
        ));
    }
    
    public function editAction($id, Request $request)
    {
        if (!$this->get('app.security')->permissionSeccion('usuarios_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $admin = true;

        //recall the user entity        
        $user = $this->getDoctrine()
                     ->getRepository('NononsenseUserBundle:Users')
                     ->find(array('id' => $id));
      
        //get the previous user rol for later use    
        $roles =  $user->getRoles();
        if (!empty($roles)){
            $rol = $roles[0]->getId();
        } else {    
            $rol = 2;//Editor by default
        }
        $form = $this->createForm(new FormUsers\UserType($admin), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            //get the old roles if any
            if (!empty($roles) && $admin){
                $oldRole = $this->getDoctrine()
                                 ->getRepository('NononsenseUserBundle:Roles')
                                 ->find($rol);
                $user->removeRole($oldRole);
            }
            //get the new role entity
            $roleId = $request->request->get('account');
            if ($admin) {
                $role = $this->getDoctrine()
                             ->getRepository('NononsenseUserBundle:Roles')
                             ->find($roleId);

                $user->addRole($role); 
            }
            if (!$user->getIsActive()) {
                $user->setLocked(new \DateTime());
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->get('utilities')->logger(
                'USER', 
                'El usuario '.$user->getUsername().' ha sido editado', 
                $this->getUser()->getUsername()
            );

            return $this->redirect($this->generateUrl('nononsense_user_profile', array('id' => $id)));
        }

        return $this->render('NononsenseUserBundle:Users:editData.html.twig', array(
            'createUser' => $form->createView(),
            'rol' => $rol,
            'admin' => $admin,
            'create' => false,
            'user' => $user
        ));
    }
    
    public function editImageAction($id, Request $request)
    {
        if (!$this->get('app.security')->permissionSeccion('usuarios_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        //recall the user entity
        $width = $this->container->getParameter('avatar_width');
        $height = $this->container->getParameter('avatar_height');
        $size = array('width' => $width, 'height' => $height);
        
        $user = $this->getDoctrine()
                     ->getRepository('NononsenseUserBundle:Users')
                     ->findOneBy(array('id' => $id));
        if (empty($user->getPhoto())) {
            $image = \Nononsense\UtilsBundle\Classes\Utils::generateColoredPNG($size);
            $user->setPhoto($image);
        } else {
            $image = $user->getPhoto();
        }
        $form = $this->createForm(new FormUsers\UserImageType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            //save the uploaded image as a  medium size image and a thumb
            $webPath = $this->container->getParameter('user_img_dir');
            $absolutePath = __DIR__ .'/../../../../web/' . $webPath;
            $imagePath = $absolutePath . 'user_' . $id . '.jpg';
            $img = \Nononsense\UtilsBundle\Classes\Utils::resize2JPG($user->getPhoto(), $width, $height, 90, $imagePath);
            $thumbPath = $absolutePath . 'thumb_' . $id . '.jpg';
            $thumb = \Nononsense\UtilsBundle\Classes\Utils::resize2JPG($user->getPhoto(), 140, 140, 100, $thumbPath);
            $em = $this->getDoctrine()->getManager();                
            $em->persist($user);
            $em->flush();
            return $this->redirect($this->generateUrl('nononsense_user_profile', array('id' => $id)));
        }

        return $this->render('NononsenseUserBundle:Users:editImage.html.twig', array(
            'createUser' => $form->createView(),
            'photo' => $image
        ));
    }
    
    public function resetPasswordAction($id, Request $request)
    {
        if (!$this->get('app.security')->permissionSeccion('usuarios_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }


        $admin = true;

        //recall the user entity        
        $user = $this->getDoctrine()
                     ->getRepository('NononsenseUserBundle:Users')
                     ->find(array('id' => $id));
      
        $salt = $user->getSalt();
        $pwd = $user->getPassword();
        $form = $this->createForm(new FormUsers\ResetPassType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            //check that the old password is correct if is not an admin
            if (!$admin) {
                $oldPWDerror = false;
                $oldPassword = $request->request->get('oldPassword');
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $checkPWD = $encoder->encodePassword($oldPassword, $salt);
                if ($checkPWD != $pwd) {
                    $oldPWDerror = $this->get('translator')->trans('The old password that you just introduced was incorrect, please, try again.');
                    //Notify the user
                    $this->get('session')->getFlashBag()->add(
                    'pwdError',
                    $oldPWDerror
                    );
                    return $this->redirect($this->generateUrl('nononsense_user_modify_password', array('id' => $id)));
                }
            }
            //insert new password
            // generate a new unique salt
            $generator = new SecureRandom();
            $user->setSalt(base64_encode($generator->nextBytes(10)));
            // encode password and set the new value instead the plain value
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
            $user->setPassword($password);
            $user->setRecoverPass(null);
            $em = $this->getDoctrine()->getManager();
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirect($this->generateUrl('nononsense_user_profile', array('id' => $id)));
        }

        return $this->render('NononsenseUserBundle:Users:resetPass.html.twig', array(
            'editPass' => $form->createView(),
            'admin' => $admin,
            'pwd' => false
        ));
    }
    
    public function keepAliveAction(Request $request)
    {
 
        $response = new Response();
        $response->setContent('OK');
        
        return  $response;
    }
    
    public function loadUsersAction(Request $request)
    {       
        // if does not enjoy the required permission kick him out
        if (!$this->get('app.security')->permissionSeccion('usuarios_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }
        echo 'No debería estar aquí';
        exit;
        
    }

    public function reportAction(Request $request){

        if (!$this->get('app.security')->permissionSeccion('usuarios_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters['page']         = (!$request->get('page')) ? 1 : $request->get('page');
        $filters['is_active']    = $request->get('is_active');
        $filters['name']         = $request->get('name');
        $filters['email']        = $request->get('email');
        $filters['phone']        = $request->get('phone');
        $filters['from']         = $request->get('from');
        $filters['until']        = $request->get('until');
        $filters['locked_from']  = $request->get('locked_from');
        $filters['locked_until'] = $request->get('locked_until');
        $filters['uri']          = ($request->query->all()) ? $_SERVER['REQUEST_URI'].'&csv=true' : $_SERVER['REQUEST_URI'].'?csv=true';
        $filters['pdf']          = ($request->query->all()) ? $_SERVER['REQUEST_URI'].'&pdf=true' : $_SERVER['REQUEST_URI'].'?pdf=true';

        if ($request->get('csv')) return $this->reportCsvAction($this->getDoctrine()->getRepository('NononsenseUserBundle:Users')->listBy($filters, 1000)['rows'], $filters);
        if ($request->get('pdf')) return $this->reportPDFAction($request, $this->getDoctrine()->getRepository('NononsenseUserBundle:Users')->listBy($filters, 1000)['rows'], $filters);

        $users     = $this->getDoctrine()->getRepository('NononsenseUserBundle:Users')->listBy($filters, 20);
        $params    = $request->query->all();           

        unset($params["page"]);
        $parameters = (!empty($params)) ? true : false;

        $pagination    = \Nononsense\UtilsBundle\Classes\Utils::paginador(20, $request, false, $users["count"], "/", $parameters);
       
        return $this->render('NononsenseUserBundle:Users:report.html.twig', ['users' => $users['rows'], 'filters' => $filters, 'pagination' => $pagination]);
    }

    public function reportCsvAction($data, $filters){

        if (!$this->get('app.security')->permissionSeccion('usuarios_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties();
        $phpExcelObject->setActiveSheetIndex(0)
        ->setCellValue('A1','Nombre')
        ->setCellValue('B1','Email')
        ->setCellValue('C1','Teléfono')
        ->setCellValue('D1','Fecha de alta')
        ->setCellValue('E1','Fecha de modificación')
        ->setCellValue('F1','Fecha de baja');

        $row = 2;
        foreach ($data as $key => $value) {
            $phpExcelObject->getActiveSheet()
             ->setCellValue('A'.$row, $value->getName())
             ->setCellValue('B'.$row, $value->getEmail())
             ->setCellValue('C'.$row, $value->getPhone())
             ->setCellValue('D'.$row, date_format($value->getCreated(), 'd-m-Y'))
             ->setCellValue('E'.$row, date_format($value->getModified(), 'd-m-Y'));

             if ($value->getLocked() !== null && $value->getLocked()) {
                
                $phpExcelObject->getActiveSheet()
                ->setCellValue('F'.$row, date_format($value->getLocked(), 'd-m-Y'));
             }
             
            $row++;
        }

        for($col = 'A'; $col <= 'F'; $col++) {
            $phpExcelObject->getActiveSheet()
                ->getColumnDimension($col)
                ->setAutoSize(true);
        }
        
        $phpExcelObject->getActiveSheet()->setTitle('User report'.date('d-m-y'));
        $phpExcelObject->setActiveSheetIndex(0);

        $writer     = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response   = $this->get('phpexcel')->createStreamedResponse($writer);

        $dispositionHeader = $response->headers->makeDisposition(
          ResponseHeaderBag::DISPOSITION_ATTACHMENT,
          'User-report-'.date('d-m-y').'.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    public function reportPDFAction($request, $data, $filters){

        $html='<html><body style="font-size:8px;width:100%">';
        $sintax_head_f="<b>Filtros:</b><br>";

        if($request->get("name")){
            $html.=$sintax_head_f."Nombre => ".$request->get("name")."<br>";
            $sintax_head_f="";
        }

        if($request->get("email")){
            $html.=$sintax_head_f."Email => ".$request->get("email")."<br>";
            $sintax_head_f="";
        }

        if($request->get("phone")){
            $html.=$sintax_head_f."Teléfono => ".$request->get("phone")."<br>";
            $sintax_head_f="";
        }

        if($request->get("from") || $request->get("until")){
            $html.=$sintax_head_f."Fecha de alta  => ".$request->get("from") . " / " . $request->get("until") . "<br>";
            $sintax_head_f="";
        }

        if($request->get("locked_from") || $request->get("locked_until")){
            $html.=$sintax_head_f."Fecha de baja  => ".$request->get("locked_from") . " / " . $request->get("locked_until") . "<br>";
            $sintax_head_f="";
        }

        if($request->get("is_active")){
            $html.=$sintax_head_f."Tipo => ".$request->get("is_active") . "<br>";
            $sintax_head_f="";
        }

        if (!$this->get('app.security')->permissionSeccion('usuarios_gestion')) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $html.='<br>
            <table autosize="1" style="overflow:wrap;width:95%">
            <tr style="font-size:8px;width:100%">
                <th style="font-size:8px;width:20%">Nombre y apellidos</th>
                <th style="font-size:8px;width:20%">Email</th>
                <th style="font-size:8px;width:10%">Teléfono</th>
                <th style="font-size:8px;width:20%">Fecha de alta</th>
                <th style="font-size:8px;width:20%">Última modificación</th> 
                <th style="font-size:8px;width:20%">Fecha de baja</th> 
            </tr>';

        foreach ($data as $key => $value) {
            $html.='<tr style="font-size:8px">
                        <td>'.$value->getName().'</td>
                        <td>'.$value->getEmail().'</td>
                        <td>'.$value->getPhone().'</td>
                        <td>'.date_format($value->getCreated(), 'd-m-Y').'</td>
                        <td>'.date_format($value->getModified(), 'd-m-Y').'</td>'
            ;
            if ($value->getLocked() !== null && $value->getLocked()) {
                $html.='<td>'.date_format($value->getLocked(), 'd-m-Y').'</td>';
            }
            $html.='</tr>';
        }

        $html.='</table></body></html>';

        return $this->get('utilities')->returnPDFResponseFromHTML($html, self::TITLE_PDF, self::FILENAME_PDF);
    }
}
