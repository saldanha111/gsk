<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'params.php';

try {
    $conn = oci_connect($db_username, $db_password, $tns, 'AL32UTF8');
    if (!$conn) {
        $e = oci_error();
        throw new Exception($e['message']);
    }
	
	//VENDOR
    $sql= 'select * FROM APLICRY.RY_V_VENDOR WHERE CIF_CODE =\'' . $_GET['CIF'] . '\'';
    $stid = oci_parse($conn, $sql);
    if (!$stid) {
        $e = oci_error($conn);
        throw new Exception($e['message']);
    }
    // Perform the logic of the query
    $r = oci_execute($stid);
    if (!$r) {
        $e = oci_error($stid);
        throw new Exception($e['message']);
    }
    // Fetch the results of the query
    if ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $row = array_change_key_case($row, CASE_LOWER);
		$pro_razon_social = array($row['social_reason']);
		$pro_direccion = array($row['address']);
		$pro_tef = array($row['telefono']);
		$pro_poblacion = array($row['city']);
		$pro_provincial = array($row['provin_desc']);
		$pro_fax = array($row['fax']);
		$pro_cp = array($row['postal_code']);
		$pro_email = array($row['e_mail']);
		$pro_pais = array($row['vat_cou_name']);
		$pro_cif = array($_GET['CIF']);
		$vendor = $row['ven_id'];
    }
    
    // Close statement
    oci_free_statement($stid);
	
	//DA
	$sql= 'select * FROM APLICRY.RY_V_DA WHERE VEN_ID =\'' . $vendor . '\'';
    $stid = oci_parse($conn, $sql);
    if (!$stid) {
        $e = oci_error($conn);
        throw new Exception($e['message']);
    }
    // Perform the logic of the query
    $r = oci_execute($stid);
    if (!$r) {
        $e = oci_error($stid);
        throw new Exception($e['message']);
    }
    // Fetch the results of the query
	$counter = 0;
	$idsec = array();
	$seccion = array();
	$codigo_proveedor = array();
	$opcion = array();

    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $row = array_change_key_case($row, CASE_LOWER);
		$idsec[] = $row['vad_id'];
		$seccion[] = $row['mse_sms'];
		$codigo_proveedor[] = $row['cod_comercial'];
		$tmp = '';
		if($row['marca_nacional'] == 1){
			$tmp .= 'MN';
		}
		if($row['marca_propia'] == 1){
			if ($tmp == ''){
				$tmp .= 'MP';
			} else {
				$tmp .= ',MP';
			}
		}
		if($row['primer_precio'] == 1){
			if ($tmp == ''){
				$tmp .= 'PP';
			} else {
				$tmp .= ',PP';
			}
		}
		$opcion[] = $tmp;
		
		$counter++;
    }
    // Close statement
    oci_free_statement($stid);

	
	//build the final JSON
	$data = new stdClass();
	//first generate the groupId that we want to clone
	$var2clone = array('idsec', 'seccion', 'codigo_proveedor', 'opcion');
	$groupId = md5(implode(',', $var2clone));
	//create the array to be inserted in the JSON with all the elements to be cloned
	$cloneMe = array();
	$da = new stdClass();
	$da->id = $groupId;
	$da->ntimes = $counter -1;
	$da->type = 'table';
	$da->match = 0;
	$cloneMe[] = $da;
	//insert the conable groups
	$data->cloneGroupsByID = $cloneMe;
	$varValues = new stdClass();
	//CODIGOS PROVEEDOR
	$varValues->idsec = $idsec;
	$varValues->seccion = $seccion;
	$varValues->codigo_proveedor = $codigo_proveedor;
	$varValues->opcion = $opcion;
	//VENDOR INFO
	$varValues->pro_razon_social = $pro_razon_social;
	$varValues->pro_direccion = $pro_direccion;
	$varValues->pro_tef = $pro_tef;
	$varValues->pro_poblacion = $pro_poblacion;
	$varValues->pro_provincial = $pro_provincial;
	$varValues->pro_fax = $pro_fax;
	$varValues->pro_cp = $pro_cp;
	$varValues->pro_email = $pro_email;
	$varValues->pro_pais = $pro_pais;
	$varValues->pro_cif = $pro_cif;
	$data->varValues = $varValues;
    
	echo json_encode($data);

    // Disconnect
    oci_close($conn);
	
}
catch (Exception $e) {
    print_r($e);
}
?>
