<?php

namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Nononsense\HomeBundle\Entity\ArchiveRecords;
use Nononsense\HomeBundle\Entity\ArchiveStates;
use Nononsense\HomeBundle\Entity\ArchiveUseStates;
use Nononsense\HomeBundle\Entity\ArchiveTypes;
use Nononsense\HomeBundle\Entity\ArchiveLocations;
use Nononsense\HomeBundle\Entity\Areas;
use Nononsense\HomeBundle\Entity\ArchiveCategories;
use Nononsense\HomeBundle\Entity\ArchivePreservations;
use Nononsense\HomeBundle\Entity\ArchiveAZ;
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

class ArchiveRecordsController extends Controller
{
    public function listAction(Request $request)
    {
        $filters=array();
        $filters2=array();

        $agent = $this->get('app.security')->permissionSeccion('archive_agent');
        if(!$agent){
            $request->attributes->set("retentionAction", null);
        }

        $filters=array_filter($request->query->all());
        $filters2=array_filter($request->query->all());

        $user = $this->container->get('security.context')->getToken()->getUser();

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

        if($request->get("retentionAction") || $request->get("masive_edition")){
            $filters["areas"]=$this->get('app.security')->getAreas('archive_agent');
            $filters2["areas"]=$this->get('app.security')->getAreas('archive_agent');
        }

        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(ArchiveRecords::class)->list("list",$filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(ArchiveRecords::class)->list("count",$filters2);
        $array_item["states"] = $this->getDoctrine()->getRepository(ArchiveStates::class)->findAll();
        $array_item["useStates"] = $this->getDoctrine()->getRepository(ArchiveUseStates::class)->findAll();
        $array_item["types"] = $this->getDoctrine()->getRepository(ArchiveTypes::class)->findAll();
        $array_item["areas"] = $this->getDoctrine()->getRepository(Areas::class)->findAll();
        $array_item["categories"] = $this->getDoctrine()->getRepository(ArchiveCategories::class)->findAll();
        $array_item["preservations"] = $this->getDoctrine()->getRepository(ArchivePreservations::class)->findBy(array("active"=>TRUE));
        $areas=$this->get('app.security')->getAreas('archive_agent');
        $array_item["myAreas"]=$areas;
        foreach($areas as $area){
            $array_item["agentareas"][]=$area->getId();
        }



        $url=$this->container->get('router')->generate('nononsense_archive_records');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);

        $array_item["agent"] = $this->get('app.security')->permissionSeccion('archive_agent');

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
            return $this->render('NononsenseHomeBundle:Archive:records.html.twig',$array_item);
        }
        else{
            //Exportamos a Excel
            if($request->get("export_excel")){
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                $phpExcelObject->getProperties();
                $phpExcelObject->setActiveSheetIndex(0)
                 ->setCellValue('A1', "Consulta de registros de archivo - ".$user->getUsername()." - ".$this->get('utilities')->sp_date(date("d/m/Y H:i:s")));
                $phpExcelObject->setActiveSheetIndex()
                 ->setCellValue('A2', 'ID')
                 ->setCellValue('B2', 'Identificador')
                 ->setCellValue('C2', 'Título')
                 ->setCellValue('D2', 'Edición')
                 ->setCellValue('E2', 'Área custodio')
                 ->setCellValue('F2', 'Área')
                 ->setCellValue('G2', 'Tipo de documento')
                 ->setCellValue('H2', 'Estado')
                 ->setCellValue('I2', 'Disponibilidad')
                 ->setCellValue('J2', 'Categoría retención')
                 ->setCellValue('K2', 'Inicio retención')
                 ->setCellValue('L2', 'Fecha destrucción')
                 ->setCellValue('M2', 'Preservation notice');
            }

            if($request->get("export_pdf")){
                $html='<html><body style="font-size:8px;width:100%"><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%">
                        <th style="font-size:8px;width:5%">ID</th>
                        <th style="font-size:8px;width:5%">Identificador</th>
                        <th style="font-size:8px;width:9%">Título</th>
                        <th style="font-size:8px;width:5%">Edición</th>
                        <th style="font-size:8px;width:9%">Área custodio</th>
                        <th style="font-size:8px;width:8%">Área</th>
                        <th style="font-size:8px;width:9%">Tipo de documento</th>
                        <th style="font-size:8px;width:9%">Estado</th>
                        <th style="font-size:8px;width:9%">Disponibilidad</th>
                        <th style="font-size:8px;width:9%">Categoría</th>
                        <th style="font-size:8px;width:9%">Inicio retención</th>
                        <th style="font-size:8px;width:9%">Fecha destrucción</th>
                        <th style="font-size:8px;width:5%">Preserv. notice</th>
                    </tr>';
            }

            $i=3;
            foreach($array_item["items"] as $item){

                if($request->get("export_excel")){
                    $phpExcelObject->getActiveSheet()
                    ->setCellValue('A'.$i, $item["id"])
                    ->setCellValue('B'.$i, $item["uniqueNumber"])
                    ->setCellValue('C'.$i, $item["title"])
                    ->setCellValue('D'.$i, $item["edition"])
                    ->setCellValue('E'.$i, $item["area"])
                    ->setCellValue('F'.$i, $item["areaInfo"])
                    ->setCellValue('G'.$i, $item["type"])
                    ->setCellValue('H'.$i, $item["state"])
                    ->setCellValue('I'.$i, $item["useState"])
                    ->setCellValue('J'.$i, $item["category"])
                    ->setCellValue('K'.$i, ($item["initRetention"]) ? $this->get('utilities')->sp_date($item["initRetention"]->format('d/m/Y')) : '')
                    ->setCellValue('K'.$i, ($item["destructionDate"]) ? $this->get('utilities')->sp_date(date('d/m/Y', strtotime($item["destructionDate"]))) : '')
                    ->setCellValue('L'.$i, $item["preservation"]);
                }

                if($request->get("export_pdf")){
                    $html.='<tr style="font-size:8px">
                        <td>'.$item["id"].'</td>
                        <td>'.$item["uniqueNumber"].'</td>
                        <td>'.$item["title"].'</td>
                        <td>'.$item["edition"].'</td>
                        <td>'.$item["area"].'</td>
                        <td>'.$item["areaInfo"].'</td>
                        <td>'.$item["type"].'</td>
                        <td>'.$item["state"].'</td>
                        <td>'.$item["useState"].'</td>
                        <td>'.$item["category"].'</td>
                        <td>'.(($item["initRetention"]) ? $this->get('utilities')->sp_date($item["initRetention"]->format('d/m/Y')) : '').'</td>
                        <td>'.(($item["destructionDate"]) ? $this->get('utilities')->sp_date(date('d/m/Y', strtotime($item["destructionDate"]))) : '').'</td>
                        <td>'.$item["preservation"].'</td>
                    </tr>';
                }

                $i++;
            }

            if($request->get("export_excel")){
                $phpExcelObject->getActiveSheet()->setTitle('Registros archivo');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                  ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                  'records_archive.xlsx'
                );
                $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'maxage=1');
                $response->headers->set('Content-Disposition', $dispositionHeader);

                return $response; 
            }

            if($request->get("export_pdf")){
                $html.='</table></body></html>';
                $this->get('utilities')->returnPDFResponseFromHTML($html,"Registros archivo");
            }
        }
    }

    public function editAction(Request $request, $id)
    {
        $agent = $this->get('app.security')->permissionSeccion('archive_agent');



        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $record = $em->getRepository(ArchiveRecords::class)->findOneBy(['id' => $id]);
        $areas = $em->getRepository(Areas::class)->findBy(array("isActive"=>TRUE));
        $myAreas=$this->get('app.security')->getAreas('archive_agent');
        $types = $em->getRepository(ArchiveTypes::class)->findBy(array("active"=>TRUE));
        $states = $em->getRepository(ArchiveStates::class)->findAll();
        $categories = $em->getRepository(ArchiveCategories::class)->findBy(array("active"=>TRUE),array("retentionDays" => "DESC"));
        $preservations = $em->getRepository(ArchivePreservations::class)->findBy(array("active"=>TRUE));

        if (!$record) {
            $record = new ArchiveRecords();
            $record->setCreated(new \DateTime());
            $stateUse = $em->getRepository(ArchiveUseStates::class)->findOneBy(['id' => 1]);
            $record->setUseState($stateUse);
            $record->setCreator($user);
        }
        else{
            if(!in_array($record->getArea(),$myAreas)){
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'No tiene permisos para realizar esta acción'
                );
                return $this->redirect($this->generateUrl('nononsense_home_homepage'));
            }
        }

        if ($request->getMethod() == 'POST' && $this->saveData($request, $record)) {
            return $this->redirect($this->generateUrl('nononsense_archive_records'));
        }

        $data = [
            'record' => $record,
            'used' => false,
            'areas' => $areas,
            'myAreas' => $myAreas,
            'types' => $types,
            'states' => $states,
            'categories' => $categories,
            'preservations' => $preservations,
            'agent' => $agent
        ];

        return $this->render('NononsenseHomeBundle:Archive:record_edit.html.twig', $data);
    }

    public function updateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $is_valid = $this->get('app.security')->permissionSeccion('archive_agent');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        if(!$request->get("password")){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'La firma es incorrecta'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        
        $types=array();

        $filters=array_filter($request->query->all());
        $filters2=array_filter($request->query->all());

        if($request->get("retentions")){
            $filters["retentions"]=$request->get("retentions");
            $filters2["retentions"]=$request->get("retentions");
        }


        $items = $this->getDoctrine()->getRepository(ArchiveRecords::class)->list("list",$filters);
        $count = $this->getDoctrine()->getRepository(ArchiveRecords::class)->list("count",$filters2);

        $ids=array();
        foreach($items as $item){
            $ids[]=intval($item["id"]);
        }
        
        $sentence="La acción de actualización de archivos ha finalizado satisfactoriamente";
        $records=$this->getDoctrine()->getRepository(ArchiveRecords::class)->findBy(array("id" => $ids));
        switch($request->get("action")){
            case "1":
                
                foreach($records as $record){
                    $record->setRetentionRevision(TRUE);
                    $this->get('utilities')->saveLogArchive($this->getUser(),6,$request->get('comment'),"record",$record->getId());
                    $em->persist($record);
                }
                break;
            case "2":
                $file=NULL;
                if($request->files->get('certification')){
                    $file = $this->uploadFile($request);
                }
                foreach($records as $record){
                    $record->setRemovedAt(new \DateTime());
                    $this->get('utilities')->saveLogArchive($this->getUser(),7,$request->get('comment'),"record",$record->getId(),$file);
                    $em->persist($record);
                }
                $sentence="Los registros han sido destruidos satisfactoriamente";
                break;
            case "3":
                foreach($records as $record){
                    if($record->getUseState()->getId()!=1){
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            'Algunos de los registros seleccionados se encuentran actualmente prestados'
                        );
                        $route = $this->container->get('router')->generate('nononsense_home_homepage');
                        return $this->redirect($route);
                    }
                    $sentence="La solicitud de registro ha sido tramitada satisfactoriamente";
                    $this->get('utilities')->saveLogArchive($this->getUser(),8,$request->get('comment'),"record",$record->getId());
                    $em->persist($record);
                }
                break;
            case "4":
                foreach($records as $record){
                    if($request->get("retention_date")){
                        $retentionDate = new \DateTime($request->get("retention_date"));
                        $record->setInitRetention($retentionDate);
                    }
                    if($request->get("state")){
                        $state = $em->getRepository(ArchiveStates::class)->findOneBy(['id' => $request->get('state')]);
                        $record->setState($state);
                    }

                    if($request->get("area")){
                        $area = $em->getRepository(Areas::class)->findOneBy(['id' => $request->get('area')]);
                        $record->setArea($area);
                    }

                    if($request->get("area_info")){
                        $areaInfo = $em->getRepository(Areas::class)->findOneBy(['id' => $request->get('area_info')]);
                        $record->setAreaInfo($areaInfo);
                    }

                    $record->setModified(new \DateTime());

                    if($request->get('categories')){
                        if($record->getCategories()){
                            $record->getCategories()->clear();
                        }
                        foreach($request->get('categories') as $category){
                            $category = $em->getRepository(ArchiveCategories::class)->findOneBy(['id' => $category]);
                            $record->addCategory($category);
                        }
                    }

                    if($request->get('preservations')){
                        if($record->getPreservations()){
                            $record->getPreservations()->clear();
                        }
                        foreach($request->get('preservations') as $preservation){
                            $preservation = $em->getRepository(ArchivePreservations::class)->findOneBy(['id' => $preservation]);
                            $record->addPreservation($preservation); 
                        }
                    }
                    $sentence="La edición masiva se ha ejecutado satisfactoriamente";
                    $this->get('utilities')->saveLogArchive($this->getUser(),2,$request->get('comment'),"record",$record->getId());
                    $em->persist($record);
                }
                break;
        }
        
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', $sentence);

        return $this->redirect($this->generateUrl('nononsense_archive_records'));
    }

    public function uploadAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $is_valid = $this->get('app.security')->permissionSeccion('archive_agent');
        if (!$is_valid) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'No tiene permisos para realizar esta acción'
            );
            return $this->redirect($this->generateUrl('nononsense_archive_records'));
        }


        if ($request->getMethod() == 'POST') {
            $file = $request->files->get('excel');
            
            if ($file) {
                $em->getConnection()->beginTransaction();
                try {
                    $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject($file);

                    $sheet = $phpExcelObject->getActiveSheet();
                    $allRows = $sheet->toArray();
                    $rows = array_filter($allRows, function($row) {
                        // Esta función retorna true si al menos una celda en $row tiene contenido
                        return count(array_filter($row)) > 0;
                    });


                    // Suponiendo que las columnas obligatorias son 'Nombre', 'Edad', y 'Correo'
                    $requiredColumns = ['Document Number', 'Version', 'Document Name', 'Type', 'Document Status', 'Global Retention Schedule (GRS)','Area'];
                    $actualColumns = $rows[0];  // Asumimos que la primera fila tiene los nombres de las columnas
                    
                    $required=array_diff($requiredColumns, $actualColumns);
                    if (!empty($required)) {
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            'No se encuentra las siguientes columnas: '.implode(",", $required)
                        );
                        return $this->redirect($this->generateUrl('nononsense_archive_records'));
                    }

                    $columnNames = $rows[0];
                    $array_records=array();
                    foreach ($rows as $rowNumber => $row) {
                        if ($rowNumber == 0) { 
                            continue;
                        }

                        $record = new ArchiveRecords();
                        $record->setCreated(new \DateTime());
                        $stateUse = $em->getRepository(ArchiveUseStates::class)->findOneBy(['id' => 1]);
                        $record->setUseState($stateUse);
                        $record->setCreator($user);

                        foreach ($columnNames as $columnIndex => $columnName) {
                            $value = $row[$columnIndex];
                            switch($columnName){
                                case "Document Number":
                                    $searchRecord = $em->getRepository(ArchiveRecords::class)->findOneBy(['uniqueNumber' => $value]);
                                    if($searchRecord){
                                        $this->get('session')->getFlashBag()->add(
                                            'error',
                                            'El siguiente document number ya está en uso: '.$value
                                        );
                                        return $this->redirect($this->generateUrl('nononsense_archive_records'));
                                    }
                                    $record->setUniqueNumber($value);
                                    break;
                                case "Version":
                                    $record->setEdition($value);
                                    break;
                                case "Document Name":
                                    $record->setTitle($value);
                                    break;
                                case "Area":
                                    $area = $em->getRepository(Areas::class)->findOneBy(['name' => $value]);
                                    if(!$area){
                                        $this->get('session')->getFlashBag()->add(
                                            'error',
                                            'No se encuentra el area del registro: '.$value
                                        );
                                        return $this->redirect($this->generateUrl('nononsense_archive_records'));
                                    }
                                    $record->setArea($area);
                                    break;
                                case "Type":
                                    $type = $em->getRepository(ArchiveTypes::class)->findOneBy(['name' => $value]);
                                    if(!$type){
                                        $this->get('session')->getFlashBag()->add(
                                            'error',
                                            'No se encuentra el tipo de registro: '.$value
                                        );
                                        return $this->redirect($this->generateUrl('nononsense_archive_records'));
                                    }
                                    $record->setType($type);
                                    break;
                                case "Document Status":
                                    break;
                                case "Legal Preservation Name":
                                    if($value){
                                        $preservation = $em->getRepository(ArchivePreservations::class)->findOneBy(['name' => $value]);
                                        if(!$preservation){
                                            $this->get('session')->getFlashBag()->add(
                                                'error',
                                                'No se encuentra la preservation notice '.$value
                                            );
                                            return $this->redirect($this->generateUrl('nononsense_archive_records'));
                                        }
                                        $record->addPreservation($preservation); 
                                    }
                                    break;
                                case "Global Retention Schedule (GRS)":
                                    if($value){
                                        $category = $em->getRepository(ArchiveCategories::class)->findOneBy(['name' => $value]);
                                        if(!$category){
                                            $this->get('session')->getFlashBag()->add(
                                                'error',
                                                'No se encuentra la categoria de retención '.$value
                                            );
                                            return $this->redirect($this->generateUrl('nononsense_archive_records'));
                                        }
                                        $record->addCategory($category);
                                    }
                                    break;
                                case "AZ":
                                    if($value){
                                        $az = $em->getRepository(ArchiveAZ::class)->findOneBy(['code' => $value]);
                                        if(!$az){
                                            $this->get('session')->getFlashBag()->add(
                                                'error',
                                                'No se encuentra el AZ del registro: '.$value
                                            );
                                            return $this->redirect($this->generateUrl('nononsense_archive_records'));
                                        }
                                        $record->setAZ($az);
                                    }
                                    else{
                                        $docNumberColumnIndex = array_search('Document Number', $columnNames);
                                        $cell = $sheet->getCellByColumnAndRow($docNumberColumnIndex, $rowNumber+1);
                                        $link = $cell->getHyperlink()->getUrl();
                                        if($link){
                                            $record->setLink($link);
                                        }
                                        else{
                                            $this->get('session')->getFlashBag()->add(
                                                'error',
                                                'No se encuentra el Link para este archivo digital: '.$cell->getValue()
                                            );
                                            return $this->redirect($this->generateUrl('nononsense_archive_records'));
                                        }
                                    }
                                    break;
                            }

                        }

                        $record->setModified(new \DateTime());
                        


                        $em->persist($record);
                        $em->flush();
                        $this->get('utilities')->saveLogArchive($this->getUser(),12,$request->get("comment"),"record",$record->getId());
                    }

                } catch (\Exception $e) {
                    return new Response('Error al leer el archivo Excel: ' . $e->getMessage(). " - Line: ".$e->getLine(). " - File:".$e->getFile());
                }
            }
        }
        $em->getConnection()->commit();
        $this->get('session')->getFlashBag()->add('success', "La importación se ha realizado satisfactoriamente. Se han importado ".(count($rows)-1)." registros");

        return $this->redirect($this->generateUrl('nononsense_archive_records'));
    }

    public function checkUniqueAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$request->get("code")){
            $response = new Response(json_encode(array("check" => false)), 400);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $currentRecord=null;
        if($id){
            $currentRecord = $em->getRepository(ArchiveRecords::class)->findOneBy(['id' => $id]);
        }

        $searchRecord = $em->getRepository(ArchiveRecords::class)->findOneBy(['uniqueNumber' => $request->get("code")]);

        if($searchRecord && $searchRecord!=$currentRecord){
            $response = new Response(json_encode(array("check" => false)), 400);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $response = new Response(json_encode(array("check" => TRUE)), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    
    /**
     * @param Request $request
     * @param ArchiveRecords $record
     * @return bool
     */
    private function saveData(Request $request, ArchiveRecords $record)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('archive_agent');
        if (!$is_valid) {
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $saved = false;
        $action = 5;

        if ($record->getId()) {
            $action = 2;
        }
        $em->getConnection()->beginTransaction();
        try {
            if($request->get('location')){
                $az = $em->getRepository(ArchiveAZ::class)->findOneBy(['id' => $request->get('location')]);
                $record->setAz($az);
                $record->setLink(NULL);
            }
            $state = $em->getRepository(ArchiveStates::class)->findOneBy(['id' => $request->get('state')]);
            
            if($record->getState()!=$state){
                if($state->getId()==1 || $state->getId()==2){
                    if(!$request->get("retention_date")){
                        $record->setInitRetention(new \DateTime());
                    }
                    else{
                        $retentionDate = new \DateTime($request->get("retention_date"));
                        $record->setInitRetention($retentionDate);
                    }
                }
                else{
                    $record->setInitRetention(NULL);
                }
            }
            $record->setState($state);
            $type = $em->getRepository(ArchiveTypes::class)->findOneBy(['id' => $request->get('type')]);
            $record->setType($type);
            $area = $em->getRepository(Areas::class)->findOneBy(['id' => $request->get('area')]);
            $record->setArea($area);

            $areaInfo = $em->getRepository(Areas::class)->findOneBy(['id' => $request->get('area_info')]);
            $record->setAreaInfo($areaInfo);

            $record->setUniqueNumber($request->get("unique_number"));
            $record->setModified(new \DateTime());
            if($request->get("title")){
                $record->setTitle($request->get('title'));
            }
            if($request->get("edition")){
                $record->setEdition($request->get('edition'));
            }


            if($request->get("link")){
                $record->setLink($request->get('link'));
                $record->setAZ(NULL);
            }

            if ((!$request->get('location') && !$request->get("link")) || !$request->get('state') || !$request->get('type') || !$request->get('area') || !$request->get('area_info') || !$request->get('comment')) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'Todos los datos son obligatorios'
                );
                return $this->redirect($this->generateUrl('nononsense_archive_records'));
            }

            if($record->getCategories()){
                $record->getCategories()->clear();
            }
            if($request->get('categories')){
                foreach($request->get('categories') as $category){
                    $category = $em->getRepository(ArchiveCategories::class)->findOneBy(['id' => $category]);
                    $record->addCategory($category);
                }
            }
            
            if($record->getPreservations()){
                $record->getPreservations()->clear();
            }
            if($request->get('preservations')){
                foreach($request->get('preservations') as $preservation){
                    $preservation = $em->getRepository(ArchivePreservations::class)->findOneBy(['id' => $preservation]);
                    $record->addPreservation($preservation); 
                }
            }


            if($request->get("comment")){
                $comment=$request->get("comment");
            }

            $em->persist($record);
            $em->flush();
            $this->get('utilities')->saveLogArchive($this->getUser(),$action,$comment,"record",$record->getId());
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add(
                'message',
                "La registro de archivo se ha guardado correctamente"
            );
            $saved = true;
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add(
                'error',
                'Todos los datos son obligatorios'
            );
            return $this->redirect($this->generateUrl('nononsense_archive_records'));
        }
        return $saved;
    }

    private function uploadFile($request)
    {
        //====================
        // GUARDAR DOCUMENTOS
        //====================

        //--------------------
        // url carpeta usuario
        //--------------------
        $ruta='/files/archive-retention/removes/'.date('Y-m-d').'/';
        $full_path = $this->get('kernel')->getRootDir() . $ruta;

        //---------------------------
        // ayudante archivos Symfony
        //---------------------------
        $fs = new Filesystem();

        //----------------------------
        // crear carpeta si no existe
        //----------------------------
        if(!$fs->exists($full_path))
        {
            $fs->mkdir($full_path);
        }

        //----------------------
        // nombre del documento
        //----------------------
        $file = $request->files->get('certification');
        $file_name = $file->getClientOriginalName();
        $file_name_ = $file_name;

        //--------------------------------------------------
        // si existe documento mismo nombre, cambiar nombre
        //--------------------------------------------------
        if(file_exists($full_path.$file_name))
        {
            $i = 0;

            do
            {
                $i++;
                $file_name = $i.$file_name_;
            }

            while (file_exists($full_path.$file_name));
        }

        //-------------------
        // guardar documento
        //-------------------
        $file->move($full_path, $file_name);

        /**
         * @return [ nombre documento, tamaño documento ]
         */
        return [
            'name' => $ruta.$file_name,
            'size' => $file->getClientSize()
        ];
    }

}