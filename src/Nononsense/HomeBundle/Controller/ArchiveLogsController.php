<?php

namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Nononsense\HomeBundle\Entity\ArchiveSignatures;
use Nononsense\HomeBundle\Entity\ArchiveActions;
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

class ArchiveLogsController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('archive_admin');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }
        $user = $this->container->get('security.context')->getToken()->getUser();

        $filters=array();
        $filters2=array();

        $filters=array_filter($request->query->all());
        $filters2=array_filter($request->query->all());

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

        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(ArchiveSignatures::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(ArchiveSignatures::class)->count($filters2);
        $array_item["actions"] = $this->getDoctrine()->getRepository(ArchiveActions::class)->findAll();


        $url=$this->container->get('router')->generate('nononsense_archive_log');
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
            return $this->render('NononsenseHomeBundle:Archive:logs.html.twig',$array_item);
        }
        else{
            //Exportamos a Excel
            if($request->get("export_excel")){
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                $phpExcelObject->getProperties();
                $phpExcelObject->setActiveSheetIndex(0)
                 ->setCellValue('A1', "Audit trail archivo - ".$user->getUsername()." - ".$this->get('utilities')->sp_date(date("d/m/Y H:i:s")));
                $phpExcelObject->setActiveSheetIndex()
                 ->setCellValue('A2', 'Fecha')
                 ->setCellValue('B2', 'Tipo')
                 ->setCellValue('C2', 'ID')
                 ->setCellValue('D2', 'Acción')
                 ->setCellValue('E2', 'Usuario')
                 ->setCellValue('F2', 'Comentario');
            }

            if($request->get("export_pdf")){
                $html='<html><body style="font-size:8px;width:100%"><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%">
                        <th style="font-size:8px;width:15%">Fecha</th>
                        <th style="font-size:8px;width:10%">Tipo</th>
                        <th style="font-size:8px;width:5%">ID</th>
                        <th style="font-size:8px;width:10%">Acción</th>
                        <th style="font-size:8px;width:10%">Usuario</th>
                        <th style="font-size:8px;width:50%">Comentario</th>
                    </tr>';
            }

            $i=3;
            foreach($array_item["items"] as $item){
                if($item["preservation"]!=""){
                    $type="Preservation Notice";
                    $id=$item["preservation"];
                }
                else{
                    if($item["record"]!=""){
                        $type="Registro";
                        $id=$item["record"];
                    }
                    else{
                        if($item["type"]!=""){
                            $type="Tipo";
                            $id=$item["type"];
                        }
                        else{
                            if($item["az"]!=""){
                                $type="AZ";
                                $id=$item["az"];
                            }
                            else{
                                $type="Categoría";
                                $id=$item["category"];
                            }
                        }
                    }
                }

                if($request->get("export_excel")){
                    $phpExcelObject->getActiveSheet()
                    ->setCellValue('A'.$i, ($item["modified"]) ? $this->get('utilities')->sp_date($item["modified"]->format('d/m/Y H:i:s')) : '')
                    ->setCellValue('B'.$i, $type)
                    ->setCellValue('C'.$i, $id)
                    ->setCellValue('D'.$i, $item["action"])
                    ->setCellValue('E'.$i, $item["user"])
                    ->setCellValue('F'.$i, $item["description"]);

                    $dom = new \DOMDocument;

                    @$dom->loadHTML($item["changes"]);

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
                            ->setCellValue('G'.$i, isset($rowData[0]) ? $rowData[0] : '')
                            ->setCellValue('H'.$i, isset($rowData[1]) ? $rowData[1] : '')
                            ->setCellValue('I'.$i, isset($rowData[2]) ? $rowData[2] : '');
                                       
                        
                    }
                }

                if($request->get("export_pdf")){
                    $html.='<tr style="font-size:8px">
                        <td>'.(($item["modified"]) ? $this->get('utilities')->sp_date($item["modified"]->format('d/m/Y H:i:s')) : '').'</td>
                        <td>'.$type.'</td>
                        <td>'.$id.'</td>
                        <td>'.$item["action"].'</td>
                        <td>'.$item["user"].'</td>
                        <td>'.$item["description"].'</td>
                    </tr>';

                    if($item["changes"]!=""){
                        $html.='<tr style="font-size:8px"><td colspan="3"></td><td colspan="3">'.$item["changes"].'</td></tr>';
                    }

                }

                $i++;
            }

            if($request->get("export_excel")){
                $phpExcelObject->getActiveSheet()->setTitle('Audit trail archivo');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                  ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                  'audittrail_archive.xlsx'
                );
                $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'maxage=1');
                $response->headers->set('Content-Disposition', $dispositionHeader);

                return $response; 
            }

            if($request->get("export_pdf")){
                $html.='</table></body></html>';
                $this->get('utilities')->returnPDFResponseFromHTML($html,"Audit trail archivo");
            }
        }
    }

    public function downloadCertificationAction(Request $request, int $id){
        
        $is_valid = $this->get('app.security')->permissionSeccion('archive_admin');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $certification = $this->getDoctrine()->getRepository(ArchiveSignatures::class)->findOneBy(['id' => $id]);

        $response = new BinaryFileResponse($this->get('kernel')->getRootDir().$certification->getAttachment());

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($certification->getAttachment())
        );

        return $response;
    }
}