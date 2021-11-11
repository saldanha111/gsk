<?php
namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
    public function reconciliacionDetailAction(Request $request, $id){

        $user = $this->container->get('security.context')->getToken()->getUser();
        $filters=Array();
        $types=array();

        $filters=array_filter($request->query->all());
        $filters2=array_filter($request->query->all());

        $users_actions=$this->get('utilities')->get_users_actions($user,1);
        $filters["users"]=$users_actions;
        $filters2["users"]=$users_actions;


        $array_item["suser"]["id"]=$user->getId();

        $filters["limit_from"]=0;
        $filters["limit_many"]=99999999999;

        $array_item["suser"]["id"]=$user->getId();
        $array_item["record"]=$this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $id));

        if($array_item["record"]->getFirstReconciliation()){
            $filters["recon_history"]=$array_item["record"]->getFirstReconciliation()->getId();
            $filters2["recon_history"]=$array_item["record"]->getFirstReconciliation()->getId();
        }
        else{
            $filters["recon_history"]=$array_item["record"]->getId();
            $filters2["recon_history"]=$array_item["record"]->getId();
        }

        
        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(CVRecords::class)->search("list",$filters);
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
            return $this->render('NononsenseHomeBundle:CV:reconciliacion_history.html.twig', $array_item);
        }
        else{
            //Exportamos a Excel

            if($request->get("export_excel")){
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                $phpExcelObject->getProperties();
                $phpExcelObject->setActiveSheetIndex(0)
                 ->setCellValue('A1', 'Id')
                 ->setCellValue('B1', 'Area')
                 ->setCellValue('C1', 'Nombre')
                 ->setCellValue('D1', 'Solicitante')
                 ->setCellValue('E1', 'Fecha solicitud')
                 ->setCellValue('F1', 'Estado')
                 ->setCellValue('G1', 'Ultima modificación');
            }

            if($request->get("export_pdf")){
                $html='<html><body style="font-size:8px;width:100%"><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%"><th style="font-size:8px;width:5%">Id</th><th style="font-size:8px;width:15%">Area</th><th style="font-size:8px;width:30%">Nombre</th><th style="font-size:8px;width:15%">Solicitante</th><th style="font-size:8px;width:10%">F. solicitud</th><th style="font-size:8px;width:15%">Estado</th><th style="font-size:8px;width:10%">Ult. modificación</th></tr>';
            }

            $i=2;
            foreach($array_item["items"] as $item){

                if($request->get("export_excel")){
                    $phpExcelObject->getActiveSheet()
                    ->setCellValue('A'.$i, $item["id"])
                    ->setCellValue('B'.$i, $item["area"])
                    ->setCellValue('C'.$i, $item["name"])
                    ->setCellValue('D'.$i, $item["creator"])
                    ->setCellValue('E'.$i, ($item["created"]) ? $item["created"]->format('d/m/Y H:i:s') : '')
                    ->setCellValue('F'.$i, $item["state"])
                    ->setCellValue('G'.$i, ($item["modified"]) ? $item["modified"]->format('d/m/Y H:i:s') : '');
                }

                if($request->get("export_pdf")){
                    $html.='<tr style="font-size:8px"><td>'.$item["id"].'</td><td>'.$item["area"].'</td><td>'.$item["name"].'</td><td>'.$item["creator"].'</td><td>'.(($item["created"]) ? $item["created"]->format('d/m/Y H:i:s') : '').'</td><td>'.$item["state"].'</td><td>'.(($item["modified"]) ? $item["modified"]->format('d/m/Y H:i:s') : '').'</td></tr>';
                }

                $i++;
            }

            if($request->get("export_excel")){
                $phpExcelObject->getActiveSheet()->setTitle('Registros reconciliados');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                  ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                  'list_records_reconciliations.xlsx'
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

    public function checkCodeUniqueAction(Request $request, $id)
    {
        if(!$request->get("code_unique")){
            $response = new Response(json_encode([
                'errors' => 'Invalid data',
            ]), 400);
            return $response;
        }

        $exist = $this->getDoctrine()->getRepository(CVRecords::class)->search("count",array("plantilla_id" => $id,"code_unique" => json_decode($request->get("code_unique"),TRUE)));

        if($exist==0){
            $json_content["ok"]=0;
        }
        else{
            $json_content["ok"]=1;
        }

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent(json_encode($json_content));

        return $response;
    }
}