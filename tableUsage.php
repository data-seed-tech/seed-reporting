<?php
require_once("./connection.inc");
require_once("./counter.inc");
require_once("./xml_functions.inc");
require_once("./appCode.inc");

/////////////////////////////////////
//              table              //
/////////////////////////////////////
$table  =	@$_POST['table'];
if($table == "")
    $table  = @$_GET['table'];
if($table == "")
    $table  = @$_POST['TABLE_NAME'];
if($table == "")
    $table  = @$_GET['TABLE_NAME'];
//print(table);

/////////////////////////////////////
//          current_schema         //
/////////////////////////////////////
$query = "SELECT DATABASE() as current_schema;";
$result = $conn -> query($query);
$row = $result -> fetch_object();
$current_schema  = $row -> current_schema;



$query = "SHOW CREATE TABLE ".$table."";
//print($query);
$result = $conn -> query($query);
$row = $result -> fetch_object();
$valori = (array)$row;
$create_table = $valori;



$query = "SELECT '".$table."' as targetTable, 'seed_menus' as 'table', menuId as 'id', appCode 
            FROM seed_menus
            WHERE tableName = '".$table."'
            UNION 
            SELECT '".$table."' as targetTable, 'seed_nomenclatures' as 'table', nomenclatureId as 'id', appCode 
            FROM seed_nomenclatures
            WHERE tableName = '".$table."'
            UNION
            SELECT '".$table."' as targetTable, 'seed_app_reports' as 'table', reportId as 'id', appCode 
            FROM seed_app_reports
            WHERE sqlReport LIKE '%".$table."%'";

//print($query);

$root_element_name  = 'root';
$element_name       = 'element';
$msg = "The table " . $table . " is used by the next DATA-SEED objects:";
?>

<html>
<head>
<title>Table</title>
<link rel="stylesheet" type="text/css" href="./styles.css">
<script language=javascript>
function Test()
{
    document.frmForm.q.value = document.frmForm.txaSQL.value;
    //alert(document.frmForm.q.value);
    document.frmForm.submit();
}
</script>
</head>
<body>
<div class="header">
    <h1><a href="./" class="header">&#127968; <?php print($Site); ?></a>
        &nbsp;&nbsp;>&nbsp; <a href="./indexApp.php?app=<?php print($appCode); ?>" class="header"><?php print($appIcon); ?> <?php print($appName); ?></a>
        &nbsp;&nbsp;>&nbsp; <?php print($table); ?></h1>
</div>
<div class="container">
    <?php require_once("./menu.inc"); ?>
    <?php require_once("./menu_left.inc"); ?>
    <h1>Table Usage: <?php print($table); ?></h1>

<div class="right">
<TABLE width="100%" cellpadding="3" cellspacing="0" class='responsive' style="text-align: center">
	<tr>
            <TD bgcolor="#e6f2ff" style="text-align: center">
                <b><?php print("&nbsp;".$msg) ?></b>
            </TD>
	</TR>
</TABLE>
<br/>    
   
<?php
if($query != "")
{
    // defined in xml_functions.inc
    $xml = GetXMLfromQuery($conn, $query, $root_element_name, $element_name);
    //print_r ($xml);

    //$xsl = new DOMDocument();
    //$xsl->load('raport_xml.xsl');



    $proc = new XSLTProcessor();




    ////////////////////////////////////////////////////////////////////////////////
    // AFISARE RAPORT
    ////////////////////////////////////////////////////////////////////////////////
    //$xslPreprocesare = new DOMDocument();
    //$xslPreprocesare->load('raport_xml_preprocesare.xsl');
    //$proc->importStyleSheet($xslPreprocesare);
    //$xmlPre = $proc->transformToXML($xml);
    //print ($xmlPre);
    //$xmlPre = simplexml_load_string($xmlPre);


    $xslTabel = GetXSLTable($root_element_name, $element_name, $link_address = 'entityView.php', $link_id = 'table', $parinte = '');
    $proc->importStyleSheet($xslTabel);
    //$xmlTabel = $proc->transformToXML($xmlPre);
    $xmlTabel = $proc->transformToXML($xml);

    print ($xmlTabel);
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////


// AFISARE XML INTR-UN TEXTAREA
?>

<br /><br />
<div style="border-style: groove; border-width: 1px; border-radius: 3px; background-color: #ffffdc; padding: 5px">
    &nbsp;
    <a onclick="javascript:document.getElementById('txaCREATE').style.display = 'inline';">[ Generate CREATE TABLE ]</a>
    
    
    &nbsp;
    <a target="_blank" href="tableSqlInsert.php?app=<?php print($appCode) ?>&table=<?php print($table); ?>">[ Generate SQL Insert ]</a>    
</div><br />

<textarea rows="10" cols='200' id='txaXML' style='display:none;'><?php print($xml->asXML()); ?></textarea><br />
<textarea rows='10' cols='200' id='txaCREATE' style='display:none;'><?php print($create_table['Create Table']); ?></textarea>    
    
    
    
    

</div>
</body>
</html>
