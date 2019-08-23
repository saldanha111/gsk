<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 23/08/2019
 * Time: 9:03
 */

/*
 *
 */
$sleep = 1800; // 30 minutes


$host = trim(shell_exec('hostname'));
echo 'host: ---' . $host . '---' . PHP_EOL;
if ($host == 'DESKTOP-8TQA670') {
    $baseURL = 'http://gsk.local/';
} else {
    $baseURL = 'http://gsk.docxpresso.org/';
}
$urlprocess = $baseURL . 'backoffice_bloquear_registros';

//echo 'do nothing: '.$urlprocess;
//exit;
//echo 'error';

//infinite loop
while (true) {

    //OUT

    $hourNow = date("G");
    $timeNow = date("Ymd");
    //$dayInText = date("D");

    if($hourNow == 9){
        // 9.00 am process
        //echo 'dentro';
        $yerterday = strtotime('-1 day');
        $fileResult = "Registros_bloqueados" . $timeNow . ".txt";

        if(file_exists($fileResult)){
            //echo 'file_exists';
        }else{
            //echo file_get_contents($urlprocess);
            $response = file_get_contents($urlprocess);

            file_put_contents($fileResult,$response);
        }

    }

    //$response = file_get_contents($urlout . $id . '?apikey=' . $secret);
    sleep($sleep);
}



