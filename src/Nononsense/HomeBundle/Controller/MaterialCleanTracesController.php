<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Nononsense\HomeBundle\Entity\MaterialCleanCenters;
use Nononsense\HomeBundle\Entity\MaterialCleanCleans;
use Nononsense\HomeBundle\Entity\MaterialCleanCleansRepository;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class MaterialCleanTracesController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_traces_list');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $lotNumber = $request->get("lot");
        $filters = $this->getFilters($request);
        $cleansRepository = $this->getDoctrine()->getRepository(MaterialCleanCleans::class);
        $array_item["filters"]=$filters;
        $array_item['status'] = MaterialCleanCleansRepository::status;
        $array_item["items"] = $cleansRepository->list($filters);
        $array_item["count"] = $cleansRepository->count($filters);
        if($array_item['count'] && isset($lotNumber)){
            // Obtenemos los diferentes estados de los materiales
            $distinctStatus = $cleansRepository->getDistinctStatus($filters);
            if(is_array($distinctStatus) && count($distinctStatus) == 1){
                // Si solo hay un estado se usa ese.
                $singleStatus = reset($distinctStatus);
                $status = $singleStatus['status'];
            }elseif(is_array($distinctStatus) && count($distinctStatus) == 2){
                // Si hay 2 estados Quitamos el estado 3 (Material sucio) que es el único que se aplica automáticamente.
                $status = ($distinctStatus[0]['status'] == 3) ? $distinctStatus[1]['status'] : $distinctStatus[0]['status'];
            }else{
                // Si hay más de 2 estados diferentes no mostramos los botones.
                $status = 0;
            }

            if(($status == 3 || $status == 2) && $this->get('app.security')->permissionSeccion('mc_traces_review')){
                $array_item["formAction"] = $this->container->get('router')->generate('nononsense_mclean_traces_review', ['lot' => $lotNumber]);
                $array_item["buttonName"] = 'Revisar Lote';
                $array_item['showCommentBox'] = true;
                $array_item['materialMessages'] = $this->getMaterialMessages($lotNumber);
            }
        }
        $array_item["pagination"] = $this->getPagination($filters, $request, $array_item['count']);
        if(!$request->get("export_excel") && !$request->get("export_pdf")){
            return $this->render('NononsenseHomeBundle:MaterialClean:traces_index.html.twig',$array_item);
        }
        else{
            if($request->get("export_excel")){
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                $phpExcelObject->getProperties();
                $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Id')
                    ->setCellValue('B1', 'P.order')
                    ->setCellValue('C1', 'Material')
                    ->setCellValue('D1', 'Departamento')
                    ->setCellValue('E1', 'Estado')
                    ->setCellValue('F1', 'Identificador')
                    ->setCellValue('G1', 'Usuario limpieza')
                    ->setCellValue('H1', 'Fecha limpieza')
                    ->setCellValue('I1', 'Fecha caducidad')
                    ->setCellValue('J1', 'Usuario verificación')
                    ->setCellValue('K1', 'Fecha verificación')
                    ->setCellValue('L1', 'Comentario verificación')
                    ->setCellValue('M1', 'Usuario limpieza vencida')
                    ->setCellValue('N1', 'Fecha limpieza vencida')
                    ->setCellValue('O1', 'Usuario revisión')
                    ->setCellValue('P1', 'Fecha revisión')
                    ->setCellValue('Q1', 'Comentario revisión')
                    ->setCellValue('R1', 'Usuario cancelación')
                    ->setCellValue('S1', 'Fecha cancelación')
                    ->setCellValue('T1', 'Comentario cancelación');
            }

            if($request->get("export_pdf")){
                $html='<html><body style="font-size:8px;width:100%">';
                $sintax_head_f="<b>Filtros:</b><br>";

                if($request->get("material")){
                    $html.=$sintax_head_f."Material => ".$request->get("material")."<br>";
                    $sintax_head_f="";
                }

                if($request->get("lot")){
                    $html.=$sintax_head_f."Proccess order => ".$request->get("lot")."<br>";
                    $sintax_head_f="";
                }

                if($request->get("user")){
                    $html.=$sintax_head_f."Usuario => ".$request->get("user")."<br>";
                    $sintax_head_f="";
                }

                if($request->get("identifier")){
                    $html.=$sintax_head_f."Identificador => ".$request->get("identifier")."<br>";
                    $sintax_head_f="";
                }

                if($request->get("state")){
                    switch($request->get("state")){
                        case 1: $hstate="Material limpio";break;
                        case 2: $hstate="Verificado limpieza";break;
                        case 3: $hstate="Limpieza vencida";break;
                        case 4: $hstate="Revisado";break;
                        case 5: $hstate="Limpieza cancelada";break;
                    }
                    $html.=$sintax_head_f."Estado => ".$hstate."<br>";
                    $sintax_head_f="";
                }

                if($request->get("clean_date_start")){
                    $html.=$sintax_head_f."Fecha limpieza desde => ".$request->get("clean_date_start")."<br>";
                    $sintax_head_f="";
                }

                if($request->get("clean_date_end")){
                    $html.=$sintax_head_f."Fecha limpieza hasta => ".$request->get("clean_date_end")."<br>";
                    $sintax_head_f="";
                }

                if($request->get("verification_date_start")){
                    $html.=$sintax_head_f."Fecha verificación desde => ".$request->get("verification_date_start")."<br>";
                    $sintax_head_f="";
                }

                if($request->get("verification_date_end")){
                    $html.=$sintax_head_f."Fecha verificación hasta => ".$request->get("verification_date_end")."<br>";
                    $sintax_head_f="";
                }

                $html.='<br><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%">
                <th style="font-size:8px;">Id</th>
                <th style="font-size:8px;">P.order</th>
                <th style="font-size:8px;">Material</th>
                <th style="font-size:8px;">Dpt</th>
                <th style="font-size:8px;">Estado</th>
                <th style="font-size:8px;">Ident.</th>
                <th style="font-size:8px;">Usu. limpieza</th>
                <th style="font-size:8px;">F. limp.</th>
                <th style="font-size:8px;">F. caducidad</th>
                <th style="font-size:8px;">Usu. verif.</th>
                <th style="font-size:8px;">F. verificación</th>
                <th style="font-size:8px;">Coment. verif.</th>
                <th style="font-size:8px;">Usu. limp. venc.</th>
                <th style="font-size:8px;">F. limp. venc.</th>
                <th style="font-size:8px;">Usuario rev.</th>
                <th style="font-size:8px;">Fecha rev.</th>
                <th style="font-size:8px;">Coment. rev.</th>
                <th style="font-size:8px;">Usuario canc.</th>
                <th style="font-size:8px;">Fecha canc.</th>
                <th style="font-size:8px;">Coment. canc.</th>
                </tr>';
            }

            $i=2;
            foreach($array_item["items"] as $item){
                switch($item->getStatus()){
                    case 1: $status="Material limpio";break;
                    case 2: $status="Verificado limpieza";break;
                    case 3: $status="Limpieza vencida";break;
                    case 4: $status="Revisado";break;
                    case 5: $status="Limpieza cancelada";break;
                    default: $status="Desconocido";
                }

                if($item->getMaterialOther()){
                    $other_material=" - ".$item->getMaterialOther();
                }
                else{
                    $other_material="";
                }

                $department = $item->getCenter()->getDepartment() ? $item->getCenter()->getDepartment()->getName() : '';

                if($request->get("export_excel")){
                    $phpExcelObject->getActiveSheet()
                        ->setCellValue('A'.$i, $item->getId())
                        ->setCellValue('B'.$i, $item->getLotNumber())
                        ->setCellValue('C'.$i, $item->getMaterial()->getName().$other_material)
                        ->setCellValue('D'.$i, $department)
                        ->setCellValue('E'.$i, $status)
                        ->setCellValue('F'.$i, $item->getCode())
                        ->setCellValue('G'.$i, $item->getCleanUser()->getName())
                        ->setCellValue('H'.$i, ($item->getCleanDate() ? $item->getCleanDate()->format('Y-m-d H:i:s') : ''))
                        ->setCellValue('I'.$i, ($item->getCleanExpiredDate() ? $item->getCleanExpiredDate()->format('Y-m-d H:i:s') : ''))
                        ->setCellValue('J'.$i, ($item->getVerificationUser() ? $item->getVerificationUser()->getName() : ''))
                        ->setCellValue('K'.$i, ($item->getVerificationDate() ? $item->getVerificationDate()->format('Y-m-d H:i:s') : ''))
                        ->setCellValue('L'.$i, substr($item->getUseInformation(), 0, 45))
                        ->setCellValue('M'.$i, ($item->getDirtyMaterialUser() ? $item->getDirtyMaterialUser()->getName() : ''))
                        ->setCellValue('N'.$i, ($item->getDirtyMaterialDate() ? $item->getDirtyMaterialDate()->format('Y-m-d H:i:s') : ''))
                        ->setCellValue('O'.$i, ($item->getReviewUser() ? $item->getReviewUser()->getName() : ''))
                        ->setCellValue('P'.$i, ($item->getReviewDate() ? $item->getReviewDate()->format('Y-m-d H:i:s') : ''))
                        ->setCellValue('Q'.$i, substr($item->getReviewInformation(), 0, 45))
                        ->setCellValue('R'.$i, ($item->getCancelUser() ? $item->getCancelUser()->getName() : ''))
                        ->setCellValue('S'.$i, ($item->getCancelDate() ? $item->getCancelDate()->format('Y-m-d H:i:s') : ''))
                        ->setCellValue('T'.$i, substr($item->getCancelInformation(), 0, 45));
                }

                if($request->get("export_pdf")){
                    $html.='<tr style="font-size:8px"><td>'.$item->getId().'</td><td>'.$item->getLotNumber().'</td><td>'.$item->getMaterial()->getName().$other_material.'</td><td>'.$department.'</td><td>'.$status.'</td><td>'.$item->getCode().'</td><td>'.$item->getCleanUser()->getName().'</td><td>'.($item->getCleanDate() ? $item->getCleanDate()->format('Y-m-d H:i:s') : '').'</td><td>'.($item->getCleanExpiredDate() ? $item->getCleanExpiredDate()->format('Y-m-d H:i:s') : '').'</td><td>'. ($item->getVerificationUser() ? $item->getVerificationUser()->getName() : '').'</td><td>'.($item->getVerificationDate() ? $item->getVerificationDate()->format('Y-m-d H:i:s') : '').'</td><td>'.substr($item->getUseInformation(), 0, 45).'</td><td>'.($item->getDirtyMaterialUser() ? $item->getDirtyMaterialUser()->getName() : '').'</td><td>'.($item->getDirtyMaterialDate() ? $item->getDirtyMaterialDate()->format('Y-m-d H:i:s') : '').'</td><td>'.($item->getReviewUser() ? $item->getReviewUser()->getName() : '').'</td><td>'.($item->getReviewDate() ? $item->getReviewDate()->format('Y-m-d H:i:s') : '').'</td><td>'.substr($item->getReviewInformation(), 0, 45).'</td><td>'.($item->getCancelUser() ? $item->getCancelUser()->getName() : '').'</td><td>'.($item->getCancelDate() ? $item->getCancelDate()->format('Y-m-d H:i:s') : '').'</td><td>'.substr($item->getCancelInformation(), 0, 45).'</td></tr>';
                }

                $i++;
            }

            if($request->get("export_excel")){
                $phpExcelObject->getActiveSheet()->setTitle('Trazabilidad material limpio');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                  ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                  'trace_material_clean.xlsx'
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

    /**
     * @param Request $request
     * @return array
     */
    private function getFilters(Request $request)
    {
        $filters = [];

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

        if($request->get("material")){
            $filters["material"]=$request->get("material");
        }

        if($request->get("lot")){
            $filters["lot"]=$request->get("lot");
        }

        if($request->get("clean_date_start")){
            $filters["clean_date_start"]=$request->get("clean_date_start");
        }

        if($request->get("clean_date_end")){
            $filters["clean_date_end"]=$request->get("clean_date_end");
        }

        if($request->get("verification_date_start")){
            $filters["verification_date_start"]=$request->get("verification_date_start");
        }

        if($request->get("verification_date_end")){
            $filters["verification_date_end"]=$request->get("verification_date_end");
        }

        if($request->get("user")){
            $filters["user"]=$request->get("user");
        }

        if($request->get("state")){
            $filters["state"]=$request->get("state");
        }
        if($request->get("identifier")){
            $filters["identifier"]=$request->get("identifier");
        }
        return $filters;
    }

    /**
     * @param array $filters
     * @param Request $request
     * @param int $count
     * @return array
     */
    private function getPagination(array $filters, Request $request, int $count)
    {
        $url=$this->container->get('router')->generate('nononsense_mclean_traces_list');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=true;
        }
        else{
            $parameters=false;
        }
        return Utils::paginador($filters["limit_many"],$request,$url,$count,"/", $parameters);
    }

    private function getMaterialMessages($lotNumber)
    {
        $message = [];
        $totalNeed = 0;

        $materialNeed = $this->getMaterialNeed($lotNumber);
        if($materialNeed){
            $text = 'Se han detectado los siguientes productos:'.'<br/>';
            foreach($materialNeed as $need){
                $s = ($need['total'] == 1) ? '' : 's';
                $es = ($need['total'] == 1) ? '' : 'es';
                $totalNeed += $need['total'];
                $text .= $need['name'].' con '.$need['total']. ' material'.$es.' necesario'.$s.'<br/>';
            }
            $text .= 'Total '.$totalNeed.' materiales necesarios';
            $message[] = [
                'type' => 'success',
                'message' => $text
            ];
        }

        $materialInvalid = $this->getMaterialInvalid($lotNumber);
        if($materialInvalid){
            $es = ($materialInvalid == 1) ? '' : 'es';
            $message[] = [
                'type' => 'danger',
                'message' => 'La fecha de limpieza de '.$materialInvalid. ' material'.$es. ' ha caducado antes de su uso.'
            ];
        }

        $materialUsed = $this->getMaterialUsed($lotNumber);
        if($materialUsed && $totalNeed > 0){
            $nNeed = ($totalNeed == 1) ? '' : 'n';
            $esNeed = ($totalNeed == 1) ? '' : 'es';
            $nUsed = ($materialUsed == 1) ? '' : 'n';
            $esUsed = ($materialUsed == 1) ? '' : 'es';
            $sUsed = ($materialUsed == 1) ? '' : 's';
            $message[] = [
                'type' => ($totalNeed != $materialUsed) ? 'danger' : 'success',
                'message' => 'Se necesitaba'.$nNeed.' '.$totalNeed.' material'.$esNeed.', se ha'.$nUsed.' utilizado '.$materialUsed.' material'.$esUsed.' no vencido'.$sUsed
            ];
        }
//        elseif ($materialUsed){
//            $es = ($materialUsed == 1) ? '' : 'es';
//            $n = ($materialUsed == 1) ? '' : 'n';
//            $sUsed = ($materialUsed == 1) ? '' : 's';
//            $message[] = [
//                'type' => 'success',
//                'message' => 'Se ha'.$n.' usado '.$materialUsed.' material'.$es.' no vencido'.$sUsed
//            ];
//        }
        return $message;
    }

    /**
     * @param string $lotNumber
     * @return int
     */
    private function getMaterialUsed($lotNumber)
    {
        /** @var MaterialCleanCleansRepository $cleansRepository */
        $cleansRepository = $this->getDoctrine()->getRepository(MaterialCleanCleans::class);
        return $cleansRepository->getMaterialUsed($lotNumber);
    }

    /**
     * @param string $lotNumber
     * @return array
     */
    private function getMaterialNeed($lotNumber)
    {
        /** @var MaterialCleanCleansRepository $cleansRepository */
        $cleansRepository = $this->getDoctrine()->getRepository(MaterialCleanCleans::class);
        return $cleansRepository->getMaterialNeed($lotNumber);
    }

    /**
     * @param string $lotNumber
     * @return int
     */
    private function getMaterialInvalid($lotNumber)
    {
        $cleansRepository = $this->getDoctrine()->getRepository(MaterialCleanCleans::class);
        $invalid = $cleansRepository->findBy(['lotNumber' => $lotNumber, 'status' => 3, 'verificationDate' => null]);
        return ($invalid) ? count($invalid) : 0;
    }

    public function markDirtyAction(Request $request, $lot)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_traces_dirty');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $error = false;

        $em = $this->getDoctrine()->getManager();
        /** @var MaterialCleanCleansRepository $traces */
        $cleansRepository = $em->getRepository('NononsenseHomeBundle:MaterialCleanCleans');
        $traces = $cleansRepository->findBy(['lotNumber' => $lot, 'status' => 2]);

        if (!$traces) {
            $this->get('session')->getFlashBag()->add('error', "No se ha encontrado material usado con ese número de lote.");
            $error = true;
        }

        $password = $request->get('password');
        if(!$this->get('utilities')->checkUser($password)){
            $this->get('session')->getFlashBag()->add('error', "La contraseña no es correcta.");
            $error = true;
        }

        if(!$error){
            $now = new DateTime();
            $firma = 'Material limpieza caducada registrado con contraseña de usuario el día ' . $now->format('d-m-Y H:i:s');
            try{
                /** @var MaterialCleanCleans $trace */
                foreach($traces as $trace){
                    $department = $trace->getCenter()->getDepartment() ? $trace->getCenter()->getDepartment()->getName() : '';
                    $html = '
                        <p>Material limpieza caducada</p>
                        <ul>
                            <li>Id trazabilidad:'.$trace->getId().'</li>
                            <li>Material:'.$trace->getMaterial()->getName().'</li>
                            <li>Código:'.$trace->getCode().'</li>
                            <li>Departamento: '.$department.'</li>
                            <li>Centro:'.$trace->getCenter()->getName().'</li>
                            <li>Usuario:'.$this->getUser()->getUsername().'</li>
                            <li>Fecha: '.$now->format('d-m-Y H:i:s').'</li>
                        </ul>';

                    $file = Utils::generatePdf($this->container, 'GSK - Material limpio', 'Material limpieza caducada', $html, 'material', $this->getParameter('crt.root_dir'));
                    Utils::setCertification($this->container, $file, 'material-limpieza caducada', $trace->getId());

                    $trace->setStatus(3)
                        ->setDirtyMaterialUser($this->getUser())
                        ->setDirtyMaterialDate($now)
                        ->setDirtyMaterialSignature($firma);

                    $em->persist($trace);
                    $em->flush();
                }
                    $this->get('session')->getFlashBag()->add('message',"El material se ha marcado correctamente");

            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar marcar el material como Material sucio");
            }
        }
        return $this->redirect($this->generateUrl('nononsense_mclean_traces_list'));
    }

    public function markReviewAction(Request $request, $lot)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_traces_review');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $error = false;

        $em = $this->getDoctrine()->getManager();
        /** @var MaterialCleanCleansRepository $traces */
        $cleansRepository = $em->getRepository('NononsenseHomeBundle:MaterialCleanCleans');
        $traces = $cleansRepository->findBy(['lotNumber' => $lot]);

        if (!$traces) {
            $this->get('session')->getFlashBag()->add('error', "No se ha encontrado material sucio con ese número de lote.");
            $error = true;
        }

        $password = $request->get('password');
        if(!$this->get('utilities')->checkUser($password)){
            $this->get('session')->getFlashBag()->add('error', "La contraseña no es correcta.");
            $error = true;
        }

        if(!$error){
            $now = new DateTime();
            $firma = 'Revisión de material registrada con contraseña de usuario el día ' . $now->format('d-m-Y H:i:s');
            try{
                /** @var MaterialCleanCleans $trace */
                foreach($traces as $trace){
                    if($trace->getStatus() != 4){
                        $department = $trace->getCenter()->getDepartment() ? $trace->getCenter()->getDepartment()->getName() : '';
                        $html = '
                        <p>Revisión de material</p>
                        <ul>
                            <li>Id trazabilidad:'.$trace->getId().'</li>
                            <li>Material:'.$trace->getMaterial()->getName().'</li>
                            <li>Código:'.$trace->getCode().'</li>
                            <li>Departamento: '.$department.'</li>
                            <li>Centro:'.$trace->getCenter()->getName().'</li>
                            <li>Usuario:'.$this->getUser()->getUsername().'</li>
                            <li>Fecha: '.$now->format('d-m-Y H:i:s').'</li>
                        </ul>';

                        $file = Utils::generatePdf($this->container, 'GSK - Material limpio', 'Revisión de material', $html, 'material', $this->getParameter('crt.root_dir'));
                        Utils::setCertification($this->container, $file, 'material revision', $trace->getId());

                        $trace->setStatus(4)
                            ->setReviewUser($this->getUser())
                            ->setReviewDate(new DateTime())
                            ->setReviewSignature($firma)
                            ->setReviewInformation($request->get('comment-box'));

                        $em->persist($trace);
                        $em->flush();
                    }
                }
                $this->get('session')->getFlashBag()->add('message',"El material se ha marcado correctamente");
            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar marcar el material como Revisado");
            }
        }
        return $this->redirect($this->generateUrl('nononsense_mclean_traces_list'));
    }

    public function markCancelAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_traces_list');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $error = false;

        $em = $this->getDoctrine()->getManager();
        /** @var MaterialCleanCleansRepository $trace */
        $cleansRepository = $em->getRepository(MaterialCleanCleans::class);
        $trace = $cleansRepository->find($id);

        if (!$trace) {
            $this->get('session')->getFlashBag()->add('error', "No se ha encontrado la limpieza.");
            $error = true;
        }

        $password = $request->get('password');
        if(!$this->get('utilities')->checkUser($password)){
            $this->get('session')->getFlashBag()->add('error', "La contraseña no es correcta.");
            $error = true;
        }
        $userId = $this->getUser()->getId();

        if($trace->getStatus() !== 1 || $userId === $trace->getCleanUser()->getId()){
            $this->get('session')->getFlashBag()->add('error', "No puedes cancelar esa limpieza");
            $error = true;
        }

        if(!$request->get('comment-box') || trim($request->get('comment-box')) == ''){
            $this->get('session')->getFlashBag()->add('error', "Para cancelar la limpieza es obligatorio escribir un comentario");
            $error = true;
        }

        if(!$error){
            $now = new DateTime();
            $firma = 'Cancelación de limpieza registrada con contraseña de usuario el día ' . $now->format('d-m-Y H:i:s');
            try{
                $department = $trace->getCenter()->getDepartment() ? $trace->getCenter()->getDepartment()->getName() : '';
                $html = '
                <p>Cancelación de limpieza de material</p>
                <ul>
                    <li>Id trazabilidad:'.$trace->getId().'</li>
                    <li>Material:'.$trace->getMaterial()->getName().'</li>
                    <li>Código:'.$trace->getCode().'</li>
                    <li>Departamento: '.$department.'</li>
                    <li>Centro:'.$trace->getCenter()->getName().'</li>
                    <li>Usuario:'.$this->getUser()->getUsername().'</li>
                    <li>Fecha: '.$now->format('d-m-Y H:i:s').'</li>
                </ul>';

                $file = Utils::generatePdf($this->container, 'GSK - Material limpio', 'Cancelación de limpieza', $html, 'material', $this->getParameter('crt.root_dir'));
                Utils::setCertification($this->container, $file, 'material-cancelacion', $trace->getId());

                $trace->setStatus(5)
                    ->setCancelUser($this->getUser())
                    ->setCancelDate(new DateTime())
                    ->setCancelSignature($firma)
                    ->setCancelInformation($request->get('comment-box'));

                $em->persist($trace);
                $em->flush();
                $this->get('session')->getFlashBag()->add('message',"La limpieza se ha cancelado correctamente");
            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar cancelar la limpieza");
            }
        }
        return $this->redirect($this->generateUrl('nononsense_mclean_traces_list'));
    }

    public function showTraceAction(Request $request, $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('mc_traces_list');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        /** @var MaterialCleanCleansRepository $traces */
        $cleansRepository = $em->getRepository('NononsenseHomeBundle:MaterialCleanCleans');
        $trace = $cleansRepository->find($id);

        if(!$trace){
            $this->get('session')->getFlashBag()->add('error', "No se ha podido encontrar la traza.");
            return $this->redirect($this->generateUrl('nononsense_mclean_traces_list'));
        }

        $userId = $this->getUser()->getId();

        $canCancel = $trace->getStatus() === 1 && $userId !== $trace->getCleanUser()->getId();

        $result = [
            'canCancel' => $canCancel,
            'trace' => $trace,
            'status' => MaterialCleanCleansRepository::status
        ];

        return $this->render('NononsenseHomeBundle:MaterialClean:trace_view.html.twig',$result);
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