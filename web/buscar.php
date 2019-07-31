<h3>Buscador de proveedores</h3>
<p>Parámetro de búsqueda:
<select id="parametro">
<option value="social_reason" selected>Nombre</option>
<option value="cif_code">CIF</option>
<option value="ven_id">VENDOR ID</option>
</select></p>

<p>Término de búsqueda: <input id="search_term" type="text" value="" /></p>

<p><button onclick="sendRequest()">Buscar proveedor</button></p>

<script>
function sendRequest(){
	var e = document.getElementById("parametro");
	var parametro = e.options[e.selectedIndex].value;
	var search_term = document.getElementById("search_term").value.toLowerCase();
	if (typeof search_term != 'undefined' && search_term != ''){
		window.location.href= 'search.php?parametro=' + parametro + '&search_term=' + search_term;
	} else {
		alert('Tiene que escribir un termino de búsqueda');
	}
}
</script>
