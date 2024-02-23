<?php

namespace Nononsense\UtilsBundle\Classes\JSON;

/**
 * Methods to manipulate Docxpresso JSON Data
 */
use Nononsense\UtilsBundle\Classes\Utils;

class Tools
{

    public function __construct($em = NULL, $container)
    {
        //doctrine manager
        $this->em = $em;
        $this->container = $container;

        /*
         * Construir excepciones
         */
        $exceptions = new \stdClass();
        $exceptions->name = new \stdClass();
        $exceptions->type = new \stdClass();

        $exceptions->name->opt_pago = new \stdClass();
        $exceptions->name->opt_pago->default = "formapago"; //0 no hacer nada.

        /*
         * Opcion 1:
         *
        $exceptions->name->opt_pago->includes = array("80", "205");
        $exceptions->name->opt_pago->includesTemplates = new \stdClass();
        $exceptions->name->opt_pago->includesTemplates->ms159 = 25;
        $exceptions->name->opt_pago->includesTemplates->ms205 = 359;
        */
        /*
         * Opcion 2:
         */
        $exceptions->name->opt_pago->ms79 = "ejemplo1";

        $exceptions->name->opt_pago->excludes = array("7"); // No hacer nada

        $exceptions->type->date = new \stdClass();
        $exceptions->type->date->default = "formatofecha";

        /*
         * Excepcion cambio formato variable ABEL
         */
        $exceptions->name->opcion = new \stdClass();
        $exceptions->name->opcion->default = "formatoarray";

        $this->excepciones = $exceptions;

    }

    /**
     * Parses the JSON data of a template
     * Takes the full data JSON and returns the parsed varValues where the
     * variables that are hidden in the associated document are
     *  removed if $format equals remove
     *  set to NULL if $format equals nullify
     *  emptied (empty array) if format is empty
     *
     * @param string|object $data
     * @param string $format
     * @return object
     */

    public function parseVarValues($data, $template, $format = 'nullify')
    {
        $dataInfo = new \stdClass();

        if (is_string($data)) {
            $data = json_decode($data);
            /*
             * Parse types
             */
            $ObjetDataToParse = $data->data;
            foreach ($ObjetDataToParse as $variableInfo) {
                $dataInfo->{$variableInfo->name} = new \stdClass();
                $dataInfo->{$variableInfo->name}->type = $variableInfo->type;
            }

        }
        $varValues = $data->varValues;
        $active = $data->validations->variables->active;
        //run over $varValues props
        foreach ($varValues as $prop => $value) {
            if (isset($active->{$prop}) && !empty($active->{$prop})) {
                //we just check at the first array entry because Docxpresso
                //does not, in principle, for cloned values with different
                //visibility attributes
                if (!$active->{$prop}[0]) {
                    //this field is not active
                    if ($format == 'remove') {
                        unset($varValues->{$prop});
                    } else if ($format == 'nullify') {
                        $varValues->{$prop} = NULL;
                    } else if ($format == 'empty') {
                        $varValues->{$prop} = array();
                    }
                } else {


                }


            }
            $valueArray = $varValues->{$prop};

            if (is_array($valueArray)) {
                $newArray = array();
                foreach ($valueArray as $element) {
                    if (is_array($element)) {
                        foreach ($element as $element2) {
                            $newArray[] = $this->parseVariable($element2, $prop, $template, $dataInfo->{$prop}->type);
                        }
                    } else {
                        $newArray[] = $this->parseVariable($element, $prop, $template, $dataInfo->{$prop}->type);

                    }
                }
                $varValues->{$prop} = $newArray;
            }
        }

        /*
         * Parse prop ¿?
         */
        $varValuesParsed = new \stdClass();
        foreach ($varValues as $prop => $value) {
            $label = $this->parseProp($prop, $template);
            $varValuesParsed->{$label} = $value;
        }

        return $varValuesParsed;

    }

    /**
     *
     * Process varValues, modifica algunas variables a petición de integración.
     *
     * @param string|object $values
     * @return object
     */

    public function processVarValues($values, $template)
    {
        $masterStepId = $template->getMasterStep()->getId();
        switch ($masterStepId) {
            case 102:
                /*
                 * Atípicos 1
                 * Concatenar las siguientes variables como "eurosatipicos1"
                 * maneurosatipicos1
                 * deseurosatipicos1
                 * apeurosatipicos1
                 */
                $maneurosatipicos1 = $values->maneurosatipicos1;
                $deseurosatipicos1 = $values->deseurosatipicos1;
                $apeurosatipicos1 = $values->apeurosatipicos1;

                $eurosatipicos1 = array();
                foreach ($maneurosatipicos1 as $element){
                    $eurosatipicos1[] = $element;
                }

                foreach ($deseurosatipicos1 as $element){
                    $eurosatipicos1[] = $element;
                }

                foreach ($apeurosatipicos1 as $element){
                    $eurosatipicos1[] = $element;
                }

                $values->eurosatipicos1 = $eurosatipicos1;

                break;
        }
        return $values;
    }

    /**
     * Parse prop
     *
     * Parse variable label
     */
    private function parseProp($prop, $template)
    {
        $masterStepId = $template->getMasterStep()->getId();
        switch ($masterStepId) {
            case 33:
            case 153:
            case 122:
                /*
                 * Condiciones particulares CMA
                 * CMA modificaciones
                 * Atípicos 3
                 */
                switch ($prop) {
                    case "escala2por1":
                        $newProp = "c4_escalainicio";
                        break;
                    case "escala2por2":
                        $newProp = "c4_escalafin";
                        break;
                    case "total_1":
                        $newProp = "c4_total";
                        break;
                    case "observaciones":
                        $newProp = "c4_observaciones";
                        break;
                    case "bloquefechas":
                        $newProp = "c5_bloquefechas";
                        break;
                    case "Descripcion2":
                        $newProp = "c5_Descripcion_Dos";
                        break;
                    case "Porcentaje_Total_Facturacion":
                        $newProp = "c5_Porcentaje_Total_Facturacion_Dos";
                        break;
                    case "Descripcion":
                        $newProp = "c5_Descripcion_Uno";
                        break;
                    case "Porcentaje_Total_Facturacion2":
                        $newProp = "c5_Porcentaje_Total_Facturacion_Uno";
                        break;
                    case "opt_c5b":
                        $newProp = "opt_c6";
                        break;
                    case "c5b_idsec":
                        $newProp = "c6_idsec";
                        break;
                    case "c5b_seccion":
                        $newProp = "c6_seccion";
                        break;
                    case "c5b_codigo_proveedor":
                        $newProp = "c6_codigo_proveedor";
                        break;
                    case "c5b_opcion":
                        $newProp = "c6_tipoproducto";
                        break;
                    case "seleccionar_unidad_c5":
                        $newProp = "c6_seleccionar_unidad";
                        break;
                    case "valor1_c5":
                        $newProp = "c6_valorinicio";
                        break;
                    case "valor2_c5":
                        $newProp = "c6_valorfin";
                        break;
                    case "porcentaje":
                        $newProp = "c6_porcentaje";
                        break;
                    case "c5b_jerarquia_incluye":
                        $newProp = "c6_jerarquia_incluye";
                        break;
                    case "c5b_articulo_incluye":
                        $newProp = "c6_articulo_incluye";
                        break;
                    case "c5b_jerarquia_excluye":
                        $newProp = "c6_jerarquia_excluye";
                        break;
                    case "c5b_articulo_excluye":
                        $newProp = "c6_articulo_excluye";
                        break;
                    case "c4b_seccion":
                        $newProp = "c7_seccion";
                        break;
                    case "c4b_codigo_proveedor":
                        $newProp = "c7_codigo_proveedor";
                        break;
                    case "c4b_opcion":
                        $newProp = "c7_opcion";
                        break;
                    case "opt_lineal":
                        $newProp = "c7_opt_lineal";
                        break;
                    case "selec_tantoporciento":
                        $newProp = "c7_selec_tantoporciento";
                        break;
                    case "selec_euros":
                        $newProp = "c7_selec_euros";
                        break;
                    case "c4_opcion":
                        $newProp = "c4_tipoproducto";
                        break;
                    default:
                        $newProp = $prop;
                        break;
                }
                $label = $newProp;

                break;
            default:
                $label = $prop;
                break;
        }

        return $label;
    }


    /**
     * Parse variable.
     * urldecode
     * strip html tags
     *
     * @param string that repret value to parse
     * @param string that repret name of the value
     * @param InstanciasSteps that repret step
     *
     * @return string
     */
    public function parseVariable($element, $prop, $template, $type)
    {
        /*
         * Exceptions
         * Plantilla negociación cargo manual, ms = 80. La variable opt_pago, sustituir el 1 por 0 y el 2 por 5.
         */

        $element = urldecode($element);
        $element = strip_tags($element);

        $exceptions = $this->excepciones;

        if (($template != null) && ($prop != null)) {

            if (isset($exceptions->name->{$prop})) {
                $masterStepJsonValue = "ms" . $template->getMasterStep()->getId();

                $excepcionId = $exceptions->name->{$prop}->default;

                if (isset($exceptions->name->{$prop}->excludes)) {
                    $arrayExcludes = $exceptions->name->{$prop}->excludes;

                    if (in_array($template->getMasterStep()->getId(), $arrayExcludes)) {
                        $excepcionId = "none";
                    }
                }

                if (isset($exceptions->name->{$prop}->{$masterStepJsonValue})) {
                    $excepcionId = $exceptions->name->{$prop}->{$masterStepJsonValue};

                }

                switch ($excepcionId) {
                    case "none":
                        // Do nothing
                        break;
                    case "formapago":
                        //Plantilla negociación cargo manual, ms = 80. La variable opt_pago, sustituir el 1 por 0 y el 2 por 5.
                        if ($element == "1") {
                            $element = "0";
                        } else if ($element == "2") {
                            $element = "5";
                        }

                        break;
                    case "ejemplo1":
                        // Pruebas
                        $element = "método de pruebas";
                        break;
                    case "formatoarray":
                        // Se ha pedido que se cambie el formato, que es un string separado por , a un array.
                        $element = explode(",", $element);

                        break;
                    default:
                        break;
                }

            }
        }

        if (isset($exceptions->type->{$type})) {
            $excepcionId = $exceptions->type->{$type}->default;
            setlocale(LC_TIME, "es_ES");
            switch ($excepcionId) {
                case "none":
                    // Do nothing
                    break;
                case "formatofecha":
                    // Si la fecha es dd/mm/aaaa procesar ...

                    if (strpos($element, "/") !== false) {
                        $elementSplitted = explode("/", $element);
                        $element = $this->getdateFormated($elementSplitted[0], $elementSplitted[1], $elementSplitted[2]);


                    } elseif (strpos($element, "-") !== false) {
                        $elementSplitted = explode("-", $element);
                        $element = $this->getdateFormated($elementSplitted[0], $elementSplitted[1], $elementSplitted[2]);
                    }


                    break;
                default:
                    break;
            }
        }

        if (!is_array($element)) {
            $element = urlencode($element);
        }

        return $element;
    }

    /**
     * Creates the envelope for the DAC REST service
     *
     * @param object $contract the contract entity instance
     * @return object
     */

    public function reportingEnvelope($contract)
    {
        $signValues = $contract->getSignvalues();
        $signValuesJSON = json_decode($signValues);
        if (isset($signValuesJSON->datos)) {
            $datos__fecha = $signValuesJSON->datos->fecha;
        } else {
            $datos__fecha = "";
        }

        $idBizlayer = "";
        $bizlayerEntity = $this->em
            ->getRepository('NononsenseHomeBundle:BizlayerEntity')
            ->findOneBy(array("instanciaworkflowid" => $contract->getId()));
        if ($bizlayerEntity != null) {
            $idBizlayer = $bizlayerEntity->getIdBizLayer();
        }


        $workflowDataMaster = $contract->getMasterDataValues();
        $workflowDataMasterJSON = json_decode($workflowDataMaster);

        //create the envelope
        $envelope = new \stdClass();
        //first populate the general data of the contract
        $envelope->id = $contract->getId();
        $envelope->type = $contract->getMasterWorkflow(); //id del tipo de contrato
        $envelope->name = $this->parseVariable($contract->getMasterWorkflowEntity()->getName(), null, null, 'text'); //nombre del tipo de contrato
        $envelope->year = $contract->getYear(); //Año versión contrato¿?
        $envelope->statusDA = $contract->getStatusStringId(); //Estado del DA¿?
        $envelope->bizlayerId = $idBizlayer;


        if ($contract->getStatus() == 10) {
            $envelope->date = $contract->getFechaFirma()->format('Y-m-d');
        } else {
            $envelope->date = "";
        }

        /*
        // En realidad bastaría con el fecha firma, el campo.
        if ($datos_datos_fecha == "") {
            $envelope->date = "";

        } else {
            $testDate = $this->getDateFromFechaApp($datos_datos_fecha);
            $envelope->date = $testDate->format('Y-m-d'); // fecha firma formato: 2018-08-30 // De momento formato firma DAX
        }
        */

        //section info

        $envelope->sections = array();
        /**
         * Loop over the sections
         * the final result should be something like
         * [{"id": 22233, "section": 45, "code": 4567, "type": ["MN", "MP"]},
         *  {"id": 4545454, "section": 45, "code": 4555, "type": ["MN"]}]
         */
        $secciones = $workflowDataMasterJSON->seccion->valueVar;
        $idsec = $workflowDataMasterJSON->idsec->valueVar;
        $codigo_proveedor = $workflowDataMasterJSON->codigo_proveedor->valueVar;
        $opcion = $workflowDataMasterJSON->opcion->valueVar;

        for ($it = 0; $it < sizeof($idsec); $it++) {
            $opcionString = str_replace("%2C", ",", $opcion[$it]);


            $sectionRow = array(
                "id" => $idsec[$it],
                "section" => $secciones[$it],
                "code" => $codigo_proveedor[$it],
                "type" => explode(",", $opcionString) // array
            );
            $envelope->sections[] = $sectionRow;

        }
        $pro_poblacion = "";
        if (isset($workflowDataMasterJSON->pro_poblacion->valueVar[0])) {
            $pro_poblacion = $workflowDataMasterJSON->pro_poblacion->valueVar[0];
        }
        $pro_cif = "";
        if (isset($workflowDataMasterJSON->pro_cif->valueVar[0])) {
            $pro_cif = $workflowDataMasterJSON->pro_cif->valueVar[0];
        }
        $pro_name = "";
        if (isset($workflowDataMasterJSON->pro_razon_social->valueVar[0])) {
            $pro_name = $workflowDataMasterJSON->pro_razon_social->valueVar[0];
        }
        $pro_address = "";
        if (isset($workflowDataMasterJSON->pro_direccion->valueVar[0])) {
            $pro_address = $workflowDataMasterJSON->pro_direccion->valueVar[0];
        }
        $pro_cp = "";
        if (isset($workflowDataMasterJSON->pro_cp->valueVar[0])) {
            $pro_cp = $workflowDataMasterJSON->pro_cp->valueVar[0];
        }
        $pro_province = "";
        if (isset($workflowDataMasterJSON->pro_provincial->valueVar[0])) {
            $pro_province = $workflowDataMasterJSON->pro_provincial->valueVar[0];
        }
        $pro_pais = "";
        if (isset($workflowDataMasterJSON->pro_pais->valueVar[0])) {
            $pro_pais = $workflowDataMasterJSON->pro_pais->valueVar[0];
        }
        $pro_email = "";
        if (isset($workflowDataMasterJSON->pro_email->valueVar[0])) {
            $pro_email = $workflowDataMasterJSON->pro_email->valueVar[0];
        }
        $pro_fax = "";
        if (isset($workflowDataMasterJSON->pro_fax->valueVar[0])) {
            $pro_fax = $workflowDataMasterJSON->pro_fax->valueVar[0];
        }

        $envelope->provider = new \stdClass();
        $envelope->provider->CIF = $pro_cif;
        $envelope->provider->name = $this->parseVariable($pro_name, null, null, 'text');
        $envelope->provider->address = $this->parseVariable($pro_address, null, null, 'text');
        $envelope->provider->city = $this->parseVariable($pro_poblacion, null, null, 'text');
        $envelope->provider->postalcode = $pro_cp;
        $envelope->provider->province = $this->parseVariable($pro_province, null, null, 'text');
        $envelope->provider->country = $this->parseVariable($pro_pais, null, null, 'text');
        if (isset($workflowDataMasterJSON->pro_telf)) {
            $envelope->provider->phone = $workflowDataMasterJSON->pro_telf->valueVar[0];

        } else {
            $pro_tef = "";
            if (isset($workflowDataMasterJSON->pro_tef->valueVar[0])) {
                $pro_tef = $workflowDataMasterJSON->pro_tef->valueVar[0];
            }
            $envelope->provider->phone = $pro_tef;

        }

        $envelope->provider->email = $this->parseVariable($pro_email, null, null, 'text');
        $envelope->provider->fax = $pro_fax;

        //DAC user
        $userCreatedContract = $contract->getUserCreatedEntiy();
        $envelope->user = new \stdClass();
        $envelope->user->id = $userCreatedContract->getId(); //id de ¿red? usuario creador contrato
        $envelope->user->name = $userCreatedContract->getName(); //nombre del usuario creador contrato
        $envelope->user->email = $userCreatedContract->getEmail(); //email del usuario creador contrato

        if ($userCreatedContract->getPosition() == null) {
            $rolUser = '';
        } else {
            $rolUser = $userCreatedContract->getPosition();
        }
        $envelope->user->rol = $rolUser; //perfil del usuario creador contrato

        //
        //$this->getDoctrine()
        $envelope->signers = new \stdClass();
        if (isset($signValuesJSON->datosdatos)) {
            $jefeUser = $this->em
                ->getRepository('NononsenseUserBundle:Users')
                ->findOneBy(array("email" => $signValuesJSON->datosdatos->email));

            //firmantes

            //por datos
            $envelope->signers->datos = new \stdClass();
            $envelope->signers->datos->id = $jefeUser->getUsername(); //id firmante datos..me imagino que el id de red
            $envelope->signers->datos->name = $signValuesJSON->datosdatos->nombre; //nombre firmante datos
            $envelope->signers->datos->email = $signValuesJSON->datosdatos->email; //email firmante datos

            //por proveedor
            $envelope->signers->providers = array();
            /**
             * Loop over providers
             * the final result should be something like
             * [{"email": "dist1@xxx.com", "name": "Juanita"}, {"email": "dist2@yyy.com", "name": "pepito"}]
             */

            $datos_prov1_email = $signValuesJSON->datosProveedor1->email;
            $datos_prov1_name = $signValuesJSON->datosProveedor1->nombre;
            $datos_prov1_apellidos = $signValuesJSON->datosProveedor1->apellidos;

            $envelope->signers->providers[] = array(
                "email" => $datos_prov1_email,
                "name" => $datos_prov1_name . " " . $datos_prov1_apellidos
            );

            $datos_prov2_email = $signValuesJSON->datosProveedor2->email;
            $datos_prov2_name = $signValuesJSON->datosProveedor2->nombre;
            $datos_prov2_apellidos = $signValuesJSON->datosProveedor2->apellidos;

            if ($datos_prov2_email != "") {
                $envelope->signers->providers[] = array(
                    "email" => $datos_prov2_email,
                    "name" => $datos_prov2_name . " " . $datos_prov2_apellidos
                );
            }

            $datos_prov3_email = $signValuesJSON->datosProveedor3->email;
            $datos_prov3_name = $signValuesJSON->datosProveedor3->nombre;
            $datos_prov3_apellidos = $signValuesJSON->datosProveedor3->apellidos;

            if ($datos_prov3_email != "") {
                $envelope->signers->providers[] = array(
                    "email" => $datos_prov3_email,
                    "name" => $datos_prov3_name . " " . $datos_prov3_apellidos
                );
            }
        }


        //por los distribuidores
        $envelope->signers->distributors = $this->getDistributorsInfo($contract);
        /**
         * Loop over distributors
         * the final result should be something like
         * [{"email": "dist1@xxx.com", "CIF": "000565665X"}, {"email": "dist2@yyy.com", "CIF": "68687878D"}]
         */


        //TODO: integrar aquí el addTemplate?
        //used templates
        $envelope->templates = array();
        $steps = $contract->getSteps();
        foreach ($steps as $oneStep) {
            /*
             * Only contract send to sing
             */
            if ($oneStep->getStatusId() == 5 || $oneStep->getStatusId() == 1) {
                /*
                 * Special condition with no reporting templates.
                 * listado de productos etc...
                 *
                 */
                $idMasterStep = $oneStep->getMasterStepId();

                if ($idMasterStep == 38 || $idMasterStep == 131 || $idMasterStep == 148 || $idMasterStep == 83 ||
                    $idMasterStep == 155 || $idMasterStep == 207 || $idMasterStep == 214) {
                    // Special CSV Productos
                    $this->addTemplateFromCSVAnexo3($oneStep, $envelope);

                } elseif ($idMasterStep == 37 || $idMasterStep == 105 || $idMasterStep == 154 || $idMasterStep == 161 || $idMasterStep == 187 || $idMasterStep == 213) {
                    // Special CSV Distribuidores
                    $this->addTemplateFromCSVAnexo2($oneStep, $envelope);

                } else if ($idMasterStep == 159 ||
                    $idMasterStep == 160) {
                    // Special CSV
                    $this->addTemplateFromAnexo5CargoCSV($oneStep, $envelope);

                } else {
                    $this->addTemplate($oneStep, $envelope);

                }
            }
        }

        return $envelope;
    }

    /**
     * Adds a template to an existing envelope
     *
     * @param object $template the contract entity instance
     * @param object $envelope
     * @return void
     */

    public function addTemplate($template, $envelope)
    {

        $data = $template->getStepdatavalue();
        $values = $this->parseVarValues($data, $template);
        $values = $this->processVarValues($values,$template);
        //create the object
        $templateData = new \stdClass();
        //first populate the general data of the template
        $templateData->id = $template->getId();
        $templateData->DX = $template->getMasterStep()->getPlantillaIdByYear($template->getInstanciaWorkflow()->getYear()); //this must be the DX template id?
        $templateData->master_step_id = $template->getMasterStep()->getId();
        $templateData->name = $template->getMasterStep()->getName(); //nombre del template
        //var values
        $templateData->values = $values;
        //insert it into the envelope
        $envelope->templates[] = $templateData;

        return $envelope;
    }

    /**
     * Adds a template from CSV to an existing envelope
     *
     * @param object $template the contract entity instance
     * @param object $envelope
     * @return void
     */
    public function addTemplateFromCSVAnexo3($template, $envelope)
    {
        //create the object
        $templateData = new \stdClass();
        //first populate the general data of the template
        $templateData->id = $template->getId();
        $templateData->DX = $template->getMasterStep()->getPlantillaIdByYear($template->getInstanciaWorkflow()->getYear()); //this must be the DX template id?
        $templateData->master_step_id = $template->getMasterStep()->getId();
        $templateData->name = $template->getMasterStep()->getName(); //nombre del template

        $InstanciaWorkflow = $template->getInstanciaWorkflow();
        $workflowDataMaster = $InstanciaWorkflow->getMasterDataValues();
        $workflowDataMasterJSON = json_decode($workflowDataMaster);

        $idsec = $workflowDataMasterJSON->idsec->valueVar;

        // Productos
        $rootdiranexo3 = __DIR__ . "/../../../../../web/Anexos/anexo3";


        $counter = 0;
        $values = new \stdClass();
        foreach ($idsec as $vad_id) {
            $filename = $rootdiranexo3 . "/anexo_productos_" . $template->getId() . "_" . $vad_id . ".csv";
            //var_dump($filename);
            //exit;
            if (file_exists($filename)) {
                $data = $this->dataCSV($filename);
                $fields = $data['fields'];
                $rows = $data['rows'];

                if ($counter == 0) {
                    foreach ($fields as $fieldElement) {
                        $values->{$fieldElement} = array();
                    }
                }
                foreach ($rows as $row) {
                    for ($columnId = 0; $columnId < sizeof($row); $columnId++) {
                        $fieldElement = $fields[$columnId];
                        $values->{$fieldElement}[] = $row[$columnId];
                    }
                }

                $counter++;
            }
        }

        //var values
        $templateData->values = $values;
        //insert it into the envelope
        $envelope->templates[] = $templateData;

        return $envelope;
    }

    /**
     * Adds a template from CSV to an existing envelope
     *
     * @param object $template the contract entity instance
     * @param object $envelope
     * @return void
     */
    public function addTemplateFromCSVAnexo2($template, $envelope)
    {
        //create the object
        $templateData = new \stdClass();
        //first populate the general data of the template
        $templateData->id = $template->getId();
        $templateData->DX = $template->getMasterStep()->getPlantillaIdByYear($template->getInstanciaWorkflow()->getYear()); //this must be the DX template id?
        $templateData->master_step_id = $template->getMasterStep()->getId();
        $templateData->name = $template->getMasterStep()->getName(); //nombre del template

        $InstanciaWorkflow = $template->getInstanciaWorkflow();
        $workflowDataMaster = $InstanciaWorkflow->getMasterDataValues();
        $workflowDataMasterJSON = json_decode($workflowDataMaster);

        $vad_id = $workflowDataMasterJSON->idsec->valueVar;
        $mse_sms = $workflowDataMasterJSON->seccion->valueVar;
        $cod_comercial = $workflowDataMasterJSON->codigo_proveedor->valueVar;



        $stepData = $template->getStepDataValue();
        $stepDataJSON = json_decode($stepData);
        if (!empty($stepDataJSON)) {
            $vsi = $stepDataJSON->vsi_id;
            $datosDist = $stepDataJSON->datosDistribuidor;
            $razon_socialDist = $datosDist->razon_social;
        }else{
            $vsi = "";
            $razon_socialDist = "";
        }

        /*
         * Valores especiales
         */
        $templateData->vad_id = $vad_id;
        $templateData->cod_comercial = $cod_comercial;
        $templateData->mse_sms= $mse_sms;

        $templateData->vsi = array($vsi);
        $templateData->razon_social_distribuidor = $razon_socialDist;


        //distribuidores
        $rootdiranexo2 = __DIR__ . "/../../../../../web/Anexos/anexo2";


        $counter = 0;
        $values = new \stdClass();

        $filename = $rootdiranexo2 . "/anexo_centros_" . $InstanciaWorkflow->getId() . "_" . $template->getId() . ".csv";
        //var_dump($filename);
        //exit;
        $values->codigoDa = array();
        $values->codigoProveedor = array();
        $values->centroRCM = array();
        $values->centroSMS = array();
        $values->descripcionCentro = array();


        if (file_exists($filename)) {
            $data = $this->dataCSV($filename);
            $fields = $data['fields'];
            $rows = $data['rows'];

            if ($counter == 0) {

            }
            foreach ($rows as $row) {
                for ($columnId = 0; $columnId < sizeof($row); $columnId++) {
                    $fieldElement = $fields[$columnId];
                    $newFieldElement = "";
                    switch ($fieldElement){
                        case "Cod. Dep. Actividad":
                            $newFieldElement = "codigoDa";
                            break;
                        case "Cod. Proveedor":
                            $newFieldElement = "codigoProveedor";
                            break;
                        case "Cod. Centro RCM":
                            $newFieldElement = "centroRCM";
                            break;
                        case "Cod. Centro SMS":
                            $newFieldElement = "centroSMS";
                            break;
                        case "Descripcion Centro":
                            $newFieldElement = "descripcionCentro";
                            break;
                    }

                    $values->{$newFieldElement}[] = $row[$columnId];
                }
            }

            $counter++;
        }





        //var values
        $templateData->values = $values;
        //insert it into the envelope
        $envelope->templates[] = $templateData;

        return $envelope;
    }

    /**
     * Adds a template from CSV to an existing envelope
     *
     * @param object $template the contract entity instance
     * @param object $envelope
     * @return void
     */
    public function addTemplateFromAnexo5CargoCSV($template, $envelope)
    {
        //create the object
        $templateData = new \stdClass();
        //first populate the general data of the template
        $templateData->id = $template->getId();
        $templateData->DX = $template->getMasterStep()->getPlantillaIdByYear($template->getInstanciaWorkflow()->getYear()); //this must be the DX template id?
        $templateData->master_step_id = $template->getMasterStep()->getId();
        $templateData->name = $template->getMasterStep()->getName(); //nombre del template

        $values = new \stdClass();
        $rootdiranexo = __DIR__ . "/../../../../../web/Anexos/anexo5";

        if ($template->getMasterStep()->getId() == 159) {
            $filenameCSV = $rootdiranexo . "/anexo_productos5_" . $template->getId() . ".csv";
        } else {
            $filenameCSV = $rootdiranexo . "/anexo_productos_centros_" . $template->getId() . ".csv";

        }

        if (file_exists($filenameCSV)) {
            $data = $this->dataSpecialCSV($filenameCSV);
            //$fields = $data['fields'];
            $rows = $data['rows'];

            /*
             * Put names fields
             * $fieldElemenet =
             * Cod. Centro	Centro	Fecha inicio	Fecha fin	SMS	Articulo	Temática	Redención (Fórmula)
             * 	VENTA BRUTA	VENTA FIDELIZADA	UNIDADES	PRECIO COSTE MEDIO	EUROS x UNIDAD	% SOBRE VTA / VTA FIDELIZADA
             * 	% SOBRE DIF. DE PRECIO	% PRECIO COSTE	REDENCIÓN TOTAL (S/IVA)
             */
            $fields = array(
                "codCentro",
                "centro",
                "fechaInicio",
                "fechaFin",
                "sms",
                "articulo",
                "tematica",
                "redencionFormula",
                "ventaBruta",
                "ventaFidelizada",
                "unidades",
                "precioCosteMedio",
                "eurosUnidad",
                "porVtaVtaFidelizada",
                "porDifPrecio",
                "porPrecioCoste",
                "redencionTotal"
            );

            foreach ($fields as $fieldElement) {
                $values->{$fieldElement} = array();
            }

            foreach ($rows as $row) {

                for ($columnId = 1; $columnId < sizeof($row); $columnId++) {
                    $fieldPosition = $columnId - 1;
                    $fieldElement = $fields[$fieldPosition];

                    $element = $row[$columnId];

                    if ($columnId == 3 || $columnId == 4) {
                        // fechas
                        $element = $this->processExcelDate($element);
                    }

                    //$values->{$fieldElement}[] = utf8_encode(rawurlencode($element));
                    $values->{$fieldElement}[] = $this->processRowValue($element);
                }

            }

        }

        //var values
        $templateData->values = $values;
        //insert it into the envelope
        $envelope->templates[] = $templateData;

        return $envelope;
    }


    /**
     * Get distributors info
     */
    private function getDistributorsInfo($contract)
    {
        $result = array();
        $master_step_dist = 0;

        if ($contract->getMasterWorkflow() == 2) {
            /*
             * Obtener los datos de firma del distribuidor
             * MS = 37
             */
            $master_step_dist = 37;


        } elseif ($contract->getMasterWorkflow() == 17) {
            /*
             * Obtener los datos de firma del distribuidor
             * MS = 105
             */
            $master_step_dist = 105;

        } elseif ($contract->getMasterWorkflow() == 20) {
            /*
             * Obtener los datos de firma del distribuidor
             * MS = 161
             */
            $master_step_dist = 161;

        } elseif ($contract->getMasterWorkflow() == 24) {
            /*
             * Obtener los datos de firma del distribuidor
             * MS = 154
             */
            $master_step_dist = 154;

        } elseif ($contract->getMasterWorkflow() == 25) {
            /*
             * Obtener los datos de firma del distribuidor
             * MS = 187
             */
            $master_step_dist = 187;
        }

        if ($master_step_dist != 0) {
            $stepDistribuidoresArray = $this->em
                ->getRepository('NononsenseHomeBundle:InstanciasSteps')
                ->findBy(array("workflow_id" => $contract->getId(), "master_step_id" => $master_step_dist));

            for ($counter = 0; $counter < sizeof($stepDistribuidoresArray); $counter++) {
                $stepDistribuidor = $stepDistribuidoresArray[$counter];

                if ($stepDistribuidor->getStatusId() == 1 || $stepDistribuidor->getStatusId() == 5) {
                    $stepData = $stepDistribuidor->getStepDataValue();
                    $stepDataJSON = json_decode($stepData);
                    $vsi_id = $stepDataJSON->vsi_id;


                    $cif_code_dist = "valor de pruebas";


                    $razon_social_distribuidor = $stepDataJSON->datosDistribuidor->razon_social;
                    $cif_distribuidor = $cif_code_dist;
                    $email_distribuidor = $stepDataJSON->datosDistribuidor->email;

                    /*
                     * [{"email": "dist1@xxx.com", "CIF": "000565665X"}, {"email": "dist2@yyy.com", "CIF": "68687878D"}]
                     */

                    $result[] = array(
                        "name" => $razon_social_distribuidor,
                        "email" => $email_distribuidor,
                        "CIF" => $cif_distribuidor
                    );

                }
            }

        }


        return $result;
    }


    /**
     * generates the needed arrays to generate an Excel file from a CSV file
     *
     * @param string $CSVPath path to the CSV file
     * @return array
     * @access public
     */
    function dataCSV($CSVPath)
    {
        $fh = fopen($CSVPath, "r");
        //var_dump($fh);
        //The CSVs saved by Excel are separated by "," by default
        $fields = array();
        $rows = array();
        $header = "";
        $counter = 0;
        while (($data = fgetcsv($fh, 10000, ";")) !== FALSE) {
            if ($counter == 0) {
                //this is just an info row do not do anything
                foreach ($data as $value) {
                    $headerArray[] = $value;
                }
                $header = $headerArray[0];
            } else if ($counter == 1) {
                //var_dump('entro');
                //this is the row with the field names
                foreach ($data as $value) {
                    $fields[] = $value;
                }
            } else {
                //this is a data row
                $row = array();
                foreach ($data as $value) {
                    $row[] = $value;
                }
                $rows[] = $row;
            }
            $counter++;
        }
        //var_dump($counter);
        fclose($fh);

        return array('fields' => $fields, 'rows' => $rows, 'header' => $header);
    }


    /**
     * generates the needed arrays to generate an Excel file from a Special CSV file
     *
     * Condition, first 5 rows no info.
     * 6º row: header
     * 7+ row: info
     * @param string $CSVPath path to the CSV file
     * @return array
     * @access public
     */
    function dataSpecialCSV($CSVPath)
    {
        $fh = fopen($CSVPath, "r");
        //var_dump($fh);
        //The CSVs saved by Excel are separated by "," by default
        $fields = array();
        $rows = array();
        $header = "";
        $counter = 0;
        while (($data = fgetcsv($fh, 10000, ";")) !== FALSE) {
            if ($counter < 4) {

            } else if ($counter == 5) {
                //var_dump('entro');
                //this is the row with the field names
                foreach ($data as $value) {
                    $fields[] = $value;
                }

            } else {
                //this is a data row
                $row = array();
                $empties = 0;
                foreach ($data as $value) {
                    if ($value == 0) {
                        $empties++;
                    }
                    $row[] = $value;
                }
                if ($empties != sizeof($data)) {
                    $rows[] = $row;
                }

            }
            $counter++;
        }

        //var_dump($counter);
        fclose($fh);
        //var_dump($fields);
        //var_dump($rows);
        //exit;
        return array('fields' => $fields, 'rows' => $rows, 'header' => $header);
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

    private function getdateFormated($day, $month, $year)
    {
        $cadena = "";
        $monthString = "";

        switch ($month) {
            case 1:
                $monthString = "enero";
                break;
            case 2:
                $monthString = "febrero";
                break;
            case 3:
                $monthString = "marzo";
                break;
            case 4:
                $monthString = "abril";
                break;
            case 5:
                $monthString = "mayo";
                break;
            case 6:
                $monthString = "junio";
                break;
            case 7:
                $monthString = "julio";
                break;
            case 8:
                $monthString = "agosto";
                break;
            case 9:
                $monthString = "septiembre";
                break;
            case 10:
                $monthString = "octubre";
                break;
            case 11:
                $monthString = "noviembre";
                break;
            case 12:
                $monthString = "diciembre";
                break;
        }

        $cadena = $day . " de " . $monthString . " de " . $year;

        return $cadena;
    }

    public function processExcelDate($element)
    {
        $year = 0;
        $month = 0;
        $day = 0;


        if (strpos($element, "-")) {
            $process = true;
            $elementSplitted = explode("-", $element);

        } else if (strpos($element, "/")) {
            $process = true;
            $elementSplitted = explode("/", $element);

        } else {
            $elementSplitted = array();
            $process = false;
        }

        if ($process) {
            /*
             * Algunos usuarios habían introducido fecha sin año.
            if(!isset($elementSplitted[2])){
                $year = 2018;
            }else{
                $year = $elementSplitted[2];
            }
            */
            $year = $elementSplitted[2];
            $day = $elementSplitted[0];
            $monthValue = $elementSplitted[1];

            if (is_numeric($monthValue)) {
                $month = $monthValue;

            } else {
                $monthValue = strtolower($monthValue);

                if ($monthValue == "enero" || $monthValue = "ene" || $monthValue = "jan") {
                    $month = 1;
                } elseif ($monthValue == "febrero" || $monthValue = "ene" || $monthValue = "jan") {
                    $month = 2;
                } elseif ($monthValue == "marzo" || $monthValue = "mar") {
                    $month = 3;
                } elseif ($monthValue == "abril" || $monthValue = "abr" || $monthValue = "apr") {
                    $month = 4;
                } elseif ($monthValue == "mayo" || $monthValue = "may") {
                    $month = 5;
                } elseif ($monthValue == "junio" || $monthValue = "jun") {
                    $month = 6;
                } elseif ($monthValue == "julio" || $monthValue = "jul") {
                    $month = 7;
                } elseif ($monthValue == "agosto" || $monthValue = "ago" || $monthValue = "aug") {
                    $month = 8;
                } elseif ($monthValue == "septiembre" || $monthValue = "sep") {
                    $month = 9;
                } elseif ($monthValue == "octubre" || $monthValue = "oct") {
                    $month = 10;
                } elseif ($monthValue == "noviembre" || $monthValue = "nov") {
                    $month = 11;
                } elseif ($monthValue == "diciembre" || $monthValue = "dic") {
                    $month = 12;
                }
            }


            return $this->getdateFormated($day, $month, $year);
        } else {
            return $element;
        }

    }
}
