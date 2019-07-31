<?php
error_reporting(E_ALL);

include 'params.php';

try {
    $conn = oci_connect($db_username, $db_password, $tns, 'AL32UTF8');
    if (!$conn) {
        $e = oci_error();
        throw new Exception($e['message']);
    }
    //echo "Connection OK\n";
    
    $sql= 'select vendor.VEN_ID AS seller, vendor.SOCIAL_REASON AS company FROM APLICRY.RY_V_VENDOR vendor WHERE vendor.CIF_CODE = \'' . $_GET['CIF'] . '\'';
    var_dump($sql);
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
        var_dump($row);
        $identificador = $row['SELLER'];
        $company = $row['COMPANY'];
        $cif = $_GET['CIF'];
    }
    
    // Close statement
    oci_free_statement($stid);
    
}
catch (Exception $e) {
    print_r($e);
}
?>
{
"identificador": "<?php echo $identificador; ?>",
"company": "<?php echo $company; ?>",
"cif": "<?php echo $cif; ?>"
}
<?php
try {
    $sql= 'SELECT da.MSE_SMS AS SMS, da.COD_COMERCIAL AS CODIGO_COMERCIAL, da.MARCA_NACIONAL AS MARCA_NACIONAL, da.MARCA_PROPIA AS MARCA_PROPIA, da.PRIMER_PRECIO AS PRIMER_PRECIO FROM APLICRY.RY_V_DA da WHERE da.VEN_ID = \'' . $identificador . '\'';
    var_dump($sql);
    $stid = oci_parse($conn, $sql);
var_dump($stdi);
    if (!$stid) {
        $e = oci_error($conn);
        throw new Exception($e['message']);
    }
    // Perform the logic of the query
    $r = oci_execute($stid);
var_dump($r);
    if (!$r) {
        $e = oci_error($stid);
        throw new Exception($e['message']);
    }

    // Fetch the results of the query
   while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        var_dump($row);
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

