<?php

namespace Nononsense\HomeBundle\Controller;

use DateTime;
use Nononsense\HomeBundle\Entity\MaterialCleanCenters;
use Nononsense\HomeBundle\Entity\MaterialCleanCleans;
use Nononsense\HomeBundle\Entity\MaterialCleanCleansRepository;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        return $this->render('NononsenseHomeBundle:MaterialClean:traces_index.html.twig',$array_item);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getFilters(Request $request)
    {
        $filters = [];

        if($request->get("page")){
            $filters["limit_from"]=$request->get("page")-1;
        }
        else{
            $filters["limit_from"]=0;
        }
        $filters["limit_many"]=15;

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
        }elseif ($materialUsed){
            $es = ($materialUsed == 1) ? '' : 'es';
            $n = ($materialUsed == 1) ? '' : 'n';
            $sUsed = ($materialUsed == 1) ? '' : 's';
            $message[] = [
                'type' => 'success',
                'message' => 'Se ha'.$n.' usado '.$materialUsed.' material'.$es.' no vencido'.$sUsed
            ];
        }
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
                    $html = '
                        <p>Material limpieza caducada</p>
                        <ul>
                            <li>Material:'.$trace->getMaterial()->getName().'</li>
                            <li>Código:'.$trace->getCode().'</li>
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
                $this->get('session')->getFlashBag()->add('error', "Error al intentar marcar el material como Material sucio: ".$e->getMessage());
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
                    $html = '
                        <p>Revisión de material</p>
                        <ul>
                            <li>Material:'.$trace->getMaterial()->getName().'</li>
                            <li>Código:'.$trace->getCode().'</li>
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
                $this->get('session')->getFlashBag()->add('message',"El material se ha marcado correctamente");
            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error', "Error al intentar marcar el material como Revisado: ".$e->getMessage());
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

        $result = [
            'trace' => $trace,
            'status' => MaterialCleanCleansRepository::status
        ];

        return $this->render('NononsenseHomeBundle:MaterialClean:trace_view.html.twig',$result);
    }
}
