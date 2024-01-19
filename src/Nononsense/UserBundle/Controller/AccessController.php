<?php

namespace Nononsense\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nononsense\HomeBundle\Entity\Logs;
use Nononsense\HomeBundle\Entity\LogsTypes;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class AccessController extends Controller
{
    public function logAction(Request $request)
    {   
        if (!$this->isAllowed('usuarios_acceso')) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $em = $this->getDoctrine()->getManager();
        
        $filters['page'] = (!$request->get('page')) ? 1 : $request->get('page');
        $filters['username'] = $request->get('username');
        $filters['name'] = $request->get('name');
        $filters['description'] = $request->get('description');
        $filters['from'] = $request->get('from');
        $filters['until'] = $request->get('until');
        $filters['logType'] = $request->get('logType');
        $limit = 15;

        $logsTypes = $em->getRepository(LogsTypes::class)->findAll();
        $logs = $em->getRepository(Logs::class)->listBy($filters, $limit);

        $params = $request->query->all();
        unset($params["page"]);
        $parameters = !empty($params);

        if ($request->get('export_excel') == '1') {
            //$logs = $em->getRepository(Logs::class)->listBy($filters, $limit);
            return $this->exportCsv($logs);
        }

        if ($request->get('export_pdf') == '1') {
            //$logs = $em->getRepository(Logs::class)->listBy($filters, $limit);
            return $this->exportPDF($request, $logs);
        }

        return $this->render('NononsenseUserBundle:Users:logs.html.twig', 
            [
                'logs' => $logs['rows'],
                'pagination' => Utils::paginador($limit, $request, false, $logs["count"], "/", $parameters),
                'filters' => $filters,
                'logsTypes' => $logsTypes
            ]
        );
    }

    private function isAllowed($section)
    {
        if (!$this->get('app.security')->permissionSeccion($section)){

            $this->get('session')->getFlashBag()->add('error', 'No tiene permisos suficientes para acceder a esta sección.');

            return false;
        }

        return true;
    }

    private function exportCsv($logs)
    {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties();
        $phpExcelObject->setActiveSheetIndex(0)
           ->setCellValue('A1', 'ID')
           ->setCellValue('B1', 'TIPO')
           ->setCellValue('C1', 'USUARIO')
           ->setCellValue('D1', 'DESCRIPCIÓN')
           ->setCellValue('E1', 'IP')
           ->setCellValue('F1', 'FECHA');

        for ($i='A'; $i <= 'F'; $i++) { 
            $phpExcelObject->getActiveSheet()
                ->getColumnDimension($i)
                ->setAutoSize(true);
        }

        $i=2;
        foreach ($logs['rows'] as $key => $log) {
            $phpExcelObject->getActiveSheet()
                ->setCellValue('A'.$i, $log->getId())
                ->setCellValue('B'.$i, ($log->getType()) ? $log->getType()->getName() : 'Sin definir')
                ->setCellValue('C'.$i, ($log->getUser()) ? $log->getUser()->getUsername() : '')
                ->setCellValue('D'.$i, $log->getDescription())
                ->setCellValue('E'.$i, $log->getIp())
                ->setCellValue('F'.$i, $log->getDate()->format('Y-m-d H:i'));
            $i++;
        }

        $phpExcelObject->getActiveSheet()->setTitle('Registro de accesos');
        $phpExcelObject->setActiveSheetIndex(0);

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'Registro de accesos.xlsx'
        );

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response; 
    }

    private function exportPdf($request,$logs)
    {
        $html='<html><body style="font-size:8px;width:100%">';
        $sintax_head_f="<b>Filtros:</b><br>";

        if($request->get("name")){
            $html.=$sintax_head_f."Nombre => ".$request->get("name")."<br>";
            $sintax_head_f="";
        }

        if($request->get("description")){
            $html.=$sintax_head_f."Descripción => ".$request->get("description")."<br>";
            $sintax_head_f="";
        }

        if($request->get("logType")){
            switch($request->get("logType")){
                case "1": $hstate="Grupo";break;
                case "2": $hstate="Usuario";break;
            }
            $html.=$sintax_head_f."Tipo de registro => ".$hstate."<br>";
            $sintax_head_f="";
        }

        if($request->get("from") || $request->get("until")){
            $html.=$sintax_head_f."Fecha de alta  => ".$request->get("from") . " / " . $request->get("until") . "<br>";
            $sintax_head_f="";
        }

        $html.='<br><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%">
                        <th style="font-size:8px;width:10%">ID</th>
                        <th style="font-size:8px;width:10%">TIPO</th>
                        <th style="font-size:8px;width:15%">USUARIO</th>
                        <th style="font-size:8px;width:45%">DESCRIPCIÓN</th>
                        <th style="font-size:8px;width:10%">IP</th>
                        <th style="font-size:8px;width:10%">FECHA</th>
                    </tr>';
        foreach ($logs['rows'] as $key => $log) {
            $html.='<tr style="font-size:8px">
                <td>'.$log->getId().'</td>
                <td>'.(($log->getType()) ? $log->getType()->getName() : 'Sin definir').'</td>
                <td>'.(($log->getUser()) ? $log->getUser()->getUsername() : '').'</td>
                <td>'.$log->getDescription().'</td>
                <td>'.$log->getIp().'</td>
                <td>'.($log->getDate()->format('Y-m-d H:i')).'</td>
            </tr>';
        }

        $html.='</table></body></html>';
        $this->get('utilities')->returnPDFResponseFromHTML($html,"Log general");
    }

}
