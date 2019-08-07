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
            $array_item["item"] = json_decode($serializer->serialize($item, 'json',array('groups' => array('detail_document'))),true);

            $baseSignatures = $this->getDoctrine()->getRepository('NononsenseHomeBundle:DocumentsSignatures')->findBy(array("document"=> $item));
            $array_item["baseSignatures"] = json_decode($serializer->serialize($baseSignatures, 'json',array('groups' => array('list_baseS'))),true);
        }
        else{

        }

        return $this->render('NononsenseHomeBundle:Contratos:document.html.twig',$array_item);
    }
}