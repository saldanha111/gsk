<?php

namespace Nononsense\UtilsBundle\Classes;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Nononsense\HomeBundle\Entity\Certifications;
use Nononsense\HomeBundle\Entity\CertificationsType;

/**
 * Static methods to be used by any class
 */
class Utils
{
    /**
     * Checks the validity of an API key
     * 
     * @param string $key
     * @param string $data
     * @return boolean
     */
    static public function apikey_control($key, $data, $masterKey)
    {
        $resultbin =  self::sha1_hmac($masterKey , $data);
        $result = bin2hex($resultbin);
        if ($key == $result) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Encodes in base64 url safe
     * 
     * @param string $str
     * @return string
     */
    static public function base64_encode_url_safe($str)
    {
        return strtr(base64_encode($str), '+/=', '-_,');
    }
    
    /**
     * Decodes base64 url safe
     * 
     * @param string $str
     * @return string
     */
    static public function base64_decode_url_safe($str)
    {
        return base64_decode(strtr($str, '-_,', '+/='));
    }
    
    /**
     * Generates an empty colored png
     * 
     * @param array $options
     * @return string
     */
    static public function generateColoredPNG($options = array())
    {
        $color = array();
        for ($j = 0; $j < 3; $j++) {
            $color[] = rand(0, 255);
        }
        ob_start (); 
            $png_image = imagecreate($options['width'], $options['width']);
            imagecolorallocate($png_image, $color[0], $color[1], $color[2]);
            imagepng($png_image);
            $image_data = ob_get_contents (); 
        ob_end_clean (); 

        return 'data:image/png;base64,' . base64_encode ($image_data);
    }
    
    /**
     * Generates a random color in hezadecimal notation
     * 
     * @return string
     */
    static public function generateRandomColor()
    {
        $color = array();
        for ($j = 0; $j < 3; $j++) {
            $color[] = (string) dechex(rand(0, 255));
        }
        return '#' . $color[0] . $color[1] . $color[2];
    }
    
    /**
     * Resizes the uploaded image and saves it to the requested directory
     * 
     * @param string $base64
     * @param int $width
     * @param int $height
     * @param string $name
     * @return boolean
     */
    static public function resize2JPG($base64, $width, $height, $quality, $name)
    {
        $raw = base64_decode(str_replace('data:image/png;base64,', '', $base64));
        $image = imagecreatefromstring($raw);
        $x = imagesx($image);
        $y = imagesy($image);
        $new = imagecreatetruecolor($width * 0.8, $height * 0.8);
        imagecopyresampled($new, $image, 0, 0, 0, 0, $width * 0.8, $height * 0.8, $x, $y);
        $result = imagejpeg($new, $name, $quality);
        imagedestroy($image);
        imagedestroy($new);
        return $result;
    }



    /**
     * Returns the countries
     */
    static public function getCountries() {
        $countries = array(
                    'AF' => 'Afghanistan',
                    'AX' => 'Åland Islands',
                    'AL' => 'Albania',
                    'DZ' => 'Algeria',
                    'AS' => 'American Samoa',
                    'AD' => 'Andorra',
                    'AO' => 'Angola',
                    'AI' => 'Anguilla',
                    'AQ' => 'Antarctica',
                    'AG' => 'Antigua and Barbuda',
                    'AR' => 'Argentina',
                    'AM' => 'Armenia',
                    'AW' => 'Aruba',
                    'AC' => 'Ascension Island',
                    'AU' => 'Australia',
                    'AT' => 'Austria',
                    'AZ' => 'Azerbaijan',
                    'BS' => 'Bahamas',
                    'BH' => 'Bahrain',
                    'BD' => 'Bangladesh',
                    'BB' => 'Barbados',
                    'BY' => 'Belarus',
                    'BE' => 'Belgium',
                    'BZ' => 'Belize',
                    'BJ' => 'Benin',
                    'BM' => 'Bermuda',
                    'BT' => 'Bhutan',
                    'BO' => 'Bolivia',
                    'BA' => 'Bosnia and Herzegovina',
                    'BW' => 'Botswana',
                    'BV' => 'Bouvet Island',
                    'BR' => 'Brazil',
                    'IO' => 'British Indian Ocean Territory',
                    'VG' => 'British Virgin Islands',
                    'BN' => 'Brunei',
                    'BG' => 'Bulgaria',
                    'BF' => 'Burkina Faso',
                    'BI' => 'Burundi',
                    'KH' => 'Cambodia',
                    'CM' => 'Cameroon',
                    'CA' => 'Canada',
                    'IC' => 'Canary Islands',
                    'CV' => 'Cape Verde',
                    'BQ' => 'Caribbean Netherlands',
                    'KY' => 'Cayman Islands',
                    'CF' => 'Central African Republic',
                    'EA' => 'Ceuta and Melilla',
                    'TD' => 'Chad',
                    'CL' => 'Chile',
                    'CN' => 'China',
                    'CX' => 'Christmas Island',
                    'CP' => 'Clipperton Island',
                    'CC' => 'Cocos [Keeling] Islands',
                    'CO' => 'Colombia',
                    'KM' => 'Comoros',
                    'CG' => 'Congo - Brazzaville',
                    'CD' => 'Congo - Kinshasa',
                    'CK' => 'Cook Islands',
                    'CR' => 'Costa Rica',
                    'CI' => 'Côte d’Ivoire',
                    'HR' => 'Croatia',
                    'CU' => 'Cuba',
                    'CW' => 'Curaçao',
                    'CY' => 'Cyprus',
                    'CZ' => 'Czech Republic',
                    'DK' => 'Denmark',
                    'DG' => 'Diego Garcia',
                    'DJ' => 'Djibouti',
                    'DM' => 'Dominica',
                    'DO' => 'Dominican Republic',
                    'EC' => 'Ecuador',
                    'EG' => 'Egypt',
                    'SV' => 'El Salvador',
                    'GQ' => 'Equatorial Guinea',
                    'ER' => 'Eritrea',
                    'EE' => 'Estonia',
                    'ET' => 'Ethiopia',
                    'EU' => 'European Union',
                    'FK' => 'Falkland Islands',
                    'FO' => 'Faroe Islands',
                    'FJ' => 'Fiji',
                    'FI' => 'Finland',
                    'FR' => 'France',
                    'GF' => 'French Guiana',
                    'PF' => 'French Polynesia',
                    'TF' => 'French Southern Territories',
                    'GA' => 'Gabon',
                    'GM' => 'Gambia',
                    'GE' => 'Georgia',
                    'DE' => 'Germany',
                    'GH' => 'Ghana',
                    'GI' => 'Gibraltar',
                    'GR' => 'Greece',
                    'GL' => 'Greenland',
                    'GD' => 'Grenada',
                    'GP' => 'Guadeloupe',
                    'GU' => 'Guam',
                    'GT' => 'Guatemala',
                    'GG' => 'Guernsey',
                    'GN' => 'Guinea',
                    'GW' => 'Guinea-Bissau',
                    'GY' => 'Guyana',
                    'HT' => 'Haiti',
                    'HM' => 'Heard Island and McDonald Islands',
                    'HN' => 'Honduras',
                    'HK' => 'Hong Kong SAR China',
                    'HU' => 'Hungary',
                    'IS' => 'Iceland',
                    'IN' => 'India',
                    'ID' => 'Indonesia',
                    'IR' => 'Iran',
                    'IQ' => 'Iraq',
                    'IE' => 'Ireland',
                    'IM' => 'Isle of Man',
                    'IL' => 'Israel',
                    'IT' => 'Italy',
                    'JM' => 'Jamaica',
                    'JP' => 'Japan',
                    'JE' => 'Jersey',
                    'JO' => 'Jordan',
                    'KZ' => 'Kazakhstan',
                    'KE' => 'Kenya',
                    'KI' => 'Kiribati',
                    'XK' => 'Kosovo',
                    'KW' => 'Kuwait',
                    'KG' => 'Kyrgyzstan',
                    'LA' => 'Laos',
                    'LV' => 'Latvia',
                    'LB' => 'Lebanon',
                    'LS' => 'Lesotho',
                    'LR' => 'Liberia',
                    'LY' => 'Libya',
                    'LI' => 'Liechtenstein',
                    'LT' => 'Lithuania',
                    'LU' => 'Luxembourg',
                    'MO' => 'Macau SAR China',
                    'MK' => 'Macedonia',
                    'MG' => 'Madagascar',
                    'MW' => 'Malawi',
                    'MY' => 'Malaysia',
                    'MV' => 'Maldives',
                    'ML' => 'Mali',
                    'MT' => 'Malta',
                    'MH' => 'Marshall Islands',
                    'MQ' => 'Martinique',
                    'MR' => 'Mauritania',
                    'MU' => 'Mauritius',
                    'YT' => 'Mayotte',
                    'MX' => 'Mexico',
                    'FM' => 'Micronesia',
                    'MD' => 'Moldova',
                    'MC' => 'Monaco',
                    'MN' => 'Mongolia',
                    'ME' => 'Montenegro',
                    'MS' => 'Montserrat',
                    'MA' => 'Morocco',
                    'MZ' => 'Mozambique',
                    'MM' => 'Myanmar [Burma]',
                    'NA' => 'Namibia',
                    'NR' => 'Nauru',
                    'NP' => 'Nepal',
                    'NL' => 'Netherlands',
                    'AN' => 'Netherlands Antilles',
                    'NC' => 'New Caledonia',
                    'NZ' => 'New Zealand',
                    'NI' => 'Nicaragua',
                    'NE' => 'Niger',
                    'NG' => 'Nigeria',
                    'NU' => 'Niue',
                    'NF' => 'Norfolk Island',
                    'KP' => 'North Korea',
                    'MP' => 'Northern Mariana Islands',
                    'NO' => 'Norway',
                    'OM' => 'Oman',
                    'QO' => 'Outlying Oceania',
                    'PK' => 'Pakistan',
                    'PW' => 'Palau',
                    'PS' => 'Palestinian Territories',
                    'PA' => 'Panama',
                    'PG' => 'Papua New Guinea',
                    'PY' => 'Paraguay',
                    'PE' => 'Peru',
                    'PH' => 'Philippines',
                    'PN' => 'Pitcairn Islands',
                    'PL' => 'Poland',
                    'PT' => 'Portugal',
                    'PR' => 'Puerto Rico',
                    'QA' => 'Qatar',
                    'RE' => 'Réunion',
                    'RO' => 'Romania',
                    'RU' => 'Russia',
                    'RW' => 'Rwanda',
                    'BL' => 'Saint Barthélemy',
                    'SH' => 'Saint Helena',
                    'KN' => 'Saint Kitts and Nevis',
                    'LC' => 'Saint Lucia',
                    'MF' => 'Saint Martin',
                    'PM' => 'Saint Pierre and Miquelon',
                    'VC' => 'Saint Vincent and the Grenadines',
                    'WS' => 'Samoa',
                    'SM' => 'San Marino',
                    'ST' => 'São Tomé and Príncipe',
                    'SA' => 'Saudi Arabia',
                    'SN' => 'Senegal',
                    'RS' => 'Serbia',
                    'SC' => 'Seychelles',
                    'SL' => 'Sierra Leone',
                    'SG' => 'Singapore',
                    'SX' => 'Sint Maarten',
                    'SK' => 'Slovakia',
                    'SI' => 'Slovenia',
                    'SB' => 'Solomon Islands',
                    'SO' => 'Somalia',
                    'ZA' => 'South Africa',
                    'GS' => 'South Georgia and the South Sandwich Islands',
                    'KR' => 'South Korea',
                    'SS' => 'South Sudan',
                    'ES' => 'Spain',
                    'LK' => 'Sri Lanka',
                    'SD' => 'Sudan',
                    'SR' => 'Suriname',
                    'SJ' => 'Svalbard and Jan Mayen',
                    'SZ' => 'Swaziland',
                    'SE' => 'Sweden',
                    'CH' => 'Switzerland',
                    'SY' => 'Syria',
                    'TW' => 'Taiwan',
                    'TJ' => 'Tajikistan',
                    'TZ' => 'Tanzania',
                    'TH' => 'Thailand',
                    'TL' => 'Timor-Leste',
                    'TG' => 'Togo',
                    'TK' => 'Tokelau',
                    'TO' => 'Tonga',
                    'TT' => 'Trinidad and Tobago',
                    'TA' => 'Tristan da Cunha',
                    'TN' => 'Tunisia',
                    'TR' => 'Turkey',
                    'TM' => 'Turkmenistan',
                    'TC' => 'Turks and Caicos Islands',
                    'TV' => 'Tuvalu',
                    'UM' => 'U.S. Outlying Islands',
                    'VI' => 'U.S. Virgin Islands',
                    'UG' => 'Uganda',
                    'UA' => 'Ukraine',
                    'AE' => 'United Arab Emirates',
                    'GB' => 'United Kingdom',
                    'US' => 'United States',
                    'UY' => 'Uruguay',
                    'UZ' => 'Uzbekistan',
                    'VU' => 'Vanuatu',
                    'VA' => 'Vatican City',
                    'VE' => 'Venezuela',
                    'VN' => 'Vietnam',
                    'WF' => 'Wallis and Futuna',
                    'EH' => 'Western Sahara',
                    'YE' => 'Yemen',
                    'ZM' => 'Zambia',
                    'ZW' => 'Zimbabwe',
                );

        return $countries;
    }


    /**
     * Slug a string. Remove non letters or digits, and trim and lowercase the string
     */
    static public function slugify($string)
    {
        $string = urldecode($string);
        // trim and lowercase the string
        $string = strtolower(trim($string, '-'));
        $string = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $string
        );

        $string = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $string
        );

        $string = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $string
        );

        $string = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'º', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $string
        );

        $string = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $string
        );

        $string = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'),
            array('n', 'N', 'c', 'C',),
            $string
        );
        // replace all non letters or digits by -
        $string = preg_replace('/\W+/', '-', $string); 

        // if no value set slug as 'undefined'
        if (empty($string)) {
            $string = 'undefined';
        }

        return $string;
    }
    
    /**
     * Returns the default validation types
     */
    static public function defaultValidations() {
        $rx = array();
        $rx['phone'] = '^[0-9\-\(\)\+\. ext]{9,31}$';
        $rx['empty'] =  '.+';
        $rx['email'] = '^[^@\s]+@[^@\s]+\.[^@\.\s]+$';
        $rx['numeric'] = '^[ 0-9\.\,]+$';
        $rx['URL'] = '^(https?:\/\/)([0-9a-zA-Z\.-_]+)\.([A-Za-z\.]{2,6})([\/\w\?=@:% \.-]*)*\/?$';
        //$rx['phone-VidSigner'] = '^\+[0-9]{9,16}$';

        return $rx;
    }
    
    /**
     * Parses a date expression into a regex
     */
    static public function parseDateRegex($str) {
        $rx = $str;
        //start the replacements
        //yyyy
        $rx = str_replace('yyyy', '[0-9]{4}', $rx);
        //yy
        $rx = str_replace('yy', '[0-9]{2}', $rx);
        //MM
        $rx = str_replace('MM', '[A-zÀ-ÿ]{3,20}', $rx);
        //M
        $rx = str_replace('M', '[A-zÀ-ÿ]{3}', $rx);
        //mm
        $rx = str_replace('mm', '[0-9]{2}', $rx);
        //m
        $rx = str_replace('m', '[0-9]{1,2}', $rx);
        //DD
        $rx = str_replace('DD', '[A-zÀ-ÿ]{3,20}', $rx);
        //D
        $rx = str_replace('D', '[A-zÀ-ÿ]{3}', $rx);
        //dd
        $rx = str_replace('dd', '[0-9]{2}', $rx);
        //d
        $rx = str_replace('d', '[0-9]{1,2}', $rx);
        
        return '^' . $rx . '$';
    }
    
    /**
     * Local dat formats
     */
    static public function defaultLocales($lang) {
        
        $locale = array();
        if ($lang == 'es'){
            $locale['date'] = 'dd/mm/yyyy';
            $locale['date2'] = 'd/m/Y';
        } else {
            $locale['date'] = 'mm/dd/yyyy';
            $locale['date2'] = 'm/d/Y';
        }
        
        return $locale;
    }
    
    /**
     * Makes a remote call via CURL
     * 
     * @param string $url remote addrees to connect
     * @return string
     * @access public
     */
    static public function plainCurlRequest ($url) {
        $status = 'KO';
        $data = '';
        
        $dataServer = curl_init();
        curl_setopt($dataServer, CURLOPT_URL, $url); 
        //Modify the following line to avoid man in the middle attacks
        curl_setopt($dataServer, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, TRUE); 
        //curl_setopt ($ch, CURLOPT_CAINFO, $this->CAcerts);
        //curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($dataServer, CURLOPT_HEADER, 1);   
        curl_setopt($dataServer, CURLOPT_VERBOSE, 1);  
        curl_setopt($dataServer, CURLOPT_FOLLOWLOCATION, 1);  
        curl_setopt($dataServer, CURLOPT_FRESH_CONNECT, 1);  
        curl_setopt($dataServer, CURLOPT_RETURNTRANSFER, 1);  
        curl_setopt($dataServer, CURLINFO_HEADER_OUT, 1);  
        curl_setopt($dataServer, CURLOPT_CONNECTTIMEOUT, 10);

        $serverResponse = curl_exec($dataServer);
        $header_size = curl_getinfo($dataServer, CURLINFO_HEADER_SIZE);
        $header = substr($serverResponse, 0, $header_size);
        $data = trim(substr($serverResponse, $header_size));
        curl_close($dataServer);
        $regex = '/HTTP\/[0-9]{1}\.[0-9]{1} ([0-9]{3}) /i';
        preg_match_all($regex, $header, $matches);
        
        
        if (in_array('200', $matches[1])) {
            $status = 'OK';
        }
        
        $result = array();
        $result['status'] = $status;
        $result['data'] = $data;
        $result['debug'] = $serverResponse;
        return $result;
    }
    
    
    
    /**
     * generates hmac with sha1
     * 
     * This method has been taken from the Wikipedia:
     * https://en.wikipedia.org/wiki/Hash-based_message_authentication_code
     * This content is released under CC-BY-SA:
     * http://creativecommons.org/licenses/by-sa/3.0/
     * 
     */
    
    static public function sha1_hmac($key,$data,$blockSize=64,$opad=0x5c,$ipad=0x36) {

        // Keys longer than blocksize are shortened
        if (strlen($key) > $blockSize) {
            $key = sha1($key,true);	
        }

        // Keys shorter than blocksize are right, zero-padded (concatenated)
        $key       = str_pad($key,$blockSize,chr(0x00),STR_PAD_RIGHT);	
        $o_key_pad = $i_key_pad = '';

        for($i = 0;$i < $blockSize;$i++) {
            $o_key_pad .= chr(ord(substr($key,$i,1)) ^ $opad);
            $i_key_pad .= chr(ord(substr($key,$i,1)) ^ $ipad);
        }

        return sha1($o_key_pad.sha1($i_key_pad.$data,true),true);
    }
    
    static public function getExtensions($container)
    {
        if ($container->hasParameter('uploadMSOffice') &&
            $container->getParameter('uploadMSOffice')) {
            $uploadExtensions = self::getUploadExtensions($container);
        } else {
            $uploadExtensions = self::getNativeExtensions($container);
        }
        return $uploadExtensions;
    }
    
    /*
     * gets the available extenions
     */
    static public function getUploadExtensions($container)
    {
        return explode(',', $container->getParameter('uploadExtensions'));
                
    }
    
    public static function getNativeExtensions($container)
    {
        return explode(',', $container->getParameter('nativeExtensions'));
    }
    
    /*
     * provides the required conversion maps
     */
    public static function getConversions()
    {
        return array('docx' => 'odt', 
                     'xlsx' => 'ods');
    }
    
    /*
     * provides the required conversion maps
     */
    public static function getInternalServices($container)
    {
        $services = array();
        $services[] = array(
                    'id' => 100000, 
                    'name' => $container->get('translator')->trans('Load CSV data'), 
                    'url' => $container->get('router')->generate('nononsense_auxiliar_predefined_service_loadCSV'));
        return $services;
    }
    
    /**
     * checks if a string is UTF-8 encoded
     * 
     * @access public
     * @param string $str
     * @static
     * @return boolean
     */
    public static function UTF8Encoded($str)
    {
        $str = preg_replace("#[\x09\x0A\x0D\x20-\x7E]#", "", $str);
        $str = preg_replace("#[\xC2-\xDF][\x80-\xBF]#", "", $str);
        $str = preg_replace("#\xE0[\xA0-\xBF][\x80-\xBF]#", "", $str);
        $str = preg_replace("#[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}#", "", $str);
        $str = preg_replace("#\xED[\x80-\x9F][\x80-\xBF]#", "", $str);
        $str = preg_replace("#\xF0[\x90-\xBF][\x80-\xBF]{2}#", "", $str);
        $str = preg_replace("#[\xF1-\xF3][\x80-\xBF]{3}#", "", $str);
        $str = preg_replace("#\xF4[\x80-\x8F][\x80-\xBF]{2}#", "", $str);

        if ($str == '') {
            return true;
        } else {
            return false;
        }
    }

    /** @deprecated  \Nononsense\UtilsBundle\Classes\Utils::getPaginator instead
     */
    public static function paginador($limit,$request,$url,$count,$base_url,$filtros)
    {
        $url_b=$_SERVER['REQUEST_URI'];
        if(!$filtros){
            if($url_b = preg_replace('/\?page=[^&]*/', '', $url_b)){}
            $data["character"]="?";
            //$url_b=$base_url.$url;
        }
        else{
            $url_b = preg_replace('/&?page=[^&]*/', '', $url_b);
            $data["character"]="&";
        }
        $page      = ($request->get('page', 0) > 0) ? $request->get('page')-1 : 0;
        $skip      = ($page ) * $limit;
        

        $data["needed"]=$count > $limit;
        $data["count"]=$count;
        $data["page"]=$page;
        $data["lastpage"]=(ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
        $data["limit"]=$limit;
        if (
            ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ( ! empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
            || ( ! empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)
            || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
        ) {
          $protocol = 'https://';
        }
        else{
          $protocol = 'http://';
        }
        $data["url"]=$protocol.$_SERVER["HTTP_HOST"].$url_b;
        $data["size"]=5;
        $data["skip"]=$skip ;
        return $data;
    }

    /**
     * @param $request
     * @param $limit
     * @param $count
     * @return array
     */
    public static function getPaginator($request,$limit,$count)
    {
        $data = [];
        $params = $request->query->all();
        unset($params["page"]);

        $url_b = $_SERVER['REQUEST_URI'];
        if (empty($params)) {
            $url_b = preg_replace('/\?page=[^&]*/', '', $url_b);
            $data["character"] = "?";
        } else {
            $url_b = preg_replace('/&?page=[^&]*/', '', $url_b);
            $data["character"] = "&";
        }

        $page = ($request->get('page', 0) > 0) ? $request->get('page') - 1 : 0;
        $skip = ($page) * $limit;

        $data["needed"] = $count > $limit;
        $data["count"] = $count;
        $data["page"] = $page;
        $data["lastpage"] = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
        $data["limit"] = $limit;
        if (
            ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ( ! empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
            || ( ! empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)
            || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
        ) {
          $protocol = 'https://';
        }
        else{
          $protocol = 'http://';
        }
        $data["url"] = $protocol . $_SERVER["HTTP_HOST"] . $url_b;
        $data["size"] = 5;
        $data["skip"] = $skip;

        return $data;
    }

    /**
     * @param Request $request
     * @return array
     */
    public static function getListFilters(Request $request)
    {
        $filters = [];

        if ($request->get("page")) {
            $filters["limit_from"] = $request->get("page") - 1;
        } else {
            $filters["limit_from"] = 0;
        }

        foreach ($request->query->all() as $key => $element) {
            if (strpos($key, 'f_') === 0 && ($element != '')) {
                $filterName = str_replace('f_', '', $key);
                $filters[$filterName] = $element;
            }
        }

        return $filters;
    }

    /**
    * Certifications API 
    *
    * @param string $path, string $method, array $data
    * @return json
    */
    public static function api3(string $url, $header = [], string $method = 'GET', $data = [])
    {
        // $url = $container->getParameter('api3.url');
        // $key = $container->getParameter('api3.key');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_HEADER, false);

        if ($method != 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $output = curl_exec($ch);
        if (!$output) {
            $output = curl_error($ch);
        }
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($info != 200 && $info != 201) {
            throw new \Exception($output.'/'.json_encode($data), 1);
        }

        return $output;
    }

    public static function setCertification($container, string $file, string $type, int $recordId)
    {
        $em = $container->get('doctrine.orm.entity_manager');

        $certificationsType = $em->getRepository(CertificationsType::class)->findOneBy(['name' => $type]);

        if (!$certificationsType) {
            $certificationsType = new CertificationsType();
            $certificationsType->setName($type);
        }

        $certification = new Certifications();
        $certification->setHash(hash_file('sha256', $file));
        $certification->setType($certificationsType);
        $certification->setRecordId($recordId);
        $certification->setPath($file);

        $certificationsType->addCertification($certification);

        $em->persist($certificationsType);
        $em->flush();

        return $certification;
    }

    public static function saveFile(string $file, string $type, string $path){
        
        $fileName = md5(uniqid()).'.pdf';

        $path = self::makeFolder($path, $type);

        file_put_contents($path.'/'.$fileName, $file);

        return $path.'/'.$fileName;
    }

    /*
    *
    */
    public static function generatePdf($container, string $title = 'GSK', string $subject = 'GSK', string $html = '', string $type, string $path){

        $pdf = $container->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetAuthor('GSK');
        $pdf->SetTitle($title);
        $pdf->SetSubject($subject);

        $pdf->SetHeaderData(false, false, $title);

        $pdf->AddPage();
        
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        $fileName = md5(uniqid()).'.pdf';

        $path = self::makeFolder($path, $type);

        $pdf->Output($path.'/'.$fileName, 'F');

        return $path.'/'.$fileName;
    }

    public static function makeFolder(string $path, string $type){

        $path = $path.'/'.$type.'/'.date('Y').'/'.date('m');

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

}
