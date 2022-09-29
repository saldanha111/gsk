<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Exception;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\HomeBundle\Entity\RCSignatures;
use Nononsense\HomeBundle\Entity\RCStates;
use Nononsense\HomeBundle\Entity\RCTypes;
use Nononsense\HomeBundle\Entity\RetentionCategories;
use Nononsense\HomeBundle\Entity\RetentionCategoriesRepository;
use Nononsense\HomeBundle\Entity\CVRecords;
use Nononsense\HomeBundle\Entity\TMTemplates;
use Nononsense\HomeBundle\Entity\Areas;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class RetentionRecordsController extends Controller
{
    public function listAction(Request $request){

        $user = $this->container->get('security.context')->getToken()->getUser();

        $filters=Array();
        $filters2=Array();
        $types=array();

        $filters=array_filter($request->query->all());
        $filters2=array_filter($request->query->all());

        $retention_type = $this->getDoctrine()->getRepository(RCTypes::class)->findOneBy(array("id" => $filters["retention_type"]));
        $desc_pdf="Listado de retención - ".$retention_type->getName();

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
        if($request->get("retention_type") &&  $request->get("retention_type")=="1"){
            $array_item["items"] = $this->getDoctrine()->getRepository(TMTemplates::class)->list("list",$filters);
            $array_item["count"] = $this->getDoctrine()->getRepository(TMTemplates::class)->list("count",$filters2);
        }
        else{
            $array_item["items"] = $this->getDoctrine()->getRepository(CVRecords::class)->search("list",$filters);
            $array_item["count"] = $this->getDoctrine()->getRepository(CVRecords::class)->search("count",$filters2);
        }
        
        $array_item["states"]= $this->getDoctrine()->getRepository(RCStates::class)->findBy(array("type" => $retention_type));
        $array_item["areas"] = $this->getDoctrine()->getRepository(Areas::class)->findBy(array(),array("name" => "ASC"));
        $array_item["types"] = $this->getDoctrine()->getRepository(RCTypes::class)->findAll();
        $array_item["agents"] = $this->getDoctrine()->getRepository(Users::class)->listUsersByPermission("retention_agent");
        

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
            return $this->render('NononsenseHomeBundle:Retention:list_records.html.twig',$array_item);
        }
        else{
            //Exportamos a Excel

            if($request->get("export_excel")){
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                $phpExcelObject->getProperties();
                $phpExcelObject->setActiveSheetIndex(0)
                 ->setCellValue('A1', $desc_pdf." - ".$user->getUsername()." - ".$this->get('utilities')->sp_date(date("d/m/Y H:i:s")));
                $phpExcelObject->setActiveSheetIndex()
                 ->setCellValue('A2', 'ID')
                 ->setCellValue('B2', 'Fecha de destrucción')
                 ->setCellValue('C2', 'Categoría retención')
                 ->setCellValue('D2', 'Título')
                 ->setCellValue('E2', 'Código')
                 ->setCellValue('F2', 'Edición')
                 ->setCellValue('G2', 'Área')
                 ->setCellValue('H2', 'Estado')
                 ->setCellValue('I2', 'Representante')
                 ->setCellValue('J2', 'Fecha retención');
            }

            if($request->get("export_pdf")){
                $html='<html><body style="font-size:8px;width:100%"><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%">
                        <th style="font-size:8px;width:5%">Nº</th>
                        <th>Fecha de destrucción</th>
                        <th>Categoría retención</th>
                        <th>Título</th>
                        <th>Código</th>
                        <th>Edición</th>
                        <th>Área</th>
                        <th>Estado</th>
                        <th>Representante</th>
                        <th>Fecha retención</th>
                    </tr>';
            }

            $i=3;
            foreach($array_item["items"] as $item){

                if($request->get("export_excel")){
                    $phpExcelObject->getActiveSheet()
                    ->setCellValue('A'.$i, $item["id"])
                    ->setCellValue('B'.$i, ($item["DestructionDate"]) ? $this->get('utilities')->sp_date($item["DestructionDate"]) : '')
                    ->setCellValue('C'.$i, $item["mostRestrictiveCategory"])
                    ->setCellValue('D'.$i, $item["name"])
                    ->setCellValue('E'.$i, $item["number"])
                    ->setCellValue('F'.$i, $item["numEdition"])
                    ->setCellValue('G'.$i, $item["area"])
                    ->setCellValue('H'.$i, $item["state"])
                    ->setCellValue('I'.$i, ($item["retentionDate"]) ? $this->get('utilities')->sp_date($item["retentionDate"]) : '')
                    ->setCellValue('J'.$i, '');
                }

                if($request->get("export_pdf")){
                    $html.='<tr style="font-size:8px">
                        <td>'.$item["id"].'</td>
                        <td>'.(($item["DestructionDate"]) ? $item["DestructionDate"] : '').'</td>
                        <td>'.$item["mostRestrictiveCategory"].'</td>
                        <td>'.$item["name"].'</td>
                        <td>'.$item["number"].'</td>
                        <td>'.$item["numEdition"].'</td>
                        <td>'.$item["area"].'</td>
                        <td>'.$item["state"].'</td>
                        <td>'.(($item["retentionDate"]) ? $item["retentionDate"] : '').'</td>
                        <td></td>
                    </tr>';
                }

                $i++;
            }

            if($request->get("export_excel")){
                $phpExcelObject->getActiveSheet()->setTitle('Listado de retención');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                  ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                  'list_retentions.xlsx'
                );
                $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'maxage=1');
                $response->headers->set('Content-Disposition', $dispositionHeader);

                return $response; 
            }

            if($request->get("export_pdf")){
                $html.='</table></body></html>';
                $this->get('utilities')->returnPDFResponseFromHTML($html,$desc_pdf);
            }
        }
    }
}
