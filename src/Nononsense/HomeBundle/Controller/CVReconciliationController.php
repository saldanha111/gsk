<?php
namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasSteps;
use Symfony\Component\Filesystem\Filesystem;
use Nononsense\UtilsBundle\Classes;

use Nononsense\UserBundle\Entity\Users;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\HomeBundle\Entity\Areas;
use Nononsense\HomeBundle\Entity\TMTemplates;
use Nononsense\HomeBundle\Entity\CVRecords;
use Nononsense\HomeBundle\Entity\CVActions;
use Nononsense\HomeBundle\Entity\TMSecondWorkflow;
use Nononsense\HomeBundle\Entity\CVSignatures;
use Nononsense\HomeBundle\Entity\CVWorkflow;
use Nononsense\HomeBundle\Entity\CVStates;
use Nononsense\HomeBundle\Entity\CVRecordsHistory;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CVReconciliationController extends Controller
{
    //Detalle de la reconciliación donde se aprueba o se rechaza
    public function detailAction(Request $request, int $id)
    {   
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');
        $array=array();

        $array["item"] = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $id));

        if(!$array["item"]){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'El registro no existe'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        if($array["item"]->getRedirectSearch()){
            $array["item"]->setRedirectSearch(FALSE);
            $em->persist($array["item"]);
            $em->flush();
            $route = $this->container->get('router')->generate('nononsense_cv_search');
            return $this->redirect($route);
        }

        $array["signature"] = $this->getDoctrine()->getRepository(CVSignatures::class)->findOneBy(array("record" => $array["item"]),array("id" => "DESC"));

        if(!$array["signature"] || $array["signature"]->getSigned() || !$array["signature"]->getVersion()){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'El registro no se puede firmar'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        if($array["signature"]->getUser()!=$user){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'Usted no puede firmar esta evidencia'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $can_sign = $this->getDoctrine()->getRepository(CVRecords::class)->search("count",array("id" => $array["item"]->getId(),"pending_for_me" => 1,"user" => $user));

        if($can_sign==0){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No puede abrir esta plantilla debido al workflow definido'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        return $this->render('NononsenseHomeBundle:CV:sign.html.twig',$array);
    }

    //Listado de cumplimentaciones
    public function listAction(Request $request){

        $user = $this->container->get('security.context')->getToken()->getUser();
        $fll=false;
        foreach ($user->getGroups() as $groupMe) {
            $type = $groupMe->getGroup()->getTipo();
            if ($type == 'FLL') {
                $fll = true;
            }
        }

        $array_item["fll"]=$fll;

        $filters=Array();
        $filters2=Array();
        $types=array();

        $filters=array_filter($request->query->all());
        $filters2=array_filter($request->query->all());

        $filters["user"]=$user;
        $filters2["user"]=$user;

        $filters["fll"]=$fll;
        $filters2["fll"]=$fll;

        $array_item["suser"]["id"]=$user->getId();

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
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


        $array_item["suser"]["id"]=$user->getId();
        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(CVRecords::class)->search("list",$filters);
        $array_item["states"]=$this->getDoctrine()->getRepository(CVStates::class)->findAll();

        $array_item["count"] = $this->getDoctrine()->getRepository(CVRecords::class)->search("count",$filters2);
        $url=$this->container->get('router')->generate('nononsense_cv_search');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
            return $this->render('NononsenseHomeBundle:CV:search.html.twig',$array_item);
        }
        else{
            //Exportamos a Excel

            if($request->get("export_excel")){
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                $phpExcelObject->getProperties();
                $phpExcelObject->setActiveSheetIndex(0)
                 ->setCellValue('A1', 'Nº')
                 ->setCellValue('B1', 'Nombre')
                 ->setCellValue('C1', 'Iniciado por')
                 ->setCellValue('D1', 'Fecha inicio')
                 ->setCellValue('E1', 'Ultima modificación')
                 ->setCellValue('F1', 'Estado');
            }

            if($request->get("export_pdf")){
                $html='<html><body style="font-size:8px;width:100%"><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%"><th style="font-size:8px;width:6%">Nº</th><th style="font-size:8px;width:49%">Nombre</th><th style="font-size:8px;width:10%">Iniciado por</th><th style="font-size:8px;width:10%">F. inicio</th><th style="font-size:8px;width:10%">F. modific.</th><th style="font-size:8px;width:10%">Estado</th></tr>';
            }

            $i=2;
            foreach($array_item["items"] as $item){

                if($request->get("export_excel")){
                    $phpExcelObject->getActiveSheet()
                    ->setCellValue('A'.$i, $item["id"])
                    ->setCellValue('B'.$i, $item["name"])
                    ->setCellValue('C'.$i, $item["creator"])
                    ->setCellValue('D'.$i, ($item["created"]) ? $item["created"] : '')
                    ->setCellValue('E'.$i, ($item["modified"]) ? $item["modified"] : '')
                    ->setCellValue('F'.$i, $item["state"]);
                }

                if($request->get("export_pdf")){
                    $html.='<tr style="font-size:8px"><td>'.$item["id"].'</td><td>'.$item["name"].'</td><td>'.$item["creator"].'</td><td>'.(($item["created"]) ? $item["created"]->format('Y-m-d H:i:s') : '').'</td><td>'.(($item["modified"]) ? $item["modified"]->format('Y-m-d H:i:s') : '').'</td><td>'.$item["state"].'</td></tr>';
                }

                $i++;
            }

            if($request->get("export_excel")){
                $phpExcelObject->getActiveSheet()->setTitle('Listado de registros');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                  ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                  'list_records.xlsx'
                );
                $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'maxage=1');
                $response->headers->set('Content-Disposition', $dispositionHeader);

                return $response; 
            }

            if($request->get("export_pdf")){
                $html.='</table></body></html>';
                $this->get('utilities')->returnPDFResponseFromHTML($html);
            }
        }
    }

    public function checkCodeUniqueAction(Request $request)
    {
        if(!$request->get("code_unique")){
            $response = new Response(json_encode([
                'errors' => 'Invalid data',
            ]), 400);
            return $response;
        }

        $exist = $this->getDoctrine()->getRepository(CVRecords::class)->search("count",array("code_unique" => json_decode($request->get("code_unique"),TRUE)));

        if($exist==0){
            $response = new Response(json_encode([
                'errors' => 'Not Found',
            ]), 404);
            return $response;
        }

        $response = new Response();
        $response->setStatusCode(200);
        $json_content["ok"]=1;
        $response->setContent(json_encode($json_content));

        return $response;
    }
}