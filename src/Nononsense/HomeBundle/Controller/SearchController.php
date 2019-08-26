<?php
/**
 * Nodalblock
 * User: Sergio
 * Date: 09/08/2019
 * Time: 10:32
 */
namespace Nononsense\HomeBundle\Controller;


use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasWorkflows;
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

class SearchController extends Controller
{
    public function listAction(Request $request)
    {
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

        
        $array_item["suser"]["id"]=$user->getId();
        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(InstanciasWorkflows::class)->search("list",$filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(InstanciasWorkflows::class)->search("count",$filters2);

        $url=$this->container->get('router')->generate('nononsense_search');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        if(!$request->get("export_excel")){
            return $this->render('NononsenseHomeBundle:Contratos:search.html.twig',$array_item);
        }
        else{
            //Exportamos a Excel
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

           $phpExcelObject->getProperties();
           $phpExcelObject->setActiveSheetIndex(0)
               ->setCellValue('A1', 'Nº')
               ->setCellValue('B1', 'Nombre')
               ->setCellValue('C1', 'Iniciado por')
               ->setCellValue('D1', 'Fecha inicio')
               ->setCellValue('E1', 'Ultima modificación')
               ->setCellValue('F1', 'Lote')
               ->setCellValue('G1', 'Num.equipo')
               ->setCellValue('H1', 'Material')
               ->setCellValue('I1', 'WO.SAP')
               ->setCellValue('J1', 'Estado')
               ->setCellValue('K1', 'Reconciliado');

            $i=2;
            foreach($array_item["items"] as $item){
                switch($item["status"]){
                    case 0: $status="Iniciado";break;
                    case 1: $status="Esperando firma guardado parcial";break;
                    case 2: $status="Esperando firma envío";break;
                    case 3: $status="Esperando firma cancelación";break;
                    case 4: $status="En verificación";break;
                    case 5: $status="Pendiente cancelación en edición";break;
                    case 6: $status="Cancelado en edición";break;
                    case 7: $status="Esperando firma verificación total";break;
                    case 8: $status="Cancelado";break;
                    case 9: $status="Archivado";break;
                    case 10: $status="Reconciliado";break;
                    case 11: $status="Bloqueado";break;
                    case 12: $status="Esperando firma cancelación en verificación";break;
                    case 13: $status="Esperando firma devolución a edición";break;
                    case 14: $status="Pendiente de cancelación en verificación";break;
                    case 15: $status="Esperando firma verificación parcial";break;
                    default: $status="Desconocido";
                }

                $phpExcelObject->getActiveSheet()
                ->setCellValue('A'.$i, $item["id"])
               ->setCellValue('B'.$i, $item["name"])
               ->setCellValue('C'.$i, $item["creator"])
               ->setCellValue('D'.$i, ($item["created"]) ? $item["created"] : '')
               ->setCellValue('E'.$i, ($item["modified"]) ? $item["modified"] : '')
               ->setCellValue('F'.$i, $item["lote"])
               ->setCellValue('G'.$i, $item["equipo"])
               ->setCellValue('H'.$i, $item["material"])
               ->setCellValue('I'.$i, $item["workordersap"])
               ->setCellValue('J'.$i, $status)
               ->setCellValue('K'.$i, $item["id_reconciliado"])
               ;
                $i++;
            }
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
        
        
    }
}