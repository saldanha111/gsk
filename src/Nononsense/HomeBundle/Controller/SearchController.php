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

class SearchController extends Controller
{
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
        $array_item["items"] = $this->getDoctrine()->getRepository(InstanciasSteps::class)->search("list",$filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(InstanciasSteps::class)->search("count",$filters2);

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

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
            if(!($request->query->get('destruction'))){
                if(!($request->query->get('record_contains'))){
                    return $this->render('NononsenseHomeBundle:Contratos:search.html.twig',$array_item);
                }
                else{
                    if(count($array_item["filters"])>5){
                        $array_item["filters"]["showTable"]=1;
                    }
                    return $this->render('NononsenseHomeBundle:Contratos:search_contain.html.twig',$array_item);
                }      
            }
            else{
                return $this->render('NononsenseHomeBundle:Contratos:destruction.html.twig',$array_item);
            }
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
                 ->setCellValue('F1', 'Lote')
                 ->setCellValue('G1', 'Num.equipo')
                 ->setCellValue('H1', 'Material')
                 ->setCellValue('I1', 'WO.SAP')
                 ->setCellValue('J1', 'Estado');
            }

            if($request->get("export_pdf")){
                $html='<html><body style="font-size:8px;width:100%"><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%"><th style="font-size:8px;width:6%">Nº</th><th style="font-size:8px;width:49%">Nombre</th><th style="font-size:8px;width:10%">Iniciado por</th><th style="font-size:8px;width:10%">F. inicio</th><th style="font-size:8px;width:10%">F. modific.</th><th style="font-size:8px;width:10%">Estado</th></tr>';
            }

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
                if($item["id_grid"]==0){
                    $name=$item["name"];
                }
                else{
                    $name=$item["name2"];
                }

                if($request->get("export_excel")){
                    $phpExcelObject->getActiveSheet()
                    ->setCellValue('A'.$i, $item["id_grid"])
                    ->setCellValue('B'.$i, $name)
                    ->setCellValue('C'.$i, $item["creator"])
                    ->setCellValue('D'.$i, ($item["created"]) ? $item["created"] : '')
                    ->setCellValue('E'.$i, ($item["modified"]) ? $item["modified"] : '')
                    ->setCellValue('F'.$i, $item["lote"])
                    ->setCellValue('G'.$i, $item["equipo"])
                    ->setCellValue('H'.$i, $item["material"])
                    ->setCellValue('I'.$i, $item["workordersap"])
                    ->setCellValue('J'.$i, $status);
                }

                if($request->get("export_pdf")){
                    $html.='<tr style="font-size:8px"><td>'.$item["id"].'</td><td>'.$name.'</td><td>'.$item["creator"].'</td><td>'.(($item["created"]) ? $item["created"]->format('Y-m-d H:i:s') : '').'</td><td>'.(($item["modified"]) ? $item["modified"]->format('Y-m-d H:i:s') : '').'</td><td>'.$status.'</td></tr>';
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
                $this->returnPDFResponseFromHTML($html);
            }
        }
    }

    private function returnPDFResponseFromHTML($html){
        //set_time_limit(30); uncomment this line according to your needs
        // If you are not in a controller, retrieve of some way the service container and then retrieve it
        //$pdf = $this->container->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //if you are in a controlller use :
        $pdf = $this->get("white_october.tcpdf")->create('horizontal', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAuthor('GSK');
        $pdf->SetTitle(('Registros GSK'));
        $pdf->SetSubject('Registros GSK');
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 9, '', true);
        //$pdf->SetMargins(20,20,40, true);
        $pdf->AddPage('L', 'A4');


        $filename = 'list_records';

        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        $pdf->Output($filename.".pdf",'I'); // This will output the PDF as a response directly
    }
}