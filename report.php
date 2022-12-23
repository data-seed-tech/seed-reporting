<?php
require_once("./connection.inc");
require_once("./counter.inc");
require_once("./xml_functions.inc");
require_once("./appCode.inc");


$table = "";


/////////////////////////////////////
//                PAGE              //
/////////////////////////////////////
$page =	@$_POST['p'];
if($page == "")
{
    $page = @$_GET['p'];
}
if($page == "")
{
    $page = 1;
}
//print($page);


$reportId = @$_GET['id'];
//print $reportId;
if($reportId == "")
{
    $reportId = @$_POST['id'];
}
/*
if($reportId == "")
{
    $reportId = 1;
}
 * 
 */

$for = @$_GET['for'];   // may be: 'count', 'report' sau 'sqlReport'
//print $for;
if($for == "")
{
    $for = @$_POST['for'];
}


if($for == "")
{
    $for = "report";
}
else
{
    if($for != "count" && $for!= "report" && $for!= "sql_insert")
    {
        print ("ERROR! Parameter 'for' may be only one of: 'count', 'report' or 'sql_insert'!!!");
    }
}

$query_report = "SELECT reportName, reportDescription, sqlReport, activationCriteria, linkAddress, linkId
                FROM seed_app_reports WHERE reportId = '" . $reportId . "';";
//print $query_report;

$result_report = $conn -> query($query_report);
$row_report = $result_report ->fetch_object();
$err =  $conn->error;
print $err;

$reportName = $row_report->reportName;
$reportDescription = $row_report->reportDescription;
if($for == "count")
{
    $query = $row_report->activationCriteria;
}
else
{
    $query = $row_report->sqlReport;
}
$linkAddress       = $row_report->linkAddress;
$linkId            = $row_report->linkId;


//////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////
//                       MAIN SELECT                        //
//////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////
$no_of_records_per_page = 15;
$offset = ($page - 1) * $no_of_records_per_page;

//print($query);
$result = $conn -> query($query);
$total_rows = mysqli_num_rows($result);
$total_pages = ceil($total_rows / $no_of_records_per_page);

//print($total_pages);
$query = str_replace(";", "", $query);
$query = $query . " LIMIT ".$offset.", ".$no_of_records_per_page.";";
//print($query);
?>

<html>
<head>
    <title><?php print($reportName); ?></title>
    <link rel="stylesheet" type="text/css" href="./styles.css">
</head>
<body>
<div class="header">
    <h1><a href="./" class="header">&#127968; <?php print($Site); ?></a>
        &nbsp;&nbsp;>&nbsp; <a href="./indexApp.php?app=<?php print($appCode); ?>" class="header"><?php print($appIcon); ?> <?php print($appName); ?></a>
        &nbsp;&nbsp;>&nbsp; <?php print($reportName); ?></h1>
    
</div>
<div class="container">
   <?php require_once("./menu.inc"); ?>
   <?php require_once("./menu_left.inc"); ?>
   
   <div class="right">
    <div style="border-style: groove; border-width: 1px; border-radius: 3px; background-color: #ffffdc; padding: 5px">
        <b><a href="./">&#127968; <?php print($Site); ?></a></b> &gt;
        <?php 
        print(substr($reportDescription, 0, 100) . "...&nbsp; " ); 
        
        if($for == "count")
        {
        ?>
        <a href="report.php?app=<?php print($appCode) ?>&id=<?php print($reportId) ?>">[ Detailed Report (SELECT...) ]</a>
        <?php
        }
        else
        {
        ?>&nbsp;&nbsp;&nbsp;
        <a href="report.php?app=<?php print($appCode) ?>&id=<?php print($reportId) ?>&for=count">[ &nbsp;COUNT... ]</a>
        <?php
        }
        ?>
    </div><br />
   
<?php
// comes from xml_functions.inc:
$xml = GetXMLfromQuery($conn, $query);
//print_r ($xml);

$proc = new XSLTProcessor();




////////////////////////////////////////////////////////////////////////////////
// DISPLAY THE REPORT
////////////////////////////////////////////////////////////////////////////////
//$xslPreprocesare = new DOMDocument();
//$xslPreprocesare->load('report_preprocesare.xsl');
//$proc->importStyleSheet($xslPreprocesare);
//$xmlPre = $proc->transformToXML($xml);
//print ($xmlPre);
//$xmlPre = simplexml_load_string($xmlPre);

//$xslTable = GetXSLTabel($root_element_name, $element_name, $linkAddress,    linkId, $reportId);
//$xslTable = GetXSLTabel($root_element_name, $element_name, $linkAddress,  linkId, $reportId."&amp;app=".$appCode);
$xslTable = GetXSLTable('records', 'record', $linkAddress, $linkId, $reportId."&amp;app=".$appCode);
$proc->importStyleSheet($xslTable);
//$xmlTable = $proc->transformToXML($xmlPre);
$xmlTable = $proc->transformToXML($xml);

print ($xmlTable);

?>
    
    
<br/><br/>
<?php
//tabelul cu sumele:

$xslTableSUM = GetXSLTableSUM($conn, $query, 'records', 'record', $linkAddress, $linkId, $reportId."&amp;app=".$appCode);
$proc->importStyleSheet($xslTableSUM);
//$xmlTable = $proc->transformToXML($xmlPre);
$xmlTable = $proc->transformToXML($xml);

//print ($xmlTable);  // este afisat mai jos intr-un DIV ascuns
?>
    
<div id='divTotaluri' class='reportDescription' style='display:none;'><?php print($xmlTable); ?></div><br />
<br/><br/>
<?php

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// AFISARE XML INTR-UN TEXTAREA
if($page > 1)
{
?>
<a href='report.php?app=<?php print($appCode) ?>&id=<?php print($reportId); ?>&p=1'>[ &lt;&lt; ]</a>
&nbsp;
<a href='report.php?app=<?php print($appCode) ?>&id=<?php print($reportId); ?>&p=<?php print($page - 1); ?>'>[ &lt; ]</a>
<?php
}
else
{
?>
<font color="gray">[ &lt;&lt; ]</font>
&nbsp;
<font color="gray">[ &lt; ]</font>
<?php
}
?>

&nbsp;

<font color="gray">&nbsp; [ Page <?php print($page); ?> ] &nbsp;</font>

&nbsp;

<?php
if($page < $total_pages)
{
?>
<a href='report.php?app=<?php print($appCode) ?>&id=<?php print($reportId); ?>&p=<?php print($page + 1); ?>'>[ &gt; ]</a>
&nbsp;
<a href='report.php?app=<?php print($appCode) ?>&id=<?php print($reportId); ?>&p=<?php print($total_pages); ?>'>[ &gt;&gt; ]</a>
<?php
}
else
{
?>
<font color="gray">[ &gt; ]</font>
&nbsp;
<font color="gray">[ &gt;&gt; ]</font>
<?php
}
?>    
    
    
    
    
    
    
    
    
    
<br /><br />
<div style="border-style: groove; border-width: 1px; border-radius: 3px; background-color: #ffffdc; padding: 5px">
    <a target="_blank" href="report_csv.php?app=<?php print($appCode) ?>&id=<?php print($reportId); ?>">[ Download CSV ]</a>
    
    
    &nbsp;
    <a onclick="javascript:document.getElementById('txaSQL').style.display = 'inline';">[ Report SQL ]</a>
    
    
    
    &nbsp;
    <a href="#" onclick="javascript:document.getElementById('divTotaluri').style.display = 'inline-block';" title='Totals applies just to current page!'>[ &#8721; Totals ]</a> 
    
    &nbsp;
    <a target="_blank" href="graph.php?app=<?php print($appCode) ?>&id=<?php print($reportId); ?>">[ &#128480; Graph ]</a> 
    
    &nbsp;
    <a href="#" onclick="javascript:document.getElementById('divDescriere').style.display = 'inline-block';">[ &#128161; Report Description ]</a> 
</div>


<textarea rows="10" cols='265' id='txaSQL' style='display:none;'><?php print($query); ?></textarea><br />
<div id='divDescriere' class='reportDescription' style='display:none;'><?php print(nl2br($reportDescription)); ?></div><br />

    

</div>
</div>
</body>
</html>
