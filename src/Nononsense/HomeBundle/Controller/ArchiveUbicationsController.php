<?php

namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Nononsense\HomeBundle\Entity\ArchiveLocations;
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

class ArchiveUbicationsController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('archive_admin');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters=$filters=array_filter($request->query->all());
        $filters2=$filters=array_filter($request->query->all());

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
        $array_item["items"] = $this->getDoctrine()->getRepository(ArchiveLocations::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(ArchiveLocations::class)->count($filters2);


        $url=$this->container->get('router')->generate('nononsense_archive_ubications_list');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        return $this->render('NononsenseHomeBundle:Archive:ubications.html.twig',$array_item);
    }

    public function editAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('archive_admin');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $qr = $em->getRepository('NononsenseHomeBundle:ArchiveLocations')->find($id);
        if(!$qr){
            $qr = new ArchiveLocations();
        }

        if($request->getMethod()=='POST'){
            try{
                $qr->setBuilding($request->get("building"));
                $qr->setShelf($request->get("shelf"));
                $qr->setPassage($request->get("passage"));
                $qr->setCabinet($request->get("cabinet"));
                $qr->setOthers($request->get("others"));

                $em->persist($qr);
                $em->flush();
                $this->get('session')->getFlashBag()->add('message',"La ubicación se ha guardado correctamente");
                return $this->redirect($this->generateUrl('nononsense_archive_ubications_list'));
            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar guardar los datos de la ubicación");
            }
        }

        $array_item = array();
        $array_item['qr'] = $qr;
        $array_item['time'] = time();

        return $this->render('NononsenseHomeBundle:Archive:ubication.html.twig',$array_item);
    }

    public function viewQrAction(Request $request, $id){
        
        $is_valid = $this->get('app.security')->permissionSeccion('archive_admin');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();    

        if($id){
            $qr = $this->getDoctrine()->getRepository('NononsenseHomeBundle:ArchiveLocations')->find($id);

            if($qr){
                
                $filename = self::generateQrImage($qr);

                $rootdir = $this->get('kernel')->getRootDir();
                $ruta_img_qr = $rootdir . "/files/archive-retention/qrs/";

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

    public function jsonQrAction($id){

        $em = $this->getDoctrine()->getManager();    

        if($id){
            $qr = $this->getDoctrine()->getRepository('NononsenseHomeBundle:ArchiveLocations')->find($id);

            if($qr){
                $serializer = $this->get('serializer');
                $json["location"] = json_decode($serializer->serialize($qr,'json', array('groups' => array('location'))),true);
                $json["type"] = "archive_location";


                $response = new Response(json_encode($json), 200);

                return $response;
            }
        }

        $response = new Response(json_encode(array("error" => "QR not found")), 404);

        return $response;
    }

    private function generateQrImage($qr){
        $filename = $qr->getId().".png";
        $rootdir = $this->get('kernel')->getRootDir();
        $ruta_img_qr = $rootdir . "/files/archive-retention/qrs/";
        if (!is_dir($ruta_img_qr)) {
            mkdir($ruta_img_qr, 0777, true);
        }

        
        $qrArray = $this->container->get('router')->generate('nononsense_archive_json_qr', array("id" => $qr->getId()),TRUE);
        $qrLabel = $qrArray;
        


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