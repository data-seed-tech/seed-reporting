<?php
//require_once("connection.inc");

if($appCode != "")
{
    $sel_reports     = "SELECT * FROM seed_app_reports WHERE appCode = '".$appCode."' ORDER BY appCode, reportId;";
}
else
{
    $sel_reports     = "SELECT * FROM seed_app_reports ORDER BY appCode, reportId;";
}
//print($sel_reports);

$result_reports  = $conn -> query($sel_reports);
//$err =   $conn->error;
$alert_max = "";
?>
<table class="xml_table">
    <tr>
        <th>ID</th>
        <th>Report Name</th>
        <th>COUNT...</th>
        
        <th width="40%">Report Description</th>
        <th>&#9888; Status</th>
    </tr>
    <tr>
    <?php
    while ($row_reguli = $result_reports ->fetch_object())
    {
    $appCode            = $row_reguli->appCode;    
    $reportId           = $row_reguli->reportId;
    $reportName         = $row_reguli->reportName;
    $reportDescription  = $row_reguli->reportDescription;
    $activationCriteria = $row_reguli->activationCriteria;
    $slowExecution      = $row_reguli->slowExecution;
    $sqlMinCondition    = $row_reguli->sqlMinCondition;
    $sqlMaxCondition    = $row_reguli->sqlMaxCondition;
    $priority           = $row_reguli->priority;
    
    ?>
    <tr>
        <td><a href="report.php?app=<?php print($appCode); ?>&id=<?php print($reportId); ?>"><?php print($reportId); ?></a></td>
        <td><a href="report.php?app=<?php print($appCode); ?>&id=<?php print($reportId); ?>"><?php print($reportName); ?></a></td>
        <td class="numar" style="background-color: #ffffdc" title="<?php print(htmlentities($activationCriteria)); ?>">
            <?php
            // RUNNING COUNT
            if(!$slowExecution)
            {
                $query  = $activationCriteria;
                $result = $conn -> query($query);
                $row    = $result ->fetch_row();
                $count  = $row[0];
                
                $query  = $sqlMinCondition;
                $result = $conn -> query($query);
                $row    = $result ->fetch_row();
                $sqlMinCondition= $row[0];
                
                $query  = $sqlMaxCondition;
                $result = $conn -> query($query);
                $row    = $result ->fetch_row();
                $sqlMaxCondition= $row[0];
                
                
                if($count < $sqlMinCondition)
                {
                    $alert_min = "&#9888;";
                }
                else
                {
                    $alert_min = "";
                }   
                
                
                if($count > $sqlMaxCondition)
                {
                    $alert_max = "&#9888;";
                }
                else
                {
                    $alert_max = "";
                }    
            }
            else
            {
                $count = "Slow execution...";
                $alert_min = "";
                $alert_max = "";
            }
            ?>
            <a href="report.php?app=<?php print($appCode); ?>&id=<?php print($reportId); ?><?php print("&for=criteriu"); ?>"><?php print($count); ?></a> &nbsp;
        </td>
        <td><?php print($reportDescription); ?></td>
        <td <?php if((strlen($alert_min) > 0) || (strlen($alert_max) > 0)) print(" style='background-color: #ff0000' title='Interval: " . $sqlMinCondition . " - " . $sqlMaxCondition . "'"); ?>>
            <?php print($alert_max); ?>
            <?php 
            if((strlen($alert_min) > 0) || (strlen($alert_max) > 0))
            {
                print("PRIO: " . $priority); 
            }
            else
            {
                print("&#9989; OK");
            }
            ?>
        </td>
    </tr>
    <?php
    }
    ?>
    </tr>
</table>
<br/>

<div style="text-align:right;">
<?php
print("[+] <a href='entityEdit.php?app=".$appCode."&parent=seed_apps&amp;parentId=".$appCode."&amp;table=seed_app_reports'>Add new report</a>");
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<div>