<?php
require_once("./connection.inc");
require_once("./counter.inc");
require_once("./xml_functions.inc");
require_once("./appCode.inc");


/////////////////////////////////////
//                FOR              //
/////////////////////////////////////
$for =	@$_POST['hdnFor'];
if($for == "")
{
    $for = @$_GET['for'];
}
//print($for);
$msg = '';


/////////////////////////////////////
//              table              //
/////////////////////////////////////
$table  =	@$_POST['table'];
if($table == "")
    $table  = @$_GET['table'];
//print($table);



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
if($row = $result -> fetch_object())
{
    $id_name = $row->COLUMN_NAME;
    //print($id_name);
}

/////////////////////////////////////
//              id_value           //
/////////////////////////////////////
$id_value = @$_POST['id'];
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
if($id_value == "")
{
    $id_value = @$_GET[$id_name];
}
if($id_value == "")
{
    $id_value = @$_POST[$id_name];
}

$id_old_value = $id_value;      //used in JavaScript for AddNew to delete old value from url
if($for === "ADDNEW")
{
    $id_value = "";
}
//print($for);
//print($id_old_value);



////////////////////////////////////////////////////////////////////////////////
//                                    SAVE                                    //
////////////////////////////////////////////////////////////////////////////////
if($for == "SAVE")
{
    //print($id_name);
    //print($id_value);
    
    if($id_value === "")
    {
        //print("INSERT");
        //////////////////////////////////////////////////////////////////////////////////////////
        //                                  INSERT                                              //
        //////////////////////////////////////////////////////////////////////////////////////////
        $query = "SELECT COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, COLUMN_KEY, EXTRA, COLUMN_COMMENT
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE table_schema = '" . $current_schema . "'
                  AND table_name = '" . $table . "'
                  ORDER BY ORDINAL_POSITION;";
        //print $query;
        $result = $conn -> query($query);
        //print($result->num_rows);
        //$row = $result -> fetch_object();
        $i = 1;
        $cols = "";
        //$pk_name = "";
        $pk_value = "";
        while($coloana = $result -> fetch_object())
        {
            $valoare = $_POST[$coloana->COLUMN_NAME."_".$coloana->DATA_TYPE . $coloana->ORDINAL_POSITION];
            $valoare = $conn->real_escape_string($valoare);
            
            //print(strlen($valoare));
            
            // for non-last records:
            if($i < $result->num_rows)
            {
                if($coloana->EXTRA == "auto_increment")
                {
                    $cols = $cols . "default, ";
                }
                else
                {
                    if($coloana->DATA_TYPE == "varchar" || $coloana->DATA_TYPE == "char" || $coloana->DATA_TYPE == "date")
                    {
                        $cols = $cols . "'" . $valoare . "', ";
                    }
                    else
                    {
                        if($valoare === "" || strlen($valoare) === 0)
                        {
                            $cols = $cols . "null, ";
                        }
                        else
                        {
                            $cols = $cols . $valoare . ", ";
                        }
                    }
                }
            }
            // for last record:
            else
            {
                if($coloana->EXTRA == "auto_increment")
                {
                    $cols = $cols . "default";
                }
                else
                {
                    if($coloana->DATA_TYPE == "varchar" || $coloana->DATA_TYPE == "char" || $coloana->DATA_TYPE == "date")
                    {
                        $cols = $cols . "'" . $valoare . "'";
                    }
                    else
                    {
                        if($valoare === "" || strlen($valoare) === 0)
                        {
                            $cols = $cols . "null";
                        }
                        else
                        {
                            $cols = $cols . $valoare;
                        }
                    }
                }
            }
            
            if($coloana->COLUMN_KEY === "PRI")
            {
                //$pk_name = $coloana->COLUMN_NAME;
                $pk_value = $_POST[$coloana->COLUMN_NAME."_".$coloana->DATA_TYPE . $coloana->ORDINAL_POSITION];
            }

            $i++;
        }
        $sql = "INSERT INTO " . $table . " VALUES (" . $cols . ");";

        //print($sql);
        $result = $conn -> query($sql);
        $id_value = $conn->insert_id;
        //print($id_value);
        if($id_value === 0)
        {
            $id_value = $pk_value;
        }
        
        if($conn->errno === 0)
            $msg = "&#128076; <font color='blue'>SAVED </font>";
        else
            $msg = "&#129310; <font color='red'>ERROR [" . $conn->errno . ": " . $conn->error ."]</font>";
    }
    else
    {
        //print("UPDATE");
        //////////////////////////////////////////////////////////////////////////////////////////
        //                                  UPDATE                                              //
        //////////////////////////////////////////////////////////////////////////////////////////
        $query = "SELECT COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, COLUMN_KEY, EXTRA, COLUMN_COMMENT
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE table_schema = '" . $current_schema . "'
                  AND table_name = '" . $table . "';";
        //print $query;
        $result = $conn -> query($query);
        //print($result->num_rows);
        //$row = $result -> fetch_object();
        $i = 1;
        $cols = "";
        while($coloana = $result -> fetch_object())
        {
            $valoare = $_POST[$coloana->COLUMN_NAME."_".$coloana->DATA_TYPE . $coloana->ORDINAL_POSITION];
            $valoare = $conn->real_escape_string($valoare);
            if($valoare === "" || strlen($valoare) === 0)
            {
                if($coloana->DATA_TYPE == "varchar" || $coloana->DATA_TYPE == "char" || $coloana->DATA_TYPE == "date")
                {
                    $valoare = '';
                }
                else
                {
                    $valoare = 'null';
                }
            }
            
            if($i < $result->num_rows)
            {
                if($coloana->EXTRA != "auto_increment")
                {
                    if($coloana->DATA_TYPE == "varchar" || $coloana->DATA_TYPE == "char" || $coloana->DATA_TYPE == "text" || $coloana->DATA_TYPE == "date")
                    {
                        $cols = $cols . $coloana->COLUMN_NAME ." = '" . $valoare . "', ";
                    }
                    else
                    {
                        $cols = $cols . $coloana->COLUMN_NAME ." = " . $valoare . ", ";
                    }
                }
            }
            else
            {
                if($coloana->EXTRA != "auto_increment")
                {
                    if($coloana->DATA_TYPE == "varchar" || $coloana->DATA_TYPE == "char" || $coloana->DATA_TYPE == "text" || $coloana->DATA_TYPE == "date")
                        $cols = $cols . $coloana->COLUMN_NAME ." = '" . $valoare . "'";
                    else
                        $cols = $cols . $coloana->COLUMN_NAME ." = " . $valoare;
                }
            }

            $i++;
        }
        if(is_numeric($id_value))
        {
            $sql = "UPDATE " . $table . " SET " . $cols . " WHERE " . $id_name . " = " . $id_value . ";";
        }
        else
        {
            $sql = "UPDATE " . $table . " SET " . $cols . " WHERE " . $id_name . " = '" . $id_value . "';";
        }
            
        //print($sql);
        $result = $conn -> query($sql);
        if($conn->errno === 0)
            $msg = "&#128076; <font color='blue'>SAVED </font>";
        else
            $msg = "&#129310; <font color='red'>ERROR [" . $conn->errno . ": " . $conn->error ."]</font>";
    }
}




////////////////////////////////////////////////////////////////////////////////
//                                    DELETE                                    //
////////////////////////////////////////////////////////////////////////////////
if($for == "DELETE")
{
    //print($id_name);
    //print($id_value);
    
    if(is_numeric($id_value))
    {
        $sql = "DELETE FROM " . $table . " WHERE " . $id_name . " = " . $id_value . ";";
    }
    else
    {
        $sql = "DELETE FROM " . $table . " WHERE " . $id_name . " = '" . $id_value . "';";
    }

    //print($sql);
    $result = $conn -> query($sql);
    if($conn->errno === 0)
    {
        $msg = "&#128076; <font color='blue'>DELETED SUCCESSFULLY </font>";
        $id_value = "";
    }
    else
    {
        $msg = "&#129310; <font color='red'>ERROR [" . $conn->errno . ": " . $conn->error ."]</font>";
    }
}

?>
<html>
<head>
<title>Edit: <?php print($table); ?></title>
<link rel="stylesheet" type="text/css" href="./styles.css">
<script language=javascript>
function Save()
{
    document.frmForm.hdnFor.value = "SAVE";
    document.frmForm.submit();
}
function AddNew()
{
    //document.frmForm.<?php //print($id_name); ?>.value = "";
    //document.getElementById("frmForm").reset();
    var elements = frmForm.elements;
    for(i=0; i<elements.length; i++) 
    {
        field_type = elements[i].type.toLowerCase();
        switch(field_type)
        {
            case "text":
            case "number":
            case "textarea":
            case "hidden":
                elements[i].value = "";
                break;
            case "select-one":
            case "select-multi":
                elements[i].selectedIndex = -1;
                break;
            default:
                  break;
        }
    }
    //alert(window.location.href);
    //alert('<?php print($id_name . "=" . $id_old_value); ?>');
    txt = window.location.href;
    res = txt.replace('<?php print($id_name . "=" . $id_old_value); ?>', '<?php print($id_name . "="); ?>');
    //alert(res);
    document.frmForm.action = res;
    //document.frmForm.reset();
    document.frmForm.hdnFor.value = "ADDNEW";
    document.frmForm.submit();
}
function Delete(i)
{
    if(confirm('Confirm delete?'))
    {
        document.frmForm.hdnFor.value = "DELETE";
        document.frmForm.submit(); 
    }
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
<TABLE width="100%" cellpadding="3" cellspacing="0" class='clsFineBorder' style="text-align: center">
	<tr>
            <TD bgcolor="#e6f2ff" style="text-align: center">
                <b><?php print("&nbsp;".$msg) ?></b>
            </TD>
	</TR>
</TABLE>
<br/>


   
<?php
/////////////////////////////////////////////////////////////////////////////
//                          table parent                                  //
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


<table class="form_table" cellspacing="0" cellpadding="0">
    
<?php

if($table_parent === "" || strlen($table_parent) === 0)
{
    //$query = "SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
    //            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    //            WHERE
    //              REFERENCED_TABLE_SCHEMA = '" . $current_schema . "' AND
    //              TABLE_NAME = '" . $table . "';";
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
        if(($parentIdValue === "" || strlen($parentIdValue) === 0 && $id_value != ""))
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
    <td colspan="4" style="border:none;">&#128194;&nbsp;
        <b><a class='entity_name' href='entityView.php?app=<?php print($appCode); ?>&table=<?php print($table_parent); ?>'><?php print($table_parent); ?></a></b>
    </td>
    </tr>

    <tr>
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
    
    //print('aaa');
    $query = "SELECT * FROM " . $table_parent . " WHERE " . $parentIdColumnFK . " = '" . $parentIdValue . "';";
    //print('aaa');

    //defined in xml_functions.inc
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
    <td colspan="4" style="font-size:16px;border:none;">&#128194;&nbsp;
        <b><a class='entity_name' href='entityView.php?app=<?php print($appCode); ?>&table=<?php print($table_parent); ?>'><?php print($table_parent); ?></a></b>
    </td>
    </tr>

    <tr>
    <td style="background-image:url(./images/linie2.JPG);background-repeat:repeat-y;width:25px;border:none;"></td>
    <td colspan="3">

    <?php
    print ($xmlTable);
    print ("<br /><br />");
}
?>




</td>
</tr>


<tr>
<td style="background-image:url(./images/sageata_capat2.JPG);background-repeat:no-repeat;border:none;"></td>
<td style="width:10px;border:none;">&#128194;&nbsp;</td>
<td colspan="2" style="border:none;background-color:#e6eefc;">    
    <b><a class='entity_name' href='entityView.php?app=<?php print($appCode); ?>&table=<?php print($table); ?>&parent=<?php print($table_parent); ?>&parentId=<?php print($parentIdValue); ?>'><?php print($table); ?></a></b> &#128204; 
</td>
</tr>





<tr>
<td style="border:none;"></td>
<?php
///////////////////////////////////////////////////
//  HAS KIDS?
///////////////////////////////////////////////////
$query = "SELECT COUNT(*) as C
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
          REFERENCED_TABLE_SCHEMA = '" . $current_schema . "' AND
          REFERENCED_TABLE_NAME = '" . $table . "';";
//print($query);
$result = $conn -> query($query);
$row = $result -> fetch_object();
$k = $row->C;
//print($k);
if($k > 0)
{
?>
<td style="background-image:url(./images/linie3.JPG);background-repeat:repeat-y;border:none;"></td>
<?php
}
else
{
?>
<td style="border:none;"></td>
<?php
}
?>

<td colspan="2" style="border:none;background-color:#e6eefc;">    
<form name='frmForm' method='post'>
<?php
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
//                              EDIT FROM:                               //
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
$query = "SELECT C.COLUMN_NAME, C.ORDINAL_POSITION, C.COLUMN_DEFAULT, C.IS_NULLABLE, C.DATA_TYPE, C.CHARACTER_MAXIMUM_LENGTH, C.NUMERIC_PRECISION, C.NUMERIC_SCALE, C.COLUMN_KEY, C.EXTRA, C.COLUMN_COMMENT,
	CU.REFERENCED_TABLE_NAME, CU.REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS AS C
        LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS CU ON C.COLUMN_NAME = CU.COLUMN_NAME
            AND CU.table_schema = '" . $current_schema . "' AND CU.table_name = '" . $table . "'   
            AND CU.REFERENCED_TABLE_NAME IS NOT NULL
        WHERE C.table_schema = '" . $current_schema . "'
          AND C.table_name = '" . $table . "' 
          ORDER by C.ORDINAL_POSITION;";
//print $query;
$result = $conn -> query($query);
//$row = $result -> fetch_object();
while($row = $result -> fetch_array(MYSQLI_ASSOC))
{
    $coloane[] = $row;
}


//print_r($coloane);

//defined in xml_functions.inc
$xml = GetXMLfromQuery($conn, $query, $table, $table);
//print_r ($xml);


$proc = new XSLTProcessor();




$query = "SELECT * FROM " . $table . " WHERE " . $id_name . " = '" . $id_value . "';";
//print($query);
$result = $conn -> query($query);
$row = $result -> fetch_object();
$valori = (array)$row;
//$xslTable = GetXSLInsertForm($table, $table);
$xslTable = GetXSLEditForm($table, $coloane, $valori, $parentIdColumn, $parentIdValue, $conn, $appCode);

$proc->importStyleSheet($xslTable);
//$xmlTable = $proc->transformToXML($xmlPre);
$xmlTable = $proc->transformToXML($xml);
print ($xmlTable);

?>
    <br /><br />
    <input type="button" name="btnAddNew" onclick="AddNew()" VALUE=" Add  &#128396;">&nbsp;&nbsp;&nbsp;
    <input type="button" name="btnDelete" onclick="Delete('<?php print($id_value) ?>')" value="Delete &#128683;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    
    <input type="button" name="btnSave" class='button_default' onclick='Save()' value=' Save  &#128190;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    
    <!--input type="button" name='btnPlus' onclick="plus(<?php print($id_value) ?>)" value="&gt;"-->
    
    <?php
    if($table_parent != "")
    {
    ?>
    <span style="float:right;"><b><a class='entity_name' href='entityView.php?app=<?php print($appCode); ?>&table=<?php print($table); ?>&parent=<?php print($table_parent); ?>&<?php print($id_name); ?>=<?php print($id_value); ?>'>Back to <?php print($table); ?></a></b></span>
    <?php
    }
    ?>
    
    <input type="hidden" name="hdnFor">
    <input type="hidden" name="<?php print($id_name); ?>" value="<?php print($id_value) ?>">
</form>

<br /><br />

</td>
</tr>



<?php
//////////////////////////////////////////////////////////////////////
//                            CHILD TABLES                          //
//////////////////////////////////////////////////////////////////////
$query = "SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
          REFERENCED_TABLE_SCHEMA = '" . $current_schema . "' AND
          REFERENCED_TABLE_NAME = '" . $table . "';";
//print($query);
$result = $conn -> query($query);
//$row = $result -> fetch_object();
$link_details = "";
$c = mysqli_num_rows($result);
$c1 = 1;

//print($c);

while($row = $result -> fetch_object())
{
    $link_details = $row->TABLE_NAME;
    $query = "SELECT * FROM " . $link_details . " WHERE " . $row->COLUMN_NAME . " = '" . $id_value . "';";
    //print $query;

    // VINE DIN xml_functions.inc
    $xml = GetXMLfromQuery($conn, $query, $link_details, $table);
    //print_r ($xml);

    
    $query2 = "SELECT COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, COLUMN_KEY, EXTRA, COLUMN_COMMENT
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_schema = '" . $current_schema . "'
          AND table_name = '" . $link_details . "';";
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

    $xslTable = GetXSLTable($link_details, $table, 'entityEdit.php', $link_id, $table."&amp;app=".$appCode."&amp;table=".$link_details, "_self");
    $proc->importStyleSheet($xslTable);
    //$xmlTable = $proc->transformToXML($xmlPre);
    $xmlTable = $proc->transformToXML($xml);
    //print("<hr/>");
    
    ?>
    <tr>
    <td style="border:none;"></td>
    <td style="background-image:url(./images/sageata_capat3.JPG);background-repeat:no-repeat;border:none;"></td>
    <td style="font-size: 14px;width:10px;border:none;">&#128194; </td>
    <td style="font-size: 14px;border:none;"> 
        <b><a class='entity_name' href='entityView.php?app=<?php print($appCode); ?>&table=<?php print($link_details); ?>&parent=<?php print($table); ?>&parentId=<?php print($id_value); ?>'><?php print($link_details); ?></a></b>
    </td>
    </tr>
    
    <tr>
    <td style="border:none;"></td>
    <?php
    //print($c1);
    if($c1 < $c)
    {
        ///print($c1);
    ?>
    <td colspan="2" style="background-image:url(./images/linie3.JPG);background-repeat:repeat-y;border:none;"></td>
    <?php
    }
    else
    {
    ?>
    <td colspan="2" style="border:none;"></td>
    <?php
    }
    ?>
    
    <td> 
    <?php
    print ($xmlTable);
    
    if($link_details != "")
    {
        print("[+] <a href='entityEdit.php?app=".$appCode."&parent=".$table."&amp;parentId=".$id_value."&amp;table=".$link_details."'>Add new</a>");
        print("<br/><br/><br/>");
    }
    ?>
    </td>
    </tr>
    <?php
    $c1 = $c1 + 1;
}


?>
</td>
</tr>
</table>

</div>
<?php require_once("menu_right.inc"); ?>
<div class="right">
    &#128200; Entity item reports:
    <hr/>
    <ul>
    <?php
    $query = "SELECT * FROM seed_entity_item_reports WHERE `table` = '".$table."'";
    //print($query);
    $result = $conn -> query($query);
    while($row = $result -> fetch_object())
    {
        //$query_entity_item_report = $row->sqlReport;
        $query_entity_item_report = str_replace('???', $id_value, $row->sqlReport);
        //print($query_entity_item_report);
        
        //print("<li>". $row->description);
        print("<li> <span title='". $query_entity_item_report . "'>". $row->description."</span>");
        //$xml = GetXMLfromQuery($conn, $query_entity_item_report, 'items', 'item');
        $xml = GetXMLfromQuery($conn, $query_entity_item_report, 'items', 'item');
        $proc = new XSLTProcessor();
        
        //$xslTabel = GetXSLTable('items', 'item');
        $xslTabel = GetXSLTable('items', 'item', 'entityEdit.php', $parentIdColumnFK, "&amp;app=".$appCode."&amp;table=".$table_parent, "_self", "", "xml_report");
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
        [+] <a target='_blank' href='./entityEdit.php?app=_system&table=seed_entity_item_reports'>Add</a>&nbsp;
    </div>
    <br/><br/>
</div>
    
</div>

</body>
</html>