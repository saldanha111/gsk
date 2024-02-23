<?php

namespace Nononsense\UtilsBundle\Classes\JSON;

/**
 * Methods to manipulate Docxpresso JSON Data
 */

class Tools
{
    
    public function __construct($em = NULL) {
        //doctrine manager
        $this->em = $em;
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
    
    public function parseVarValues($data, $format = 'nullify')
    {
        if (is_string($data)){
            $data = json_decode($data);
        }
        $varValues = $data->varValues;
        $active = $data->validations->variables->active;
        //run over $varValues props
        foreach ($varValues as $prop => $value){
            if (isset($active->{$prop})){
                //we just check at the first array entry because Docxpresso
                //does not, in principle, for cloned values with different
                //visibility attributes
                if (!$active->{$prop}[0]){
                    //this field is not active
                    if ($format == 'remove'){
                        unset($varValues->{$prop});
                    } else if ($format == 'nullify'){
                        $varValues->{$prop} = NULL;
                    } else if ($format == 'empty'){
                        $varValues->{$prop} = array();
                    }
                }
            }
        }
        
        return $varValues;
        
    }
    
    /**
     * Creates the envelope for the DAC REST service
     * 
     * @param object $contract the contract entity instance
     * @return object
     */
    
    public function reportingEnvelope($contract)
    {
        //create the envelope
        $envelope = new \stdClass();
        //first populate the general data of the contract
        $envelope->id = $contract->getId();
        $envelope->type = ''; //id del tipo de contrato
        $envelope->name = ''; //nombre del tipo de contrato
        $envelope->year = ''; //Año versión contrato¿?
        $envelope->statusDA = ''; //Estado del DA¿?
        $envelope->date = ''; // fecha firma formato: 2018-08-30
        //section info
        $envelope->sections = array();
        /**
         * Loop over the sections
         * the final result should be something like
         * [{"id": 22233, "section": 45, "code": 4567, "type": ["MN", "MP"]}, 
         *  {"id": 4545454, "section": 45, "code": 4555, "type": ["MN"]}]
         */
        $envelope->provider = new \stdClass();
        $envelope->provider->CIF = '';
        $envelope->provider->name = '';
        $envelope->provider->address = '';
        $envelope->provider->city = '';
        $envelope->provider->postalcode = '';
        $envelope->provider->province = '';
        $envelope->provider->country = '';
        $envelope->provider->phone = '';
        $envelope->provider->email = '';
        $envelope->provider->fax = '';
        //DAC user
        $envelope->user = new \stdClass();
        $envelope->user->id = ''; //id de ¿red? usuario creador contrato
        $envelope->user->name = ''; //nombre del usuario creador contrato
        $envelope->user->email = ''; //email del usuario creador contrato
        $envelope->user->rol = ''; //perfil del usuario creador contrato
        //firmantes
        $envelope->signers = new \stdClass();
        //por partner
        $envelope->signers->partner = new \stdClass();
        $envelope->signers->partner->id = ''; //id firmante ..me imagino que el id de red
        $envelope->signers->partner->name = ''; //nombre firmante partner
        $envelope->signers->partner->email = ''; //email firmante partner
        //por proveedor
        $envelope->signers->provider = array();
        /**
         * Loop over providers
         * the final result should be something like
         * [{"email": "dist1@xxx.com", "name": "Juanita"}, {"email": "dist2@yyy.com", "name": "pepito"}]
         */
        //por los distribuidores
        $envelope->signers->distributors = array();
        /**
         * Loop over distributors
         * the final result should be something like
         * [{"email": "dist1@xxx.com", "CIF": "000565665X"}, {"email": "dist2@yyy.com", "CIF": "68687878D"}]
         */
        
        //TODO: integrar aquí el addTemplate?
        //used templates
        $envelope->templates = array();
        
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
        $values = $this->parseVarValues($data);
        //create the object
        $templateData = new \stdClass();
        //first populate the general data of the template
        $templateData->id = $template->getId();
        $templateData->DX = ''; //this must be the DX template id?
        $templateData->name = ''; //nombre del template
        //var values
        $templateData->values = $values;
        //insert it into the envelope
        $envelope->templates[] = $templateData;
        
        return $envelope;
    }
}
