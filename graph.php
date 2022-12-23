<?php
require_once("./connection.inc");
require_once("./counter.inc");
require_once("./appCode.inc");

function checkIsAValidDate($myDateString){
    //print(strtotime($myDateString));
    return (bool)strtotime($myDateString);
}



$reportId = @$_GET['id'];
//print $reportId;
if($reportId == "")
{
    $reportId = @$_POST['id'];
}

$axaX = "";
$axaY = "";

/////////////////////////////////////
//              table              //
/////////////////////////////////////
$table  =	@$_POST['table'];
if($table == "")
{
    $table  = @$_GET['table'];
}

$query_report = "SELECT reportName, reportDescription, activationCriteria, sqlReport, linkAddress, linkId
                FROM seed_app_reports WHERE reportId = '" . $reportId . "';";
//print $query_report;

$result_report = $conn -> query($query_report);
$row_report = $result_report ->fetch_object();
$err =   $conn->error;
print $err;

$reportName = $row_report->reportName;
$reportDescription = $row_report->reportDescription;
$sqlReport = $row_report->sqlReport;
//print $sqlReport;
/*
$link_address       = $row_report->link_address;
$link_id            = $row_report->link_id;
 */

$result = $conn -> query($sqlReport);
//$row = $result ->fetch_object();
$row = $result ->fetch_row();
$nr_coloane = $result->field_count;
$nr_randuri = $result->num_rows;
$i = 0;
while ($i < $nr_coloane)
{
    //$nume_coloana = htmlentities(mysqli_fetch_field_direct($result, $i)->name, ENT_XML1, 'UTF-8');
    $nume_coloana = mysqli_fetch_field_direct($result, $i)->name;
    //print($nume_coloana);
    if($nume_coloana == 'count(*)')
    {
        print ("Eroare! Selectul nu poate fi count(*)! Trebuie sa aiba un alias!");
        return;
    }

    if(checkIsAValidDate($row[$i]))
    {
        if($axaX === "")
        {
            $axaX = $nume_coloana;
            //break;
        }
    }
    
    if(is_numeric($row[$i]))
    {
        if($axaY === "")
        {
            $axaY = $nume_coloana;
            //break;
        }
    } 


    $i++;
}

//print $axaX;
//print $axaY;


$grosime = round(420/$nr_randuri, 0);
?>

<html>

<head>
<title><?php print($reportName); ?></title>
<link rel="stylesheet" type="text/css" href="./styles.css">
<script type="text/javascript" src="./scripturi.js"></script>
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
    
<div class="content_left">

<?php
$min = 0;
$max = 0;


$result = $conn -> query($sqlReport);
//print $sqlReport;
//$row = $result->fetch_object();
//while($row = $result->fetch_row())
while($row = $result->fetch_array(MYSQLI_ASSOC))
{
    if($min === 0)
    {
        $min = $row[$axaY];
        //print($min);
    }
    elseif($min > $row[$axaY])
    {
        $min = $row[$axaY];
    }
    //print($min);
    
    if($max === 0)
    {
        $max = $row[$axaY];
    }
    elseif($max < $row[$axaY])
    {
        $max = $row[$axaY];
    }
}
//print($min);
//print($max);

//print ($max - $min) * 100;
$height	= 150;
$zile = "";
$luna = "";
$l = 0;
$lunile = "";
?>


<table style="border:1px solid #c0c0c0" width="99%" height=<?php print($height); ?>>
    <tr style="height:<?php print $height-2 ?>">
        <td width="1%" valign=top>
            <TABLE style="border:1px;" CELLSPACING="0" CELLPADDING="0" width="100%" height="100%">
                <TR>
                    <td valign=top style='border:0px;font-size:9px' nowrap title="<?php print round($max, 4) ?>"><?php print round($max, 4) ?>&nbsp;</td>
                    <td valign=top style="border:0px;"  background="./imagini/rigla2.gif"><img src="./imagini/rigla1.gif"></td>
                    <td style="border:0px;">&nbsp;</td>
                </TR>
                <TR>
                    <td valign=top style='border:0px;font-size:9px' nowrap><?php print round($min, 4) ?>&nbsp;</td>
                    <td valign=top style="border:0px;"  background="./imagini/rigla2.gif"><img src="./imagini/rigla1.gif"></td>
                    <td style="border:0px;">&nbsp;</td>
                </TR>
            </TABLE>
        </td>
        <?php
        $result = $conn -> query($sqlReport);
        //while($row = $result ->fetch_object())
        while($row = $result->fetch_array(MYSQLI_ASSOC))
        {
            ?>
            <td valign="bottom" style="border:0px;"><img style="height:<?php print round(((($row[$axaY] - $min)*$height/2)/($max - $min))) + $height/2 ?>; width:<?php print($grosime); ?>px;" src="./imagini/g.gif" title="<?php print $row[$axaX] . ": " . $row[$axaY]; ?>"></td>
            <?php
            $d = date('d', strtotime($row[$axaX]));
            $zile = $zile . "<TD style='font-size:8px' bgcolor=#cacaca>" . $d . "</TD>";

            if ($luna != date('M', strtotime($row[$axaX]))) // trecerea in alta luna
            {
                if ($luna != "")
                {
                        $lunile = $lunile . "<TD style='font-size:8px' bgcolor='#cacaca' colspan=" . $l . ">" . $luna . "</TD>";
                }

                $luna = date('M', strtotime($row[$axaX]));
                $l = 1;
            }
            else
            {
                $l = $l + 1;
            }
        } //sfarsit while

        $lunile = $lunile . "<TD style='font-size:8px' bgcolor='#cacaca' colspan=" . $l . ">" . $luna . "</TD>"; // introduc ultima luna in stringul de luni
        ?>
        <td style="border:0px;"></td>
    </tr>
    <tr><td style="border:0px;"></td><?php print($zile); ?><td></td></tr>
    <tr><td style="border:0px;"></td><?php print($lunile); ?><td></td></tr>
</table>
<br />






<br /><br /><br />
<?php

$result = $conn -> query($sqlReport);
?>

<table class="xml_table">
    <tr>
        <th><?php print($axaX); ?></th>
        <th><?php print($axaY); ?></th>
    </tr>
    <?php
    //while($row = $result ->fetch_object())
    while($row = $result->fetch_array(MYSQLI_ASSOC))
    {
    ?>
        <tr><td><?php print $row[$axaX] ?></td><td><?php print($row[$axaY]); ?></td></tr>
    <?php
    }
    ?>
</table>


    
<br /><br />
<div style="border-style: groove; border-width: 1px; border-radius: 3px; background-color: #ffffdc; padding: 5px">
    &nbsp;
    <a onclick="javascript:document.getElementById('txaSQL').style.display = 'inline';">[ Afiseaza SQL ]</a>
    
    &nbsp;
    <a target="_blank" href="report_sql_insert.php?app=<?php print($appCode) ?>&id=<?php print($reportId); ?>">[ SQL Insert ]</a>
    
    &nbsp;
    <a target="_blank" href="report_xml_csv.php?app=<?php print($appCode) ?>&id=<?php print($reportId); ?>">[ Download CSV ]</a>
    
    
    &nbsp;
    <a href="#" onclick="javascript:document.getElementById('divDescriere').style.display = 'inline-block';">[ &#128161; Descriere report ]</a> 
</div>


<textarea rows="10" cols='200' id='txaSQL' style='display:none;'><?php print($sqlReport); ?></textarea><br />
<div id='divDescriere' class='reportDescription' style='display:none;'><?php print(nl2br($reportDescription)); ?></div><br />



</div>
</div>
</html>