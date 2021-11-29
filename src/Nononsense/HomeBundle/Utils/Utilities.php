<?php

namespace Nononsense\HomeBundle\Utils;

use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Nononsense\HomeBundle\Entity\Logs;
use Nononsense\HomeBundle\Entity\LogsTypes;
use Nononsense\HomeBundle\Entity\LogsTypesRepository;
use Nononsense\HomeBundle\Entity\Tokens;
use Nononsense\HomeBundle\Entity\CVRecordsHistory;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\NotificationsBundle\Entity\Notifications;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\HomeBundle\Utils\GskPdf;

class Utilities{
    
    public function __construct(\Doctrine\ORM\EntityManager $em, $logger, $session, $container, $templating) {
        $this->em = $em;
        $this->logger = $logger;
        $this->session = $session;
        $this->container = $container;
        $this->templating = $templating;
    }

    public function generateToken()
    {
    	$user = $this->container->get('security.context')->getToken()->getUser();

    	$token = uniqid().rand(1000,9999);
        $token_get_data = new Tokens();
        $token_get_data->setToken($token);
        if($user && $user !== 'anon.'){
            $token_get_data->setUser($user);
        }

        $this->em->persist($token_get_data);
        $this->em->flush();

        return $token;
    }

    public function tokenExpired($token){
    	$expired_token = 0;
        $tokenObj = $this->em->getRepository('NononsenseHomeBundle:Tokens')->findOneByToken($token);
        if($tokenObj){
            $token_date_created = $tokenObj->getCreated();
            $token_date_created->modify('+15 minute');
            $current_minute = date('YmdHis');
            if($current_minute > $token_date_created->format('YmdHis')){
                $expired_token = 1;
            }
            $this->em->persist($tokenObj);
            $this->em->flush();
        }
        else{
            $expired_token = 1;
        }

        return $expired_token;
    }

    public function tokenRemove($token){
        $expired_token = 0;
        $tokenObj = $this->em->getRepository('NononsenseHomeBundle:Tokens')->findOneByToken($token);
        if($tokenObj){
            $this->em->remove($tokenObj);
            $this->em->flush();
        }
        return TRUE;
    }

    public function getUserByToken($token){
        $expired_token = 0;
        $tokenObj = $this->em->getRepository('NononsenseHomeBundle:Tokens')->findOneByToken($token);
        if($tokenObj){
            $token_date_created = $tokenObj->getCreated();
            $token_date_created->modify('+15 minute');
            $current_minute = date('YmdHis');
            if($current_minute < $token_date_created->format('YmdHis')){
                $this->em->persist($tokenObj);
                $this->em->flush();
                return $tokenObj->getUser()->getId();
            }
            return false;
        }

        return false;
    }

    public function sendNotification($mailTo, $link, $logo, $accion, $subject, $message, $useTemplate = true)
    {
        if($useTemplate){
            $renderedBody = $this->templating->render(
                'NononsenseHomeBundle:Email:notificationUser.html.twig', array(
                'logo' => $logo,
                'accion' => $accion,
                'message' => $message,
                'link' => $link
            ));
        }else{
            $renderedBody = $message;
        }

        $email = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->container->getParameter('mailer_username'))
            ->setTo($mailTo)
            ->setBody($renderedBody,'text/html');
        if ($this->container->get('mailer')->send($email)) {
            $this->insertNotification($mailTo,$subject,$message."<br><br><a href='".$link."'>".$link."</a>");
            return true;
        } else {
            //echo '[SWIFTMAILER] not sending email: ' . $mailLogger->dump();
            return false;
        }

    }

    public function signWithP12($path_document_to_sign, $p12Path, $p12Pass){
        try {
            $command = 'AutoFirma sign -i '.$path_document_to_sign.' -o '.$path_document_to_sign.' -store pkcs12:'.$p12Path.' -filter cualquiertexto -password '.$p12Pass;
            $result = shell_exec($command);
            if(strpos($result,'La operacion ha terminado correctamente') === false){
                return false;
            }
        } catch(\Exception $ex){
            $this->logger->error("Utilities->signWithP12: ".$ex->getCode().": ".$ex->getMessage());
            return false;
        }

        return true;
    }

    public function saveLog(string $type, string $description)
    {
        /** @var LogsTypesRepository $logsTypesRepository */
        $logsTypesRepository = $this->em->getRepository(LogsTypes::class);
        /** @var LogsTypes $logType */
        $logType = $logsTypesRepository->findOneBy(['stringId' => $type]);
        if(!$logType){
            $logType = $logsTypesRepository->findOneBy(['stringId' => 'unknown']);
        }

        $log = new Logs();
        $log->setType($logType);
        $log->setDate(new DAteTime());
        $log->setDescription($description);
        $this->em->persist($log);
        try {
            $this->em->flush();
        } catch (OptimisticLockException $e) {
        }
    }

    public function checkUser($password, $username=''){

        if (!$username) $username = $this->container->get('security.context')->getToken()->getUsername();
        $password       = $password;

        $user           = $this->em->getRepository('NononsenseUserBundle:Users')->findOneBy(array('username' => $username));

        if ($user) {
            
            $factory    = $this->container->get('security.encoder_factory');
            $encoder    = $factory->getEncoder($user);

            $validPassword = ($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) ? true : false;
            return $validPassword;
        }

        return false;   
    }


    public function returnPDFResponseFromHTML($html){
        //$pdf = $this->container->get("white_october.tcpdf")->create('horizontal', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf = new GskPdf('horizontal', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, false, array());
        $pdf->SetAuthor('GSK');
        $pdf->SetTitle(('Registros GSK'));
        $pdf->SetSubject('Registros GSK');
        $pdf->setFontSubsetting(true);

        $pdf->SetHeaderData(NULL, NULL, date("d/m/Y H:i:s"),NULL, array(0,0,0), array(0,0,0));
        $pdf->SetPrintHeader(true);
        $pdf->SetPrintFooter(true);
        $pdf->SetFont('helvetica', '', 9, '', true);
        $pdf->AddPage('L', 'A4');
        $filename = 'list_records';
        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        $pdf->Output($filename.".pdf",'I'); // This will output the PDF as a response directly
    }

    public function wich_wf($record,$user,$type){
        $return=NULL;
        $groups=array();
        foreach($user->getGroups() as $uniq_group){
            $groups[]=$uniq_group->getGroup();
        }

        $wfs=$this->em->getRepository('NononsenseHomeBundle:CVWorkflow')->findBy(array('record' => $record,"signed" => FALSE,"type" => $record->getState()->getType()));
        if(count($wfs)==0){
            return NULL;
        }

        $find=0;
        foreach($wfs as $item){
            if($item->getUser() && $item->getUser()==$user){
                $find=1;
                $item_find=$item;
            }
        }
        if($find==0){
            foreach($wfs as $item){
                if($item->getGroup()){
                    $in_group=0;
                    foreach($user->getGroups() as $uniq_group){
                        if($uniq_group->getGroup()==$item->getGroup()){
                            $in_group=1;
                            $item_find=$item;
                            break;
                        }
                    }
                    if($in_group==1){
                        $find=1;
                        break;
                    }
                }
            }
        }

        if($find==0){
            $users_actions=$this->get_users_actions($user,$type);
            foreach($users_actions as $user_action){
                foreach($wfs as $item){
                    if($item->getUser() && $item->getUser()==$user_action){
                        $find=1;
                        $item_find=$item;
                    }
                    if($find==1){
                        break;
                    }
                }
                if($find==1){
                    break;
                }
            }
        }

        if($find==0){
            foreach($users_actions as $user_action){
                foreach($wfs as $item){
                    if($item->getGroup()){
                        $in_group=0;
                        foreach($user_action->getGroups() as $uniq_group){
                            if($uniq_group->getGroup()==$item->getGroup()){
                                $in_group=1;
                                $item_find=$item;
                                break;
                            }
                        }
                        if($in_group==1){
                            $find=1;
                            break;
                        }
                    }
                    if($find==1){
                        break;
                    }
                }
                if($find==1){
                    break;
                }
            }
        }

        if($record->getTemplate()->getCorrelative()){
            if($item_find==$wfs[0]){
                $return=$item_find;
            }
        }
        else{
            $return=$item_find;
        }

        return $return;
    }

    public function wich_second_wf($record,$user,$type,$subtype = NULL){
        $return=NULL;
        $groups=array();
        foreach($user->getGroups() as $uniq_group){
            $groups[]=$uniq_group->getGroup();
        }

        $wfs=$this->em->getRepository('NononsenseHomeBundle:CVSecondWorkflow')->findBy(array('record' => $record,"signed" => FALSE,"type" => $type));
        if(count($wfs)==0){
            return NULL;
        }

        $find=0;
        foreach($wfs as $item){
            if($item->getUser() && $item->getUser()==$user){
                $find=1;
                $item_find=$item;
            }
        }
        if($find==0){
            foreach($wfs as $item){
                if($item->getGroup()){
                    $in_group=0;
                    foreach($user->getGroups() as $uniq_group){
                        if($uniq_group->getGroup()==$item->getGroup()){
                            $in_group=1;
                            $item_find=$item;
                            break;
                        }
                    }
                    if($in_group==1){
                        $find=1;
                        break;
                    }
                }
            }
        }

        if($subtype){
            if($find==0){
                $users_actions=$this->get_users_actions($user,$subtype);
                foreach($users_actions as $user_action){
                    foreach($wfs as $item){
                        if($item->getUser() && $item->getUser()==$user_action){
                            $find=1;
                            $item_find=$item;
                        }
                        if($find==1){
                            break;
                        }
                    }
                    if($find==1){
                        break;
                    }
                }
            }

            if($find==0){
                foreach($users_actions as $user_action){
                    foreach($wfs as $item){
                        if($item->getGroup()){
                            $in_group=0;
                            foreach($user_action->getGroups() as $uniq_group){
                                if($uniq_group->getGroup()==$item->getGroup()){
                                    $in_group=1;
                                    $item_find=$item;
                                    break;
                                }
                            }
                            if($in_group==1){
                                $find=1;
                                break;
                            }
                        }
                        if($find==1){
                            break;
                        }
                    }
                    if($find==1){
                        break;
                    }
                }
            }
        }

        return $item_find;
    }

    public function insertNotification($email,$subject,$message){

       $user = $this->em->getRepository(Users::class)->findOneBy(array('email' => $email));

       $notification = new Notifications();

       $notification->setSubject($subject);
       $notification->setBody($message);
       $notification->setIsActive(1);
       $notification->setUser($user);

       $this->em->persist($notification);
       $this->em->flush();

       return $notification;
    }

    public $multi_obj_diff_counter = 0;

    public function multi_obj_diff($obj1, $obj2, $obj3, $compare, $regex, $field, $evidencia, $clonedObj, $removedOrAdded = null, $compareWith, $aux = ''){

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
                    $multi = $this->multi_obj_diff($value, $deph, $obj3, $compare, $regex, $field, $evidencia, $clonedObj, $removedOrAdded, $compareWith, $key);
                    if ($multi) {
                        $diff[$key] =  $multi;
                        //$diff[$key] = (key($multi) == 'value') ? $multi['value'] : $multi; //(key($multi) == 'value') ? $multi['value'] : $multi;
                    }

                }else{

                    //echo $field.':'.$removedOrAdded.'<br>';
                    try {
                        $other_value=eval("return $compare;");
                    } catch (\Exception $e) {
                        $other_value="";
                    }
                    if ($value != $other_value) {

                        $lineOptions = null;
                        $index = ($aux != $field) ? $aux : $key;

                        if ($index == 'value' || $index == '') {
                            $index = null;
                        }

                        if ($compareWith == 'old') {
                            if ($field == $removedOrAdded && $field!="removedOrAdded") {
                                $lineOptions = 0;
                                // $diff[$key]['field'] = $field;
                                // $diff[$key]['line_options'] = $lineOptions;
                                // $diff[$key]['field_index'] = $index;
                                // $diff[$key]['field_value'] = $value;
                                // $diff[$key]['prevVal'] = eval("return $compare;");
                                $this->insertDiff($field, $index, $value, $other_value, $lineOptions, $evidencia);
                            }
                        }else{
                            $flag_checkbox=0;
                            if ($field == $removedOrAdded) {
                                $lineOptions = 1;
                            }

                            if(isset($obj3) && $obj3 && $obj3->variables->$field->subformat=="checkbox" && $obj3->variables->$field->value==""){
                                $obj3->variables->$field->value=0;
                                if ($other_value == $obj3->variables->$field->value && ($value=="" || $value==0)) {
                                    $flag_checkbox=1;
                                }
                            }

                            if (isset($obj3) && $other_value == $obj3->variables->$field->value && $obj3) {
                                $lineOptions = 1;
                            }

                            if(!$flag_checkbox){
                                $diff[$key]['field'] = $field;
                                $diff[$key]['line_options'] = $lineOptions;
                                $diff[$key]['field_index'] = $index;
                                $diff[$key]['field_value'] = $value;
                                $diff[$key]['prevVal'] = $other_value;

                                $this->insertDiff($field, $index, $value, $other_value, $lineOptions, $evidencia);
                            }
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

        //$arr->removedOrAdded = 'removed';

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
        $info="";
        if($evidencia->getRecord()->getJson()){
            $config_json = json_decode($evidencia->getRecord()->getJson(),TRUE);
            if(array_key_exists($field, $config_json["configuration"]["variables"]) && $config_json["configuration"]["variables"][$field]["info"]!=""){
                $info=$config_json["configuration"]["variables"][$field]["info"];
            }
        }

        if($info==""){
            $info=$field;
        }
        
        $stepHistory = new CVRecordsHistory();
        $stepHistory->setField($field);
        $stepHistory->setInfo($info);
        $stepHistory->setIndex($index);
        $stepHistory->setValue($value);


        if (strpos($stepHistory->getValue(), ';base64,') !== false) {
            $extension = explode("/", mime_content_type($stepHistory->getValue()))[1];
            $path=$this->container->getParameter('crt.root_dir')."/file-record/".date('Y')."/".date('m');
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            $file = file_get_contents($stepHistory->getValue());
            $fileName = md5(uniqid()).'.'.$extension;
            file_put_contents($path."/".$fileName, $file);
            $url_file=$path."/".$fileName;
            Utils::setCertification($this->container, $url_file, "file-record", $evidencia->getRecord()->getId());   
        } 

        $stepHistory->setPrevValue($prevValue);
        $stepHistory->setLineOptions($lineOptions);
        $stepHistory->setSignature($evidencia);

        $this->em->persist($stepHistory);
        //$this->em->flush();

        return $stepHistory;
    }

    public function get_users_actions($user,$type){
        $users[]=$user;
        if($type){
            $delegations=$this->em->getRepository('NononsenseHomeBundle:Delegations')->findBy(array('sustitute' => $user,"type" => $type,"deleted" => NULL));
            foreach($delegations as $delegation){
                $users[]=$delegation->getUser();
            }
        }

        return $users;
    }
}
