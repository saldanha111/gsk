<?php

//$FPDI = '../../v3_html52pdf/PDFMerger.inc';
$FPDI = '/var/www/docxpresso/docx/core/PDFMerger.inc';
require_once($FPDI);
$pdfmerger = new \PDFMerger; 
$files = array('licencia.pdf', 'Rumania.pdf');
$pdfmerger->merge('base.pdf', $files);
?>
<h3>Resultado</h3>
<a href="base.pdf" download>PDF fusionado</a>
<h4>Ficheros originales</h4>
<p><a href="licencia.pdf" download>Licencia.pdf</a></p>
<p><a href="Rumania.pdf" download>Rumania.pdf</a></p>