<h3>Listado de Proveedores</h3>
<p><strong>Filtro:</strong> <?php echo $_GET['parametro']; ?></p>
<p><strong>Término de búsqueda:</strong> <?php echo $_GET['search_term']; ?></p>
<select id="proveedor">
<option val="">Escoja un proveedor</option>
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
    $sql= 'select * FROM APLICRY.RY_V_VENDOR WHERE lower(' . $_GET['parametro'] . ') LIKE \'%' . $_GET['search_term'] . '%\'';
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
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $row = array_change_key_case($row, CASE_LOWER);
		echo '<option value="' . $row['cif_code'] . '">' . $row['social_reason'] . '</option>';
    }
    
    // Close statement
    oci_free_statement($stid);
    // Disconnect
    oci_close($conn);
	
}
catch (Exception $e) {
    print_r($e);
}
?>
</select></p>
<p><button onclick="sendRequest()">Seleccionar proveedor</button></p>
<?php echo $sql; ?>
<script>
function sendRequest(){
	var e = document.getElementById("proveedor");
	var cif = e.options[e.selectedIndex].value;
	if (typeof cif != 'undefined' && cif != ''){
		window.location.href= 'edit.php?CIF=' + cif;
	} else {
		alert('Tiene que escoger un proveedor.');
	}
}
</script>
