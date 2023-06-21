<?php
require_once("./connection.inc");
require_once("./counter.inc");
require_once("./xml_functions.inc");
require_once("./appCode.inc");


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


/////////////////////////////////////
//              table              //
/////////////////////////////////////
$table  =	@$_POST['table'];
if($table == "")
    $table  = @$_GET['table'];
//print(table);

//$msg = "Values of ".$table.":";
$msg = "";

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

/////////////////////////////////////
//              id_value           //
/////////////////////////////////////
$id_value = @$_POST[$id_name];
if($id_value == "")
{
    $id_value = @$_GET[$id_name];
}
if($id_value == "")
{
    $id_value = @$_POST['id'];
}
//print $id_value;
if($id_value == "")
{
    $id_value = @$_GET['id'];
}
if($id_value == "")
{
    $id_value = @$_POST['Id'];
}
if($id_value == "")
{
    $id_value = @$_GET['Id'];
}
if($id_value == "")
{
    $id_value = @$_POST['txtID'];
}
if($id_value == "")
{
    $id_value = @$_GET['txtID'];
}

//print($id_value);




?>
<html>
<head>
<title>View: <?php print($table); ?></title>
<link rel="stylesheet" type="text/css" href="./styles.css">
<script language=javascript>
function AddNew()
{
    document.frmForm.hdnFor.value = "ADDNEW";
    document.frmForm.submit();
}

function toggle_right_menu() {
  var x = document.getElementById("right_menu");
  var y = document.getElementById("content_right");
  
  if (x.style.display === "none") {
    x.style.display = "block";
    y.style.width = "13%";
  } else {
    x.style.display = "none";
    y.style.width = "3%";
  }
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
    
<div class="content_left">
<TABLE width="100%" cellpadding="3" cellspacing="0" style="text-align: center">
	<tr>
            <TD bgcolor="#e6f2ff" style="text-align: left">
                <?php print("&nbsp;".$msg) ?>
            </TD>
	</TR>
</TABLE>
<br/>


   
<?php
/////////////////////////////////////////////////////////////////////////////
//                          Parent table                                   //
/////////////////////////////////////////////////////////////////////////////
$table_parent  =	@$_POST['parent'];
if($table_parent == "")
    $table_parent  = @$_GET['parent'];

$parentIdValue      =	@$_POST['parentId'];
if($parentIdValue == "")
    $parentIdValue  = @$_GET['parentId'];

//if($parentIdValue === "" || strlen($parentIdValue) === 0)
//    print(strlen($parentIdValue));
$parentIdColumn = "";
$parentIdColumnFK = "";
?>


<table class="frame_table" cellspacing="0" cellpadding="0">
    
<?php
if($table_parent === "" || strlen($table_parent) === 0)
{
    $query = "SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE as KU
                INNER JOIN " . $current_schema . ".seed_menus as MEN ON KU.REFERENCED_TABLE_NAME = MEN.tableName
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
        if(($parentIdValue === "" || strlen($parentIdValue) === 0) && $id_value != "")
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
        //print($query4);
        //print ("&#128194; <b><a class='entity_name' href='entityView.php?table=" . $table_parent . "'>" . $table_parent . "</a></b>");

        // VINE DIN xml_functions.inc
        $xml = GetXMLfromQuery($conn, $query4, $table_parent, $table_parent);
        //print_r ($xml);
        //print ($xml);

        $proc = new XSLTProcessor();

        $xslTable = GetXSLTable($table_parent, $table_parent, 'entityEdit.php', $parentIdColumnFK, "&amp;app=".$appCode."&amp;table=".$table_parent, "_self");
        //$xslTable = GetXSLTable($table_parent, $table_parent);
        //print ($xslTable);

        $proc->importStyleSheet($xslTable);
        //$xmlTable = $proc->transformToXML($xmlPre);
        $xmlTable = $proc->transformToXML($xml);
        //print ("<li>" . $table_parent . ":<br /><br />");
    ?>
    <tr>
    <td colspan="4">&#128194;&nbsp;
        <a class='entity_name' href='entityView.php?app=<?php print($appCode); ?>&table=<?php print($table_parent); ?>'><?php print($table_parent); ?></a>
    </td>
    </tr>

    <tr>
    <!--td style="background-image:url(./images/linie2.JPG);background-repeat:repeat-y;width:25px;">a2</td-->
    <td style="background-image:url(./images/linie2.JPG);background-repeat:repeat-y;width:25px;border:none;"></td>
    <td colspan="3">

    <?php
        print ($xmlTable);
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
    if(($parentIdValue === "" || strlen($parentIdValue) === 0) && $id_value != "")
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

    $xslTable = GetXSLTable($table_parent, $table_parent, 'entityEdit.php', $parentIdColumnFK, "&amp;app=".$appCode."&amp;table=".$table_parent, "_self");
    //$xslTable = GetXSLTable($table_parent, $table_parent);
    //print ($xslTable);
    
    $proc->importStyleSheet($xslTable);
    //$xmlTable = $proc->transformToXML($xmlPre);
    $xmlTable = $proc->transformToXML($xml);
    //print ("<li>" . $table_parent . ":<br /><br />");
    ?>
    </tr>
    <tr>
    <td colspan="4">&#128194;&nbsp;
       <a class='entity_name' href='entityView.php?app=<?php print($appCode); ?>&table=<?php print($table_parent); ?>'><?php print($table_parent); ?></a>
    </td>
    </tr>

    <tr>
    <td style="background-image:url(./images/linie2.JPG);background-repeat:repeat-y;width:25px;"></td>
    <td colspan="3">

    <?php
    print ($xmlTable);
    print ("<br /><br />");
}
?>




</td>
</tr>


<tr>
<td style="background-image:url(./images/sageata_capat2.JPG);background-repeat:no-repeat;"></td>
<td class="frame_table_td" style="font-size:16px;width:10px;">&#128194;&nbsp;</td>
<td colspan="2" class="frame_table_td">
    <b><?php print($table); ?></b> &#128204; 
</td>
</tr>





<tr>
<td></td>
<td colspan="2" class="frame_table_td">
<?php
//////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////
//                       MAIN SELECT                        //
//////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////
$no_of_records_per_page = 15;
$offset = ($page - 1) * $no_of_records_per_page;

if($parentIdColumn != "" && $parentIdValue != "")
{
    $query = "SELECT * FROM " . $table . " WHERE " . $parentIdColumn . " = '" . $parentIdValue . "' LIMIT ".$offset.", ".$no_of_records_per_page.";";
    $total_pages_sql = "SELECT COUNT(*) FROM " . $table . " WHERE " . $parentIdColumn . " = '" . $parentIdValue . "';";
}
else
{
    $query = "SELECT * FROM " . $table . " LIMIT ".$offset.", ".$no_of_records_per_page.";";
    $total_pages_sql = "SELECT COUNT(*) FROM " . $table . ";";
}
//print $query;
$result = mysqli_query($conn, $total_pages_sql);
$total_rows = mysqli_fetch_array($result)[0];
$total_pages = ceil($total_rows / $no_of_records_per_page);


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

$xslTable = GetXSLTable($id_name, $table, 'entityEdit.php', $link_id, "&amp;app=".$appCode."&amp;table=".$table, "_self");
$proc->importStyleSheet($xslTable);
//$xmlTable = $proc->transformToXML($xmlPre);
$xmlTable = $proc->transformToXML($xml);
//print("<hr/>");


print ($xmlTable);

if($id_name != "")
{
    print("[+] <a href='entityEdit.php?app=".$appCode."&parent=".$table_parent."&amp;parentId=".$parentIdValue."&amp;table=".$table."'>Add new</a>");
    print("<br/><br/><br/>");
}
?>
    
    
    

<?php
if($page > 1)
{
?>
<a href='entityView.php?app=<?php print($appCode); ?>&parent=<?php print($table_parent); ?>&amp;parentId=<?php print($parentIdValue); ?>&amp;table=<?php print($table); ?>&p=1'>[ &lt;&lt; ]</a>
&nbsp;
<a href='entityView.php?app=<?php print($appCode); ?>&parent=<?php print($table_parent); ?>&amp;parentId=<?php print($parentIdValue); ?>&amp;table=<?php print($table); ?>&p=<?php print($page - 1); ?>'>[ &lt; ]</a>
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
<a href='entityView.php?app=<?php print($appCode); ?>&parent=<?php print($table_parent); ?>&amp;parentId=<?php print($parentIdValue); ?>&amp;table=<?php print($table); ?>&p=<?php print($page + 1); ?>'>[ &gt; ]</a>
&nbsp;
<a href='entityView.php?app=<?php print($appCode); ?>&parent=<?php print($table_parent); ?>&amp;parentId=<?php print($parentIdValue); ?>&amp;table=<?php print($table); ?>&p=<?php print($total_pages); ?>'>[ &gt;&gt; ]</a>
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

</td>
</tr>
</table>

</div>
<?php require_once("menu_right.inc"); ?>
    
    
    
<div class="right">
    &#128200; Entity reports:
    <hr/>
    <ul>
    <?php
    $query = "SELECT * FROM seed_entity_view_reports WHERE `table` = '".$table."'";
    //print($query);
    $result = $conn -> query($query);
    while($row = $result -> fetch_object())
    {
        //$query_entity_view_report = $row->sqlReport;
        $query_entity_view_report = str_replace('???', $id_value, $row->sqlReport);
        //print($query_entity_view_report);
        
        print("<li>". $row->description);
        print(" <font color=lightgray>[". $query_entity_view_report . "]</font>");
        //$xml = GetXMLfromQuery($conn, $query_entity_view_report, 'items', 'item');
        $xml = GetXMLfromQuery($conn, $query_entity_view_report, 'items', 'item');
        $proc = new XSLTProcessor();
        
        //$xslTabel = GetXSLTable('items', 'item');
        $xslTabel = GetXSLTable('items', 'item', 'entityEdit.php', $parentIdColumnFK, "&amp;app=".$appCode."&amp;table=".$table_parent, "_self");
        $proc->importStyleSheet($xslTabel);
        //$xmlTabel = $proc->transformToXML($xmlPre);
        $xmlTabel = $proc->transformToXML($xml);

        print ($xmlTabel);
        print("<br/>");
    }
    ?>
    </ul>
    <br/>
    <div style="text-align: right;font-size:8px">
        [+] <a target='_blank' href='./entityEdit.php?app=_system&table=seed_entity_view_reports'>Add</a>&nbsp;
    </div>
    <br/><br/>
</div>
</div>

</body>
</html>