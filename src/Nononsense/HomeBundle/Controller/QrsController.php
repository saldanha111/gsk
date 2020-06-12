<?php

namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Nononsense\HomeBundle\Entity\Qrs;
use Nononsense\HomeBundle\Entity\QrsFields;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nononsense\HomeBundle\Form\Type as FormProveedor;

use Nononsense\UtilsBundle\Classes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Nononsense\UtilsBundle\Classes\Auxiliar;
use Nononsense\UtilsBundle\Classes\Utils;

use Endroid\QrCode\QrCode;

class QrsController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('qrs_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters=array();
        $filters2=array();

        if($request->get("page")){
            $filters["limit_from"]=$request->get("page")-1;
        }
        else{
            $filters["limit_from"]=0;
        }
        $filters["limit_many"]=15;


        if($request->get("id")){
            $filters["id"]=$request->get("id");
            $filters2["id"]=$request->get("id");
        }

        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(Qrs::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(Qrs::class)->count($filters2);


        $url=$this->container->get('router')->generate('nononsense_qr_list');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        return $this->render('NononsenseHomeBundle:Qrs:index.html.twig',$array_item);
    }

    public function editAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('qrs_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $qr = $em->getRepository('NononsenseHomeBundle:Qrs')->find($id);

        if(!$qr){
            $qr = new Qrs();
        }

        if($request->getMethod()=='POST'){
            try{
                $qr->setName($request->get("name"));

                $array_ids_fields_before = array();
                $fieldsJson = $request->get('fieldsJson');
                $array_fields = json_decode($fieldsJson, true);

                $array_ids_fields_before = array();
                $fieldsBefore = $qr->getFields();
                foreach ($fieldsBefore as $fieldBefore) {
                    $array_ids_fields_before[$fieldBefore->getId()] = $fieldBefore;
                }

                foreach ($array_fields as $field) {
                    $qrsFields = $em->getRepository('NononsenseHomeBundle:QrsFields')->find($field['id']);
                    if(!$qrsFields){
                        $qrsFields = new QrsFields();
                        $qrsFields->setQr($qr);
                    }
                    $qrsFields->setName($field['name']);
                    $qrsFields->setValue($field['value']);
                    $em->persist($qrsFields);
                    $array_ids_fields_before[$field['id']] = null;
                }

                //borro los fields sobrantes
                foreach ($array_ids_fields_before as $fieldBefore) {
                    if($fieldBefore){
                        $em->remove($fieldBefore);
                    }
                }



                $em->persist($qr);
                $em->flush();
                $this->get('session')->getFlashBag()->add('message',"El QR se ha guardado correctamente");
                return $this->redirect($this->generateUrl('nononsense_qr_list'));
            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar guardar los datos del QR: ".$e->getMessage());
            }
        }

        $array_item = array();
        $array_item['qr'] = $qr;

        $serializer = $this->get('serializer');
        $fieldsJson = $serializer->serialize(
            $qr->getFields(),
            'json', array('groups' => array('group1'))
        );

        $array_item['fieldsJson'] = $fieldsJson;
        $array_item['time'] = time();

        return $this->render('NononsenseHomeBundle:Qrs:qr.html.twig',$array_item);
    }

    public function deleteAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('qrs_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        try{
            $em = $this->getDoctrine()->getManager();
            $qr = $em->getRepository('NononsenseHomeBundle:Qrs')->find($id);

            if($qr){
                $em->remove($qr);
                $em->flush();
            }

            $this->get('session')->getFlashBag()->add('message',"El QR se ha borrado correctamente");
        }
        catch(\Exception $e){
            $this->get('session')->getFlashBag()->add('error',"Error al borrar el QR: ".$e->getMessage());
        }

        return $this->redirect($this->generateUrl('nononsense_qr_list'));
    }

    public function viewQrAction($id){
        
        $is_valid = $this->get('app.security')->permissionSeccion('qrs_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();    

        if($id){
            $qr = $this->getDoctrine()->getRepository('NononsenseHomeBundle:Qrs')->find($id);

            if($qr){
                $filename = self::generateQrImage($qr);

                $rootdir = $this->get('kernel')->getRootDir();
                $ruta_img_qr = $rootdir . "/files/qrs/";

                $content = file_get_contents($ruta_img_qr.$filename);

                $response = new Response();
                $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, 'image.png');
                $response->headers->set('Content-Disposition', $disposition);
                $response->headers->set('Content-Type', 'image/png');
                $response->setContent($content);
                return $response;
            }
        }

        echo "Error al generar el QR";
        exit();
    }

    private function generateQrImage($qr){
        $filename = $qr->getId().".png";
        $rootdir = $this->get('kernel')->getRootDir();
        $ruta_img_qr = $rootdir . "/files/qrs/";

        $fields = $qr->getFields();

        $array_fields = array();
        foreach ($fields as $field) {
           $array_fields[$field->getName()]=$field->getValue();
        }

        $qrArray = array('id'=>$qr->getId(), 'fields'=>$array_fields);
        $qrLabel = json_encode($qrArray);


        $qrCode = new QrCode();
        $qrCode
        ->setText($qrLabel)
        ->setSize(500)
        ->setPadding(5)
        ->setErrorCorrection('high')
        ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
        ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
        ->setLabel('')
        ->setLabelFontSize(14)
        ->setImageType(QrCode::IMAGE_TYPE_PNG)
        ;
         
        $qrCode->save($ruta_img_qr.$filename);

        return $filename;
    }

}