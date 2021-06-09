<?php
namespace Nononsense\HomeBundle\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasSteps;
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

class CVCumplimentationController extends Controller
{
    //Creamos nuevo registro
    public function newAction(Request $request, int $template)
    {   
        $em = $this->getDoctrine()->getManager();
    	$serializer = $this->get('serializer');
        $array=array();

        $items=$this->getDoctrine()->getRepository(TMTemplates::class)->list("list",array("id" => $template,"init_cumplimentation" => 1));

        if(!$items){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'La plantilla indicada no puede cumplimentarse'
            );
            $route = $this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
            return $this->redirect($route);
        }

        $array["item"]=$items[0];
        $array["secondWf"]=$em->getRepository(TMSecondWorkflow::class)->findBy(array("template" => $template));
        $array["users"] = $em->getRepository(Users::class)->findAll();
        $array["groups"] = $em->getRepository(Groups::class)->findAll();
        
        return $this->render('NononsenseHomeBundle:CV:new_cumpli.html.twig',$array);
    }

    //Guardamos el nuevo registro (PRE CUMPLIMENTADO)
    public function newSaveAction(Request $request, int $template)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');
        $array=array();
        $error=0;

        $items=$em->getRepository(TMTemplates::class)->list("list",array("id" => $template,"init_cumplimentation" => 1));

        $concat="?";

        if($request->get("logbook")){
            $concat.="logbook=".$request->get("logbook")."&";
        }

        if(!$items){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'La plantilla indicada no puede cumplimentarse'
            );
            $route = $this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
            return $this->redirect($route);
        }

        $item = $em->getRepository(TMTemplates::class)->findOneBy(array("id" => $items[0]["id"]));
        $action=$em->getRepository(CVActions::class)->findOneBy(array("id" => 15));
        $state=$em->getRepository(CVStates::class)->findOneBy(array("id" => 1));
        $user = $this->container->get('security.context')->getToken()->getUser();

        $wfs=$em->getRepository(TMSecondWorkflow::class)->findBy(array("template" => $item),array("id" => "ASC"));
        

        $record= new CVRecords();
        $record->setTemplate($item);
        $record->setCreated(new \DateTime());
        $record->setModified(new \DateTime());
        $record->setInEdition(FALSE);
        $record->setEnabled(TRUE);
        $record->setState($state);
        $record->setUser($user);
        $em->persist($record);

        $params["data"]=array();

        $sign= new CVSignatures();
        $sign->setRecord($record);
        $sign->setUser($user);
        $sign->setAction($action);
        $sign->setCreated(new \DateTime());
        $sign->setModified(new \DateTime());
        $sign->setSigned(FALSE);
        $sign->setJustification(FALSE);
        $sign->setNumberSignature(1);

        if($request->get("unique")){
            foreach($request->get("unique") as $unique){
                if($request->get($unique)){
                    $params["data"][$unique]=$request->get($unique);
                }
            }
        }

        if($request->get("value_qr")){
            $value_qr=json_decode($request->get("value_qr"), true);
            $params["data"]=array_merge($params["data"],$value_qr);
        }

        $json_value=json_encode(array("data" => $params["data"]), JSON_FORCE_OBJECT);
        $sign->setJson($json_value);
        $em->persist($sign);

        $key=0;
        foreach($wfs as $wf){
            for ($i = 1; $i <= $wf->getSignaturesNumber(); $i++) {
                if($request->get($wf->getTmCumplimentation()->getName()) && array_key_exists($key, $request->get($wf->getTmCumplimentation()->getName())) && $request->get("relationals") && array_key_exists($key, $request->get("relationals"))){

                    $cvwf= new CVWorkflow();
                    $cvwf->setRecord($record);
                    $cvwf->setType($wf->getTmCumplimentation());

                    if($request->get($wf->getTmCumplimentation()->getName())[$key]=="1"){
                        $group=$em->getRepository(Groups::class)->findOneBy(array("id" => $request->get("relationals")[$key]));
                        $cvwf->setGroup($group);
                    }
                    else{
                        $user_aux=$em->getRepository(Users::class)->findOneBy(array("id" => $request->get("relationals")[$key]));
                        $cvwf->setUser($user_aux);
                    }

                    $cvwf->setNumberSignature($key);
                    $cvwf->setSigned(FALSE);
                    $em->persist($cvwf);
                }
                else{
                    $error=1;
                }
                if($error){
                   break 2; 
                }
                $key++;
            }
        }

        
        if($error==0){
            $em->flush();

             $route = $this->container->get('router')->generate('nononsense_cv_docoaro_new', array("id" => $record->getId())).$concat;
        }
        else{
            $this->get('session')->getFlashBag()->add(
                'error',
                    'Hubo un error al intentar iniciar la cumplimentación de la plantilla'
            );
            $route = $this->container->get('router')->generate('nononsense_tm_templates')."?state=6";
        }
        
        return $this->redirect($route);
    }

    //Pedimos al usuario que firme la cumplimentación/verificación
    public function recordAction(Request $request, int $id)
    {   
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');
        $array=array();

        $array["item"] = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $id));

        if(!$array["item"]){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'El registro no existe'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        if($array["item"]->getRedirectSearch()){
            $array["item"]->setRedirectSearch(FALSE);
            $em->persist($array["item"]);
            $em->flush();
            $route = $this->container->get('router')->generate('nononsense_cv_search');
            return $this->redirect($route);
        }

        $array["signature"] = $this->getDoctrine()->getRepository(CVSignatures::class)->findOneBy(array("record" => $array["item"]),array("id" => "DESC"));

        if(!$array["signature"] || $array["signature"]->getSigned()){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'El registro no se puede firmar'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        if($array["signature"]->getUser()!=$user){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'Usted no puede firmar esta evidencia'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $can_sign = $this->getDoctrine()->getRepository(CVRecords::class)->search("count",array("id" => $array["item"]->getId(),"pending_for_me" => 1,"user" => $user));

        if($can_sign==0){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No puede abrir esta plantilla debido al workflow definido'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        return $this->render('NononsenseHomeBundle:CV:sign.html.twig',$array);
    }

    //Firmamos la cumplimentación/verificación
    public function signAction(Request $request, int $id)
    {   
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');
        $array=array();

        $record = $this->getDoctrine()->getRepository(CVRecords::class)->findOneBy(array("id" => $id));

        if(!$record){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'El registro no existe'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $signature = $this->getDoctrine()->getRepository(CVSignatures::class)->findOneBy(array("record" => $record),array("id" => "DESC"));

        $all_signatures = $this->getDoctrine()->getRepository(CVSignatures::class)->search("list",array("record" => $record, "have_json" => 1,"not_this" => $signature->getId()));

        if(!$signature || $signature->getSigned()){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'El registro no se puede firmar'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        if($signature->getUser()!=$user){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'Usted no puede firmar esta evidencia'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }
        
        if(!$request->get('password') || !$this->get('utilities')->checkUser($request->get('password'))){
            $this->get('session')->getFlashBag()->add(
                'error',
                "No se pudo firmar el registro, la contraseña es incorrecta"
            );
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }

        if(!$request->get('justification') && ($signature->getJustification() || $signature->getAction()->getJustification())){
            $this->get('session')->getFlashBag()->add(
                'error',
                "El registro no se pudo firmar porque era necesaria una justificación"
            );
            return $this->redirect($this->container->get('router')->generate('nononsense_home_homepage'));
        }

        $can_sign = $this->getDoctrine()->getRepository(CVRecords::class)->search("count",array("id" => $record->getId(),"pending_for_me" => 1,"user" => $user));

        if($can_sign==0){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No puede abrir esta plantilla debido al workflow definido1'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        $wf=$this->get('utilities')->wich_wf($record,$user);

        if(!$wf){
            $this->get('session')->getFlashBag()->add(
                'error',
                    'No puede abrir esta plantilla debido al workflow definido2'
            );
            $route = $this->container->get('router')->generate('nononsense_home_homepage');
            return $this->redirect($route);
        }

        if($request->get('justification')){
            $signature->setDescription($request->get('justification'));
        }
        $signature->setJson(str_replace("gsk_id_firm", $signature->getNumberSignature(), $signature->getJson()));
        $signature->setSigned(TRUE);
        $signature->setModified(new \DateTime());
        

        $record->setModified(new \DateTime());
        

        if($signature->getAction()->getFinishUser()){
            $wf->setSigned(TRUE);
            $signature->setFinish(TRUE);
            $em->persist($wf);
        }

        $em->persist($signature);

        //Miramos si termina el workflow por que no quedan wf de su mismo tipo de acción pendientes
        $finish_workflow=1;
        $exist_wfs=$this->getDoctrine()->getRepository(CVWorkflow::class)->findBy(array('record' => $record,"signed" => FALSE));
        foreach($exist_wfs as $exist_wf){
            if($exist_wf->getType()==$wf->getType() && $exist_wf!=$wf){
                $finish_workflow=0;
            }
        }

        if($signature->getAction()->getFinishWorkflow() || $finish_workflow){
            if($record->getState()!=$signature->getAction()->getNextState()){
                //Capturamos el estado correspondiente a la última acción que se firma
                $next_state=$signature->getAction()->getNextState();

                //Si hay una firma de devolución en un workflow activo, tiene prioridad esta a la hora de setear el próximo estado
                $action=$this->getDoctrine()->getRepository(CVActions::class)->findOneBy(array('id' => 6));
                $exist_return=$this->getDoctrine()->getRepository(CVSignatures::class)->findBy(array('record' => $record,"signed" => TRUE,"finish" => TRUE,'action' => $action),array("id" => "DESC"));
                if(count($exist_return)>0 && $record->getState()->getType()==$exist_return[0]->getAction()->getType()){
                    $next_state=$exist_return[0]->getAction()->getNextState();
                }

                //Vaciamos próximo workflow activo
                $clean_wfs=$this->getDoctrine()->getRepository(CVWorkflow::class)->findBy(array('record' => $record,"signed" => TRUE));
                foreach($clean_wfs as $clean_wf){
                    if($clean_wf->getType()->getTmType()==$next_state->getType()){
                        $clean_wf->setSigned(FALSE);
                        $em->persist($clean_wf);
                    }
                }

                //Vaciamos próximo bloque de firmas activo
                $other_signatures=$this->getDoctrine()->getRepository(CVSignatures::class)->findBy(array('record' => $record,"signed" => TRUE,"finish" => TRUE));
                foreach($other_signatures as $other_signature){
                    if($other_signature->getAction()->getType()==$next_state->getType()){
                        $other_signature->setFinish(FALSE);
                        $em->persist($other_signature);
                    }
                }
                $record->setState($next_state);
            }
        }

        $obj1 = json_decode($signature->getJson())->data;

        if(count($all_signatures)>0){
            $obj2 = json_decode($all_signatures[0]->getJson())->data;

            //Compares new signature with old step and instert differences
            $this->multi_obj_diff_counter = 0;
            $this->multi_obj_diff($obj1, $obj2, '$obj2->$key', '/^(in_|gsk_|dxo_|delete_)|(name|extension\b)/', false, $signature, false, null, 'new');

            //Compares old signature with new step and check removed fields
            $this->multi_obj_diff_counter = 0;
            $this->multi_obj_diff($obj2, $obj1, '$obj2->$key', '/^(in_|gsk_|dxo_|delete_)|(name|extension\b)/', false, $signature, false, null, 'old');
        }
        else{
            $obj2 = json_decode($signature->getRecord()->getJson())->configuration;
            //Compares with default values
            $this->multi_obj_diff($obj1, $obj2, '$obj2->variables->$field->value', '/^(in_|gsk_|dxo_|delete_)|(name|extension\b)/', false, $signature, false, null, 'new');
        }

        $em->persist($record);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            "El documento se ha firmado correctamente"
        );
        return $this->redirect($this->container->get('router')->generate('nononsense_cv_search'));
    }

    //Listado de cumplimentaciones
    public function listAction(Request $request){

        $user = $this->container->get('security.context')->getToken()->getUser();
        $fll=false;
        foreach ($user->getGroups() as $groupMe) {
            $type = $groupMe->getGroup()->getTipo();
            if ($type == 'FLL') {
                $fll = true;
            }
        }

        $array_item["fll"]=$fll;

        $filters=Array();
        $filters2=Array();
        $types=array();

        $filters=array_filter($request->query->all());
        $filters2=array_filter($request->query->all());

        $filters["user"]=$user;
        $filters2["user"]=$user;

        $filters["fll"]=$fll;
        $filters2["fll"]=$fll;

        $array_item["suser"]["id"]=$user->getId();

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


        $array_item["suser"]["id"]=$user->getId();
        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(CVRecords::class)->search("list",$filters);
        $array_item["states"]=$this->getDoctrine()->getRepository(CVStates::class)->findAll();

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
            return $this->render('NononsenseHomeBundle:CV:search.html.twig',$array_item);
        }
        else{
            //Exportamos a Excel

            if($request->get("export_excel")){
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                $phpExcelObject->getProperties();
                $phpExcelObject->setActiveSheetIndex(0)
                 ->setCellValue('A1', 'Nº')
                 ->setCellValue('B1', 'Nombre')
                 ->setCellValue('C1', 'Iniciado por')
                 ->setCellValue('D1', 'Fecha inicio')
                 ->setCellValue('E1', 'Ultima modificación')
                 ->setCellValue('F1', 'Estado');
            }

            if($request->get("export_pdf")){
                $html='<html><body style="font-size:8px;width:100%"><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%"><th style="font-size:8px;width:6%">Nº</th><th style="font-size:8px;width:49%">Nombre</th><th style="font-size:8px;width:10%">Iniciado por</th><th style="font-size:8px;width:10%">F. inicio</th><th style="font-size:8px;width:10%">F. modific.</th><th style="font-size:8px;width:10%">Estado</th></tr>';
            }

            $i=2;
            foreach($array_item["items"] as $item){
                switch($item["status"]){
                    case 0: $status="Iniciado";break;
                    case 1: $status="Esperando firma guardado parcial";break;
                    case 2: $status="Esperando firma envío";break;
                    case 3: $status="Esperando firma cancelación";break;
                    case 4: $status="En verificación";break;
                    case 5: $status="Pendiente cancelación en edición";break;
                    case 6: $status="Cancelado en edición";break;
                    case 7: $status="Esperando firma verificación total";break;
                    case 8: $status="Cancelado";break;
                    case 9: $status="Archivado";break;
                    case 10: $status="Reconciliado";break;
                    case 11: $status="Bloqueado";break;
                    case 12: $status="Esperando firma cancelación en verificación";break;
                    case 13: $status="Esperando firma devolución a edición";break;
                    case 14: $status="Pendiente de cancelación en verificación";break;
                    case 15: $status="Esperando firma verificación parcial";break;
                    default: $status="Desconocido";
                }
                if($item["id_grid"]==0){
                    $name=$item["name"];
                }
                else{
                    $name=$item["name2"];
                }

                if($request->get("export_excel")){
                    $phpExcelObject->getActiveSheet()
                    ->setCellValue('A'.$i, $item["id_grid"])
                    ->setCellValue('B'.$i, $name)
                    ->setCellValue('C'.$i, $item["creator"])
                    ->setCellValue('D'.$i, ($item["created"]) ? $item["created"] : '')
                    ->setCellValue('E'.$i, ($item["modified"]) ? $item["modified"] : '')
                    ->setCellValue('F'.$i, $status);
                }

                if($request->get("export_pdf")){
                    $html.='<tr style="font-size:8px"><td>'.$item["id"].'</td><td>'.$name.'</td><td>'.$item["creator"].'</td><td>'.(($item["created"]) ? $item["created"]->format('Y-m-d H:i:s') : '').'</td><td>'.(($item["modified"]) ? $item["modified"]->format('Y-m-d H:i:s') : '').'</td><td>'.$status.'</td></tr>';
                }

                $i++;
            }

            if($request->get("export_excel")){
                $phpExcelObject->getActiveSheet()->setTitle('Listado de registros');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                  ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                  'list_records.xlsx'
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

    public function listContentAction(Request $request){
        $filters = array_filter($request->query->all());

        $histories = $this->getDoctrine()->getRepository(CVRecordsHistory::class)->list($filters);

        return $this->render('NononsenseHomeBundle:CV:search_contain.html.twig', ['histories' => $histories, 'filters' => $filters]);
    }

    public function downloadBase64Action(Request $request, $id){

        $hisotry = $this->getDoctrine()->getRepository(CVRecordsHistory::class)->findOneBy(['id' => $id]);

        $value = $hisotry->getValue();

        if ($request->get('type') !== null && $request->get('type') == 'prev') {
            $value = $hisotry->getPrevValue();
        }

        $file = file_get_contents($value);
        $mime = mime_content_type($value);

        $extension = preg_split('#(/|;)#', $hisotry->getValue())[1];
        $filename = $hisotry->getField().'.'.$extension;

        header('Content-Description: File Transfer');
        header('Content-Type: '.$mime);
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($file));
        echo $file;
        exit;
    }

    public $multi_obj_diff_counter = 0;

    public function multi_obj_diff($obj1, $obj2, $compare, $regex, $field, $evidencia, $clonedObj, $removedOrAdded = null, $compareWith, $aux = ''){

        if ($this->multi_obj_diff_counter == 0) {
            $clonedObj = clone $obj1; //Clone $obj1 once time to get keys of the first dimension
        }

        $diff = [];
        //$removedOrAdded = null;
        //$lineOptions = null;

        foreach ($obj1 as $key => $value) {
            if (!preg_match($regex, $key)) {
                
                if (array_key_exists($key, $clonedObj)) {
                    $field = $key;
                }

                if (!isset($obj2->$key)) {
          
                    $obj2->$key = $this->format_object($value); //Checks if object exsist, if not, create the empty object
                    $removedOrAdded = $field; //Save $field in $removedOrAdded (temp) if input line is removed or added, if is added or removed depends on $compareWith
                }
            

                $deph = ($compare == '$obj2->variables->$field->value') ? $obj2 : $obj2->$key; //Check if first insert or not

                if (is_object($value)) {
                    $multi = $this->multi_obj_diff($value, $deph, $compare, $regex, $field, $evidencia, $clonedObj, $removedOrAdded, $compareWith, $key);
                    if ($multi) {
                        $diff[$key] =  $multi;
                        //$diff[$key] = (key($multi) == 'value') ? $multi['value'] : $multi; //(key($multi) == 'value') ? $multi['value'] : $multi;
                    }

                }else{

                    //echo $field.':'.$removedOrAdded.'<br>';

                    if ($value != eval("return $compare;")) {

                        $lineOptions = null;
                        $index = ($aux != $field) ? $aux : $key;

                        if ($index == 'value' || $index == '') {
                            $index = null;
                        }

                        if ($compareWith == 'old') {
                            if ($field == $removedOrAdded) {
                                $lineOptions = 0;
                                // $diff[$key]['field'] = $field;
                                // $diff[$key]['line_options'] = $lineOptions;
                                // $diff[$key]['field_index'] = $index;
                                // $diff[$key]['field_value'] = $value;
                                // $diff[$key]['prevVal'] = eval("return $compare;");
                                $this->insertDiff($field, $index, $value, eval("return $compare;"), $lineOptions, $evidencia);
                            }
                        }else{
                            if ($field == $removedOrAdded) {
                                $lineOptions = 1;
                            }

                            $diff[$key]['field'] = $field;
                            $diff[$key]['line_options'] = $lineOptions;
                            $diff[$key]['field_index'] = $index;
                            $diff[$key]['field_value'] = $value;
                            $diff[$key]['prevVal'] = eval("return $compare;");

                            $this->insertDiff($field, $index, $value, eval("return $compare;"), $lineOptions, $evidencia);
                        }
                        // if ($field == $removedOrAdded) { //Check removed or added field
                        //     $lineOptions = ($compareWith == 'old') ? 0 : 1; //if we compare the old object, we know that it is removed (1), otherwise, added (0)
                        // }
                        // //echo $field.'<br>';
                        
                        // $this->insertDiff($field, $index, $value, eval("return $compare;"), $lineOptions, $evidencia);

                        
      
                        // $diff[$key]['field'] = $field;
                        // $diff[$key]['line_options'] = $lineOptions;
                        // $diff[$key]['field_index'] = $index;
                        // $diff[$key]['field_value'] = $value;
                        // $diff[$key]['prevVal'] = eval("return $compare;");
                    }
                }
            }
            $this->multi_obj_diff_counter++;
        }

      
        //print_r($obj1);

        return $diff;
    }


    public function format_object($value){

        $arr = new \stdClass();

        $arr->removedOrAdded = 'removed';

        if (is_object($value)) {
            foreach ($value as $key => $v) {
                $arr->$key = $this->format_object($v);
            }
        }else{
            $arr = '';
        }
        
        return $arr;
    }

    public function insertDiff($field, $index = null, $value, $prevValue = null, $lineOptions = null, $evidencia){

            $stepHistory = new CVRecordsHistory();
            $stepHistory->setField($field);
            $stepHistory->setIndex($index);
            $stepHistory->setValue($value);
            $stepHistory->setPrevValue($prevValue);
            $stepHistory->setLineOptions($lineOptions);
            $stepHistory->setSignature($evidencia);

            $em = $this->getDoctrine()->getManager();
            $em->persist($stepHistory);
            //$em->flush();

            return $stepHistory;
    }
}