<?php

namespace Nononsense\DataDocumentBundle\Controller;

/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 10/04/2018
 * Time: 11:29
 */

use Nononsense\HomeBundle\Entity\MetaData;
use Nononsense\HomeBundle\Entity\RevisionStep;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nononsense\HomeBundle\Entity\MasterSteps;

use Nononsense\UtilsBundle\Classes\Auxiliar;
use Nononsense\UtilsBundle\Classes\Utils;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Docxpresso;


class DataDocumentController extends Controller
{

    public function getDataURIRequestAction($instancia_step_id)
    {

        // get the InstanciasSteps entity
        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($instancia_step_id);

        $master_step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:MasterSteps')
            ->find($step->getMasterStepId());

        $instancia_workflow = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($step->getWorkflowId());

        $request = Request::createFromGlobals();
        $postData = $request->request->all();

        // WARGING SECURITY MISSING DONT FORGET GUS

        $data = "{}";
        foreach ($postData as $key => $value) {
            ${$key} = $value;
        }
        $dataJSON = json_decode($data);

        $em = $this->getDoctrine()->getManager();


        /*
         * Update master-data if step is master of that data
         */
        $stepMasterData = $master_step->getStepData();
        if (!empty($stepMasterData)) {
            $stepMasterDataJSON = json_decode($stepMasterData);

            $workflowDataMaster = $instancia_workflow->getMasterDataValues();
            $workflowDataMasterJSON = json_decode($workflowDataMaster);

            foreach ($stepMasterDataJSON as $variable) {

                $nameVariable = $variable->nameVar;
                $stepmaster = $variable->step;

                if ($stepmaster == $master_step->getId()) {
                    // this document is master of this data -> Update value
                    // ¿error if empty?
                    if (!empty($dataJSON->varValues->{$nameVariable})) {
                        /*
                         * Process values
                         * Problem with radio button
                         */
                        $arrayValues = $dataJSON->varValues->{$nameVariable};
                        /*
                        $arrayAux = array();
                        foreach ($arrayValues as $val) {
                            if ($val == "") {
                                $arrayAux[] = "    ";
                            } else {
                                $arrayAux[] = $val;
                            }
                        }
                        */
                        $workflowDataMasterJSON->{$nameVariable}->valueVar = $arrayValues;

                    }
                }
            }

            $workflowDataMaster = json_encode($workflowDataMasterJSON);
            $instancia_workflow->setMasterDataValues($workflowDataMaster);
        }

        /*
         * Si el status ya es 1 es que lo que se está haciendo es validar. Ya que no se permite un segundo rellenado.
         * Se pisan los valores y ahora el status es 2 y el workflow pasa a validado 3 . Y su validación a "validada" 2
         */

        $now = new \DateTime();


        if ($instancia_workflow->getMasterWorkflow() == 16) {
            // Reconciliacion
            $reconciliacionRegistro = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
                ->findOneBy(array("solicitud_id" => $instancia_workflow->getId()));

            if (isset($dataJSON->varValues->FLL_opt1)) {
                $FLL_opt1 = $dataJSON->varValues->FLL_opt1;
                $FLL_opt2 = $dataJSON->varValues->FLL_opt2;

                if (in_array('Si', $FLL_opt1)) {
                    // O mejor por masterworkflow y poner estado "reconciliado"

                    $registroViejoId = $reconciliacionRegistro->getRegistroViejoId();

                    $registroViejo = $this->getDoctrine()
                        ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                        ->find($registroViejoId);

                    $registroViejo->setStatus(9);
                    $reconciliacionRegistro->setStatus(1);

                    $em->persist($registroViejo);

                }
                if (in_array('No', $FLL_opt2)) {
                    $reconciliacionRegistro->setStatus(2);
                }
                $em->persist($reconciliacionRegistro);
            }
        }

        /*
         * Actualizar metaData según variables
         */
        $varValues = $dataJSON->varValues;

        $workorderSAP = "";
        $equipo = "";
        $lote = "";
        $material = "";
        $codigo_documento_lote = "";

        foreach ($varValues as $prop => $value) {

            switch ($prop) {
                /*
                 * Falta codigo_documento_lote
                 */
                case "u_lote":
                    $lote = rawurldecode($value[0]);
                    break;
                case "u_material":
                    $material = rawurldecode($value[0]);
                    break;
                case "u_codigo":
                    $lote = rawurldecode($value[0]);
                    break;
                case "u_batch":
                    $lote = rawurldecode($value[0]);
                    break;
                case "u_SAP":
                    $workorderSAP = rawurldecode($value[0]);
                    break;
                case "u_equipo":
                    $equipo = rawurldecode($value[0]);
                    break;
                default:
                    // do nothing
                    break;
            }
        }

        $metaData = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:MetaData')
            ->findOneBy(array("workflow_id" => $instancia_workflow->getId()));

        $metaData->setWorkordersap($workorderSAP);
        $metaData->setLote($lote);
        $metaData->setMaterial($material);
        $metaData->setEquipo($equipo);
        $metaData->setCodigoDocumentoLote($codigo_documento_lote);

        $em->persist($metaData);

        $data = json_encode($dataJSON);
        $step->setStepDataValue($data);
        $step->setStatusId(1);

        /*
         * Historial real de revisiones.
         */
        $revisionStep = new RevisionStep();
        $revisionStep->setStepDataValue($data);
        $revisionStep->setToken($dataJSON->token);
        $revisionStep->setUsageId($dataJSON->usageId);
        $revisionStep->setStepEntity($step);

        $step->setToken($dataJSON->token);
        $step->setUsageId($dataJSON->usageId);

        $em->persist($revisionStep);
        $em->persist($instancia_workflow);
        $em->persist($step);

        $em->flush();

        //return $this->render('NononsenseDataDocumentBundle:Default:index.html.twig', array('name' => $instancia_step_id));
        $responseAction = new Response();
        $responseAction->setStatusCode(200);
        $responseAction->setContent("OK");
        return $responseAction;
    }

    public function RequestDataAction($instancia_step_id, $logbook,$modo)
    {
        /*
         * Get Value from JSON to put into document
         */

        // get the InstanciasSteps entity
        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($instancia_step_id);

        $master_step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:MasterSteps')
            ->find($step->getMasterStepId());

        $instancia_workflow = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($step->getWorkflowId());
        $instancia_workflowAux = $instancia_workflow;

        $stepMasterData = $master_step->getStepData();
        $workflowMasterData = $instancia_workflow->getMasterDataValues();
        $workflowMasterDataJSON = json_decode($workflowMasterData);
        $em = $this->getDoctrine()->getManager();

        $data = new \stdClass();
        $varValues = new \stdClass();
        $data->varValues = $varValues;
        $completo=0;

        if ($step->getStatusId() == 0) {
            // first usage
            $data = new \stdClass();
            $varValues = new \stdClass();
            $varValues->u_id_cumplimentacion = array($step->getId());

            if (isset($workflowMasterDataJSON)) {
                foreach ($workflowMasterDataJSON as $variable) {
                    $varName = $variable->nameVar;
                    $varValue = $variable->valueVar;

                    $varValues->{$varName} = $varValue;
                }
            }

            /*
             * SENSIBILIDAD
             * 	//IMPORTANTE: todos los valores númericos deben estar en gramos
             */
            if($step->getMasterStepId() == 6){
                $cl_sup = $workflowMasterDataJSON->cl_sup->valueVar[0];
                $cl_inf = $workflowMasterDataJSON->cl_inf->valueVar[0];
                $limite_control = $cl_inf . " - " . $cl_sup;

                $varValues->u_limite_control = array($limite_control);

                $wl_sup = $workflowMasterDataJSON->wl_sup->valueVar[0];
                $wl_inf = $workflowMasterDataJSON->wl_inf->valueVar[0];
                $u_limite_aviso = $wl_inf . " - " . $wl_sup;

                $varValues->u_limite_aviso = array($u_limite_aviso);

                $pesa_chequeo_sensibilidad = $workflowMasterDataJSON->pesa_chequeo_sensibilidad->valueVar[0];
                $varValues->u_pesa = array($pesa_chequeo_sensibilidad);


            }
            /*
             * Repetibilidad
             */
            if($step->getMasterStepId() == 7){
                $pesa_chequeo_sensibilidad = $workflowMasterDataJSON->peso_chequeo_repetibilidad->valueVar[0];
                $varValues->u_pesa = array($pesa_chequeo_sensibilidad);

                $limite_control = $workflowMasterDataJSON->cl_desv_std->valueVar[0];
                $varValues->u_limite_control = array($limite_control);

                $u_limite_aviso = $workflowMasterDataJSON->wl_desv_std->valueVar[0];
                $varValues->u_limite_aviso = array($u_limite_aviso);
            }


            $varValues->historico_steps = array("     ");
            $data->varValues = $varValues;


        } else {
            // Data Ingegrity other usage

            $stepDataValue = $step->getStepDataValue();
            $data = json_decode($stepDataValue);

            $stepMasterDataJSON = json_decode($stepMasterData);

            $workflowMasterDataJSON = json_decode($workflowMasterData);
            $flag = true;
            if (!empty($stepMasterDataJSON) && $flag) {
                foreach ($stepMasterDataJSON as $variables) {
                    $nameVariable = $variables->nameVar;
                    $fisrt = $variables->first;

                    if ($fisrt == "") {
                        if (isset($workflowMasterDataJSON->{$nameVariable})) {
                            if ($workflowMasterDataJSON->{$nameVariable}->valueVar != "") {
                                if ($nameVariable != "pro_razon_social") {
                                    $data->varValues->{$nameVariable} = $workflowMasterDataJSON->{$nameVariable}->valueVar;
                                }
                            } else {
                                $data->varValues->{$nameVariable} = array("     ");
                            }
                        }
                    }
                }
            }
            /*
             * Tendría que haber un protocolo para diferenciar cuando se imprimen sólo las firmas o el histórico completo:
             */

            if($modo == 0){
                $completo = true;
            }else{
                $completo = false;
            }

            if ($completo) {
                $data->varValues->dxo_gsk_audit_trail_bloque = array("Si");
                $data->varValues->dxo_gsk_audit_trail = array($this->_construirHistorico($step));
                $data->varValues->dxo_gsk_firmas_bloque = array("No");

            }

            $mapVariable = $this->_construirMapVariables($step);
            foreach ($mapVariable as $prop => $value) {
                
                $varIndiceName = str_replace("u_", "in_", $prop);
                $varIndiceName = str_replace("verchk_", "in_verchk_", $varIndiceName);

                foreach($value->firma as $key => $indice){
                    if(isset($value->firma[$key])){
                        $data->varValues->{$varIndiceName}[$key] = $value->firma[$key];
                    }
                }


            }
        }

        $firmas = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:FirmasStep')
            ->findBy(array("step_id" => $step->getId()));
        /*
        * Si hay firmas claro.
        */
        if (!empty($firmas) && !$completo) {
            $data->varValues->dxo_gsk_audit_trail_bloque = array("No");
            $data->varValues->dxo_gsk_firmas_bloque = array("Si");
            $data->varValues->dxo_gsk_firmas = array($this->_construirFirmas($firmas));
        }

        $reconciliation=$this->_construirReconciliacion($step);
        if($reconciliation!=""){
            $data->varValues->dxo_gsk_firmas[0].=$reconciliation;
        }

        /*
        $fullText.=$reconciliation."****";
        /*
        var_dump(json_encode($data));
                exit;
        */
        if (isset($data->custom)) {
            unset($data->custom);
        }


        /*
         * Generar los cuatro últimos, si logbook
         */
        $logbokk = true;
        if ($instancia_workflowAux->getMasterWorkflowEntity()->getLogbook() == 1 && $logbokk && $logbook > 0) {
            /*
             * Cargar una serie de datos de registros válidos
             */
            $arrayIds = array();
            $nRegistros = $logbook;
            $mostrados = 0;
            $index = 1;
            $mostrar = true;
            $first = true;
            $fullText = "<table id='tableAnteriores' class='table table-striped' >";

            while ($mostrar) {
                $idMostrar = $instancia_workflowAux->getId() - $index;
                if ($idMostrar != 0) {
                    $InstanciaWorkflowAMostrar = $this->getDoctrine()
                        ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
                        ->findOneBy(array('id' => $idMostrar, 'master_workflow' => $instancia_workflowAux->getMasterWorkflowEntity()->getId()));

                    if (isset($InstanciaWorkflowAMostrar) && $InstanciaWorkflowAMostrar->getStatus() != 20
                        && $InstanciaWorkflowAMostrar->getStatus() != -2) {
                        // registro válido que debo mostrar


                        $instanciaStepAMostrar = $this->getDoctrine()
                            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                            ->findOneBy(array('workflow_id' => $idMostrar));

                        $stepDataValueMostrar = $instanciaStepAMostrar->getStepDataValue();
                        if ($stepDataValueMostrar != "") {
                            $mostrados++;

                            $dataMostrar = json_decode($stepDataValueMostrar);

                            $varValuesMostrar = $dataMostrar->varValues;
                            if ($first) {
                                // pintar cabecera
                                $fullText .= "<tr><th>id Registro</th><th>Estado</th>";
                                foreach ($varValuesMostrar as $prop => $value) {
                                    $position = strpos($prop, "u_");

                                    if ($position === 0) {
                                        // variable válida.

                                        $fullText .= '<th>' . $prop . '</th>';
                                    }

                                }
                                $fullText .= '</tr>';

                                $first = false;
                            }
                            $estadoString = $this->processRowValue($InstanciaWorkflowAMostrar->getStatusString());

                            $fullText .= '<tr><td>' . $instanciaStepAMostrar->getId() . '</td><td>' . $estadoString . '</td>';
                            foreach ($varValuesMostrar as $prop => $value) {
                                $position = strpos($prop, "u_");

                                if ($position === 0) {
                                    // variable válida.
                                    $valor = $value[0];
                                    $fullText .= '<td>' . $valor . '</td>';
                                }

                            }
                            $fullText .= '</tr>';
                        }


                    }
                    $index++;
                } else {
                    $mostrar = false;
                }

                if ($mostrados == $nRegistros) {
                    $mostrar = false;
                }
            }
            $fullText .= '</table>';
            $data->varValues->dxo_gsk_logbook = array("Si");
            $data->varValues->dxp_gsk_logbook_bloque = array($fullText);
            //$data->varValues->dxp_gsk_logbook_bloque = array('1');

        }
        //var_dump(json_encode($data));
        //echo 'venga ya!';
        //exit;

        /*
                var_dump($instancia_workflowAux->getMasterWorkflowEntity()->getName());
                echo '-separate-';
                var_dump($instancia_workflowAux->getMasterWorkflowEntity()->getLogbook());
                var_dump($fullText);
                exit;*/

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent(json_encode($data));

        return $response;
    }

    public function descargarpdfdocumentoAction($workflow_id, $step_id)
    {
        /*
         *
         */

        $dataResponse = new \stdClass();
        $dataResponse->workflow_id = $workflow_id;
        $dataResponse->step_id = $step_id;

        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($step_id);

        $instancia_workflow = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($workflow_id);

        $name = $step->getMasterStep()->getName();

        $template = $step->getMasterStep()->getPlantillaIdByYear($instancia_workflow->getYear());


        $stepDataValues = $step->getStepDataValue();
        $stepDataValuesJSON = json_decode($stepDataValues);

        $rootdir = $this->get('kernel')->getRootDir();
        $rootdirFiles = $rootdir . "/../web/Files";

        $filenamedownloadpdf = $rootdirFiles . "/" . $name . $workflow_id . "_" . $step_id . ".pdf";
        $filenamepdf = $name . $workflow_id . "_" . $step_id;

        $aux = new Auxiliar();
        $utils = new Utils();

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
            //print_r($curlResponse);

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
                $responseAction = new Response();
                $responseAction->setStatusCode(200);
                $dataResponse->feedback = "Fichero descargado correctamente";
                $responseAction->setContent(json_encode($dataResponse));
            }
        }

        return $responseAction;
    }


    public function getpdfdocumentoAction($workflow_id, $step_id)
    {
        $step = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasSteps')
            ->find($step_id);

        $name = $step->getMasterStep()->getName();

        $rootdir = $this->get('kernel')->getRootDir();
        $rootdir .= "/../web/Files";

        $filenamedownloadpdf = $rootdir . "/" . $name . $workflow_id . "_" . $step_id . ".pdf";

        $response = $this->descargarpdfdocumentoAction($workflow_id, $step_id);

        if ($response->getStatusCode() == 200) {

            $responseFile = new BinaryFileResponse($filenamedownloadpdf);
            $responseFile->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $name . ".pdf"
            );
            return $responseFile;
        } else {
            return $response;
        }
    }

    private function _construirHistorico($step)
    {
        /*
         * Similar al de las firmas
         * Se obtiene el listado de "evidencias"
         * En el caso de la primera se pinta la firma. Se mantiene una lastEvidencia como entidad.
         * En el caso de las siguientes se compara los valores de las variables llamadas "validas" aquellas que comienzan por u_
         * Si son diferentes se pinta la comparación (¿tabla dentro de tabla?)
         */
        $evidencias = $step->getEvidenciasStep();
        $first = true;
        $lastEvidencia = null;

        $fullText = "<table id='tableHistorico' class='table table-striped' >";
        foreach ($evidencias as $evidenciaElement) {
            $firmaAsociada = $evidenciaElement->getFirmaEntity();
            if ($first) {

                $id = $firmaAsociada->getNumber();
                $nombre = $firmaAsociada->getUserEntiy()->getName();
                $fecha = $firmaAsociada->getCreated()->format('d-m-Y H:i:s');
                $comentario = $firmaAsociada->getAccion();
                $firmaImagen = $firmaAsociada->getFirma();

                $fullText .= "<tr><td>" . $id . "</td><td>" . $nombre . " " . $fecha . "</td><td>Comentarios: " . $comentario . "</td><td><img src='" . $firmaImagen . "' /></td></tr>";
                $lastEvidencia = $evidenciaElement;
                $first = false;

            } else {
                // Primero descifrar si ha habido cambios.
                $dataStringCurrent = $evidenciaElement->getStepDataValue();
                $dataJsonCurrent = json_decode($dataStringCurrent);

                $currentVarValues = $dataJsonCurrent->varValues;


                $dataString = $lastEvidencia->getStepDataValue();
                $dataJson = json_decode($dataString);
                $lastVarValues = $dataJson->varValues;

                $bloqueHTML = "<tr><td colspan='4'>Modificaciones</td></tr><tr><td rowspan='###NUMBERREPLACE###'>" . $firmaAsociada->getNumber() . "</td><td>Campo</td><td>Antes</td><td>Después</td></tr>";
                $modified = false;
                $counterModified = 1;

                foreach ($currentVarValues as $prop => $value) {
                    $position = strpos($prop, "u_");
                    $positionV = strpos($prop, "verchk_");

                    if ($position === 0 || $positionV === 0) {
                        // variable válida.
                        $lastValue = trim(implode("", $lastVarValues->{$prop})); // Para que funcione en los "checboxes" y "radioButton" habría que hacer un implode + trim
                        // if lastValue es un valor vacío no haría falta hacer un "modificado"
                        $currentValue = trim(implode("", $value));

                        if ($lastValue != "") {
                            $audittrail=1;
                            foreach($dataJsonCurrent->data as $field){
                                if($field->name==$prop){
                                    if($lastValue == $field->label){
                                        $lastValue="";
                                    }
                                    if($currentValue == $field->label){
                                        $currentValue="";
                                    }

                                    if($field->tip!=""){
                                        $info=$field->tip;
                                    }
                                    else{
                                        $info=$prop;
                                    }
                                }
                            }

                            if ($lastValue != $currentValue && $audittrail) {
                                $counterModified++;
                                $modified = true;
                                $bloqueHTML .= "<tr><td>" . $info . "</td><td>" . $lastValue . "</td><td>" . $currentValue . "</td></tr>";
                            }
                        }
                    }
                }
                
                if ($modified) {
                    $bloqueHTML = str_replace('###NUMBERREPLACE###', $counterModified, $bloqueHTML);
                    $fullText .= $bloqueHTML;
                }
                // Después añadir la fila de firma sin mas.
                $id = $firmaAsociada->getNumber();
                $nombre = $firmaAsociada->getUserEntiy()->getName();
                $fecha = $firmaAsociada->getCreated()->format('d-m-Y H:i:s');
                $comentario = $firmaAsociada->getAccion();
                $firmaImagen = $firmaAsociada->getFirma();

                $fullText .= "<tr><td colspan='4'>Firma</td></tr><tr><td>" . $id . "</td><td>" . $nombre . " " . $fecha . "</td><td>Comentarios: " . $comentario . "</td><td><img src='" . $firmaImagen . "' /></td></tr>";
                $lastEvidencia = $evidenciaElement;

            }

        }

        $fullText .= "</table>";

        return $fullText;
    }

    private function _construirReconciliacion($step){
        $id=$step->getWorkflowId();
        $current_id=$step->getId();

        $registroViejo = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($id);

        $name=$registroViejo->getMasterWorkflowEntity()->getName();
        $documentsReconciliacion=array();
        $peticionReconciliacionAntigua=NULL;

        $procesarReconciliaciones=TRUE;
        $i=0;
        $txhash="";
        while ($procesarReconciliaciones) {
            if ($registroViejo != null) {
                if($peticionReconciliacionAntigua){
                    $txhash=$peticionReconciliacionAntigua->getTxhash();
                }
                $subcat = $registroViejo->getMasterWorkflowEntity()->getCategory()->getName();
                $name = $registroViejo->getMasterWorkflowEntity()->getName();

                $element = array(
                    "id" => $registroViejo->getId(),
                    "subcat" => $subcat,
                    "name" => $name,
                    "status" => $registroViejo->getStatus(),
                    "fecha" => $registroViejo->getModified(),
                    "id_grid" => $step->getId(),
                    "txhash" => $txhash
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

                $step = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                ->findOneBy(array("workflow_id" => $registroViejo->getId(), "dependsOn" => 0));

            } else {
                $registroViejo = null;
                $procesarReconciliaciones = false;
            }

            $i--;
        }

        $registroNuevo = $this->getDoctrine()
            ->getRepository('NononsenseHomeBundle:InstanciasWorkflows')
            ->find($id);

        $procesarReconciliaciones=TRUE;
        $i=1;

        while ($procesarReconciliaciones) {
            // Ver una posible reconciliación del registro viejo
            $peticionReconciliacionNueva = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:ReconciliacionRegistro')
                ->findOneBy(array("registro_viejo_id" => $registroNuevo->getId()));

            if (isset($peticionReconciliacionNueva)) {
                $registroNuevo = $peticionReconciliacionNueva->getRegistroNuevoEntity();

                $step = $this->getDoctrine()
                ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                ->findOneBy(array("workflow_id" => $registroNuevo->getId(), "dependsOn" => 0));

                $subcat = $registroNuevo->getMasterWorkflowEntity()->getCategory()->getName();
                $name = $registroNuevo->getMasterWorkflowEntity()->getName();

                $element = array(
                    "id" => $registroNuevo->getId(),
                    "subcat" => $subcat,
                    "name" => $name,
                    "status" => $registroNuevo->getStatus(),
                    "fecha" => $registroNuevo->getModified(),
                    "id_grid" => $step->getId(),
                    "txhash" => $peticionReconciliacionNueva->getTxhash()
                );
                $documentsReconciliacion[$i] = $element;

            } else {
                $registroNuevo = null;
                $procesarReconciliaciones = false;
            }

            $i++;
        }

        $html="";

        if(count($documentsReconciliacion)>1){

            ksort($documentsReconciliacion);

            $html="<br><br><table class='table table-striped' style='font-size:11px'><tr><th colspan='4'>Reconciliación</th></tr><tr><td>Nº</td><td>Nombre</td><td>Estado</td><td>Fecha</td></tr>";
            foreach($documentsReconciliacion as $key => $element){
                $url=$this->container->get('router')->generate('nononsense_ver_registro', array('revisionid' => $element["id"]),TRUE);
                if($current_id!=$element["id_grid"]){
                    $html.="<tr><td>".$element["id_grid"]."</td><td><a href='".$url."' target='_blank'>".$element["name"]."</a></td>";
                }
                else{
                    $html.="<tr><td>".$element["id_grid"]."</td><td><b>".$element["name"]."</b></td>";
                }
                    if($element["status"]==0){
                        $html.='<td>Iniciado</td>';
                    }
                    elseif($element["status"]==1){
                        $html.='<td>Esperando firma guardado parcial</td>';
                    }
                    elseif($element["status"]==2){
                        $html.='<td>Esperando firma envío</td>';
                    }
                    elseif($element["status"]==3){
                        $html.='<td>Esperando firma cancelación</td>';
                    }
                    elseif($element["status"]==4){
                        $html.='<td>En verificación</td>';
                    }
                    elseif($element["status"]==5){
                        $html.='<td>Pendiente cancelación en edición</td>';
                    }
                    elseif($element["status"]==6){
                        $html.='<td>Cancelado en edición</td>';
                    }
                    elseif($element["status"]==7){
                        $html.='<td>Esperando firma verificación total</td>';
                    }
                    elseif($element["status"]==8){
                        $html.='<td>Cancelado</td>';
                    }
                    elseif($element["status"]==9){
                        $html.='<td>Archivado</td>';
                    }
                    elseif($element["status"]==10){
                        $html.='<td>Reconciliado</td>';
                    }
                    elseif($element["status"]==11){
                        $html.='<td>Bloqueado</td>';
                    }
                    elseif($element["status"]==12){
                        $html.='<td>Esperando firma cancelación en verificación</td>';
                    }
                    elseif($element["status"]==13){
                        $html.='<td>Esperando firma devolución a edición</td>';
                    }
                    elseif($element["status"]==14){
                        $html.='<td>Pendiente de cancelación en verificación</td>';
                    }
                    elseif($element["status"]==15){
                        $html.='<td>Esperando firma verificación parcial</td>';
                    }
                    elseif($element["status"]==16){
                        $html.='<td>Esperando autorización para reconciliación</td>';
                    }
                    else{
                        $html.='<td>'.$element["status"].'</td>';
                    }

                    $html.='<td>'.$element["fecha"]->format('d/m/Y H:i:s').'</td>';

                $html.='</tr>';
            }   
            $html.='</table>';
        }

        return $html;
    }

    private function _construirFirmas($firmas)
    {
        //$firmas => array entidad firmas
        $fullText = "<table id='tablefirmas' class='table table-striped'>";

        foreach ($firmas as $firma) {
            $id = $firma->getNumber();
            $nombre = $firma->getUserEntiy()->getName();
            $fecha = $firma->getCreated()->format('d-m-Y H:i:s');
            $comentario = $firma->getAccion();
            $firma = $firma->getFirma();


            $fullText .= "<tr><td colspan='4'>Firma</td></tr><tr><td>" . $id . "</td><td>" . $nombre . " " . $fecha . "</td><td>Comentarios: " . $comentario . "</td><td><img src='" . $firma . "' /></td></tr>";
        }

        $fullText .= "</table>";
        return $fullText;

    }

    private function _construirMapVariables($step)
    {
        /*
         * Generar un mapeado con el nomrbe de las variables
         * - Valor
         * - indice de la firma
         * Se realiza un bucle for, por todas las evidencias. En el primer caso se rellenan por defecto con el primero
         * Si en un paso el valor ha cambiado, se sustituye el id de la firma.
         */
        $mapVariable = new \stdClass();

        $evidencias = $step->getEvidenciasStep();
        $first = true;

        foreach ($evidencias as $evidenciaElement) {
            $dataString = $evidenciaElement->getStepDataValue();
            $dataJson = json_decode($dataString);

            $varValues = $dataJson->varValues;

            $firmaId = $evidenciaElement->getFirmaEntity()->getNumber();

            if ($first) {

                foreach ($varValues as $prop => $values) {
                    $position = strpos($prop, "u_");
                    $positionV = strpos($prop, "verchk_");

                    if ($position === 0 || $positionV === 0 ) {
                        // variable válida.
                        if (!isset($mapVariable->{$prop}) ||
                            empty($mapVariable->{$prop})) {
                            $mapVariable->{$prop} = new \stdClass();
                        }

                        if (isset($mapVariable->{$prop}->valor)) {
                            $mapVariable->{$prop}->valor = new \stdClass();
                        }

                        if (isset($mapVariable->{$prop}->firma)) {
                            $mapVariable->{$prop}->firma = new \stdClass();
                        }

                        $mapVariable->{$prop}->valor = $values;
                        foreach($values as $key => $value){
                            $mapVariable->{$prop}->firma=array($firmaId);
                        }
                        //$mapVariable->{$prop}->firma = $firmaId;
                    }

                }

                $first = false;
            } else {
                //$fin=0;
                foreach ($varValues as $prop => $values) {
                    $position = strpos($prop, "u_");
                    $positionV = strpos($prop, "verchk_");

                    if ($position === 0 || $positionV === 0) {
                        // variable válida.
                        $modificado=-1;
                        foreach($values as $key => $value){
                            
                            if(isset($mapVariable->{$prop}->valor[$key])){
                                $currentValue = $mapVariable->{$prop}->valor[$key]; // solo se están guardando los valores unitarios
                            }
                            else{
                                $currentValue="";
                            }
                            if ($currentValue != $value) {
                                // Modificado
                                if($firmaId>$mapVariable->{$prop}->firma[$key]){
                                    $modificado=$key;
                                    $mapVariable->{$prop}->firma[$modificado]=$firmaId;
                                }
                            }
                        }
                        if($modificado>-1){
                           $mapVariable->{$prop}->valor = $values; 
                        }
                    }
                }
            }
        }

        return $mapVariable;

    }

    private function _parseValue($value)
    {
        switch ($value) {
            case "u_comentar":
                $newValue = "";
                break;
            default:
                $newValue = $value;
        }
        return $newValue;
    }

    private function _parseProp($prop)
    {
        switch ($prop) {
            case "u_limpieza":
                $newProp = "Limpieza";
                break;
            case "u_observacion":
                $newProp = "Observaciones";
                break;
            case "u_comentarios":
                $newProp = "Comentarios";
                break;
            case "u_comentar":
                $newProp = "Justificacion";
                break;

            default:
                $newProp = $prop;
        }
        return $newProp;
    }

    private function processRowValue($cadena)
    {
        $cadena = html_entity_decode($cadena);
        $cadena = str_replace("&", "&amp;", $cadena);
        $cadena = str_replace("&amp;amp;", "&amp;", $cadena);

        $utils = new Utils();
        if (!$utils->UTF8Encoded($cadena)) {
            $cadena = utf8_encode($cadena);
        }

        $cadena = rawurlencode($cadena);

        $cadena = str_replace("%C2%80", "%E2%82%AC", $cadena);
        return $cadena;
    }
}