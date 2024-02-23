<?php

namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Nononsense\HomeBundle\Entity\RetentionSignatures;
use Nononsense\HomeBundle\Entity\RetentionActions;
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

class RetentionLogsController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('retention_admin');
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
        $array_item["items"] = $this->getDoctrine()->getRepository(RetentionSignatures::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(RetentionSignatures::class)->count($filters2);
        $array_item["actions"] = $this->getDoctrine()->getRepository(RetentionActions::class)->findAll();


        $url=$this->container->get('router')->generate('nononsense_retention_log');
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
            return $this->render('NononsenseHomeBundle:Retention:logs.html.twig',$array_item);
        }
        else{
            //Exportamos a Excel
            if($request->get("export_excel")){
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                $phpExcelObject->getProperties();
                $phpExcelObject->setActiveSheetIndex(0)
                 ->setCellValue('A1', "Audit trail retenciones - ".$user->getUsername()." - ".$this->get('utilities')->sp_date(date("d/m/Y H:i:s")));
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
                if($item["template"]!=""){
                    $type="Plantilla";
                    $id=$item["template"];
                }
                else{
                    if($item["record"]!=""){
                        $type="Cumplimentación";
                        $id=$item["record"];
                    }
                    else{
                        $type="Categoría retención";
                        $id=$item["category"];
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
                }

                $i++;
            }

            if($request->get("export_excel")){
                $phpExcelObject->getActiveSheet()->setTitle('Audit trail retención');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                  ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                  'audittrail_retention.xlsx'
                );
                $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'maxage=1');
                $response->headers->set('Content-Disposition', $dispositionHeader);

                return $response; 
            }

            if($request->get("export_pdf")){
                $html.='</table></body></html>';
                $this->get('utilities')->returnPDFResponseFromHTML($html,"Audit trail retenciones");
            }
        }
    }
}