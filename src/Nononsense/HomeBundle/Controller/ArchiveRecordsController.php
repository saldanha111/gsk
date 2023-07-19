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

        if($request->get("retentionAction")){
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
                 ->setCellValue('E2', 'Área')
                 ->setCellValue('F2', 'Tipo')
                 ->setCellValue('G2', 'Estado')
                 ->setCellValue('H2', 'Disponibilidad')
                 ->setCellValue('I2', 'Categoría retención')
                 ->setCellValue('J2', 'Inicio retención')
                 ->setCellValue('K2', 'Fecha destrucción')
                 ->setCellValue('L2', 'Preservation notice');
            }

            if($request->get("export_pdf")){
                $html='<html><body style="font-size:8px;width:100%"><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%">
                        <th style="font-size:8px;width:5%">ID</th>
                        <th style="font-size:8px;width:5%">Identificador</th>
                        <th style="font-size:8px;width:10%">Título</th>
                        <th style="font-size:8px;width:5%">Edición</th>
                        <th style="font-size:8px;width:10%">Área</th>
                        <th style="font-size:8px;width:10%">Tipo</th>
                        <th style="font-size:8px;width:10%">Estado</th>
                        <th style="font-size:8px;width:10%">Disponibilidad</th>
                        <th style="font-size:8px;width:10%">Categoría</th>
                        <th style="font-size:8px;width:10%">Inicio retención</th>
                        <th style="font-size:8px;width:10%">Fecha destrucción</th>
                        <th style="font-size:8px;width:5%">Preserv. notice</th>
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
                            $type="Categoría";
                            $id=$item["category"];
                        }
                    }
                }

                if($request->get("export_excel")){
                    $phpExcelObject->getActiveSheet()
                    ->setCellValue('A'.$i, $item["id"])
                    ->setCellValue('B'.$i, $item["uniqueNumber"])
                    ->setCellValue('C'.$i, $item["title"])
                    ->setCellValue('D'.$i, $item["edition"])
                    ->setCellValue('E'.$i, $item["area"])
                    ->setCellValue('F'.$i, $item["type"])
                    ->setCellValue('G'.$i, $item["state"])
                    ->setCellValue('H'.$i, $item["useState"])
                    ->setCellValue('I'.$i, $item["category"])
                    ->setCellValue('J'.$i, ($item["initRetention"]) ? $this->get('utilities')->sp_date($item["initRetention"]->format('d/m/Y')) : '')
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
        $types = $em->getRepository(ArchiveTypes::class)->findBy(array("active"=>TRUE));
        $states = $em->getRepository(ArchiveStates::class)->findAll();
        $categories = $em->getRepository(ArchiveCategories::class)->findBy(array("active"=>TRUE));
        $preservations = $em->getRepository(ArchivePreservations::class)->findBy(array("active"=>TRUE));

        if (!$record) {
            $record = new ArchiveRecords();
            $record->setCreated(new \DateTime());
            $stateUse = $em->getRepository(ArchiveUseStates::class)->findOneBy(['id' => 1]);
            $record->setUseState($stateUse);
            $record->setCreator($user);
        }

        if ($request->getMethod() == 'POST' && $this->saveData($request, $record)) {
            return $this->redirect($this->generateUrl('nononsense_archive_records'));
        }

        $data = [
            'record' => $record,
            'used' => false,
            'areas' => $areas,
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
                foreach($records as $record){
                    $record->setRemovedAt(new \DateTime());
                    $this->get('utilities')->saveLogArchive($this->getUser(),7,$request->get('comment'),"record",$record->getId());
                    $em->persist($record);
                }
                break;
        }
        
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "La acción de actualización de archivos ha finalizado satisfactoriamente");

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

            $location = $em->getRepository(ArchiveLocations::class)->findOneBy(['id' => $request->get('location')]);
            $record->setLocation($location);
            $state = $em->getRepository(ArchiveStates::class)->findOneBy(['id' => $request->get('state')]);
            
            if($record->getState()!=$state){
                if($state->getId()==1 || $state->getId()==2){
                    $record->setInitRetention(new \DateTime());
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

            $record->setUniqueNumber($request->get("unique_number"));
            $record->setModified(new \DateTime());
            if($request->get("title")){
                $record->setTitle($request->get('title'));
            }
            if($request->get("edition")){
                $record->setEdition($request->get('edition'));
            }

            $record->setAZ($request->get('az'));

            if (!$request->get('location') || !$request->get('state') || !$request->get('type') || !$request->get('area') || !$request->get('az') || !$request->get('comment')) {
                throw new Exception('Todos los datos son obligatorios.');
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
                "Error al intentar guardar los datos del registro"
            );
            return $e->getMessage();
        }
        return $saved;
    }
}