<?php
/**
 * Nodalblock
 * User: Sergio
 * Date: 02/08/2019
 * Time: 07:07
 */
namespace Nononsense\HomeBundle\Controller;


use Nononsense\HomeBundle\Entity\Documents;
use Nononsense\HomeBundle\Entity\RecordsDocuments;
use Nononsense\HomeBundle\Entity\DocumentsSignatures;
use Nononsense\HomeBundle\Entity\RecordsSignatures;
use Nononsense\HomeBundle\Entity\Types;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\GroupBundle\Entity\Groups;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasSteps;
use Symfony\Component\Filesystem\Filesystem;
use Nononsense\UtilsBundle\Classes;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DocumentsController extends Controller
{
    public function listAction(Request $request)
    {
        $filters=Array();
        $filters2=Array();
        $types=array();

        if(!$request->get("export_excel")){
            if($request->get("page")){
                $filters["limit_from"]=$request->get("page")-1;
            }
            else{
                $filters["limit_from"]=0;
            }
            $filters["limit_many"]=15;
        }
        else{
            $filters["limit_from"]=0;
            $filters["limit_many"]=99999999999;
        }


        if($request->get("name")){
            $filters["name"]=$request->get("name");
            $filters2["name"]=$request->get("name");
        }

        if($request->get("type")){
            $filters["type"]=$request->get("type");
            $filters2["type"]=$request->get("type");
        }


        $array_item["filters"]=$filters;
        $array_item["types"] = $this->getDoctrine()->getRepository(Types::class)->findAll();
        $array_item["items"] = $this->getDoctrine()->getRepository(Documents::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(Documents::class)->count($filters2,$types);

        $url=$this->container->get('router')->generate('nononsense_documents');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);
        
        return $this->render('NononsenseHomeBundle:Contratos:documents.html.twig',$array_item);
    }

    public function editAction(Request $request, string $id)
    {
        $serializer = $this->get('serializer');

        $array_item["types"] = $this->getDoctrine()->getRepository(Types::class)->findAll();
        $array_item["users"] = $this->getDoctrine()->getRepository(Users::class)->findAll();
        $array_item["groups"] = $this->getDoctrine()->getRepository(Groups::class)->findAll();

        if($id!=0){
            $item = $this->getDoctrine()->getRepository(Documents::class)->findOneById($id);
            if(!$item){
                return $this->redirect($this->container->get('router')->generate('nononsense_documents'));
            }
            $array_item["item"] = json_decode($serializer->serialize($item, 'json',array('groups' => array('detail_document'))),true);

            $baseSignatures = $this->getDoctrine()->getRepository('NononsenseHomeBundle:DocumentsSignatures')->findBy(array("document"=> $item),array("number" => "ASC"));
            $array_item["baseSignatures"] = json_decode($serializer->serialize($baseSignatures, 'json',array('groups' => array('list_baseS'))),true);

            $records = $this->getDoctrine()->getRepository(RecordsDocuments::class)->findBy(array("document" => $item));
            if($records){
                $array_item["not_update"]=1;
            }

            $base_url=$this->getParameter('api_docoaro')."/documents/".$array_item["item"]["plantilla_id"];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER,array("Api-Key: ".$this->getParameter('api_key_docoaro')));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());    
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $raw_response = curl_exec($ch);
            $array_item["apiTemplate"] = json_decode($raw_response, true);
        }

        return $this->render('NononsenseHomeBundle:Contratos:document.html.twig',$array_item);
    }

    public function updateAction(Request $request, string $id)
    {   
        $em = $this->getDoctrine()->getManager();

        try {
            $not_update=0;
            if($id!=0){
                $document = $this->getDoctrine()->getRepository(Documents::class)->findOneById($id);

                $signatures = $em->getRepository(DocumentsSignatures::class)->findBy(["document"=>$document]);
                foreach ($signatures as $signature) {
                    $em->remove($signature);
                }

                if($request->files->get('template') && $request->get("template_name")){
                    $update_template=1;
                    $template_name=$request->get("template_name");
                }

                $base_url=$this->getParameter('api_docoaro')."/documents/".$document->getPlantillaId();
            }
            else{
                $document = new Documents();
                $user = $this->container->get('security.context')->getToken()->getUser();
                $document->setUserCreatedEntiy($user);

                if(!$request->files->get('template')){
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        "Es necesario adjuntar un documento paga subir la plantilla"
                    );
                    return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
                }
                else{
                    $update_template=1;
                    $template_name=$request->get("name");
                }

                $base_url=$this->getParameter('api_docoaro')."/documents";
                $update_template=1;
            }

            $records = $this->getDoctrine()->getRepository(RecordsDocuments::class)->findBy(array("document" => $document));
            if($records){
                $not_update=1;
            }

            if(!$not_update){
                $type = $this->getDoctrine()->getRepository(Types::class)->find($request->get("type"));
                $document->setType($type);

                if($update_template==1){
                    $fs = new Filesystem();
                    $file = $request->files->get('template');
                    $data_file = curl_file_create($file->getRealPath(), $file->getClientMimeType(), $file->getClientOriginalName());
                    $post = array('name' => $template_name,'file'=> $data_file);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $base_url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
                    curl_setopt($ch, CURLOPT_HTTPHEADER,array("Content-Type: multipart/form-data","Api-Key: ".$this->getParameter('api_key_docoaro')));
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);    
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $raw_response = curl_exec($ch);
                    $response = json_decode($raw_response, true);
                    
                    if(!$response["version"]){
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            'Error al subir la plantilla. '.$response["message"]
                        );
                        return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
                    }
                    $document->setPlantillaId($response["id"]);
                }
            }

            $document->setName($request->get("name"));
            $document->setDescription($request->get("description"));
            $document->setPosition(1);
            $document->setBlock(1);
            $document->setOptional(0);
            $document->setDependsOn(0);
            $document->setCreated(new \DateTime());
            $document->setModified(new \DateTime());

            if($request->get("is_active")){
                $document->setIsActive(1);
            }
            else{
                $document->setIsActive(0);
            }

            if($request->get("sign_creator")){
                $document->setSignCreator(1);
            }
            else{
                $document->setSignCreator(0);
            }

            if($request->get("attachment")){
                $document->setAttachment(1);
            }
            else{
                $document->setAttachment(0);
            }

            if($request->get("relationals")){
                $order=1;
                foreach($request->get("relationals") as $key => $relational){
                    $signature = new DocumentsSignatures();

                    if($request->get("types")[$key]=="1"){
                        $group = $this->getDoctrine()->getRepository(Groups::class)->find($relational);
                        $signature->setGroupEntiy($group);
                        $signature->setEmail($request->get("emails")[$key]);
                    }
                    else{
                        $user = $this->getDoctrine()->getRepository(Users::class)->find($relational);
                        $signature->setUserEntiy($user);
                    }

                    $signature->setDocument($document);
                    $signature->setCreated(new \DateTime());
                    $signature->setModified(new \DateTime());
                    $signature->setNumber($order);
                    $signature->setAttachment(0);
                    $em->persist($signature);

                    $order++;
                }
            }

            $em->persist($document);
            $em->flush();

        }catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error desconocido al intentar guardar los datos de la plantilla".$e->getMessage()
                );
            $route = $this->container->get('router')->generate('nononsense_documents_edit', array("id" => $id));
        
            return $this->redirect($route);
        }

        

        $route = $this->container->get('router')->generate('nononsense_documents');
        
        return $this->redirect($route);
    }
}