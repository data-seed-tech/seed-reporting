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


/////////////////////////////////////
//          current_schema         //
/////////////////////////////////////
$query = "SELECT DATABASE() as current_schema;";
$result = $conn -> query($query);
$row = $result -> fetch_object();
$current_schema  = $row -> current_schema;


function extractTableName($sql_select)
{
    $tableName = "";
    $pos1 = strpos($sql_select, "FROM");
    if(strlen($pos1) === 0)
    {
        $pos1 = strpos($sql_select, "from");
    }
    //print ($pos1);
    //$str1 = substr($sql_report, $pos1 + 5, strlen($sql_report));
    //print ($str1);
    //$pos2 = strpos($str1, " ");
    //print ($pos2);
    //$table = substr($str1, 0, $pos2);
    //print ($table);
    $str1 = substr($sql_select, $pos1 + 5, strlen($sql_select));
    //print ($str1);

    $pos2 = min(strpos($str1, " "), strpos($str1, "\n"), strpos($str1, "\r"));

    //print ($pos2);
    if($pos2 == 0)
        $tableName = $str1;
    else
        $tableName = trim(substr($str1, 0, $pos2 + 1), " \n\r\t\v\0\," );

    $tableName = str_replace("`", "", $tableName);
    return $tableName;
}


$table = extractTableName($sql_report);
//print ($table);


$query = "select TABLE_NAME, VIEW_DEFINITION
    from information_schema.views
    WHERE table_schema = '" . $current_schema . "'
    AND TABLE_NAME = '" . $table . "';";

$result = $conn -> query($query);
//$row = $result -> fetch_object();
if($row = $result -> fetch_object())
{
    //print($row->VIEW_DEFINITION);
    //$table = extractTableName($row->VIEW_DEFINITION);
    
    $table = $link_details;
    //print ($table);   
}


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
    
    //if($id_name != "")
    //{
    //    print("[+] <a href='entityEdit.php?app=".$appCode."&parent=".$table_parent."&amp;parentId=".$parentIdValue."&amp;tabel=".$table."'>Add new</a>");
    //    print("<br/><br/><br/>");
    //}
    ?>
</td>
</tr>
</table>
   </div>


</body>
</html>