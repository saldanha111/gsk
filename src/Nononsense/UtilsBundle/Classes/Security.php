<?php

namespace Nononsense\UtilsBundle\Classes;

use Nononsense\DocumentBundle\Entity\APIKeys;

/**
 * Static external security methods to be used by any class
 */
class Security 
{
    public function __construct($user, $auth, $level, $masterKey, $em, $expirationTime, $CAcerts) {
        if (PHP_SAPI != 'cli') {
            $this->user = $user->getToken()->getUser();
        }
        $this->auth = $auth;
        $this->level = $level;
        $this->masterKey = $masterKey;
        $this->em = $em;
        $this->expirationTime = $expirationTime;
        $this->CAcerts = $CAcerts;
    }

    public function permissionSeccion($subseccion){

        $is_grant = 0;

        $subseccionObj = $this->em->getRepository('NononsenseUserBundle:Subsecciones')->findOneByNameId($subseccion);

        $Egroups =$this->em->getRepository('NononsenseGroupBundle:GroupUsers')->findBy(array("user"=>$this->user));
        foreach ($Egroups as $groupUser) {

            $groupSubseccionObj = $this->em->getRepository('NononsenseUserBundle:GroupsSubsecciones')->findBy(array('group'=>$groupUser->getGroup(), 'subseccion'=>$subseccionObj));
            if($groupSubseccionObj){
                $is_grant = 1;
                break;
            }
        }

        return $is_grant;
    }
    
    public function permission ($templateId) 
    {
        $userid = $this->user->getId();
        
        if ($this->auth->isGranted('ROLE_ADMIN')) {
            return 'rw';
        } else if ($this->isAuthor ($userid, $templateId)) {
            return 'rw';
        } else if ($this->isPublic ($templateId)) {
            if ($this->auth->isGranted('ROLE_EDITOR')){
                return 'rw';
            } else {
                return 'r-';
            }
        } else {
            $read = $this->em
                         ->getRepository('NononsenseDocumentBundle:Templates')
                         ->access($userid, $templateId, 'read');
            $write = $this->em
                         ->getRepository('NononsenseDocumentBundle:Templates')
                         ->access($userid, $templateId, 'write');
            if ($read && $write) {
                return 'rw';
            } else if ($read) {
                return 'r-';
            } else {
                return '--';
            }
        }
    }
    
    public function isAuthor ($templateId) 
    {
        $auth = $this->em
                     ->getRepository('NononsenseDocumentBundle:Templates')
                     ->findOneBy(array('user' => $this->user, 'id' => $templateId));  
        
        if ($auth) {
            return true;
        } else {
            return false;
        }
    }
    
    public function isPublic ($templateId) 
    {
        $publ = $this->em
                     ->getRepository('NononsenseDocumentBundle:Templates')
                     ->findOneBy(array('public' => 1, 'id' => $templateId));  
        
        if ($publ) {
            return true;
        } else {
            return false;
        }
    }
    
    public function check4Tampering ($id, $request) 
    {
        $APIKEY = $request->request->get('APIKEY');
        $uniqid = $request->request->get('uniqid');
        $timestamp = $request->request->get('timestamp');
        $options = $request->request->get('preoptions');
        $keySource = $id . '-' . $timestamp . '-' . $uniqid . '-' . $options;
        $dataKey = sha1($keySource, true);
        $valid = \Nononsense\UtilsBundle\Classes\Utils::apikey_control($APIKEY, $dataKey, $this->masterKey);
        return $valid;
    }
    
    public function checkAPIToken ($keyVars) 
    {
        $message = 'rejected';
        $granted = false;
        $error = 1;
            
        if (empty($keyVars['APIKEY'])) {
            $message = 'Incorrect APIKEY value';
        } else if ($this->checkTimestamp($keyVars['timestamp']) === false) {
            $message = 'Incorrect timestamp';
        } else {
            if ($keyVars['id'] == 'none') {
                $keySource = $keyVars['timestamp'] . '-' . $keyVars['uniqid'];
            } else {
                $keySource = $keyVars['id'] . '-' . $keyVars['timestamp'] . '-' . $keyVars['uniqid'];
            }
            if (!empty($keyVars['options'])) {
                $keySource .= '-' . $keyVars['options'];
            }
            $dataKey = sha1($keySource, true);
            $access = \Nononsense\UtilsBundle\Classes\Utils::apikey_control($keyVars['APIKEY'], $dataKey, $this->masterKey);
            if ($access) {
                $message = 'Correct APIKEY.';
                $granted = true;
                $error = 0;
            } else {
                $message = 'Incorrect APIKEY.';
                $granted = false;
            }
        }
        return array('granted' => $granted, 
                     'message' => $message, 
                     'error' => $error);
    }
    
    public function checkTimestamp($timestamp)
    {
        //check that it is a reasonable timestamp
        $requestTime = intval($timestamp);
        $currentTime = time();
        $timeDiff = abs($currentTime - $requestTime);
        if ($this->level == 1.5 || $this->level == 2.5) {
            $maxDiff =  $this->expirationTime;
        } else {
            $maxDiff = 31536000; //one year
        }
        if ($timeDiff > $maxDiff){
            return false;
        } else {
            return true;
        }
    }
    
    public  function onceTokenAPI($keyVars)
    {
        $APIKEY = $keyVars['APIKEY'];
        $timestamp = $keyVars['timestamp'];
        $uniqid = $keyVars['uniqid'];
        $id = $keyVars['id'];
        $level = $this->level;
        //Check if the timestamp is too old for levels 1.5 and 2.5
        if (($level == 1.5 || $level == 2.5)
            && $this->checkTimestamp($timestamp) === false){
            return array('granted' => false, 
                         'message' => 'This APIKEY has expired.', 
                         'error' => 3); 
        }
        //we have to further check that the APIKEY has not been already used
        $message = 'This APIKEY has expired.';
        $error = 2;
        $granted = false;
        $tokenAPI = $this->em
                ->getRepository('NononsenseDocumentBundle:APIKeys')
                ->find($APIKEY);

        if (empty($tokenAPI)) {
            //before continuing we have to save this api token so it can only be used once
            $key = new APIKeys();
            $key->setId($APIKEY);
            if (!empty($enduserid)) {
                $key->setUserid($enduserid);
            } else {
                $key->setUserid('anonymous');
            }
            $key->setTemplateId($id);
            $key->setActionType('preview');
            $key->setActive(0);
            $key->setPublicKey($timestamp . $uniqid);               
            $this->em->persist($key);
            $this->em->flush();
            
            $message = 'Access granted.';
            $error = 0;
            $granted = true;
        } 
        return array('granted' => $granted, 
                     'message' => $message, 
                     'error' => $error); 
    }
    
    public  function twiceTokenAPI($APIKEY, $timestamp, $uniqid, $templateId)
    {
        $level = $this->level;
        if ($level == 0) {
            //do not implement any additional security feature
            $message = 'Access granted';
            $granted = true;
            $error = 0;
        } else if ($level == 1 || $level == 1.5) {
            if ($level == 1.5 && $this->checkTimestamp($timestamp) === false){
                return array('granted' => false, 
                             'message' => 'This APIKEY has expired.', 
                             'error' => 3); 
            }
            //only checks that the APIKEY is correctly formed
            $check = $this->checkAPIToken($keyVars);
            if ($check['granted']) {
                $message = $check['message'];
                $granted = true;
                $error = 0;
            } else {
                $message = $check['message'];
                $granted = false;
            }        
        } else if ($level == 2 || $level == 2.5) {
            if ($level == 2.5 && $this->checkTimestamp($timestamp) === false){
                return array('granted' => false, 
                             'message' => 'This APIKEY has expired.', 
                             'error' => 3); 
            }
            //we have to further check that this APIKEY has just been used once before
            $message = 'This APIKEY has expired.';
            $error = 3;
            $granted = false;
            $searchData = array();
            $searchData['id'] = $APIKEY;
            $searchData['publicKey'] = $timestamp . $uniqid;
            $searchData['templateId'] = $templateId;

            $tokenAPI = $this->em
                    ->getRepository('NononsenseDocumentBundle:APIKeys')
                    ->findBy($searchData);    

            if (!empty($tokenAPI[0])) {    
                //We also check that it has only been used once to avoid attacks!!!
                if ($tokenAPI[0]->getActive() < 1) {
                    //update active state
                    $tokenAPI[0]->setActive(1);                
                    $this->em->persist($tokenAPI[0]);
                    $this->em->flush();
                    $message = 'Access granted.';
                    $granted = true;
                    $error = 0;
                } 
            }
        }
        return array('granted' => $granted, 
                     'message' => $message, 
                     'error' => $error); 
    }
    
    public  function accessPolicy($keyVars, $tokenPolicy = 1)
    {
        $message = 'Access not granted';
        $granted = false;
        $error = 1;
        $level = $this->level;
        if (!empty($options->securityLevel)) {
            $overrideSecurity = floatval($options->securityLevel);
            $currentSecurity = floatval($this->level);
            if ($overrideSecurity > $currentSecurity) {
                $level = $options->securityLevel;
            } 
        }
        
        if ($level == 0) {
            //do not implement any additional security feature
            $message = 'Access granted';
            $granted = true;
            $error = 0;
        } else if ($level == 1 || $level == 1.5) {
            //only checks that the APIKEY is correctly formed
            $check = $this->checkAPIToken($keyVars);
            if ($check['granted']) {
                $message = $check['message'];
                $granted = true;
                $error = 0;
            } else {
                $message = $check['message'];
                $granted = false;
            }        
        } else if ($level == 2 || $level == 2.5) {
            //also checks that the APIKEY has only been used one for
            // tokenPolicy = 1 or that has been previously generated for
            // an additional use if tokenPolicy = 2
            $check = $this->checkAPIToken($keyVars);
            if ($check['granted']) {
                // we have to further proceed depending on tokenPolicy
                if ($tokenPolicy == 0) {
                    $message = 'Access granted';
                    $granted = true;
                    $error = 0;
                } else if ($tokenPolicy == 1) {
                    $valid = $this->onceTokenAPI($keyVars);
                    if ($valid['granted']) {
                        $message = $valid['message'];
                        $granted = true;
                        $error = 0;
                    } else {
                        $message = $valid['message'];
                        $granted = false;
                        $error = 2;
                    }
                } else if ($tokenPolicy == 2) {
                    $valid = $this->twiceTokenAPI($keyVars['APIKEY'], 
                                                  $keyVars['timestamp'],
                                                  $keyVars['uniqid'],
                                                  $keyVars['id']);
                    if ($valid['granted']) {
                        $message = $valid['message'];
                        $granted = true;
                        $error = 0;
                    } else {
                        $message = $valid['message'];
                        $granted = false;
                        $error = 3;
                    }
                }
            } else {
                $message = $check['message'];
                $granted = false;
            }
        }
        
        return array('granted' => $granted, 
                     'message' => $message, 
                     'error' => $error);
    }
    
    public function access($id, $request, $backoffice, $tokenPolicy = 1)
    {
        $APIKEY = '';
        $timestamp = '';
        $uniqid = '';
        //check for POST data
        $postData = $request->request->all();
        foreach ($postData as $key => $value) {
            ${$key} = $value;
        }
        
        //check for GET data
        $queryData = $request->query->all();
        foreach ($queryData as $key => $value) {
            ${$key} = $value;
        }
        $host = $request->getHttpHost();
        $referer = $request->headers->get('referer');
        //initialize options parameters
        $params = array(
            'version' => '1.0',
            'pathSave' => NULL,
            'data' => NULL,
            'requestConfigURI' => NULL,
            'requestDataURI' => NULL,
            'responseDataURI' => NULL,
            'requestExternalJS' => NULL,
            'requestExternalCSS' => NULL,
            'custom' => NULL,
            'display' => 'document',
            'comments' => '',
            'host' => $host,
            'enduserid' => 'anonymous',
            'identifier' => '',
            'reference' => '',
            'responseURL' => NULL,
            'docFormat' => 'odt',
            'name' => NULL,
            'mimeType' => NULL,
            'token' => NULL,
            'trackID' => NULL,
            'referer' => $referer,
            'documentName' => NULL,
            'securityLevel' => NULL,
            'access' => 'unknown',
            'email' => NULL,
            'attachments' => '[]',
            'deletedAttachments' => '',
            'usageId' => NULL,
            'prefix' => '',
            'forward' => '',
            'notify' => '',
            'enforceValidation' => NULL,
            'captcha' => 0,
            'captchaStamp' => '',
            'captchaHash' => '',
            'secVars' => \Nononsense\UtilsBundle\Classes\Utils::base64_encode_url_safe('{}'),
            'position' => NULL,
            'response' => 'download',
            'callback' => '',
            'url' => ''
        );

        if (!isset($options)) {
            $options = '';
            $obj = json_decode('{}');
        } else {
            $obj = json_decode(\Nononsense\UtilsBundle\Classes\Utils::base64_decode_url_safe($options));
        }
        
        $access = new \stdClass();
        $access->APIKEY = $APIKEY;
        $access->timestamp = $timestamp;
        $access->uniqid = $uniqid;
        if ($obj !== NULL){
            foreach ($params as $key => $value) {
                if (isset($obj->{$key})) {
                    $access->{$key} = $obj->{$key};
                } else {
                    $access->{$key} = $value;
                }
            }
        } else {
            $access->granted = false;
            $access->message = 'Incorrect request parameters.';
            $access->error = 6;
            //we generate default values for templates that have open access control
            foreach ($params as $key => $value) {
                $access->{$key} = $value;
            }
            return $access;
        }
        if ($backoffice && empty($APIKEY)) {
            $access->granted = true;
            $access->message = '';
            $access->referer = $referer;
            $access->error = false;
        } else {
            $keyVars = array('APIKEY' => $APIKEY,
                             'timestamp' => $timestamp,
                             'uniqid' => $uniqid,
                             'options' => $options,
                             'id' => $id
               );
            $accessData = $this->accessPolicy($keyVars, $tokenPolicy); 
            $access->granted = $accessData['granted'];
            $access->message = $accessData['message'];
            $access->error = $accessData['error'];
        }
        
        return $access;
    }
    
    public function curlRequest ($requestDataURI, $attachments = NULL) {
        $status = 'KO';
        $externalData = '';
        $myRequest = json_decode($requestDataURI);
        $requestURL = $myRequest->URL;
        if (!empty($myRequest->requestData)) {
            $requestData = urldecode($myRequest->requestData);
        } else {
            $requestData = 'docxpresso_' . rand(9999, 999999999);
        }

        $timestamp = time();
        $uniqid = uniqid() . rand(9999, 999999999);
        $dataKey = sha1($timestamp . '-' . $uniqid . '-' . $requestData , true);
        if ($myRequest !== false && !empty($requestURL)) {
            $newAPIKEY = bin2hex(\Nononsense\UtilsBundle\Classes\Utils::sha1_hmac($this->masterKey, $dataKey));
            $curlData = array('data' =>  $requestData,
                              'timestamp' => $timestamp,
                              'uniqid' => $uniqid,
                              'APIKEY' => $newAPIKEY
                );
            if ($attachments !== NULL){
                foreach($attachments as $file => $attachment){
                    if (is_object($attachment)){
                        $curlData[$file] = '@' . $attachment->getRealPath()
                            . ';filename=' . urldecode($file) . $attachment->getClientOriginalExtension()
                            . ';type='     . $attachment->getClientMimeType();
                    } else {
                        $curlData[$file] = '';
                    }
                }
            }
            $dataServer = curl_init();
            curl_setopt($dataServer, CURLOPT_URL, $requestURL); 
            //Modify the following line to avoid man in the middle attacks
            curl_setopt($dataServer, CURLOPT_SSL_VERIFYPEER, false);
            //curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, TRUE); 
            //curl_setopt ($ch, CURLOPT_CAINFO, $this->CAcerts);
            //curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($dataServer, CURLOPT_HEADER, 1);  
            curl_setopt($dataServer, CURLOPT_POST, 1);  
            curl_setopt($dataServer, CURLOPT_VERBOSE, 1);  
            curl_setopt($dataServer, CURLOPT_FOLLOWLOCATION, 1);  
            curl_setopt($dataServer, CURLOPT_FRESH_CONNECT, 1);  
            curl_setopt($dataServer, CURLOPT_RETURNTRANSFER, 1);  
            curl_setopt($dataServer, CURLINFO_HEADER_OUT, 1);  
            curl_setopt($dataServer, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($dataServer, CURLOPT_POSTFIELDS, $curlData);

            $serverResponse = curl_exec($dataServer);
            $header_size = curl_getinfo($dataServer, CURLINFO_HEADER_SIZE);
            $header = substr($serverResponse, 0, $header_size);
            $externalData = substr($serverResponse, $header_size);
            curl_close($dataServer);
            $regex = '/HTTP\/[0-9]{1}\.[0-9]{1} ([0-9]{3}) /i';
            preg_match_all($regex, $header, $matches);
        } else {
            $status = 'Incorrect requestDataURI format.';
        }
        
        if (in_array('200', $matches[1])) {
            $status = 'OK';
        }
        
        $result = array();
        $result['status'] = $status;
        $result['externalData'] = $externalData;
        $result['debug'] = $serverResponse;
        return $result;
    }
    
    public function generateAPIKEY ($id, $timestamp, $uniqid, $options) {
        $keySource = $id . '-' . $timestamp . '-' . $uniqid . '-' . $options;
        $data = sha1($keySource, true);
        $APIKEY = \Nononsense\UtilsBundle\Classes\Utils::sha1_hmac($this->masterKey, $data);
        return bin2hex($APIKEY);
    }
    
    public function secureVars($loadedData) {
        $uniqid = uniqid() . mt_rand();
        $secVars = new \stdClass();
        $secVars->uniqid = $uniqid;
        $secVars->data = new \stdClass();
        $loaded = json_decode($loadedData);
        foreach ($loaded as $key => $value) {
            $secVars->data->{$key} = $this->hashVar($value, $uniqid);
        }
        return json_encode($secVars);
    }
    
    public function hashVar($value, $uniqid){
        $hashes = array();
        if (\is_string($value)) {
            $input = $value . '-' . $uniqid;
            $hashes[] = bin2hex(\Nononsense\UtilsBundle\Classes\Utils::sha1_hmac($this->masterKey, $input));
        } else if (\is_array($value)) {
            foreach ($value as $val) {
                $input = $val . '-' . $uniqid;
                $hashes[] = bin2hex(\Nononsense\UtilsBundle\Classes\Utils::sha1_hmac($this->masterKey, $input));
            }
        }
        return $hashes;
    }
    
    public function checkHashVar($value, $hashes, $uniqid){
        $pass = true;
        $input = $this->hashVar($value, $uniqid);
        $inputLength = \count($input);
        $hashLength = \count($hashes);
        if ($inputLength != $hashLength){
            return false;
        }
        for ($j = 0; $j < $inputLength; $j++) {
            if($input[$j] != $hashes[$j]){
                $pass = false;
                break;
            }
        }
        return $pass;
    }
    
    public function captchaHash($id, $captchaStamp, $captcha){
        $input = $id . '-' . $captchaStamp . '-' . $captcha;
        $hash = bin2hex(\Nononsense\UtilsBundle\Classes\Utils::sha1_hmac($this->masterKey, $input));     
        return $hash;
    }
    
    public function checkCaptcha($id, $captchaStamp, $captchaHash){
        $input_0 = $id . '-' . $captchaStamp . '-0';
        $hash_0 = bin2hex(\Nononsense\UtilsBundle\Classes\Utils::sha1_hmac($this->masterKey, $input_0));     
        $input_1 = $id . '-' . $captchaStamp . '-1';
        $hash_1 = bin2hex(\Nononsense\UtilsBundle\Classes\Utils::sha1_hmac($this->masterKey, $input_1));
        if ($captchaHash == $hash_0) {
            return 0;
        } else if ($captchaHash == $hash_1) {
            return 1;
        } else {
            return 2;
        }
    }

}
