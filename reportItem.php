<?php
require_once("./connection.inc");
require_once("./counter.inc");
require_once("./xml_functions.inc");
require_once("./appCode.inc");
//require_once("./environment.inc");


$table = "";

$for =	@$_POST['hdnFor'];
$msg = '';

if(count($_GET) > 0)
{
    //print_r($_GET);
    $id_name = array_keys($_GET)[0];
    $id_value = $_GET[$id_name];
    //var_dump($_GET);
}

if($id_name == "")
{
    if(count($_POST) > 0)
    {
        //print_r($_POST);
        $id_name = array_keys($_POST)[0];
        $id_value = $_POST[$id_name];
        //var_dump($_POST);
    }
}

//print($id_value);


$reportId  =	@$_POST['parent'];
if($reportId == "")
    $reportId  = @$_GET['parent'];
//print($reportId);
        
$query_report = "SELECT *
                FROM seed_app_reports WHERE reportId = '" . $reportId . "';";
//print $query_report;

$result_report = $conn -> query($query_report);
$row_report = $result_report ->fetch_object();
//$err =   $conn->error;
//print $err;

//$denumire = $row_report->denumire;
//$descriere = $row_report->descriere;
//if($for == "criteriu")
//{
//    $query = $row_report->criteriu_activare;
//}
//else
//{
//    $query = $row_report->sql_report;
//}
//$root_element_name  = $row_report->xml_root_element_name;
//$element_name       = $row_report->xml_element_name;
$sql_report         = $row_report->sqlReport;
$link_id            = $row_report->linkId;        
$link_details       = $row_report->linkDetails;

//print ($sql_report);

$pos1 = strpos($sql_report, "FROM");
if(strlen($pos1) === 0)
{
    $pos1 = strpos($sql_report, "from");
}
//print ($pos1);
//$str1 = substr($sql_report, $pos1 + 5, strlen($sql_report));
//print ($str1);
//$pos2 = strpos($str1, " ");
//print ($pos2);
//$table = substr($str1, 0, $pos2);
//print ($table);
$str1 = substr($sql_report, $pos1 + 5, strlen($sql_report));
//print ($str1);

$pos2 = min(strpos($str1, " "), strpos($str1, "\n"), strpos($str1, "\r"));

//print ($pos2);
if($pos2 == 0)
    $table = $str1;
else
    $table = trim(substr($str1, 0, $pos2 + 1), " \n\r\t\v\0\," );

$table = str_replace("`", "", $table);
//print ($table);

/////////////////////////////////////
//          current_schema         //
/////////////////////////////////////
$query = "SELECT DATABASE() as current_schema;";
$result = $conn -> query($query);
$row = $result -> fetch_object();
$current_schema  = $row -> current_schema;



/////////////////////////////////////
//              id_name           //
/////////////////////////////////////
$query = "SELECT COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, COLUMN_KEY, EXTRA, COLUMN_COMMENT
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_schema = '" . $current_schema . "'
            AND table_name = '" . $table . "'
            AND COLUMN_KEY = 'PRI';";
//print($query);
$result = $conn -> query($query);
$row = $result -> fetch_object();
$id_name = $row->COLUMN_NAME;
//print($id_name);
?>



<html>
<head>
<title>Report Item: <?php print($table); ?></title>
<link rel="stylesheet" type="text/css" href="./styles.css">
<script language=javascript>
function AddNew()
{
    document.frmForm.hdnFor.value = "ADDNEW";
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
/////////////////////////////////////////////////////////////////////////////
//                          tabel parent                                  //
/////////////////////////////////////////////////////////////////////////////
$table_parent = "";
//$table_parent  =	@$_POST['parent'];
//if($table_parent == "")
//    $table_parent  = @$_GET['parent'];

$parentIdValue      =	@$_POST['parentId'];
if($parentIdValue == "")
    $parentIdValue  = @$_GET['parentId'];

//if($parentIdValue === "" || strlen($parentIdValue) === 0)
//    print(strlen($parentIdValue));
$parentIdColumn = "";
$parentIdColumnFK = "";
?>


<!-- table style="border: none" cellspacing="0" cellpadding="0" -->
<table class="form_table" cellspacing="0" cellpadding="0">
<?php

if($table_parent === "" || strlen($table_parent) === 0)
{
    $query = "SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE
                  REFERENCED_TABLE_SCHEMA = '" . $current_schema . "' AND
                  TABLE_NAME = '" . $table . "';";
    //print($query);
    
    $result = $conn -> query($query);
    //$row = $result -> fetch_object();
    while($row = $result -> fetch_object())
    {
        $parentIdColumnFK = $row->REFERENCED_COLUMN_NAME;
        $parentIdColumn = $row->COLUMN_NAME;
        $table_parent = $row->REFERENCED_TABLE_NAME;
        if($parentIdValue === "" || strlen($parentIdValue) === 0 && $id_value != "")
        {    
            $query3 = "SELECT ".$parentIdColumn." FROM " . $table . " WHERE " . $id_name . " = '" . $id_value . "';";
            //print($query3);
            $result3 = $conn -> query($query3);
            while($row3 = $result3 -> fetch_array(MYSQLI_ASSOC))
            {
                $coloane_3[] = $row3;
            }
            $parentIdValue = $coloane_3[0][$parentIdColumn];
        }
        //print($parentIdColumn);
        
        $query4 = "SELECT * FROM " . $table_parent . " WHERE " . $parentIdColumnFK . " = '" . $parentIdValue . "';";
        //print ("&#128194; <b><a class='entity_name' href='entityView.php?tabel=" . $table_parent . "'>" . $table_parent . "</a></b>");

        // VINE DIN xml_functions.inc
        $xml = GetXMLfromQuery($conn, $query4, $table_parent, $table_parent);
        //print_r ($xml);
        //print ($xml);

        $proc = new XSLTProcessor();

        $xslTabel = GetXSLTable($table_parent, $table_parent, 'entityEdit.php', $parentIdColumnFK, "&amp;app=".$appCode."&amp;table=".$table_parent, "_self");
        //$xslTabel = GetXSLTabel($table_parent, $table_parent);
        //print ($xslTabel);

        $proc->importStyleSheet($xslTabel);
        //$xmlTabel = $proc->transformToXML($xmlPre);
        $xmlTabel = $proc->transformToXML($xml);
        //print ("<li>" . $table_parent . ":<br /><br />");
    ?>
    <tr>
    <td colspan="4" style="font-size:12px;border:none;">&#128194;&nbsp;
        <b><a class='entity_name' href='entityView.php?app=<?php print($appCode); ?>&tabel=<?php print($table_parent); ?>'><?php print($table_parent); ?></a></b>
    </td>
    </tr>

    <tr>
    <!-- td style="background-image:url(./imagini/linie2.JPG);background-repeat:repeat-y;width:25px;">a2</td -->
    <td style="background-image:url(./images/linie2.JPG);background-repeat:repeat-y;width:25px;border:none;"></td>
    <td colspan="3" style="border:none;">

    <?php
        print ($xmlTabel);
        print ("<br /><br />");
    }
}
else
{
    
    $query = "SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE
                  REFERENCED_TABLE_SCHEMA = '" . $current_schema . "' AND
                  REFERENCED_TABLE_NAME = '" . $table_parent . "' AND
                  TABLE_NAME = '" . $table . "';";
    //print($query);

    $result = $conn -> query($query);
    //$row = $result -> fetch_object();
    $row = $result -> fetch_object();

    $parentIdColumnFK = $row->REFERENCED_COLUMN_NAME;
    $parentIdColumn = $row->COLUMN_NAME;
    if($parentIdValue === "" || strlen($parentIdValue) === 0)
    {    
        $query = "SELECT ".$parentIdColumn." FROM " . $table . " WHERE " . $id_name . " = '" . $id_value . "';";
        //print($query);
        $result = $conn -> query($query);
        while($row = $result -> fetch_array(MYSQLI_ASSOC))
        {
            $coloane_1[] = $row;
        }
        $parentIdValue = $coloane_1[0][$parentIdColumn];
    }
    
    $query = "SELECT * FROM " . $table_parent . " WHERE " . $parentIdColumnFK . " = '" . $parentIdValue . "';";
    
    // VINE DIN xml_functions.inc
    $xml = GetXMLfromQuery($conn, $query, $table_parent, $table_parent);
    //print_r ($xml);
    //print ($xml);

    $proc = new XSLTProcessor();

    $xslTabel = GetXSLTabel($table_parent, $table_parent, 'entityEdit.php', $parentIdColumnFK, "&amp;app=".$appCode."&&amp;tabel=".$table_parent, "_self");
    //$xslTabel = GetXSLTabel($table_parent, $table_parent);
    //print ($xslTabel);
    
    $proc->importStyleSheet($xslTabel);
    //$xmlTabel = $proc->transformToXML($xmlPre);
    $xmlTabel = $proc->transformToXML($xml);
    //print ("<li>" . $table_parent . ":<br /><br />");
    ?>
    <tr>
    <td colspan="4" style="font-size:16px;border:none;">&#128194;&nbsp;
        <b><a class='entity_name' href='entityView.php?app=<?php print($appCode); ?>&tabel=<?php print($table_parent); ?>'><?php print($table_parent); ?></a></b>
    </td>
    </tr>

    <tr>
    <td style="background-image:url(./imagini/linie2.JPG);background-repeat:repeat-y;width:25px;">a2</td>
    <td colspan="3">

    <?php
    print ($xmlTabel);
    print ("<br /><br />");
}
?>




</td>
</tr>


<tr>
<td style="background-image:url(./images/sageata_capat2.JPG);background-repeat:no-repeat;border:none;"></td>

<td style="font-size: 16px;width:10px;border:none;">&#128194;&nbsp;</td>
<td colspan="2" style="font-size: 16px;background-color:#e6eefc;border:none;">
    <b><a class='entity_name' href='entityView.php?app=<?php print($appCode); ?>&tabel=<?php print($table); ?>'><?php print($table); ?></a></b> &#128204; 
</td>
</tr>





<tr>
<!-- td style="">b2</td -->
<td style="border:none;">&nbsp;</td>
<td style="border:none;">&nbsp;</td>
<td colspan="2" style="background-color:#e6eefc;">
<?php
//if($parentIdColumn != "")
    $query = "SELECT * FROM " . $table . " WHERE " . $id_name . " = '" . $id_value . "';";
//else
//    $query = "SELECT * FROM " . $table . ";";
//    print $query;

    // VINE DIN xml_functions.inc
    $xml = GetXMLfromQuery($conn, $query, $id_name, $table);
    //print_r ($xml);

    
    $query2 = "SELECT COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, COLUMN_KEY, EXTRA, COLUMN_COMMENT
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_schema = '" . $current_schema . "'
          AND table_name = '" . $table . "';";
    //print $query2;
    $result2 = $conn -> query($query2);
    while($row2 = $result2 -> fetch_object())
    //while($row2 = $result2 -> fetch_array(MYSQLI_ASSOC))
    {
        //$coloane2[] = $row2;
        if($row2->COLUMN_KEY === "PRI")
            $link_id = $row2->COLUMN_NAME;
    }
    //print_r(array_keys($coloane2));
    //print($coloane2[0]['COLUMN_NAME']);
    
    $proc = new XSLTProcessor();

    $xslTabel = GetXSLTable($id_name, $table, 'entityEdit.php', $link_id, "&amp;app=".$appCode."&amp;table=".$table, "_self");
    $proc->importStyleSheet($xslTabel);
    //$xmlTabel = $proc->transformToXML($xmlPre);
    $xmlTabel = $proc->transformToXML($xml);
    //print("<hr/>");
    
    
    print ($xmlTabel);
    
    if($id_name != "")
    {
        print("[+] <a href='entityEdit.php?app=".$appCode."&parent=".$table_parent."&amp;parentId=".$parentIdValue."&amp;tabel=".$table."'>Add new</a>");
        print("<br/><br/><br/>");
    }
    ?>
</td>
</tr>
</table>
   </div>


</body>
</html>