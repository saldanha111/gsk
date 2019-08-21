<?php

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
$urlprocess = $baseURL . 'notificacion_semanal';

//echo 'do nothing: '.$urlprocess;
//exit;
//echo 'error';

//infinite loop
while (true) {

    //OUT

    $hourNow = date("G");
    $timeNow = date("Ymd");
    $dayInText = date("D");

    if($hourNow == 9 && $dayInText ='Tue'){
        // 5.00 am process
        //echo 'dentro';
        $yerterday = strtotime('-1 day');
        $fileResult = "Aviso_notificado" . $timeNow . ".txt";

        if(file_exists($fileResult)){
            //echo 'file_exists';
        }else{
            //echo file_get_contents($urlprocess);
            $response = file_get_contents($urlprocess);

            file_put_contents($fileResult,$response);
        }

    }else{
        echo $dayInText;
    }

    //$response = file_get_contents($urlout . $id . '?apikey=' . $secret);
    sleep($sleep);
}



