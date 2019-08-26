<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 03/04/2018
 * Time: 19:44
 */

namespace Nononsense\HomeBundle\Controller;


use Nononsense\HomeBundle\Entity\MetaData;
use Nononsense\HomeBundle\Entity\MetaFirmantes;
use Nononsense\HomeBundle\Entity\Revision;
use Nononsense\HomeBundle\Entity\RevisionInstanciaWorkflow;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\HomeBundle\Entity\InstanciasWorkflows;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasSteps;

use Nononsense\UtilsBundle\Classes;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class RegistroArchivoController extends Controller
{
    public function listAction(Request $request)
    {
        $nRegistro = $request->query->get('nRegistro');
        $nombrePlantilla = $request->query->get('nombrePlantilla');
        $usuario = $request->query->get('usuario'); //Name
        $fechaInicio = $request->query->get('fechaInicio');
        $fechaFin = $request->query->get('fechaFin');
        $lote = $request->query->get('lote');
        $material = $request->query->get('material');
        $SAP = $request->query->get('SAP');
        $equipo = $request->query->get('equipo');
        $nPlantilla = $request->query->get('nPlantilla');
        $estado = $request->query->get('estado');
        //nPlantilla

        $user = $this->container->get('security.context')->getToken()->getUser();
        $user_logged = $user->getId();
        /*
                $arrayUsersId = array();

                if (isset($usuario) && $usuario != "") {
                    $userCreatedArray = $this->getDoctrine()
                        ->getRepository('NononsenseUserBundle:Users')
                        ->listUsersLikeName($usuario);

                    foreach ($userCreatedArray as $userFounded) {
                        $arrayUsersId[] = $userFounded->getId();
                    }
                } else {
                    $arrayUsersId = array();
                }
        */
        $filtro = false;
        if (isset($nRegistro) ||
            isset($nombrePlantilla) ||
            isset($usuario) ||
            isset($fechaInicio) ||
            isset($fechaFin) ||
            isset($lote) ||
            isset($material) ||
            isset($SAP) ||
            isset($equipo) ||
            isset($nPlantilla)
        ) {
            $filtro = true;
        }

        if ($filtro) {
            if (isset($nPlantilla)) {
                $masterStep = $this->getDoctrine()
                    ->getRepository('NononsenseHomeBundle:MasterSteps')
                    ->findOneBy(array("plantilla_id" => $nPlantilla));

                if ($masterStep != null) {
                    $masterWorkflowID = $masterStep->getWorkflowId();
                    //var_dump($masterWorkflowID);
                } else {
                    $masterWorkflowID = "";
                }
            } else {
                $masterWorkflowID = "";
            }


            $documents = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                ->listArchivo($user_logged, $usuario, $nRegistro, $nombrePlantilla, $SAP, $equipo, $lote, $material, $masterWorkflowID, $estado, $fechaInicio, $fechaFin);
        } else {
            $documents = array();
        }


        /*
         * Debería estar paginado.
         */

        foreach ($documents as &$element) {
            $metaDataArray = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:MetaData')
                ->findBy(array("workflow_id" => $element['id']));

            $fechaInicio = null;
            $fechaValidado = null;
            $lote = "";
            $material = "";

            foreach ($metaDataArray as $MetaData) {


                $fechaInicio = $MetaData->getFechainicio();


                $fechaValidado = $MetaData->getFechafin();

                $lote = $MetaData->getLote();

                $material = $MetaData->getMaterial();

            }

            if ($fechaInicio != null) {
                //var_dump($fechaInicio);
                $element['fechainicio'] = $fechaInicio->format('d-m-Y H:i:s');
            } else {
                $element['fechainicio'] = '';
            }

            if ($fechaValidado != null) {
                //var_dump($fechaValidado);
                $element['fechavalidado'] = $fechaValidado->format('d-m-Y H:i:s');
            } else {
                $element['fechavalidado'] = '';
            }

            $element['lote'] = $lote;
            $element['material'] = $material;

        }

        return $this->render('NononsenseHomeBundle:Contratos:registro_archivo.html.twig', array(
            "documents" => $documents,
        ));
    }

    public function verRegistroAction($registroid)
    {

        $route = $this->container->get('router')->generate('nononsense_ver_registro', array('revisionid' => $registroid));

        return $this->redirect($route);
    }

    public function oldverRegistroAction($registroid)
    {


        $dataResponse = new \stdClass();
        $dataResponse->workflow_id = $registroid;


        $firststep = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registroid, "dependsOn" => 0));


        $options = array();

        $options['template'] = $firststep->getMasterStep()->getPlantillaId();


        $options['token'] = $firststep->getToken();

        $url_edit_documento = $this->get('app.sdk')->viewDocument($options);
        return $this->redirect($url_edit_documento);
    }

    public function verRegistroHistoricoInterfaceAction($registroid)
    {

        $route = $this->container->get('router')->generate('nononsense_ver_registro', array('revisionid' => $registroid));

        return $this->redirect($route);
    }

    function oldverRegistroHistoricoInterfaceAction($registroid)
    {
        $rootdir = $this->get('kernel')->getRootDir();
        $rootdirFiles = $rootdir . "/../web/Files";

        $registro = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($registroid);

        $firststep = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registroid, "dependsOn" => 0));

        $name = $firststep->getMasterStep()->getName();
        $step_id = $firststep->getId();

        $arrayDocumentos = array();

        $stepDataValues = $firststep->getStepDataValue();
        $stepDataValuesJSON = json_decode($stepDataValues);

        $filenamedownloadpdf = $rootdirFiles . "/" . $name . $registroid . "_" . $step_id . ".pdf";
        $this->createPDF($filenamedownloadpdf, $firststep, $stepDataValuesJSON);

        $activo = $filenamedownloadpdf;

        /*
         * Revisiones
         */
        $revisionesStep = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:RevisionStep')
            ->findBy(array('step_id' => $step_id));

        $n = 1;
        foreach ($revisionesStep as $revision) {
            $filenamedownloadpdf = $rootdirFiles . "/" . $name . $registroid . "_" . $step_id . "_Revision_" . $n . ".pdf";

            $stepDataValues = $revision->getStepDataValue();
            $stepDataValuesJSON = json_decode($stepDataValues);

            $this->createPDF($filenamedownloadpdf, $firststep, $stepDataValuesJSON);
            $arrayDocumentos[] = $filenamedownloadpdf;

            $n++;
        }

        /*
         * Waiter
         */
        $creation = false;

        while (!$creation) {
            $it = 1;
            foreach ($arrayDocumentos as $documento) {
                if (file_exists($documento)) {
                    //  var_dump($documento ." creado");
                    $it++;
                }
            }
            if ($it == $n) {
                //echo 'Todos creados';
                $creation = true;
            }
        }

        $zip = new \ZipArchive();
        $zipName = $rootdirFiles . "/" . $registroid . ".zip";
        $zip->open($zipName, \ZipArchive::CREATE);

        $zip->addFile($activo, "Activo.pdf");

        $n = 1;
        foreach ($arrayDocumentos as $documento) {
            $localname = "Evidencia" . $n . ".pdf";
            $zip->addFile($documento, $localname);
            $n++;
        }
        $zip->close();

        $responseFile = new BinaryFileResponse($zipName);
        $responseFile->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "Historico" . $registroid . ".zip"
        );
        return $responseFile;

    }

    public function verRegistroPDFAction($registroid)
    {
        // Debería funcionar con data ...

        $dataResponse = new \stdClass();
        $dataResponse->workflow_id = $registroid;


        $firststep = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->findOneBy(array("workflow_id" => $registroid, "dependsOn" => 0));

        $step_id = $firststep->getId();
        $dataResponse->step_id = $step_id;

        $name = $firststep->getMasterStep()->getName();
        $template = $firststep->getMasterStep()->getPlantillaId();

        $stepDataValues = $firststep->getStepDataValue();
        $stepDataValuesJSON = json_decode($stepDataValues);

        $rootdir = $this->get('kernel')->getRootDir();
        $rootdirFiles = $rootdir . "/../web/Files";

        $filenamedownloadpdf = $rootdirFiles . "/" . $name . $registroid . "_" . $step_id . ".pdf";
        $filenamepdf = $name . $registroid . "_" . $step_id;

        $aux = new Classes\Auxiliar();
        $utils = new Classes\Utils();

        $options = array();
        $options['template'] = (int)$template;
        $options['documentName'] = $filenamepdf;
        $options['response'] = 'json';

        $dataDXO = json_encode($stepDataValuesJSON);

        $options['data'] = $dataDXO;
        $options['docFormat'] = 'pdf';
        $options['name'] = $filenamepdf . '.pdf';
        $options['reference'] = $filenamepdf;

        $opt = $this->get('app.sdk')->base64_encode_url_safe(json_encode($options));

        //generate security info
        $uniqid = uniqid() . rand(99999, 9999999);
        $timestamp = time();
        $control = $template . '-';
        $control .= $timestamp . '-' . $uniqid;
        $control .= '-' . $opt;

        $dataKey = sha1($control, true);
        $masterKey = $this->getParameter('apikey');
        $APIKEY = bin2hex($utils->sha1_hmac($masterKey, $dataKey));

        //we should now redirect to Docxpresso
        $url = $this->getParameter('docxpresso_installation') . '/documents/requestDocument/' . $template;
        $addr = $url . '?';
        $addr .= 'uniqid=' . $uniqid . '&';
        $addr .= 'timestamp=' . $timestamp . '&';
        $addr .= 'APIKEY=' . $APIKEY;

        $curlResponse = $aux->curlRequest($addr, $opt);

        if ($curlResponse['status'] != 'OK') {
            //handle the error
            //exit('error');
            echo "</br>Error</br>";
            print_r("</br>" . $curlResponse['externalData']);

            $responseAction = new Response();
            $responseAction->setStatusCode(500);
            $dataResponse->feedback = "Error en la creación del fichero";
            $responseAction->setContent(json_encode($dataResponse));

        } else {
            $response = json_decode($curlResponse['externalData']);
            //print_r($response);

            $usageId = $response->usageId;
            $token = $response->token;
            $name = $response->name;

            $dataDonwload = array();
            $dataDonwload['id'] = $template;
            $dataDonwload['token'] = $token;
            $documentLink = $this->get('app.sdk')->downloadDocument($dataDonwload);

            if (file_exists($filenamedownloadpdf)) {
                unlink($filenamedownloadpdf);
            }

            if (file_put_contents($filenamedownloadpdf, fopen($documentLink, 'r')) === FALSE) {
                $responseAction = new Response();
                $responseAction->setStatusCode(500);
                $dataResponse->feedback = "Error en la descarga del fichero";
                $responseAction->setContent(json_encode($dataResponse));

            } else {

                $responseFile = new BinaryFileResponse($filenamedownloadpdf);
                $responseFile->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    $name . ".pdf"
                );
                return $responseFile;
            }
        }
    }

    public function reconciliacionHistoryAction($id){
        $registroViejo = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($id);

        $name=$registroViejo->getMasterWorkflowEntity()->getName();

        $procesarReconciliaciones=TRUE;
        $i=0;
        while ($procesarReconciliaciones) {
            if ($registroViejo != null) {

                $subcat = $registroViejo->getMasterWorkflowEntity()->getCategory()->getName();
                $name = $registroViejo->getMasterWorkflowEntity()->getName();

                $element = array(
                    "id" => $registroViejo->getId(),
                    "subcat" => $subcat,
                    "name" => $name,
                    "status" => $registroViejo->getStatus(),
                    "fecha" => $registroViejo->getModified()
                );
                $documentsReconciliacion[$i] = $element;
            } else {
                $procesarReconciliaciones = false;
            }

            // Ver una posible reconciliación del registro viejo
            $peticionReconciliacionAntigua = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
                ->findOneBy(array("registro_nuevo_id" => $registroViejo->getId()));

            if (isset($peticionReconciliacionAntigua)) {
                $registroViejo = $peticionReconciliacionAntigua->getRegistroViejoEntity();

            } else {
                $registroViejo = null;
                $procesarReconciliaciones = false;
            }

            $i--;
        }

        $registroViejo = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($id);

        $procesarReconciliaciones=TRUE;
        $i=1;

        while ($procesarReconciliaciones) {
            // Ver una posible reconciliación del registro viejo
            $peticionReconciliacionAntigua = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
                ->findOneBy(array("registro_viejo_id" => $registroViejo->getId()));

            if (isset($peticionReconciliacionAntigua)) {
                $registroViejo = $peticionReconciliacionAntigua->getRegistroViejoEntity();

                $subcat = $registroViejo->getMasterWorkflowEntity()->getCategory()->getName();
                $name = $registroViejo->getMasterWorkflowEntity()->getName();

                $element = array(
                    "id" => $registroViejo->getId(),
                    "subcat" => $subcat,
                    "name" => $name,
                    "status" => $registroViejo->getStatus(),
                    "fecha" => $registroViejo->getModified()
                );
                $documentsReconciliacion[$i] = $element;

            } else {
                $registroViejo = null;
                $procesarReconciliaciones = false;
            }

            $i++;
        }
        
        return $this->render('NononsenseHomeBundle:Contratos:reconciliacion_history.html.twig', array(
            "documentsReconciliacion" => $documentsReconciliacion,
            "name" => $name,
            "id" => $id));
    }

    private function createPDF($filenamedownloadpdf, $firststep, $stepDataValuesJSON)
    {
        /*
         * Crear el primer PDF
         */

        $step_id = $firststep->getId();
        $registroid = $firststep->getInstanciaWorkflow()->getId();

        //$dataResponse->step_id = $step_id;

        $name = $firststep->getMasterStep()->getName();
        $template = $firststep->getMasterStep()->getPlantillaId();

        $filenamepdf = $name . $registroid . "_" . $step_id;

        $aux = new Classes\Auxiliar();
        $utils = new Classes\Utils();

        $options = array();
        $options['template'] = (int)$template;
        $options['documentName'] = $filenamepdf;
        $options['response'] = 'json';

        $dataDXO = json_encode($stepDataValuesJSON);

        $options['data'] = $dataDXO;
        $options['docFormat'] = 'pdf';
        $options['name'] = $filenamepdf . '.pdf';
        $options['reference'] = $filenamepdf;

        $opt = $this->get('app.sdk')->base64_encode_url_safe(json_encode($options));

        //generate security info
        $uniqid = uniqid() . rand(99999, 9999999);
        $timestamp = time();
        $control = $template . '-';
        $control .= $timestamp . '-' . $uniqid;
        $control .= '-' . $opt;

        $dataKey = sha1($control, true);
        $masterKey = $this->getParameter('apikey');
        $APIKEY = bin2hex($utils->sha1_hmac($masterKey, $dataKey));

        //we should now redirect to Docxpresso
        $url = $this->getParameter('docxpresso_installation') . '/documents/requestDocument/' . $template;
        $addr = $url . '?';
        $addr .= 'uniqid=' . $uniqid . '&';
        $addr .= 'timestamp=' . $timestamp . '&';
        $addr .= 'APIKEY=' . $APIKEY;

        $curlResponse = $aux->curlRequest($addr, $opt);

        if ($curlResponse['status'] != 'OK') {
            //handle the error
            //exit('error');
            echo "</br>Error</br>";
            print_r("</br>" . $curlResponse['externalData']);


        } else {
            $response = json_decode($curlResponse['externalData']);
            //print_r($response);

            $usageId = $response->usageId;
            $token = $response->token;
            $name = $response->name;

            $dataDonwload = array();
            $dataDonwload['id'] = $template;
            $dataDonwload['token'] = $token;
            $documentLink = $this->get('app.sdk')->downloadDocument($dataDonwload);

            if (file_exists($filenamedownloadpdf)) {
                unlink($filenamedownloadpdf);
            }

            if (file_put_contents($filenamedownloadpdf, fopen($documentLink, 'r')) === FALSE) {

            } else {

            }
        }
    }
}